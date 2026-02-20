# ============================================================
# IMPORTS
# ============================================================

from pathlib import Path
from datetime import datetime, timedelta
import logging
import os
import sys
import argparse
import time
import configparser

import numpy as np
import pandas as pd
import xarray as xr
import geopandas as gpd

from janitor import clean_names
from scipy.interpolate import LinearNDInterpolator, NearestNDInterpolator
from sqlalchemy import create_engine, text
from fsspec import filesystem
from concurrent.futures import ProcessPoolExecutor, as_completed


# ============================================================
# LOGGING
# ============================================================

LOG_FORMAT = "%(asctime)s | %(levelname)-8s | %(message)s"
logging.basicConfig(level=logging.INFO, format=LOG_FORMAT)
logger = logging.getLogger("ESMERALDA")


# ============================================================
# PARAMÈTRES GÉNÉRAUX
# ============================================================

TO_DB = True
TO_DELETE = True

ID_MODELE_BRUT = 61
ID_MODELE_INTERPO = 61

PESM = {
    "no2": 13,
    "nh3": 26,
    "o3": 12,
    "pm10": 14,
    "pm25": 15,
    "so2": 17,
    "co": 19,
    "no": 45,
    "c6h6": 18,
}

STATS = {
    "pm10": "mean",
    "pm25": "mean",
    "o3": "max",
    "no2": "max",
    "so2": "max",
    "nh3": "max",
    "co": "max",
    "c6h6": "max",
    "no": "max",
}

CONV = {
    "o3": 1.997,
    "no2": 1.914,
    "so2": 2.667,
    "co": 1.167,
    "nh3": 0.0696,
    "c6h6": 3.249,
    "no": 1.247,
}

PROJ_L2E = (
    "+proj=lcc +lat_0=47.34 +lon_0=3 "
    "+lat_1=44 +lat_2=49 +R=6370000 +units=m +no_defs"
)


# ============================================================
# CONFIG
# ============================================================

def load_config(path: str) -> configparser.ConfigParser:
    cfg_path = Path(path).expanduser().resolve()
    if not cfg_path.exists():
        raise FileNotFoundError(f"Fichier config introuvable: {cfg_path}")

    cfg = configparser.ConfigParser()
    cfg.read(cfg_path)

    for section in ("mysql", "sftp"):
        if section not in cfg:
            raise RuntimeError(f"Section manquante dans config: [{section}]")

    return cfg


def get_required(cfg: configparser.ConfigParser, section: str, key: str) -> str:
    if not cfg.has_option(section, key) or not cfg.get(section, key).strip():
        raise RuntimeError(f"Clé manquante dans config: [{section}] {key}")
    return cfg.get(section, key).strip()


def get_int(cfg: configparser.ConfigParser, section: str, key: str, default: int) -> int:
    return int(cfg.get(section, key, fallback=str(default)).strip())


def build_engine_url(cfg: configparser.ConfigParser) -> str:
    db_user = get_required(cfg, "mysql", "user")
    db_pass = get_required(cfg, "mysql", "password")
    db_host = get_required(cfg, "mysql", "host")
    db_port = get_int(cfg, "mysql", "port", 3306)
    db_name = get_required(cfg, "mysql", "database")
    return f"mysql+pymysql://{db_user}:{db_pass}@{db_host}:{db_port}/{db_name}"


# ============================================================
# OUTILS
# ============================================================

def join_vals(df: pd.DataFrame, col: str) -> str:
    return "" if df.empty else ";".join(df[col].astype(str))


def parse_ymd(s: str) -> datetime:
    return datetime.strptime(s, "%Y-%m-%d")


def build_run_dates(args) -> list[datetime]:
    if args.date:
        return [parse_ymd(args.date)]

    if args.dates:
        return [parse_ymd(x.strip()) for x in args.dates.split(",") if x.strip()]

    if args.date_from and args.date_to:
        d0 = parse_ymd(args.date_from)
        d1 = parse_ymd(args.date_to)
        if d1 < d0:
            raise ValueError("--to doit être >= --from")
        out = []
        cur = d0
        while cur <= d1:
            out.append(cur)
            cur += timedelta(days=1)
        return out

    return [datetime.now()]


def make_nc_path(base_dir: Path, run_date: datetime) -> Path:
    data_dir = base_dir / "data_netcdf"
    data_dir.mkdir(exist_ok=True)
    return data_dir / f"esmeralda_{run_date.strftime('%Y%m%d')}.nc"


def ensure_valid_netcdf(nc_path: Path) -> None:
    if not nc_path.exists():
        raise FileNotFoundError(f"NetCDF introuvable: {nc_path}")
    if nc_path.stat().st_size < 1024:
        raise ValueError(f"NetCDF trop petit / suspect: {nc_path} ({nc_path.stat().st_size} bytes)")


# ============================================================
# NETCDF DOWNLOAD
# ============================================================

def fetch_netcdf(
    nc_path: Path,
    date_run8: str,
    an: str,
    mois: str,
    cfg: configparser.ConfigParser,
    no_download: bool = False,
    force_download: bool = False,
    retries: int = 5,
    retry_sleep: int = 10,
) -> Path:
    if nc_path.exists() and not force_download:
        logger.info("NETCDF déjà présent → pas de téléchargement (%s)", nc_path)
        ensure_valid_netcdf(nc_path)
        return nc_path

    if no_download:
        raise FileNotFoundError(
            f"NETCDF absent (ou force demandé) mais --no-download activé: {nc_path}"
        )

    s_host = get_required(cfg, "sftp", "host")
    s_port = get_int(cfg, "sftp", "port", 22)
    s_user = get_required(cfg, "sftp", "username")
    s_pass = cfg.get("sftp", "password", fallback="").strip()
    s_key = cfg.get("sftp", "key_filename", fallback="").strip()

    if nc_path.exists() and force_download:
        nc_path.unlink(missing_ok=True)

    remote = (
        f"dropdir/esmeralda/{an}/{mois}/{date_run8}/"
        f"archive.ESM.{date_run8}.nc"
    )

    last_exc = None
    for attempt in range(1, retries + 1):
        try:
            logger.info(
                "Téléchargement NETCDF ESMERALDA (%s) [tentative %s/%s]",
                nc_path, attempt, retries
            )

            storage = dict(
                protocol="sftp",
                host=s_host,
                port=s_port,
                username=s_user,
            )
            if s_key:
                storage["key_filename"] = s_key
            else:
                if not s_pass:
                    raise RuntimeError("SFTP: config doit fournir password OU key_filename")
                storage["password"] = s_pass

            fs = filesystem(**storage)
            fs.get_file(remote, nc_path)

            logger.info("Téléchargement terminé (%s)", nc_path)
            ensure_valid_netcdf(nc_path)
            return nc_path

        except Exception as e:
            last_exc = e
            logger.warning("Échec téléchargement (tentative %s/%s): %s", attempt, retries, e)
            if attempt < retries:
                time.sleep(retry_sleep)

    raise RuntimeError(f"Impossible de télécharger le NetCDF après {retries} tentatives") from last_exc


# ============================================================
# LOAD + PREPARE DATA
# ============================================================

def load_and_prepare_data(nc_path: Path, today: datetime) -> pd.DataFrame:
    logger.info("Lecture et préparation des données (%s)", nc_path)

    ds = xr.open_dataset(nc_path)
    today_midnight = pd.Timestamp(today.date())

    df = (
        ds[[p.upper() for p in PESM]]
        .to_dataframe()
        .reset_index()
        .pipe(clean_names)
        .rename(columns=lambda c: c.replace("_", ""))
        .drop(columns="z")
        .assign(
            lagh=lambda x: (x["time"] - today_midnight) / pd.Timedelta(hours=1)
        )
        .query("lagh != -24")
        .assign(
            lagj=lambda x: np.floor((x["lagh"] - 1) / 24).astype(int),
            lagh=lambda x: ((x["lagh"] - 1) % 24).astype(int),
        )
    )

    for pol, coef in CONV.items():
        if pol in df.columns:
            df[pol] *= coef

    df = df.round(1)

    df = (
        gpd.GeoDataFrame(
            df,
            geometry=gpd.points_from_xy(df.x, df.y),
            crs=PROJ_L2E,
        )
        .to_crs(2154)
    )

    df[["x", "y"]] = df.geometry.get_coordinates()
    df = df.drop(columns=["geometry", "lon", "lat", "time"])
    df = df.rename(columns={"lagh": "time", "lagj": "day"})

    logger.info("Préparation terminée (%s lignes)", len(df))
    return df


# ============================================================
# INTERPOLATION WORKER
# ============================================================

def interpolate_one_slot(args):
    day, hour, df_day, grid, pollutants = args

    df_hour = df_day[df_day["time"] == hour]
    if df_hour.empty:
        return None

    points = df_hour[["x", "y"]].values

    out = pd.DataFrame({
        "x": grid[:, 0],
        "y": grid[:, 1],
        "day": day,
        "time": hour,
    })

    for pol in pollutants:
        vals = df_hour[pol].values
        try:
            f = LinearNDInterpolator(points, vals)
            interp = f(grid)
            if np.isnan(interp).any():
                raise ValueError
        except Exception:
            f = NearestNDInterpolator(points, vals)
            interp = f(grid)

        out[pol] = interp

    return out


# ============================================================
# INTERPOLATION PARALLÉLISÉE
# ============================================================

def interpolate_1km_parallel(data_lb: pd.DataFrame) -> pd.DataFrame:
    logger.info("Interpolation 1 km parallélisée")

    xi, yi = np.meshgrid(
        474500 + 1000 * np.arange(239),
        6581500 + 1000 * np.arange(294),
    )
    grid = np.column_stack((xi.ravel(), yi.ravel()))

    tasks = []
    for day in range(-1, 4):
        df_day = data_lb[data_lb["day"] == day]
        if df_day.empty:
            continue
        for hour in range(24):
            tasks.append((day, hour, df_day, grid, list(PESM.keys())))

    n_workers = max(1, (os.cpu_count() or 1) - 1)
    logger.info("Tâches=%s | Workers=%s", len(tasks), n_workers)

    results = []
    with ProcessPoolExecutor(max_workers=n_workers) as executor:
        futures = [executor.submit(interpolate_one_slot, t) for t in tasks]
        for f in as_completed(futures):
            res = f.result()
            if res is not None:
                results.append(res)

    if not results:
        raise RuntimeError("Aucun résultat d'interpolation (results vide).")

    df = pd.concat(results, ignore_index=True).round(1)
    logger.info("Interpolation terminée (%s lignes)", len(df))
    return df


# ============================================================
# AGRÉGATION JOURNALIÈRE
# ============================================================

def aggregate_daily(df: pd.DataFrame) -> pd.DataFrame:
    logger.info("Agrégation journalière")
    return (
        df.groupby(["x", "y", "day"])
        .agg(STATS)
        .reset_index()
        .sort_values(["day", "y", "x"])
        .round(1)
    )


# ============================================================
# INSERTION BASE
# ============================================================

def insert_into_db(data_lb, data_jour, today, date_run, write_current: bool, engine_url: str):
    """
    write_current:
      - True  => écrit aussi dans tables "courantes" (pollution, pollu_jour)
      - False => écrit seulement dans tables annuelles (pollution_YYYY, pollu_jour_YYYY)
    """
    logger.info("Insertion en base (write_current=%s)", write_current)

    engine = create_engine(engine_url)
    year = date_run[:4]

    jour_tables = [f"pollu_jour_{year}"]
    hour_tables = [f"pollution_{year}"]

    if write_current:
        jour_tables = ["pollu_jour"] + jour_tables
        hour_tables = ["pollution"] + hour_tables

    with engine.begin() as conn:

        # ---------- JOURNALIER ----------
        for table in jour_tables:
            conn.execute(
                text(f"DELETE FROM {table} WHERE id_modele=:m AND date_run=:d"),
                {"m": ID_MODELE_INTERPO, "d": date_run},
            )

        for day in range(-1, 3):
            j_date = (today + timedelta(days=day)).strftime("%Y-%m-%d")
            df_day = data_jour[data_jour["day"] == day]

            for pol, id_param in PESM.items():
                if pol not in df_day:
                    continue

                char = join_vals(df_day.sort_values(["y", "x"]), pol)

                for table in jour_tables:
                    conn.execute(text(f"""
                        INSERT INTO {table}
                        (id_modele,id_param,date_run,j_date,j_p01)
                        VALUES (:m,:p,:d,:j,'')
                        ON DUPLICATE KEY UPDATE id_modele=id_modele
                    """), {
                        "m": ID_MODELE_INTERPO,
                        "p": id_param,
                        "d": date_run,
                        "j": j_date
                    })

                    conn.execute(text(f"""
                        UPDATE {table}
                        SET j_p01=:v
                        WHERE id_modele=:m AND id_param=:p
                          AND date_run=:d AND j_date=:j
                    """), {
                        "v": char,
                        "m": ID_MODELE_INTERPO,
                        "p": id_param,
                        "d": date_run,
                        "j": j_date
                    })

        # ---------- HORAIRE ----------
        for table in hour_tables:
            conn.execute(
                text(f"DELETE FROM {table} WHERE id_modele=:m AND date_run=:d"),
                {"m": ID_MODELE_BRUT, "d": date_run},
            )

        for day in range(-1, 3):
            j_date = (today + timedelta(days=day)).strftime("%Y-%m-%d")
            df_day = data_lb[data_lb["day"] == day]

            for pol, id_param in PESM.items():
                char_list = [
                    join_vals(
                        df_day[df_day["time"] == h].sort_values(["y", "x"]),
                        pol
                    )
                    for h in range(24)
                ]

                for table in hour_tables:
                    conn.execute(text(f"""
                        INSERT INTO {table}
                        (id_modele,id_param,date_run,j_date)
                        VALUES (:m,:p,:d,:j)
                        ON DUPLICATE KEY UPDATE id_modele=id_modele
                    """), {
                        "m": ID_MODELE_BRUT,
                        "p": id_param,
                        "d": date_run,
                        "j": j_date
                    })

                    sets = ", ".join(f"h_p{i+1:02d}=:v{i}" for i in range(24))
                    params = {f"v{i}": char_list[i] for i in range(24)}
                    params.update({
                        "m": ID_MODELE_BRUT,
                        "p": id_param,
                        "d": date_run,
                        "j": j_date
                    })

                    conn.execute(text(f"""
                        UPDATE {table}
                        SET {sets}
                        WHERE id_modele=:m AND id_param=:p
                          AND date_run=:d AND j_date=:j
                    """), params)


# ============================================================
# MAIN
# ============================================================

def main():
    parser = argparse.ArgumentParser(description="Traitement ESMERALDA (par date + config.ini)")

    parser.add_argument(
        "--config",
        default="/home/ubuntu/scripts_modelisation/config_modele.ini",
        help="Chemin vers le fichier de configuration INI",
    )

    parser.add_argument("--date", help="Date unique YYYY-MM-DD")
    parser.add_argument("--dates", help="Liste séparée par virgules YYYY-MM-DD,YYYY-MM-DD")
    parser.add_argument("--from", dest="date_from", help="Début de plage YYYY-MM-DD")
    parser.add_argument("--to", dest="date_to", help="Fin de plage YYYY-MM-DD")

    parser.add_argument("--no-download", action="store_true",
                        help="N'utilise que les NetCDF locaux (doivent exister)")
    parser.add_argument("--force-download", action="store_true",
                        help="Force le téléchargement même si le fichier existe")

    parser.add_argument("--keep-nc", action="store_true",
                        help="Ne supprime pas les NetCDF à la fin")
    parser.add_argument("--no-db", action="store_true",
                        help="N'insère pas en base")

    parser.add_argument("--also-current", action="store_true",
                        help="Écrit aussi dans les tables courantes (pollution, pollu_jour). "
                             "Sinon, en multi-dates, écrit seulement dans *_YYYY (recommandé).")

    args = parser.parse_args()

    cfg = load_config(args.config)
    engine_url = build_engine_url(cfg)

    start_global = datetime.now()
    logger.info("===== DÉBUT TRAITEMENT ESMERALDA =====")
    logger.info("Python=%s", sys.executable)
    logger.info("CWD=%s", os.getcwd())
    logger.info("Config=%s", Path(args.config).expanduser().resolve())

    base_dir = Path(__file__).resolve().parent
    run_dates = build_run_dates(args)

    multi = len(run_dates) > 1
    if multi and not args.also_current:
        logger.info("Backfill multi-dates -> écriture seulement dans tables annuelles (*_YYYY).")

    write_current = bool(args.also_current)

    for run_date in run_dates:
        start = datetime.now()

        today = run_date
        date_run = today.strftime("%Y-%m-%d")
        date_run8 = today.strftime("%Y%m%d")
        an = date_run[:4]
        mois = date_run[5:7]

        nc_path = make_nc_path(base_dir, today)
        logger.info("---- RUN %s | nc=%s ----", date_run, nc_path)

        fetch_netcdf(
            nc_path=nc_path,
            date_run8=date_run8,
            an=an,
            mois=mois,
            cfg=cfg,
            no_download=args.no_download,
            force_download=args.force_download,
        )

        data_lb = load_and_prepare_data(nc_path, today)
        data_interpo = interpolate_1km_parallel(data_lb)
        data_jour = aggregate_daily(data_interpo)

        if TO_DB and not args.no_db:
            insert_into_db(
                data_lb=data_lb,
                data_jour=data_jour,
                today=today,
                date_run=date_run,
                write_current=write_current,
                engine_url=engine_url
            )

        if TO_DELETE and not args.keep_nc and nc_path.exists():
            nc_path.unlink()
            logger.info("NETCDF supprimé (%s)", nc_path)

        logger.info("---- FIN RUN %s (%.1f s) ----", date_run, (datetime.now() - start).total_seconds())

    logger.info("===== FIN GLOBAL (%.1f s) =====", (datetime.now() - start_global).total_seconds())


# ============================================================
# ENTRYPOINT
# ============================================================

if __name__ == "__main__":
    main()

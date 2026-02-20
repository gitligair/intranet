#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
WRF -> extraction CVL -> NetCDF -> DB (meteo_2026 historique + meteo courant du jour)
- Pas de CSV
- Vérif shapefile optionnelle (--check-shape)
- Logs propres (console + fichier)
- Récupération d'une date (--date) ou d'une plage (--range)
- Copie du NetCDF extrait vers un répertoire réseau (--net-out)

Règles DB :
- meteo_2026 : on ajoute à la suite (append), option "dedup" si contrainte UNIQUE existe
- meteo : ne contient QUE la date du jour (nettoyage + insertion) et on ne la met à jour
         que si run_date == aujourd'hui (date de la machine)
"""

from __future__ import annotations

from pathlib import Path
from datetime import datetime, timedelta
import argparse
import logging
import shutil
import sys

import xarray as xr
import numpy as np
import geopandas as gpd
from shapely.geometry import Polygon
from pyproj import Geod
from fsspec import filesystem
import mysql.connector


# -------------------------
# CONFIG "FIXE" (à adapter)
# -------------------------
SHAPE_CVL = "CONTOUR_REGIONCENTRE_BDTOPO_Avril2018.shp"
TOL_COVER = 99.0
FILL_VALUE = ""

# Point Sud-Ouest CVL (WGS84)
SW_LON = 0.076133
SW_LAT = 46.300401

# Domaine d'extraction
NX, NY, NT = 77, 96, 97

VARS = [
    "t2", "relh2", "vit10", "wd10", "SWDOWN", "precip",
    "PBLH", "TH2", "RMOL", "UST",
    "PSFC", "temp",
]

ID_PARAM = {
    "t2": 1,
    "relh2": 2,
    "PSFC": 3,
    "vit10": 4,
    "wd10": 5,
    "SWDOWN": 7,
    "precip": 9,
    "PBLH": 10,
    "TH2": 34,
    "RMOL": 35,
    "UST": 46,
    "temp": 56,
}

ID_MODELE = 56

# DB MySQL (à adapter)
DB = {
    "host": "172.16.44.58",
    "port": 3306,
    "user": "vacarm_secours_user_online",
    "password": "vacarm2025",
    "database": "sortiemodeles_secours",
    "autocommit": False,
}

# Tables cibles
TABLE_CURRENT = "meteo"
TABLE_HISTORY = "meteo_2026"


# -------------------------
# LOGGING
# -------------------------
def setup_logging(log_dir: Path, run_tag: str, verbose: bool) -> logging.Logger:
    log_dir.mkdir(parents=True, exist_ok=True)
    log_file = log_dir / f"wrf_extract_{run_tag}.log"

    logger = logging.getLogger("wrf_extract")
    logger.setLevel(logging.DEBUG)
    logger.handlers.clear()
    logger.propagate = False

    fmt = logging.Formatter(
        fmt="%(asctime)s | %(levelname)-8s | %(message)s",
        datefmt="%Y-%m-%d %H:%M:%S",
    )

    ch = logging.StreamHandler(sys.stdout)
    ch.setLevel(logging.DEBUG if verbose else logging.INFO)
    ch.setFormatter(fmt)

    fh = logging.FileHandler(log_file, encoding="utf-8")
    fh.setLevel(logging.DEBUG)
    fh.setFormatter(fmt)

    logger.addHandler(ch)
    logger.addHandler(fh)

    logger.info("Logs -> %s", log_file)
    return logger


# -------------------------
# UTILS
# -------------------------
def grid_to_string(arr2d: np.ndarray) -> str:
    """Convertit une grille 2D en chaîne 'v1;v2;...'. NaN -> FILL_VALUE (vide par défaut)."""
    flat = arr2d.ravel()
    out = []
    for v in flat:
        if np.isnan(v):
            out.append(FILL_VALUE)
        else:
            out.append(f"{float(v):.1f}")
    return ";".join(out)


def build_remote_path(run_date: datetime) -> str:
    """
    Convention identique à ton script initial :
    run_date = date que tu veux traiter (dossier YYYY/MM/YYYYMMDD)
    source_date = run_date - 1 jour (nom du fichier archive.wrfout_ESM_{source8}.nc)
    """
    source_date = run_date - timedelta(days=1)

    an = run_date.strftime("%Y")
    mois = run_date.strftime("%m")
    run8 = run_date.strftime("%Y%m%d")
    source8 = source_date.strftime("%Y%m%d")

    return f"dropdir/WRF/{an}/{mois}/{run8}/archive.wrfout_ESM_{source8}.nc"


def daterange(d1: datetime, d2: datetime):
    cur = d1
    while cur <= d2:
        yield cur
        cur += timedelta(days=1)


# -------------------------
# SHAPE CHECK (OPTIONNEL)
# -------------------------
def check_shapefile_coverage(
    lon_c: np.ndarray,
    lat_c: np.ndarray,
    shape_path: str,
    tol_cover: float,
    logger: logging.Logger,
) -> float:
    logger.info("Vérification couverture shapefile: %s (tol=%.2f%%)",
                shape_path, tol_cover)

    gdf_cvl = gpd.read_file(shape_path).to_crs(epsg=4326)
    poly_cvl = gdf_cvl.unary_union

    poly_nc = Polygon([
        (lon_c[0, 0],   lat_c[0, 0]),
        (lon_c[0, -1],  lat_c[0, -1]),
        (lon_c[-1, -1], lat_c[-1, -1]),
        (lon_c[-1, 0],  lat_c[-1, 0]),
    ])

    inter = poly_nc.intersection(poly_cvl)
    ratio = inter.area / poly_cvl.area * 100

    if ratio >= tol_cover:
        logger.info("✅ Couverture OK: %.2f%%", ratio)
    else:
        logger.error(
            "❌ Couverture insuffisante: %.2f%% (< %.2f%%)", ratio, tol_cover)
        raise SystemExit(f"Couverture shapefile insuffisante: {ratio:.2f}%")

    return ratio


# -------------------------
# EXTRACTION WRF -> CVL
# -------------------------
def extract_cvl(
    ds: xr.Dataset,
    nc_out: Path,
    check_shape: bool,
    logger: logging.Logger,
) -> xr.Dataset:
    lon = ds["lon"].values
    lat = ds["lat"].values

    # Localisation SW
    dist2 = (lon - SW_LON) ** 2 + (lat - SW_LAT) ** 2
    j0, i0 = np.unravel_index(np.argmin(dist2), dist2.shape)
    logger.info(
        "Indice SW i0=%s j0=%s | Lon/Lat réel=%.6f %.6f",
        i0, j0, lon[j0, i0], lat[j0, i0]
    )

    # Extraction variables + sous-domaine
    sub = ds[VARS].isel(
        time=slice(0, min(NT, ds.sizes["time"])),
        y=slice(j0, j0 + NY),
        x=slice(i0, i0 + NX),
    )

    # Conserver la vraie géométrie en coords 2D
    lon_c = ds["lon"].isel(y=slice(j0, j0 + NY), x=slice(i0, i0 + NX)).values
    lat_c = ds["lat"].isel(y=slice(j0, j0 + NY), x=slice(i0, i0 + NX)).values

    sub = sub.assign_coords(
        lon=(("y", "x"), lon_c),
        lat=(("y", "x"), lat_c),
        ech=ds["time"].isel(time=slice(0, min(NT, ds.sizes["time"]))),
    ).swap_dims({"time": "ech"})

    # Attributs CRS utiles QGIS
    sub.attrs["crs"] = "EPSG:4326"
    sub["lon"].attrs.update(
        {"standard_name": "longitude", "units": "degrees_east"})
    sub["lat"].attrs.update(
        {"standard_name": "latitude", "units": "degrees_north"})

    # Vérif shapefile optionnelle
    if check_shape:
        check_shapefile_coverage(lon_c, lat_c, SHAPE_CVL, TOL_COVER, logger)
    else:
        logger.info("Vérification shapefile désactivée.")

    # Sauvegarde netcdf extrait
    sub.to_netcdf(nc_out)
    logger.info("NetCDF CVL créé: %s", nc_out)

    return sub


# -------------------------
# DB - génération des lignes
# -------------------------
def _build_rows(sub: xr.Dataset, run_date: datetime):
    """Génère les tuples (id_modele, id_param, date_run, ech, previs)."""
    date_run_str = run_date.strftime("%Y-%m-%d")

    ech_vals = sub["ech"].values
    ech_ref = ech_vals[0]
    ech_hours = ((ech_vals - ech_ref) / np.timedelta64(1, "h")).astype(int)

    for var in VARS:
        da = sub[var]
        id_param = ID_PARAM[var]
        for it in range(len(ech_vals)):
            field = da.isel(ech=it).values
            previs = grid_to_string(field)
            ech_h = int(ech_hours[it])
            yield (ID_MODELE, id_param, date_run_str, ech_h, previs)


# -------------------------
# DB - insertion sur 2 tables
# -------------------------
def export_to_db_dual(
    sub: xr.Dataset,
    run_date: datetime,
    logger: logging.Logger,
    batch_size: int = 500,
    history_table: str = TABLE_HISTORY,
    current_table: str = TABLE_CURRENT,
    # Historique: "append" (ajout pur) ou "dedup" (UPSERT si contrainte UNIQUE)
    history_mode: str = "append",
    # Courant: "upsert" conseillé (si UNIQUE), sinon "insert"
    current_mode: str = "upsert",
    # Nettoyage de la table courante: "truncate" (rapide) ou "delete_other_dates" (safe)
    current_cleanup: str = "delete_other_dates",
):
    """
    Règles:
    - Historique: toujours alimenté (meteo_2026)
    - Courant (meteo): seulement si run_date == aujourd'hui, et meteo ne garde QUE aujourd'hui
    """
    date_run_str = run_date.strftime("%Y-%m-%d")
    today_str = datetime.now().strftime("%Y-%m-%d")
    is_today = (date_run_str == today_str)

    def insert_sql(table: str) -> str:
        return f"""
        INSERT INTO {table} (id_modele, id_param, date_run, ech, previs)
        VALUES (%s, %s, %s, %s, %s)
        """

    def upsert_sql(table: str) -> str:
        return insert_sql(table) + " ON DUPLICATE KEY UPDATE previs = VALUES(previs)"

    # Historique
    sql_hist = upsert_sql(
        history_table) if history_mode == "dedup" else insert_sql(history_table)
    # Courant
    sql_cur = upsert_sql(
        current_table) if current_mode == "upsert" else insert_sql(current_table)

    logger.info("Connexion DB %s:%s/%s",
                DB["host"], DB["port"], DB["database"])
    cnx = mysql.connector.connect(**DB)
    cur = cnx.cursor()

    try:
        # 1) Historique toujours
        logger.info("Insertion historique -> %s | date_run=%s | mode=%s",
                    history_table, date_run_str, history_mode)
        batch = []
        total_hist = 0

        for row in _build_rows(sub, run_date):
            batch.append(row)
            total_hist += 1
            if len(batch) >= batch_size:
                cur.executemany(sql_hist, batch)
                cnx.commit()
                logger.debug("Historique commit batch=%d (total=%d)",
                             len(batch), total_hist)
                batch.clear()

        if batch:
            cur.executemany(sql_hist, batch)
            cnx.commit()
            logger.debug(
                "Historique commit final batch=%d (total=%d)", len(batch), total_hist)

        logger.info("Historique OK -> %s | lignes=%d",
                    history_table, total_hist)

        # 2) Table courante uniquement si run_date == aujourd'hui
        if not is_today:
            logger.info(
                "Table courante '%s' non modifiée (run_date=%s != aujourd'hui=%s).",
                current_table, date_run_str, today_str
            )
            return

        logger.info(
            "Mise à jour table courante -> %s (uniquement aujourd'hui=%s)", current_table, today_str)

        # Nettoyage pour ne garder que le jour courant
        if current_cleanup == "truncate":
            logger.warning("TRUNCATE %s", current_table)
            cur.execute(f"TRUNCATE TABLE {current_table}")
            cnx.commit()
        else:
            logger.info("Nettoyage %s: suppression des dates != %s",
                        current_table, today_str)
            cur.execute(
                f"DELETE FROM {current_table} WHERE date_run <> %s", (today_str,))
            cnx.commit()

        # Insertion du jour
        batch = []
        total_cur = 0

        for row in _build_rows(sub, run_date):
            batch.append(row)
            total_cur += 1
            if len(batch) >= batch_size:
                cur.executemany(sql_cur, batch)
                cnx.commit()
                logger.debug("Courant commit batch=%d (total=%d)",
                             len(batch), total_cur)
                batch.clear()

        if batch:
            cur.executemany(sql_cur, batch)
            cnx.commit()
            logger.debug("Courant commit final batch=%d (total=%d)",
                         len(batch), total_cur)

        logger.info("Courant OK -> %s | lignes=%d | date_run=%s",
                    current_table, total_cur, today_str)

    except Exception:
        cnx.rollback()
        logger.exception("Erreur DB -> rollback")
        raise
    finally:
        cur.close()
        cnx.close()


# -------------------------
# PIPELINE - 1 DATE
# -------------------------
def process_one_date(
    run_date: datetime,
    fs,
    out_dir: Path,
    network_copy_dir: Path | None,
    to_db: bool,
    keep_global: bool,
    check_shape: bool,
    logger: logging.Logger,
    history_mode: str,
    current_cleanup: str,
):
    out_dir.mkdir(parents=True, exist_ok=True)

    run8 = run_date.strftime("%Y%m%d")
    remote = build_remote_path(run_date)

    local_nc_global = out_dir / f"wrf_global_{run8}.nc"
    local_nc_cvl = out_dir / f"{run8}.nc"

    logger.info("---- Traitement %s ----", run_date.strftime("%Y-%m-%d"))
    logger.info("Remote: %s", remote)

    # Download
    fs.get_file(remote, str(local_nc_global))
    logger.info("Téléchargé: %s", local_nc_global)

    # Open + extract
    ds = xr.open_dataset(local_nc_global, engine="netcdf4")
    try:
        sub = extract_cvl(ds, local_nc_cvl,
                          check_shape=check_shape, logger=logger)
    finally:
        ds.close()

    # Copy to network dir (optionnel)
    if network_copy_dir is not None:
        network_copy_dir.mkdir(parents=True, exist_ok=True)
        dest = network_copy_dir / local_nc_cvl.name
        shutil.copy2(local_nc_cvl, dest)
        logger.info("Copie vers réseau: %s", dest)

    # DB export (double table)
    if to_db:
        export_to_db_dual(
            sub=sub,
            run_date=run_date,
            logger=logger,
            batch_size=500,
            history_table=TABLE_HISTORY,
            current_table=TABLE_CURRENT,
            history_mode=history_mode,          # "append" ou "dedup"
            current_mode="upsert",              # conseillé
            current_cleanup=current_cleanup,    # "delete_other_dates" ou "truncate"
        )
    else:
        logger.info("Insertion DB désactivée.")

    # Cleanup global
    if not keep_global:
        try:
            local_nc_global.unlink(missing_ok=True)
            logger.info("Supprimé: %s", local_nc_global)
        except Exception:
            logger.exception("Suppression impossible: %s", local_nc_global)
    else:
        logger.info("Conservation du fichier global: %s", local_nc_global)


# -------------------------
# CLI
# -------------------------
def parse_args():
    p = argparse.ArgumentParser()

    # Arguments date (optionnels maintenant)
    p.add_argument("--date", help="YYYYMMDD (date unique)")
    p.add_argument("--range", nargs=2, metavar=("START", "END"),
                   help="YYYYMMDD YYYYMMDD (plage)")

    p.add_argument("--out", default="outputs", help="répertoire sortie local")
    p.add_argument("--net-out", default=None,
                   help="répertoire réseau pour déposer le NC extrait (optionnel)")

    p.add_argument("--no-db", action="store_true",
                   help="désactive l'insertion DB")
    p.add_argument("--keep", action="store_true",
                   help="ne supprime pas le netcdf global")

    p.add_argument("--check-shape", action="store_true",
                   help="active la vérif shapefile")
    p.add_argument("--verbose", action="store_true",
                   help="logs DEBUG en console")
    p.add_argument("--log-dir", default="logs",
                   help="répertoire des fichiers log")

    p.add_argument(
        "--history-mode",
        choices=["append", "dedup"],
        default="append",
        help="mode historique meteo_2026",
    )

    p.add_argument(
        "--current-cleanup",
        choices=["delete_other_dates", "truncate"],
        default="delete_other_dates",
        help="nettoyage table meteo (courant)",
    )

    return p.parse_args()


def main():
    args = parse_args()

    out_dir = Path(args.out)
    net_dir = Path(args.net_out) if args.net_out else None

    if args.date:
        run_tag = args.date
    elif args.range:
        run_tag = f"{args.range[0]}_{args.range[1]}"
    else:
        run_tag = datetime.now().strftime("%Y%m%d")

    logger = setup_logging(
        Path(args.log_dir), run_tag=run_tag, verbose=args.verbose)

    # Sanity check
    missing = [v for v in VARS if v not in ID_PARAM]
    if missing:
        logger.error("ID_PARAM manquant pour: %s", missing)
        raise SystemExit(f"ID_PARAM manquant pour: {missing}")

    # SFTP source (à adapter)
    fs = filesystem(
        protocol="sftp",
        host="37.58.181.228",
        username="airprfro",
        password="Vf3F!N7anM",
        port=2332,
    )

    # Liste dates
   # Liste des dates à traiter
    if args.date:
        dates = [datetime.strptime(args.date, "%Y%m%d")]

    elif args.range:
        d1 = datetime.strptime(args.range[0], "%Y%m%d")
        d2 = datetime.strptime(args.range[1], "%Y%m%d")
        dates = list(daterange(d1, d2))

    else:
        # 👉 comportement par défaut : date du jour
        today = datetime.now()
        dates = [today]

    logger.info("Dates à traiter: %s", ", ".join(
        d.strftime("%Y-%m-%d") for d in dates))
    logger.info(
        "Options: db=%s | check_shape=%s | keep_global=%s | net_out=%s | history_mode=%s | current_cleanup=%s",
        (not args.no_db),
        args.check_shape,
        args.keep,
        str(net_dir) if net_dir else "None",
        args.history_mode,
        args.current_cleanup,
    )

    for d in dates:
        try:
            process_one_date(
                run_date=d,
                fs=fs,
                out_dir=out_dir,
                network_copy_dir=net_dir,
                to_db=(not args.no_db),
                keep_global=args.keep,
                check_shape=args.check_shape,
                logger=logger,
                history_mode=args.history_mode,
                current_cleanup=args.current_cleanup,
            )
        except Exception:
            logger.exception("Échec traitement date %s",
                             d.strftime("%Y-%m-%d"))
            raise

    logger.info("✅ Terminé.")


if __name__ == "__main__":
    main()

# ================= PARAMÈTRES =================

# Commandes utiles

# Traiter aujourd’hui(meteo_2026 + meteo du jour):
# python wrf.py - -date 20260203


# Traiter une date ancienne(meteo_2026 seulement, meteo inchangé):

# python wrf.py - -date 20240115


# Plage de dates(historique), sans toucher meteo:

# python wrf.py - -range 20240101 20240107


# Activer la vérif shapefile:

# python wrf.py - -date 20260203 - -check-shape


# Copier le NC extrait sur un répertoire réseau:

# python wrf.py - -date 20260203 - -net-out / mnt/share/wrf_cvl


# Historique en mode “dedup” (UPSERT) si tu as une clé UNIQUE:

# python wrf.py - -date 20260203 --history-mode dedup

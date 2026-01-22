#!/usr/bin/env python3
import mysql.connector
import numpy as np

# =====================================================
# FONCTIONS UTILITAIRES PROMPT
# =====================================================


def ask_int(question, default):
    val = input(f"{question} [{default}] : ").strip()
    return int(val) if val else default


def ask_str(question, default):
    val = input(f"{question} [{default}] : ").strip()
    return val if val else default


def ask_yes_no(question, default=True):
    d = "O/n" if default else "o/N"
    val = input(f"{question} ({d}) : ").strip().lower()
    if not val:
        return default
    return val.startswith("o")


# =====================================================
# QUESTIONS UTILISATEUR
# =====================================================
print("\n=== Calcul moyenne pollution (pollution_moyenne) ===\n")

ID_MODELE = ask_int("ID du modèle", 47)
ID_PARAM = ask_int("ID du polluant", 26)

DATE_RUN_DEBUT = ask_str("Date run début (YYYY-MM-DD)", "2025-07-17")
DATE_RUN_FIN = ask_str("Date run fin (YYYY-MM-DD)", "2025-07-21")

NB_POINTS = ask_int("Nombre de valeurs dans la grille", 70266)
NB_HEURES = ask_int("Nombre de champs horaires (h_pXX)", 23)

CONFIRM = ask_yes_no("Lancer le calcul ?", True)
if not CONFIRM:
    print("❌ Annulé par l'utilisateur")
    exit(0)

# =====================================================
# CONFIGURATION BASE DE DONNÉES
# =====================================================
DB_CONFIG = {
    "host": "193.70.39.2",
    "user": "vacarm",
    "password": "vacarm2023",
    "database": "sortiesmodeles_2018"
}

# =====================================================
# CONNEXION MYSQL
# =====================================================
conn = mysql.connector.connect(**DB_CONFIG)
cursor = conn.cursor()

cols = ", ".join([f"h_p{str(i).zfill(2)}" for i in range(1, NB_HEURES + 1)])

query = f"""
SELECT {cols}
FROM pollution_2025
WHERE id_modele = %s
  AND id_param  = %s
  AND j_date = DATE_SUB(date_run, INTERVAL 1 DAY)
  AND date_run BETWEEN %s AND %s
"""

cursor.execute(query, (ID_MODELE, ID_PARAM, DATE_RUN_DEBUT, DATE_RUN_FIN))
rows = cursor.fetchall()

print(f"\n📄 Lignes SQL trouvées : {len(rows)}")

# =====================================================
# INITIALISATION
# =====================================================
somme = np.zeros(NB_POINTS, dtype=np.float64)
compte = np.zeros(NB_POINTS, dtype=np.int64)

champs_utilises = 0

# =====================================================
# TRAITEMENT
# =====================================================
for row in rows:
    for champ in row:
        if not champ:
            continue

        valeurs = champ.split(";")

        # Ajustement longueur
        if len(valeurs) < NB_POINTS:
            valeurs += ["0"] * (NB_POINTS - len(valeurs))
        elif len(valeurs) > NB_POINTS:
            valeurs = valeurs[:NB_POINTS]

        arr = np.zeros(NB_POINTS, dtype=np.float64)

        for i, v in enumerate(valeurs):
            try:
                f = float(v)
                if f > 0:
                    arr[i] = f
            except:
                pass

        masque = arr > 0
        if np.any(masque):
            somme[masque] += arr[masque]
            compte[masque] += 1
            champs_utilises += 1

print(f"✔ Champs utilisés : {champs_utilises}")

# =====================================================
# MOYENNE + ARRONDI
# =====================================================
moyenne = np.zeros(NB_POINTS, dtype=np.float64)
mask_ok = compte > 0
moyenne[mask_ok] = np.round(somme[mask_ok] / compte[mask_ok], 2)

print("✔ Points non nuls :", np.sum(moyenne > 0))
print("✔ Max moyenne :", np.max(moyenne))

# =====================================================
# CONVERSION TEXTE
# =====================================================
valeurs_texte = ";".join(f"{v:.2f}" for v in moyenne)

# =====================================================
# INSERTION EN BASE
# =====================================================
insert_sql = """
INSERT INTO pollution_moyenne
(id_modele, id_param, date_run_debut, date_run_fin, valeurs)
VALUES (%s, %s, %s, %s, %s)
"""

cursor.execute(
    insert_sql,
    (ID_MODELE, ID_PARAM, DATE_RUN_DEBUT, DATE_RUN_FIN, valeurs_texte)
)

conn.commit()

cursor.close()
conn.close()

print("\n✅ Calcul terminé et inséré dans pollution_moyenne\n")

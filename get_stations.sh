#!/bin/bash

# === Configuration ===
API_KEY="rvvbrwhq5gwadjafdbxfqjcgfinhh1e5"
API_SECRET="fdhlrmyjqvr6lr968gnajid0apyvm39b"

# === Timestamp actuel ===
TIMESTAMP=$(date +%s)

# === Chemin de l'API ===
API_PATH="/v2/stations"

# === Construction de la query string ===
QUERY="api-key=${API_KEY}&t=${TIMESTAMP}"

# === Construction du string à signer ===
STRING_TO_SIGN="${API_PATH}?${QUERY}"

# === Génération de la signature HMAC-SHA256 ===
API_SIGNATURE=$(echo -n "$STRING_TO_SIGN" | openssl dgst -sha256 -hmac "$API_SECRET" | sed 's/^.* //')

# === URL finale ===
# FULL_URL="https://api.weatherlink.com${API_PATH}?${QUERY}&api-signature=${API_SIGNATURE}"
FULL_URL="https://api.weatherlink.com/v2/stations?api-key=${API_KEY}&demo=true"

# === Appel de l'API avec curl ===
echo "Requête vers :"
echo "$FULL_URL"
echo
curl -s "$FULL_URL" | jq .

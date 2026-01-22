#!/bin/bash

# API Key et Secret (v2)
API_KEY="eeiuns90ahloj5mc1sijf68yvt5khlte"
API_SECRET="bbuo9zymvo2m4z5xi4tik6ljqo5twv2c"

# Timestamp actuel en secondes
TIMESTAMP=$(date +%s)

# Chaîne à signer EXACTEMENT
STRING_TO_SIGN="api-key=$API_KEY&t=$TIMESTAMP"

# Calcul de la signature HMAC-SHA256
SIGNATURE=$(echo -n "$STRING_TO_SIGN" | openssl dgst -sha256 -hmac "$API_SECRET" | awk '{print $2}')

# URL finale
URL="https://api.weatherlink.com/v2/stations?api-key=$API_KEY&t=$TIMESTAMP&api-signature=$SIGNATURE"

# Test curl
curl -v "$URL"

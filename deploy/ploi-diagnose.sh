#!/usr/bin/env bash
# Voer uit op de Ploi-server (SSH). Geen sudo nodig.
#   bash deploy/ploi-diagnose.sh
#
set -u

echo "========== 1) PM2: welke Nuxt-apps draaien? =========="
pm2 list 2>/dev/null || true

echo ""
echo "========== 2) PM2 detail =========="
pm2 show dehuurradar-nuxt 2>/dev/null || echo "(geen dehuurradar-nuxt)"

echo ""
echo "========== 3) Poorten 3000–3005 =========="
ss -tlnp 2>/dev/null | grep -E ':300[0-5] ' || true

echo ""
echo "========== 4) Nuxt op 127.0.0.1:3000 — footer staat ONDERAAN de HTML =========="
curl -sI --max-time 5 "http://127.0.0.1:3000/" | head -15 || true
BODY=$(curl -s --max-time 10 "http://127.0.0.1:3000/" || true)
if echo "$BODY" | grep -q 'Build'; then
  echo "$BODY" | grep -oE 'Build[^<]{0,120}' | head -3
else
  echo "(geen 'Build' in HTML — check of buildId in nuxt.config staat, of pagina is leeg)"
fi
if echo "$BODY" | grep -q 'De Huur Radar'; then
  echo "OK: merknaam 'De Huur Radar' gevonden in HTML."
else
  echo "Let op: 'De Huur Radar' niet gevonden (oude tekst of andere fout)."
fi

echo ""
echo "========== 5) Nginx (zonder sudo) =========="
FOUND=0
for f in /etc/nginx/sites-enabled/*; do
  [[ -r "$f" ]] || continue
  if grep -qE 'humble|dehuur|ploi\.website' "$f" 2>/dev/null; then
    echo "--- $f ---"
    grep -E 'server_name|proxy_pass|listen' "$f" 2>/dev/null | head -40
    FOUND=1
  fi
done
if [[ "$FOUND" -eq 0 ]]; then
  echo "Geen leesbare site-config in /etc/nginx/sites-enabled/ (rechten)."
  echo "→ In Ploi: elke site → NGINX → zoek per domein 'proxy_pass http://127.0.0.1:3000'"
  echo "→ humble-shore en dehuurradar moeten naar hetzelfde poort wijzen als PM2."
fi

echo ""
echo "========== 6) Git (frontend) =========="
if [[ -d /home/ploi/dehuurradar.nl/frontend/.git ]]; then
  (cd /home/ploi/dehuurradar.nl/frontend && git log -1 --oneline && git status -sb)
else
  echo "Geen .git in frontend (deploy zonder git?)."
fi

echo ""
echo "Klaar."

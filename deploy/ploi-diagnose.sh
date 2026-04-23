#!/usr/bin/env bash
# Run on the Ploi server (SSH). No sudo required.
#   bash deploy/ploi-diagnose.sh
#
set -u

echo "========== 1) PM2: which Nuxt apps are running? =========="
pm2 list 2>/dev/null || true

echo ""
echo "========== 2) PM2 detail =========="
pm2 show dehuurradar-nuxt 2>/dev/null || echo "(no dehuurradar-nuxt)"

echo ""
echo "========== 3) Ports 3000–3005 =========="
ss -tlnp 2>/dev/null | grep -E ':300[0-5] ' || true

echo ""
echo "========== 4) Nuxt on 127.0.0.1:3000 — footer is at the BOTTOM of the HTML =========="
curl -sI --max-time 5 "http://127.0.0.1:3000/" | head -15 || true
BODY=$(curl -s --max-time 10 "http://127.0.0.1:3000/" || true)
if echo "$BODY" | grep -qE 'name="x-wsc-build"|name=.x-wsc-build'; then
  echo "OK: meta x-wsc-build found:"
  echo "$BODY" | grep -oE '<meta[^>]*x-wsc-build[^>]*>' | head -1
elif echo "$BODY" | grep -q 'Build'; then
  echo "$BODY" | grep -oE 'Build[^<]{0,120}' | head -3
else
  echo "(no x-wsc-build / Build in HTML — old deploy or empty response; rebuild + pm2 restart)"
fi
if echo "$BODY" | grep -q 'De Huur Radar'; then
  echo "OK: brand name 'De Huur Radar' found in HTML."
else
  echo "Note: 'De Huur Radar' not found (old bundle, or i18n lazy/SSR — test again after rebuild)."
fi

echo ""
echo "========== 5) Nginx (no sudo) =========="
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
  echo "No readable site config in /etc/nginx/sites-enabled/ (permissions)."
  echo "→ In Ploi: each site → NGINX → find 'proxy_pass http://127.0.0.1:3000' per domain"
  echo "→ humble-shore and dehuurradar should point to the same port as PM2."
fi

echo ""
echo "========== 6) Git (frontend) =========="
if [[ -d /home/ploi/dehuurradar.nl/frontend/.git ]]; then
  (cd /home/ploi/dehuurradar.nl/frontend && git log -1 --oneline && git status -sb)
else
  echo "No .git in frontend (deploy without git?)."
fi

echo ""
echo "Done."

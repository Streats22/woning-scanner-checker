#!/usr/bin/env bash
# Voer uit op de Ploi-server (SSH) om te zien waarom een domein een andere versie toont.
#   bash deploy/ploi-diagnose.sh
#
set -euo pipefail

echo "========== 1) PM2: welke Nuxt-apps draaien? =========="
pm2 list 2>/dev/null || true

echo ""
echo "========== 2) PM2 detail (pas naam aan indien nodig) =========="
pm2 show dehuurradar-nuxt 2>/dev/null || echo "(geen dehuurradar-nuxt)"

echo ""
echo "========== 3) Wat luistert op poorten 3000–3005? =========="
ss -tlnp 2>/dev/null | grep -E ':300[0-5] ' || true

echo ""
echo "========== 4) Direct Nuxt op 3000 (headers + Build in HTML) =========="
curl -sI "http://127.0.0.1:3000/" | head -20 || true
echo "--- grep Build in body (eerste 64k) ---"
curl -s --max-time 5 "http://127.0.0.1:3000/" | head -c 65536 | grep -oE 'Build · [^<]{8,80}' | head -3 || echo "(geen 'Build ·' in HTML — oude bundle of andere fout)"

echo ""
echo "========== 5) Nginx: server_name en proxy_pass (zoek humble + dehuur) =========="
if [[ -d /etc/nginx/sites-enabled ]]; then
  sudo grep -rE 'server_name|proxy_pass' /etc/nginx/sites-enabled/ 2>/dev/null | grep -E 'humble|dehuur|ploi\.website' || sudo grep -rE 'server_name|proxy_pass' /etc/nginx/sites-enabled/ 2>/dev/null | head -60
else
  echo "Geen /etc/nginx/sites-enabled"
fi

echo ""
echo "========== 6) Git laatste commit (frontend) =========="
if [[ -d /home/ploi/dehuurradar.nl/frontend/.git ]]; then
  (cd /home/ploi/dehuurradar.nl/frontend && git log -1 --oneline && git status -sb)
else
  echo "Geen git in /home/ploi/dehuurradar.nl/frontend"
fi

echo ""
echo "Klaar. Vergelijk: humble-site moet naar dezelfde proxy_pass wijzen als de site die je net hebt gebouwd."
echo "Als humble naar een ANDER poort/proces wijst: daar ook builden + pm2 restart, of nginx aanpassen."

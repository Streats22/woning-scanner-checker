#!/usr/bin/env bash
# Herbouwt Nuxt op de Ploi-server en herstart PM2 — los dit uit op de VPS (SSH).
#
# 1) Echte site-paden:  ls /home/ploi/
#     Niet elke test-URL heeft een map "humble-shore-…"; namen verschillen per server.
#
# 2) Vaak staat de frontend alleen onder de hoofdsite, bv.:
#      /home/ploi/dehuurradar.nl/frontend
#    Test-URL (humble-shore-….ploi.website) kan naar dezelfde Nginx → zelfde poort →
#    dezelfde PM2-app wijzen. Dan is één build + één restart genoeg.
#
# 3) pm2: gebruik de exacte naam uit `pm2 list`, zonder < > haakjes.
#    FOUT:  pm2 restart <dehuurradar-nuxt>
#    GOED:  pm2 restart dehuurradar-nuxt
#
# Voorbeeld (jouw pad uit de build-log):
#   export SITE_ROOT=/home/ploi/dehuurradar.nl
#   export PM2_NAME=dehuurradar-nuxt
#   bash deploy/ploi-rebuild-frontend.sh
#
set -euo pipefail

SITE_ROOT="${SITE_ROOT:?Zet SITE_ROOT, bv. export SITE_ROOT=/home/ploi/dehuurradar.nl}"
FRONTEND="${SITE_ROOT}/frontend"

if [[ ! -d "$FRONTEND" ]]; then
  echo "Geen map: $FRONTEND — controleer SITE_ROOT (Ploi → site → pad)." >&2
  exit 1
fi

cd "$FRONTEND"

if [[ ! -f .env ]] || ! grep -qE '^[[:space:]]*NUXT_PUBLIC_GOOGLE_ANALYTICS_ID=G-' .env 2>/dev/null; then
  echo "==> WAARSCHUWING: in frontend/.env ontbreekt NUXT_PUBLIC_GOOGLE_ANALYTICS_ID=G-… (of .env bestaat niet)." >&2
  echo "    Ploi/Laravel-site-env gaat naar PHP, niet naar Node — zet GA hier, niet alleen in Laravel." >&2
  echo "    Zonder deze regel bevat npm run build geen measurement-ID." >&2
fi

echo "==> $(pwd)"
echo "==> git pull…"
git pull --ff-only || true

echo "==> npm ci…"
npm ci

echo "==> npm run build…"
npm run build

if [[ -n "${PM2_NAME:-}" ]]; then
  echo "==> pm2 restart $PM2_NAME --update-env (laadt .env / ecosystem env opnieuw)"
  pm2 restart "$PM2_NAME" --update-env
else
  echo "==> Geen PM2_NAME gezet — herstart handmatig, bv.:"
  echo "    pm2 restart dehuurradar-nuxt"
  echo "    (naam uit: pm2 list — geen < > gebruiken in bash)"
fi

echo "==> Klaar. Controleer footer 'Build · …' op de site en vergelijk met lokaal."
echo "==> Cloudflare: eventueel Purge Cache voor die hostname."

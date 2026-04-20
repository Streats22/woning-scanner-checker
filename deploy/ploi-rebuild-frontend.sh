#!/usr/bin/env bash
# Herbouwt Nuxt op de Ploi-server en herstart PM2 — los dit uit op de VPS (SSH).
#
# Gebruik (pas SITE_ROOT aan naar wat Ploi toont bij de site):
#   SITE_ROOT=/home/ploi/humble-shore-hib6wzuau5.ploi.website bash deploy/ploi-rebuild-frontend.sh
#
# Of voor dehuurradar.nl:
#   SITE_ROOT=/home/ploi/dehuurradar.nl bash deploy/ploi-rebuild-frontend.sh
#
# Optioneel:
#   PM2_NAME=dehuurradar-nuxt   # naam zoals in `pm2 list`
#
set -euo pipefail

SITE_ROOT="${SITE_ROOT:?Zet SITE_ROOT, bv. export SITE_ROOT=/home/ploi/humble-shore-hib6wzuau5.ploi.website}"
FRONTEND="${SITE_ROOT}/frontend"

if [[ ! -d "$FRONTEND" ]]; then
  echo "Geen map: $FRONTEND — controleer SITE_ROOT (Ploi → site → pad)." >&2
  exit 1
fi

cd "$FRONTEND"

echo "==> $(pwd)"
echo "==> git pull…"
git pull --ff-only || true

echo "==> npm ci…"
npm ci

echo "==> npm run build…"
npm run build

if [[ -n "${PM2_NAME:-}" ]]; then
  echo "==> pm2 restart $PM2_NAME"
  pm2 restart "$PM2_NAME"
else
  echo "==> Geen PM2_NAME gezet — start of herstart handmatig, bv.:"
  echo "    pm2 restart <naam>   # zie: pm2 list"
  echo "    of: pm2 start /pad/naar/deploy/ecosystem.config.cjs"
fi

echo "==> Klaar. Controleer footer 'Build · …' op de site en vergelijk met lokaal."
echo "==> Cloudflare: eventueel Purge Cache voor die hostname."

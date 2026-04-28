#!/usr/bin/env bash
# Rebuild Nuxt on the Ploi server and restart PM2 — run this on the VPS (SSH).
#
# 1) Real site paths:  ls /home/ploi/
#     Not every test URL has a folder "humble-shore-…"; names differ per server.
#
# 2) Often the frontend lives only under the main site, e.g.:
#      /home/ploi/dehuurradar.nl/frontend
#    A test URL (humble-shore-….ploi.website) may hit the same Nginx → same port →
#    the same PM2 app. Then one build + one restart is enough.
#
# 3) pm2: use the exact name from `pm2 list`, without angle brackets.
#    WRONG:  pm2 restart <dehuurradar-nuxt>
#    RIGHT:  pm2 restart dehuurradar-nuxt
#
# Example (your path from the build log):
#   export SITE_ROOT=/home/ploi/dehuurradar.nl
#   export PM2_NAME=dehuurradar-nuxt
#   bash deploy/ploi-rebuild-frontend.sh
#
set -euo pipefail

SITE_ROOT="${SITE_ROOT:?Set SITE_ROOT, e.g. export SITE_ROOT=/home/ploi/dehuurradar.nl}"
FRONTEND="${SITE_ROOT}/frontend"

if [[ ! -d "$FRONTEND" ]]; then
  echo "No directory: $FRONTEND — check SITE_ROOT (Ploi → site → path)." >&2
  exit 1
fi

cd "$FRONTEND"

if [[ ! -f .env ]] || ! grep -qE '^[[:space:]]*NUXT_PUBLIC_GOOGLE_ANALYTICS_ID=G-' .env 2>/dev/null; then
  echo "==> WARNING: frontend/.env is missing NUXT_PUBLIC_GOOGLE_ANALYTICS_ID=G-… (or .env is missing)." >&2
  echo "    Ploi/Laravel site env is for PHP, not Node — set GA here, not only in Laravel." >&2
  echo "    Without this line, npm run build will not include a measurement ID." >&2
fi

echo "==> $(pwd)"
echo "==> git pull…"
git pull --ff-only || true

echo "==> npm ci…"
npm ci

# Moet: PM2 draaide al gestopt, of Node houdt oude .output open. Nieuwe build = nieuwe chunk-hashes
# (bijv. check-xxxxx.mjs). Mix van oude server.mjs + nieuwe/ontbrekende chunks → ERR_MODULE_NOT_FOUND.
echo "==> Remove previous .output (voorkomt missing check-*.mjs / server.mjs import errors)…"
rm -rf .output

echo "==> npm run build…"
npm run build

if [[ -n "${PM2_NAME:-}" ]]; then
  echo "==> pm2 restart $PM2_NAME --update-env (reloads .env / ecosystem env)"
  pm2 restart "$PM2_NAME" --update-env
else
  echo "==> PM2_NAME not set — restart manually, e.g.:"
  echo "    pm2 restart dehuurradar-nuxt"
  echo "    (name from: pm2 list — do not use < > in bash)"
fi

echo "==> Done. Check the site footer 'Build · …' and compare with local."

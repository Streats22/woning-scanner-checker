#!/usr/bin/env bash
# =============================================================================
# Full stack deploy: Laravel + Nuxt (PM2) — Ploi/VPS
#
# Example (SSH on the server):
#   export SITE_ROOT=/home/ploi/dehuurradar.nl
#   export PM2_NAME=dehuurradar-nuxt
#   export GIT_BRANCH=master
#   bash deploy/deploy-full.sh
#
# Site root:
#   SITE_ROOT  — optional; path to the Laravel repo. If unset, uses $PWD (Ploi runs
#   deploy from the site root) or, if that has no artisan/, the parent of this script’s directory.
# Optional:
#   PM2_NAME           — app name in pm2 (default: dehuurradar-nuxt)
#   GIT_BRANCH         — default: master
#   PM2_ECOSYSTEM      — default: $SITE_ROOT/deploy/ecosystem.config.cjs
#   NUXT_PORT          — health check port (default: 3000)
#   SKIP_GIT_PULL      — set to 1 to skip git pull
#   SKIP_COMPOSER      — set to 1 to skip composer
#   SKIP_MIGRATIONS    — set to 1 to skip migrations
#   FLUSH_FASTCGI_CMD  — optional, e.g. "sudo ploi fpm-cache" or empty
#   RELOAD_FPM_CMD     — optional, e.g. "sudo systemctl reload php8.3-fpm"
#   NUXT_PREBUILD_CLEAN — 1: rm -rf (same user) .output and node_modules/.cache before Nuxt; default: 0
#                        (script wist .output altijd vóór build; zie comment bij die stap)
#
#   node_modules must be owned by the Ploi / deploy user. If you ever ran npm as root, npm ci will
#   fail with EACCES — the script will stop early with a one-time fix to run over SSH (no sudo here).
# =============================================================================
set -euo pipefail

# --- config ---
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

# Ploi deploy hooks do not set SITE_ROOT; cwd is usually the site (Laravel) root.
if [[ -z "${SITE_ROOT:-}" ]]; then
  if [[ -f "$(pwd)/artisan" ]]; then
    SITE_ROOT=$(pwd)
  else
    _here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    if [[ -f "$_here/../artisan" ]]; then
      SITE_ROOT="$(cd "$_here/.." && pwd)"
    else
      echo -e "${RED}Error: SITE_ROOT is unset and Laravel root not found (no artisan in \$PWD or next to this script). Export SITE_ROOT=/path/to/site or run this from the repo root.${NC}" >&2
      exit 1
    fi
  fi
fi
FRONTEND="${SITE_ROOT}/frontend"
PM2_NAME="${PM2_NAME:-dehuurradar-nuxt}"
GIT_BRANCH="${GIT_BRANCH:-master}"
PM2_ECOSYSTEM="${PM2_ECOSYSTEM:-${SITE_ROOT}/deploy/ecosystem.config.cjs}"
NUXT_PORT="${NUXT_PORT:-3000}"

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}  Deploy: Laravel + Nuxt (PM2)${NC}"
echo -e "${CYAN}  SITE_ROOT=${SITE_ROOT}${NC}"
echo -e "${GREEN}============================================${NC}"

cd "$SITE_ROOT"

if [[ ! -f "artisan" ]]; then
  echo -e "${RED}Error: no artisan in $SITE_ROOT (not Laravel root?)${NC}" >&2
  exit 1
fi
if [[ ! -d "$FRONTEND" ]]; then
  echo -e "${RED}Error: no frontend/ at $FRONTEND${NC}" >&2
  exit 1
fi

# --- git ---
if [[ "${SKIP_GIT_PULL:-0}" != "1" ]]; then
  echo -e "${YELLOW}→ git fetch & pull ($GIT_BRANCH)…${NC}"
  git fetch origin
  git pull --ff-only "origin" "$GIT_BRANCH"
else
  echo -e "${YELLOW}→ SKIP_GIT_PULL=1 — git skipped${NC}"
fi

# --- PHP dependencies ---
if [[ "${SKIP_COMPOSER:-0}" != "1" ]]; then
  echo -e "${YELLOW}→ composer install…${NC}"
  if command -v composer >/dev/null 2>&1; then
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
  else
    echo -e "${RED}composer not in PATH${NC}" >&2
    exit 1
  fi
else
  echo -e "${YELLOW}→ SKIP_COMPOSER=1 — composer skipped${NC}"
fi

# --- root npm (Laravel Vite: resources/js, css) ---
if [[ -f "$SITE_ROOT/package.json" ]]; then
  echo -e "${YELLOW}→ npm ci (Laravel / Vite root)…${NC}"
  npm ci --no-audit --no-fund
  if grep -q '"build:laravel"' "$SITE_ROOT/package.json" 2>/dev/null; then
    echo -e "${YELLOW}→ npm run build:laravel…${NC}"
    npm run build:laravel
  fi
fi

# --- Nuxt build ---
if [[ ! -f "$FRONTEND/package.json" ]]; then
  echo -e "${RED}Error: missing $FRONTEND/package.json${NC}" >&2
  exit 1
fi

if [[ ! -f "$FRONTEND/.env" ]] || ! grep -qE '^[[:space:]]*NUXT_PUBLIC_GOOGLE_ANALYTICS_ID=G-' "$FRONTEND/.env" 2>/dev/null; then
  echo -e "${YELLOW}==> Warning: frontend/.env is missing NUXT_PUBLIC_GOOGLE_ANALYTICS_ID=G-… (build may run without GA).${NC}" >&2
  echo -e "${YELLOW}    Set NUXT_PUBLIC_GOOGLE_ANALYTICS_ID in frontend/.env on the server.${NC}" >&2
fi

# Stop Nitro first: a running process can keep file handles under .output and make cleanup fail
if command -v pm2 >/dev/null 2>&1; then
  if pm2 describe "$PM2_NAME" >/dev/null 2>&1; then
    echo -e "${YELLOW}→ pm2 stop $PM2_NAME (release old .output)…${NC}"
    pm2 stop "$PM2_NAME" || true
  fi
fi

cd "$FRONTEND"

# npm ci must be able to unlink every file in node_modules; fail fast with fix steps (Ploi cannot use interactive sudo)
if [[ -d node_modules ]]; then
  if [[ ! -O node_modules ]]; then
    echo -e "${RED}Error: node_modules is not owned by $(id -un) (EACCES on npm ci).${NC}" >&2
    echo "  One-time over SSH, then redeploy:" >&2
    echo "    cd $PWD" >&2
    echo "    sudo chown -R $(id -un):$(id -gn) ." >&2
    echo "    rm -rf node_modules" >&2
    echo "  Then let deploy run npm ci; always use the site user for npm, never root." >&2
    exit 1
  fi
  if [[ -d node_modules/.bin ]]; then
    if ! [[ -O node_modules/.bin ]]; then
      echo -e "${RED}Error: node_modules/.bin directory is not owned by $(id -un).${NC}" >&2
      echo "  One-time over SSH, then redeploy:" >&2
      echo "    cd $PWD && sudo chown -R $(id -un):$(id -gn) . && rm -rf node_modules" >&2
      exit 1
    fi
    # Only scan *inside* .bin; do not use find on the .bin path alone (it lists that dir and looked like a false positive)
    _fix=$(find node_modules/.bin -mindepth 1 -maxdepth 1 ! -user "$(id -u)" 2>/dev/null | head -1)
    if [[ -n "${_fix}" ]]; then
      echo -e "${RED}Error: not owned by $(id -un) under node_modules/.bin: $_fix${NC}" >&2
      echo "  One-time over SSH, then redeploy:" >&2
      echo "    cd $PWD && sudo chown -R $(id -un):$(id -gn) . && rm -rf node_modules" >&2
      exit 1
    fi
  fi
fi

shopt -s nullglob
_olds=(node_modules/.cache.stale* .deploy-stale-*)
if (( ${#_olds[@]} )); then
  echo -e "${YELLOW}==> Old deploy leftovers: ${_olds[*]}. If npm EACCES, remove them over SSH (see README above).${NC}" >&2
fi
shopt -u nullglob

if [[ "${NUXT_PREBUILD_CLEAN:-0}" == "1" ]]; then
  echo -e "${YELLOW}→ Nuxt: NUXT_PREBUILD_CLEAN=1 — rm .output, caches, stale paths (best-effort, no sudo)…${NC}"
  shopt -s nullglob
  rm -rf .output node_modules/.cache node_modules/.cache.stale* .deploy-stale-* 2>/dev/null || true
  shopt -u nullglob
fi

echo -e "${YELLOW}→ Nuxt: npm ci + build…${NC}"
npm ci --prefer-offline --no-audit --no-fund

# Altijd: oude .output weg vóór build. Anders kan server.mjs een nieuwe import-hash hebben terwijl
# een eerdere check-*.mjs ontbreekt (of omgekeerd) → "Cannot find module check-…mjs" op /check e.d.
if [[ -d .output ]]; then
  echo -e "${YELLOW}→ Nuxt: rm -rf .output (schone build, geen gemixte chunk-hashes)…${NC}"
  rm -rf .output
fi

NODE_ENV=production npm run build

if [[ ! -d ".output" ]]; then
  echo -e "${RED}Error: Nuxt build failed — .output missing${NC}" >&2
  exit 1
fi

cd "$SITE_ROOT"

# --- Laravel: migrate + cache ---
if [[ "${SKIP_MIGRATIONS:-0}" != "1" ]]; then
  echo -e "${YELLOW}→ php artisan migrate --force…${NC}"
  php artisan migrate --force
else
  echo -e "${YELLOW}→ SKIP_MIGRATIONS=1 — migrations skipped${NC}"
fi

echo -e "${YELLOW}→ php artisan (config, event, route, view) cache…${NC}"
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache

echo -e "${YELLOW}→ queue:restart…${NC}"
php artisan queue:restart || true

echo -e "${YELLOW}→ storage:link…${NC}"
rm -rf ./public/storage
php artisan storage:link

# --- optional server hooks (Ploi) ---
if [[ -n "${FLUSH_FASTCGI_CMD:-}" ]]; then
  echo -e "${YELLOW}→ FLUSH_FASTCGI_CMD…${NC}"
  eval "$FLUSH_FASTCGI_CMD" || true
fi
if [[ -n "${RELOAD_FPM_CMD:-}" ]]; then
  echo -e "${YELLOW}→ RELOAD_FPM_CMD…${NC}"
  eval "$RELOAD_FPM_CMD" || true
fi

# --- PM2: Nuxt Nitro (ecosystem from repo) ---
if [[ ! -f "$PM2_ECOSYSTEM" ]]; then
  echo -e "${RED}Error: PM2 ecosystem not found: $PM2_ECOSYSTEM${NC}" >&2
  echo "  Set PM2_ECOSYSTEM or add deploy/ecosystem.config.cjs to the repo." >&2
  exit 1
fi

mkdir -p "$FRONTEND/logs"
echo -e "${YELLOW}→ PM2 ($PM2_NAME) via $PM2_ECOSYSTEM…${NC}"

if pm2 describe "$PM2_NAME" >/dev/null 2>&1; then
  pm2 reload "$PM2_ECOSYSTEM" --only "$PM2_NAME" --update-env
else
  echo -e "${YELLOW}→ First start: pm2 start…${NC}"
  pm2 start "$PM2_ECOSYSTEM" --only "$PM2_NAME"
fi
pm2 save

# --- health (local Nitro) ---
echo -e "${YELLOW}→ Health check http://127.0.0.1:${NUXT_PORT}…${NC}"
sleep 2
code=$(curl -s -o /dev/null -w "%{http_code}" "http://127.0.0.1:${NUXT_PORT}/" || echo "000")
if [[ "$code" == "200" ]] || [[ "$code" == "301" ]] || [[ "$code" == "302" ]]; then
  echo -e "${GREEN}✓ Nuxt OK (HTTP $code)${NC}"
else
  echo -e "${YELLOW}⚠ Unexpected HTTP $code — check PM2 logs: pm2 logs $PM2_NAME${NC}"
fi

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}  Done — $(date -u '+%Y-%m-%dT%H:%M:%SZ')${NC}"
echo -e "${GREEN}============================================${NC}"
pm2 show "$PM2_NAME" --no-color 2>/dev/null | head -22 || true

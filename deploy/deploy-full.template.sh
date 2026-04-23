#!/usr/bin/env bash
# =============================================================================
# Generic: Laravel (root) + Nuxt in a subfolder + PM2 — Ploi, Forge, or plain VPS
# Copy to your repo (e.g. deploy/deploy-full.sh) and set env vars for your project.
#
# One-time (SSH), adjust for your user/host:
#   export SITE_ROOT=/home/ploi/yourdomain.com
#   export PM2_NAME=nuxt-app
#   export PM2_ECOSYSTEM="$SITE_ROOT/deploy/ecosystem.config.cjs"   # optional
#   bash deploy/deploy-full.sh
#
# Ploi: deploy runs in the site directory — SITE_ROOT usually auto-detects from $PWD.
# Set in the Ploi UI "Environment" or at the top of the deploy script:
#   export PM2_NAME=your-pm2-app-name
#
# Site root
#   SITE_ROOT     — optional; if unset, uses $PWD if artisan is here, else parent of this script
#   NUXT_SUBDIR   — optional; Nuxt app folder under SITE_ROOT (default: frontend)
#
# Required for PM2
#   PM2_NAME      — must match the app name in ecosystem.config.cjs
#
# Optional
#   GIT_BRANCH         — default: main
#   PM2_ECOSYSTEM      — default: $SITE_ROOT/deploy/ecosystem.config.cjs
#   NUXT_PORT          — health check (default: 3000)
#   SKIP_GIT_PULL      — 1 to skip
#   SKIP_COMPOSER      — 1 to skip
#   SKIP_MIGRATIONS    — 1 to skip
#   FLUSH_FASTCGI_CMD  — e.g. sudo ploi fpm-cache
#   RELOAD_FPM_CMD     — e.g. sudo systemctl reload php8.3-fpm
#   WARN_ANALYTICS_ID  — if set to 1, warn when NUXT_PUBLIC_GOOGLE_ANALYTICS_ID=G- is missing
#   NUXT_PREBUILD_CLEAN — 1: optional same-user rm before Nuxt; default: 0
#   node_modules must be owned by the deploy user; if not, fix over SSH (script exits with instructions)
# =============================================================================
set -euo pipefail

# --- config ---
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

if [[ -z "${SITE_ROOT:-}" ]]; then
  if [[ -f "$(pwd)/artisan" ]]; then
    SITE_ROOT=$(pwd)
  else
    _here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    if [[ -f "$_here/../artisan" ]]; then
      SITE_ROOT="$(cd "$_here/.." && pwd)"
    else
      echo -e "${RED}Error: SITE_ROOT is unset and Laravel root not found. Set SITE_ROOT or run from the repo root.${NC}" >&2
      exit 1
    fi
  fi
fi

NUXT_SUBDIR="${NUXT_SUBDIR:-frontend}"
FRONTEND="${SITE_ROOT}/${NUXT_SUBDIR}"

if [[ -z "${PM2_NAME:-}" ]]; then
  echo -e "${RED}Error: PM2_NAME is not set. It must match the app name in your PM2 ecosystem file, e.g. export PM2_NAME=nuxt-app${NC}" >&2
  exit 1
fi

GIT_BRANCH="${GIT_BRANCH:-main}"
PM2_ECOSYSTEM="${PM2_ECOSYSTEM:-${SITE_ROOT}/deploy/ecosystem.config.cjs}"
NUXT_PORT="${NUXT_PORT:-3000}"

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}  Deploy: Laravel + Nuxt (PM2)${NC}"
echo -e "${CYAN}  SITE_ROOT=${SITE_ROOT}${NC}"
echo -e "${CYAN}  NUXT_SUBDIR=${NUXT_SUBDIR}  PM2_NAME=${PM2_NAME}${NC}"
echo -e "${GREEN}============================================${NC}"

cd "$SITE_ROOT"

if [[ ! -f "artisan" ]]; then
  echo -e "${RED}Error: no artisan in $SITE_ROOT (not Laravel root?)${NC}" >&2
  exit 1
fi
if [[ ! -d "$FRONTEND" ]]; then
  echo -e "${RED}Error: no Nuxt directory at $FRONTEND (set NUXT_SUBDIR if your app is not in frontend/)${NC}" >&2
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

# --- root npm (Laravel / Vite) ---
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

if [[ "${WARN_ANALYTICS_ID:-0}" == "1" ]]; then
  if [[ ! -f "$FRONTEND/.env" ]] || ! grep -qE '^[[:space:]]*NUXT_PUBLIC_GOOGLE_ANALYTICS_ID=G-' "$FRONTEND/.env" 2>/dev/null; then
    echo -e "${YELLOW}==> Warning: set NUXT_PUBLIC_GOOGLE_ANALYTICS_ID in $FRONTEND/.env on the server if you use GA.${NC}" >&2
  fi
fi

if command -v pm2 >/dev/null 2>&1; then
  if pm2 describe "$PM2_NAME" >/dev/null 2>&1; then
    echo -e "${YELLOW}→ pm2 stop $PM2_NAME (release old .output)…${NC}"
    pm2 stop "$PM2_NAME" || true
  fi
fi

cd "$FRONTEND"

if [[ -d node_modules ]]; then
  if [[ ! -O node_modules ]]; then
    echo -e "${RED}Error: node_modules not owned by $(id -un).${NC}" >&2
    echo "  Over SSH once: cd $PWD && sudo chown -R $(id -un):$(id -gn) . && rm -rf node_modules" >&2
    exit 1
  fi
  if [[ -d node_modules/.bin ]]; then
    if ! [[ -O node_modules/.bin ]]; then
      echo -e "${RED}Error: node_modules/.bin is not owned by $(id -un).${NC}" >&2
      echo "  Over SSH: cd $PWD && sudo chown -R $(id -un):$(id -gn) . && rm -rf node_modules" >&2
      exit 1
    fi
    _fix=$(find node_modules/.bin -mindepth 1 -maxdepth 1 ! -user "$(id -u)" 2>/dev/null | head -1)
    if [[ -n "${_fix}" ]]; then
      echo -e "${RED}Error: under node_modules/.bin, not owned by $(id -un): $_fix${NC}" >&2
      echo "  Over SSH: cd $PWD && sudo chown -R $(id -un):$(id -gn) . && rm -rf node_modules" >&2
      exit 1
    fi
  fi
fi

shopt -s nullglob
_olds=(node_modules/.cache.stale* .deploy-stale-*)
if (( ${#_olds[@]} )); then
  echo -e "${YELLOW}==> Old deploy leftovers: ${_olds[*]}. If npm EACCES, clean over SSH.${NC}" >&2
fi
shopt -u nullglob

if [[ "${NUXT_PREBUILD_CLEAN:-0}" == "1" ]]; then
  echo -e "${YELLOW}→ Nuxt: NUXT_PREBUILD_CLEAN=1 — rm .output, caches, stale (best-effort, no sudo)…${NC}"
  shopt -s nullglob
  rm -rf .output node_modules/.cache node_modules/.cache.stale* .deploy-stale-* 2>/dev/null || true
  shopt -u nullglob
fi

echo -e "${YELLOW}→ Nuxt: npm ci + build…${NC}"
npm ci --prefer-offline --no-audit --no-fund
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

# --- optional server hooks ---
if [[ -n "${FLUSH_FASTCGI_CMD:-}" ]]; then
  echo -e "${YELLOW}→ FLUSH_FASTCGI_CMD…${NC}"
  eval "$FLUSH_FASTCGI_CMD" || true
fi
if [[ -n "${RELOAD_FPM_CMD:-}" ]]; then
  echo -e "${YELLOW}→ RELOAD_FPM_CMD…${NC}"
  eval "$RELOAD_FPM_CMD" || true
fi

# --- PM2 ---
if [[ ! -f "$PM2_ECOSYSTEM" ]]; then
  echo -e "${RED}Error: PM2 ecosystem not found: $PM2_ECOSYSTEM${NC}" >&2
  echo "  Set PM2_ECOSYSTEM to your ecosystem file path." >&2
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

# --- health ---
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

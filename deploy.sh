#!/usr/bin/env bash
# Run this on the Oracle Cloud (or any Linux) server after cloning the repo.
# Prerequisites: Docker + Docker Compose installed, .env present.
set -euo pipefail

cd "$(dirname "$0")"

echo "==> Building and starting containers..."
docker compose up -d --build

echo "==> Waiting for app to come up..."
sleep 5

echo "==> Installing Telegram webhook..."
SECRET="$(grep '^TELEGRAM_WEBHOOK_SECRET=' .env | cut -d= -f2-)"
BASE_URL="$(grep '^APP_URL=' .env | cut -d= -f2-)"

docker compose exec -T app php artisan nutgram:hook:set "${BASE_URL%/}/tg/webhook/${SECRET}"

echo "==> Done. Webhook URL: ${BASE_URL%/}/tg/webhook/${SECRET}"
docker compose ps

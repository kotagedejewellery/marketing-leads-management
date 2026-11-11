#!/usr/bin/env bash
set -euo pipefail

compose=(docker compose --env-file .env.production -f compose.production.yml)

"${compose[@]}" config -q
"${compose[@]}" build app
"${compose[@]}" up -d db

for _ in $(seq 1 30); do
  if "${compose[@]}" exec -T db pg_isready -U marketing_leads -d marketing_leads >/dev/null 2>&1; then
    break
  fi
  sleep 2
done
"${compose[@]}" exec -T db pg_isready -U marketing_leads -d marketing_leads >/dev/null

mkdir -p backups
backup_file="backups/pre-migrate-$(date -u +%Y%m%dT%H%M%SZ).dump"
"${compose[@]}" exec -T db pg_dump -U marketing_leads -Fc marketing_leads > "$backup_file"

"${compose[@]}" run --rm --no-deps migrate
"${compose[@]}" up -d --build --no-deps app

for _ in $(seq 1 30); do
  if "${compose[@]}" exec -T app php -r 'exit(@file_get_contents("http://127.0.0.1/up") === false ? 1 : 0);'; then
    "${compose[@]}" ps
    "${compose[@]}" logs --tail=100 app
    exit 0
  fi
  sleep 2
done

echo "Application health check failed." >&2
exit 1

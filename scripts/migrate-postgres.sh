#!/bin/sh
set -eu

psql -v ON_ERROR_STOP=1 <<'SQL'
CREATE TABLE IF NOT EXISTS app_schema_migrations (
  filename TEXT PRIMARY KEY,
  checksum CHAR(64) NOT NULL,
  applied_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
SQL

find /migrations -maxdepth 1 -type f -name '[0-9][0-9][0-9]_*.sql' ! -name '001_*' -print \
  | LC_ALL=C sort \
  | while IFS= read -r file; do
      filename=${file##*/}
      checksum=$(sha256sum "$file" | awk '{print $1}')
      applied=$(psql -v ON_ERROR_STOP=1 -v filename="$filename" -At <<'SQL'
SELECT checksum FROM app_schema_migrations WHERE filename = :'filename';
SQL
)

      if [ -n "$applied" ]; then
        [ "$applied" = "$checksum" ] || {
          echo "Migration $filename was changed after it was applied." >&2
          exit 1
        }
        continue
      fi

      if grep -Eiq 'DROP[[:space:]]+(DATABASE|SCHEMA|TABLE)|TRUNCATE|DROP[[:space:]]+COLUMN' "$file"; then
        echo "Refusing destructive migration: $filename" >&2
        exit 1
      fi

      echo "Applying $filename"
      {
        printf 'BEGIN;\n\\i %s\n' "$file"
        printf "INSERT INTO app_schema_migrations (filename, checksum) VALUES ('%s', '%s');\n" "$filename" "$checksum"
        printf 'COMMIT;\n'
      } | psql -v ON_ERROR_STOP=1
    done

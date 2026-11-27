#!/usr/bin/env bash
set -euo pipefail

test_dir=$(mktemp -d)
trap 'rm -rf "$test_dir"' EXIT
mkdir -p "$test_dir/bin"
cat > "$test_dir/bin/docker" <<'EOF'
#!/usr/bin/env bash
echo "$*" >> "$TEST_COMMANDS"
EOF
chmod +x "$test_dir/bin/docker"
export TEST_COMMANDS="$test_dir/commands"
export PATH="$test_dir/bin:$PATH"
cd "$test_dir"
bash "$1/scripts/deploy-production.sh"
grep -Fx 'compose --env-file .env.production -f compose.production.yml build app scheduler' "$TEST_COMMANDS"
grep -Fx 'compose --env-file .env.production -f compose.production.yml stop scheduler' "$TEST_COMMANDS"
grep -Fx 'compose --env-file .env.production -f compose.production.yml up -d --no-deps app scheduler' "$TEST_COMMANDS"

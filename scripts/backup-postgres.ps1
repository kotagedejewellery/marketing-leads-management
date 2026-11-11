param(
    [string]$Destination = (Join-Path $PSScriptRoot '..\backups')
)

$timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$target = Join-Path $Destination "kgj-leads-$timestamp.dump"
$container = docker compose ps -q db

if (-not $container) {
    throw 'Container database tidak berjalan. Jalankan docker compose up -d terlebih dahulu.'
}

New-Item -ItemType Directory -Force -Path $Destination | Out-Null
docker compose exec -T db pg_dump -U marketing_leads -Fc -f /tmp/kgj-leads-backup.dump marketing_leads
docker cp "${container}:/tmp/kgj-leads-backup.dump" $target
docker compose exec -T db rm -f /tmp/kgj-leads-backup.dump

Write-Host "Backup dibuat: $target"

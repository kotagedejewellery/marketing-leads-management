# Panduan Deployment KGJ Leads

Dokumen ini ditujukan untuk pengelola teknis. Penggunaan aplikasi sehari-hari dijelaskan di [README.md](../README.md).

## Production VPS 1

- Lokasi aplikasi: `/opt/apps/sistem-management-leads`
- Domain: `https://leadcontrol.kotagedejewellery.tech`
- Reverse proxy: Traefik existing melalui network Docker eksternal `proxy`
- Database: PostgreSQL khusus aplikasi, tidak dipublikasikan ke host

Sebelum deploy pertama, arahkan DNS domain ke VPS 1, clone repository ke lokasi di atas, lalu salin `.env.production.example` menjadi `.env.production`. Isi `APP_KEY` dan `DB_PASSWORD` yang unik; file tersebut tidak boleh masuk Git.

## Auto deploy

Setiap push ke branch `main` menjalankan pemeriksaan CI kemudian deployment melalui GitHub Actions. Konfigurasikan secrets berikut:

- `VPS_HOST`
- `VPS_USER` dengan nilai `Kotagede-Jewellery`
- `VPS_SSH_KEY`
- `VPS_SSH_KNOWN_HOSTS`

Workflow hanya memperbarui project `leadcontrol`, lalu memeriksa endpoint `/up` dan menjalankan audit VPS.

## Migrasi dan backup

Migrasi database aplikasi berada di `database/postgres/` dengan nama berurutan, misalnya `007_add_field.sql`. Saat deploy, file baru dijalankan otomatis satu kali dan dicatat dengan checksum. File migrasi yang sudah pernah diterapkan tidak boleh diubah.

Sebelum runner migrasi berjalan, deploy membuat backup PostgreSQL pada folder `backups/`. Siapkan retensi backup sesuai kapasitas disk VPS.

Runner menolak migrasi yang mengandung `DROP DATABASE`, `DROP SCHEMA`, `DROP TABLE`, `DROP COLUMN`, atau `TRUNCATE`.

## Deploy manual

```bash
cd /opt/apps/sistem-management-leads
bash scripts/deploy-production.sh
```

Gunakan `compose.production.yml` untuk production. File `docker-compose.yml` hanya untuk development lokal.

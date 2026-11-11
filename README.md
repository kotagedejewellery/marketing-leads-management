# KGJ Leads Dashboard

Dashboard internal untuk Admin dan CS, dengan Laravel, PostgreSQL, dan Docker Compose.

Struktur utama: kode Laravel berada langsung di root; skrip PostgreSQL berada di `database/postgres/`.

```powershell
docker compose up --build
```

Salin `.env.example` menjadi `.env` dan isi `APP_KEY` serta `DB_PASSWORD` yang kuat sebelum menjalankan. Untuk server, set `APP_ENV=production`, `APP_URL=https://domain-anda`, dan `SESSION_SECURE_COOKIE=true`.

Buka `http://localhost:8082/login`.

Instalasi baru tidak membuat akun dengan kata sandi bawaan. Setelah database siap, buat admin pertama secara interaktif:

```powershell
docker compose exec laravel php artisan kgj:bootstrap-admin
```

Admin dapat memantau seluruh cabang, membuat cabang dan akun CS, menetapkan lead ke akun CS, dan mengimpor CSV atau XLSX. Import XLSX otomatis memakai tab `All Leads` bila tersedia. Header sumber harus memuat `Tanggal` atau `No`, `Nama/Nomor`, `Respond Time`, `Nama CS`, `Keterangan`, `Kebutuhan`, `Greeting*`, `Waktu Chat`, `Keterangan Sumber*`, dan `Keterangan Campaign*`. Akun CS hanya melihat serta mengubah lead yang ditugaskan kepadanya.

Database menggunakan volume Docker persisten dan tidak ada perintah reset atau seed destruktif. Sistem legacy tidak lagi terdaftar sebagai service Docker. PostgreSQL dibatasi ke `127.0.0.1:5434`; akseskan aplikasi produksi melalui reverse proxy HTTPS, bukan dengan membuka port database.

Buat backup database sebelum pembaruan besar dan jadwalkan perintah berikut melalui Task Scheduler/server scheduler:

```powershell
.\scripts\backup-postgres.ps1
```

## Production VPS 1

Deployment production berada di `/opt/apps/sistem-management-leads` dengan domain `https://leadcontrol.kotagedejewellery.tech`. Gunakan `compose.production.yml`; file `docker-compose.yml` tetap khusus development lokal.

1. Pastikan DNS domain mengarah ke VPS 1 dan network Docker eksternal `proxy` sudah tersedia dari Traefik existing.
2. Clone repository ke folder tersebut, lalu salin `.env.production.example` menjadi `.env.production`. Isi `APP_KEY` dan `DB_PASSWORD` yang unik; file ini tidak boleh masuk Git.
3. Jalankan `bash scripts/deploy-production.sh`. Skrip memvalidasi compose, membuat backup database, menjalankan migrasi SQL forward-only, menjalankan app, dan memeriksa `/up`.
4. Buat GitHub Actions secrets: `VPS_HOST`, `VPS_USER` (nilai: `Kotagede-Jewellery`), `VPS_SSH_KEY`, dan `VPS_SSH_KNOWN_HOSTS`.

Setiap push ke `main` menjalankan CI lalu deploy otomatis. Deploy hanya menyentuh compose project `leadcontrol`, membuat backup `backups/pre-migrate-*.dump`, dan memeriksa `https://leadcontrol.kotagedejewellery.tech/up`.

Migrasi production aplikasi ini adalah file SQL bernomor di `database/postgres/`, misalnya `007_add_field.sql`. File baru dijalankan otomatis sekali dan dicatat bersama checksum-nya. Jangan mengubah file yang sudah pernah dipakai, dan jangan gunakan `DROP DATABASE`, `DROP SCHEMA`, `DROP TABLE`, `DROP COLUMN`, atau `TRUNCATE`; runner akan menolaknya.

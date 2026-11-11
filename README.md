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

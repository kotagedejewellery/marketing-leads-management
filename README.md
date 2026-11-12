# KGJ Leads

KGJ Leads adalah ruang kerja internal untuk membantu tim Customer Service (CS) dan Admin Kotagede Jewellery mengelola lead dengan rapi, cepat, dan mudah dipantau.

Sistem ini menggantikan proses pemantauan lead yang sebelumnya tersebar di spreadsheet harian. Setiap calon pelanggan memiliki satu data utama, status yang jelas, serta riwayat pembaruan yang dapat ditelusuri.

## Tujuan

- Memudahkan CS melihat dan memperbarui lead yang menjadi tanggung jawabnya.
- Memudahkan Admin memantau seluruh cabang, pembagian lead, dan perkembangan follow-up.
- Menyimpan informasi kebutuhan calon pelanggan secara lebih terstruktur.
- Mengurangi risiko data ganda, lead terlewat, atau status yang tidak terbarui.

## Siapa yang menggunakan?

### Admin

Admin dapat melihat seluruh data lead, mengimpor data awal, mengelola cabang dan akun CS, membagikan lead, serta memantau statistik dan antrean follow-up.

### Customer Service

CS melihat lead yang ditugaskan kepadanya serta dapat menambahkan lead baru yang otomatis menjadi tanggung jawabnya. CS dapat memperbarui perkembangan konsultasi, kebutuhan pelanggan, jadwal follow-up, catatan, dan riwayat komunikasi pada lead miliknya.

## Alur kerja harian

1. Admin mengimpor data lead dari spreadsheet atau file CSV/XLSX.
2. Admin mengecek lead yang belum ditugaskan lalu membagikannya kepada CS yang sesuai.
3. CS membuka daftar leadnya, lalu memperbarui status dan catatan setelah berkomunikasi dengan pelanggan.
4. Admin memantau statistik, lead yang perlu ditindaklanjuti, serta kualitas pembagian lead antar cabang.

Lead dapat diarsipkan agar tidak tampil pada daftar aktif dan dapat dipulihkan kembali. Penghapusan permanen hanya tersedia dari arsip karena tindakan ini juga menghapus seluruh riwayat lead. Daftar lead juga menyediakan pilihan semua lead pada halaman untuk aksi massal.

## Status perkembangan lead

| Kelompok | Status yang tersedia                                                               |
| -------- | ---------------------------------------------------------------------------------- |
| Cold     | Klik saja                                                                          |
| Warm     | Tanya-tanya saja, Sampai Pricelist                                                 |
| Hot      | Datang Offline, Diskusi Online, Closing tapi belum Transfer, Closing, Repeat Order |

Setiap lead memiliki satu status perkembangan terbaru. Seluruh perubahan tetap tersimpan dalam riwayat sehingga alur komunikasi dapat ditinjau kembali.

## Informasi yang dicatat

Selain nama dan nomor pelanggan, sistem membantu mencatat:

- kebutuhan pelanggan, seperti tunangan, lamaran, nikahan, anniversary, atau lainnya;
- waktu pemakaian dan kisaran budget;
- catatan konsultasi dan jadwal follow-up;
- sumber dan campaign asal lead;
- status perkembangan serta riwayat pembaruan.

## Import data lead

Admin dapat mengimpor file CSV atau XLSX dari data monitoring lead. Pilih **All Leads** untuk data master, atau **Tab harian** untuk file XLSX penuh yang memiliki tab angka seperti `1` sampai `31`. Pada mode tab harian, sistem hanya membaca tabel lead utama pada area yang sesuai template, memakai tanggal dari header tab, dan melewati data duplikat tanpa menimpa pembaruan CS. Maksimal satu import adalah 5.000 lead total. Nomor Indonesia dengan awalan `08` dan `+62` diperlakukan sebagai kontak yang sama.

## Bantuan

Jika data tidak muncul, status tidak dapat diperbarui, atau akses akun bermasalah, hubungi Admin sistem terlebih dahulu. Admin dapat membantu mengecek cabang, akun CS, dan penugasan lead.

Dokumen khusus untuk pengelola teknis tersedia di [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).

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

Admin dapat melihat seluruh data lead, mengelola cabang dan akun CS, memantau sinkronisasi spreadsheet, serta menangani pengecualian seperti lead tanpa penanggung jawab atau nomor lintas cabang.

### Customer Service

CS melihat lead yang menjadi tanggung jawab utama atau yang ditangani bersama sebagai CS pendamping. CS dapat menambahkan lead baru yang otomatis menjadi tanggung jawabnya, lalu memperbarui perkembangan konsultasi, kebutuhan pelanggan, jadwal follow-up, catatan, dan riwayat komunikasi.

## Alur kerja harian

1. CS mengisi spreadsheet harian cabangnya pada tab tanggal `1` sampai `31`.
2. Sistem membaca spreadsheet setiap dua menit dan menyimpan lead baru ke cabang sumbernya.
3. Lead baru langsung ditugaskan ke CS default impor pada cabang tersebut.
4. CS memperbarui status, kebutuhan, catatan, dan follow-up dari sistem.
5. Bila batas follow-up terlewati tanpa aktivitas lanjutan, sistem memberi penanda pengambilalihan CRM tanpa mengganti CS utama.
6. Admin memantau antrean follow-up, pengambilalihan CRM, kualitas data, konflik nomor lintas cabang, serta sinkronisasi tiap cabang.

Lead dapat diarsipkan agar tidak tampil pada daftar aktif dan dapat dipulihkan kembali. Penghapusan permanen hanya tersedia dari arsip karena tindakan ini juga menghapus seluruh riwayat lead. Daftar lead juga menyediakan pilihan semua lead pada halaman untuk aksi massal, termasuk menambahkan CS pendamping tanpa mengganti CS utama.

## Status perkembangan lead

| Kelompok | Status yang tersedia                                   |
| -------- | ------------------------------------------------------ |
| Cold     | Klik saja                                              |
| Warm     | Tanya-Tanya, Sampai Pricelist                          |
| Hot      | Datang Offline, Diskusi Online, Closing / Repeat Order |

Setiap lead memiliki satu status perkembangan terbaru. Seluruh perubahan tetap tersimpan dalam riwayat sehingga alur komunikasi dapat ditinjau kembali.

## Informasi yang dicatat

Selain nama dan nomor pelanggan, sistem membantu mencatat:

- kebutuhan pelanggan, seperti tunangan, lamaran, nikahan, anniversary, atau lainnya;
- waktu pemakaian dan kisaran budget;
- catatan konsultasi dan jadwal follow-up;
- sumber dan campaign asal lead;
- status perkembangan serta riwayat pembaruan.

## Import data lead

Admin dapat mengimpor file CSV atau XLSX sebagai cadangan atau data historis. Pilih **All Leads** untuk data master, atau **Tab harian** untuk file XLSX penuh dengan tab angka `1` sampai `31`; pada mode ini pilih cabang pemilik data agar lead langsung masuk ke CS cabang tersebut. Sistem melewati duplikat tanpa menimpa pembaruan CS. Maksimal satu import adalah 5.000 lead total. Nomor Indonesia dengan awalan `08` dan `+62` diperlakukan sebagai kontak yang sama.

## Sumber Google Sheets per cabang

Admin mengisi **Google Spreadsheet ID** pada menu Cabang, menambahkan satu atau beberapa akun CS aktif, lalu memilih satu **CS default impor**. Setiap lead baru dari spreadsheet cabang tersebut ditugaskan ke CS default ini; akun CS lain tetap dapat masuk dan ditambahkan sebagai pendamping pada lead bila diperlukan. Spreadsheet dibagikan sebagai Viewer kepada Service Account sistem. Simpan kredensial Service Account sebagai `secrets/google-service-account.json` di server (file ini tidak masuk Git). Tombol **Sinkronkan** pada cabang digunakan saat ingin mengecek satu sumber sekarang juga; sinkronisasi rutin tetap berjalan otomatis.

Sebelum memindahkan akun CS ke cabang lain, pindahkan atau lepaskan dulu seluruh penugasan lead aktifnya. Aturan ini menjaga lead tidak terlihat seolah-olah berpindah cabang hanya karena akun CS dipindahkan.

Jika nomor yang sama muncul pada spreadsheet cabang lain, sistem tidak memindahkan pemiliknya secara otomatis. Lead diberi tanda nomor lintas cabang agar Admin dapat meninjaunya.

## Bantuan

Jika data tidak muncul, status tidak dapat diperbarui, atau akses akun bermasalah, hubungi Admin sistem terlebih dahulu. Admin dapat membantu mengecek cabang, akun CS, dan penugasan lead.

Dokumen khusus untuk pengelola teknis tersedia di [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).

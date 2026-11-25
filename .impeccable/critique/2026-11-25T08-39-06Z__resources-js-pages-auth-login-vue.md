---
target: Login lead control
total_score: 28
max_score: 36
na_heuristics: 10
p0_count: 0
p1_count: 0
target_identity: "file:E:\\AI Engineer\\PT KotaGede Jewellery\\New System\\marketing-leads\\resources\\js\\pages\\auth\\Login.vue"
target_fingerprint: 'sha256:bb739a9212228733e8d820ce6b549fb7d87412992f3b1bf6a0061e165abf8e3c'
target_path: "E:\\AI Engineer\\PT KotaGede Jewellery\\New System\\marketing-leads\\resources\\js\\pages\\auth\\Login.vue"
timestamp: 2026-11-25T08-39-06Z
slug: resources-js-pages-auth-login-vue
---

Method: dual-agent (A: /root/login_design_review · B: /root/login_mechanical_review)

## Design Health Score

| #         | Heuristic                       |     Score | Key issue                                                                       |
| --------- | ------------------------------- | --------: | ------------------------------------------------------------------------------- |
| 1         | Visibility of system status     |         3 | Processing and error feedback exist; visual runtime needs verification.         |
| 2         | Match system / real world       |         4 | Bahasa dan alur kredensial familiar bagi staf.                                  |
| 3         | User control and freedom        |         3 | Password dapat ditampilkan; alur tetap sederhana.                               |
| 4         | Consistency and standards       |         3 | Mengikuti sistem KGJ, namun logo masih bergantung pada filter runtime.          |
| 5         | Error prevention                |         3 | Required, autocomplete, dan label jelas tersedia.                               |
| 6         | Recognition rather than recall  |         3 | Label, ikon, dan state input jelas.                                             |
| 7         | Flexibility and efficiency      |         3 | Mendukung autofill/password manager, tetapi belum ada fokus error yang presisi. |
| 8         | Aesthetic and minimalist design |         4 | Card ringkas, terpusat, dan fokus pada satu tugas.                              |
| 9         | Error recovery                  |         2 | Password-only error masih mengarahkan fokus ke email.                           |
| 10        | Help and documentation          |       n/a | Tidak relevan untuk form autentikasi internal satu-langkah.                     |
| **Total** |                                 | **28/36** | **Good**                                                                        |

## Design Specificity Verdict

Login sudah terasa sebagai KGJ Atelier Console melalui permukaan hangat, graphite action, dan lockup brand. Namun identitas tersebut masih rapuh karena lockup SVG bergantung pada raster eksternal dan filter alpha pada runtime.

Deterministic scan bersih (`[]`) untuk `resources/js/pages/auth/Login.vue`. Browser evidence tidak tersedia karena Agent Browser tidak diekspos pada sesi ini.

## Overall Impression

Struktur mobile baru menyelesaikan gap utama: card kini memakai dynamic viewport dan fokus pada satu tugas. Peluang terbesar berikutnya adalah membuat logo deterministik dan menyelesaikan pemulihan error form.

## What's Working

- `min-h-dvh` dan grid centering menghindari mismatch `100vh` pada mobile browser.
- Form memiliki label, autocomplete, error announcement, dan target utama 44px.
- Hierarki ringkas: brand, tujuan, kredensial, lalu aksi tunggal.

## Priority Issues

- **P2 — Lockup rapuh lintas browser.** SVG memakai external raster dan `feColorMatrix`; hasil HTTP/build tidak membuktikan iOS Safari merendernya sama. Gunakan aset resmi dengan transparansi asli. Suggested command: `$impeccable harden`.
- **P2 — Fokus error tidak presisi.** `onError` selalu memfokuskan email walau password saja yang invalid. Fokuskan field invalid pertama. Suggested command: `$impeccable harden`.
- **P2 — Target tombol tampilkan password belum dijamin 44×44px.** Lebarnya sekitar 40px dari ikon dan padding. Tambahkan `min-w-11`. Suggested command: `$impeccable audit`.
- **P3 — Keyboard/autofocus dan banner error dapat mengubah tinggi viewport.** Overflow fallback benar, tetapi perlu verifikasi iPhone nyata agar tombol tidak tersembunyi. Suggested command: `$impeccable adapt`.

## Persona Red Flags

- **Jordan (first-timer):** saat password salah, fokus kembali ke email dapat membuat penyebab error terasa tidak jelas.
- **Sam (accessibility-dependent):** tombol tampilkan password belum memiliki lebar target sentuh minimum yang terjamin.
- **Casey (distracted mobile user):** autofocus dapat langsung membuka keyboard dan mengubah komposisi card sebelum pengguna bertindak.

## Minor Observations

- Tidak ada P0 atau P1 ditemukan.
- Cognitive load rendah: form menjaga satu tujuan dan tidak memaksa pengguna mengingat informasi dari layar lain.

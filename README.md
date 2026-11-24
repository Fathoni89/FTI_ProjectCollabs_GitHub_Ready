# FTI FEST — Portal Pengumpulan & Showcase Proyek Kolaboratif Semester V

Aplikasi web **PHP 8.1+ dan MySQL/MariaDB**, tanpa Composer, Node.js, atau build step. Cocok untuk shared hosting **cPanel + Apache** yang menyediakan ekstensi PHP `pdo_mysql` dan `fileinfo`.

> **Konteks:** portal ini menyiapkan karya untuk **FTI FEST Januari 2027**, **bukan** sebagai penjadwalan UAS. Penilaian mata kuliah tetap mengikuti dosen dan CPMK masing-masing. Tanggal pasti dan lokasi kegiatan belum ditetapkan dalam aplikasi; administrator dapat mengisinya nanti.

## 1. Fitur

- **Publik:** halaman galeri responsif, pencarian judul/kelompok, filter kategori, halaman detail, tautan demo/Figma/repository/video, poster, dan kartu cetak dengan **QR**.
- **Mahasiswa:** registrasi menggunakan kode angkatan; login; kelola draf, anggota dan kontribusi, kategori proyek, ringkasan, masalah, solusi, fitur MVP, unggah poster/cover/PDF, tautan karya; kirim review; revisi setelah ada catatan dosen.
- **Dosen Reviewer:** lihat karya masuk, periksa artefak internal dan laporan PDF, beri persetujuan atau catatan revisi. Persetujuan portal **bukan** nilai UAS/CPMK.
- **Panitia/HIMA:** kelola format kegiatan (seminar/pameran/keduanya), ruang, sesi, booth, waktu; ekspor CSV rekap. Tidak dapat menilai akademik atau mempublikasikan karya.
- **Administrator:** seluruh akses reviewer dan panitia, publikasi galeri (terpisah dari approval), proyek unggulan, pengaturan acara/semester, buat/nonaktifkan akun, reset kata sandi, buka/tutup pengumpulan.
- **Keamanan:** PDO prepared statements, password hash, CSRF, throttling login berbasis database, file upload MIME/size check dan nama acak, berkas tertutup dari akses HTTP langsung, escaping HTML, audit trail.

## 2. Kebutuhan hosting

| Komponen | Persyaratan |
|---|---|
| PHP | 8.1 atau lebih baru, disarankan 8.2/8.3 |
| Ekstensi PHP | `pdo_mysql`, `fileinfo`, `openssl` (umumnya aktif di cPanel) |
| Database | MySQL 5.7/8+ atau MariaDB 10.4+ |
| Web Server | Apache 2.4+ agar aturan `.htaccess` berlaku |
| Disk | Sesuaikan jumlah proyek dan berkas yang diunggah |
| SSL | HTTPS aktif sangat disarankan; wajib sebelum produksi |
| PHP upload | Disarankan `upload_max_filesize=20M`, `post_max_size=32M`, `max_file_uploads>=20` |

Jika hosting menggunakan Nginx dan tidak menghormati `.htaccess`, **wajib** membuat aturan server untuk menolak akses HTTP ke `app/`, `views/`, `storage/`, `database/`, `docs/`, `config.php`. Lebih aman menggunakan Apache/cPanel sesuai desain distribusi ini.

## 3. Instalasi via cPanel (tanpa terminal)

1. **Buat database MySQL** di cPanel → MySQL Databases. Catat nama DB, pengguna, dan password. **Assign user ke DB dengan hak akses yang diperlukan** (untuk aplikasi ini cukup SELECT, INSERT, UPDATE, DELETE pada tabel yang sudah dibuat; hak CREATE diperlukan hanya ketika import jika melalui user yang sama).
2. **Import** file `database/schema.sql` melalui **phpMyAdmin** setelah memilih database yang dibuat.
3. Buka cPanel → **File Manager** → `public_html`. Buat folder misalnya `fti-fest`, unggah dan ekstrak ZIP aplikasi ke folder tersebut. Pastikan `index.php` berada langsung di `public_html/fti-fest/index.php` (jangan ada nested folder tambahan).
4. Di folder aplikasi salin `config.example.php` menjadi `config.php` (jangan hanya mengganti nama contoh). Isi:
   - `app_url` = `https://domain-kampus.ac.id/fti-fest` (tanpa slash di akhir)
   - `db.host`, `db.port`, `db.name`, `db.user`, `db.pass`
   - `setup_token` = token acak **minimal 32 karakter**, bukan nilai `GANTI_...`
   - `registration_code` = kode khusus yang akan dibagikan hanya ke mahasiswa Semester V (minimal 10 karakter)
5. Jalankan installer sekali di `https://domain-kampus.ac.id/fti-fest/install.php?token=TOKEN_SETUP_YANG_ANDA_SET`. Buat admin pertama dengan password minimal 12 karakter.
6. **Wajib hapus `install.php` dari hosting** segera setelah admin berhasil dibuat.
7. Login dari portal. Di menu **Acara**, set tahun akademik, bulan, tanggal, lokasi; di menu **Pengguna**, buat akun dosen dan panitia HIMA.
8. Bagikan **kode registrasi angkatan** kepada ketua kelompok mahasiswa. Gunakan menu **Jadwal & Booth** dan **Galeri** setelah proyek diverifikasi.
9. Pastikan folder `storage/uploads` dan `storage/logs` dapat ditulis oleh proses PHP. Biasanya hak akses folder `755` atau `775` sesuai pemilik proses PHP cPanel; **jangan set `777` secara permanen**.

**Membuat token acak:** jika ada PHP Terminal, jalankan `php -r 'echo bin2hex(random_bytes(24)), PHP_EOL;'`. Alternatif gunakan generator password terpercaya untuk 48 karakter acak.

## 4. Alur kerja

`Mahasiswa → Simpan draf → Kirim review → Dosen/Admin: Setujui / Minta revisi → Panitia: Tentukan format + booth/jadwal → Admin: Tampilkan di galeri → Cetak kartu QR untuk booth`

**Penting:** mahasiswa yang sudah mengirim tidak dapat mengedit lagi kecuali proyek dikembalikan dalam status **Perlu Revisi**. Setiap aksi penting masuk `audit_logs`.

### Opsi A — Seminar Produk

Panitia memberi `event_format = Seminar Produk`, mengisi **sesi, tanggal/jam, dan lokasi**. Format waktu pelaksanaan mengikuti pedoman: **20 menit per kelompok** (2 menit pembukaan, 3 menit pitch proses, 7 menit demo, 8 menit tanya jawab); waktu pergantian kelompok ditambahkan panitia terpisah.

### Opsi B — Pameran Produk

Panitia memberi `event_format = Pameran Produk`, mengisi **nomor booth, zona/ruang dan waktu**. Setiap kelompok menyediakan poster, perangkat demo, dan **QR ke halaman detail**, dengan **video cadangan** jika perangkat/jaringan bermasalah. Dosen dapat rotasi **10–12 menit/booth**.

Jika satu proyek mengikuti dua format, pilih `Seminar + Pameran`. Dalam dua opsi tersebut, hanya dosen pengampu yang menetapkan nilai masing-masing MK.

## 5. Panduan pengisian dan publikasi

- Draf membutuhkan **judul, kelas, kode kelompok, nama kelompok, kategori**.
- Pengiriman review mensyaratkan **ringkasan, masalah, solusi, pengguna sasaran, fitur inti, minimal 1 anggota, poster, PDF laporan, dan setidaknya satu tautan karya**.
- Berkas laporan PDF **tidak tersedia untuk publik**; hanya pemilik proyek, dosen reviewer, dan administrator yang dapat mengaksesnya saat login. Panitia HIMA tidak dapat mengunduh laporan internal. Foto/cover dan poster hanya dapat diakses publik setelah admin menerbitkan proyek.
- **NIM anggota tidak ditampilkan ke pengunjung publik** (hanya untuk pemilik dan petugas berwenang). Mohon minta persetujuan mahasiswa atas publikasi nama, poster, foto, dan tautan karya.
- Status **Disetujui** tidak otomatis membuat proyek terlihat di publik. Administrator harus mengaktifkan **Tampilkan di Galeri**.
- Ketika lokasi/tanggal belum ditentukan, biarkan kosong; portal akan menampilkan informasi belum diumumkan.
- Template `report_file` hanya menerima PDF, poster/cover hanya JPEG, PNG, atau WEBP. Batas ukuran diatur pada `config.php`.

## 6. Struktur folder

```text
fti_fest_portal/
  index.php                 # Routing publik, akun, submission, administrasi
  download.php              # Mengirim file dengan pengecekan hak akses
  install.php               # HAPUS setelah instalasi
  config.example.php        # Contoh konfigurasi, salin ke config.php
  .htaccess                 # Proteksi root & hardening Apache
  app/                      # DB, autentikasi, utilitas, pengelolaan proyek
  views/                    # Tampilan PHP responsif
  assets/                   # CSS, JS, generator QR lokal
  database/schema.sql       # Struktur MySQL + setting default
  storage/uploads/          # Berkas privat, tidak boleh diakses URL langsung
  storage/logs/             # Cadangan lokasi log jika dibutuhkan
  docs/                     # Panduan operasional
```

## 7. Pemeliharaan dan keamanan produksi

- Aktifkan **HTTPS** dan sertifikat SSL. Jangan pernah membagikan `config.php` atau password DB di Git/public URL.
- Batasi siapa yang menerima kode registrasi angkatan. Ubah kode bila bocor.
- Jadwalkan backup **database + folder `storage/uploads`** (keduanya diperlukan untuk pemulihan).
- Gunakan email kampus untuk admin/reviewer jika tersedia; ubah password awal setelah login.
- Setelah acara selesai, admin dapat menutup pengumpulan di menu **Acara**.
- Hindari mengunggah data sensitif, KTP, dokumen kesehatan, dan repositori privat berisi kredensial.
- Jika pameran bersifat publik, pastikan ada persetujuan atas penggunaan nama dan materi proyek.
- Audit keamanan tambahan (rate limiting tingkat reverse proxy/WAF, monitoring, pemindaian malware, pentest) perlu dilakukan sebelum aplikasi dianggap siap untuk skala besar.

## 8. Daftar uji penerimaan saat sudah dipasang di hosting

1. Login admin berfungsi dan installer sudah dihapus.
2. Mahasiswa mendaftar dengan kode valid; kode salah ditolak.
3. Mahasiswa menyimpan draf dan mengirim poster + PDF + link.
4. Dosen meminta revisi; mahasiswa dapat mengedit kembali dan mengirim ulang.
5. Dosen menyetujui proyek; belum otomatis muncul di galeri.
6. Panitia menetapkan seminar atau booth; informasi tampil pada detail proyek.
7. Admin mempublikasikan proyek; kini muncul di pencarian/galeri publik.
8. Detail publik menampilkan poster, video/repository; PDF laporan internal tetap tertutup.
9. Kartu proyek dicetak sebagai PDF/print dan QR menuju halaman detail.
10. Ekspor CSV terbuka hanya untuk admin/panitia; NIM tidak bocor ke publik.
11. Pengunjung tanpa login tidak dapat masuk ke halaman dosen/admin.
12. Uji unggah file HTML/PHP berganti nama `.jpg` — harus ditolak MIME-check.
13. Backup dan restore database + file diuji di staging.

## 9. Status verifikasi paket ini

Kode PHP telah diperiksa dengan `php -l`, bundle QR diuji memakai Node.js, dan tampilan ditinjau secara visual. **Uji end-to-end pada MySQL belum dilakukan di lingkungan pembuat**, karena runtime ini tidak menyediakan driver `pdo_mysql`/server MySQL. **Lakukan 13 pengujian penerimaan di atas pada hosting staging sebelum membuka akses publik.**

Dokumen pendukung: `docs/PERAN_DAN_ALUR.md` dan `docs/DAFTAR_UJI_HOSTING.md`.

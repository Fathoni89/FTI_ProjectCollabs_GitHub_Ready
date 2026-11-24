# Panduan Deploy untuk PUSDATIN FTI UNSAP

## Konteks
FTI Fest Januari 2027 menjadi kegiatan *showcase* proyek kolaboratif Semester V, terpisah dari proses UAS mata kuliah. Panel pengelolaan tersedia bagi administrator, dosen reviewer, dan panitia HIMA.

## Persyaratan
- Hosting PHP 8.1+ (disarankan 8.2 atau 8.3), Apache 2.4+ dengan `.htaccess` aktif
- Ekstensi PHP `pdo_mysql`, `fileinfo`, dan `openssl`
- MySQL 8+ atau MariaDB 10.4+
- HTTPS wajib untuk produksi
- Direktori `/storage/uploads` dapat ditulis oleh PHP tanpa permission `777`

## Persiapan pertama
1. Siapkan subdomain/virtual host untuk aplikasi, misalnya `ftifest.fti.unsap.ac.id` jika sudah disetujui dan tersedia.
2. Buat database serta pengguna MySQL terpisah. Import `database/schema.sql` via phpMyAdmin.
3. Deploy **isi direktori repository** ke document root aplikasi (`index.php` berada di akar situs/subfolder yang dikonfigurasi).
4. Copy `config.example.php` ke `config.php` di server. Masukkan URL lengkap berbasis `https://`, host/nama database, username dan password MySQL. Buat `setup_token` acak minimal 32 karakter dan `registration_code` khusus mahasiswa.
5. Pastikan `config.php` tidak dapat diakses publik, bukan bagian dari Git, dan izin file ketat.
6. Buka `install.php?token=<setup_token>` hanya saat instalasi awal untuk membuat akun admin, kemudian **hapus `install.php` dari server**. Jangan aktifkan ulang pada server produksi.
7. Login admin, atur informasi FTI Fest, tanggal dan lokasi; buat akun dosen reviewer serta panitia HIMA.

## Kebijakan publikasi
- Persetujuan reviewer **tidak sama** dengan nilai UAS dan **tidak otomatis** membuat proyek ditampilkan publik. Publikasi dilakukan admin.
- Jangan publikasikan NIM, laporan internal, kredensial, atau data pribadi yang tidak diperlukan.
- Pendaftaran mahasiswa dibatasi dengan kode registrasi yang tidak dipublikasikan di GitHub.
- Panitia mengelola booth dan jadwal, bukan memberikan nilai akademik.

## Pengujian sebelum go-live
Jalankan langkah dalam `docs/DAFTAR_UJI_HOSTING.md`. Prioritaskan: registrasi, unggah poster dan PDF, review, revisi, penjadwalan, publikasi, dan uji kontrol akses unduhan laporan. Pastikan akses langsung ke `app/`, `views/`, `database/`, `storage/`, dan `config.php` ditolak. Uji backup & restore DB dan file unggahan pada staging.

## Konfigurasi hosting dan upgrade
- Isi `app_url` sesuai domain dan subfolder; periksa link katalog publik, login, berkas, dan QR setelah migrasi domain.
- Database produksi dan `storage/uploads/` adalah data persisten, **jangan menimpa atau menghapusnya** saat deploy versi baru.
- Sebelum upgrade, backup database dan `storage/uploads/`, lakukan perubahan pada staging dan catat versi rilis.
- Jangan commit `config.php`, berkas unggahan, dump database, file log, atau kunci privat. File `.gitignore` sudah mengecualikannya dari Git.
- `README.md` berisi panduan operasional lengkap; `docs/PERAN_DAN_ALUR.md` menjelaskan kewenangan akun.

## Status pengujian
Paket ini telah melewati pemeriksaan sintaks PHP dan tinjauan antarmuka pada tahap pembuatan sebelumnya. Uji integrasi MySQL dan pengamanan hosting **belum dinyatakan lulus**; PUSDATIN perlu menjalankan uji staging sebelum membuka pendaftaran mahasiswa.

<section class="auth-container"><div class="panel auth-card"><span class="section-kicker">AKSES PORTAL</span><h1>Selamat datang kembali.</h1><p>Masuk untuk mengelola pengumpulan karya, review, atau jadwal showcase FTI Fest.</p><form method="post" action="<?=h(app_url('/index.php'))?>">
<?=csrf_field()?><input type="hidden" name="action" value="login">
<label>Email akun<input name="email" type="email" autocomplete="username" placeholder="nama@contoh.ac.id" maxlength="160" required></label>
<label>Kata sandi<input name="password" type="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required></label>
<button class="btn primary full" type="submit">Masuk ke Dashboard ↗</button>
</form><p class="auth-help">Belum memiliki akun mahasiswa? <a href="<?=h(route_url('register'))?>">Daftar dengan kode angkatan</a>.</p></div><aside class="auth-side"><span class="icon-huge">✳</span><h2>Satu portal,<br>banyak inovasi.</h2><p>Seluruh dokumentasi proyek, proses kurasi, dan informasi seminar/pameran tersimpan rapi pada satu sistem.</p></aside></section>

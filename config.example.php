<?php
/** Salin menjadi config.php. JANGAN unggah kredensial ini ke Git publik. */
return [
    'app_name' => 'FTI Fest – Showcase Proyek Informatika',
    'app_url' => 'https://domain-anda.ac.id/fti-fest', // URL lengkap, tanpa slash terakhir
    'timezone' => 'Asia/Jakarta',
    'db' => [
        'host' => 'localhost',
        'port' => '3306',
        'name' => 'cpaneluser_ftifest',
        'user' => 'cpaneluser_userdb',
        'pass' => 'GANTI_PASSWORD_DATABASE',
    ],
    'setup_token' => 'GANTI_TOKEN_RAHASIA_ACAK_MINIMAL_32_KARAKTER',
    'registration_code' => 'GANTI_KODE_PENDAFTARAN_ANGKATAN',
    'uploads' => [
        'poster_max_mb' => 5,
        'cover_max_mb' => 4,
        'report_max_mb' => 15,
    ],
];

<?php
// koneksi.php
$cfg = require __DIR__ . '/config.php';

$conn = mysqli_connect($cfg['db_host'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_name']);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set charset (penting agar karakter Indonesia & emoji tampil benar)
mysqli_set_charset($conn, $cfg['db_charset']);

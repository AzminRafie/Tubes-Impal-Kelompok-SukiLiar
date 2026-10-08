<?php
// Konfigurasi Database MySQL XAMPP
$host     = "localhost"; // Server database lokal
$user     = "root";      // Username default XAMPP
$password = "";          // Password default XAMPP (biasanya kosong)
$database = "rentamobil"; // Nama database yang akan kita pakai nanti

// Membuat koneksi ke database
$koneksi = mysqli_connect($host, $user, $password, $database);

// Cek apakah koneksi berhasil atau gagal
if (!$koneksi) {
    // Jika gagal, tampilkan pesan error
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>
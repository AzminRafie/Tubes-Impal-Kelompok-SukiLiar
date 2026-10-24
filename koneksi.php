<?php
// Konfigurasi database
$host     = "localhost";
$user     = "root";
$password = "";
$database = "rentamobil";

// Buat koneksi
$conn = mysqli_connect($host, $user, $password, $database);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set charset supaya karakter Indonesia (é, ñ, dll) tidak rusak
mysqli_set_charset($conn, "utf8mb4");
?>

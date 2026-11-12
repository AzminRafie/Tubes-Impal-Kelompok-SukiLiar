<?php
// ============================================================
// Koneksi Database Pusat - RentaMobil
// Mendukung MySQLi ($conn, $koneksi) dan PDO (db())
// ============================================================

date_default_timezone_set("Asia/Jakarta");

// Konfigurasi database
$host     = "localhost";
$user     = "root";
$password = "";
$database = "rentamobil";

// Konstanta database untuk PDO & helper lainnya
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'rentamobil');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

// Buat koneksi MySQLi
$conn = mysqli_connect($host, $user, $password, $database);

// Cek koneksi MySQLi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set charset supaya karakter Indonesia tidak rusak
mysqli_set_charset($conn, "utf8mb4");

// Alias supaya kode lain yang memakai $koneksi tetap jalan
$koneksi = $conn;

/**
 * Koneksi PDO Singleton untuk Model berbasis PDO (MobilModel, RentalModel, dll)
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }
    return $pdo;
}

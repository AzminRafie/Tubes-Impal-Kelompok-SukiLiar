<?php
// Entry point Page Konfirmasi Pembayaran
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
if (($_SESSION['role'] ?? '') === 'admin') {
    header('Location: ../views/dashboard_sementara.php');
    exit;
}

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../controllers/TransaksiController.php';

$transaksi = new TransaksiController(db());
$userId    = (int)$_SESSION['user_id'];
$rentalId  = (int)($_GET['rental_id'] ?? 0);

// Verifikasi kepemilikan pesanan
$pesanan = $transaksi->getDetailPesanan($rentalId, $userId);
if (!$pesanan) {
    $_SESSION['flash_error'] = 'Pesanan tidak ditemukan atau Anda tidak memiliki akses ke pesanan tersebut.';
    header('Location: my_bookings.php');
    exit;
}

// Jika belum ada bukti pembayaran dan belum Lunas, arahkan ke halaman pembayaran
if (empty($pesanan['bukti_transfer']) && ($pesanan['status_pembayaran'] ?? '') !== 'Lunas') {
    header('Location: pembayaran.php?rental_id=' . $rentalId);
    exit;
}

$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

require __DIR__ . '/../views/konfirmasi_pembayaran.php';

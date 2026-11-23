<?php
// Entry point Page Pembayaran & Upload Bukti Transfer
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
$rentalId  = (int)($_POST['rental_id'] ?? $_GET['rental_id'] ?? 0);

// Verifikasi kepemilikan pesanan: hanya pemilik pesanan yang dapat mengakses
$pesanan = $transaksi->getDetailPesanan($rentalId, $userId);
if (!$pesanan) {
    $_SESSION['flash_error'] = 'Pesanan tidak ditemukan atau Anda tidak memiliki akses ke pesanan tersebut.';
    header('Location: my_bookings.php');
    exit;
}

if (in_array($pesanan['status_pesanan'], ['Dibatalkan', 'Selesai'], true)) {
    $_SESSION['flash_error'] = 'Pesanan yang sudah selesai atau dibatalkan tidak dapat dilakukan pembayaran.';
    header('Location: my_bookings.php');
    exit;
}

// Jika sudah terverifikasi Lunas, arahkan ke halaman konfirmasi pembayaran
if (($pesanan['status_pembayaran'] ?? '') === 'Lunas') {
    header('Location: konfirmasi_pembayaran.php?rental_id=' . $rentalId);
    exit;
}

$rekeningList  = $transaksi->getDaftarRekening();
$metodeTerpilih = trim((string)($_POST['metode_pembayaran'] ?? ($pesanan['metode_pembayaran'] ?? 'Transfer BCA')));
if (!in_array($metodeTerpilih, TransaksiController::METODE_VALID, true)) {
    $metodeTerpilih = 'Transfer BCA';
}

$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesi formulir tidak valid atau telah kedaluwarsa. Silakan muat ulang halaman dan coba lagi.';
    } else {
        $transaksi->setKonteksPembayaran($rentalId, $userId, $metodeTerpilih);
        if ($transaksi->uploadBuktiTransfer('bukti_transfer')) {
            $_SESSION['flash_success'] = 'Bukti pembayaran berhasil dikirim dan sedang menunggu verifikasi admin.';
            header('Location: konfirmasi_pembayaran.php?rental_id=' . $rentalId);
            exit;
        }
        $error = $transaksi->getLastError() ?? 'Gagal mengunggah bukti pembayaran. Silakan coba lagi.';
    }
}

$csrfToken = csrfToken();
require __DIR__ . '/../views/pembayaran.php';

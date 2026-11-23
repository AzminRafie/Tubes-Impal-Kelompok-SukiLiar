<?php
// Entry point Page Checkout & Upload KTP
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

$mobilId = (int)($_POST['mobil_id'] ?? $_GET['id'] ?? 0);
$mulai   = trim((string)($_POST['tanggal_mulai'] ?? $_GET['mulai'] ?? ''));
$selesai = trim((string)($_POST['tanggal_selesai'] ?? $_GET['selesai'] ?? ''));

$mobil = $transaksi->getMobilUntukCheckout($mobilId);
if (!$mobil) {
    $_SESSION['flash_error'] = 'Data mobil tidak ditemukan.';
    header('Location: katalog.php');
    exit;
}
if ($mobil['status_sediaan'] !== 'Tersedia') {
    $_SESSION['flash_error'] = 'Mobil ini sedang tidak tersedia untuk dipesan.';
    header('Location: katalog.php');
    exit;
}

// Validasi tanggal pada GET agar pengguna tidak masuk checkout dengan tanggal tidak valid
$errorTanggal = $transaksi->validasiTanggalRental($mulai, $selesai, $mobilId);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $errorTanggal !== null) {
    $_SESSION['flash_error'] = $errorTanggal;
    header('Location: detail_mobil.php?id=' . $mobilId);
    exit;
}

$durasi     = (!$errorTanggal) ? durasiHari($mulai, $selesai) : 1;
$totalHarga = $durasi * (int)$mobil['harga_perhari'];

$error  = null;
$alamat = trim((string)($_POST['alamat_penjemputan'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesi formulir tidak valid atau telah kedaluwarsa. Silakan muat ulang halaman dan coba lagi.';
    } elseif ($errorTanggal !== null) {
        $error = $errorTanggal;
    } elseif (mb_strlen($alamat) < 5 || mb_strlen($alamat) > 500) {
        $error = 'Alamat penjemputan wajib diisi dengan lengkap (minimal 5 karakter).';
    } else {
        // 1. Upload & validasi foto KTP
        if (!$transaksi->uploadJaminan('foto_ktp')) {
            $error = $transaksi->getLastError() ?? 'Gagal mengunggah foto KTP.';
        } else {
            $uploadedKtp = $transaksi->getLastUploadedFile();
            $transaksi->setDataPesanan($mulai, $selesai, $alamat, $uploadedKtp);

            // 2. Buat pesanan menggunakan user_id dari session (bukan dari frontend)
            $userObj = new User(
                $userId,
                $_SESSION['nama'] ?? null,
                $_SESSION['email'] ?? null,
                null,
                null,
                null,
                $_SESSION['role'] ?? 'customer'
            );
            $mobilObj = new Mobil(
                (int)$mobil['mobil_id'],
                $mobil['nama_mobil'],
                $mobil['plat_nomor'],
                (int)$mobil['harga_perhari'],
                (int)$mobil['kapasitas_kursi'],
                $mobil['transmisi'],
                $mobil['foto_mobil'],
                $mobil['status_sediaan']
            );

            if ($transaksi->buatPesanan($userObj, $mobilObj)) {
                $rentalId = $transaksi->getLastRentalId();
                $_SESSION['flash_success'] = 'Pesanan berhasil dibuat dan foto KTP berhasil diunggah. Silakan pilih metode pembayaran dan unggah bukti transfer.';
                header('Location: pembayaran.php?rental_id=' . $rentalId);
                exit;
            }

            // Hapus file KTP jika penyimpanan pesanan ke database gagal
            $transaksi->hapusFileUpload($uploadedKtp);
            $error = $transaksi->getLastError() ?? 'Gagal membuat pesanan. Silakan coba lagi.';
        }
    }
}

$csrfToken = csrfToken();
require __DIR__ . '/../views/checkout.php';

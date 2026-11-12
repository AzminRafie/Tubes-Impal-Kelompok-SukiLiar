<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Mobil.php';
require_once __DIR__ . '/../models/Rental.php';
require_once __DIR__ . '/../models/Pembayaran.php';

class TransaksiController {

    public function __construct() {
        // Inisialisasi controller transaksi
    }

    // +boolean buatPesanan(User user, Mobil mobil)
    public function buatPesanan(User $user, Mobil $mobil): bool {
        // Skeleton: Membuat pesanan rental baru untuk pengguna dan mobil terpilih
        return true;
    }

    // +boolean uploadJaminan(String fileKTP)
    public function uploadJaminan(string $fileKTP): bool {
        // Skeleton: Mengunggah dan menyimpan file jaminan KTP untuk rental
        return true;
    }

    // +boolean uploadBuktiTransfer(String fileBukti)
    public function uploadBuktiTransfer(string $fileBukti): bool {
        // Skeleton: Mengunggah dan menyimpan bukti transfer pembayaran
        return true;
    }

    // +List tampilkanRiwayat(int userId)
    public function tampilkanRiwayat(int $userId): array {
        // Skeleton: Mengambil dan mengembalikan riwayat transaksi rental milik user
        return [];
    }
}

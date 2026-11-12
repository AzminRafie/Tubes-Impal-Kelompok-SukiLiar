<?php

require_once __DIR__ . '/../models/Mobil.php';
require_once __DIR__ . '/../models/Rental.php';
require_once __DIR__ . '/../models/Pembayaran.php';

class AdminController {
    private ?string $adminRole;

    public function __construct(?string $adminRole = 'admin') {
        $this->adminRole = $adminRole;
    }

    // +boolean tambahMobil(Mobil data)
    public function tambahMobil(Mobil $data): bool {
        // Skeleton: Menambahkan data mobil baru ke database
        return true;
    }

    // +boolean editMobil(int mobilId)
    public function editMobil(int $mobilId): bool {
        // Skeleton: Mengubah data mobil berdasarkan mobilId
        return true;
    }

    // +boolean hapusMobil(int mobilId)
    public function hapusMobil(int $mobilId): bool {
        // Skeleton: Menghapus data mobil berdasarkan mobilId
        return true;
    }

    // +boolean verifikasiDokumen(int rentalId)
    public function verifikasiDokumen(int $rentalId): bool {
        // Skeleton: Verifikasi dokumen KTP / jaminan dari penyewa
        return true;
    }

    // +void updateStatusRental(int rentalId, String status)
    public function updateStatusRental(int $rentalId, string $status): void {
        // Skeleton: Mengubah status pemesanan rental (misal: dikonfirmasi, berjalan, selesai)
    }

    // Getters and Setters
    public function getAdminRole(): ?string {
        return $this->adminRole;
    }

    public function setAdminRole(?string $adminRole): void {
        $this->adminRole = $adminRole;
    }
}

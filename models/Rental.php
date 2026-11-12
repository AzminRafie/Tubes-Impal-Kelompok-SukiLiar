<?php

class Rental {
    private ?int $rental_id;
    private ?int $user_id;
    private ?int $mobil_id;
    private ?string $tanggal_mulai;
    private ?string $tanggal_selesai;
    private ?int $total_harga;
    private ?string $alamat_penjemputan;
    private ?string $foto_ktp;
    private ?string $status_pesanan;

    public function __construct(
        ?int $rental_id = null,
        ?int $user_id = null,
        ?int $mobil_id = null,
        ?string $tanggal_mulai = null,
        ?string $tanggal_selesai = null,
        ?int $total_harga = null,
        ?string $alamat_penjemputan = null,
        ?string $foto_ktp = null,
        ?string $status_pesanan = 'menunggu_pembayaran'
    ) {
        $this->rental_id = $rental_id;
        $this->user_id = $user_id;
        $this->mobil_id = $mobil_id;
        $this->tanggal_mulai = $tanggal_mulai;
        $this->tanggal_selesai = $tanggal_selesai;
        $this->total_harga = $total_harga;
        $this->alamat_penjemputan = $alamat_penjemputan;
        $this->foto_ktp = $foto_ktp;
        $this->status_pesanan = $status_pesanan;
    }

    // +int hitungTotal()
    public function hitungTotal(): int {
        // Skeleton: Menghitung total harga rental berdasarkan durasi hari & harga per hari mobil
        return $this->total_harga ?? 0;
    }

    // Getters and Setters
    public function getRentalId(): ?int {
        return $this->rental_id;
    }

    public function setRentalId(?int $rental_id): void {
        $this->rental_id = $rental_id;
    }

    public function getUserId(): ?int {
        return $this->user_id;
    }

    public function setUserId(?int $user_id): void {
        $this->user_id = $user_id;
    }

    public function getMobilId(): ?int {
        return $this->mobil_id;
    }

    public function setMobilId(?int $mobil_id): void {
        $this->mobil_id = $mobil_id;
    }

    public function getTanggalMulai(): ?string {
        return $this->tanggal_mulai;
    }

    public function setTanggalMulai(?string $tanggal_mulai): void {
        $this->tanggal_mulai = $tanggal_mulai;
    }

    public function getTanggalSelesai(): ?string {
        return $this->tanggal_selesai;
    }

    public function setTanggalSelesai(?string $tanggal_selesai): void {
        $this->tanggal_selesai = $tanggal_selesai;
    }

    public function getTotalHarga(): ?int {
        return $this->total_harga;
    }

    public function setTotalHarga(?int $total_harga): void {
        $this->total_harga = $total_harga;
    }

    public function getAlamatPenjemputan(): ?string {
        return $this->alamat_penjemputan;
    }

    public function setAlamatPenjemputan(?string $alamat_penjemputan): void {
        $this->alamat_penjemputan = $alamat_penjemputan;
    }

    public function getFotoKtp(): ?string {
        return $this->foto_ktp;
    }

    public function setFotoKtp(?string $foto_ktp): void {
        $this->foto_ktp = $foto_ktp;
    }

    public function getStatusPesanan(): ?string {
        return $this->status_pesanan;
    }

    public function setStatusPesanan(?string $status_pesanan): void {
        $this->status_pesanan = $status_pesanan;
    }
}

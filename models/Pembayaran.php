<?php

class Pembayaran {
    private ?int $pembayaran_id;
    private ?int $rental_id;
    private ?string $metode_pembayaran;
    private ?int $jumlah_bayar;
    private ?string $bukti_transfer;
    private ?string $status_pembayaran;

    public function __construct(
        ?int $pembayaran_id = null,
        ?int $rental_id = null,
        ?string $metode_pembayaran = null,
        ?int $jumlah_bayar = null,
        ?string $bukti_transfer = null,
        ?string $status_pembayaran = 'menunggu_verifikasi'
    ) {
        $this->pembayaran_id = $pembayaran_id;
        $this->rental_id = $rental_id;
        $this->metode_pembayaran = $metode_pembayaran;
        $this->jumlah_bayar = $jumlah_bayar;
        $this->bukti_transfer = $bukti_transfer;
        $this->status_pembayaran = $status_pembayaran;
    }

    // Getters and Setters
    public function getPembayaranId(): ?int {
        return $this->pembayaran_id;
    }

    public function setPembayaranId(?int $pembayaran_id): void {
        $this->pembayaran_id = $pembayaran_id;
    }

    public function getRentalId(): ?int {
        return $this->rental_id;
    }

    public function setRentalId(?int $rental_id): void {
        $this->rental_id = $rental_id;
    }

    public function getMetodePembayaran(): ?string {
        return $this->metode_pembayaran;
    }

    public function setMetodePembayaran(?string $metode_pembayaran): void {
        $this->metode_pembayaran = $metode_pembayaran;
    }

    public function getJumlahBayar(): ?int {
        return $this->jumlah_bayar;
    }

    public function setJumlahBayar(?int $jumlah_bayar): void {
        $this->jumlah_bayar = $jumlah_bayar;
    }

    public function getBuktiTransfer(): ?string {
        return $this->bukti_transfer;
    }

    public function setBuktiTransfer(?string $bukti_transfer): void {
        $this->bukti_transfer = $bukti_transfer;
    }

    public function getStatusPembayaran(): ?string {
        return $this->status_pembayaran;
    }

    public function setStatusPembayaran(?string $status_pembayaran): void {
        $this->status_pembayaran = $status_pembayaran;
    }
}

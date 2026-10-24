<?php

class Mobil {
    private ?int $mobil_id;
    private ?string $nama_mobil;
    private ?string $plat_nomor;
    private ?int $harga_perhari;
    private ?int $kapasitas_kursi;
    private ?string $transmisi;
    private ?string $foto_mobil;
    private ?string $status_sediaan;

    public function __construct(
        ?int $mobil_id = null,
        ?string $nama_mobil = null,
        ?string $plat_nomor = null,
        ?int $harga_perhari = null,
        ?int $kapasitas_kursi = null,
        ?string $transmisi = null,
        ?string $foto_mobil = null,
        ?string $status_sediaan = 'tersedia'
    ) {
        $this->mobil_id = $mobil_id;
        $this->nama_mobil = $nama_mobil;
        $this->plat_nomor = $plat_nomor;
        $this->harga_perhari = $harga_perhari;
        $this->kapasitas_kursi = $kapasitas_kursi;
        $this->transmisi = $transmisi;
        $this->foto_mobil = $foto_mobil;
        $this->status_sediaan = $status_sediaan;
    }

    // +void getDetailMobil()
    public function getDetailMobil(): void {
        // Skeleton: Mengambil atau menampilkan informasi detail mobil
    }

    // Getters and Setters
    public function getMobilId(): ?int {
        return $this->mobil_id;
    }

    public function setMobilId(?int $mobil_id): void {
        $this->mobil_id = $mobil_id;
    }

    public function getNamaMobil(): ?string {
        return $this->nama_mobil;
    }

    public function setNamaMobil(?string $nama_mobil): void {
        $this->nama_mobil = $nama_mobil;
    }

    public function getPlatNomor(): ?string {
        return $this->plat_nomor;
    }

    public function setPlatNomor(?string $plat_nomor): void {
        $this->plat_nomor = $plat_nomor;
    }

    public function getHargaPerhari(): ?int {
        return $this->harga_perhari;
    }

    public function setHargaPerhari(?int $harga_perhari): void {
        $this->harga_perhari = $harga_perhari;
    }

    public function getKapasitasKursi(): ?int {
        return $this->kapasitas_kursi;
    }

    public function setKapasitasKursi(?int $kapasitas_kursi): void {
        $this->kapasitas_kursi = $kapasitas_kursi;
    }

    public function getTransmisi(): ?string {
        return $this->transmisi;
    }

    public function setTransmisi(?string $transmisi): void {
        $this->transmisi = $transmisi;
    }

    public function getFotoMobil(): ?string {
        return $this->foto_mobil;
    }

    public function setFotoMobil(?string $foto_mobil): void {
        $this->foto_mobil = $foto_mobil;
    }

    public function getStatusSediaan(): ?string {
        return $this->status_sediaan;
    }

    public function setStatusSediaan(?string $status_sediaan): void {
        $this->status_sediaan = $status_sediaan;
    }
}

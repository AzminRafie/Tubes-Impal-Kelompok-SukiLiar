<?php

require_once __DIR__ . '/../models/Mobil.php';

class KatalogController {
    private ?string $kataKunci;
    private ?string $filterTransmisi;

    public function __construct(?string $kataKunci = null, ?string $filterTransmisi = null) {
        $this->kataKunci = $kataKunci;
        $this->filterTransmisi = $filterTransmisi;
    }

    // +List cariMobil(String keyword)
    public function cariMobil(string $keyword): array {
        // Skeleton: Mengembalikan daftar mobil yang sesuai dengan kata kunci pencarian
        return [];
    }

    // +void tampilkanKatalog()
    public function tampilkanKatalog(): void {
        // Skeleton: Menampilkan daftar mobil / view katalog ke pengguna
    }

    // +boolean cekKetersediaan(Date tglMulai, Date tglSelesai)
    public function cekKetersediaan(string $tglMulai, string $tglSelesai): bool {
        // Skeleton: Memeriksa apakah mobil tersedia pada rentang tanggal tersebut
        return true;
    }

    // Getters and Setters
    public function getKataKunci(): ?string {
        return $this->kataKunci;
    }

    public function setKataKunci(?string $kataKunci): void {
        $this->kataKunci = $kataKunci;
    }

    public function getFilterTransmisi(): ?string {
        return $this->filterTransmisi;
    }

    public function setFilterTransmisi(?string $filterTransmisi): void {
        $this->filterTransmisi = $filterTransmisi;
    }
}

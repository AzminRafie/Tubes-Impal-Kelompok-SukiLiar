<?php

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../models/MobilModel.php';
require_once __DIR__ . '/../models/RentalModel.php';
require_once __DIR__ . '/../models/Mobil.php';

/**
 * Controller (MVC) - Usecase #5 Cari & Filter Mobil.
 * Menerima input (GET), memvalidasi, memanggil Model, lalu menyiapkan data untuk View.
 * Menggabungkan implementasi fungsional fitur Reisa dan kerangka Class Diagram.
 */
class KatalogController
{
    private const PER_PAGE  = 6;
    private const TRANSMISI = ['Manual', 'Matic'];
    private const STATUS    = ['Tersedia', 'Disewa'];

    private ?MobilModel $model = null;
    private ?RentalModel $rental = null;

    private ?string $kataKunci = null;
    private ?string $filterTransmisi = null;

    public function __construct(?string $kataKunci = null, ?string $filterTransmisi = null)
    {
        $this->kataKunci = $kataKunci;
        $this->filterTransmisi = $filterTransmisi;

        if (function_exists('db')) {
            $this->model  = new MobilModel(db());
            $this->rental = new RentalModel(db());
        }
    }

    private function param(string $key): string
    {
        $v = $_GET[$key] ?? '';
        return is_string($v) ? trim($v) : '';
    }

    public function index(): array
    {
        // Langkah 4: validasi kata kunci & filter
        $filter = [
            'q'         => mb_substr($this->param('q'), 0, 100),
            'transmisi' => in_array($this->param('transmisi'), self::TRANSMISI, true) ? $this->param('transmisi') : '',
            'status'    => in_array($this->param('status'), self::STATUS, true) ? $this->param('status') : '',
        ];
        $sort = array_key_exists($this->param('sort'), MobilModel::SORT_OPTIONS) ? $this->param('sort') : 'recommended';

        // Langkah 5-6: cari ke tabel mobil + hitung ringkasan
        $ringkasan    = $this->model ? $this->model->ringkasan($filter) : ['total' => 0, 'tersedia' => 0];
        $totalHalaman = max(1, (int)ceil($ringkasan['total'] / self::PER_PAGE));
        $halaman      = min(max(1, (int)$this->param('page')), $totalHalaman);
        $mobil        = $this->model ? $this->model->cari($filter, $sort, self::PER_PAGE, ($halaman - 1) * self::PER_PAGE) : [];

        foreach ($mobil as &$m) {
            $m['foto_url'] = $this->fotoUrl($m['foto_mobil']);
        }
        unset($m);

        return [
            'filter'       => $filter,
            'sort'         => $sort,
            'ringkasan'    => $ringkasan,
            'mobil'        => $mobil,
            'halaman'      => $halaman,
            'totalHalaman' => $totalHalaman,
        ];
    }

    /** Usecase #6: data detail mobil + tanggal yang tidak tersedia untuk kalender. */
    public function detail(): array
    {
        $id    = (int)$this->param('id');
        $mobil = ($id > 0 && $this->model) ? $this->model->find($id) : null;

        // Alternative flow 3: data mobil sudah dihapus admin
        if (!$mobil) {
            $this->kembaliKeKatalog('Data mobil tidak ditemukan');
        }
        // Precondition: hanya mobil berstatus Available yang bisa dipesan
        if ($mobil['status_sediaan'] !== 'Tersedia') {
            $this->kembaliKeKatalog('Mobil ini sedang disewa dan belum bisa dipesan.');
        }

        $mobil['foto_url'] = $this->fotoUrl($mobil['foto_mobil']);

        return [
            'mobil'     => $mobil,
            'terblokir' => $this->rental ? $this->rental->tanggalTerblokir($id) : [],
            'hariIni'   => date('Y-m-d'),
        ];
    }

    private function kembaliKeKatalog(string $pesan): never
    {
        $_SESSION['flash_error'] = $pesan;
        header('Location: katalog.php');
        exit;
    }

    /** URL foto jika file ada di public/uploads/mobil, selain itu null (view pakai placeholder). */
    private function fotoUrl(?string $nama): ?string
    {
        if (!$nama) {
            return null;
        }
        $nama = basename($nama);
        $path = __DIR__ . '/../public/uploads/mobil/' . $nama;
        return is_file($path) ? 'uploads/mobil/' . rawurlencode($nama) : null;
    }

    // ─── Skeleton methods from Class Diagram ─────────────────

    // +List cariMobil(String keyword)
    public function cariMobil(string $keyword): array {
        // Skeleton: Mengembalikan daftar mobil yang sesuai dengan kata kunci pencarian
        if ($this->model) {
            return $this->model->cari(['q' => $keyword, 'transmisi' => '', 'status' => ''], 'recommended', 10, 0);
        }
        return [];
    }

    // +void tampilkanKatalog()
    public function tampilkanKatalog(): void {
        // Skeleton: Menampilkan daftar mobil / view katalog ke pengguna
        header('Location: katalog.php');
        exit;
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

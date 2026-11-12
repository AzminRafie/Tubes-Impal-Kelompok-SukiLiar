<?php
/**
 * Model (MVC) - entity `mobil`.
 * Hanya berisi query pencarian ke database (DPPL 2.1).
 */
class MobilModel
{
    // Whitelist urutan: nilai dari user tidak pernah masuk langsung ke ORDER BY
    public const SORT_OPTIONS = [
        'recommended' => ['label' => 'Recommended',        'sql' => "CASE WHEN status_sediaan = 'Tersedia' THEN 0 ELSE 1 END, mobil_id ASC"],
        'price_asc'   => ['label' => 'Price: low to high', 'sql' => 'harga_perhari ASC, mobil_id ASC'],
        'price_desc'  => ['label' => 'Price: high to low', 'sql' => 'harga_perhari DESC, mobil_id ASC'],
        'name_asc'    => ['label' => 'Name: A to Z',       'sql' => 'nama_mobil ASC'],
    ];

    public function __construct(private PDO $db) {}

    /** Susun klausa WHERE dari kata kunci + filter. */
    private function where(array $f): array
    {
        $cond = [];
        $params = [];

        if ($f['q'] !== '') {
            $cond[] = 'nama_mobil LIKE :q';
            $params[':q'] = '%' . addcslashes($f['q'], '%_\\') . '%';
        }
        if ($f['transmisi'] !== '') {
            $cond[] = 'transmisi = :transmisi';
            $params[':transmisi'] = $f['transmisi'];
        }
        if ($f['status'] !== '') {
            $cond[] = 'status_sediaan = :status';
            $params[':status'] = $f['status'];
        }

        return [$cond ? 'WHERE ' . implode(' AND ', $cond) : '', $params];
    }

    /** Jumlah hasil + jumlah yang tersedia (untuk "6 cars found • 4 available"). */
    public function ringkasan(array $f): array
    {
        [$where, $params] = $this->where($f);
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(status_sediaan = 'Tersedia'), 0) AS tersedia
             FROM mobil $where"
        );
        $stmt->execute($params);
        $row = $stmt->fetch();
        return ['total' => (int)$row['total'], 'tersedia' => (int)$row['tersedia']];
    }

    /** Satu mobil berdasarkan id (null jika tidak ada / sudah dihapus admin). */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT mobil_id, nama_mobil, plat_nomor, harga_perhari, kapasitas_kursi, transmisi, foto_mobil, status_sediaan
             FROM mobil WHERE mobil_id = :id'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Daftar mobil sesuai pencarian, filter, urutan, dan halaman. */
    public function cari(array $f, string $sort, int $limit, int $offset): array
    {
        [$where, $params] = $this->where($f);
        $orderBy = self::SORT_OPTIONS[$sort]['sql'];

        $stmt = $this->db->prepare(
            "SELECT mobil_id, nama_mobil, plat_nomor, harga_perhari, kapasitas_kursi,
                    transmisi, foto_mobil, status_sediaan
             FROM mobil $where
             ORDER BY $orderBy
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}

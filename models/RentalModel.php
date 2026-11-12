<?php
/** Model (MVC) - entity `rental` (+ join mobil & pembayaran). */
class RentalModel
{
    // Pesanan berstatus ini dianggap sudah tidak memblokir jadwal (DPPL 5.1)
    private const STATUS_BEBAS = "('Dibatalkan', 'Selesai')";

    public function __construct(private PDO $db) {}

    /** Semua tanggal (Y-m-d) yang sudah terisi untuk satu mobil, termasuk tanggal mulai & selesai. */
    public function tanggalTerblokir(int $mobilId): array
    {
        $stmt = $this->db->prepare(
            'SELECT tanggal_mulai, tanggal_selesai FROM rental
             WHERE mobil_id = :id AND status_pesanan NOT IN ' . self::STATUS_BEBAS . '
               AND tanggal_selesai >= CURDATE()'
        );
        $stmt->execute([':id' => $mobilId]);

        $tanggal = [];
        foreach ($stmt->fetchAll() as $r) {
            $d   = new DateTimeImmutable($r['tanggal_mulai']);
            $end = new DateTimeImmutable($r['tanggal_selesai']);
            for (; $d <= $end; $d = $d->modify('+1 day')) {
                $tanggal[$d->format('Y-m-d')] = true;
            }
        }
        return array_keys($tanggal);
    }

    /** Cek irisan tanggal (rumus DPPL 5.1). Dipakai ulang saat checkout (#7). */
    public function adaBentrok(int $mobilId, string $mulai, string $selesai): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM rental
             WHERE mobil_id = :id AND status_pesanan NOT IN ' . self::STATUS_BEBAS . '
               AND :mulai <= tanggal_selesai AND :selesai >= tanggal_mulai'
        );
        $stmt->execute([':id' => $mobilId, ':mulai' => $mulai, ':selesai' => $selesai]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /** Seluruh pesanan milik satu user beserta data mobil dan pembayarannya. */
    public function pesananUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.rental_id, r.tanggal_mulai, r.tanggal_selesai, r.total_harga, r.status_pesanan,
                    m.nama_mobil, m.transmisi, m.harga_perhari,
                    COALESCE(p.jumlah_bayar, 0) AS jumlah_bayar
             FROM rental r
             JOIN mobil m ON m.mobil_id = r.mobil_id
             LEFT JOIN pembayaran p ON p.rental_id = r.rental_id
             WHERE r.user_id = :uid
             ORDER BY r.rental_id DESC'
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }
}

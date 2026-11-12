<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../models/RentalModel.php';

/** Controller (MVC) - Usecase #9 Lihat My Bookings & Riwayat. */
class RentalController
{
    private const AKTIF = ['Menunggu Pembayaran', 'Menunggu Verifikasi', 'Disewa'];

    private RentalModel $model;

    public function __construct()
    {
        $this->model = new RentalModel(db());
    }

    public function myBookings(int $userId): array
    {
        $aktif = [];
        $riwayat = [];
        foreach ($this->model->pesananUser($userId) as $r) {
            $r['kode']   = kodeBooking((int)$r['rental_id'], $r['tanggal_mulai']);
            $r['durasi'] = durasiHari($r['tanggal_mulai'], $r['tanggal_selesai']);
            if (in_array($r['status_pesanan'], self::AKTIF, true)) {
                $aktif[] = $r;
            } else {
                $riwayat[] = $r;
            }
        }

        // Banner: pesanan terbaru yang sedang menunggu verifikasi admin
        $banner = null;
        foreach ($aktif as $r) {
            if ($r['status_pesanan'] === 'Menunggu Verifikasi') {
                $banner = $r['kode'] . ': Payment receipt and KTP have been received. Please wait up to 1×24 hours for admin confirmation.';
                break; // daftar sudah urut rental_id terbaru dulu
            }
        }

        return ['aktif' => $aktif, 'riwayat' => $riwayat, 'banner' => $banner];
    }
}

<?php

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Mobil.php';
require_once __DIR__ . '/../models/Rental.php';
require_once __DIR__ . '/../models/Pembayaran.php';
require_once __DIR__ . '/../models/MobilModel.php';
require_once __DIR__ . '/../models/RentalModel.php';

/**
 * Controller (MVC) - Transaksi & Pembayaran (Checkout, Upload KTP, Transfer Bank, Riwayat).
 * Mempertahankan 4 method utama sesuai Class Diagram:
 *   +boolean buatPesanan(User user, Mobil mobil)
 *   +boolean uploadJaminan(String fileKTP)
 *   +boolean uploadBuktiTransfer(String fileBukti)
 *   +List tampilkanRiwayat(int userId)
 */
class TransaksiController {
    public const MAX_UPLOAD_BYTES = 5242880; // 5 MB
    public const ALLOWED_MIME = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
    ];
    public const METODE_VALID = [
        'Transfer BCA',
        'Transfer Mandiri',
        'Transfer BRI',
    ];
    private const STATUS_BEBAS = "('Dibatalkan', 'Selesai')";

    private ?PDO $db = null;
    private ?MobilModel $mobilModel = null;
    private ?RentalModel $rentalModel = null;

    // Konteks sementara untuk operasi pesanan & pembayaran
    private ?string $tanggalMulai = null;
    private ?string $tanggalSelesai = null;
    private ?string $alamatPenjemputan = null;
    private ?string $fotoKtpPath = null;
    private ?int $activeRentalId = null;
    private ?int $activeUserId = null;
    private ?string $selectedMetode = null;

    // Hasil / status operasi terakhir
    private ?int $lastRentalId = null;
    private ?int $lastPembayaranId = null;
    private ?string $lastUploadedFile = null;
    private ?string $lastError = null;

    public function __construct(?PDO $pdo = null) {
        if ($pdo !== null) {
            $this->db = $pdo;
        } elseif (function_exists('db')) {
            $this->db = db();
        }
        if ($this->db !== null) {
            $this->mobilModel = new MobilModel($this->db);
            $this->rentalModel = new RentalModel($this->db);
        }
    }

    // ─── Helper Konteks & Getter Status ──────────────────────

    public function setDataPesanan(
        string $tanggalMulai,
        string $tanggalSelesai,
        string $alamatPenjemputan,
        ?string $fotoKtpPath = null
    ): void {
        $this->tanggalMulai = trim($tanggalMulai);
        $this->tanggalSelesai = trim($tanggalSelesai);
        $this->alamatPenjemputan = trim($alamatPenjemputan);
        if ($fotoKtpPath !== null) {
            $this->fotoKtpPath = $fotoKtpPath;
        }
    }

    public function setKonteksPembayaran(int $rentalId, int $userId, string $metodePembayaran): void {
        $this->activeRentalId = $rentalId;
        $this->activeUserId = $userId;
        $this->selectedMetode = trim($metodePembayaran);
    }

    public function setRentalAktif(int $rentalId, ?int $userId = null): void {
        $this->activeRentalId = $rentalId;
        $this->activeUserId = $userId;
    }

    public function getLastRentalId(): ?int {
        return $this->lastRentalId;
    }

    public function getLastPembayaranId(): ?int {
        return $this->lastPembayaranId;
    }

    public function getLastUploadedFile(): ?string {
        return $this->lastUploadedFile;
    }

    public function getLastError(): ?string {
        return $this->lastError;
    }

    public function getDaftarRekening(): array {
        $path = __DIR__ . '/../config/rekening.php';
        if (!is_file($path)) {
            return [];
        }
        $data = require $path;
        return is_array($data) ? $data : [];
    }

    /** Ambil data mobil dari DB untuk halaman checkout (termasuk foto_url). */
    public function getMobilUntukCheckout(int $mobilId): ?array {
        if ($mobilId <= 0 || !$this->mobilModel) {
            return null;
        }
        $mobil = $this->mobilModel->find($mobilId);
        if (!$mobil) {
            return null;
        }
        $mobil['foto_url'] = $this->fotoMobilUrl($mobil['foto_mobil']);
        return $mobil;
    }

    /** Validasi format, rentang minimal H+1, dan ketersediaan tanggal mobil. */
    public function validasiTanggalRental(string $mulai, string $selesai, int $mobilId): ?string {
        if (!$this->isValidDate($mulai) || !$this->isValidDate($selesai)) {
            return 'Format tanggal rental tidak valid.';
        }
        $hariIni = date('Y-m-d');
        if ($mulai < $hariIni) {
            return 'Tanggal mulai sewa tidak boleh sebelum hari ini.';
        }
        if ($selesai <= $mulai) {
            return 'Sewa minimal 24 jam. Tanggal selesai harus minimal 1 hari setelah tanggal mulai.';
        }
        if ($this->rentalModel && $this->rentalModel->adaBentrok($mobilId, $mulai, $selesai)) {
            return 'Mobil sudah dipesan pada rentang tanggal tersebut. Silakan pilih tanggal lain.';
        }
        return null;
    }

    /** Ambil detail satu pesanan milik user tertentu (memastikan kepemilikan). */
    public function getDetailPesanan(int $rentalId, int $userId): ?array {
        if ($rentalId <= 0 || $userId <= 0 || !$this->db) {
            return null;
        }
        $stmt = $this->db->prepare(
            'SELECT r.rental_id, r.user_id, r.mobil_id, r.tanggal_mulai, r.tanggal_selesai,
                    r.total_harga, r.alamat_penjemputan, r.foto_ktp, r.status_pesanan,
                    m.nama_mobil, m.plat_nomor, m.harga_perhari, m.kapasitas_kursi, m.transmisi, m.foto_mobil,
                    p.pembayaran_id, p.metode_pembayaran, COALESCE(p.jumlah_bayar, 0) AS jumlah_bayar,
                    p.bukti_transfer, p.status_pembayaran
             FROM rental r
             JOIN mobil m ON m.mobil_id = r.mobil_id
             LEFT JOIN pembayaran p ON p.pembayaran_id = (
                 SELECT MAX(p2.pembayaran_id) FROM pembayaran p2 WHERE p2.rental_id = r.rental_id
             )
             WHERE r.rental_id = :rid AND r.user_id = :uid
             LIMIT 1'
        );
        $stmt->execute([':rid' => $rentalId, ':uid' => $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $row['kode'] = kodeBooking((int)$row['rental_id'], $row['tanggal_mulai']);
        $row['durasi'] = durasiHari($row['tanggal_mulai'], $row['tanggal_selesai']);
        $row['foto_url'] = $this->fotoMobilUrl($row['foto_mobil']);
        $row['bukti_url'] = $this->uploadPublicUrl($row['bukti_transfer'], 'bukti_transfer');
        $row['ktp_url'] = $this->uploadPublicUrl($row['foto_ktp'], 'ktp');
        return $row;
    }

    // ─── 4 Method Utama Sesuai Class Diagram ─────────────────

    // +boolean buatPesanan(User user, Mobil mobil)
    public function buatPesanan(User $user, Mobil $mobil): bool {
        $this->lastError = null;
        $this->lastRentalId = null;

        if (!$this->db) {
            $this->lastError = 'Koneksi database tidak tersedia.';
            return false;
        }

        $userId = (int)($user->getUserId() ?? 0);
        $mobilId = (int)($mobil->getMobilId() ?? 0);
        if ($userId <= 0 || $mobilId <= 0) {
            $this->lastError = 'Data pengguna atau mobil tidak valid.';
            return false;
        }

        $mulai = $this->tanggalMulai ?? '';
        $selesai = $this->tanggalSelesai ?? '';
        $alamat = $this->alamatPenjemputan ?? '';
        $fotoKtp = $this->fotoKtpPath ?? '';

        if (!$this->isValidDate($mulai) || !$this->isValidDate($selesai)) {
            $this->lastError = 'Format tanggal mulai atau tanggal selesai tidak valid.';
            return false;
        }
        if ($mulai < date('Y-m-d')) {
            $this->lastError = 'Tanggal mulai sewa tidak boleh sebelum hari ini.';
            return false;
        }
        if ($selesai <= $mulai) {
            $this->lastError = 'Sewa minimal 24 jam. Tanggal selesai harus minimal H+1 dari tanggal mulai.';
            return false;
        }
        if (mb_strlen($alamat) < 5 || mb_strlen($alamat) > 500) {
            $this->lastError = 'Alamat penjemputan wajib diisi (minimal 5 karakter, maksimal 500 karakter).';
            return false;
        }
        if ($fotoKtp === '') {
            $this->lastError = 'Foto KTP wajib diunggah sebagai jaminan.';
            return false;
        }

        try {
            $this->db->beginTransaction();

            // Ambil data mobil & harga langsung dari database (jangan percaya harga frontend)
            $stmtMobil = $this->db->prepare(
                'SELECT mobil_id, nama_mobil, plat_nomor, harga_perhari, status_sediaan
                 FROM mobil WHERE mobil_id = :mid FOR UPDATE'
            );
            $stmtMobil->execute([':mid' => $mobilId]);
            $mobilDb = $stmtMobil->fetch();

            if (!$mobilDb) {
                $this->db->rollBack();
                $this->lastError = 'Data mobil tidak ditemukan.';
                return false;
            }
            if ($mobilDb['status_sediaan'] !== 'Tersedia') {
                $this->db->rollBack();
                $this->lastError = 'Mobil ini sedang tidak tersedia untuk dipesan.';
                return false;
            }

            // Cek bentrok tanggal di dalam transaksi untuk mencegah pemesanan ganda
            $stmtBentrok = $this->db->prepare(
                'SELECT COUNT(*) FROM rental
                 WHERE mobil_id = :mid AND status_pesanan NOT IN ' . self::STATUS_BEBAS . '
                   AND :mulai <= tanggal_selesai AND :selesai >= tanggal_mulai
                 FOR UPDATE'
            );
            $stmtBentrok->execute([
                ':mid'     => $mobilId,
                ':mulai'   => $mulai,
                ':selesai' => $selesai,
            ]);
            if ((int)$stmtBentrok->fetchColumn() > 0) {
                $this->db->rollBack();
                $this->lastError = 'Tanggal yang dipilih bertabrakan dengan pesanan lain. Silakan pilih tanggal berbeda.';
                return false;
            }

            // Hitung total harga di server menggunakan model Rental
            $rental = new Rental(
                null,
                $userId,
                $mobilId,
                $mulai,
                $selesai,
                null,
                $alamat,
                $fotoKtp,
                'Menunggu Pembayaran'
            );
            $rental->setHargaPerhariReferensi((int)$mobilDb['harga_perhari']);
            $totalHarga = $rental->hitungTotal();

            $stmtInsert = $this->db->prepare(
                'INSERT INTO rental
                    (user_id, mobil_id, tanggal_mulai, tanggal_selesai, total_harga, alamat_penjemputan, foto_ktp, status_pesanan)
                 VALUES
                    (:uid, :mid, :mulai, :selesai, :total, :alamat, :ktp, :status)'
            );
            $stmtInsert->execute([
                ':uid'     => $rental->getUserId(),
                ':mid'     => $rental->getMobilId(),
                ':mulai'   => $rental->getTanggalMulai(),
                ':selesai' => $rental->getTanggalSelesai(),
                ':total'   => $totalHarga,
                ':alamat'  => $rental->getAlamatPenjemputan(),
                ':ktp'     => $rental->getFotoKtp(),
                ':status'  => $rental->getStatusPesanan(),
            ]);

            $this->lastRentalId = (int)$this->db->lastInsertId();
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->lastError = 'Gagal menyimpan pesanan ke database. Silakan coba lagi.';
            return false;
        }
    }

    // +boolean uploadJaminan(String fileKTP)
    public function uploadJaminan(string $fileKTP): bool {
        $this->lastError = null;
        $this->lastUploadedFile = null;

        $savedRelativePath = $this->prosesUploadGambar($fileKTP, 'ktp', 'ktp');
        if ($savedRelativePath === null) {
            return false;
        }

        $this->fotoKtpPath = $savedRelativePath;
        $this->lastUploadedFile = $savedRelativePath;

        // Jika dipanggil untuk memperbarui rental yang sudah ada, simpan ke kolom foto_ktp
        if ($this->activeRentalId && $this->activeUserId && $this->db) {
            try {
                $stmt = $this->db->prepare(
                    'UPDATE rental SET foto_ktp = :ktp WHERE rental_id = :rid AND user_id = :uid'
                );
                $stmt->execute([
                    ':ktp' => $savedRelativePath,
                    ':rid' => $this->activeRentalId,
                    ':uid' => $this->activeUserId,
                ]);
            } catch (Throwable $e) {
                $this->lastError = 'File KTP tersimpan, tetapi gagal memperbarui data pesanan.';
                return false;
            }
        }

        return true;
    }

    // +boolean uploadBuktiTransfer(String fileBukti)
    public function uploadBuktiTransfer(string $fileBukti): bool {
        $this->lastError = null;
        $this->lastUploadedFile = null;
        $this->lastPembayaranId = null;

        if (!$this->db) {
            $this->lastError = 'Koneksi database tidak tersedia.';
            return false;
        }

        $rentalId = (int)($this->activeRentalId ?? 0);
        $userId = (int)($this->activeUserId ?? 0);
        $metode = $this->selectedMetode ?? '';

        if ($rentalId <= 0 || $userId <= 0) {
            $this->lastError = 'Pesanan atau sesi pengguna tidak valid.';
            return false;
        }
        if (!in_array($metode, self::METODE_VALID, true)) {
            $this->lastError = 'Metode pembayaran tidak valid. Pilih Transfer BCA, Transfer Mandiri, atau Transfer BRI.';
            return false;
        }

        // Validasi file terlebih dahulu sebelum memulai transaksi database
        $savedRelativePath = $this->prosesUploadGambar($fileBukti, 'bukti_transfer', 'bukti');
        if ($savedRelativePath === null) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            // Pastikan pesanan benar-benar milik user yang sedang login
            $stmtRental = $this->db->prepare(
                'SELECT rental_id, user_id, total_harga, status_pesanan
                 FROM rental
                 WHERE rental_id = :rid AND user_id = :uid
                 FOR UPDATE'
            );
            $stmtRental->execute([':rid' => $rentalId, ':uid' => $userId]);
            $rental = $stmtRental->fetch();

            if (!$rental) {
                $this->db->rollBack();
                $this->hapusFileUpload($savedRelativePath);
                $this->lastError = 'Pesanan tidak ditemukan atau Anda tidak memiliki akses ke pesanan ini.';
                return false;
            }

            if (in_array($rental['status_pesanan'], ['Dibatalkan', 'Selesai'], true)) {
                $this->db->rollBack();
                $this->hapusFileUpload($savedRelativePath);
                $this->lastError = 'Pesanan yang sudah selesai atau dibatalkan tidak dapat dibayar.';
                return false;
            }

            // Cek record pembayaran yang sudah ada agar tidak terjadi duplikasi / pembayaran ganda
            $stmtCek = $this->db->prepare(
                'SELECT pembayaran_id, status_pembayaran, bukti_transfer
                 FROM pembayaran
                 WHERE rental_id = :rid
                 ORDER BY pembayaran_id DESC
                 LIMIT 1
                 FOR UPDATE'
            );
            $stmtCek->execute([':rid' => $rentalId]);
            $existing = $stmtCek->fetch();

            if ($existing && $existing['status_pembayaran'] === 'Lunas') {
                $this->db->rollBack();
                $this->hapusFileUpload($savedRelativePath);
                $this->lastError = 'Pembayaran untuk pesanan ini sudah lunas dan diverifikasi admin.';
                return false;
            }

            // Jumlah bayar SELALU diambil dari total_harga di database, bukan dari input browser
            $pembayaran = new Pembayaran(
                $existing ? (int)$existing['pembayaran_id'] : null,
                $rentalId,
                $metode,
                (int)$rental['total_harga'],
                $savedRelativePath,
                'Menunggu Verifikasi'
            );

            $oldFileToRemove = null;
            if ($existing) {
                $oldFileToRemove = $existing['bukti_transfer'] ?? null;
                $stmtUpdate = $this->db->prepare(
                    'UPDATE pembayaran
                     SET metode_pembayaran = :metode,
                         jumlah_bayar = :jumlah,
                         bukti_transfer = :bukti,
                         status_pembayaran = :status
                     WHERE pembayaran_id = :pid'
                );
                $stmtUpdate->execute([
                    ':metode' => $pembayaran->getMetodePembayaran(),
                    ':jumlah' => $pembayaran->getJumlahBayar(),
                    ':bukti'  => $pembayaran->getBuktiTransfer(),
                    ':status' => $pembayaran->getStatusPembayaran(),
                    ':pid'    => $pembayaran->getPembayaranId(),
                ]);
                $this->lastPembayaranId = $pembayaran->getPembayaranId();
            } else {
                $stmtInsert = $this->db->prepare(
                    'INSERT INTO pembayaran (rental_id, metode_pembayaran, jumlah_bayar, bukti_transfer, status_pembayaran)
                     VALUES (:rid, :metode, :jumlah, :bukti, :status)'
                );
                $stmtInsert->execute([
                    ':rid'    => $pembayaran->getRentalId(),
                    ':metode' => $pembayaran->getMetodePembayaran(),
                    ':jumlah' => $pembayaran->getJumlahBayar(),
                    ':bukti'  => $pembayaran->getBuktiTransfer(),
                    ':status' => $pembayaran->getStatusPembayaran(),
                ]);
                $this->lastPembayaranId = (int)$this->db->lastInsertId();
            }

            // Perbarui status_pesanan menjadi Menunggu Verifikasi sesuai alur status aplikasi
            if (in_array($rental['status_pesanan'], ['Menunggu Pembayaran', 'Menunggu Verifikasi'], true)) {
                $stmtStatus = $this->db->prepare(
                    'UPDATE rental SET status_pesanan = :status WHERE rental_id = :rid'
                );
                $stmtStatus->execute([
                    ':status' => 'Menunggu Verifikasi',
                    ':rid'    => $rentalId,
                ]);
            }

            $this->db->commit();
            $this->lastUploadedFile = $savedRelativePath;

            // Hapus bukti transfer lama jika ada dan berada di folder upload bukti_transfer
            if ($oldFileToRemove && $oldFileToRemove !== $savedRelativePath) {
                $this->hapusFileUpload($oldFileToRemove);
            }

            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->hapusFileUpload($savedRelativePath);
            $this->lastError = 'Gagal menyimpan bukti pembayaran ke database. Silakan coba lagi.';
            return false;
        }
    }

    // +List tampilkanRiwayat(int userId)
    public function tampilkanRiwayat(int $userId): array {
        if ($userId <= 0 || !$this->db) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT r.rental_id, r.user_id, r.mobil_id, r.tanggal_mulai, r.tanggal_selesai,
                    r.total_harga, r.alamat_penjemputan, r.foto_ktp, r.status_pesanan,
                    m.nama_mobil, m.plat_nomor, m.transmisi, m.harga_perhari, m.foto_mobil,
                    p.pembayaran_id, p.metode_pembayaran,
                    COALESCE(p.jumlah_bayar, 0) AS jumlah_bayar,
                    p.bukti_transfer, p.status_pembayaran
             FROM rental r
             JOIN mobil m ON m.mobil_id = r.mobil_id
             LEFT JOIN pembayaran p ON p.pembayaran_id = (
                 SELECT MAX(p2.pembayaran_id) FROM pembayaran p2 WHERE p2.rental_id = r.rental_id
             )
             WHERE r.user_id = :uid
             ORDER BY r.rental_id DESC'
        );
        $stmt->execute([':uid' => $userId]);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$r) {
            $r['kode'] = kodeBooking((int)$r['rental_id'], $r['tanggal_mulai']);
            $r['durasi'] = durasiHari($r['tanggal_mulai'], $r['tanggal_selesai']);
            $r['foto_url'] = $this->fotoMobilUrl($r['foto_mobil']);
        }
        unset($r);

        return $rows;
    }

    // ─── Internal Helpers (Upload & Validasi) ────────────────

    private function isValidDate(string $date): bool {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        $dt = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        return $dt !== false && $dt->format('Y-m-d') === $date;
    }

    /**
     * Memvalidasi & menyimpan file gambar (JPG/JPEG/PNG, maks 5 MB, nama acak).
     * Parameter $fileInput dapat berupa nama key di $_FILES (mis. 'foto_ktp') atau path lokal.
     */
    private function prosesUploadGambar(string $fileInput, string $subFolder, string $prefix): ?string {
        $isUploadedFile = false;
        $tmpPath = '';
        $origName = '';
        $size = 0;

        if (isset($_FILES[$fileInput]) && is_array($_FILES[$fileInput])) {
            $file = $_FILES[$fileInput];
            $err = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($err !== UPLOAD_ERR_OK) {
                $this->lastError = match ($err) {
                    UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas maksimal 5 MB.',
                    UPLOAD_ERR_NO_FILE => 'File belum dipilih. Silakan pilih gambar berformat JPG atau PNG.',
                    default => 'Gagal mengunggah file. Silakan coba lagi.',
                };
                return null;
            }
            $tmpPath = (string)($file['tmp_name'] ?? '');
            $origName = (string)($file['name'] ?? '');
            $size = (int)($file['size'] ?? 0);
            $isUploadedFile = true;

            if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
                $this->lastError = 'File unggahan tidak valid.';
                return null;
            }
        } elseif ($fileInput !== '' && is_file($fileInput)) {
            $tmpPath = $fileInput;
            $origName = basename($fileInput);
            $size = (int)filesize($tmpPath);
        } else {
            $this->lastError = 'File tidak ditemukan. Silakan pilih file gambar JPG atau PNG.';
            return null;
        }

        if ($size <= 0 || $size > self::MAX_UPLOAD_BYTES) {
            $this->lastError = 'Ukuran file harus antara 1 byte hingga maksimal 5 MB.';
            return null;
        }

        // Validasi ekstensi awal (tolak ekstensi berbahaya / executable)
        $extOrig = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if (!in_array($extOrig, ['jpg', 'jpeg', 'png'], true)) {
            $this->lastError = 'Format file tidak diperbolehkan. Hanya menerima gambar JPG, JPEG, atau PNG.';
            return null;
        }

        // Validasi MIME type asli dari isi file menggunakan finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpPath);
        if (!is_string($mime) || !array_key_exists($mime, self::ALLOWED_MIME)) {
            $this->lastError = 'Tipe file tidak valid. Pastikan file adalah gambar JPG atau PNG asli.';
            return null;
        }

        // Verifikasi tambahan bahwa file benar-benar gambar valid
        if (@getimagesize($tmpPath) === false) {
            $this->lastError = 'File gambar rusak atau tidak valid.';
            return null;
        }

        $targetDir = __DIR__ . '/../public/uploads/' . $subFolder;
        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            $this->lastError = 'Direktori penyimpanan upload tidak dapat dibuat.';
            return null;
        }

        // Nama file unik & acak agar tidak menimpa file pengguna lain
        $safeExt = self::ALLOWED_MIME[$mime];
        $fileName = $prefix . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $safeExt;
        $destPath = $targetDir . '/' . $fileName;

        $moved = $isUploadedFile
            ? move_uploaded_file($tmpPath, $destPath)
            : copy($tmpPath, $destPath);

        if (!$moved) {
            $this->lastError = 'Gagal menyimpan file ke server.';
            return null;
        }

        return 'uploads/' . $subFolder . '/' . $fileName;
    }

    /** Hapus file upload lama dengan proteksi path traversal. */
    public function hapusFileUpload(?string $relativePath): void {
        if (!$relativePath) {
            return;
        }
        $normalized = ltrim(str_replace('\\', '/', $relativePath), '/');
        if (!str_starts_with($normalized, 'uploads/ktp/') && !str_starts_with($normalized, 'uploads/bukti_transfer/')) {
            return;
        }
        $fullPath = realpath(__DIR__ . '/../public/' . $normalized);
        $baseDir = realpath(__DIR__ . '/../public/uploads');
        if ($fullPath && $baseDir && str_starts_with($fullPath, $baseDir) && is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function fotoMobilUrl(?string $nama): ?string {
        if (!$nama) {
            return null;
        }
        $nama = basename($nama);
        $path = __DIR__ . '/../public/uploads/mobil/' . $nama;
        return is_file($path) ? 'uploads/mobil/' . rawurlencode($nama) : null;
    }

    private function uploadPublicUrl(?string $storedPath, string $subFolder): ?string {
        if (!$storedPath) {
            return null;
        }
        $normalized = ltrim(str_replace('\\', '/', $storedPath), '/');
        if (str_starts_with($normalized, 'uploads/' . $subFolder . '/')) {
            $fileName = basename($normalized);
        } else {
            $fileName = basename($normalized);
        }
        $fullPath = __DIR__ . '/../public/uploads/' . $subFolder . '/' . $fileName;
        return is_file($fullPath) ? 'uploads/' . $subFolder . '/' . rawurlencode($fileName) : null;
    }
}

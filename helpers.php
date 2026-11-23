<?php
function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupiah(int $n): string { return 'Rp' . number_format($n, 0, ',', '.'); }

const BULAN_PENDEK = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

/** Durasi sewa (hari) berbasis 24 jam. Minimal 1 hari sewa (H+1). */
function durasiHari(string $mulai, string $selesai): int
{
    $selisih = (int)(new DateTimeImmutable($mulai))->diff(new DateTimeImmutable($selesai))->format('%a');
    return max(1, $selisih);
}

/** "12–15 Nov 2026", "30 Oct–02 Nov 2026", atau lintas tahun. */
function rentangTanggal(string $mulai, string $selesai): string
{
    $a = new DateTimeImmutable($mulai);
    $b = new DateTimeImmutable($selesai);
    $bln = fn(DateTimeImmutable $d) => BULAN_PENDEK[(int)$d->format('n') - 1];

    if ($a->format('Y-m') === $b->format('Y-m')) {
        return $a->format('d') . '–' . $b->format('d') . ' ' . $bln($b) . ' ' . $b->format('Y');
    }
    if ($a->format('Y') === $b->format('Y')) {
        return $a->format('d') . ' ' . $bln($a) . '–' . $b->format('d') . ' ' . $bln($b) . ' ' . $b->format('Y');
    }
    return $a->format('d') . ' ' . $bln($a) . ' ' . $a->format('Y') . '–' . $b->format('d') . ' ' . $bln($b) . ' ' . $b->format('Y');
}

/** Format satu tanggal tampilan, mis. "12 Nov 2026". */
function formatTanggalPendek(string $tgl): string
{
    $d = new DateTimeImmutable($tgl);
    return $d->format('d') . ' ' . BULAN_PENDEK[(int)$d->format('n') - 1] . ' ' . $d->format('Y');
}

/** Kode pesanan tampilan, mis. RM-261112-014 (tanggal mulai sewa + id rental). */
function kodeBooking(int $rentalId, string $tanggalMulai): string
{
    return 'RM-' . (new DateTimeImmutable($tanggalMulai))->format('ymd') . '-' . str_pad((string)$rentalId, 3, '0', STR_PAD_LEFT);
}

/** Label & kelas badge per status_pesanan. */
function badgeStatus(string $status): array
{
    return match ($status) {
        'Menunggu Pembayaran' => ['Awaiting payment',      'rm-badge-wait'],
        'Menunggu Verifikasi' => ['Pending confirmation',  'rm-badge-wait'],
        'Disewa'              => ['Rented',                'rm-badge-rented'],
        'Selesai'             => ['Completed',             'rm-badge-ok'],
        'Dibatalkan'          => ['Cancelled',             'rm-badge-cancel'],
        default               => [$status,                 'rm-badge-rented'],
    };
}

/** Label & kelas badge per status_pembayaran. */
function badgePembayaran(?string $status, ?string $buktiTransfer = null): array
{
    if ($status === 'Lunas') {
        return ['Lunas (Verified)', 'rm-badge-ok'];
    }
    if ($status === 'Ditolak') {
        return ['Ditolak', 'rm-badge-cancel'];
    }
    if ($status === 'Menunggu Verifikasi' && !empty($buktiTransfer)) {
        return ['Menunggu Verifikasi', 'rm-badge-wait'];
    }
    return ['Belum Dibayar', 'rm-badge-neutral'];
}

/** Indeks tahap aktif pada tracker: Submitted, Verification, Confirmed, Pickup, Return. */
function tahapPesanan(string $status): int
{
    return match ($status) {
        'Menunggu Pembayaran' => 0,
        'Menunggu Verifikasi' => 1,
        'Disewa'              => 4,
        default               => 5,
    };
}

/** Menghasilkan atau mengambil token CSRF per sesi. */
function csrfToken(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Memverifikasi token CSRF dari request POST. */
function verifyCsrf(?string $token): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION['csrf_token']) || !is_string($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

<?php
/** View (MVC) - Page Konfirmasi Pembayaran. Data dari public/konfirmasi_pembayaran.php. */
$judul = 'Konfirmasi Pembayaran ' . $pesanan['kode'];
$menuAktif = 'bookings';
$statusBayar = $pesanan['status_pembayaran'] ?? 'Menunggu Verifikasi';
[$labelStatusPesanan, $kelasStatusPesanan] = badgeStatus($pesanan['status_pesanan']);
[$labelStatusBayar, $kelasStatusBayar]     = badgePembayaran($statusBayar, $pesanan['bukti_transfer'] ?? null);
require __DIR__ . '/partials/header.php';
?>

<main class="container py-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
      <h1 class="h4 fw-bold mb-1">Konfirmasi Pembayaran</h1>
      <nav aria-label="breadcrumb" class="small text-secondary">
        <a href="my_bookings.php" class="text-secondary text-decoration-none">My Bookings</a> /
        <?= e($pesanan['kode']) ?> / Konfirmasi
      </nav>
    </div>
    <a href="my_bookings.php" class="btn btn-outline-dark btn-sm">
      <i class="bi bi-list-check me-1"></i>My Bookings
    </a>
  </div>

  <!-- Stepper: seluruh tahap pengajuan hingga menunggu konfirmasi selesai -->
  <ol class="rm-stepper list-unstyled d-flex flex-wrap gap-3 small bg-white border rounded p-2 mb-4" id="stpProgress">
    <li><span class="rm-step-no done">1</span> Choose car</li>
    <li><span class="rm-step-no done">2</span> Select dates</li>
    <li><span class="rm-step-no done">3</span> Checkout &amp; payment</li>
    <li class="fw-semibold"><span class="rm-step-no done">4</span> Pending confirmation</li>
  </ol>

  <?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success d-flex align-items-center small" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i>
      <div><?= e($flashSuccess) ?></div>
    </div>
  <?php endif; ?>

  <div class="row justify-content-center">
    <div class="col-lg-8">
      <section class="card rm-card">
        <div class="card-body p-4">
          <div class="text-center pb-3 mb-4 border-bottom">
            <?php if ($statusBayar === 'Lunas'): ?>
              <div class="rm-status-circle rm-status-ok mx-auto mb-3"><i class="bi bi-check-lg"></i></div>
              <h2 class="h5 fw-bold mb-1">Pembayaran Terverifikasi</h2>
              <p class="small text-secondary mb-2">Pembayaran Anda telah diverifikasi oleh admin RentAMobil.</p>
            <?php elseif ($statusBayar === 'Ditolak'): ?>
              <div class="rm-status-circle rm-status-cancel mx-auto mb-3"><i class="bi bi-x-lg"></i></div>
              <h2 class="h5 fw-bold mb-1">Pembayaran Perlu Diperbaiki</h2>
              <p class="small text-secondary mb-2">Bukti pembayaran Anda ditolak oleh admin. Silakan unggah ulang bukti transfer yang valid.</p>
            <?php else: ?>
              <div class="rm-status-circle rm-status-wait mx-auto mb-3"><i class="bi bi-hourglass-split"></i></div>
              <h2 class="h5 fw-bold mb-1">Pembayaran Berhasil Dikirim</h2>
              <p class="small text-secondary mb-2">
                Bukti transfer Anda telah diterima dan sedang <strong>menunggu verifikasi</strong> oleh admin (maksimal 1&times;24 jam).
              </p>
            <?php endif; ?>
            <span class="badge rm-badge <?= $kelasStatusBayar ?> px-3 py-2"><?= e($labelStatusBayar) ?></span>
          </div>

          <div class="row g-4 align-items-start">
            <div class="col-md-7">
              <h3 class="h6 fw-bold mb-3">Ringkasan Transaksi</h3>
              <dl class="row small mb-0 rm-spec">
                <dt class="col-5 fw-normal text-secondary">ID Rental</dt>
                <dd class="col-7 text-end fw-semibold"><?= e($pesanan['kode']) ?> <span class="text-secondary fw-normal">(#<?= (int)$pesanan['rental_id'] ?>)</span></dd>

                <dt class="col-5 fw-normal text-secondary">Mobil</dt>
                <dd class="col-7 text-end"><?= e($pesanan['nama_mobil']) ?> (<?= e($pesanan['plat_nomor']) ?>)</dd>

                <dt class="col-5 fw-normal text-secondary">Periode Rental</dt>
                <dd class="col-7 text-end"><?= e(rentangTanggal($pesanan['tanggal_mulai'], $pesanan['tanggal_selesai'])) ?> &bull; <?= (int)$pesanan['durasi'] ?> hari</dd>

                <dt class="col-5 fw-normal text-secondary">Metode Pembayaran</dt>
                <dd class="col-7 text-end fw-semibold"><?= e($pesanan['metode_pembayaran'] ?? '-') ?></dd>

                <dt class="col-5 fw-normal text-secondary">Jumlah Dibayarkan</dt>
                <dd class="col-7 text-end fw-bold rm-price"><?= rupiah((int)($pesanan['jumlah_bayar'] ?: $pesanan['total_harga'])) ?></dd>

                <dt class="col-5 fw-normal text-secondary">Status Pembayaran</dt>
                <dd class="col-7 text-end"><span class="badge rm-badge <?= $kelasStatusBayar ?>"><?= e($labelStatusBayar) ?></span></dd>

                <dt class="col-5 fw-normal text-secondary">Status Pesanan</dt>
                <dd class="col-7 text-end"><span class="badge rm-badge <?= $kelasStatusPesanan ?>"><?= e($labelStatusPesanan) ?></span></dd>
              </dl>
            </div>

            <div class="col-md-5">
              <h3 class="h6 fw-bold mb-2">Bukti Transfer</h3>
              <?php if (!empty($pesanan['bukti_url'])): ?>
                <div class="border rounded p-2 bg-light text-center">
                  <img src="<?= e($pesanan['bukti_url']) ?>" alt="Bukti transfer pesanan <?= e($pesanan['kode']) ?>" class="rm-preview-img">
                </div>
              <?php else: ?>
                <div class="border rounded p-3 bg-light text-center small text-secondary">
                  <i class="bi bi-file-earmark-image fs-3 d-block mb-1"></i>
                  Bukti transfer tercatat: <code><?= e(basename((string)($pesanan['bukti_transfer'] ?? '-'))) ?></code>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <hr class="my-4">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <?php if ($statusBayar === 'Ditolak'): ?>
              <a href="pembayaran.php?rental_id=<?= (int)$pesanan['rental_id'] ?>" class="btn btn-rm btn-sm">
                <i class="bi bi-upload me-1"></i>Unggah Ulang Bukti Pembayaran
              </a>
            <?php else: ?>
              <a href="my_bookings.php" class="btn btn-rm btn-sm">
                <i class="bi bi-card-checklist me-1"></i>Lihat Status di My Bookings
              </a>
            <?php endif; ?>
            <a href="katalog.php" class="btn btn-outline-dark btn-sm">Kembali ke Katalog</a>
          </div>
        </div>
      </section>
    </div>
  </div>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

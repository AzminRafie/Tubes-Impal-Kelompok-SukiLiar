<?php
/** View (MVC) - Page My Bookings & History (P009 / P009A). Data dari RentalController. */
$judul = 'My Bookings & History';
$menuAktif = 'bookings';
$labelTransmisi = ['Manual' => 'Manual', 'Matic' => 'Automatic'];
$tahap = ['Submitted', 'Verification', 'Confirmed', 'Pickup', 'Return'];
require __DIR__ . '/partials/header.php';
?>

<main class="container py-4">
  <div class="d-flex justify-content-between align-items-start mb-3">
    <div>
      <h1 class="h4 fw-bold mb-1" id="lblPageTitle">My Bookings &amp; History</h1>
      <p class="text-secondary small mb-0">Track your current rentals and review your past bookings.</p>
    </div>
    <a href="katalog.php" id="btnBrowseCars" class="btn btn-rm btn-sm">Browse Cars</a>
  </div>

<?php if (!$aktif && !$riwayat): ?>
  <!-- P009A: belum pernah memesan -->
  <section class="card rm-card text-center py-5">
    <i class="bi bi-car-front fs-1 text-secondary"></i>
    <p class="fw-semibold mt-2 mb-1" id="lblEmptyTitle">You haven't booked any cars yet</p>
    <p class="text-secondary small">Find the right car for your next trip around Bandung. Your bookings and rental history will appear here.</p>
    <div><a href="katalog.php" class="btn btn-rm btn-sm">Browse Cars</a></div>
  </section>
<?php else: ?>

  <?php if ($banner): ?>
    <div class="alert alert-primary small" id="bannerStatus"><?= e($banner) ?></div>
  <?php endif; ?>

  <section class="card rm-card mb-4">
    <div class="card-header bg-light fw-semibold small" id="lblActiveTitle">Active rentals (<?= count($aktif) ?>)</div>
    <?php if ($aktif): ?>
    <div class="table-responsive">
      <table class="table align-middle small mb-0" id="tblActive">
        <thead class="table-light"><tr><th>Booking ID</th><th>Car</th><th>Rental period</th><th>Total</th><th>Status tracker</th></tr></thead>
        <tbody>
        <?php foreach ($aktif as $r):
          [$label, $kelas] = badgeStatus($r['status_pesanan']);
          $idx = tahapPesanan($r['status_pesanan']); ?>
          <tr>
            <td class="text-nowrap"><?= e($r['kode']) ?></td>
            <td><?= e($r['nama_mobil']) ?><br><span class="text-secondary"><?= e($labelTransmisi[$r['transmisi']] ?? $r['transmisi']) ?> &bull; Bandung</span></td>
            <td><?= e(rentangTanggal($r['tanggal_mulai'], $r['tanggal_selesai'])) ?><br><span class="text-secondary">09:00 &rarr; 09:00<br><?= $r['durasi'] ?> day<?= $r['durasi'] > 1 ? 's' : '' ?></span></td>
            <td><?= rupiah((int)$r['total_harga']) ?><br><span class="text-secondary"><?= $r['durasi'] ?> × <?= rupiah((int)$r['harga_perhari']) ?></span></td>
            <td>
              <span class="badge rm-badge <?= $kelas ?> mb-1"><?= e($label) ?></span>
              <div class="rm-tracker" aria-label="Status: <?= e($label) ?>">
                <?php foreach ($tahap as $i => $nama):
                  $mark = $i < $idx ? '✓' : ($i === $idx ? '●' : '○'); ?>
                  <span class="<?= $i < $idx ? 'done' : ($i === $idx ? 'now' : '') ?>"><?= $mark ?> <?= e($nama) ?></span>
                <?php endforeach; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="small text-secondary px-3 py-2 mb-0">✓ Complete &bull; ● Current stage &bull; ○ Not started</p>
    <?php else: ?>
      <p class="small text-secondary p-3 mb-0">No active rentals.</p>
    <?php endif; ?>
  </section>

  <section class="card rm-card">
    <div class="card-header bg-light fw-semibold small" id="lblHistoryTitle">Past rental history (<?= count($riwayat) ?>)</div>
    <?php if ($riwayat): ?>
    <div class="table-responsive">
      <table class="table align-middle small mb-0" id="tblHistory">
        <thead class="table-light"><tr><th>Booking ID</th><th>Car</th><th>Rental period</th><th>Total paid</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($riwayat as $r):
          [$label, $kelas] = badgeStatus($r['status_pesanan']); ?>
          <tr>
            <td class="text-nowrap"><?= e($r['kode']) ?></td>
            <td><?= e($r['nama_mobil']) ?></td>
            <td><?= e(rentangTanggal($r['tanggal_mulai'], $r['tanggal_selesai'])) ?> &bull; <?= $r['durasi'] ?> day<?= $r['durasi'] > 1 ? 's' : '' ?></td>
            <td><?= rupiah((int)$r['jumlah_bayar']) ?></td>
            <td><span class="badge rm-badge <?= $kelas ?>"><?= e($label) ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <p class="small text-secondary p-3 mb-0">No past rentals yet.</p>
    <?php endif; ?>
  </section>
<?php endif; ?>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

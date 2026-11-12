<?php
/** View (MVC) - Page Detail Mobil dan Kalender (P006). Data dari KatalogController::detail(). */
$judul = $mobil['nama_mobil'];
$menuAktif = 'catalog';
$labelTransmisi = ['Manual' => 'Manual', 'Matic' => 'Automatic'];
$dataKalender = [
    'today'       => $hariIni,
    'blocked'     => $terblokir,
    'rate'        => (int)$mobil['harga_perhari'],
    'checkoutUrl' => 'checkout.php?id=' . (int)$mobil['mobil_id'],
];
require __DIR__ . '/partials/header.php';
?>

<main class="container py-4">
  <div class="d-flex justify-content-between align-items-start mb-3">
    <div>
      <h1 class="h4 fw-bold mb-1" id="lblCarTitle"><?= e($mobil['nama_mobil']) ?></h1>
      <nav aria-label="breadcrumb" class="small text-secondary">
        <a href="katalog.php" class="text-secondary text-decoration-none">Catalog</a> / <?= e($mobil['nama_mobil']) ?> &bull; Bandung
      </nav>
    </div>
    <a href="katalog.php" id="btnBackToCatalog" class="btn btn-outline-dark btn-sm">Back to Catalog</a>
  </div>

  <!-- Stepper: tahap "Select dates" aktif -->
  <ol class="rm-stepper list-unstyled d-flex flex-wrap gap-3 small bg-white border rounded p-2 mb-4" id="stpProgress">
    <li><span class="rm-step-no done">1</span> Choose car</li>
    <li class="fw-semibold"><span class="rm-step-no done">2</span> Select dates</li>
    <li class="text-secondary"><span class="rm-step-no">3</span> Checkout &amp; payment</li>
    <li class="text-secondary"><span class="rm-step-no">4</span> Pending confirmation</li>
  </ol>

  <div class="row g-4">
    <!-- Kiri: info mobil + persyaratan -->
    <div class="col-lg-5">
      <section class="card rm-card mb-3">
        <?php if ($mobil['foto_url']): ?>
          <img src="<?= e($mobil['foto_url']) ?>" class="card-img-top rm-photo" alt="<?= e($mobil['nama_mobil']) ?>">
        <?php else: ?>
          <div class="rm-photo rm-photo-empty" role="img" aria-label="Foto belum tersedia"><i class="bi bi-car-front"></i></div>
        <?php endif; ?>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="h6 fw-bold mb-0"><?= e($mobil['nama_mobil']) ?></h2>
            <span class="badge rm-badge rm-badge-ok">Available</span>
          </div>
          <dl class="row small mb-0 rm-spec">
            <dt class="col-6 fw-normal text-secondary">Transmission</dt>
            <dd class="col-6 text-end" id="lblTransmission"><?= e($labelTransmisi[$mobil['transmisi']] ?? $mobil['transmisi']) ?></dd>
            <dt class="col-6 fw-normal text-secondary">Capacity</dt>
            <dd class="col-6 text-end" id="lblCapacity"><?= (int)$mobil['kapasitas_kursi'] ?> seats</dd>
            <dt class="col-6 fw-normal text-secondary">License plate / Location</dt>
            <dd class="col-6 text-end" id="lblLocation"><?= e($mobil['plat_nomor']) ?> / Bandung</dd>
            <dt class="col-6 fw-bold">Daily rental rate</dt>
            <dd class="col-6 text-end fw-bold rm-price mb-0" id="lblDailyRate"><?= rupiah((int)$mobil['harga_perhari']) ?></dd>
          </dl>
        </div>
      </section>

      <section class="card rm-card" id="cardRequirements">
        <div class="card-header bg-light fw-semibold small">Rental requirements</div>
        <ul class="small mb-0 py-3">
          <li>Valid KTP or SIM for collateral verification</li>
          <li>Pickup address must be within Bandung</li>
          <li>Self-drive rental; fuel and tolls not included</li>
          <li>Payment via BCA bank transfer</li>
        </ul>
      </section>
    </div>

    <!-- Kanan: kalender ketersediaan -->
    <div class="col-lg-7">
      <section class="card rm-card">
        <div class="card-header bg-light fw-semibold small">Choose rental dates</div>
        <div class="card-body" id="calRental">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <button type="button" class="btn btn-outline-dark btn-sm" id="btnPrevMonth" aria-label="Previous month">&lsaquo;</button>
            <strong id="lblMonth" aria-live="polite"></strong>
            <button type="button" class="btn btn-outline-dark btn-sm" id="btnNextMonth" aria-label="Next month">&rsaquo;</button>
          </div>

          <div class="rm-cal-grid text-center small text-secondary mb-1" aria-hidden="true">
            <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
          </div>
          <div class="rm-cal-grid" id="calGrid"></div>

          <div class="d-flex gap-3 small text-secondary mt-3" id="lblCalendarLegend">
            <span><i class="rm-dot rm-dot-avail"></i> Available</span>
            <span><i class="rm-dot rm-dot-blocked"></i> Blocked</span>
            <span><i class="rm-dot rm-dot-sel"></i> Selected</span>
          </div>
          <p class="small text-secondary mb-3">Pilih tanggal Pickup dan Return (sewa minimal 24 jam / pengembalian minimal H+1). Tanggal merah/abu tidak dapat dipesan.</p>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label small fw-semibold mb-1" for="txtPickupDate">Pickup date</label>
              <input type="text" class="form-control form-control-sm" id="txtPickupDate" readonly placeholder="Select a date">
            </div>
            <div class="col-6">
              <label class="form-label small fw-semibold mb-1" for="txtReturnDate">Return date</label>
              <input type="text" class="form-control form-control-sm" id="txtReturnDate" readonly placeholder="Select a date">
            </div>
          </div>

          <dl class="row small mb-3">
            <dt class="col-6 fw-normal text-secondary">Rental duration</dt>
            <dd class="col-6 text-end" id="lblDuration">-</dd>
            <dt class="col-6 fw-normal text-secondary">Rate calculation</dt>
            <dd class="col-6 text-end" id="lblRateCalc">-</dd>
            <dt class="col-6 fw-bold">Total rental</dt>
            <dd class="col-6 text-end fw-bold fs-5 rm-price mb-0" id="lblTotalRental">Rp0</dd>
          </dl>

          <button type="button" class="btn btn-rm w-100" id="btnProceedCheckout">Proceed to Checkout</button>
          <p class="text-danger small mt-2 mb-0" id="msgDateBooked" role="alert"></p>
        </div>
      </section>

      <div class="alert alert-primary small mt-3 mb-0" id="lblInfoBanner">
        Your booking is confirmed only after the admin verifies your payment and collateral.
      </div>
    </div>
  </div>
</main>

<!-- Toast "Invalid input" -->
<div class="toast-container position-fixed top-0 end-0 p-3 mt-5">
  <div id="toastError" class="toast rm-toast-error" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
    <div class="d-flex align-items-center">
      <div class="toast-body"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Invalid input</div>
      <button type="button" class="btn-close me-2 ms-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
<script id="calData" type="application/json"><?= json_encode($dataKalender, JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script src="assets/js/kalender.js"></script>
</body>
</html>

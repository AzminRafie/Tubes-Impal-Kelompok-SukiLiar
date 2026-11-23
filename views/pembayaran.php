<?php
/** View (MVC) - Page Pembayaran & Upload Bukti Transfer. Data dari public/pembayaran.php. */
$judul = 'Pembayaran ' . $pesanan['kode'];
$menuAktif = 'bookings';
[$labelStatusPesanan, $kelasStatusPesanan] = badgeStatus($pesanan['status_pesanan']);
[$labelStatusBayar, $kelasStatusBayar]     = badgePembayaran($pesanan['status_pembayaran'] ?? null, $pesanan['bukti_transfer'] ?? null);
require __DIR__ . '/partials/header.php';
?>

<main class="container py-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
      <h1 class="h4 fw-bold mb-1">Pembayaran Pesanan</h1>
      <nav aria-label="breadcrumb" class="small text-secondary">
        <a href="my_bookings.php" class="text-secondary text-decoration-none">My Bookings</a> /
        <?= e($pesanan['kode']) ?> / Pembayaran
      </nav>
    </div>
    <a href="my_bookings.php" class="btn btn-outline-dark btn-sm">
      <i class="bi bi-arrow-left me-1"></i>Kembali ke My Bookings
    </a>
  </div>

  <!-- Stepper -->
  <ol class="rm-stepper list-unstyled d-flex flex-wrap gap-3 small bg-white border rounded p-2 mb-4" id="stpProgress">
    <li><span class="rm-step-no done">1</span> Choose car</li>
    <li><span class="rm-step-no done">2</span> Select dates</li>
    <li class="fw-semibold"><span class="rm-step-no done">3</span> Checkout &amp; payment</li>
    <li class="text-secondary"><span class="rm-step-no">4</span> Pending confirmation</li>
  </ol>

  <?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success d-flex align-items-center small" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i>
      <div><?= e($flashSuccess) ?></div>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger d-flex align-items-center small" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <div><?= e($error) ?></div>
    </div>
  <?php endif; ?>

  <div class="row g-4">
    <!-- Kiri: Ringkasan Pesanan & Tagihan -->
    <div class="col-lg-5">
      <section class="card rm-card mb-3">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <span class="fw-semibold small">Detail Pesanan</span>
          <span class="badge rm-badge <?= $kelasStatusBayar ?>"><?= e($labelStatusBayar) ?></span>
        </div>
        <div class="card-body">
          <div class="d-flex align-items-center gap-3 mb-3">
            <?php if (!empty($pesanan['foto_url'])): ?>
              <img src="<?= e($pesanan['foto_url']) ?>" alt="<?= e($pesanan['nama_mobil']) ?>" class="rm-thumb rounded border">
            <?php else: ?>
              <div class="rm-thumb rm-photo-empty rounded border" aria-hidden="true"><i class="bi bi-car-front fs-4"></i></div>
            <?php endif; ?>
            <div>
              <div class="fw-bold"><?= e($pesanan['nama_mobil']) ?></div>
              <div class="small text-secondary"><?= e($pesanan['plat_nomor']) ?> &bull; <?= e($pesanan['transmisi']) ?></div>
            </div>
          </div>

          <dl class="row small mb-0 rm-spec">
            <dt class="col-5 fw-normal text-secondary">Nomor Pesanan</dt>
            <dd class="col-7 text-end fw-semibold"><?= e($pesanan['kode']) ?> <span class="text-secondary fw-normal">(#<?= (int)$pesanan['rental_id'] ?>)</span></dd>
            <dt class="col-5 fw-normal text-secondary">Periode Rental</dt>
            <dd class="col-7 text-end"><?= e(rentangTanggal($pesanan['tanggal_mulai'], $pesanan['tanggal_selesai'])) ?> (<?= (int)$pesanan['durasi'] ?> hari)</dd>
            <dt class="col-5 fw-normal text-secondary">Status Pesanan</dt>
            <dd class="col-7 text-end"><span class="badge rm-badge <?= $kelasStatusPesanan ?>"><?= e($labelStatusPesanan) ?></span></dd>
            <dt class="col-5 fw-normal text-secondary">Metode Dipilih</dt>
            <dd class="col-7 text-end fw-semibold" id="lblSelectedMethod"><?= e($metodeTerpilih) ?></dd>
          </dl>

          <hr class="my-3">
          <div class="d-flex justify-content-between align-items-center">
            <span class="fw-bold small">Total Tagihan</span>
            <div class="d-flex align-items-center gap-2">
              <span class="fw-bold fs-4 rm-price" id="lblTotalBill"><?= rupiah((int)$pesanan['total_harga']) ?></span>
              <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 rm-copy-btn"
                      data-copy="<?= (int)$pesanan['total_harga'] ?>" title="Salin nominal">
                <i class="bi bi-clipboard"></i>
              </button>
            </div>
          </div>
          <div class="small text-secondary mt-1">Pastikan nominal transfer sesuai persis dengan total tagihan di atas.</div>
        </div>
      </section>

      <?php if (!empty($pesanan['bukti_transfer'])): ?>
      <section class="card rm-card">
        <div class="card-header bg-light fw-semibold small">Bukti Transfer Terakhir</div>
        <div class="card-body small">
          <p class="mb-2 text-secondary">
            Anda telah mengunggah bukti pembayaran sebelumnya (<strong><?= e($pesanan['metode_pembayaran'] ?? '-') ?></strong>).
            Mengunggah file baru akan menggantikan bukti sebelumnya dan tetap berstatus <strong>Menunggu Verifikasi</strong>.
          </p>
          <?php if (!empty($pesanan['bukti_url'])): ?>
            <img src="<?= e($pesanan['bukti_url']) ?>" alt="Bukti transfer sebelumnya" class="rm-preview-img">
          <?php endif; ?>
        </div>
      </section>
      <?php endif; ?>
    </div>

    <!-- Kanan: Pilihan Metode, Instruksi, & Upload Bukti Transfer -->
    <div class="col-lg-7">
      <form method="post" action="pembayaran.php" enctype="multipart/form-data" id="formPembayaran" class="rm-submit-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
        <input type="hidden" name="rental_id" value="<?= (int)$pesanan['rental_id'] ?>">

        <!-- Bagian 1: Pilih Metode Pembayaran -->
        <section class="card rm-card mb-3">
          <div class="card-header bg-light fw-semibold small">1. Pilih Metode Pembayaran (Transfer Bank Manual)</div>
          <div class="card-body">
            <div class="row g-2 mb-3" role="radiogroup" aria-label="Metode pembayaran">
              <?php foreach ($rekeningList as $metodeKey => $rek):
                $checked = ($metodeTerpilih === $metodeKey);
                $inputId = 'metode_' . strtolower($rek['kode']);
              ?>
                <div class="col-md-4">
                  <input type="radio" class="btn-check rm-method-radio" name="metode_pembayaran"
                         id="<?= e($inputId) ?>" value="<?= e($metodeKey) ?>"
                         <?= $checked ? 'checked' : '' ?> required>
                  <label class="rm-method-card w-100 h-100" for="<?= e($inputId) ?>">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <span class="rm-bank-pill"><?= e($rek['kode']) ?></span>
                      <i class="bi bi-check-circle-fill rm-method-check"></i>
                    </div>
                    <div class="fw-semibold small"><?= e($rek['nama_metode']) ?></div>
                    <div class="text-secondary" style="font-size:.75rem;"><?= e($rek['nama_bank']) ?></div>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Panel Instruksi Dinamis per Metode -->
            <?php foreach ($rekeningList as $metodeKey => $rek):
              $isVisible = ($metodeTerpilih === $metodeKey);
              $hasRekening = trim((string)($rek['nomor_rekening'] ?? '')) !== '';
            ?>
              <div class="rm-bank-panel border rounded p-3 bg-light <?= $isVisible ? '' : 'd-none' ?>"
                   data-bank-panel="<?= e($metodeKey) ?>">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="fw-semibold small"><i class="bi bi-bank me-1"></i>Instruksi <?= e($rek['nama_metode']) ?></span>
                  <span class="small text-secondary"><?= e($rek['nama_bank']) ?></span>
                </div>

                <?php if ($hasRekening): ?>
                  <div class="bg-white border rounded p-2 px-3 mb-3">
                    <div class="row g-2 align-items-center small">
                      <div class="col-sm-7">
                        <div class="text-secondary" style="font-size:.75rem;">Nomor Rekening Resmi</div>
                        <div class="fw-bold fs-6 rm-price font-monospace"><?= e($rek['nomor_rekening']) ?></div>
                        <?php if (!empty($rek['atas_nama'])): ?>
                          <div class="text-secondary">a.n. <strong class="text-dark"><?= e($rek['atas_nama']) ?></strong></div>
                        <?php endif; ?>
                      </div>
                      <div class="col-sm-5 text-sm-end">
                        <button type="button" class="btn btn-outline-dark btn-sm rm-copy-btn"
                                data-copy="<?= e($rek['nomor_rekening']) ?>">
                          <i class="bi bi-clipboard me-1"></i>Salin No. Rekening
                        </button>
                      </div>
                    </div>
                  </div>
                <?php else: ?>
                  <div class="alert alert-warning small py-2 px-3 mb-3" role="status">
                    <div class="fw-semibold mb-1">
                      <i class="bi bi-exclamation-circle me-1"></i>Nomor Rekening Resmi Belum Dikonfigurasi
                    </div>
                    Informasi rekening tujuan resmi untuk <strong><?= e($rek['nama_bank']) ?></strong> belum diisi oleh admin.
                    Silakan hubungi admin RentAMobil (<code>support@rentamobil.example</code>) untuk mendapatkan nomor rekening resmi sebelum melakukan transfer.
                  </div>
                <?php endif; ?>

                <ol class="small text-secondary mb-0 ps-3">
                  <?php foreach ($rek['instruksi'] as $langkah): ?>
                    <li class="mb-1"><?= e($langkah) ?></li>
                  <?php endforeach; ?>
                </ol>
              </div>
            <?php endforeach; ?>
          </div>
        </section>

        <!-- Bagian 2: Upload Bukti Transfer -->
        <section class="card rm-card">
          <div class="card-header bg-light fw-semibold small">2. Unggah Bukti Transfer</div>
          <div class="card-body">
            <div class="rm-upload-zone mb-3" data-upload-zone="bukti">
              <input type="file" name="bukti_transfer" id="fileBukti" class="d-none rm-upload-input"
                     accept=".jpg,.jpeg,.png,image/jpeg,image/png" required>
              <div class="rm-upload-placeholder">
                <i class="bi bi-cloud-arrow-up rm-upload-icon"></i>
                <p class="fw-semibold small mb-1">Seret gambar ke sini atau klik tombol di bawah</p>
                <p class="text-secondary small mb-2">Bukti transfer berformat JPG, JPEG, atau PNG &bull; Maksimal 5 MB</p>
                <button type="button" class="btn btn-outline-dark btn-sm rm-upload-trigger">
                  <i class="bi bi-image me-1"></i>Pilih Bukti Pembayaran
                </button>
              </div>
              <div class="rm-upload-preview d-none">
                <img src="" alt="Preview bukti transfer" class="rm-preview-img mb-2">
                <div class="small fw-semibold rm-file-name"></div>
                <div class="small text-secondary rm-file-size mb-2"></div>
                <button type="button" class="btn btn-outline-dark btn-sm rm-upload-trigger">
                  <i class="bi bi-arrow-repeat me-1"></i>Ganti File
                </button>
              </div>
            </div>
            <div class="text-danger small mb-3 d-none rm-upload-error" role="alert"></div>

            <div class="alert alert-primary small mb-3">
              <i class="bi bi-clock-history me-1"></i>
              Setelah bukti pembayaran dikirim, status pembayaran akan tercatat sebagai <strong>Menunggu Verifikasi</strong> hingga diperiksa oleh admin.
            </div>

            <button type="submit" class="btn btn-rm w-100" id="btnKirimBukti" data-loading-text="Mengirim bukti pembayaran...">
              <i class="bi bi-send-check me-1"></i>Kirim Bukti Pembayaran
            </button>
          </div>
        </section>
      </form>
    </div>
  </div>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
<script src="assets/js/transaksi.js"></script>
</body>
</html>

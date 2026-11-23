<?php
/** View (MVC) - Page Checkout & Upload KTP. Data dari public/checkout.php. */
$judul = 'Checkout - ' . $mobil['nama_mobil'];
$menuAktif = 'catalog';
$labelTransmisi = ['Manual' => 'Manual', 'Matic' => 'Automatic'];
require __DIR__ . '/partials/header.php';
?>

<main class="container py-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
      <h1 class="h4 fw-bold mb-1">Checkout Pesanan</h1>
      <nav aria-label="breadcrumb" class="small text-secondary">
        <a href="katalog.php" class="text-secondary text-decoration-none">Catalog</a> /
        <a href="detail_mobil.php?id=<?= (int)$mobil['mobil_id'] ?>" class="text-secondary text-decoration-none"><?= e($mobil['nama_mobil']) ?></a> /
        Checkout
      </nav>
    </div>
    <a href="detail_mobil.php?id=<?= (int)$mobil['mobil_id'] ?>" class="btn btn-outline-dark btn-sm">
      <i class="bi bi-arrow-left me-1"></i>Ubah Tanggal
    </a>
  </div>

  <!-- Stepper konsisten dengan detail_mobil.php -->
  <ol class="rm-stepper list-unstyled d-flex flex-wrap gap-3 small bg-white border rounded p-2 mb-4" id="stpProgress">
    <li><span class="rm-step-no done">1</span> Choose car</li>
    <li><span class="rm-step-no done">2</span> Select dates</li>
    <li class="fw-semibold"><span class="rm-step-no done">3</span> Checkout &amp; payment</li>
    <li class="text-secondary"><span class="rm-step-no">4</span> Pending confirmation</li>
  </ol>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger d-flex align-items-center small" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <div><?= e($error) ?></div>
    </div>
  <?php endif; ?>

  <div class="row g-4">
    <!-- Kiri: Ringkasan Pesanan -->
    <div class="col-lg-5">
      <section class="card rm-card mb-3">
        <?php if (!empty($mobil['foto_url'])): ?>
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
            <dt class="col-6 fw-normal text-secondary">Nomor Polisi</dt>
            <dd class="col-6 text-end"><?= e($mobil['plat_nomor']) ?></dd>
            <dt class="col-6 fw-normal text-secondary">Transmisi &amp; Kapasitas</dt>
            <dd class="col-6 text-end"><?= e($labelTransmisi[$mobil['transmisi']] ?? $mobil['transmisi']) ?> &bull; <?= (int)$mobil['kapasitas_kursi'] ?> seats</dd>
            <dt class="col-6 fw-normal text-secondary">Harga per Hari</dt>
            <dd class="col-6 text-end fw-semibold"><?= rupiah((int)$mobil['harga_perhari']) ?></dd>
          </dl>
        </div>
      </section>

      <section class="card rm-card">
        <div class="card-header bg-light fw-semibold small">Ringkasan Jadwal &amp; Biaya</div>
        <div class="card-body">
          <dl class="row small mb-0 rm-spec">
            <dt class="col-6 fw-normal text-secondary">Tanggal Mulai (Pickup)</dt>
            <dd class="col-6 text-end"><?= e(formatTanggalPendek($mulai)) ?>, 09:00</dd>
            <dt class="col-6 fw-normal text-secondary">Tanggal Selesai (Return)</dt>
            <dd class="col-6 text-end"><?= e(formatTanggalPendek($selesai)) ?>, 09:00</dd>
            <dt class="col-6 fw-normal text-secondary">Durasi Rental</dt>
            <dd class="col-6 text-end"><?= $durasi ?> hari (<?= $durasi * 24 ?> jam)</dd>
            <dt class="col-6 fw-normal text-secondary">Perhitungan Tarif</dt>
            <dd class="col-6 text-end"><?= $durasi ?> &times; <?= rupiah((int)$mobil['harga_perhari']) ?></dd>
          </dl>
          <hr class="my-2">
          <div class="d-flex justify-content-between align-items-center">
            <span class="fw-bold small">Total Biaya Rental</span>
            <span class="fw-bold fs-5 rm-price"><?= rupiah($totalHarga) ?></span>
          </div>
        </div>
      </section>
    </div>

    <!-- Kanan: Formulir Alamat Penjemputan & Upload KTP -->
    <div class="col-lg-7">
      <section class="card rm-card">
        <div class="card-header bg-light fw-semibold small">Data Penjemputan &amp; Dokumen Jaminan</div>
        <div class="card-body">
          <form method="post" action="checkout.php" enctype="multipart/form-data" id="formCheckout" class="rm-submit-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="mobil_id" value="<?= (int)$mobil['mobil_id'] ?>">
            <input type="hidden" name="tanggal_mulai" value="<?= e($mulai) ?>">
            <input type="hidden" name="tanggal_selesai" value="<?= e($selesai) ?>">

            <div class="mb-3">
              <label for="txtAlamat" class="form-label small fw-semibold">
                Alamat Penjemputan / Pengantaran <span class="text-danger">*</span>
              </label>
              <textarea name="alamat_penjemputan" id="txtAlamat" rows="3" maxlength="500" required
                        class="form-control form-control-sm"
                        placeholder="Contoh: Jl. Dago No. 20, Coblong, Kota Bandung (patokan dekat minimarket)"><?= e($alamat) ?></textarea>
              <div class="form-text small">Masukkan alamat lengkap di area Bandung agar tim kami mudah melakukan serah terima unit.</div>
            </div>

            <div class="mb-4">
              <label for="fileKtp" class="form-label small fw-semibold">
                Upload Foto KTP (Jaminan) <span class="text-danger">*</span>
              </label>
              <div class="rm-upload-zone" data-upload-zone="ktp">
                <input type="file" name="foto_ktp" id="fileKtp" class="d-none rm-upload-input"
                       accept=".jpg,.jpeg,.png,image/jpeg,image/png" required>
                <div class="rm-upload-placeholder">
                  <i class="bi bi-person-vcard rm-upload-icon"></i>
                  <p class="fw-semibold small mb-1">Unggah foto KTP asli yang jelas terbaca</p>
                  <p class="text-secondary small mb-2">Format JPG, JPEG, atau PNG &bull; Maksimal 5 MB</p>
                  <button type="button" class="btn btn-outline-dark btn-sm rm-upload-trigger">
                    <i class="bi bi-upload me-1"></i>Pilih File KTP
                  </button>
                </div>
                <div class="rm-upload-preview d-none">
                  <img src="" alt="Preview KTP" class="rm-preview-img mb-2">
                  <div class="small fw-semibold rm-file-name"></div>
                  <div class="small text-secondary rm-file-size mb-2"></div>
                  <button type="button" class="btn btn-outline-dark btn-sm rm-upload-trigger">
                    <i class="bi bi-arrow-repeat me-1"></i>Ganti File
                  </button>
                </div>
              </div>
              <div class="text-danger small mt-1 d-none rm-upload-error" role="alert"></div>
            </div>

            <div class="alert alert-primary small mb-3">
              <i class="bi bi-info-circle me-1"></i>
              Setelah pesanan dibuat, Anda akan diarahkan ke halaman pembayaran untuk memilih metode transfer bank dan mengunggah bukti transfer.
            </div>

            <button type="submit" class="btn btn-rm w-100" id="btnBuatPesanan" data-loading-text="Memproses pesanan...">
              <i class="bi bi-shield-check me-1"></i>Buat Pesanan &amp; Lanjut ke Pembayaran
            </button>
          </form>
        </div>
      </section>
    </div>
  </div>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
<script src="assets/js/transaksi.js"></script>
</body>
</html>

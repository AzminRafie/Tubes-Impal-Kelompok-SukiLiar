<?php
/** View (MVC) - Page Dashboard Pencarian (P005). Variabel datang dari KatalogController. */
// URL halaman tertentu dengan filter yang sedang aktif tetap terbawa
function urlHalaman(int $page, array $filter, string $sort): string
{
    $q = array_filter($filter + ['sort' => $sort, 'page' => $page], fn($v) => $v !== '' && $v !== 1 && $v !== 'recommended');
    return 'katalog.php' . ($q ? '?' . http_build_query($q) : '');
}

$labelTransmisi = ['Manual' => 'Manual', 'Matic' => 'Automatic'];
$labelStatus    = ['Tersedia' => 'Available', 'Disewa' => 'Rented'];
$namaUser       = $_SESSION['nama'] ?? 'Account';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Car Rental in Bandung | RentAMobil</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="assets/css/katalog.css" rel="stylesheet">
</head>
<body>

<!-- Navbar: logo, pencarian, filter, menu akun -->
<nav class="navbar navbar-expand-lg rm-navbar">
  <div class="container">
    <a class="navbar-brand fw-bold" href="katalog.php"><i class="bi bi-car-front me-2"></i>RentAMobil</a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-label="Menu">
      <i class="bi bi-list text-white fs-3"></i>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      <form id="filterForm" class="d-flex flex-column flex-lg-row gap-2 mx-lg-3 flex-grow-1 py-2 py-lg-0" method="get" action="katalog.php" role="search">
        <div class="input-group input-group-sm rm-search">
          <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
          <input type="search" name="q" id="txtSearchCar" class="form-control border-0" maxlength="100"
                 placeholder="Search car name..." value="<?= e($filter['q']) ?>" aria-label="Search car name">
        </div>
        <select name="transmisi" id="dropTransmission" class="form-select form-select-sm rm-select" aria-label="Transmission">
          <option value="">All transmissions</option>
          <?php foreach ($labelTransmisi as $val => $label): ?>
            <option value="<?= e($val) ?>" <?= $filter['transmisi'] === $val ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="status" id="dropAvailability" class="form-select form-select-sm rm-select" aria-label="Availability">
          <option value="">All availability</option>
          <?php foreach ($labelStatus as $val => $label): ?>
            <option value="<?= e($val) ?>" <?= $filter['status'] === $val ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </form>

      <div class="dropdown" id="ddlUserMenu">
        <a class="nav-link dropdown-toggle text-white small" href="#" data-bs-toggle="dropdown" aria-expanded="false">
          <?= e($namaUser) ?>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
          <li><a class="dropdown-item" href="my_bookings.php">My Bookings</a></li>
          <li><a class="dropdown-item" href="profil.php">Settings</a></li>
          <li><a class="dropdown-item" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<main class="container py-4">
  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible small" role="alert">
      <?= e($_SESSION['flash_error']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>
  <header class="mb-3">
    <h1 class="h4 fw-bold mb-1" id="lblPageTitle">Car Rental in Bandung</h1>
    <p class="text-secondary small mb-0">Choose a car for your trip. Daily rates include the vehicle only; fuel is not included.</p>
  </header>

  <div class="d-flex justify-content-between align-items-center mb-3 small">
    <span id="lblResultCount" class="fw-semibold">
      <?= $ringkasan['total'] ?> cars found &bull; <?= $ringkasan['tersedia'] ?> available
    </span>
    <label class="text-secondary d-flex align-items-center gap-2 mb-0">
      Sort by:
      <select name="sort" id="dropSort" form="filterForm" class="form-select form-select-sm w-auto">
        <?php foreach (MobilModel::SORT_OPTIONS as $key => $opt): ?>
          <option value="<?= e($key) ?>" <?= $sort === $key ? 'selected' : '' ?>><?= e($opt['label']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
  </div>

  <?php if (!$mobil): ?>
    <!-- Alternative flow 1: mobil tidak ditemukan -->
    <div class="text-center py-5 border rounded bg-white">
      <i class="bi bi-search fs-1 text-secondary"></i>
      <p class="fw-semibold mt-2 mb-1">Mobil tidak ditemukan</p>
      <p class="text-secondary small">Coba kata kunci lain atau hapus filter yang aktif.</p>
      <a href="katalog.php" class="btn btn-rm btn-sm">Tampilkan semua mobil</a>
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($mobil as $m):
        $tersedia = $m['status_sediaan'] === 'Tersedia'; ?>
        <div class="col-12 col-md-6 col-lg-4">
          <article class="card h-100 rm-card">
            <?php if ($m['foto_url']): ?>
              <img src="<?= e($m['foto_url']) ?>" class="card-img-top rm-photo" alt="<?= e($m['nama_mobil']) ?>" loading="lazy">
            <?php else: ?>
              <div class="rm-photo rm-photo-empty" role="img" aria-label="Foto belum tersedia"><i class="bi bi-car-front"></i></div>
            <?php endif; ?>

            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <h2 class="h6 fw-bold mb-1" id="lblCarName"><?= e($m['nama_mobil']) ?></h2>
                <span class="badge rm-badge <?= $tersedia ? 'rm-badge-ok' : 'rm-badge-rented' ?>">
                  <?= e($labelStatus[$m['status_sediaan']] ?? $m['status_sediaan']) ?>
                </span>
              </div>
              <p class="text-secondary small mb-0">
                <?= e($labelTransmisi[$m['transmisi']] ?? $m['transmisi']) ?> &bull; <?= (int)$m['kapasitas_kursi'] ?> seats
              </p>
              <p class="text-secondary small mb-3">Bandung &bull; Self-drive</p>

              <div class="d-flex justify-content-between align-items-center">
                <div><span class="fw-bold rm-price"><?= rupiah((int)$m['harga_perhari']) ?></span> <small class="text-secondary">/ day</small></div>
                <?php if ($tersedia): ?>
                  <a href="detail_mobil.php?id=<?= (int)$m['mobil_id'] ?>" class="btn btn-rm btn-sm px-3">Rent Now</a>
                <?php else: ?>
                  <!-- Alternative flow 3: status Rented -> tombol nonaktif -->
                  <button type="button" class="btn btn-sm btn-light border px-3" disabled>Rent Now</button>
                <?php endif; ?>
              </div>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($totalHalaman > 1): ?>
      <nav class="mt-4" aria-label="Catalog pages">
        <ul class="pagination pagination-sm justify-content-center rm-pagination">
          <li class="page-item <?= $halaman <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e(urlHalaman($halaman - 1, $filter, $sort)) ?>">Previous</a>
          </li>
          <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
            <li class="page-item <?= $i === $halaman ? 'active' : '' ?>">
              <a class="page-link" href="<?= e(urlHalaman($i, $filter, $sort)) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e(urlHalaman($halaman + 1, $filter, $sort)) ?>">Next</a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</main>

<footer class="border-top bg-white py-3 mt-4">
  <div class="container d-flex justify-content-between small text-secondary">
    <span>&copy; 2026 RentAMobil &bull; Bandung</span>
    <span>Need help? support@rentamobil.example</span>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/katalog.js"></script>
</body>
</html>

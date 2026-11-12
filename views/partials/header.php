<?php /** Kepala halaman + navbar sederhana. Variabel: $judul, $menuAktif ('catalog'|'bookings'). */
$namaUser = $_SESSION['nama'] ?? 'Account';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($judul) ?> | RentAMobil</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="assets/css/katalog.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand rm-navbar">
  <div class="container">
    <a class="navbar-brand fw-bold" href="katalog.php"><i class="bi bi-car-front me-2"></i>RentAMobil</a>
    <ul class="navbar-nav me-auto small">
      <li class="nav-item"><a class="nav-link text-white <?= ($menuAktif ?? '') === 'catalog' ? 'fw-semibold' : 'opacity-75' ?>" href="katalog.php">Catalog</a></li>
      <li class="nav-item"><a class="nav-link text-white <?= ($menuAktif ?? '') === 'bookings' ? 'fw-semibold' : 'opacity-75' ?>" href="my_bookings.php">My Bookings</a></li>
    </ul>
    <div class="dropdown" id="ddlUserMenu">
      <a class="nav-link dropdown-toggle text-white small" href="#" data-bs-toggle="dropdown" aria-expanded="false"><?= e($namaUser) ?></a>
      <ul class="dropdown-menu dropdown-menu-end shadow-sm">
        <li><a class="dropdown-item" href="my_bookings.php">My Bookings</a></li>
        <li><a class="dropdown-item" href="profil.php">Settings</a></li>
        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

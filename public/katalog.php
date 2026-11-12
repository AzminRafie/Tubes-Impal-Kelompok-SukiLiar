<?php
// Entry point halaman Catalog (P005)
session_start();

// Precondition use case #5: user sudah login
if (empty($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../controllers/KatalogController.php';

$data = (new KatalogController())->index();
extract($data);

require __DIR__ . '/../views/katalog.php';

<?php
// Entry point Page Detail Mobil dan Kalender (P006)
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../controllers/KatalogController.php';

extract((new KatalogController())->detail());
require __DIR__ . '/../views/detail_mobil.php';

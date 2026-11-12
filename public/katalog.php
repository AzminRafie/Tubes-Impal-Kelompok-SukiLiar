<?php
// Entry point halaman Catalog (P005)
session_start();

// Precondition use case #5: user sudah login
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/controllers/KatalogController.php';

$data = (new KatalogController())->index();
extract($data);

require __DIR__ . '/../app/views/katalog.php';

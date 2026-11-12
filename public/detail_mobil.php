<?php
// Entry point Page Detail Mobil dan Kalender (P006)
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/controllers/KatalogController.php';

extract((new KatalogController())->detail());
require __DIR__ . '/../app/views/detail_mobil.php';

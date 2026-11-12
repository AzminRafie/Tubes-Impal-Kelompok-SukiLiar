<?php
// Entry point Page My Bookings & History (P009)
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../controllers/RentalController.php';

extract((new RentalController())->myBookings((int)$_SESSION['user_id']));
require __DIR__ . '/../views/my_bookings.php';

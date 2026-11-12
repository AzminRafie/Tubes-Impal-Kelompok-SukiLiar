<?php
// Entry point Page My Bookings & History (P009)
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/controllers/RentalController.php';

extract((new RentalController())->myBookings((int)$_SESSION['user_id']));
require __DIR__ . '/../app/views/my_bookings.php';

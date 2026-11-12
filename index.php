<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        header("Location: views/dashboard_sementara.php");
    } else {
        header("Location: public/katalog.php");
    }
} else {
    header("Location: auth/login.php");
}
exit();

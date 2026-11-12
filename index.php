<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: public/katalog.php");
} else {
    header("Location: auth/login.php");
}
exit();

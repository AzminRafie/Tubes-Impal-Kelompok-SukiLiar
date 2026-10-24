<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$nama  = $_SESSION['nama'] ?? 'Pengguna';
$email = $_SESSION['email'] ?? '-';
$role  = $_SESSION['role'] ?? 'customer';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Sementara – RentaMobil</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background-color: #1e293b;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(51, 65, 85, 0.45) 0%, transparent 45%),
                radial-gradient(circle at 90% 80%, rgba(51, 65, 85, 0.45) 0%, transparent 45%),
                linear-gradient(to right, rgba(255, 255, 255, 0.04) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 100% 100%, 100% 100%, 36px 36px, 36px 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: #f8fafc;
        }

        .success-card {
            background: #ffffff;
            color: #0f172a;
            width: 100%;
            max-width: 440px;
            border-radius: 20px;
            padding: 40px 32px;
            text-align: center;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ─── ANIMATED CHECKMARK ─── */
        .checkmark-wrapper {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: #ecfdf5;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            border: 2px solid #a7f3d0;
        }

        .checkmark-svg {
            width: 48px;
            height: 48px;
            stroke: #10b981;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: drawCheck 0.5s cubic-bezier(0.65, 0, 0.45, 1) forwards 0.2s;
        }

        @keyframes drawCheck {
            to {
                stroke-dashoffset: 0;
            }
        }

        .title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .subtitle {
            font-size: 0.95rem;
            color: #64748b;
            margin-bottom: 24px;
            line-height: 1.5;
        }

        /* ─── USER INFO BOX ─── */
        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.88rem;
        }

        .info-label {
            color: #64748b;
            font-weight: 500;
        }

        .info-value {
            color: #0f172a;
            font-weight: 600;
        }

        .role-badge {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 3px 10px;
            border-radius: 20px;
            letter-spacing: 0.05em;
        }

        .role-admin {
            background: #fee2e2;
            color: #dc2626;
        }

        .role-customer {
            background: #dbeafe;
            color: #2563eb;
        }

        /* ─── BUTTONS ─── */
        .btn-logout {
            display: block;
            width: 100%;
            padding: 12px;
            background: #ef4444;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s, transform 0.1s;
        }

        .btn-logout:hover {
            background: #dc2626;
        }

        .btn-logout:active {
            transform: scale(0.99);
        }
    </style>
</head>
<body>

    <div class="success-card">
        <!-- CENTANG ANIMASI -->
        <div class="checkmark-wrapper">
            <svg class="checkmark-svg" viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>

        <h1 class="title">Dashboard Sementara</h1>
        <p class="subtitle">Login Berhasil! Sesi Anda telah aktif di sistem <strong>RentaMobil</strong>.</p>

        <!-- INFO DETAIL LOGIN -->
        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Nama Pengguna</span>
                <span class="info-value"><?= htmlspecialchars($nama) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-value"><?= htmlspecialchars($email) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Hak Akses</span>
                <span class="role-badge <?= $role === 'admin' ? 'role-admin' : 'role-customer' ?>">
                    <?= htmlspecialchars(strtoupper($role)) ?>
                </span>
            </div>
        </div>

        <!-- TOMBOL LOGOUT -->
        <a href="logout.php" class="btn-logout">Logout / Keluar</a>
    </div>

</body>
</html>

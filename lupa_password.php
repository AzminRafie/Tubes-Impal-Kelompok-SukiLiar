<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$step    = 1;
$error   = "";
$success = "";
$email_terverifikasi = "";
$pertanyaan          = "";

require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['step'])) {
    $s = $_POST['step'];

    // ─── Step 1: Cari email ───
    if ($s == '1') {
        $email = trim($_POST['email']);
        if (empty($email)) {
            $error = "Email tidak boleh kosong."; $step = 1;
        } else {
            $stmt = mysqli_prepare($conn, "SELECT email, security_question FROM user WHERE email = ?");
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($res)) {
                $email_terverifikasi = $row['email'];
                $pertanyaan          = $row['security_question'] ?? '';
                // Jika akun tidak memiliki pertanyaan keamanan, langsung buat password baru (Step 3)
                if (empty($pertanyaan)) {
                    $step = 3;
                } else {
                    $step = 2;
                }
            } else {
                $error = "Email tidak ditemukan dalam sistem."; $step = 1;
            }
            mysqli_stmt_close($stmt);
        }
    }

    // ─── Step 2: Verifikasi jawaban keamanan ───
    elseif ($s == '2') {
        $email   = trim($_POST['email']);
        $jawaban = trim($_POST['security_answer']);
        $email_terverifikasi = $email;

        $stmt = mysqli_prepare($conn, "SELECT security_question, security_answer FROM user WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        $pertanyaan = $row['security_question'] ?? '';

        if (empty($jawaban)) {
            $error = "Jawaban tidak boleh kosong."; $step = 2;
        } elseif ($row && strtolower(trim($row['security_answer'])) === strtolower($jawaban)) {
            $step = 3;
        } else {
            $error = "Jawaban keamanan salah. Coba periksa kembali."; $step = 2;
        }
    }

    // ─── Step 3: Ganti password baru ───
    elseif ($s == '3') {
        $email      = trim($_POST['email']);
        $pass_baru  = trim($_POST['password_baru']);
        $konfirmasi = trim($_POST['konfirmasi_password']);
        $email_terverifikasi = $email;

        if (empty($pass_baru) || empty($konfirmasi)) {
            $error = "Password tidak boleh kosong."; $step = 3;
        } elseif (strlen($pass_baru) < 6) {
            $error = "Password minimal 6 karakter."; $step = 3;
        } elseif ($pass_baru !== $konfirmasi) {
            $error = "Konfirmasi password tidak cocok."; $step = 3;
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE user SET password = ? WHERE email = ?");
            mysqli_stmt_bind_param($stmt, "ss", $pass_baru, $email);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Password berhasil diubah. Silakan masuk dengan kata sandi baru Anda.";
                $step    = 4;
            } else {
                $error = "Gagal memperbarui password pada database."; $step = 3;
            }
            mysqli_stmt_close($stmt);
        }
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password – RentaMobil</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            /* Latar abu-abu slate keren (agak gelap tapi tidak pekat) */
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
            padding: 40px 20px;
            color: #0f172a;
            position: relative;
            overflow-x: hidden;
        }

        /* ─── BACKGROUND DECORATIVE ELEMENTS ─── */
        .bg-glow-1 {
            position: absolute;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            background: rgba(59, 130, 246, 0.12);
            filter: blur(100px);
            top: -120px;
            left: -100px;
            pointer-events: none;
            z-index: 0;
        }
        .bg-glow-2 {
            position: absolute;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            background: rgba(148, 163, 184, 0.15);
            filter: blur(90px);
            bottom: -100px;
            right: -80px;
            pointer-events: none;
            z-index: 0;
        }

        /* Floating Info Chips pada latar belakang slate */
        .floating-badge {
            position: absolute;
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
            padding: 7px 14px;
            border-radius: 30px;
            font-size: 0.76rem;
            font-weight: 600;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            gap: 7px;
            z-index: 1;
            pointer-events: none;
        }
        .badge-location {
            bottom: 28px;
            left: 36px;
        }

        /* ─── AUTH WRAPPER (SPLIT 2-COLUMN) ─── */
        .auth-wrapper {
            width: 100%;
            max-width: 980px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.1);
            display: flex;
            overflow: hidden;
            min-height: 580px;
            position: relative;
            z-index: 2;
        }

        /* ─── LEFT PANEL (SHOWCASE) ─── */
        .panel-showcase {
            flex: 1.1;
            background: #0f172a;
            color: #ffffff;
            padding: 44px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        .showcase-top {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }
        .brand-icon {
            width: 40px;
            height: 40px;
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .brand-info .title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #f8fafc;
            line-height: 1.2;
            letter-spacing: -0.3px;
        }
        .brand-info .subtitle {
            font-size: 0.74rem;
            color: #94a3b8;
        }

        .hero-text h1 {
            font-size: 1.65rem;
            font-weight: 700;
            line-height: 1.35;
            color: #f8fafc;
            margin-bottom: 12px;
            letter-spacing: -0.4px;
        }
        .hero-text p {
            font-size: 0.88rem;
            color: #94a3b8;
            line-height: 1.6;
        }

        /* Feature List */
        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .feature-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .feature-item .text-main {
            font-size: 0.84rem;
            font-weight: 600;
            color: #e2e8f0;
        }
        .feature-item .text-sub {
            font-size: 0.76rem;
            color: #64748b;
        }

        /* Social Media & Contact Section */
        .showcase-footer {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .footer-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 600;
            color: #94a3b8;
        }
        .social-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .social-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: #1e293b;
            color: #cbd5e1;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s;
        }
        .social-link:hover {
            background: #334155;
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.25);
        }
        .social-link svg {
            width: 14px;
            height: 14px;
            stroke-width: 2;
        }
        .copyright-text {
            font-size: 0.72rem;
            color: #64748b;
            margin-top: 4px;
        }

        /* ─── RIGHT PANEL (FORM) ─── */
        .panel-form {
            flex: 1;
            padding: 44px 42px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        /* Step Progress Indicator */
        .step-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }
        .step-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            font-size: 0.78rem;
            font-weight: 700;
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #cbd5e1;
            transition: all 0.2s;
        }
        .step-badge.active {
            background: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
        }
        .step-badge.done {
            background: #10b981;
            color: #ffffff;
            border-color: #10b981;
        }
        .step-line {
            flex: 1;
            height: 2px;
            background: #e2e8f0;
            border-radius: 2px;
        }
        .step-line.done {
            background: #10b981;
        }

        .form-header {
            margin-bottom: 20px;
        }
        .form-header h2 {
            font-size: 1.45rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.3px;
            margin-bottom: 6px;
        }
        .form-header p {
            font-size: 0.84rem;
            color: #64748b;
        }

        /* Alert */
        .alert-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 10px 14px;
            color: #b91c1c;
            font-size: 0.84rem;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Form elements */
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-size: 0.83rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 6px;
        }

        .input-wrap {
            position: relative;
        }
        .input-wrap input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.88rem;
            font-family: inherit;
            color: #0f172a;
            background: #f8fafc;
            outline: none;
            transition: all 0.2s;
        }
        .input-wrap input:focus {
            background: #ffffff;
            border-color: #0f172a;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08);
        }

        /* Info box pertanyaan keamanan */
        .question-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            padding: 12px 14px;
            margin-bottom: 16px;
        }
        .question-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 4px;
        }
        .question-text {
            font-size: 0.88rem;
            font-weight: 600;
            color: #0f172a;
        }

        .pw-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 15px;
            padding: 4px;
            opacity: 0.6;
            transition: opacity 0.15s;
        }
        .pw-toggle:hover { opacity: 1; }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 11px 16px;
            background: #0f172a;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 0.92rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
            margin-top: 4px;
        }
        .btn-submit:hover { background: #1e293b; }
        .btn-submit:active { transform: scale(0.99); }

        /* Back to login */
        .back-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-align: center;
            font-size: 0.84rem;
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            margin-top: 18px;
            width: 100%;
            transition: color 0.15s;
        }
        .back-link:hover {
            color: #0f172a;
            text-decoration: underline;
        }

        /* Success State */
        .success-box {
            text-align: center;
            padding: 20px 0;
        }
        .success-icon {
            font-size: 3rem;
            margin-bottom: 12px;
        }
        .success-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }
        .success-desc {
            font-size: 0.86rem;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 22px;
        }
        .btn-success-login {
            display: inline-block;
            width: 100%;
            padding: 11px 16px;
            background: #0f172a;
            color: #ffffff;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.92rem;
            font-weight: 600;
            transition: background 0.15s;
        }
        .btn-success-login:hover {
            background: #1e293b;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .floating-badge {
                display: none;
            }
        }

        @media (max-width: 860px) {
            .auth-wrapper {
                flex-direction: column;
                max-width: 480px;
            }
            .panel-showcase {
                padding: 32px 28px;
            }
            .panel-form {
                padding: 32px 28px;
            }
            .feature-list {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- Ambient background glow -->
    <div class="bg-glow-1"></div>
    <div class="bg-glow-2"></div>

    <!-- Floating status badges pada latar belakang slate -->
    <div class="floating-badge badge-location">
        <span>📍 Bandung & Sekitarnya</span>
    </div>
    <div class="floating-badge badge-rating">
        <span>⭐ 4.9/5 Penilaian</span>
    </div>

    <!-- Main Container -->
    <div class="auth-wrapper">

        <!-- Left Showcase Panel -->
        <div class="panel-showcase">
            <div class="showcase-top">
                <div class="brand-badge">
                    <div class="brand-icon">🚗</div>
                    <div class="brand-info">
                        <div class="title">RentaMobil</div>
                        <div class="subtitle">Sistem Rental Mobil Terpercaya</div>
                    </div>
                </div>

                <div class="hero-text">
                    <h1>Pemulihan Akun Cepat & Aman.</h1>
                    <p>Jangan khawatir jika Anda lupa password. Ikuti langkah verifikasi mudah untuk memperbarui kata sandi akun RentaMobil Anda.</p>
                </div>

                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-icon">🔒</div>
                        <div>
                            <div class="text-main">Keamanan Terjamin</div>
                            <div class="text-sub">Password baru langsung terenkripsi di sistem</div>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">⚡</div>
                        <div>
                            <div class="text-main">Verifikasi Cepat</div>
                            <div class="text-sub">Cukup masukkan email yang terdaftar di akun Anda</div>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">💬</div>
                        <div>
                            <div class="text-main">Bantuan Admin 24/7</div>
                            <div class="text-sub">Siap membantu jika Anda mengalami kendala login</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Media & Contact Info Footer -->
            <div class="showcase-footer">
                <div class="footer-label">Hubungi & Ikuti Kami</div>
                <div class="social-row">
                    <a href="https://instagram.com" target="_blank" class="social-link" title="Instagram">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                        <span>@rentamobil.id</span>
                    </a>
                    <a href="https://wa.me/6281234567890" target="_blank" class="social-link" title="WhatsApp">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                        <span>0812-3456-7890</span>
                    </a>
                    <a href="https://facebook.com" target="_blank" class="social-link" title="Facebook">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                        <span>RentaMobil</span>
                    </a>
                </div>
                <div class="copyright-text">
                    © 2026 RentaMobil Indonesia. Hak Cipta Dilindungi.
                </div>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="panel-form">

            <!-- Step Progress Indicator -->
            <?php if ($step < 4): ?>
            <div class="step-indicator">
                <div class="step-badge <?= $step > 1 ? 'done' : ($step == 1 ? 'active' : '') ?>">
                    <?= $step > 1 ? '✓' : '1' ?>
                </div>
                <div class="step-line <?= $step > 1 ? 'done' : '' ?>"></div>
                <div class="step-badge <?= $step > 2 ? 'done' : ($step == 2 ? 'active' : '') ?>">
                    <?= $step > 2 ? '✓' : '2' ?>
                </div>
                <div class="step-line <?= $step > 2 ? 'done' : '' ?>"></div>
                <div class="step-badge <?= $step == 3 ? 'active' : '' ?>">
                    3
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
            <div class="alert-box">
                <span>⚠</span>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>

            <?php if ($step == 1): ?>
                <div class="form-header">
                    <h2>Lupa Password?</h2>
                    <p>Masukkan alamat email yang terdaftar pada akun Anda</p>
                </div>

                <form method="POST" action="lupa_password.php" id="step1Form">
                    <input type="hidden" name="step" value="1">
                    <div class="form-group">
                        <label for="email">Alamat Email</label>
                        <div class="input-wrap">
                            <input type="email" id="email" name="email" placeholder="nama@email.com"
                                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                                required autocomplete="email">
                        </div>
                    </div>
                    <button type="submit" class="btn-submit" id="btn1">Lanjutkan Verifikasi →</button>
                </form>

            <?php elseif ($step == 2): ?>
                <div class="form-header">
                    <h2>Verifikasi Identitas</h2>
                    <p>Jawab pertanyaan keamanan akun untuk melanjutkan</p>
                </div>

                <div class="question-box">
                    <div class="question-label">Pertanyaan Keamanan</div>
                    <div class="question-text"><?= htmlspecialchars($pertanyaan) ?></div>
                </div>

                <form method="POST" action="lupa_password.php" id="step2Form">
                    <input type="hidden" name="step" value="2">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email_terverifikasi) ?>">
                    
                    <div class="form-group">
                        <label for="security_answer">Jawaban Anda</label>
                        <div class="input-wrap">
                            <input type="text" id="security_answer" name="security_answer"
                                placeholder="Ketik jawaban yang sesuai" required autofocus>
                        </div>
                    </div>
                    <button type="submit" class="btn-submit" id="btn2">Verifikasi Jawaban →</button>
                </form>

            <?php elseif ($step == 3): ?>
                <div class="form-header">
                    <h2>Buat Password Baru</h2>
                    <p>Masukkan kata sandi baru untuk akun <?= htmlspecialchars($email_terverifikasi) ?></p>
                </div>

                <form method="POST" action="lupa_password.php" id="step3Form">
                    <input type="hidden" name="step" value="3">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email_terverifikasi) ?>">

                    <div class="form-group">
                        <label for="password_baru">Password Baru</label>
                        <div class="input-wrap">
                            <input type="password" id="password_baru" name="password_baru"
                                placeholder="Min. 6 karakter"
                                style="padding-right: 42px;" required autocomplete="new-password">
                            <button type="button" class="pw-toggle" id="tp1" title="Lihat password">
                                <span id="e1">👁</span>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="konfirmasi_password">Konfirmasi Password Baru</label>
                        <div class="input-wrap">
                            <input type="password" id="konfirmasi_password" name="konfirmasi_password"
                                placeholder="Ulangi password baru"
                                style="padding-right: 42px;" required autocomplete="new-password">
                            <button type="button" class="pw-toggle" id="tp2" title="Lihat konfirmasi password">
                                <span id="e2">👁</span>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit" id="btn3">Simpan Password Baru</button>
                </form>

            <?php elseif ($step == 4): ?>
                <div class="success-box">
                    <div class="success-icon">✅</div>
                    <div class="success-title">Password Berhasil Diubah</div>
                    <div class="success-desc">
                        Kata sandi akun Anda telah berhasil diperbarui. Silakan masuk kembali menggunakan password baru Anda.
                    </div>
                    <a href="login.php" class="btn-success-login">Masuk ke Akun Sekarang →</a>
                </div>
            <?php endif; ?>

            <?php if ($step < 4): ?>
                <a href="login.php" class="back-link">← Kembali ke Halaman Masuk</a>
            <?php endif; ?>

        </div>

    </div>

    <script>
        // Toggle Show/Hide Password
        function makePwToggle(btnId, inputId, iconId) {
            const btn = document.getElementById(btnId);
            if (!btn) return;
            btn.addEventListener('click', () => {
                const inp = document.getElementById(inputId);
                const ico = document.getElementById(iconId);
                const show = inp.type === 'password';
                inp.type = show ? 'text' : 'password';
                ico.textContent = show ? '🙈' : '👁';
            });
        }
        makePwToggle('tp1', 'password_baru', 'e1');
        makePwToggle('tp2', 'konfirmasi_password', 'e2');

        // Loading state
        ['step1Form', 'step2Form', 'step3Form'].forEach(formId => {
            document.getElementById(formId)?.addEventListener('submit', function(e) {
                const btn = this.querySelector('.btn-submit');
                if (btn) {
                    btn.textContent = 'Memproses...';
                    btn.style.opacity = '0.8';
                }
            });
        });
    </script>
</body>
</html>

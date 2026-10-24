<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error   = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once 'koneksi.php';

    $nama       = trim($_POST['nama']);
    $email      = trim($_POST['email']);
    $password   = trim($_POST['password']);
    $konfirmasi = trim($_POST['konfirmasi_password']);

    if (empty($nama) || empty($email) || empty($password) || empty($konfirmasi)) {
        $error = "Semua kolom wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } elseif ($password !== $konfirmasi) {
        $error = "Konfirmasi password tidak cocok.";
    } else {
        $cek = "SELECT user_id FROM user WHERE email = ?";
        $stmt_cek = mysqli_prepare($conn, $cek);
        mysqli_stmt_bind_param($stmt_cek, "s", $email);
        mysqli_stmt_execute($stmt_cek);
        mysqli_stmt_store_result($stmt_cek);

        if (mysqli_stmt_num_rows($stmt_cek) > 0) {
            $error = "Email sudah terdaftar. Silakan gunakan email lain atau login.";
        } else {
            $role  = "customer";
            $sql   = "INSERT INTO user (nama, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt  = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $password, $role);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Akun berhasil dibuat! Silakan masuk dengan akun baru Anda.";
            } else {
                $error = "Terjadi kesalahan pada sistem, coba beberapa saat lagi.";
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($stmt_cek);
        mysqli_close($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun – RentaMobil</title>
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
            max-width: 1040px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.1);
            display: flex;
            overflow: hidden;
            min-height: 640px;
            position: relative;
            z-index: 2;
        }

        /* ─── LEFT PANEL (SHOWCASE) ─── */
        .panel-showcase {
            flex: 1;
            background: #0f172a;
            color: #ffffff;
            padding: 44px 38px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        .showcase-top {
            display: flex;
            flex-direction: column;
            gap: 22px;
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
            font-size: 1.6rem;
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
            flex: 1.3;
            padding: 40px 42px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        /* Tab Switcher (Sesuai Figma) */
        .auth-tabs {
            display: flex;
            background: #f1f5f9;
            border-radius: 10px;
            padding: 4px;
            margin-bottom: 22px;
            border: 1px solid #e2e8f0;
        }
        .tab-btn {
            flex: 1;
            text-align: center;
            padding: 8px 12px;
            font-size: 0.84rem;
            font-weight: 600;
            color: #64748b;
            text-decoration: none;
            border-radius: 7px;
            transition: all 0.15s;
        }
        .tab-btn:hover {
            color: #0f172a;
        }
        .tab-btn.active {
            background: #0f172a;
            color: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .form-header {
            margin-bottom: 18px;
        }
        .form-header h2 {
            font-size: 1.45rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.3px;
            margin-bottom: 4px;
        }
        .form-header p {
            font-size: 0.84rem;
            color: #64748b;
        }

        /* Alert */
        .alert {
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.84rem;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }

        /* Form elements */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        @media (max-width: 540px) {
            .form-row { grid-template-columns: 1fr; }
        }

        .form-group {
            margin-bottom: 14px;
        }
        .form-group label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .input-wrap {
            position: relative;
        }
        .input-wrap input,
        .input-wrap select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.88rem;
            font-family: inherit;
            color: #0f172a;
            background: #f8fafc;
            outline: none;
            transition: all 0.2s;
        }
        .input-wrap input:focus,
        .input-wrap select:focus {
            background: #ffffff;
            border-color: #0f172a;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08);
        }
        .input-wrap select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2364748b' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 36px;
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

        /* Strength bar */
        .strength-wrap {
            margin-top: 5px;
        }
        .strength-bars {
            display: flex;
            gap: 4px;
        }
        .strength-bars span {
            flex: 1;
            height: 3px;
            border-radius: 3px;
            background: #e2e8f0;
            transition: background 0.3s;
        }
        .strength-label {
            font-size: 0.72rem;
            color: #64748b;
            margin-top: 3px;
        }

        .section-divider {
            font-size: 0.73rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin: 16px 0 12px;
            padding-bottom: 6px;
            border-bottom: 1px solid #f1f5f9;
        }

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
            margin-top: 6px;
        }
        .btn-submit:hover { background: #1e293b; }
        .btn-submit:active { transform: scale(0.99); }

        /* Terms & Privacy Note */
        .terms-note {
            text-align: center;
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 20px;
            line-height: 1.5;
        }
        .terms-note a {
            color: #0f172a;
            font-weight: 600;
            text-decoration: underline;
            transition: color 0.15s;
        }
        .terms-note a:hover {
            color: #2563eb;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .floating-badge {
                display: none;
            }
        }

        @media (max-width: 890px) {
            .auth-wrapper {
                flex-direction: column;
                max-width: 520px;
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

    <!-- Floating badge: Lokasi Bandung -->
    <div class="floating-badge badge-location">
        <span>📍 Bandung & Sekitarnya</span>
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
                    <h1>Bergabung & Nikmati Kemudahan Sewa Mobil.</h1>
                    <p>Daftarkan akun Anda dalam hitungan detik untuk mendapatkan akses armada terlengkap, reservasi instan, dan tarif sewa terbaik.</p>
                </div>

                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-icon">⚡</div>
                        <div>
                            <div class="text-main">Reservasi Cepat & Praktis</div>
                            <div class="text-sub">Pesan mobil langsung diverifikasi oleh admin</div>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">🛡️</div>
                        <div>
                            <div class="text-main">Keamanan Akun Terlindungi</div>
                            <div class="text-sub">Dilengkapi pertanyaan keamanan untuk pemulihan akun</div>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">🚗</div>
                        <div>
                            <div class="text-main">Armada Lengkap Siap Pakai</div>
                            <div class="text-sub">Pilihan City Car, MPV, hingga SUV prima & ber-AC</div>
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

            <!-- Tab Switcher (Sesuai Figma) -->
            <div class="auth-tabs">
                <a href="login.php" class="tab-btn">Masuk</a>
                <a href="register.php" class="tab-btn active">Daftar Akun</a>
            </div>

            <div class="form-header">
                <h2>Buat Akun Baru</h2>
                <p>Isi data di bawah untuk membuat akun RentaMobil Anda</p>
            </div>

            <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <span>⚠</span>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php elseif (!empty($success)): ?>
            <div class="alert alert-success">
                <span>✓</span>
                <div>
                    <?= htmlspecialchars($success) ?>
                    <a href="login.php" style="margin-left: 6px; color: inherit; font-weight: 700; text-decoration: underline;">Masuk Sekarang →</a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (empty($success)): ?>
            <form method="POST" action="register.php" id="regForm">
                <!-- Baris Data Diri -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>
                        <div class="input-wrap">
                            <input type="text" id="nama" name="nama" placeholder="contoh: Dimas Pratama"
                                value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>"
                                required autocomplete="name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-wrap">
                            <input type="email" id="email" name="email" placeholder="nama@email.com"
                                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                                required autocomplete="email">
                        </div>
                    </div>
                </div>

                <!-- Baris Password -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <input type="password" id="password" name="password"
                                placeholder="Min. 6 karakter"
                                style="padding-right: 42px;"
                                required autocomplete="new-password">
                            <button type="button" class="pw-toggle" id="tp1" title="Lihat password">
                                <span id="e1">👁</span>
                            </button>
                        </div>
                        <div class="strength-wrap">
                            <div class="strength-bars">
                                <span id="b1"></span><span id="b2"></span>
                                <span id="b3"></span><span id="b4"></span>
                            </div>
                            <div class="strength-label" id="sLabel"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="konfirmasi_password">Konfirmasi Password</label>
                        <div class="input-wrap">
                            <input type="password" id="konfirmasi_password" name="konfirmasi_password"
                                placeholder="Ulangi password"
                                style="padding-right: 42px;"
                                required autocomplete="new-password">
                            <button type="button" class="pw-toggle" id="tp2" title="Lihat konfirmasi password">
                                <span id="e2">👁</span>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">Daftar Akun Baru</button>
            </form>
            <?php endif; ?>

            <div class="terms-note">
                Dengan membuat akun, Anda menyetujui <a href="#" onclick="alert('Syarat & Ketentuan Layanan RentaMobil'); return false;">Syarat & Ketentuan</a> serta <a href="#" onclick="alert('Kebijakan Privasi RentaMobil'); return false;">Kebijakan Privasi</a> kami.
            </div>
        </div>

    </div>

    <script>
        // Toggle Show/Hide Password
        function makePwToggle(btnId, inputId, iconId) {
            document.getElementById(btnId).addEventListener('click', () => {
                const inp = document.getElementById(inputId);
                const ico = document.getElementById(iconId);
                const show = inp.type === 'password';
                inp.type = show ? 'text' : 'password';
                ico.textContent = show ? '🙈' : '👁';
            });
        }
        makePwToggle('tp1', 'password', 'e1');
        makePwToggle('tp2', 'konfirmasi_password', 'e2');

        // Password Strength Meter
        const passInput = document.getElementById('password');
        const bars      = ['b1','b2','b3','b4'].map(id => document.getElementById(id));
        const sLabel    = document.getElementById('sLabel');
        const colors    = ['#ef4444','#f59e0b','#3b82f6','#22c55e'];
        const labels    = ['Sangat Lemah','Lemah','Cukup Kuat','Sangat Kuat'];

        if (passInput) {
            passInput.addEventListener('input', () => {
                const v = passInput.value;
                let s = 0;
                if (v.length >= 6) s++;
                if (v.length >= 10) s++;
                if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++;
                if (/[0-9]/.test(v) || /[^a-zA-Z0-9]/.test(v)) s++;
                bars.forEach((b, i) => b.style.background = i < s ? colors[s-1] : '#e2e8f0');
                sLabel.textContent = v.length === 0 ? '' : labels[s-1] || '';
            });
        }

        // Loading state saat submit
        document.getElementById('regForm')?.addEventListener('submit', () => {
            const btn = document.getElementById('submitBtn');
            btn.textContent = 'Memproses...';
            btn.style.opacity = '0.8';
        });
    </script>
</body>
</html>

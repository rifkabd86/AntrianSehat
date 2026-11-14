<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= CsrfMiddleware::getToken() ?>">
    <title><?= htmlspecialchars($title ?? 'ANTRIAN SEHAT', ENT_QUOTES, 'UTF-8') ?> - ANTRIAN SEHAT</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .auth-wrapper {
            min-height: 100vh;
            display: flex;
        }

        /* Left Panel - Branding */
        .auth-left {
            background: linear-gradient(135deg, #1E40AF 0%, #2563EB 50%, #3B82F6 100%);
            width: 60%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem;
            position: relative;
            overflow: hidden;
            color: #fff;
        }

        .auth-left::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(ellipse at center, rgba(255,255,255,0.08) 0%, transparent 70%);
            animation: pulse-bg 8s ease-in-out infinite;
        }

        @keyframes pulse-bg {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 1; }
        }

        .auth-left .brand-content {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 480px;
        }

        .auth-left .brand-logo {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .auth-left .brand-name {
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 0.5rem;
        }

        .auth-left .brand-tagline {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 1.5rem;
            letter-spacing: 3px;
        }

        .auth-left .brand-description {
            font-size: 1rem;
            opacity: 0.8;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .auth-left .brand-illustration {
            position: relative;
            z-index: 1;
            margin-top: 2rem;
        }

        .auth-left .illustration-icons {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .auth-left .illustration-icons .icon-box {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            transition: transform 0.3s;
        }

        .auth-left .illustration-icons .icon-box:hover {
            transform: translateY(-5px);
        }

        /* Floating decoration circles */
        .auth-left .deco-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            z-index: 0;
        }

        .auth-left .deco-circle-1 {
            width: 300px;
            height: 300px;
            top: -80px;
            right: -80px;
        }

        .auth-left .deco-circle-2 {
            width: 200px;
            height: 200px;
            bottom: -60px;
            left: -60px;
        }

        .auth-left .deco-circle-3 {
            width: 120px;
            height: 120px;
            bottom: 20%;
            right: 10%;
        }

        /* Right Panel - Form */
        .auth-right {
            width: 40%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            background: #fff;
            overflow-y: auto;
        }

        .auth-form-container {
            width: 100%;
            max-width: 440px;
            padding: 1rem;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .auth-left {
                display: none;
            }

            .auth-right {
                width: 100%;
                background: linear-gradient(180deg, #f0f4ff 0%, #ffffff 30%);
            }

            .auth-form-container {
                max-width: 480px;
            }
        }

        @media (max-width: 575.98px) {
            .auth-right {
                padding: 1rem;
            }

            .auth-form-container {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <!-- Left Panel: Branding -->
        <div class="auth-left">
            <!-- Decoration Circles -->
            <div class="deco-circle deco-circle-1"></div>
            <div class="deco-circle deco-circle-2"></div>
            <div class="deco-circle deco-circle-3"></div>

            <div class="brand-content">
                <div class="brand-logo">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h1 class="brand-name">ANTRIAN SEHAT</h1>
                <p class="brand-tagline">Mudah &bull; Cepat &bull; Sehat</p>
                <p class="brand-description">
                    Sistem manajemen antrean kesehatan modern yang memudahkan pasien mendaftar,
                    memantau antrean secara real-time, dan mendapatkan pelayanan kesehatan
                    dengan lebih efisien dan nyaman.
                </p>

                <!-- Illustration Icons -->
                <div class="brand-illustration">
                    <div class="illustration-icons">
                        <div class="icon-box" title="Registrasi Online">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="icon-box" title="Antrean Digital">
                            <i class="fas fa-users-line"></i>
                        </div>
                        <div class="icon-box" title="Pelayanan Cepat">
                            <i class="fas fa-user-doctor"></i>
                        </div>
                        <div class="icon-box" title="Riwayat Lengkap">
                            <i class="fas fa-file-medical"></i>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3">
                    <div class="d-flex justify-content-center gap-4 text-white-50 small">
                        <div class="text-center">
                            <i class="fas fa-shield-halved fs-4 mb-1 d-block text-white"></i>
                            Aman
                        </div>
                        <div class="text-center">
                            <i class="fas fa-bolt fs-4 mb-1 d-block text-white"></i>
                            Cepat
                        </div>
                        <div class="text-center">
                            <i class="fas fa-mobile-screen fs-4 mb-1 d-block text-white"></i>
                            Responsif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Form Content -->
        <div class="auth-right">
            <div class="auth-form-container">
                <!-- Mobile Only: Mini Branding -->
                <div class="text-center mb-4 d-lg-none">
                    <div class="mb-2">
                        <span class="text-primary fs-1"><i class="fas fa-heartbeat"></i></span>
                    </div>
                    <h4 class="fw-bold text-primary mb-0">ANTRIAN SEHAT</h4>
                    <small class="text-muted">Mudah &bull; Cepat &bull; Sehat</small>
                </div>

                <!-- Flash Messages -->
                <?php if (!empty($_SESSION['flash'])): ?>
                    <?php
                        $flash = $_SESSION['flash'];
                        unset($_SESSION['flash']);
                        $alertType = 'info';
                        $alertIcon = 'fa-circle-info';
                        switch ($flash['type'] ?? 'info') {
                            case 'success':
                                $alertType = 'success';
                                $alertIcon = 'fa-circle-check';
                                break;
                            case 'error':
                                $alertType = 'danger';
                                $alertIcon = 'fa-circle-exclamation';
                                break;
                            case 'warning':
                                $alertType = 'warning';
                                $alertIcon = 'fa-triangle-exclamation';
                                break;
                        }
                    ?>
                    <div class="alert alert-<?= $alertType ?> alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="fas <?= $alertIcon ?> me-2"></i>
                        <div><?= htmlspecialchars($flash['message'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                <?php endif; ?>

                <!-- Page Content -->
                <?= $content ?? '' ?>
            </div>

            <!-- Footer -->
            <div class="text-center mt-4 pt-3">
                <small class="text-muted">
                    &copy; <?= date('Y') ?> ANTRIAN SEHAT. Semua hak dilindungi.
                </small>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>/js/app.js"></script>
</body>
</html>

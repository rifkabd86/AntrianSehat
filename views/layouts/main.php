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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- Custom CSS with Cache Buster -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css?v=<?= time() ?>">
</head>
<body>
    <div class="app-wrapper d-flex">
        <!-- Sidebar -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Content Wrapper -->
        <div class="main-content-wrapper flex-grow-1">
            <!-- Header -->
            <?php include __DIR__ . '/header.php'; ?>

            <!-- Flash Messages -->
            <div class="container-fluid px-4 pt-3">
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
                    <div class="alert alert-<?= $alertType ?> alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                        <i class="fas <?= $alertIcon ?> me-2 fs-5"></i>
                        <div><?= htmlspecialchars($flash['message'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Page Content -->
            <main class="main-content container-fluid px-4 pb-4">
                <?= $content ?? '' ?>
            </main>

            <!-- Footer -->
            <?php include __DIR__ . '/footer.php'; ?>
        </div>
    </div>

    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>/js/app.js"></script>
</body>
</html>

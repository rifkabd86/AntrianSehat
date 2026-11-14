<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak | ANTRIAN SEHAT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body>
    <div class="text-center p-4">
        <h1 class="display-1 fw-bold text-danger mb-2">403</h1>
        <h3 class="fw-bold text-dark mb-2">Akses Ditolak</h3>
        <p class="text-muted mb-4" style="max-width: 450px;">Anda tidak memiliki hak akses yang diizinkan untuk melihat modul atau data ini.</p>
        <a href="<?= BASE_URL ?>/dashboard" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
            <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
        </a>
    </div>
</body>
</html>


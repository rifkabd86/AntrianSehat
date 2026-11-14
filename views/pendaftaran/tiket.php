<?php
$d = is_object($data) ? (array)$data : (array)($data ?? []);
?>
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <!-- Print Area -->
        <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white" id="printTicketArea">
            <div class="border-bottom pb-3 mb-3">
                <div class="d-inline-flex align-items-center justify-content-center text-primary mb-2">
                    <i class="fas fa-heartbeat fs-2 me-2"></i>
                    <h5 class="fw-bold mb-0 text-dark">ANTRIAN SEHAT</h5>
                </div>
                <p class="text-muted small mb-0">Puskesmas Sehat Sentosa &bull; Layanan Cepat</p>
            </div>

            <div class="py-2">
                <small class="text-muted text-uppercase tracking-wider fw-semibold">Nomor Antrean Anda</small>
                <h1 class="display-1 fw-bold text-primary my-2" style="letter-spacing: 2px;">
                    <?= htmlspecialchars($d['no_antrean'] ?? 'A001', ENT_QUOTES, 'UTF-8') ?>
                </h1>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill">
                    <?= htmlspecialchars($d['nama_poli'] ?? 'Poli Umum', ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <div class="bg-light rounded-3 p-3 my-3 text-start small">
                <div class="d-flex justify-content-between py-1 border-bottom border-white">
                    <span class="text-muted">Nama Pasien:</span>
                    <strong class="text-dark"><?= htmlspecialchars($d['nama_pasien'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom border-white">
                    <span class="text-muted">No. Rekam Medis:</span>
                    <strong><?= htmlspecialchars($d['no_rekam_medis'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom border-white">
                    <span class="text-muted">Dokter Pemeriksa:</span>
                    <strong><?= htmlspecialchars($d['nama_dokter'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom border-white">
                    <span class="text-muted">Tanggal:</span>
                    <strong><?= Helper::formatTanggal($d['tanggal_kunjungan'] ?? date('Y-m-d')) ?></strong>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Waktu Pendaftaran:</span>
                    <strong><?= Helper::formatWaktu($d['created_at'] ?? date('H:i:s')) ?></strong>
                </div>
            </div>

            <!-- Barcode Mockup Display -->
            <div class="my-2 py-2">
                <div style="font-family: monospace; letter-spacing: 8px; font-size: 1.25rem; font-weight: bold; color: #334155;">
                    ||||| | |||| ||| |||| | ||
                </div>
                <small class="text-muted" style="font-size: 0.75rem;">Simpan bukti ini sampai pemanggilan antrean selesai</small>
            </div>

            <div class="d-flex gap-2 justify-content-center mt-3 pt-3 border-top d-print-none">
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Cetak Bukti
                </button>
                <a href="<?= BASE_URL ?>/antrean" class="btn btn-primary">
                    <i class="fas fa-list-ol me-1"></i> Pantau Antrean
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #printTicketArea, #printTicketArea * {
        visibility: visible;
    }
    #printTicketArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        box-shadow: none !important;
        border: none !important;
    }
}
</style>

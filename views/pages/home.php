<section class="welcome-page">
    <header class="welcome-nav">
        <a class="brand-lockup" href="<?= SecurityHelper::escape(UrlHelper::to()) ?>"><span class="brand-symbol"><i class="bi bi-heart-pulse-fill" aria-hidden="true"></i></span><span><strong>ANTRIAN SEHAT</strong><small>LAYANAN KESEHATAN TERPADU</small></span></a>
        <a class="btn btn-outline-primary btn-sm" href="<?= SecurityHelper::escape(UrlHelper::to('login')) ?>">Masuk <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
    </header>
    <div class="welcome-content">
        <div class="welcome-copy">
            <span class="eyebrow">LAYANAN PUSKESMAS, DALAM GENGGAMAN</span>
            <h1>Pelayanan Sehat,<br><span>Antrean Lebih Cepat.</span></h1>
            <p>Temukan jadwal layanan, kelola pendaftaran, dan ikuti antrean Puskesmas dengan lebih nyaman.</p>
            <a class="btn btn-primary" href="<?= SecurityHelper::escape(UrlHelper::to('login')) ?>">Masuk ke layanan <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            <a class="welcome-preview-link" href="<?= SecurityHelper::escape(UrlHelper::to('dashboard-preview.php')) ?>">Lihat pratinjau dashboard <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
            <div class="welcome-trust"><i class="bi bi-shield-check" aria-hidden="true"></i><span>Informasi layanan yang tertata untuk Anda dan keluarga</span></div>
        </div>
        <div class="welcome-image-wrap"><img src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1400&q=85" alt="Tenaga kesehatan memberikan pelayanan di klinik"><div class="welcome-image-caption"><span class="caption-icon"><i class="bi bi-heart-pulse" aria-hidden="true"></i></span><span><strong>Lebih terarah</strong><small>Sejak pendaftaran hingga pelayanan</small></span></div></div>
    </div>
    <footer class="welcome-footer"><span>ANTRIAN SEHAT</span><span>Sistem Informasi Pelayanan Puskesmas</span></footer>
</section>
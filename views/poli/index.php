<div class="card border-0 shadow-sm rounded-4 p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Data Poli & Poliklinik</h4>
            <p class="text-muted small mb-0">Kelola daftar unit poli pelayanan kesehatan di Puskesmas</p>
        </div>
        <?php if (in_array($_SESSION['user_role'] ?? '', ['admin'])): ?>
            <a href="<?= BASE_URL ?>/poli/create" class="btn btn-primary d-inline-flex align-items-center">
                <i class="fas fa-plus me-2"></i> Tambah Poli
            </a>
        <?php endif; ?>
    </div>

    <!-- Cards Grid Poli -->
    <div class="row g-3">
        <?php if (!empty($list)): ?>
            <?php foreach ($list as $p): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 p-4" style="border-top: 4px solid <?= $p->warna ?? '#2563EB' ?> !important;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 48px; height: 48px; background-color: <?= $p->warna ?? '#2563EB' ?>;">
                                <i class="<?= $p->icon ?? 'fas fa-stethoscope' ?> fs-5"></i>
                            </div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">
                                <?= htmlspecialchars($p->kode_poli ?? $p['kode_poli'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>

                        <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($p->nama_poli ?? $p['nama_poli'], ENT_QUOTES, 'UTF-8') ?></h5>
                        <p class="text-muted small mb-3 flex-grow-1"><?= htmlspecialchars($p->deskripsi ?? 'Pelayanan kesehatan spesifik poli', ENT_QUOTES, 'UTF-8') ?></p>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top text-muted small">
                            <span><i class="fas fa-user-doctor me-1"></i> <?= $p->total_dokter ?? 0 ?> Dokter</span>
                            <span><i class="fas fa-clock me-1"></i> <?= $p->antrean_hari_ini ?? 0 ?> Hari Ini</span>
                        </div>

                        <?php if (in_array($_SESSION['user_role'] ?? '', ['admin'])): ?>
                            <div class="mt-3 text-end">
                                <a href="<?= BASE_URL ?>/poli/edit/<?= $p->id ?? $p['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>


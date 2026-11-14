<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
            <div class="mx-auto rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center mb-3 fw-bold" style="width: 72px; height: 72px;">
                <i class="fas fa-user-doctor fs-2"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($dokter->nama_dokter ?? $dokter['nama_dokter'], ENT_QUOTES, 'UTF-8') ?></h5>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 mb-3">
                <?= htmlspecialchars($dokter->nama_poli ?? $dokter['nama_poli'], ENT_QUOTES, 'UTF-8') ?>
            </span>

            <div class="text-start border-top pt-3 mt-2">
                <div class="mb-2">
                    <span class="text-muted small d-block">Kode Dokter:</span>
                    <span class="fw-semibold text-dark"><?= htmlspecialchars($dokter->kode_dokter ?? $dokter['kode_dokter'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="mb-2">
                    <span class="text-muted small d-block">Spesialisasi:</span>
                    <span class="fw-semibold text-dark"><?= htmlspecialchars($dokter->spesialisasi ?? $dokter['spesialisasi'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="mb-2">
                    <span class="text-muted small d-block">Kontak:</span>
                    <span class="fw-semibold text-dark"><?= htmlspecialchars($dokter->no_telepon ?? $dokter['no_telepon'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex gap-2 justify-content-center">
                <a href="<?= BASE_URL ?>/dokter" class="btn btn-outline-secondary rounded-pill btn-sm px-3">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Dokter
                </a>
                <?php if (in_array($_SESSION['user_role'] ?? '', ['admin'])): ?>
                    <a href="<?= BASE_URL ?>/jadwal/create?dokter_id=<?= $dokter->id ?? $dokter['id'] ?>" class="btn btn-primary rounded-pill btn-sm px-3">
                        <i class="fas fa-plus me-1"></i> Tambah Jadwal
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold text-dark mb-1">Daftar Jadwal Praktik Mingguan</h5>
                    <p class="text-muted small mb-0">Jadwal layanan aktif untuk dokter bersangkutan</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-muted small">
                            <th>Hari</th>
                            <th>Jam Layanan</th>
                            <th>Kuota / Hari</th>
                            <th>Status</th>
                            <?php if (in_array($_SESSION['user_role'] ?? '', ['admin'])): ?>
                                <th class="text-center" style="width: 140px;">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($jadwalList)): ?>
                            <?php foreach ($jadwalList as $j): ?>
                                <tr>
                                    <td class="fw-semibold text-dark">
                                        <i class="far fa-calendar-check text-primary me-2"></i>
                                        <?= htmlspecialchars($j->hari ?? $j['hari'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <i class="far fa-clock text-muted me-1"></i>
                                            <?= htmlspecialchars(substr($j->jam_mulai ?? $j['jam_mulai'], 0, 5), ENT_QUOTES, 'UTF-8') ?> - 
                                            <?= htmlspecialchars(substr($j->jam_selesai ?? $j['jam_selesai'], 0, 5), ENT_QUOTES, 'UTF-8') ?> WIB
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info fw-bold">
                                            <?= (int)($j->kuota ?? $j['kuota']) ?> Pasien
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (($j->is_active ?? $j['is_active']) == 1): ?>
                                            <span class="badge bg-success-subtle text-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if (in_array($_SESSION['user_role'] ?? '', ['admin'])): ?>
                                        <td class="text-center">
                                            <a href="<?= BASE_URL ?>/jadwal/edit/<?= $j->id ?? $j['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="<?= BASE_URL ?>/jadwal/delete/<?= $j->id ?? $j['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?');">
                                                <?= CsrfMiddleware::field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= in_array($_SESSION['user_role'] ?? '', ['admin']) ? 5 : 4 ?>" class="text-center py-4 text-muted">
                                    <i class="fas fa-calendar-times fs-2 mb-2 d-block text-secondary"></i>
                                    Belum ada jadwal praktik untuk dokter ini.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


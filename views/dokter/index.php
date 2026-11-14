<div class="card border-0 shadow-sm rounded-4 p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Data Dokter & Tenaga Medis</h4>
            <p class="text-muted small mb-0">Daftar dokter spesialis dan umum beserta jadwal praktiknya</p>
        </div>
        <?php if (in_array($_SESSION['user_role'] ?? '', ['admin'])): ?>
            <a href="<?= BASE_URL ?>/dokter/create" class="btn btn-primary d-inline-flex align-items-center">
                <i class="fas fa-plus me-2"></i> Tambah Dokter
            </a>
        <?php endif; ?>
    </div>

    <!-- Tabel Dokter -->
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-muted small">
                    <th>Kode</th>
                    <th>Nama Dokter</th>
                    <th>Poli / Unit</th>
                    <th>Spesialisasi</th>
                    <th>Kontak</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($list)): ?>
                    <?php foreach ($list as $d): ?>
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border fw-bold">
                                    <?= htmlspecialchars($d->kode_dokter ?? $d['kode_dokter'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="fw-semibold text-dark">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center me-2 fw-bold" style="width: 36px; height: 36px;">
                                        <i class="fas fa-user-doctor"></i>
                                    </div>
                                    <span><?= htmlspecialchars($d->nama_dokter ?? $d['nama_dokter'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    <?= htmlspecialchars($d->nama_poli ?? $d['nama_poli'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="text-muted small">
                                <?= htmlspecialchars($d->spesialisasi ?? $d['spesialisasi'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="text-muted small">
                                <?= htmlspecialchars($d->no_telepon ?? $d['no_telepon'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td>
                                <?php if (($d->is_active ?? 1) == 1): ?>
                                    <span class="badge bg-success-subtle text-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <a href="<?= BASE_URL ?>/dokter/jadwal/<?= $d->id ?? $d['id'] ?>" class="btn btn-outline-primary btn-sm rounded-pill px-2 py-1" title="Lihat Jadwal">
                                        <i class="fas fa-calendar-alt"></i>
                                    </a>
                                    <?php if (in_array($_SESSION['user_role'] ?? '', ['admin'])): ?>
                                        <a href="<?= BASE_URL ?>/dokter/edit/<?= $d->id ?? $d['id'] ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-2 py-1" title="Edit Dokter">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?= BASE_URL ?>/dokter/delete/<?= $d->id ?? $d['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus/menonaktifkan dokter ini?');">
                                            <?= CsrfMiddleware::field() ?>
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2 py-1" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


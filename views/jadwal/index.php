<div class="card border-0 shadow-sm rounded-4 p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Jadwal Praktik Dokter</h4>
            <p class="text-muted small mb-0">Kelola jadwal hari, jam pelayanan, dan kuota pasien untuk setiap dokter</p>
        </div>
        <?php if (in_array($_SESSION['user_role'] ?? '', ['admin'])): ?>
            <a href="<?= BASE_URL ?>/jadwal/create" class="btn btn-primary d-inline-flex align-items-center">
                <i class="fas fa-calendar-plus me-2"></i> Tambah Jadwal Baru
            </a>
        <?php endif; ?>
    </div>

    <!-- Filter Bar -->
    <form action="<?= BASE_URL ?>/jadwal" method="GET" class="card bg-light border-0 rounded-4 p-3 mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-secondary">Poli / Unit</label>
                <select class="form-select" name="poli_id" onchange="this.form.submit()">
                    <option value="">Semua Poli</option>
                    <?php foreach ($poliList as $p): ?>
                        <option value="<?= $p->id ?? $p['id'] ?>" <?= (($filters['poli_id'] ?? 0) == ($p->id ?? $p['id'])) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p->nama_poli ?? $p['nama_poli'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-secondary">Dokter</label>
                <select class="form-select" name="dokter_id" onchange="this.form.submit()">
                    <option value="">Semua Dokter</option>
                    <?php foreach ($dokterList as $d): ?>
                        <option value="<?= $d->id ?? $d['id'] ?>" <?= (($filters['dokter_id'] ?? 0) == ($d->id ?? $d['id'])) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d->nama_dokter ?? $d['nama_dokter'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary">Hari Praktik</label>
                <select class="form-select" name="hari" onchange="this.form.submit()">
                    <option value="">Semua Hari</option>
                    <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $h): ?>
                        <option value="<?= $h ?>" <?= (($filters['hari'] ?? '') === $h) ? 'selected' : '' ?>><?= $h ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <a href="<?= BASE_URL ?>/jadwal" class="btn btn-outline-secondary w-100" title="Reset Filter">
                    <i class="fas fa-rotate-left"></i>
                </a>
            </div>
        </div>
    </form>

    <!-- Tabel Jadwal -->
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-muted small">
                    <th>Hari</th>
                    <th>Dokter</th>
                    <th>Poli / Unit</th>
                    <th>Jam Pelayanan</th>
                    <th>Kuota Harian</th>
                    <th>Status</th>
                    <?php if (in_array($_SESSION['user_role'] ?? '', ['admin'])): ?>
                        <th class="text-center" style="width: 140px;">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($list)): ?>
                    <?php foreach ($list as $j): ?>
                        <tr>
                            <td>
                                <span class="badge bg-primary fs-6 fw-semibold px-2 py-1">
                                    <?= htmlspecialchars($j->hari ?? $j['hari'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="fw-semibold text-dark">
                                <?= htmlspecialchars($j->nama_dokter ?? $j['nama_dokter'], ENT_QUOTES, 'UTF-8') ?>
                                <small class="text-muted d-block"><?= htmlspecialchars($j->spesialisasi ?? $j['spesialisasi'] ?? '-', ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= htmlspecialchars($j->nama_poli ?? $j['nama_poli'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="text-dark fw-medium">
                                <i class="fas fa-clock text-primary me-1"></i>
                                <?= substr($j->jam_mulai ?? $j['jam_mulai'], 0, 5) ?> - <?= substr($j->jam_selesai ?? $j['jam_selesai'], 0, 5) ?> WIB
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info border border-info-subtle">
                                    <?= $j->kuota ?? $j['kuota'] ?> Pasien
                                </span>
                            </td>
                            <td>
                                <?= ($j->is_active ?? $j['is_active']) == 1 
                                    ? '<span class="badge bg-success-subtle text-success">Aktif</span>' 
                                    : '<span class="badge bg-secondary-subtle text-secondary">Nonaktif</span>' ?>
                            </td>
                            <?php if (in_array($_SESSION['user_role'] ?? '', ['admin'])): ?>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/jadwal/edit/<?= $j->id ?? $j['id'] ?>" class="btn btn-outline-secondary" title="Edit Jadwal">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?= BASE_URL ?>/jadwal/delete/<?= $j->id ?? $j['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus/menonaktifkan jadwal ini?')">
                                            <?= SecurityHelper::csrfField() ?>
                                            <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-times fs-2 mb-2 d-block"></i>
                            Tidak ada jadwal dokter yang cocok dengan filter.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

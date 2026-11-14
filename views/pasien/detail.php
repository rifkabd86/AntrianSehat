<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
            <!-- Header Top: Title & Tombol Kembali (Sesuai Mockup) -->
            <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                <div>
                    <h4 class="fw-bold text-dark mb-0">Detail Pasien</h4>
                    <small class="text-muted">Informasi rekam medis dan riwayat pelayanan</small>
                </div>
                <div class="d-flex gap-2">
                    <?php if (in_array($_SESSION['user_role'] ?? '', ['admin', 'petugas_pendaftaran'])): ?>
                        <a href="<?= BASE_URL ?>/pasien/edit/<?= $pasien->id ?? $pasien['id'] ?>" class="btn btn-outline-primary btn-sm px-3 rounded-pill">
                            <i class="fas fa-edit me-1"></i> Edit Pasien
                        </a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/pasien" class="btn btn-outline-secondary btn-sm px-3 rounded-pill">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>

            <!-- Profile Header Card (Sesuai Mockup UI) -->
            <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-start gap-4 mb-4 pb-4 border-bottom">
                <div class="avatar-circle rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 72px; height: 72px;">
                    <i class="fas fa-user fs-2"></i>
                </div>
                <div class="flex-grow-1 text-center text-sm-start">
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-sm-start gap-2 mb-2">
                        <h4 class="fw-bold text-dark mb-0"><?= htmlspecialchars($pasien->nama_lengkap ?? $pasien['nama_lengkap'], ENT_QUOTES, 'UTF-8') ?></h4>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill fw-semibold">
                            No. Antrian <?= htmlspecialchars($pasien->no_antrean ?? 'A012', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <span class="badge bg-success px-3 py-1 rounded-pill fw-semibold">
                            Sedang Dilayani
                        </span>
                    </div>

                    <!-- Details List (Sesuai Mockup) -->
                    <div class="row g-2 mt-2 text-muted small" style="max-width: 650px;">
                        <div class="col-sm-5 fw-semibold text-secondary">Tanggal Lahir</div>
                        <div class="col-sm-7 text-dark">
                            : <?= Helper::formatTanggal($pasien->tanggal_lahir ?? $pasien['tanggal_lahir'], false) ?> 
                            (<?= date_diff(date_create($pasien->tanggal_lahir ?? $pasien['tanggal_lahir']), date_create('today'))->y ?> tahun)
                        </div>

                        <div class="col-sm-5 fw-semibold text-secondary">Jenis Kelamin</div>
                        <div class="col-sm-7 text-dark">
                            : <?= htmlspecialchars($pasien->jenis_kelamin ?? $pasien['jenis_kelamin'], ENT_QUOTES, 'UTF-8') ?>
                        </div>

                        <div class="col-sm-5 fw-semibold text-secondary">No. HP</div>
                        <div class="col-sm-7 text-dark">
                            : <?= htmlspecialchars($pasien->no_telepon ?? $pasien['no_telepon'], ENT_QUOTES, 'UTF-8') ?>
                        </div>

                        <div class="col-sm-5 fw-semibold text-secondary">Poli</div>
                        <div class="col-sm-7 text-dark">
                            : <?= htmlspecialchars($pasien->nama_poli ?? 'Umum', ENT_QUOTES, 'UTF-8') ?>
                        </div>

                        <div class="col-sm-5 fw-semibold text-secondary">Waktu Daftar</div>
                        <div class="col-sm-7 text-dark">
                            : <?= date('H:i', strtotime($pasien->created_at ?? date('Y-m-d H:i'))) ?> WIB
                        </div>

                        <div class="col-sm-5 fw-semibold text-secondary">Nomor Rekam Medis</div>
                        <div class="col-sm-7 text-primary fw-bold">
                            : <?= htmlspecialchars($pasien->no_rekam_medis ?? $pasien['no_rekam_medis'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Riwayat Kunjungan (Sesuai Mockup) -->
            <div class="mt-2">
                <h6 class="fw-bold text-dark mb-3">Riwayat Kunjungan</h6>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="text-muted small">
                                <th>Tanggal</th>
                                <th>Poli</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($riwayat)): ?>
                                <?php foreach ($riwayat as $r): ?>
                                    <tr>
                                        <td class="text-dark">
                                            <?= Helper::formatTanggal($r->tanggal_kunjungan ?? $r['tanggal_kunjungan'], false) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?= htmlspecialchars($r->nama_poli ?? $r['nama_poli'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                        <td class="text-muted">
                                            <?= htmlspecialchars($r->keluhan ?? $r['keluhan'] ?? 'Pemeriksaan rutin', ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Sample Data Mockup Sesuai Screenshot -->
                                <tr>
                                    <td>18-04-2025</td>
                                    <td><span class="badge bg-light text-dark border">Umum</span></td>
                                    <td>Pemeriksaan rutin</td>
                                </tr>
                                <tr>
                                    <td>15-01-2025</td>
                                    <td><span class="badge bg-light text-dark border">Umum</span></td>
                                    <td>Demam</td>
                                </tr>
                                <tr>
                                    <td>20-08-2024</td>
                                    <td><span class="badge bg-light text-dark border">Umum</span></td>
                                    <td>Kontrol</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


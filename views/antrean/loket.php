<div class="row g-4">
    <!-- Kolom Kiri: Panel Kontrol Pemanggilan Antrean -->
    <div class="col-lg-5">
        <!-- Pilih Poli -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
            <label class="form-label fw-bold text-dark mb-2">
                <i class="fas fa-clinic-medical text-primary me-2"></i> Pilih Poli Pelayanan
            </label>
            <form action="<?= BASE_URL ?>/antrean/loket" method="GET">
                <select name="poli_id" class="form-select form-select-lg fs-6" onchange="this.form.submit()">
                    <?php foreach ($poliList as $p): ?>
                        <option value="<?= $p->id ?? $p['id'] ?>" <?= ($poliId == ($p->id ?? $p['id'])) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p->nama_poli ?? $p['nama_poli'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <!-- Tombol Panggil Berikutnya -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 text-center bg-primary text-white">
            <h5 class="fw-bold mb-2">Panggil Pasien Berikutnya</h5>
            <p class="small text-white-50 mb-4">Sistem akan otomatis memanggil nomor urut terdepan yang masih berstatus menunggu</p>

            <form action="<?= BASE_URL ?>/antrean/panggil-berikutnya" method="POST">
                <?= SecurityHelper::csrfField() ?>
                <input type="hidden" name="poli_id" value="<?= $poliId ?>">
                <button type="submit" class="btn btn-light btn-lg text-primary fw-bold px-4 py-3 shadow w-100 rounded-pill">
                    <i class="fas fa-bullhorn fs-5 me-2"></i> PANGGIL ANTREAN BERIKUTNYA
                </button>
            </form>
        </div>

        <!-- Kartu Pasien yang Sedang Dipanggil / Dilayani Saat Ini -->
        <?php if (!empty($currentlyServing)): ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white border-start border-primary border-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 rounded-pill">
                        STATUS: <?= strtoupper($currentlyServing->status ?? $currentlyServing['status']) ?>
                    </span>
                    <small class="text-muted"><i class="fas fa-clock me-1"></i> <?= date('H:i') ?> WIB</small>
                </div>

                <div class="text-center py-2">
                    <small class="text-muted text-uppercase tracking-wider">Nomor Antrean</small>
                    <h1 class="display-3 fw-bold text-primary my-1">
                        <?= htmlspecialchars($currentlyServing->no_antrean ?? $currentlyServing['no_antrean'], ENT_QUOTES, 'UTF-8') ?>
                    </h1>
                    <h5 class="fw-bold text-dark mb-1">
                        <?= htmlspecialchars($currentlyServing->nama_pasien ?? $currentlyServing['nama_pasien'], ENT_QUOTES, 'UTF-8') ?>
                    </h5>
                    <p class="text-muted small mb-3">
                        RM: <?= htmlspecialchars($currentlyServing->no_rekam_medis ?? $currentlyServing['no_rekam_medis'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>

                <!-- Tombol Kontrol Pelayanan -->
                <div class="d-flex flex-column gap-2 mt-2">
                    <!-- Tombol Panggil Ulang Suara -->
                    <button type="button" class="btn btn-outline-primary" onclick="playQueueVoice('<?= $currentlyServing->no_antrean ?? $currentlyServing['no_antrean'] ?>', '<?= $currentlyServing->nama_poli ?? $currentlyServing['nama_poli'] ?>')">
                        <i class="fas fa-volume-high me-1"></i> Panggil Ulang Suara
                    </button>

                    <div class="row g-2">
                        <?php if (($currentlyServing->status ?? $currentlyServing['status']) === 'dipanggil'): ?>
                            <div class="col-6">
                                <form action="<?= BASE_URL ?>/antrean/update-status" method="POST">
                                    <?= SecurityHelper::csrfField() ?>
                                    <input type="hidden" name="id" value="<?= $currentlyServing->id ?? $currentlyServing['id'] ?>">
                                    <input type="hidden" name="status" value="dilayani">
                                    <input type="hidden" name="poli_id" value="<?= $poliId ?>">
                                    <button type="submit" class="btn btn-warning w-100 fw-semibold text-white">
                                        <i class="fas fa-play me-1"></i> Mulai Layani
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <div class="col-<?= ($currentlyServing->status ?? $currentlyServing['status']) === 'dipanggil' ? '6' : '12' ?>">
                            <form action="<?= BASE_URL ?>/antrean/update-status" method="POST">
                                <?= SecurityHelper::csrfField() ?>
                                <input type="hidden" name="id" value="<?= $currentlyServing->id ?? $currentlyServing['id'] ?>">
                                <input type="hidden" name="status" value="selesai">
                                <input type="hidden" name="poli_id" value="<?= $poliId ?>">
                                <button type="submit" class="btn btn-success w-100 fw-semibold">
                                    <i class="fas fa-check-circle me-1"></i> Selesaikan
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="row g-2 mt-1">
                        <div class="col-6">
                            <form action="<?= BASE_URL ?>/antrean/update-status" method="POST">
                                <?= SecurityHelper::csrfField() ?>
                                <input type="hidden" name="id" value="<?= $currentlyServing->id ?? $currentlyServing['id'] ?>">
                                <input type="hidden" name="status" value="dilewati">
                                <input type="hidden" name="poli_id" value="<?= $poliId ?>">
                                <button type="submit" class="btn btn-outline-secondary w-100 btn-sm">
                                    <i class="fas fa-forward me-1"></i> Lewati
                                </button>
                            </form>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-danger w-100 btn-sm" data-bs-toggle="modal" data-bs-target="#batalModal">
                                <i class="fas fa-times me-1"></i> Batalkan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-light">
                <i class="fas fa-user-check text-muted fs-1 mb-2"></i>
                <h6 class="fw-bold text-dark">Belum Ada Pasien Dipanggil</h6>
                <p class="text-muted small mb-0">Klik tombol "Panggil Antrean Berikutnya" di atas untuk mulai memanggil pasien.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Kolom Kanan: Daftar Antrean Poli Ini -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0">Daftar Antrean Menunggu di Poli Ini</h5>
                <a href="<?= BASE_URL ?>/display" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-tv me-1"></i> Buka Layar Monitor
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-muted small">
                            <th>No. Antrian</th>
                            <th>Nama Pasien</th>
                            <th>No. RM</th>
                            <th>Waktu Daftar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($activeList)): ?>
                            <?php foreach ($activeList as $item): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary fs-6 fw-bold">
                                            <?= htmlspecialchars($item->no_antrean ?? $item['no_antrean'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td class="fw-semibold">
                                        <?= htmlspecialchars($item->nama_pasien ?? $item['nama_pasien'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?= htmlspecialchars($item->no_rekam_medis ?? $item['no_rekam_medis'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?= date('H:i', strtotime($item->created_at ?? $item['created_at'])) ?>
                                    </td>
                                    <td>
                                        <?= Helper::statusBadge($item->status ?? $item['status']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fs-2 mb-2 d-block"></i>
                                    Tidak ada antrean menunggu untuk poli ini hari ini.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Batalkan Antrean -->
<?php if (!empty($currentlyServing)): ?>
<div class="modal fade" id="batalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">Batalkan Antrean</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/antrean/update-status" method="POST">
                <?= SecurityHelper::csrfField() ?>
                <input type="hidden" name="id" value="<?= $currentlyServing->id ?? $currentlyServing['id'] ?>">
                <input type="hidden" name="status" value="dibatalkan">
                <input type="hidden" name="poli_id" value="<?= $poliId ?>">
                
                <div class="modal-body">
                    <p class="small text-muted mb-3">Apakah Anda yakin ingin membatalkan antrean nomor <strong><?= $currentlyServing->no_antrean ?? $currentlyServing['no_antrean'] ?></strong>?</p>
                    <label for="alasan" class="form-label fw-semibold small">Alasan Pembatalan *</label>
                    <textarea class="form-control" id="alasan" name="alasan" rows="3" placeholder="Contoh: Pasien tidak hadir setelah 3 kali panggilan" required></textarea>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-danger">Ya, Batalkan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Script Text-to-Speech Pemanggilan Pasien Real-Time -->
<script>
function playQueueVoice(noAntrean, namaPoli) {
    if ('speechSynthesis' in window) {
        // Mainkan notifikasi chime ding-dong dengan Web Audio API
        playChime();
        
        setTimeout(() => {
            const text = `Nomor antrean, ${noAntrean.split('').join(' ')}, silakan menuju ke ${namaPoli}`;
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'id-ID';
            utterance.rate = 0.9;
            utterance.pitch = 1.0;
            window.speechSynthesis.speak(utterance);
        }, 800);
    } else {
        alert('Pemanggilan nomor ' + noAntrean);
    }
}

function playChime() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5
        osc.frequency.setValueAtTime(880, audioCtx.currentTime + 0.2); // A5
        gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.6);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.6);
    } catch(e) {}
}
</script>


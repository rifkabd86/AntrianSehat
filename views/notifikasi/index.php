<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <h4 class="fw-bold text-dark mb-0">Notifikasi Anda</h4>
                    <small class="text-muted">Pemberitahuan pendaftaran, pemanggilan antrean, dan status pelayanan</small>
                </div>
            </div>

            <div class="list-group list-group-flush">
                <?php if (!empty($list)): ?>
                    <?php foreach ($list as $n): ?>
                        <div class="list-group-item px-0 py-3 d-flex align-items-start gap-3">
                            <div class="rounded-circle bg-primary-subtle text-primary p-2 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($n->judul ?? $n['judul'], ENT_QUOTES, 'UTF-8') ?></h6>
                                <p class="text-muted small mb-1"><?= htmlspecialchars($n->pesan ?? $n['pesan'], ENT_QUOTES, 'UTF-8') ?></p>
                                <small class="text-muted" style="font-size: 0.75rem;">
                                    <i class="fas fa-clock me-1"></i> <?= Helper::timeAgo($n->created_at ?? $n['created_at']) ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-bell-slash fs-1 mb-2 d-block"></i>
                        Tidak ada notifikasi saat ini.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


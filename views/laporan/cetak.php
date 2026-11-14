<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kunjungan Pasien - ANTRIAN SEHAT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 12px; color: #111; }
        .kop-surat { border-bottom: 3px double #000; padding-bottom: 12px; margin-bottom: 20px; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="p-4" onload="window.print()">

    <div class="no-print mb-3 text-end">
        <button class="btn btn-primary btn-sm" onclick="window.print()">Cetak</button>
        <button class="btn btn-secondary btn-sm" onclick="window.close()">Tutup</button>
    </div>

    <!-- Kop Laporan Puskesmas -->
    <div class="kop-surat text-center">
        <h4 class="fw-bold mb-0 text-uppercase">PEMERINTAH KABUPATEN / KOTA</h4>
        <h3 class="fw-bold mb-1 text-uppercase">DINAS KESEHATAN &bull; PUSKESMAS SEHAT SENTOSA</h3>
        <p class="mb-0 text-muted small">Jl. Merdeka No. 100 &bull; Telp. (021) 555-0199 &bull; Email: puskesmas@sehatsentosa.go.id</p>
    </div>

    <div class="text-center mb-4">
        <h5 class="fw-bold mb-1 text-uppercase">LAPORAN KUNJUNGAN DAN ANTREAN PASIEN</h5>
        <p class="text-muted small">Periode: <?= Helper::formatTanggal($startDate, false) ?> s.d. <?= Helper::formatTanggal($endDate, false) ?></p>
    </div>

    <table class="table table-bordered table-sm align-middle">
        <thead class="table-light text-center">
            <tr>
                <th style="width: 40px;">No</th>
                <th>Tanggal</th>
                <th>No. Antrean</th>
                <th>No. RM</th>
                <th>Nama Pasien</th>
                <th>Poli Tujuan</th>
                <th>Dokter</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($rows)): ?>
                <?php $no = 1; foreach ($rows as $r): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td><?= Helper::formatTanggal($r->tanggal_antrean ?? $r['tanggal_antrean'], false) ?></td>
                        <td class="text-center fw-bold"><?= $r->no_antrean ?? $r['no_antrean'] ?></td>
                        <td><?= $r->no_rekam_medis ?? $r['no_rekam_medis'] ?></td>
                        <td class="fw-semibold"><?= $r->nama_pasien ?? $r['nama_pasien'] ?></td>
                        <td><?= $r->nama_poli ?? $r['nama_poli'] ?></td>
                        <td><?= $r->nama_dokter ?? $r['nama_dokter'] ?></td>
                        <td class="text-center"><?= ucfirst($r->status ?? $r['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center py-3">Tidak ada data kunjungan</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="row mt-5 pt-3">
        <div class="col-8"></div>
        <div class="col-4 text-center">
            <p class="mb-1">Dicetak pada: <?= date('d F Y') ?></p>
            <p class="mb-5">Kepala Puskesmas,</p>
            <p class="fw-bold text-decoration-underline mb-0">dr. H. Budi Santoso, M.Kes</p>
            <small>NIP. 19780512 200312 1 002</small>
        </div>
    </div>

</body>
</html>


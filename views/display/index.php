<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MONITOR ANTREAN PUSKESMAS - ANTRIAN SEHAT</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts Inter & Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #2563EB;
            --primary-dark: #1E40AF;
            --success: #10B981;
            --warning: #F59E0B;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            color: #F8FAFC;
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }

        .display-header {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(10px);
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }

        .font-numbers {
            font-family: 'Outfit', sans-serif;
        }

        .called-card {
            background: linear-gradient(135deg, #1E40AF 0%, #2563EB 100%);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(37, 99, 235, 0.35);
            border: 2px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .called-number {
            font-size: 7.5rem;
            line-height: 1;
            letter-spacing: 4px;
            font-weight: 900;
            text-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }

        .poli-card {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .poli-card:hover {
            transform: translateY(-2px);
            border-color: rgba(37, 99, 235, 0.5);
        }

        .next-queue-item {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
        }

        .pulse-indicator {
            width: 14px;
            height: 14px;
            background-color: #10B981;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse-dot 1.8s infinite;
        }

        @keyframes pulse-dot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 12px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .running-text {
            background: #0284C7;
            color: #FFFFFF;
            font-size: 1.1rem;
            font-weight: 500;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Top Display Header -->
    <header class="display-header py-3 px-4 px-md-5 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <div class="bg-primary text-white rounded-circle p-2 px-3 me-3 fs-3 shadow">
                <i class="fas fa-heartbeat"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0 text-white font-numbers" style="letter-spacing: 1px;">ANTRIAN SEHAT</h3>
                <small class="text-white-50">PUSKESMAS SEHAT SENTOSA &bull; SISTEM INFORMASI PELAYANAN</small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-4">
            <div class="d-flex align-items-center gap-2">
                <span class="pulse-indicator"></span>
                <span class="text-success fw-bold small">MONITOR AKTIF</span>
            </div>
            <div class="text-end">
                <h3 class="fw-bold mb-0 text-white font-numbers" id="liveClockDisplay">--:--:--</h3>
                <small class="text-white-50" id="liveDateDisplay"><?= Helper::formatTanggal(date('Y-m-d')) ?></small>
            </div>
        </div>
    </header>

    <!-- Main Display Body -->
    <main class="container-fluid px-4 px-md-5 py-4 flex-grow-1">
        <div class="row g-4 h-100">
            <!-- Kolom Kiri (60%): Panggilan Utama Besar -->
            <div class="col-lg-7 d-flex flex-column justify-content-center">
                <div class="called-card p-5 text-center text-white h-100 d-flex flex-column justify-content-center">
                    <span class="badge bg-white text-primary fs-5 px-4 py-2 rounded-pill fw-bold text-uppercase mx-auto mb-3 shadow">
                        <i class="fas fa-bullhorn me-2"></i> NOMOR ANTREAN DIPANGGIL
                    </span>

                    <!-- Nomor Antrean Raksasa -->
                    <div class="called-number font-numbers text-white my-2" id="currentQueueNumber">
                        —
                    </div>

                    <h2 class="fw-bold text-white mb-2" id="currentPoliName">
                        BELUM ADA PANGGILAN AKTIF
                    </h2>

                    <div class="text-white-50 fs-4 mb-4" id="currentDoctorName">
                        <i class="fas fa-user-doctor me-2" aria-hidden="true"></i><span id="currentDoctorLabel"></span>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan (40%): Antrean Tiap Poli & Antrean Berikutnya -->
            <div class="col-lg-5 d-flex flex-column gap-3">
                <!-- Status Per Poli -->
                <div class="card bg-transparent border-0">
                    <h5 class="fw-bold text-white mb-3 d-flex align-items-center">
                        <i class="fas fa-hospital me-2 text-primary"></i> PANGGILAN TERAKHIR PER POLI
                    </h5>
                    
                    <div class="d-flex flex-column gap-2" id="poliStatusContainer">
                        <p class="text-white-50 mb-0">Memuat informasi poli...</p>
                    </div>
                </div>

                <!-- Antrean Menunggu Berikutnya -->
                <div class="card bg-transparent border-0 mt-2">
                    <h5 class="fw-bold text-white mb-2 d-flex align-items-center">
                        <i class="fas fa-list-ol me-2 text-info"></i> ANTREAN BERIKUTNYA
                    </h5>
                    <div class="d-flex flex-column gap-2" id="nextQueuesContainer">
                        <p class="text-white-50 mb-0">Memuat antrean...</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Running Text Footer -->
    <footer class="running-text py-2 px-4 shadow">
        <marquee behavior="scroll" direction="left" scrollamount="6">
            Selamat Datang di Puskesmas Sehat Sentosa &bull; Harap perhatikan nomor antrean pada layar &bull; Siapkan Kartu Identitas (KTP/BPJS) saat dipanggil ke ruang pemeriksaan &bull; Jaga kebersihan dan ketertiban area ruang tunggu &bull; Layanan Pengaduan: 0812-0000-0000
        </marquee>
    </footer>

    <!-- Auto Refresh Script via Fetch API (Setiap 4 detik) -->
    <script>
    let lastCalled = 'A012';

    function updateLiveClock() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        document.getElementById('liveClockDisplay').innerText = timeStr;
    }
    setInterval(updateLiveClock, 1000);
    updateLiveClock();

    function fetchDisplayData() {
        fetch('<?= BASE_URL ?>/antrean/display-data')
            .then(res => res.json())
            .then(data => {
                if (data.current) {
                    const no = data.current.no_antrean;
                    const poli = data.current.nama_poli;
                    const dokter = data.current.nama_dokter;

                    document.getElementById('currentQueueNumber').textContent = no;
                    document.getElementById('currentPoliName').textContent = poli.toUpperCase();
                    document.getElementById('currentDoctorLabel').textContent = dokter;

                    // Jika nomor berubah, bunyikan lonceng panggilan suara
                    if (no !== lastCalled) {
                        lastCalled = no;
                        announceQueue(no, poli);
                    }
                } else {
                    document.getElementById('currentQueueNumber').textContent = '—';
                    document.getElementById('currentPoliName').textContent = 'BELUM ADA PANGGILAN AKTIF';
                    document.getElementById('currentDoctorLabel').textContent = '';
                    lastCalled = '';
                }

                const poliContainer = document.getElementById('poliStatusContainer');
                poliContainer.replaceChildren();
                (data.poli_list || []).forEach(p => {
                    const item = document.createElement('div');
                    item.className = 'poli-card p-3 d-flex justify-content-between align-items-center';
                    const description = document.createElement('div');
                    const name = document.createElement('h6');
                    name.className = 'fw-bold text-white mb-0';
                    name.textContent = p.nama_poli;
                    const waiting = document.createElement('small');
                    waiting.className = 'text-white-50';
                    waiting.textContent = `Sisa antrean: ${p.sisa_antrean || 0}`;
                    const number = document.createElement('span');
                    number.className = 'font-numbers fs-3 fw-bold text-primary';
                    number.textContent = p.no_sedang_dipanggil || '-';
                    description.append(name, waiting);
                    item.append(description, number);
                    poliContainer.appendChild(item);
                });
                if (!poliContainer.childElementCount) {
                    const empty = document.createElement('p');
                    empty.className = 'text-white-50 mb-0';
                    empty.textContent = 'Belum ada poli aktif.';
                    poliContainer.appendChild(empty);
                }

                const nextContainer = document.getElementById('nextQueuesContainer');
                nextContainer.replaceChildren();
                (data.next_queues || []).forEach(q => {
                    const item = document.createElement('div');
                    item.className = 'next-queue-item p-2 px-3 d-flex justify-content-between align-items-center';
                    const number = document.createElement('span');
                    number.className = 'font-numbers fw-bold text-info fs-5';
                    number.textContent = q.no_antrean;
                    const clinic = document.createElement('span');
                    clinic.className = 'badge bg-secondary';
                    clinic.textContent = q.nama_poli;
                    item.append(number, clinic);
                    nextContainer.appendChild(item);
                });
                if (!nextContainer.childElementCount) {
                    const empty = document.createElement('p');
                    empty.className = 'text-white-50 mb-0';
                    empty.textContent = 'Belum ada antrean menunggu.';
                    nextContainer.appendChild(empty);
                }
            })
            .catch(err => console.log('Polling display...', err));
    }

    // Jalankan auto refresh polling setiap 4 detik
    setInterval(fetchDisplayData, 4000);

    function announceQueue(noAntrean, namaPoli) {
        if ('speechSynthesis' in window) {
            playDingDong();
            setTimeout(() => {
                const text = `Nomor antrean, ${noAntrean.split('').join(' ')}, silakan menuju ke ${namaPoli}`;
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'id-ID';
                utterance.rate = 0.88;
                window.speechSynthesis.speak(utterance);
            }, 600);
        }
    }

    function playDingDong() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.frequency.setValueAtTime(587.33, ctx.currentTime);
            osc.frequency.setValueAtTime(880, ctx.currentTime + 0.2);
            gain.gain.setValueAtTime(0.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.6);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.6);
        } catch (error) {
            console.warn('Audio notification could not be played.', error);
        }
    }
    </script>
</body>
</html>

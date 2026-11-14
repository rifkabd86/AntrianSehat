document.addEventListener('DOMContentLoaded', () => {
    const dashboard = document.querySelector('[data-dashboard]');
    const toastStack = document.querySelector('[data-toast-stack]');

    const showToast = (message) => {
        if (!toastStack) return;
        const toast = document.createElement('div');
        toast.className = 'app-toast';
        const icon = document.createElement('i');
        icon.className = 'bi bi-info-circle';
        icon.setAttribute('aria-hidden', 'true');
        const text = document.createElement('span');
        text.textContent = message;
        const close = document.createElement('button');
        close.type = 'button';
        close.setAttribute('aria-label', 'Tutup notifikasi');
        close.innerHTML = '<i class="bi bi-x" aria-hidden="true"></i>';
        close.addEventListener('click', () => toast.remove());
        toast.append(icon, text, close);
        toastStack.append(toast);
        window.setTimeout(() => toast.remove(), 3600);
    };

    const dateNode = document.querySelector('[data-current-date]');
    if (dateNode) {
        dateNode.textContent = new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        }).format(new Date());
    }

    const patientDate = document.querySelector('[data-patient-date]');
    if (patientDate) {
        patientDate.textContent = new Intl.DateTimeFormat('id-ID', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        }).format(new Date()).toLocaleUpperCase('id-ID');
    }

    document.querySelectorAll('[data-auth-switch]').forEach((button) => {
        button.addEventListener('click', () => {
            const target = button.dataset.authSwitch;
            document.querySelectorAll('[data-auth-view]').forEach((view) => {
                view.hidden = view.dataset.authView !== target;
            });
            document.querySelector(`[data-auth-view="${target}"] input`)?.focus();
        });
    });

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;
            const showPassword = input.type === 'password';
            input.type = showPassword ? 'text' : 'password';
            button.setAttribute('aria-label', showPassword ? 'Sembunyikan password' : 'Tampilkan password');
            button.querySelector('i')?.classList.toggle('bi-eye', !showPassword);
            button.querySelector('i')?.classList.toggle('bi-eye-slash', showPassword);
        });
    });

    document.querySelectorAll('form[data-demo-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            const password = form.querySelector('#register-password');
            const confirm = form.querySelector('#register-confirm');
            if (password && confirm && password.value !== confirm.value) {
                confirm.setCustomValidity('Password belum sama.');
                confirm.reportValidity();
                confirm.addEventListener('input', () => confirm.setCustomValidity(''), { once: true });
                return;
            }
            showToast('Tampilan ini belum terhubung ke layanan.');
            const modal = form.closest('.modal');
            if (modal && window.bootstrap) {
                window.bootstrap.Modal.getOrCreateInstance(modal).hide();
            }
        });
    });

    document.querySelectorAll('[data-toast]').forEach((element) => {
        element.addEventListener('click', (event) => {
            event.preventDefault();
            const message = element.dataset.toast.toLocaleLowerCase('id-ID');
            const module = message.includes('pendaftaran') ? 'patients'
                : message.includes('poli') ? 'clinics'
                    : message.includes('antrean') ? 'queue'
                        : message.includes('jadwal') ? 'history'
                            : null;
            if (module) openModule(module);
            else showToast(element.dataset.toast);
        });
    });

    const registrationModal = document.getElementById('registrationModal');
    const moduleModal = document.getElementById('moduleModal');
    const moduleTitle = moduleModal?.querySelector('[data-module-title]');
    const moduleBody = moduleModal?.querySelector('[data-module-body]');
    const moduleAction = moduleModal?.querySelector('[data-module-action]');

    const moduleViews = {
        patients: {
            title: 'Data Pasien',
            action: 'Buat pendaftaran',
            content: '<div class="table-responsive"><table class="table app-table"><thead><tr><th>PASIEN</th><th>NOMOR RM</th><th>TELEPON</th><th>KUNJUNGAN TERAKHIR</th></tr></thead><tbody><tr><td>Rina Amelia</td><td>RM-002184</td><td>0812 4455 6677</td><td>Hari ini</td></tr><tr><td>Dimas Saputra</td><td>RM-001967</td><td>0813 2277 1199</td><td>Hari ini</td></tr><tr><td>Siti Aisyah</td><td>RM-001825</td><td>0812 8899 0011</td><td>18 Sep 2026</td></tr></tbody></table></div>',
        },
        clinics: {
            title: 'Data Poli',
            content: '<div class="table-responsive"><table class="table app-table"><thead><tr><th>POLI</th><th>DOKTER AKTIF</th><th>ANTREAN HARI INI</th><th>STATUS</th></tr></thead><tbody><tr><td>Poli Umum</td><td>2 dokter</td><td>38 pasien</td><td><span class="status-pill status-done">Buka</span></td></tr><tr><td>Poli Gigi</td><td>1 dokter</td><td>19 pasien</td><td><span class="status-pill status-done">Buka</span></td></tr><tr><td>Poli Anak</td><td>1 dokter</td><td>16 pasien</td><td><span class="status-pill status-done">Buka</span></td></tr><tr><td>Poli KIA</td><td>1 dokter</td><td>13 pasien</td><td><span class="status-pill status-done">Buka</span></td></tr></tbody></table></div>',
        },
        doctors: {
            title: 'Data Dokter',
            content: '<div class="table-responsive"><table class="table app-table"><thead><tr><th>DOKTER</th><th>POLI</th><th>JADWAL HARI INI</th><th>STATUS</th></tr></thead><tbody><tr><td>dr. Budi Santoso</td><td>Poli Umum</td><td>08.00–12.00</td><td><span class="status-pill status-serving">Praktik</span></td></tr><tr><td>drg. Dewi Lestari</td><td>Poli Gigi</td><td>08.00–12.00</td><td><span class="status-pill status-serving">Praktik</span></td></tr><tr><td>dr. Andi Pratama</td><td>Poli Anak</td><td>08.00–12.00</td><td><span class="status-pill status-serving">Praktik</span></td></tr></tbody></table></div>',
        },
        history: {
            title: 'Riwayat Kunjungan',
            content: '<div class="table-responsive"><table class="table app-table"><thead><tr><th>TANGGAL</th><th>PASIEN</th><th>POLI</th><th>DOKTER</th><th>STATUS</th></tr></thead><tbody><tr><td>5 Nov 2026</td><td>Rina Amelia</td><td>Poli Umum</td><td>dr. Budi Santoso</td><td><span class="status-pill status-done">Selesai</span></td></tr><tr><td>5 Nov 2026</td><td>Dimas Saputra</td><td>Poli Gigi</td><td>drg. Dewi Lestari</td><td><span class="status-pill status-serving">Dilayani</span></td></tr><tr><td>18 Sep 2026</td><td>Siti Aisyah</td><td>Poli KIA</td><td>dr. Nurhayati</td><td><span class="status-pill status-done">Selesai</span></td></tr></tbody></table></div>',
        },
        reports: {
            title: 'Laporan Pelayanan',
            action: 'Unduh CSV',
            content: '<div class="report-summary"><p class="text-secondary">Ringkasan kunjungan pada pratinjau</p><div class="patient-stat-grid"><div class="patient-mini-stat"><span><strong>86</strong><small>Pendaftaran hari ini</small></span></div><div class="patient-mini-stat"><span><strong>56</strong><small>Kunjungan selesai</small></span></div><div class="patient-mini-stat"><span><strong>4</strong><small>Poli melayani</small></span></div></div><p class="small text-secondary mt-3 mb-0">Gunakan tombol unduh untuk mengekspor ringkasan contoh sebagai CSV.</p></div>',
        },
        settings: {
            title: 'Pengaturan Tampilan',
            action: 'Simpan preferensi',
            content: '<label class="settings-option"><span><strong>Notifikasi suara</strong><small>Putar bunyi saat nomor antrean dipanggil</small></span><input class="form-check-input" type="checkbox" data-pref-sound></label><label class="settings-option"><span><strong>Ringkas tabel</strong><small>Gunakan jarak baris yang lebih rapat</small></span><input class="form-check-input" type="checkbox" data-pref-compact></label>',
        },
        profile: {
            title: 'Profil Pengguna',
            content: '<div class="profile-preview"><span class="avatar avatar-large">NP</span><div><strong>Nadia Putri</strong><small>Administrator · Puskesmas Sehat Sentosa</small><small>nadia.putri@antriansehat.id</small></div></div><p class="small text-secondary mt-3 mb-0">Informasi profil pada layar ini merupakan data pratinjau.</p>',
        },
        notifications: {
            title: 'Notifikasi',
            action: 'Tandai dibaca',
            content: '<div class="module-notification"><strong>Pendaftaran baru</strong><span>4 pasien mendaftar hari ini</span><time>09.42</time></div><div class="module-notification"><strong>Kunjungan selesai</strong><span>Poli Umum · dr. Budi Santoso</span><time>09.31</time></div><div class="module-notification"><strong>Antrean dipanggil</strong><span>G-019 · Poli Gigi</span><time>09.24</time></div>',
        },
    };

    const openModule = (key) => {
        if (key === 'registration') {
            if (registrationModal && window.bootstrap) window.bootstrap.Modal.getOrCreateInstance(registrationModal).show();
            return;
        }
        if (key === 'queue') {
            const activePanel = dashboard?.querySelector('.role-panel:not([hidden])');
            const target = activePanel?.querySelector('.current-queue-card, .patient-queue-card, .admin-bottom-grid');
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                closeSidebar();
                return;
            }
        }

        const view = moduleViews[key];
        if (!view || !moduleModal || !moduleTitle || !moduleBody || !window.bootstrap) return;
        moduleTitle.textContent = view.title;
        moduleBody.innerHTML = view.content;
        moduleAction.hidden = !view.action;
        moduleAction.textContent = view.action ?? '';
        moduleAction.dataset.action = key;
        if (key === 'settings') {
            moduleBody.querySelector('[data-pref-sound]').checked = localStorage.getItem('as-pref-sound') === 'true';
            moduleBody.querySelector('[data-pref-compact]').checked = localStorage.getItem('as-pref-compact') === 'true';
        }
        window.bootstrap.Modal.getOrCreateInstance(moduleModal).show();
        closeSidebar();
    };

    document.querySelectorAll('[data-module]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            openModule(link.dataset.module);
        });
    });

    document.querySelectorAll('[data-nav-message]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            openModule(link.dataset.navMessage === 'Antrean' ? 'queue' : 'history');
        });
    });

    moduleAction?.addEventListener('click', () => {
        const action = moduleAction.dataset.action;
        if (action === 'patients') {
            window.bootstrap.Modal.getOrCreateInstance(moduleModal).hide();
            if (registrationModal) window.bootstrap.Modal.getOrCreateInstance(registrationModal).show();
            return;
        }
        if (action === 'settings') {
            localStorage.setItem('as-pref-sound', String(moduleBody.querySelector('[data-pref-sound]').checked));
            const compact = moduleBody.querySelector('[data-pref-compact]').checked;
            localStorage.setItem('as-pref-compact', String(compact));
            dashboard?.classList.toggle('compact-tables', compact);
            showToast('Preferensi tampilan tersimpan di browser ini.');
            window.bootstrap.Modal.getOrCreateInstance(moduleModal).hide();
            return;
        }
        if (action === 'notifications') {
            document.querySelector('.notification-dot')?.remove();
            document.querySelector('.sidebar-unread')?.remove();
            showToast('Semua notifikasi ditandai sudah dibaca.');
            window.bootstrap.Modal.getOrCreateInstance(moduleModal).hide();
            return;
        }
        if (action === 'reports') {
            const csv = 'Tanggal,Laporan,Nilai\n5 Nov 2026,Pendaftaran,86\n5 Nov 2026,Kunjungan selesai,56\n5 Nov 2026,Poli aktif,4';
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'laporan-antrian-sehat.csv';
            link.click();
            URL.revokeObjectURL(link.href);
            showToast('Laporan CSV berhasil diunduh.');
            return;
        }
        window.bootstrap.Modal.getOrCreateInstance(moduleModal).hide();
    });
    document.querySelectorAll('[data-open-registration]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            if (registrationModal && window.bootstrap) {
                window.bootstrap.Modal.getOrCreateInstance(registrationModal).show();
            }
        });
    });

    const sidebar = document.getElementById('appSidebar');
    const scrim = document.querySelector('[data-sidebar-close]');
    const closeSidebar = () => {
        sidebar?.classList.remove('is-open');
        scrim?.classList.remove('is-visible');
        document.body.classList.remove('sidebar-collapsed-mobile');
    };

    document.querySelector('[data-sidebar-open]')?.addEventListener('click', () => {
        sidebar?.classList.add('is-open');
        scrim?.classList.add('is-visible');
    });
    scrim?.addEventListener('click', closeSidebar);

    document.querySelector('[data-sidebar-collapse]')?.addEventListener('click', () => {
        dashboard?.classList.toggle('sidebar-collapsed');
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeSidebar();
        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
            const search = document.querySelector('.topbar-search input');
            if (search) {
                event.preventDefault();
                search.focus();
            }
        }
    });

    document.querySelectorAll('[data-table-search]').forEach((input) => {
        input.addEventListener('input', () => {
            const activePanel = dashboard?.querySelector('.role-panel:not([hidden])') ?? document;
            const query = input.value.trim().toLocaleLowerCase('id-ID');
            activePanel.querySelectorAll('[data-search-table] tbody tr').forEach((row) => {
                row.hidden = !row.textContent.toLocaleLowerCase('id-ID').includes(query);
            });
        });
    });

    document.querySelector('[data-queue-filter]')?.addEventListener('click', (event) => {
        const button = event.currentTarget;
        const states = ['Semua status', 'Menunggu'];
        const nextState = (states.indexOf(button.dataset.state ?? states[0]) + 1) % states.length;
        button.dataset.state = states[nextState];
        button.innerHTML = `${states[nextState]} <i class="bi bi-chevron-down" aria-hidden="true"></i>`;
        const rows = document.querySelectorAll('[data-queue-list] tr');
        rows.forEach((row) => {
            row.hidden = nextState === 1 && !row.textContent.includes('Menunggu');
        });
    });

    document.querySelector('[data-date-filter]')?.addEventListener('click', (event) => {
        const button = event.currentTarget;
        const states = ['Hari ini', '7 hari', 'Semua'];
        const nextState = (states.indexOf(button.dataset.state ?? states[0]) + 1) % states.length;
        button.dataset.state = states[nextState];
        button.innerHTML = `${states[nextState]} <i class="bi bi-chevron-down" aria-hidden="true"></i>`;
        showToast(`Rentang tampilan: ${states[nextState]}.`);
    });

    document.querySelector('[data-pagination-next]')?.addEventListener('click', (event) => {
        const label = document.querySelector('[data-pagination-label]');
        const button = event.currentTarget;
        const isNext = button.dataset.page !== '2';
        button.dataset.page = isNext ? '2' : '1';
        if (label) label.textContent = isNext ? 'Halaman 2 · data pratinjau' : 'Menampilkan 4 dari 86 pendaftaran';
        showToast(isNext ? 'Halaman data pratinjau dibuka.' : 'Kembali ke halaman pertama.');
    });

    document.querySelectorAll('[data-call-repeat]').forEach((button) => {
        button.addEventListener('click', () => {
            const number = document.querySelector('[data-current-queue]')?.textContent ?? '';
            showToast(`Nomor ${number} dipanggil ulang.`);
        });
    });

    document.querySelectorAll('[data-call-next]').forEach((button) => {
        button.addEventListener('click', () => {
            const list = document.querySelector('[data-queue-list]');
            const nextRow = list?.querySelector('tr:not([hidden])');
            const nextNumber = nextRow?.querySelector('.queue-code')?.textContent;
            const currentQueue = document.querySelector('[data-current-queue]');
            if (!nextNumber || !currentQueue) {
                showToast('Tidak ada antrean berikutnya.');
                return;
            }
            currentQueue.textContent = nextNumber;
            const nextPatient = nextRow.querySelector('.table-person strong')?.textContent;
            const nextRecord = nextRow.querySelector('.table-person small')?.textContent;
            const patientName = document.querySelector('.current-patient > div > strong');
            const patientRecord = document.querySelector('.current-patient > div > span');
            if (nextPatient && patientName) patientName.textContent = nextPatient;
            if (nextRecord && patientRecord) patientRecord.textContent = nextRecord;
            nextRow.remove();
            const waiting = document.querySelector('[data-waiting-count]');
            if (waiting) waiting.textContent = String(Math.max(0, Number(waiting.textContent) - 1));
            showToast(`Nomor ${nextNumber} dipanggil.`);
        });
    });

    document.querySelectorAll('[data-call-finish]').forEach((button) => {
        button.addEventListener('click', () => {
            const completed = document.querySelector('[data-completed-count]');
            if (completed) completed.textContent = String(Number(completed.textContent) + 1);
            const status = document.querySelector('.current-queue-heading .live-label');
            if (status) status.textContent = 'SELESAI';
            showToast('Kunjungan ditandai selesai pada pratinjau.');
        });
    });

    if (window.Chart) {
        window.Chart.defaults.font.family = 'Inter, Segoe UI, sans-serif';
        window.Chart.defaults.color = '#64748B';
        window.Chart.defaults.font.size = 9;

        const visitsCanvas = document.querySelector('[data-chart="visits"]');
        if (visitsCanvas) {
            new window.Chart(visitsCanvas, {
                type: 'line',
                data: {
                    labels: ['Kam', 'Jum', 'Sab', 'Min', 'Sen', 'Sel', 'Rab'],
                    datasets: [{
                        data: [44, 58, 39, 20, 72, 81, 86],
                        borderColor: '#2563EB',
                        backgroundColor: 'rgba(37, 99, 235, .09)',
                        fill: true,
                        tension: .35,
                        borderWidth: 2,
                        pointRadius: 2,
                        pointHoverRadius: 4,
                        pointBackgroundColor: '#FFFFFF',
                        pointBorderColor: '#2563EB',
                        pointBorderWidth: 2,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { displayColors: false } },
                    scales: {
                        x: { grid: { display: false }, border: { display: false }, ticks: { padding: 8 } },
                        y: { beginAtZero: true, suggestedMax: 100, border: { display: false }, grid: { color: '#EEF2F7' }, ticks: { stepSize: 25, padding: 8 } },
                    },
                },
            });
        }

        const poliCanvas = document.querySelector('[data-chart="poli"]');
        if (poliCanvas) {
            new window.Chart(poliCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['Umum', 'Gigi', 'Anak', 'KIA & lainnya'],
                    datasets: [{ data: [38, 19, 16, 13], backgroundColor: ['#2563EB', '#14B8A6', '#F59E0B', '#8B5CF6'], borderWidth: 0, hoverOffset: 3 }],
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '74%',
                    plugins: { legend: { display: false }, tooltip: { displayColors: false } },
                },
            });
        }
    }
});
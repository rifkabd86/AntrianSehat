/**
 * ============================================================
 * ANTRIAN SEHAT - Queue Display Monitor Script
 * 
 * Dedicated full-screen queue display for waiting room monitors.
 * Features:
 *  - Auto-refresh via Fetch API every 5 seconds
 *  - Prominent "now serving" number
 *  - Upcoming queue list
 *  - Audio notification on new call (Web Audio API)
 *  - Live clock display
 *  - Smooth transitions on data change
 * ============================================================
 */

(function () {
    'use strict';

    // ============================================================
    // CONFIGURATION
    // ============================================================

    var CONFIG = {
        /** Refresh interval in milliseconds */
        refreshInterval: 5000,

        /** API endpoint for queue display data */
        endpoint: 'antrean/displayData',

        /** Number of upcoming queue items to show */
        maxUpcoming: 8,

        /** Enable audio notifications */
        audioEnabled: true,

        /** Audio beep frequency in Hz */
        beepFrequency: 880,

        /** Audio beep duration in seconds */
        beepDuration: 0.15,

        /** Number of beeps per notification */
        beepCount: 3,

        /** Delay between beeps in milliseconds */
        beepDelay: 200
    };

    // ============================================================
    // STATE
    // ============================================================

    /** Tracks the currently displayed number to detect changes */
    var currentNumber = null;

    /** The refresh interval timer ID */
    var refreshTimer = null;

    /** The clock update interval timer ID */
    var clockTimer = null;

    /** AudioContext instance (created on first interaction or data change) */
    var audioContext = null;

    /** Whether audio has been unlocked by user interaction */
    var audioUnlocked = false;

    // ============================================================
    // DOM ELEMENTS
    // ============================================================

    var elClock            = document.getElementById('queueClock');
    var elDate             = document.getElementById('queueDate');
    var elNowServingNumber = document.getElementById('nowServingNumber');
    var elNowServingPoli   = document.getElementById('nowServingPoli');
    var elNowServingCounter = document.getElementById('nowServingCounter');
    var elUpcomingList     = document.getElementById('upcomingList');
    var elCounterGrid      = document.getElementById('counterGrid');
    var elConnectionStatus = document.getElementById('connectionStatus');

    // ============================================================
    // 1. CLOCK DISPLAY
    // ============================================================

    /**
     * Update the clock display with current time and date.
     */
    function updateClock() {
        var now = new Date();

        // Time (HH:MM:SS)
        if (elClock) {
            var h = String(now.getHours()).padStart(2, '0');
            var m = String(now.getMinutes()).padStart(2, '0');
            var s = String(now.getSeconds()).padStart(2, '0');
            elClock.textContent = h + ':' + m + ':' + s;
        }

        // Date (Indonesian format)
        if (elDate) {
            try {
                elDate.textContent = new Intl.DateTimeFormat('id-ID', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                }).format(now);
            } catch (e) {
                elDate.textContent = now.toLocaleDateString();
            }
        }
    }

    /**
     * Start the clock update loop (every second).
     */
    function startClock() {
        updateClock(); // Immediate first update
        clockTimer = setInterval(updateClock, 1000);
    }

    // ============================================================
    // 2. AUDIO NOTIFICATION (Web Audio API)
    // ============================================================

    /**
     * Initialize the AudioContext. Must be called after user interaction
     * in some browsers.
     */
    function initAudio() {
        if (audioContext) return;
        try {
            var AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (AudioCtx) {
                audioContext = new AudioCtx();
            }
        } catch (e) {
            console.warn('Web Audio API not supported:', e);
        }
    }

    /**
     * Play a single beep tone.
     *
     * @param {number} frequency Frequency in Hz
     * @param {number} duration  Duration in seconds
     * @param {number} delay     Start delay in seconds
     */
    function playBeep(frequency, duration, delay) {
        if (!audioContext || !CONFIG.audioEnabled) return;

        // Ensure context is running
        if (audioContext.state === 'suspended') {
            audioContext.resume();
        }

        var startTime = audioContext.currentTime + (delay || 0);

        // Create oscillator
        var oscillator = audioContext.createOscillator();
        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(frequency, startTime);

        // Create gain node for smooth attack/release
        var gainNode = audioContext.createGain();
        gainNode.gain.setValueAtTime(0, startTime);
        gainNode.gain.linearRampToValueAtTime(0.5, startTime + 0.01); // Attack
        gainNode.gain.linearRampToValueAtTime(0, startTime + duration); // Release

        // Connect nodes
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        // Schedule start and stop
        oscillator.start(startTime);
        oscillator.stop(startTime + duration);
    }

    /**
     * Play the notification sound sequence.
     * Plays a series of ascending beeps to grab attention.
     */
    function playNotificationSound() {
        if (!CONFIG.audioEnabled) return;

        initAudio();
        if (!audioContext) return;

        var frequencies = [660, 880, 1100]; // Ascending tones
        var delayBetween = CONFIG.beepDelay / 1000; // Convert ms to seconds

        for (var i = 0; i < CONFIG.beepCount; i++) {
            var freq = frequencies[i] || CONFIG.beepFrequency;
            playBeep(freq, CONFIG.beepDuration, i * (CONFIG.beepDuration + delayBetween));
        }
    }

    /**
     * Play a "ding-dong" style chime notification.
     * Two-tone chime commonly heard in hospital/public announcements.
     */
    function playChimeSound() {
        if (!CONFIG.audioEnabled) return;

        initAudio();
        if (!audioContext) return;

        // Ding (higher tone)
        playBeep(987.77, 0.3, 0);       // B5
        // Dong (lower tone)
        playBeep(659.25, 0.5, 0.35);    // E5
    }

    // ============================================================
    // 3. DATA FETCHING
    // ============================================================

    /**
     * Get the base URL from <base> tag or derive from current path.
     *
     * @returns {string}
     */
    function getBaseUrl() {
        var base = document.querySelector('base');
        if (base) return base.getAttribute('href').replace(/\/+$/, '');
        var path = window.location.pathname;
        var idx = path.indexOf('/public');
        if (idx !== -1) return path.substring(0, idx + 7);
        return '';
    }

    /**
     * Fetch queue display data from the server.
     *
     * @returns {Promise<object>}
     */
    function fetchQueueData() {
        var url = CONFIG.endpoint;
        if (!url.startsWith('http') && !url.startsWith('/')) {
            url = getBaseUrl() + '/' + url;
        }

        return fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        });
    }

    // ============================================================
    // 4. DISPLAY UPDATE
    // ============================================================

    /**
     * Update the queue display with fresh data.
     * Expected data format:
     * {
     *   current: {
     *     nomor_antrean: "A001",
     *     nama_poli: "Poli Umum",
     *     loket: "Loket 1"
     *   },
     *   upcoming: [
     *     { nomor_antrean: "A002", nama_poli: "Poli Umum" },
     *     ...
     *   ],
     *   counters: [
     *     { nama_loket: "Loket 1", nomor_antrean: "A001", nama_poli: "Poli Umum", status: "active" },
     *     ...
     *   ],
     *   stats: {
     *     total: 50,
     *     terlayani: 20,
     *     menunggu: 30
     *   }
     * }
     *
     * @param {object} data
     */
    function updateDisplay(data) {
        if (!data) return;

        // --- Update "Now Serving" section ---
        if (data.current) {
            var newNumber = data.current.nomor_antrean || '-';

            if (elNowServingNumber) {
                // Detect change for animation and sound
                if (currentNumber !== null && currentNumber !== newNumber && newNumber !== '-') {
                    // Number changed — trigger notification
                    playChimeSound();
                    animateNumberChange(elNowServingNumber, newNumber);
                } else {
                    elNowServingNumber.textContent = newNumber;
                }
                currentNumber = newNumber;
            }

            if (elNowServingPoli) {
                elNowServingPoli.textContent = data.current.nama_poli || '';
            }

            if (elNowServingCounter) {
                elNowServingCounter.innerHTML = data.current.loket
                    ? '<span>' + escapeHtml(data.current.loket) + '</span>'
                    : '';
            }
        } else {
            // No one being served
            if (elNowServingNumber) {
                if (currentNumber !== null && currentNumber !== '-') {
                    animateNumberChange(elNowServingNumber, '-');
                } else {
                    elNowServingNumber.textContent = '-';
                }
                currentNumber = '-';
            }
            if (elNowServingPoli) elNowServingPoli.textContent = '';
            if (elNowServingCounter) elNowServingCounter.innerHTML = '';
        }

        // --- Update counter grid (multiple service counters) ---
        if (data.counters && elCounterGrid) {
            updateCounterGrid(data.counters);
        }

        // --- Update upcoming list ---
        if (data.upcoming && elUpcomingList) {
            updateUpcomingList(data.upcoming);
        }

        // --- Update stats ---
        if (data.stats) {
            updateStats(data.stats);
        }

        // Update connection status
        setConnectionStatus('connected');
    }

    /**
     * Animate the number change with a scale/fade effect.
     *
     * @param {HTMLElement} element
     * @param {string}     newValue
     */
    function animateNumberChange(element, newValue) {
        // Scale down and fade out
        element.style.transition = 'transform 200ms ease, opacity 200ms ease';
        element.style.transform = 'scale(0.7)';
        element.style.opacity = '0';

        setTimeout(function () {
            element.textContent = newValue;
            // Scale up and fade in
            element.style.transform = 'scale(1.1)';
            element.style.opacity = '1';

            setTimeout(function () {
                element.style.transform = 'scale(1)';
            }, 200);
        }, 200);
    }

    /**
     * Update the multi-counter grid display.
     *
     * @param {Array} counters
     */
    function updateCounterGrid(counters) {
        if (!elCounterGrid) return;

        var html = '';

        counters.forEach(function (counter) {
            var isActive = counter.status === 'active' || counter.nomor_antrean;
            var activeClass = isActive ? ' active' : '';

            html +=
                '<div class="queue-counter-card' + activeClass + '">' +
                    '<div class="queue-counter-name">' + escapeHtml(counter.nama_loket || counter.loket || '') + '</div>' +
                    '<div class="queue-counter-number">' + escapeHtml(counter.nomor_antrean || '-') + '</div>' +
                    '<div class="queue-counter-status">' + escapeHtml(counter.nama_poli || '') + '</div>' +
                '</div>';
        });

        elCounterGrid.innerHTML = html;
    }

    /**
     * Update the upcoming queue list.
     *
     * @param {Array} upcoming
     */
    function updateUpcomingList(upcoming) {
        if (!elUpcomingList) return;

        if (!upcoming || upcoming.length === 0) {
            elUpcomingList.innerHTML =
                '<div class="queue-upcoming-title">Antrian Berikutnya</div>' +
                '<div class="queue-upcoming-item" style="grid-column: 1/-1; opacity:0.5">' +
                    '<div class="number">-</div>' +
                    '<div class="poli">Tidak ada antrian</div>' +
                '</div>';
            return;
        }

        var html = '<div class="queue-upcoming-title">Antrian Berikutnya</div>';
        var limit = Math.min(upcoming.length, CONFIG.maxUpcoming);

        for (var i = 0; i < limit; i++) {
            var item = upcoming[i];
            html +=
                '<div class="queue-upcoming-item">' +
                    '<div class="number">' + escapeHtml(item.nomor_antrean || '-') + '</div>' +
                    '<div class="poli">' + escapeHtml(item.nama_poli || '') + '</div>' +
                '</div>';
        }

        elUpcomingList.innerHTML = html;
    }

    /**
     * Update statistics display.
     *
     * @param {object} stats
     */
    function updateStats(stats) {
        if (!stats) return;

        var statMap = {
            'statTotal':     stats.total,
            'statTerlayani': stats.terlayani,
            'statMenunggu':  stats.menunggu
        };

        for (var id in statMap) {
            if (statMap.hasOwnProperty(id)) {
                var el = document.getElementById(id);
                if (el && typeof statMap[id] !== 'undefined') {
                    el.textContent = statMap[id];
                }
            }
        }
    }

    /**
     * Set the connection status indicator.
     *
     * @param {string} status 'connected' | 'disconnected' | 'connecting'
     */
    function setConnectionStatus(status) {
        if (!elConnectionStatus) return;

        var statusMap = {
            connected:    { text: 'Terhubung', color: '#10B981', icon: '●' },
            disconnected: { text: 'Terputus', color: '#EF4444', icon: '●' },
            connecting:   { text: 'Menghubungkan...', color: '#F59E0B', icon: '◌' }
        };

        var info = statusMap[status] || statusMap.connecting;
        elConnectionStatus.innerHTML =
            '<span style="color:' + info.color + ';margin-right:6px">' + info.icon + '</span>' +
            '<span style="color:rgba(255,255,255,0.6);font-size:12px">' + info.text + '</span>';
    }

    // ============================================================
    // 5. REFRESH LOOP
    // ============================================================

    /** Consecutive error count for backoff */
    var errorCount = 0;

    /**
     * Fetch data and update the display.
     */
    function refresh() {
        setConnectionStatus('connecting');

        fetchQueueData()
            .then(function (data) {
                errorCount = 0;
                updateDisplay(data);
            })
            .catch(function (err) {
                errorCount++;
                console.warn('Queue display refresh error (' + errorCount + '):', err.message);
                setConnectionStatus('disconnected');

                // After 5 consecutive errors, show a visual warning
                if (errorCount >= 5 && elNowServingNumber) {
                    elNowServingPoli.textContent = 'Koneksi terputus, mencoba ulang...';
                }
            });
    }

    /**
     * Start the auto-refresh loop.
     */
    function startRefresh() {
        refresh(); // Immediate first fetch
        refreshTimer = setInterval(refresh, CONFIG.refreshInterval);
    }

    /**
     * Stop the auto-refresh loop.
     */
    function stopRefresh() {
        if (refreshTimer) {
            clearInterval(refreshTimer);
            refreshTimer = null;
        }
    }

    // ============================================================
    // 6. FULLSCREEN SUPPORT
    // ============================================================

    /**
     * Toggle fullscreen mode.
     */
    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(function (err) {
                console.warn('Fullscreen not supported:', err);
            });
        } else {
            document.exitFullscreen();
        }
    }

    // Fullscreen button handler
    var fullscreenBtn = document.getElementById('fullscreenBtn');
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', toggleFullscreen);
    }

    // Double-click to toggle fullscreen
    document.addEventListener('dblclick', function () {
        toggleFullscreen();
    });

    // ============================================================
    // 7. AUDIO UNLOCK ON USER INTERACTION
    // ============================================================

    /**
     * Unlock audio on first user interaction.
     * Modern browsers require a user gesture to start AudioContext.
     */
    function unlockAudio() {
        if (audioUnlocked) return;
        initAudio();
        if (audioContext && audioContext.state === 'suspended') {
            audioContext.resume();
        }
        audioUnlocked = true;
        // Remove listeners after unlock
        document.removeEventListener('click', unlockAudio);
        document.removeEventListener('touchstart', unlockAudio);
        document.removeEventListener('keydown', unlockAudio);
    }

    document.addEventListener('click', unlockAudio);
    document.addEventListener('touchstart', unlockAudio);
    document.addEventListener('keydown', unlockAudio);

    // ============================================================
    // 8. VISIBILITY API - Pause when tab is hidden
    // ============================================================

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            stopRefresh();
        } else {
            startRefresh();
        }
    });

    // ============================================================
    // 9. UTILITY
    // ============================================================

    /**
     * Escape HTML to prevent XSS.
     *
     * @param {string} str
     * @returns {string}
     */
    function escapeHtml(str) {
        if (typeof str !== 'string') return '';
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return str.replace(/[&<>"']/g, function (m) { return map[m]; });
    }

    // ============================================================
    // 10. EXPOSE GLOBAL API
    // ============================================================

    /**
     * Global function that app.js can call when queue data arrives
     * from a different source (e.g., WebSocket in the future).
     */
    window.updateQueueDisplay = updateDisplay;

    /**
     * Allow external configuration override.
     *
     * @param {object} overrides
     */
    window.configureQueueDisplay = function (overrides) {
        for (var key in overrides) {
            if (overrides.hasOwnProperty(key) && CONFIG.hasOwnProperty(key)) {
                CONFIG[key] = overrides[key];
            }
        }
        // Restart refresh with new interval
        stopRefresh();
        startRefresh();
    };

    /**
     * Manual test functions for debugging.
     */
    window.queueDisplayTest = {
        playChime: playChimeSound,
        playBeeps: playNotificationSound,
        simulateChange: function () {
            var testData = {
                current: {
                    nomor_antrean: 'A' + String(Math.floor(Math.random() * 100)).padStart(3, '0'),
                    nama_poli: 'Poli Umum',
                    loket: 'Loket 1'
                },
                upcoming: [
                    { nomor_antrean: 'A051', nama_poli: 'Poli Umum' },
                    { nomor_antrean: 'B012', nama_poli: 'Poli Gigi' },
                    { nomor_antrean: 'A052', nama_poli: 'Poli Umum' },
                    { nomor_antrean: 'C003', nama_poli: 'Poli Anak' }
                ],
                stats: { total: 50, terlayani: 20, menunggu: 30 }
            };
            updateDisplay(testData);
        }
    };

    // ============================================================
    // 11. INITIALIZATION
    // ============================================================

    /**
     * Initialize the queue display.
     */
    function init() {
        // Verify we're on the queue display page
        var isQueuePage = document.querySelector('.queue-display-page') ||
                          document.getElementById('queueDisplayContainer') ||
                          elNowServingNumber;

        if (!isQueuePage) {
            // Not on the queue display page, don't initialize
            return;
        }

        console.log('%c📟 Queue Display Monitor Started', 'color:#60A5FA;font-size:14px;font-weight:bold;');
        console.log('Refresh interval:', CONFIG.refreshInterval + 'ms');
        console.log('Double-click or press F11 for fullscreen.');

        // Start clock
        startClock();

        // Start data refresh
        startRefresh();

        // Set initial connection status
        setConnectionStatus('connecting');
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();

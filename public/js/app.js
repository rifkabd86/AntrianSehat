/**
 * ============================================================
 * ANTRIAN SEHAT - Healthcare Queue Management System
 * Main Application JavaScript
 * ============================================================
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // ============================================================
    // 1. CSRF TOKEN HELPER
    // ============================================================

    /**
     * Retrieve the CSRF token from a meta tag or hidden input.
     * The token should be stored in:
     *   <meta name="csrf-token" content="...">
     * or a hidden input named csrf_token in a form.
     *
     * @returns {string} CSRF token value
     */
    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) {
            var token = meta.getAttribute('content');
            if (token && token.trim() !== '') {
                return token.trim();
            }
        }
        var input = document.querySelector('input[name="csrf_token"]');
        if (input && input.value && input.value.trim() !== '') {
            return input.value.trim();
        }
        return '';
    }

    // Expose globally
    window.getCsrfToken = getCsrfToken;

    // ============================================================
    // 2. SIDEBAR TOGGLE (Mobile)
    // ============================================================

    var sidebar      = document.querySelector('.sidebar');
    var sidebarToggle = document.querySelector('.sidebar-toggle');
    var sidebarToggleDesktop = document.querySelector('.sidebar-toggle-desktop');
    var sidebarOverlay = document.querySelector('.sidebar-overlay');
    var appWrapper = document.querySelector('.app-wrapper');

    /**
     * Open the sidebar on mobile screens.
     */
    function openSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('show');
        if (sidebarOverlay) {
            sidebarOverlay.classList.add('show');
        }
        document.body.style.overflow = 'hidden';
    }

    /**
     * Close the sidebar on mobile screens.
     */
    function closeSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('show');
        if (sidebarOverlay) {
            sidebarOverlay.classList.remove('show');
        }
        document.body.style.overflow = '';
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function (e) {
            e.preventDefault();
            if (sidebar.classList.contains('show')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    if (sidebarToggleDesktop && appWrapper) {
        sidebarToggleDesktop.addEventListener('click', function () {
            var isCollapsed = appWrapper.classList.toggle('sidebar-collapsed');
            sidebarToggleDesktop.setAttribute('aria-expanded', String(!isCollapsed));
            sidebarToggleDesktop.setAttribute('aria-label', isCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar');
        });
    }

    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.getElementById(button.getAttribute('data-password-toggle'));
            if (!input) return;

            var showPassword = input.type === 'password';
            input.type = showPassword ? 'text' : 'password';
            button.setAttribute('aria-label', showPassword ? 'Sembunyikan password' : 'Tampilkan password');
            var icon = button.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye', !showPassword);
                icon.classList.toggle('fa-eye-slash', showPassword);
            }
        });
    });

    // Close sidebar on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeSidebar();
            // Also close any open dropdowns
            closeAllDropdowns();
        }
    });

    // ============================================================
    // 3. ACTIVE SIDEBAR MENU HIGHLIGHTING
    // ============================================================

    /**
     * Highlights the sidebar link matching the current page URL.
     */
    function highlightActiveMenu() {
        var navLinks = document.querySelectorAll('.sidebar .nav-link');
        var currentPath = decodeURIComponent(window.location.pathname).replace(/\/+$/, '');

        navLinks.forEach(function (link) {
            link.classList.remove('active');
            var href = link.getAttribute('href');
            if (!href || href.charAt(0) === '#') return;

            var linkPath = decodeURIComponent(new URL(href, window.location.origin).pathname).replace(/\/+$/, '');

            if (currentPath === linkPath || (linkPath.length > 1 && currentPath.startsWith(linkPath + '/'))) {
                link.classList.add('active');
            }
        });
    }

    highlightActiveMenu();

    // ============================================================
    // 4. TOAST NOTIFICATION SYSTEM
    // ============================================================

    // Ensure toast container exists
    var toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }

    /**
     * Icon map for toast types.
     */
    var toastIcons = {
        success: 'bi-check-circle-fill',
        danger:  'bi-x-circle-fill',
        error:   'bi-x-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        info:    'bi-info-circle-fill'
    };

    /**
     * Title map for toast types.
     */
    var toastTitles = {
        success: 'Berhasil',
        danger:  'Gagal',
        error:   'Gagal',
        warning: 'Peringatan',
        info:    'Informasi'
    };

    /**
     * Show a toast notification.
     *
     * @param {string} message  - Message text
     * @param {string} type     - 'success' | 'danger' | 'error' | 'warning' | 'info'
     * @param {number} duration - Auto-dismiss time in ms (default 3000). Pass 0 to disable.
     */
    function showToast(message, type, duration) {
        type = type || 'success';
        duration = typeof duration === 'number' ? duration : 3000;

        var toast = document.createElement('div');
        toast.className = 'toast toast-' + type;

        var iconClass = toastIcons[type] || toastIcons.info;
        var title = toastTitles[type] || 'Notifikasi';

        toast.innerHTML =
            '<i class="bi ' + iconClass + ' toast-icon"></i>' +
            '<div class="toast-content">' +
                '<div class="toast-title">' + escapeHtml(title) + '</div>' +
                '<div class="toast-message">' + escapeHtml(message) + '</div>' +
            '</div>' +
            '<button type="button" class="toast-close" aria-label="Tutup">&times;</button>' +
            (duration > 0 ? '<div class="toast-progress" style="animation-duration:' + duration + 'ms"></div>' : '');

        // Close button handler
        toast.querySelector('.toast-close').addEventListener('click', function () {
            dismissToast(toast);
        });

        toastContainer.appendChild(toast);

        // Auto-dismiss
        if (duration > 0) {
            setTimeout(function () {
                dismissToast(toast);
            }, duration);
        }

        return toast;
    }

    /**
     * Dismiss a toast with slide-out animation.
     *
     * @param {HTMLElement} toast
     */
    function dismissToast(toast) {
        if (!toast || toast.classList.contains('hide')) return;
        toast.classList.add('hide');
        toast.addEventListener('animationend', function () {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        });
    }

    // Expose globally
    window.showToast = showToast;

    // ============================================================
    // 5. AJAX / FETCH HELPER
    // ============================================================

    /**
     * Base URL derived from the page's <base> tag or root.
     */
    function getBaseUrl() {
        var base = document.querySelector('base');
        if (base) return base.getAttribute('href').replace(/\/+$/, '');
        // Fallback: detect from current URL
        var path = window.location.pathname;
        var idx  = path.indexOf('/public');
        if (idx !== -1) return path.substring(0, idx + 7);
        return '';
    }

    /**
     * Perform an AJAX request using the Fetch API.
     *
     * @param {string} url      - Relative or absolute URL
     * @param {object} options  - Fetch options (method, body, headers, etc.)
     * @returns {Promise<object>} Parsed JSON response
     *
     * @example
     *   fetchData('antrean/displayData')
     *     .then(function(data) { console.log(data); })
     *     .catch(function(err) { showToast(err.message, 'error'); });
     */
    function fetchData(url, options) {
        options = options || {};

        // If url is relative, prepend base
        if (url && !url.startsWith('http') && !url.startsWith('/')) {
            url = getBaseUrl() + '/' + url;
        }

        // Default headers
        var headers = options.headers || {};
        headers['X-Requested-With'] = 'XMLHttpRequest';

        // Attach CSRF token for non-GET requests
        var method = (options.method || 'GET').toUpperCase();
        if (method !== 'GET') {
            var token = getCsrfToken();
            if (token) {
                headers['X-CSRF-Token'] = token;
            }
            // If body is FormData, don't set Content-Type (browser handles boundary)
            if (!(options.body instanceof FormData) && !headers['Content-Type']) {
                headers['Content-Type'] = 'application/json';
            }
        }

        options.headers = headers;

        return fetch(url, options)
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('HTTP Error ' + response.status + ': ' + response.statusText);
                }
                var ct = response.headers.get('Content-Type') || '';
                if (ct.includes('application/json')) {
                    return response.json();
                }
                return response.text();
            });
    }

    // Expose globally
    window.fetchData = fetchData;

    // ============================================================
    // 6. FORM VALIDATION HELPER
    // ============================================================

    /**
     * Validate a form before submission.
     * Checks for required fields, email, min-length, and pattern attributes.
     * Adds is-invalid / is-valid classes and shows invalid-feedback elements.
     *
     * @param {HTMLFormElement} form
     * @returns {boolean} True if valid
     */
    function validateForm(form) {
        var isValid = true;
        var fields = form.querySelectorAll('[required], [data-validate]');

        fields.forEach(function (field) {
            // Remove previous states
            field.classList.remove('is-invalid', 'is-valid');
            var feedbackEl = field.parentElement.querySelector('.invalid-feedback');

            var value = field.value.trim();
            var type  = field.getAttribute('type') || field.tagName.toLowerCase();
            var label = field.getAttribute('data-label') || field.getAttribute('placeholder') || 'Kolom ini';

            // Required check
            if (field.hasAttribute('required') && value === '') {
                setInvalid(field, feedbackEl, label + ' wajib diisi.');
                isValid = false;
                return;
            }

            // If empty and not required, skip further checks
            if (value === '') {
                field.classList.add('is-valid');
                return;
            }

            // Email format
            if (type === 'email') {
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    setInvalid(field, feedbackEl, 'Format email tidak valid.');
                    isValid = false;
                    return;
                }
            }

            // Minimum length
            var minLength = field.getAttribute('minlength');
            if (minLength && value.length < parseInt(minLength, 10)) {
                setInvalid(field, feedbackEl, 'Minimal ' + minLength + ' karakter.');
                isValid = false;
                return;
            }

            // Pattern
            var pattern = field.getAttribute('pattern');
            if (pattern) {
                var regex = new RegExp(pattern);
                if (!regex.test(value)) {
                    var patternMsg = field.getAttribute('data-pattern-message') || 'Format tidak sesuai.';
                    setInvalid(field, feedbackEl, patternMsg);
                    isValid = false;
                    return;
                }
            }

            // Password confirm
            if (field.getAttribute('data-match')) {
                var matchField = form.querySelector(field.getAttribute('data-match'));
                if (matchField && value !== matchField.value) {
                    setInvalid(field, feedbackEl, 'Password tidak cocok.');
                    isValid = false;
                    return;
                }
            }

            // All checks passed
            field.classList.add('is-valid');
        });

        // Scroll to first invalid field
        if (!isValid) {
            var firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        return isValid;
    }

    /**
     * Mark a field as invalid and set error message.
     */
    function setInvalid(field, feedbackEl, message) {
        field.classList.add('is-invalid');
        if (feedbackEl) {
            feedbackEl.textContent = message;
        }
    }

    // Expose globally
    window.validateForm = validateForm;

    // Attach real-time validation on blur
    document.addEventListener('blur', function (e) {
        var field = e.target;
        if (field.matches && field.matches('.form-control[required], .form-select[required]')) {
            if (field.value.trim() !== '') {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            }
        }
    }, true);

    // Intercept form submissions for client-side validation
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (form.hasAttribute('data-validate')) {
            if (!validateForm(form)) {
                e.preventDefault();
                e.stopImmediatePropagation();
            }
        }
    });

    // ============================================================
    // 7. CONFIRM DELETE MODAL
    // ============================================================

    /**
     * Attach click handlers to elements with [data-confirm-delete].
     * Expected attributes on the trigger element:
     *   data-confirm-delete="URL or form ID"
     *   data-confirm-message="Custom message" (optional)
     *   data-confirm-title="Custom title" (optional)
     */
    function initConfirmDelete() {
        document.addEventListener('click', function (e) {
            var trigger = e.target.closest('[data-confirm-delete]');
            if (!trigger) return;

            e.preventDefault();
            e.stopPropagation();

            var target  = trigger.getAttribute('data-confirm-delete');
            var message = trigger.getAttribute('data-confirm-message') || 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.';
            var title   = trigger.getAttribute('data-confirm-title') || 'Konfirmasi Hapus';

            showConfirmModal(title, message, function () {
                // If target starts with # it's a form ID to submit
                if (target.startsWith('#')) {
                    var form = document.querySelector(target);
                    if (form) form.submit();
                } else {
                    // It's a URL, navigate via POST form
                    var deleteForm = document.createElement('form');
                    deleteForm.method = 'POST';
                    deleteForm.action = target;
                    deleteForm.style.display = 'none';

                    // CSRF token
                    var csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = 'csrf_token';
                    csrfInput.value = getCsrfToken();
                    deleteForm.appendChild(csrfInput);

                    // Method override
                    var methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    deleteForm.appendChild(methodInput);

                    document.body.appendChild(deleteForm);
                    deleteForm.submit();
                }
            });
        });
    }

    /**
     * Show a confirmation modal dialog.
     *
     * @param {string}   title     Modal title
     * @param {string}   message   Modal body message
     * @param {function} onConfirm Callback when confirmed
     * @param {string}   type      'danger' | 'warning' | 'success' (default 'danger')
     */
    function showConfirmModal(title, message, onConfirm, type) {
        type = type || 'danger';

        // Remove existing confirm modal
        var existing = document.getElementById('confirmModal');
        if (existing) existing.remove();

        var iconMap = {
            danger:  'bi-exclamation-triangle-fill',
            warning: 'bi-exclamation-triangle-fill',
            success: 'bi-check-circle-fill'
        };

        var btnClass = type === 'success' ? 'btn-success' : (type === 'warning' ? 'btn-warning' : 'btn-danger');
        var btnText  = type === 'success' ? 'Konfirmasi' : 'Hapus';

        var overlay = document.createElement('div');
        overlay.id = 'confirmModal';
        overlay.className = 'modal-overlay show';
        overlay.innerHTML =
            '<div class="modal-dialog modal-sm">' +
                '<div class="modal-body text-center p-4">' +
                    '<div class="modal-confirm-icon ' + type + '">' +
                        '<i class="bi ' + (iconMap[type] || iconMap.danger) + '"></i>' +
                    '</div>' +
                    '<h5 class="mb-2">' + escapeHtml(title) + '</h5>' +
                    '<p class="text-muted mb-0" style="font-size:13.5px">' + escapeHtml(message) + '</p>' +
                '</div>' +
                '<div class="modal-footer">' +
                    '<button type="button" class="btn btn-secondary" data-modal-dismiss>Batal</button>' +
                    '<button type="button" class="btn ' + btnClass + '" id="confirmModalBtn">' + btnText + '</button>' +
                '</div>' +
            '</div>';

        document.body.appendChild(overlay);

        // Dismiss handlers
        overlay.querySelector('[data-modal-dismiss]').addEventListener('click', function () {
            overlay.remove();
        });

        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                overlay.remove();
            }
        });

        // Confirm handler
        document.getElementById('confirmModalBtn').addEventListener('click', function () {
            overlay.remove();
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
        });
    }

    // Expose globally
    window.showConfirmModal = showConfirmModal;

    initConfirmDelete();

    // ============================================================
    // 8. AUTO-HIDE ALERTS
    // ============================================================

    /**
     * Automatically dismiss .alert-custom elements after a delay.
     */
    function initAutoHideAlerts() {
        var alerts = document.querySelectorAll('.alert-custom[data-auto-dismiss]');
        alerts.forEach(function (alert) {
            var delay = parseInt(alert.getAttribute('data-auto-dismiss'), 10) || 5000;
            setTimeout(function () {
                alert.style.transition = 'opacity 300ms ease, max-height 300ms ease';
                alert.style.opacity = '0';
                alert.style.maxHeight = '0';
                alert.style.overflow = 'hidden';
                alert.style.padding = '0';
                alert.style.margin = '0';
                setTimeout(function () {
                    if (alert.parentNode) alert.parentNode.removeChild(alert);
                }, 350);
            }, delay);
        });
    }

    initAutoHideAlerts();

    // Alert close buttons
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.alert-close');
        if (!btn) return;
        var alert = btn.closest('.alert-custom');
        if (alert) {
            alert.style.transition = 'opacity 200ms ease';
            alert.style.opacity = '0';
            setTimeout(function () {
                if (alert.parentNode) alert.parentNode.removeChild(alert);
            }, 200);
        }
    });

    // ============================================================
    // 9. SEARCH / FILTER FOR TABLES
    // ============================================================

    /**
     * Initialize table search functionality.
     * The search input should have [data-table-search="tableId"]
     * and the table should have a matching id.
     */
    function initTableSearch() {
        var searchInputs = document.querySelectorAll('[data-table-search]');

        searchInputs.forEach(function (input) {
            var tableId = input.getAttribute('data-table-search');
            var table   = document.getElementById(tableId);
            if (!table) return;

            var debounceTimer;

            input.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    var query = input.value.toLowerCase().trim();
                    var rows  = table.querySelectorAll('tbody tr');

                    rows.forEach(function (row) {
                        if (row.querySelector('.table-empty')) return; // Skip empty state row
                        var text = row.textContent.toLowerCase();
                        row.style.display = text.includes(query) ? '' : 'none';
                    });

                    // Update visible count if present
                    var counter = document.querySelector('[data-table-count="' + tableId + '"]');
                    if (counter) {
                        var visibleRows = table.querySelectorAll('tbody tr:not([style*="display: none"]):not(.table-empty-row)');
                        counter.textContent = visibleRows.length;
                    }
                }, 250);
            });
        });
    }

    initTableSearch();

    // ============================================================
    // 10. PAGINATION HANDLER
    // ============================================================

    /**
     * Handle pagination link clicks.
     * Links with [data-page] inside .pagination-custom will trigger navigation.
     */
    document.addEventListener('click', function (e) {
        var pageLink = e.target.closest('.pagination-custom .page-link[data-page]');
        if (!pageLink) return;

        e.preventDefault();
        var page = pageLink.getAttribute('data-page');
        var url  = pageLink.getAttribute('href') || pageLink.getAttribute('data-url');

        if (url) {
            window.location.href = url;
        } else {
            // Append page parameter to current URL
            var currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('page', page);
            window.location.href = currentUrl.toString();
        }
    });

    // ============================================================
    // 11. QUEUE DISPLAY AUTO-REFRESH
    // ============================================================

    var queueDisplayContainer = document.getElementById('queueDisplayContainer');
    var queueRefreshInterval  = null;

    /**
     * Start auto-refreshing queue display data.
     * Used on the dashboard or queue monitor page.
     *
     * @param {number} intervalMs Refresh interval in milliseconds (default 10000)
     */
    function startQueueAutoRefresh(intervalMs) {
        if (!queueDisplayContainer) return;

        intervalMs = intervalMs || 10000;

        function refresh() {
            fetchData('antrean/displayData')
                .then(function (data) {
                    if (data && typeof window.updateQueueDisplay === 'function') {
                        window.updateQueueDisplay(data);
                    }
                })
                .catch(function (err) {
                    console.warn('Queue refresh failed:', err.message);
                });
        }

        // Initial fetch
        refresh();

        // Set interval
        if (queueRefreshInterval) clearInterval(queueRefreshInterval);
        queueRefreshInterval = setInterval(refresh, intervalMs);
    }

    // Expose globally
    window.startQueueAutoRefresh = startQueueAutoRefresh;

    // Auto-start if the container is present
    if (queueDisplayContainer) {
        startQueueAutoRefresh(10000);
    }

    // ============================================================
    // 12. NOTIFICATION BELL
    // ============================================================

    var notifBell     = document.querySelector('.notification-bell');
    var notifDropdown = document.querySelector('.notification-dropdown');

    if (notifBell && notifDropdown) {
        notifBell.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = notifDropdown.classList.contains('show');
            closeAllDropdowns();

            if (!isOpen) {
                notifDropdown.classList.add('show');
                // Fetch latest notifications
                fetchData('notifikasi/latest')
                    .then(function (data) {
                        if (data && data.html) {
                            var body = notifDropdown.querySelector('.notification-dropdown-body');
                            if (body) body.innerHTML = data.html;
                        }
                        if (data && typeof data.count !== 'undefined') {
                            var badge = notifBell.querySelector('.notification-badge');
                            if (badge) {
                                badge.textContent = data.count;
                                badge.style.display = data.count > 0 ? '' : 'none';
                            }
                        }
                    })
                    .catch(function () {
                        // Silently fail
                    });
            }
        });
    }

    // ============================================================
    // 13. USER DROPDOWN
    // ============================================================

    var userToggle   = document.querySelector('.user-dropdown-toggle');
    var userDropdown = document.querySelector('.user-dropdown-menu');

    if (userToggle && userDropdown) {
        userToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = userDropdown.classList.contains('show');
            closeAllDropdowns();

            if (!isOpen) {
                userDropdown.classList.add('show');
            }
        });
    }

    /**
     * Close all open dropdown menus.
     */
    function closeAllDropdowns() {
        document.querySelectorAll('.notification-dropdown.show, .user-dropdown-menu.show, .dropdown-menu.show')
            .forEach(function (el) {
                el.classList.remove('show');
            });
    }

    // Close dropdowns on outside click
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.notification-bell, .notification-dropdown, .user-dropdown, .dropdown')) {
            closeAllDropdowns();
        }
    });

    // ============================================================
    // 14. DATE / TIME HELPERS
    // ============================================================

    /**
     * Format a Date object or date string to Indonesian locale.
     *
     * @param {Date|string} date
     * @param {object}      options - Intl.DateTimeFormat options
     * @returns {string}
     */
    function formatDate(date, options) {
        if (typeof date === 'string') {
            date = new Date(date);
        }
        if (!(date instanceof Date) || isNaN(date)) return '-';

        var defaults = {
            day:   'numeric',
            month: 'long',
            year:  'numeric'
        };
        options = Object.assign({}, defaults, options || {});

        try {
            return new Intl.DateTimeFormat('id-ID', options).format(date);
        } catch (e) {
            return date.toLocaleDateString();
        }
    }

    /**
     * Format a Date to time string (HH:MM).
     *
     * @param {Date|string} date
     * @returns {string}
     */
    function formatTime(date) {
        if (typeof date === 'string') {
            date = new Date(date);
        }
        if (!(date instanceof Date) || isNaN(date)) return '-';

        var h = String(date.getHours()).padStart(2, '0');
        var m = String(date.getMinutes()).padStart(2, '0');
        return h + ':' + m;
    }

    /**
     * Format date as relative time (e.g., "5 menit yang lalu").
     *
     * @param {Date|string} date
     * @returns {string}
     */
    function formatRelativeTime(date) {
        if (typeof date === 'string') {
            date = new Date(date);
        }
        if (!(date instanceof Date) || isNaN(date)) return '-';

        var now  = new Date();
        var diff = Math.floor((now - date) / 1000); // seconds

        if (diff < 60) return 'Baru saja';
        if (diff < 3600) return Math.floor(diff / 60) + ' menit yang lalu';
        if (diff < 86400) return Math.floor(diff / 3600) + ' jam yang lalu';
        if (diff < 604800) return Math.floor(diff / 86400) + ' hari yang lalu';

        return formatDate(date);
    }

    // Expose globally
    window.formatDate = formatDate;
    window.formatTime = formatTime;
    window.formatRelativeTime = formatRelativeTime;

    // Update header date display
    var headerDateEl = document.querySelector('.header-date .date-text');
    if (headerDateEl) {
        headerDateEl.textContent = formatDate(new Date(), {
            weekday: 'long',
            day:     'numeric',
            month:   'long',
            year:    'numeric'
        });
    }

    // ============================================================
    // 15. COUNTER ANIMATION (Dashboard Numbers)
    // ============================================================

    /**
     * Animate a number counting up from 0 to its target value.
     *
     * @param {HTMLElement} element  Element with the target number as text
     * @param {number}      duration Animation duration in ms (default 1000)
     */
    function animateCounter(element, duration) {
        var target = parseInt(element.textContent, 10);
        if (isNaN(target) || target === 0) return;

        duration = duration || 1000;
        var start  = 0;
        var startTime = null;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);

            // Ease-out cubic
            var eased = 1 - Math.pow(1 - progress, 3);
            var current = Math.floor(eased * target);

            element.textContent = current.toLocaleString('id-ID');

            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                element.textContent = target.toLocaleString('id-ID');
            }
        }

        element.textContent = '0';
        requestAnimationFrame(step);
    }

    /**
     * Initialize counter animations for all stat card numbers.
     * Uses IntersectionObserver if available to trigger when visible.
     */
    function initCounterAnimations() {
        var counters = document.querySelectorAll('.stat-card-number[data-animate]');
        if (counters.length === 0) return;

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.3 });

            counters.forEach(function (counter) {
                observer.observe(counter);
            });
        } else {
            // Fallback: animate immediately
            counters.forEach(function (counter) {
                animateCounter(counter);
            });
        }
    }

    initCounterAnimations();

    // ============================================================
    // 16. CHARTS INITIALIZATION PLACEHOLDER
    // ============================================================

    /**
     * Initialize a Chart.js chart.
     * Requires Chart.js to be loaded before calling this.
     *
     * @param {string} canvasId - The canvas element ID
     * @param {string} type     - Chart type ('bar', 'line', 'pie', 'doughnut', etc.)
     * @param {object} data     - Chart.js data config
     * @param {object} options  - Chart.js options config
     * @returns {object|null}   Chart instance or null
     */
    function initChart(canvasId, type, data, options) {
        var canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.warn('Chart canvas not found:', canvasId);
            return null;
        }

        if (typeof Chart === 'undefined') {
            console.warn('Chart.js library not loaded.');
            return null;
        }

        var ctx = canvas.getContext('2d');

        // Default styling that matches our theme
        var defaultOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        font: {
                            family: "'Inter', sans-serif",
                            size: 12
                        },
                        color: '#64748B',
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: '#0F172A',
                    titleFont: { family: "'Inter', sans-serif", size: 13, weight: '600' },
                    bodyFont: { family: "'Inter', sans-serif", size: 12 },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: true,
                    boxPadding: 4
                }
            },
            scales: {
                x: {
                    grid: { color: '#F1F5F9', drawBorder: false },
                    ticks: { font: { family: "'Inter', sans-serif", size: 11 }, color: '#64748B' }
                },
                y: {
                    grid: { color: '#F1F5F9', drawBorder: false },
                    ticks: { font: { family: "'Inter', sans-serif", size: 11 }, color: '#64748B' },
                    beginAtZero: true
                }
            }
        };

        // Merge options
        var merged = deepMerge(defaultOptions, options || {});

        // For pie/doughnut, remove scales
        if (type === 'pie' || type === 'doughnut') {
            delete merged.scales;
        }

        return new Chart(ctx, {
            type: type,
            data: data,
            options: merged
        });
    }

    // Expose globally
    window.initChart = initChart;

    // ============================================================
    // 17. PRINT REPORT
    // ============================================================

    /**
     * Print a specific section of the page.
     *
     * @param {string} elementId - ID of the element to print (optional, prints whole page if omitted)
     * @param {string} title     - Document title for print (optional)
     */
    function printReport(elementId, title) {
        if (!elementId) {
            window.print();
            return;
        }

        var content = document.getElementById(elementId);
        if (!content) {
            console.warn('Print element not found:', elementId);
            window.print();
            return;
        }

        // Open print window with the content
        var printWin = window.open('', '_blank', 'width=900,height=700');
        if (!printWin) {
            // Popup blocked, fallback to regular print
            window.print();
            return;
        }

        var styles = '';
        // Copy all stylesheets
        document.querySelectorAll('link[rel="stylesheet"], style').forEach(function (el) {
            styles += el.outerHTML;
        });

        printWin.document.write(
            '<!DOCTYPE html><html><head>' +
            '<meta charset="utf-8">' +
            '<title>' + (title || 'Laporan - Antrian Sehat') + '</title>' +
            styles +
            '<style>body{background:#fff;padding:24px;margin:0;} .no-print{display:none!important;}</style>' +
            '</head><body>' +
            content.innerHTML +
            '</body></html>'
        );
        printWin.document.close();

        // Wait for styles to load then print
        printWin.onload = function () {
            printWin.focus();
            printWin.print();
            printWin.close();
        };
    }

    // Expose globally
    window.printReport = printReport;

    // Print button handler
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-print]');
        if (!btn) return;
        e.preventDefault();
        var targetId = btn.getAttribute('data-print');
        var title    = btn.getAttribute('data-print-title');
        printReport(targetId, title);
    });

    // ============================================================
    // 18. UTILITY FUNCTIONS
    // ============================================================

    /**
     * Escape HTML special characters to prevent XSS in JS-generated HTML.
     *
     * @param {string} str
     * @returns {string}
     */
    function escapeHtml(str) {
        if (typeof str !== 'string') return '';
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return str.replace(/[&<>"']/g, function (m) { return map[m]; });
    }

    window.escapeHtml = escapeHtml;

    /**
     * Deep merge two objects (simple implementation).
     *
     * @param {object} target
     * @param {object} source
     * @returns {object}
     */
    function deepMerge(target, source) {
        var output = Object.assign({}, target);
        for (var key in source) {
            if (source.hasOwnProperty(key)) {
                if (
                    source[key] &&
                    typeof source[key] === 'object' &&
                    !Array.isArray(source[key]) &&
                    target[key] &&
                    typeof target[key] === 'object' &&
                    !Array.isArray(target[key])
                ) {
                    output[key] = deepMerge(target[key], source[key]);
                } else {
                    output[key] = source[key];
                }
            }
        }
        return output;
    }

    /**
     * Debounce a function call.
     *
     * @param {function} func  The function to debounce
     * @param {number}   wait  Wait time in ms
     * @returns {function}
     */
    function debounce(func, wait) {
        var timer;
        return function () {
            var context = this;
            var args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                func.apply(context, args);
            }, wait);
        };
    }

    window.debounce = debounce;

    /**
     * Format a number with Indonesian thousand separators.
     *
     * @param {number} num
     * @returns {string}
     */
    function formatNumber(num) {
        if (typeof num !== 'number' || isNaN(num)) return '0';
        return num.toLocaleString('id-ID');
    }

    window.formatNumber = formatNumber;

    // ============================================================
    // 19. GENERIC MODAL OPEN/CLOSE
    // ============================================================

    document.addEventListener('click', function (e) {
        // Open modal
        var openTrigger = e.target.closest('[data-modal-open]');
        if (openTrigger) {
            e.preventDefault();
            var modalId = openTrigger.getAttribute('data-modal-open');
            var modal   = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        }

        // Close modal
        var closeTrigger = e.target.closest('[data-modal-dismiss]');
        if (closeTrigger) {
            var modal = closeTrigger.closest('.modal-overlay');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        }

        // Close on overlay click
        if (e.target.classList.contains('modal-overlay')) {
            e.target.classList.remove('show');
            document.body.style.overflow = '';
        }
    });

    // ============================================================
    // 20. TOOLTIP INITIALIZATION
    // ============================================================

    // Native CSS tooltips via [data-tooltip] attribute — no JS init needed.
    // Included here as a placeholder if Bootstrap tooltips are used instead.

    // ============================================================
    // 21. LOADING STATE HELPERS
    // ============================================================

    /**
     * Show a loading spinner inside a button and disable it.
     *
     * @param {HTMLElement} button
     * @param {string}      text   Loading text (default: 'Memproses...')
     */
    function btnLoading(button, text) {
        if (!button) return;
        button.disabled = true;
        button.setAttribute('data-original-text', button.innerHTML);
        button.innerHTML = '<span class="spinner"></span> ' + (text || 'Memproses...');
    }

    /**
     * Restore a button from loading state.
     *
     * @param {HTMLElement} button
     */
    function btnReset(button) {
        if (!button) return;
        button.disabled = false;
        var original = button.getAttribute('data-original-text');
        if (original) {
            button.innerHTML = original;
            button.removeAttribute('data-original-text');
        }
    }

    window.btnLoading = btnLoading;
    window.btnReset   = btnReset;

    // ============================================================
    // 22. FLASH MESSAGES (from PHP session)
    // ============================================================

    /**
     * Check for flash message data attributes on body or a container
     * and display them as toasts.
     */
    function showFlashMessages() {
        var flashContainer = document.getElementById('flashMessages');
        if (!flashContainer) return;

        var messages = flashContainer.querySelectorAll('[data-flash]');
        messages.forEach(function (el) {
            var type    = el.getAttribute('data-flash') || 'info';
            var message = el.textContent.trim();
            if (message) {
                showToast(message, type, 5000);
            }
        });

        // Remove the container after showing
        flashContainer.remove();
    }

    showFlashMessages();

    // ============================================================
    // END
    // ============================================================

    console.log('%c🏥 ANTRIAN SEHAT', 'color:#2563EB;font-size:16px;font-weight:bold;');
    console.log('%cHealthcare Queue Management System loaded.', 'color:#64748B;font-size:12px;');
});

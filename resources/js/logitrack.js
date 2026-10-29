/* ==========================================================================
   LogiTrack — shared interactions
   Place at: public/js/logitrack.js
   Everything is opt-in through data-* attributes, so Blade logic is untouched.
   ========================================================================== */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initCharCounters();
        initSubmitGuard();
        initDismissibleAlerts();
        initUnsavedWarning();
        initTableFilter();
        initYearFilterMenus();
        initDashboardFeaturedStudies();
        initAuthenticatedBackLinks();
        initNavToggle();
        initReveal();
        initCopyButtons();
        initConfirmDialogs();
        initPasswordToggle();
        initPasswordStrength();
        initPasswordMatch();
        initDialogs();
    });

    /* ---- 13. Open / close a <dialog> -----------------------------------
       <button type="button" data-open-dialog="#my-dialog">Open</button>
       <dialog id="my-dialog" data-autoopen> ... <button type="button" data-close-dialog>Cancel</button>
       data-autoopen opens it on page load (e.g. after a server-side validation error).
       Esc and clicking the dark backdrop also close it.
    --------------------------------------------------------------------- */
    function initDialogs() {
        function open(dlg) {
            if (!dlg) return;
            if (typeof dlg.showModal === 'function') { if (!dlg.open) dlg.showModal(); }
            else dlg.setAttribute('open', '');
        }

        document.querySelectorAll('[data-open-dialog]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                open(document.querySelector(btn.getAttribute('data-open-dialog')));
            });
        });

        document.querySelectorAll('[data-close-dialog]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var dlg = btn.closest('dialog');
                if (!dlg) return;
                if (typeof dlg.close === 'function') dlg.close(); else dlg.removeAttribute('open');
            });
        });

        document.querySelectorAll('dialog.lt-dialog').forEach(function (dlg) {
            dlg.addEventListener('click', function (e) {
                if (e.target === dlg && typeof dlg.close === 'function') dlg.close(); // backdrop click
            });
            if (dlg.hasAttribute('data-autoopen')) open(dlg);
        });
    }

    /* Restore buttons if the browser brings the page back from its back/forward cache,
       otherwise "Logging in..." could stay stuck on a disabled button. */
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        document.querySelectorAll('form[data-submit-guard]').forEach(function (form) {
            form.dataset.submitting = 'false';
            form.dataset.confirmed = 'false';
            var btn = form.querySelector('button[type="submit"]');
            if (btn && btn.dataset.originalHtml) {
                btn.innerHTML = btn.dataset.originalHtml;
                btn.disabled = false;
                btn.classList.remove('is-loading');
                btn.removeAttribute('aria-busy');
            }
        });
    });

    /* ---- 10. Show / hide password --------------------------------------
       <button type="button" class="lt-eye" data-toggle-password="#password" aria-pressed="false">
    --------------------------------------------------------------------- */
    function initPasswordToggle() {
        document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
            var input = document.querySelector(btn.getAttribute('data-toggle-password'));
            if (!input) return;

            btn.addEventListener('click', function () {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            });
        });
    }

    /* ---- 11. Password strength meter (advice only, the server still validates) --
       <input id="password" data-strength="#strength">
       <div id="strength" class="lt-strength" data-level="0"> ... <span class="lt-strength__label">
    --------------------------------------------------------------------- */
    function initPasswordStrength() {
        document.querySelectorAll('[data-strength]').forEach(function (input) {
            var meter = document.querySelector(input.getAttribute('data-strength'));
            if (!meter) return;
            var label = meter.querySelector('.lt-strength__label');
            var names = ['', 'Weak', 'Fair', 'Good', 'Strong'];

            function score(v) {
                if (!v) return 0;
                var s = 0;
                if (v.length >= 8) s++;
                if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
                if (/\d/.test(v)) s++;
                if (/[^A-Za-z0-9]/.test(v) || v.length >= 12) s++;
                return Math.max(1, s);
            }

            input.addEventListener('input', function () {
                var lvl = score(input.value);
                meter.setAttribute('data-level', String(lvl));
                if (label) label.textContent = lvl ? names[lvl] + (lvl < 3 ? ' · try 8+ characters with numbers and symbols' : '') : 'Use at least 8 characters';
            });
        });
    }

    /* ---- 12. Confirm-password match message ----------------------------
       <input id="password_confirmation" data-match="#password" data-match-msg="#match-msg">
    --------------------------------------------------------------------- */
    function initPasswordMatch() {
        document.querySelectorAll('[data-match]').forEach(function (input) {
            var original = document.querySelector(input.getAttribute('data-match'));
            var msg = document.querySelector(input.getAttribute('data-match-msg'));
            if (!original || !msg) return;

            function check() {
                if (!input.value) { msg.textContent = ''; msg.className = 'lt-match'; return; }
                var ok = input.value === original.value;
                msg.textContent = ok ? '✓ Passwords match' : 'Passwords do not match yet';
                msg.className = 'lt-match ' + (ok ? 'is-ok' : 'is-bad');
            }

            input.addEventListener('input', check);
            original.addEventListener('input', check);
        });
    }

    /* ---- 8. Copy to clipboard ------------------------------------------
       <button type="button" class="lt-copy" data-copy="ABC123">Copy</button>
    --------------------------------------------------------------------- */
    function initCopyButtons() {
        document.querySelectorAll('[data-copy]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var text = btn.getAttribute('data-copy');
                var original = btn.textContent;

                function done() {
                    btn.classList.add('is-copied');
                    btn.textContent = 'Copied!';
                    setTimeout(function () {
                        btn.classList.remove('is-copied');
                        btn.textContent = original;
                    }, 1500);
                }

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(done, function () {});
                } else {
                    var ta = document.createElement('textarea');
                    ta.value = text;
                    document.body.appendChild(ta);
                    ta.select();
                    try { document.execCommand('copy'); done(); } catch (err) {}
                    document.body.removeChild(ta);
                }
            });
        });
    }

    /* ---- 9. Confirm before submitting ----------------------------------
       <form data-confirm="Verify this capstone?" data-confirm-title="Verify metadata"
             data-confirm-button="Verify" data-confirm-danger>
       Uses a <dialog> if supported, otherwise window.confirm().
       Runs after the browser's own validation, so required fields are checked first.
    --------------------------------------------------------------------- */
    function initConfirmDialogs() {
        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (form.dataset.confirmed === 'true') return;

                e.preventDefault();
                e.stopImmediatePropagation();

                var message = form.getAttribute('data-confirm');
                var title = form.getAttribute('data-confirm-title') || 'Please confirm';
                var okText = form.getAttribute('data-confirm-button') || 'Confirm';
                var danger = form.hasAttribute('data-confirm-danger');

                function proceed() {
                    form.dataset.confirmed = 'true';
                    if (form.requestSubmit) form.requestSubmit();
                    else form.submit();
                }

                if (typeof HTMLDialogElement === 'undefined') {
                    if (window.confirm(message)) proceed();
                    return;
                }

                var dlg = document.createElement('dialog');
                dlg.className = 'lt-dialog';
                dlg.innerHTML =
                    '<div class="lt-dialog__body">' +
                    '<div class="lt-dialog__title"></div>' +
                    '<p class="lt-dialog__text"></p></div>' +
                    '<div class="lt-dialog__foot">' +
                    '<button type="button" class="lt-btn lt-btn--ghost lt-btn--sm" data-act="cancel">Cancel</button>' +
                    '<button type="button" class="lt-btn lt-btn--sm ' + (danger ? 'lt-btn--danger' : 'lt-btn--primary') + '" data-act="ok"></button>' +
                    '</div>';

                // textContent keeps any text safe from HTML injection
                dlg.querySelector('.lt-dialog__title').textContent = title;
                dlg.querySelector('.lt-dialog__text').textContent = message;
                dlg.querySelector('[data-act="ok"]').textContent = okText;

                document.body.appendChild(dlg);

                dlg.addEventListener('click', function (ev) {
                    var act = ev.target.getAttribute && ev.target.getAttribute('data-act');
                    if (act === 'cancel' || ev.target === dlg) dlg.close();
                    if (act === 'ok') { dlg.close(); proceed(); }
                });
                dlg.addEventListener('close', function () { dlg.remove(); });

                dlg.showModal();
            }, true);
        });
    }

    /* ---- 6. Mobile navigation toggle -----------------------------------
       <button data-nav-toggle="#menu-id"> ... <div id="menu-id" class="lt-nav__menu">
    --------------------------------------------------------------------- */
    function initNavToggle() {
        document.querySelectorAll('[data-nav-toggle]').forEach(function (btn) {
            var menu = document.querySelector(btn.getAttribute('data-nav-toggle'));
            if (!menu) return;

            btn.addEventListener('click', function () {
                var open = menu.classList.toggle('is-open');
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });

            // Close the menu after tapping an in-page link.
            menu.querySelectorAll('a[href^="#"]').forEach(function (a) {
                a.addEventListener('click', function () {
                    menu.classList.remove('is-open');
                    btn.setAttribute('aria-expanded', 'false');
                });
            });
        });
    }

    /* ---- 7. Reveal-on-scroll -------------------------------------------
       <div data-reveal>  (optional data-reveal-delay="120" in ms)
    --------------------------------------------------------------------- */
    function initReveal() {
        var items = document.querySelectorAll('[data-reveal]');
        if (!items.length) return;

        if (!('IntersectionObserver' in window)) return; // content stays visible

        document.documentElement.classList.add('js-reveal');

        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                var el = entry.target;
                var delay = parseInt(el.getAttribute('data-reveal-delay'), 10) || 0;
                setTimeout(function () { el.classList.add('is-visible'); }, delay);
                io.unobserve(el);
            });
        }, { threshold: 0.12 });

        items.forEach(function (el) { io.observe(el); });
    }

    /* ---- 1. Live character counter -------------------------------------
       <input data-counter="#target-id">  (uses the field's maxlength)
       <textarea data-counter="#target-id" data-counter-max="3000">
    --------------------------------------------------------------------- */
    function initCharCounters() {
        document.querySelectorAll('[data-counter]').forEach(function (field) {
            var target = document.querySelector(field.getAttribute('data-counter'));
            if (!target) return;

            var max = parseInt(field.getAttribute('maxlength') || field.getAttribute('data-counter-max'), 10);

            function update() {
                var len = field.value.length;
                target.textContent = max ? len + ' / ' + max : String(len);
                target.classList.remove('is-near', 'is-max');
                if (max && len >= max) target.classList.add('is-max');
                else if (max && len >= max * 0.9) target.classList.add('is-near');
            }

            field.addEventListener('input', update);
            update();
        });
    }

    /* ---- 2. Prevent double submit + loading state ----------------------
       <form data-submit-guard> ... <button type="submit" data-loading-text="Submitting...">
    --------------------------------------------------------------------- */
    function initSubmitGuard() {
        document.querySelectorAll('form[data-submit-guard]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (form.dataset.submitting === 'true') {
                    e.preventDefault();
                    return;
                }

                // Let the browser's built-in validation run first.
                if (!form.checkValidity()) return;

                form.dataset.submitting = 'true';
                form.dataset.dirty = 'false';

                var btn = form.querySelector('button[type="submit"]');
                if (!btn) return;

                var text = btn.getAttribute('data-loading-text') || 'Please wait...';
                btn.dataset.originalHtml = btn.innerHTML;
                btn.classList.add('is-loading');
                btn.setAttribute('aria-busy', 'true');
                btn.innerHTML = '<span class="lt-spinner" aria-hidden="true"></span><span>' + text + '</span>';

                // Disable on the next tick so the form value still posts.
                setTimeout(function () { btn.disabled = true; }, 0);
            });
        });
    }

    /* ---- 3. Dismissible alerts -----------------------------------------
       Success alerts fade out automatically; add data-autohide="5000" to opt in.
    --------------------------------------------------------------------- */
    function initDismissibleAlerts() {
        document.querySelectorAll('[data-autohide]').forEach(function (alertEl) {
            var ms = parseInt(alertEl.getAttribute('data-autohide'), 10) || 5000;
            setTimeout(function () {
                alertEl.style.transition = 'opacity .4s, transform .4s';
                alertEl.style.opacity = '0';
                alertEl.style.transform = 'translateY(-6px)';
                setTimeout(function () { alertEl.remove(); }, 400);
            }, ms);
        });
    }

    /* ---- 4. Warn before leaving a half-filled form --------------------
       <form data-warn-unsaved>
    --------------------------------------------------------------------- */
    function initUnsavedWarning() {
        var forms = document.querySelectorAll('form[data-warn-unsaved]');
        if (!forms.length) return;

        forms.forEach(function (form) {
            form.dataset.dirty = 'false';
            form.addEventListener('input', function () { form.dataset.dirty = 'true'; });
        });

        window.addEventListener('beforeunload', function (e) {
            var dirty = Array.prototype.some.call(forms, function (f) {
                return f.dataset.dirty === 'true';
            });
            if (dirty) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    }

    /* ---- 5. Instant table filter ---------------------------------------
       <input data-table-filter="#my-table">
       Filters rows client-side by visible text.
    --------------------------------------------------------------------- */
    function initTableFilter() {
        document.querySelectorAll('[data-table-filter]').forEach(function (input) {
            var table = document.querySelector(input.getAttribute('data-table-filter'));
            if (!table) return;

            var rows = table.querySelectorAll('tbody tr');
            var empty = document.querySelector(input.getAttribute('data-filter-empty') || '');
            var yearSelect = document.querySelector(input.getAttribute('data-filter-year') || '');

            function filterRows() {
                var q = input.value.trim().toLowerCase();
                var selectedYear = yearSelect ? yearSelect.value : '';
                var visible = 0;

                rows.forEach(function (row) {
                    var matchesText = row.textContent.toLowerCase().indexOf(q) !== -1;
                    var matchesYear = !selectedYear || row.getAttribute('data-study-year') === selectedYear;
                    var match = matchesText && matchesYear;
                    row.classList.toggle('lt-hidden', !match);
                    if (match) visible++;
                });

                if (empty) empty.classList.toggle('lt-hidden', visible !== 0);
            }

            input.addEventListener('input', filterRows);
            if (yearSelect) yearSelect.addEventListener('change', filterRows);
        });
    }

    function initYearFilterMenus() {
        document.querySelectorAll('[data-year-filter-menu]').forEach(function (filter) {
            var toggle = filter.querySelector('[data-year-filter-toggle]');
            var menu = filter.querySelector('.lt-search-filter__menu');
            var select = filter.querySelector('[data-year-filter-select]');
            var options = filter.querySelectorAll('[data-year-filter-option]');
            if (!toggle || !menu || !select || !options.length) return;

            toggle.addEventListener('click', function () {
                var isOpen = menu.hidden;
                menu.hidden = !isOpen;
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                if (isOpen) options[0].focus();
            });

            options.forEach(function (option) {
                option.addEventListener('click', function () {
                    select.value = option.getAttribute('data-year-filter-option');
                    options.forEach(function (yearOption) {
                        yearOption.setAttribute(
                            'aria-pressed',
                            yearOption === option ? 'true' : 'false'
                        );
                    });
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });

            select.addEventListener('change', function () {
                menu.hidden = true;
                toggle.setAttribute('aria-expanded', 'false');

                var form = filter.closest('form[data-year-filter-submit]');
                if (form) form.requestSubmit();
            });

            document.addEventListener('click', function (event) {
                if (!filter.contains(event.target)) {
                    menu.hidden = true;
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });

            filter.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    menu.hidden = true;
                    toggle.setAttribute('aria-expanded', 'false');
                    toggle.focus();
                }
            });
        });
    }

    function initDashboardFeaturedStudies() {
        document.querySelectorAll('[data-dashboard-featured]').forEach(function (featured) {
            var panels = Array.from(featured.querySelectorAll('[data-dashboard-feature-panel]'));
            var track = featured.querySelector('[data-dashboard-featured-track]');
            if (panels.length < 2 || !track) return;

            var activeIndex = panels.findIndex(function (panel) {
                return panel.getAttribute('aria-hidden') !== 'true';
            });
            if (activeIndex < 0) activeIndex = 0;
            panels.forEach(function (panel) {
                var next = panel.querySelector('[data-dashboard-feature-next]');
                if (!next) return;

                next.addEventListener('click', function () {
                    var nextIndex = (activeIndex + 1) % panels.length;

                    panels.forEach(function (item, itemIndex) {
                        var isActive = itemIndex === nextIndex;
                        item.inert = !isActive;
                        item.setAttribute('aria-hidden', isActive ? 'false' : 'true');
                    });
                    activeIndex = nextIndex;
                    panels[nextIndex].querySelector('[data-dashboard-feature-next]').focus();
                });
            });
        });
    }

    function initAuthenticatedBackLinks() {
        var navigation = document.querySelector('.lt-nav__links--app');
        var main = document.querySelector('main');
        if (!navigation || !main || main.querySelector('.lt-back')) return;

        var dashboardLink = navigation.querySelector('a[href*="/dashboard"]');
        if (
            (dashboardLink && new URL(dashboardLink.href).pathname === window.location.pathname)
            || window.location.pathname.startsWith('/similarity')
            || window.location.pathname.startsWith('/instructor/students')
        ) return;

        var backUrl = dashboardLink ? dashboardLink.href : window.location.origin;

        if (document.referrer) {
            var referrer = new URL(document.referrer);
            var currentPage = window.location.pathname + window.location.search;
            var referrerPage = referrer.pathname + referrer.search;

            if (referrer.origin === window.location.origin && referrerPage !== currentPage) {
                backUrl = referrer.href;
            }
        }

        var backLink = document.createElement('a');
        backLink.className = 'lt-back';
        backLink.href = backUrl;
        backLink.textContent = '\u2190 Back';
        main.insertBefore(backLink, main.firstChild);
    }
})();
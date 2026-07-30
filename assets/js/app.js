/* AutoVault shared interface behaviour. Plain JavaScript, no dependencies. */
(function () {
    'use strict';

    // CSS hides the mobile menu only when JavaScript is available.
    document.documentElement.classList.add('js');

    var storageKey = 'autovault-theme';
    var allowedThemes = ['light', 'dark', 'showroom'];
    var colorPreference = window.matchMedia
        ? window.matchMedia('(prefers-color-scheme: dark)')
        : null;

    function savedTheme() {
        try {
            var value = window.localStorage.getItem(storageKey);
            return allowedThemes.indexOf(value) !== -1 ? value : null;
        } catch (error) {
            return null;
        }
    }

    function systemTheme() {
        return colorPreference && colorPreference.matches ? 'dark' : 'light';
    }

    function applyTheme(theme) {
        var safeTheme = allowedThemes.indexOf(theme) !== -1 ? theme : 'light';
        document.documentElement.setAttribute('data-theme', safeTheme);
    }

    // Run immediately in <head> to reduce a flash of the wrong color scheme.
    applyTheme(savedTheme() || systemTheme());

    document.addEventListener('DOMContentLoaded', function () {
        // The three theme buttons in the header. Each carries the theme it
        // selects in data-theme-value (a fixed, whitelisted name).
        var themeButtons = document.querySelectorAll('.theme-switch__option');

        // Mark the button for the current theme as pressed/active so the
        // selected theme is visually obvious and announced to screen readers.
        function syncThemeButtons() {
            var current = document.documentElement.getAttribute('data-theme');
            Array.prototype.forEach.call(themeButtons, function (button) {
                var isActive = button.getAttribute('data-theme-value') === current;
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                button.classList.toggle('is-active', isActive);
            });
        }

        if (themeButtons.length) {
            syncThemeButtons();
            Array.prototype.forEach.call(themeButtons, function (button) {
                button.addEventListener('click', function () {
                    var choice = button.getAttribute('data-theme-value');
                    // Ignore anything not on the fixed whitelist.
                    if (allowedThemes.indexOf(choice) === -1) {
                        return;
                    }
                    applyTheme(choice);
                    try {
                        window.localStorage.setItem(storageKey, choice);
                    } catch (error) {
                        // Storage may be unavailable; the page still updates.
                    }
                    syncThemeButtons();
                });
            });
        }

        // Follow later operating-system changes only until the visitor chooses.
        if (colorPreference && typeof colorPreference.addEventListener === 'function') {
            colorPreference.addEventListener('change', function () {
                if (savedTheme() === null) {
                    applyTheme(systemTheme());
                    syncThemeButtons();
                }
            });
        }

        var navToggle = document.getElementById('navToggle');
        var mainNav = document.getElementById('mainNav');
        if (navToggle && mainNav) {
            function closeNavigation(returnFocus) {
                mainNav.classList.remove('is-open');
                navToggle.setAttribute('aria-expanded', 'false');
                if (returnFocus) {
                    navToggle.focus();
                }
            }

            navToggle.addEventListener('click', function () {
                var isOpen = mainNav.classList.toggle('is-open');
                navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            mainNav.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && mainNav.classList.contains('is-open')) {
                    closeNavigation(true);
                }
            });

            mainNav.addEventListener('click', function (event) {
                if (event.target.closest('a')) {
                    closeNavigation(false);
                }
            });
        }

        // Stop accidental double submissions on authentication forms.
        var authForms = document.querySelectorAll('.auth-form');
        authForms.forEach(function (form) {
            form.addEventListener('submit', function () {
                var submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Please wait...';
                }
            });
        });

        var optionControls = document.querySelectorAll('.vehicle-option__control');
        var configuredPrice = document.getElementById('configuredPrice');
        if (configuredPrice && optionControls.length > 0) {
            function updateConfiguredPrice() {
                var total = Number(configuredPrice.getAttribute('data-base-price'));
                optionControls.forEach(function (control) {
                    if (control.checked) {
                        total += Number(control.getAttribute('data-price-adjustment')) || 0;
                    }
                });
                configuredPrice.textContent = new Intl.NumberFormat('en-CA', {
                    style: 'currency',
                    currency: 'CAD',
                    maximumFractionDigits: 0
                }).format(total);
            }

            optionControls.forEach(function (control) {
                control.addEventListener('change', updateConfiguredPrice);
            });
            updateConfiguredPrice();
        }

        // Respect reduced-motion: if the visitor prefers reduced motion, do not
        // autoplay the gallery videos. We turn autoplay off and pause any that
        // the browser already started, but we leave the controls fully working.
        var reduceMotion = window.matchMedia
            ? window.matchMedia('(prefers-reduced-motion: reduce)')
            : null;
        if (reduceMotion && reduceMotion.matches) {
            var autoVideos = document.querySelectorAll('video[autoplay]');
            Array.prototype.forEach.call(autoVideos, function (video) {
                video.autoplay = false;
                video.removeAttribute('autoplay');
                if (typeof video.pause === 'function') {
                    video.pause();
                }
            });
        }
    });
}());

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
        var themeToggle = document.getElementById('themeToggle');
        var themeLabel = document.getElementById('themeLabel');

        function updateThemeControl() {
            if (!themeToggle || !themeLabel) {
                return;
            }

            var currentTheme = document.documentElement.getAttribute('data-theme');
            var nextTheme = currentTheme === 'light'
                ? 'dark'
                : (currentTheme === 'dark' ? 'showroom' : 'light');
            themeLabel.textContent = 'Use ' + nextTheme + ' theme';
            themeToggle.setAttribute(
                'aria-label',
                'Current theme: ' + currentTheme + '. Switch to ' + nextTheme + ' theme'
            );
        }

        if (themeToggle) {
            updateThemeControl();
            themeToggle.addEventListener('click', function () {
                var current = document.documentElement.getAttribute('data-theme');
                var next = current === 'light'
                    ? 'dark'
                    : (current === 'dark' ? 'showroom' : 'light');
                applyTheme(next);

                try {
                    window.localStorage.setItem(storageKey, next);
                } catch (error) {
                    // Storage may be unavailable; the current page still updates.
                }

                updateThemeControl();
            });
        }

        // Follow later operating-system changes only until the visitor chooses.
        if (colorPreference && typeof colorPreference.addEventListener === 'function') {
            colorPreference.addEventListener('change', function () {
                if (savedTheme() === null) {
                    applyTheme(systemTheme());
                    updateThemeControl();
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
    });
}());

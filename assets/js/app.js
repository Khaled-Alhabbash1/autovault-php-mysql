/* =========================================================================
   AutoVault - Main JavaScript
   -------------------------------------------------------------------------
   Written in plain JavaScript (no libraries or frameworks).
   Its main job right now is to open and close the mobile navigation menu.
   ========================================================================= */

// Wait until the page's HTML is fully loaded before running our code.
document.addEventListener('DOMContentLoaded', function () {

    // Find the menu button and the navigation menu in the page.
    var navToggle = document.getElementById('navToggle');
    var mainNav = document.getElementById('mainNav');

    // Only continue if both elements exist (they are added by header.php).
    if (navToggle && mainNav) {

        // When the button is clicked, show or hide the menu.
        navToggle.addEventListener('click', function () {

            // Toggle the "is-open" class that the CSS uses to show the menu.
            var isOpen = mainNav.classList.toggle('is-open');

            // Update the button's aria-expanded value for screen readers.
            navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    // ---------------------------------------------------------------------
    // Small helper for the login/register forms: stop the submit button from
    // being clicked twice (which could create two accounts on a slow
    // connection). The real validation still happens on the server.
    // ---------------------------------------------------------------------
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
});

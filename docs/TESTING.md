# AutoVault testing guide

## Tests completed in the development environment

The following checks were actually run against PHP 8.0.30 and the configured
Oracle MySQL 8.0.46 server:

- PHP syntax check with `C:\xampp\php\php.exe -l` on all 39 PHP files: passed
- JavaScript syntax check with `node --check assets/js/app.js`: passed
- Final JavaScript behavior harness: 16/16 assertions passed
- Final disposable-database HTTP integration harness: 138/138 assertions passed
- Earlier Milestones 11-12 HTTP integration harness: 43/43 assertions passed
- Earlier Milestones 1-10 disposable-database regression: 53/53 assertions passed
- `git diff --check`: passed
- The complete schema was imported into a disposable database successfully
- `database/sample-data.sql` imported successfully and produced 20 vehicles,
  20 distinct image paths, and 40 option rows (at least two per vehicle)
- All 20 extracted JPEG files and all three supplied MP4 files were present
- Headless Chrome rendered the media page at desktop width and inside a
  constrained 390 CSS-pixel mobile viewport; visual inspection passed
- A 390 CSS-pixel overflow probe reported equal client/scroll widths
- Headless Chrome rendered Home with JavaScript disabled; visual inspection passed
- The disposable database and all temporary test records/files were removed
- The real `autovault` database was not seeded or modified by integration tests

The 138 final integration assertions covered:

- HTTP 200 and expected content for Home, About, Media, seven Help pages,
  Catalogue, vehicle details, favourites, test-drive form, and administrator pages
- All three supplied videos, sample vehicle images, metadata, favicon, skip link,
  contextual Help links, and `aria-controls`
- HTTP 400 for malformed vehicle IDs and 404 for missing vehicles
- Registration validation, normal-user/administrator login, and logout
- Favourite add/remove flows and test-drive request submission
- Logged-out redirects, normal-user HTTP 403, and administrator access
- Vehicle, user, and request administrator views/actions
- GET requests to state-changing administrator actions returning HTTP 405
- Monitoring state/count/version access and safe output
- Database-backed display of two or more options per sample vehicle

The 16 final JavaScript assertions covered:

- JavaScript progressive-enhancement class
- Invalid saved-theme fallback and later system preference changes
- Light, dark, and showroom theme cycling, labels, and persistence
- Mobile navigation opening and `aria-expanded`
- Escape-to-close and focus return
- Navigation-link activation closing the mobile menu
- Initial and changed vehicle-option price estimates

## Tests not claimed as completed

The automated HTTP harness does not render CSS or emulate assistive technology.
These remain manual:

- Edge desktop visual testing
- Keyboard-only traversal in a real browser
- Screen-reader testing
- Measured contrast for all three themes
- Interactive testing on physical mobile devices
- Browser database-unavailable presentation
- Video caption testing (no caption files were supplied)
- A real production deployment, HTTPS, permissions, logs, cookies, and backups

## Manual XAMPP test preparation

1. Place the project directory containing `index.php` below XAMPP `htdocs`.
2. Start Apache and the intended Oracle MySQL 8 service.
3. Import `database/schema.sql`, then `database/sample-data.sql`, into a
   disposable/local test database.
4. Copy `includes/config.example.php` to the ignored `includes/config.php` and
   enter local test settings.
5. Create separate normal/admin test accounts according to `INSTALLATION.md`;
   never place credentials in either SQL import.
6. Use no production or personal data.
7. Open the site through `http://localhost/...`, not `file://`.

## Manual functional test cases

### Registration

1. Register with valid unique details.
2. Confirm redirect to Login and successful login.
3. Confirm the database role is `user`, the account is active, and the stored
   password is a hash rather than the submitted password.
4. Repeat with missing names, invalid email, short password, mismatched
   confirmation, and an existing email.
5. Confirm submitted non-password values remain visible after validation errors.

### Login and logout

1. Log in with a valid active account and confirm the session changes to signed in.
2. Try an unknown email, wrong password, blank fields, and an inactive account.
3. Confirm unknown/wrong details use a general message.
4. Log out using the navigation form and confirm protected pages redirect.
5. Send GET to `logout.php`; confirm it does not perform a state-changing logout.

### Catalogue, sorting, and pagination

1. Add enough available vehicles to create multiple pages.
2. Search by make/model and test each make, fuel, transmission, year, and price filter.
3. Test every sort choice and an unsupported sort value.
4. Confirm pagination keeps active filters and page numbers are validated.
5. Enter quotes, wildcard characters, HTML, and SQL-like text; confirm safe output
   and no database error.

### Vehicle details and IDs

1. Open a valid available vehicle.
2. Confirm specifications, options, description, images/placeholder, and actions.
3. Test missing, zero, negative, non-numeric, oversized, and unknown IDs.
4. Confirm sold/reserved vehicles are not exposed as available public records.
5. Confirm unsafe or external image paths never become image `src` values.

### Favourites

1. Confirm logged-out access redirects to Login.
2. Add an available vehicle, add it again, remove it, and remove it again.
3. Confirm only the current account's favourites appear.
4. Confirm another user's submitted identifier cannot change ownership scope.
5. Submit a missing/invalid CSRF token and confirm no data changes.

### Test-drive requests

1. Confirm logged-out access redirects.
2. Submit a valid future date with and without optional fields.
3. Test past/invalid dates, invalid time/phone, oversized message, invalid vehicle,
   inactive account, and duplicate active request.
4. Confirm name/email come from the session account rather than submitted fields.
5. Submit invalid CSRF and confirm no request is created.

### Administrator authorization

1. While logged out, request every file below `admin/`; confirm redirect to Login.
2. As a normal user, request every administrator page; confirm safe HTTP 403.
3. As an administrator, confirm each page loads.

### Vehicle administration

1. Create a valid vehicle and confirm it appears in management/public views as appropriate.
2. Test required values, ranges, fixed enums, lengths, and duplicate VIN.
3. Edit one field at a time and confirm only supported fields change.
4. Open deactivation confirmation with GET and confirm no state change occurs.
5. Submit deactivation with valid CSRF and confirm status becomes sold.
6. Submit invalid CSRF and confirm no change.

### User administration

1. Test search, role/status/date filters, sorting, and pagination preservation.
2. Activate and deactivate a normal account.
3. Confirm direct GET to the action returns HTTP 405.
4. Confirm invalid CSRF and invalid action do not change data.
5. Confirm the current administrator and final active administrator cannot be deactivated.
6. Confirm role and password hash cannot be mass-assigned or displayed.

### Request administration

1. Test search, status/make/date filters, sorting, and pagination preservation.
2. Open request details containing HTML-like text and confirm it is escaped.
3. Change through pending, confirmed, completed, and cancelled statuses.
4. Confirm direct GET returns HTTP 405 and invalid CSRF/status do not change data.
5. Confirm submitted extra user/vehicle fields cannot be mass-assigned.

### Monitoring

1. Test logged out, normal user, and administrator access.
2. Confirm Connected, version, counts, dates, and configuration-file presence.
3. Stop MySQL temporarily and refresh; confirm only Unavailable is shown.
4. Confirm the page contains no credentials, DSN, paths, environment dump,
   stack trace, SQL, logs, hashes, names, emails, request messages, or VINs.
5. Restart MySQL and confirm recovery on manual refresh.

### Theme and mobile navigation

1. Test light, dark, and showroom themes and reload after each choice.
2. Clear the saved choice and test operating-system light/dark preference.
3. Enter an invalid saved theme in browser storage and confirm safe fallback.
4. At a narrow viewport, open/close the menu, follow a link, and press Escape.
5. Confirm `aria-expanded` updates and Escape returns focus to the menu button.
6. Disable JavaScript and confirm navigation/content/forms remain usable; theme
   switching and menu collapsing are expected not to run.

### Accessibility and error states

1. Use only Tab, Shift+Tab, Enter, Space, and Escape.
2. Confirm the skip link becomes visible and moves focus to main content.
3. Confirm focus remains visible in all three themes.
4. Test 200% and 400% zoom and narrow widths for horizontal page overflow.
5. Confirm administrator tables scroll within their containers.
6. Confirm error/success messages are understandable without colour.
7. Test with a screen reader and inspect landmarks, headings, labels, status
   messages, image alternatives, and tables.

### Media

1. Open Media and confirm all three supplied videos appear with controls.
2. Confirm none autoplay or loop and each can be paused, muted, and viewed fullscreen.
3. Test desktop and narrow layouts and confirm portrait/landscape media remains contained.
4. Temporarily rename one MP4 and confirm the friendly unavailable message appears.
5. Confirm every displayed source is documented in `docs/MEDIA-CREDITS.md`.

## Production verification

Repeat the applicable tests through the final HTTPS URL with production-safe
accounts. Verify error logging, disabled error display, secure cookies,
permissions, database least privilege, backup/restore, and monitoring without
making destructive changes to live data.

# AutoVault

AutoVault is a responsive vehicle marketplace built with plain PHP and
Oracle MySQL. Visitors can browse the catalogue, registered users can save
favourites and request test drives, and administrators can manage catalogue
records, accounts, requests, and safe application health information.

## Main features

- Public home, About, Help, catalogue, and vehicle-details pages
- Catalogue search, filters, fixed sorting choices, and pagination
- Registration, login, logout, sessions, and active-account checks
- Private favourites and authenticated test-drive requests
- Administrator dashboard and vehicle, user, and request management
- Private user profile with editable name and test-drive request history
- Interactive location map (OpenStreetMap) on the About page
- Accessible "vehicles by body type" data visualization on the monitoring page
- Read-only administrator monitoring
- Three persistent site-wide themes: light, dark, and showroom
- Public media page using three student-supplied MP4 videos
- Seven-page context-sensitive Help/Wiki section
- Twenty sample vehicles with unique extracted photographs and selectable options
- Accessible shared layout, keyboard focus styles, and reduced-motion support

See [docs/FEATURES.md](docs/FEATURES.md) for the complete feature list.

## Pages

- **Public:** Home, About (with interactive map), Catalogue, Vehicle details,
  Media, Help landing page, Sitemap, Privacy, and Accessibility.
- **Authenticated user:** Profile, My test-drive requests (full history),
  Favourites, Test-drive request, Login, Register, Logout.
- **Administrator:** Dashboard, Vehicle list, Create vehicle, Edit vehicle,
  Deactivate vehicle, User administration, Test-drive request list, Request
  details, Request status update, and Monitoring (with data visualization).
- **Help/Wiki:** seven topic pages (overview, catalogue, accounts, favourites,
  test drives, media, administration).

## Technology stack

- PHP 8 or newer
- Oracle MySQL 8, InnoDB, and `utf8mb4`
- PDO with native prepared statements
- Semantic HTML5
- External CSS and plain external JavaScript
- No application or CSS framework

## Screenshots

Screenshots are not included. Capture them manually after local testing and
store them in a future `screenshots/` directory using descriptive lowercase
names, for example `home.png`, `catalogue.png`, and `admin-dashboard.png`.

Suggested captures:

- Home page
- Catalogue
- Vehicle details
- Favourites
- Test-drive form
- Admin dashboard
- Admin vehicle management
- User administration
- Request administration
- Dark theme
- Mobile layout

Do not include credentials, session values, private request messages, or other
personal data in screenshots.

## Repository structure

```text
admin/                  Administrator pages
assets/css/style.css    Shared responsive theme styles
assets/js/app.js        Theme and mobile-navigation behaviour
assets/images/          Extracted vehicle photos and interface graphics
assets/media/           Three student-supplied MP4 videos
database/schema.sql     MySQL 8 structure only; no sample data
database/sample-data.sql 20 vehicles, 20 image mappings, and 40 options
docs/                   Feature, user, admin, test, security, and deployment guides
includes/               Shared authentication, database, layout, and helper files
*.php                   Public pages and authenticated user workflows
INSTALLATION.md         Windows/XAMPP setup instructions
```

## Local setup summary

1. Install XAMPP with PHP 8+ and Oracle MySQL 8.
2. Place the project below XAMPP's `htdocs` directory.
3. Start Apache and MySQL.
4. Import `database/schema.sql`.
5. Import `database/sample-data.sql`.
6. Copy `includes/config.example.php` to `includes/config.php`.
7. Enter only your local database settings in the ignored config file.
8. Open the project through `http://localhost/`.

Follow [INSTALLATION.md](INSTALLATION.md) for detailed steps.

## Database

`database/schema.sql` creates the `autovault` database and ten InnoDB tables
using `utf8mb4`. It defines primary keys, intended foreign keys, the unique
email and VIN constraints, the unique user/vehicle favourite relationship,
and no seed or sample records.

`database/sample-data.sql` is imported separately after the schema. It adds
20 fictional catalogue vehicles, one unique local photograph per vehicle, and
two selectable options per vehicle. It creates no users, administrators, or
personal records. Vehicle model/year assignments are close visual matches to
student-supplied photographs and should be reviewed before real-world use.

## Media

- `docs/vehicle-images.pdf` is the repository copy of the supplied source PDF.
- Twenty clean embedded JPEG photographs were extracted to
  `assets/images/vehicles/`.
- Three supplied MP4 files are stored in `assets/media/` and displayed by
  `media.php` using user-controlled HTML5 video.
- No internet media was downloaded and no fake image/video files were generated.
- Missing creator, licence, and source-link information is clearly identified in
  [docs/MEDIA-CREDITS.md](docs/MEDIA-CREDITS.md).

## Themes

The external stylesheet defines visibly distinct light, dark, and showroom
themes with CSS custom properties. The external JavaScript presents three
labelled theme buttons (Light, Dark, Showroom) from a strict whitelist, marks
the active one with `aria-pressed`, saves the choice in `localStorage`, safely
rejects invalid values, and follows the operating-system preference only before
a choice is saved. The site navigation remains usable without JavaScript.

## Help/Wiki

The shared Help link changes with the current workflow. The seven topic pages
cover the overview, catalogue, accounts, favourites, test drives, media, and
administrator tasks. Content-maintenance instructions are available in
[docs/CONTENT-MAINTENANCE.md](docs/CONTENT-MAINTENANCE.md).

## Security practices

- Passwords use `password_hash()` and `password_verify()`.
- Login regenerates the session ID.
- Cookies are HttpOnly and SameSite=Lax; Secure is enabled automatically on HTTPS.
- State changes use POST and CSRF verification.
- SQL parameters use PDO prepared statements.
- Dynamic sort clauses come from fixed server-side whitelists.
- Dynamic output is escaped before HTML rendering.
- Protected pages use session authentication and every administrator page uses a role gate.
- User-facing database errors are generic; technical details go to server logs.
- The real `includes/config.php` is excluded by `.gitignore`.

See [docs/SECURITY.md](docs/SECURITY.md) for limitations and production advice.

## Testing

PHP syntax, JavaScript syntax, schema compatibility, security patterns, and
database-backed HTTP workflows are documented in [docs/TESTING.md](docs/TESTING.md).
Desktop visual, narrow viewport, keyboard-only, JavaScript-disabled, and
production-server checks remain manual and must be completed in the target
browser/server environment.

## Deployment

Production deployment requires PHP 8+, Oracle MySQL 8, HTTPS, a private local
configuration file, production-safe PHP error settings, secure session
configuration, backups, and post-deployment role-based testing. No hosting
provider or production address is assumed. See
[docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).

## Known limitations

AutoVault does not currently provide password reset, email verification,
notifications, payments, automatic image upload, video captions, or advanced
rate limiting.
See [docs/KNOWN-LIMITATIONS.md](docs/KNOWN-LIMITATIONS.md).

This repository identifies AutoVault as a university project; no additional
course or author details are asserted here.

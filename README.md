# AutoVault

A vehicle marketplace built with **plain PHP 8+ and MySQL (PDO)** — no
frameworks. Users can browse vehicles, create accounts, save favourites and
request test drives. Administrators can manage vehicles, users, website
themes and monitor system status.

> University project. Built in small, testable milestones.

---

## Features (planned)

- Browse and search a catalogue of 20+ vehicles
- Vehicle detail pages with selectable options (e.g. colour, trim)
- User registration, login/logout and profile
- Save vehicles to a favourites list
- Request a test drive
- Contact form
- Administrator area: vehicle CRUD, user management (enable/disable),
  theme switching, system monitoring
- Three switchable website themes
- Responsive design, SEO meta tags, interactive map and data visualisation
- Help / Wiki documentation for end users and administrators

## Technology

- **Backend:** PHP 8+ (no framework)
- **Database:** Oracle **MySQL 8** (InnoDB, utf8mb4) accessed with **PDO
  prepared statements**
- **Frontend:** HTML5, external CSS, external JavaScript (no Bootstrap /
  Tailwind / React / Vue)
- **Security:** `password_hash()` / `password_verify()`, CSRF tokens on every
  form that changes data, `htmlspecialchars()` on all user-generated output

## Requirements

- PHP 8.0 or newer
- Oracle MySQL 8.0 (server on `localhost:3306`)
- A web server (Apache via XAMPP, or PHP's built-in server)

## Project structure

```
autovault-php-mysql/
├── admin/            Administrator pages (added in later milestones)
├── assets/
│   ├── css/          External stylesheets (one per theme)
│   ├── js/           External JavaScript
│   ├── images/       Copyright-free images
│   │   └── vehicles/ Vehicle photos
│   └── videos/       Video / audio files
├── database/
│   └── schema.sql    MySQL 8 database schema (structure only, no data)
├── docs/             Help / Wiki and documentation pages
├── includes/         Reusable header, footer, nav, config, db connection
├── *.php             Public pages (index, catalogue, vehicle, login, ...)
├── .gitignore
├── INSTALLATION.md   Step-by-step setup guide
└── README.md
```

> The public `.php` files currently exist as empty placeholders and are
> implemented in later milestones.

## Getting started

See **[INSTALLATION.md](INSTALLATION.md)** for full setup instructions.
Short version:

1. Create the database and tables:
   ```bash
   mysql -h 127.0.0.1 -P 3306 -u root -p < database/schema.sql
   ```
2. Copy the example config and add your database credentials (added in the
   next milestone — credentials are **never** committed to Git).
3. Serve the folder with your web server and open it in a browser.

> This milestone creates the **database structure only** — there is no seed
> or sample data, and no user accounts. Sample vehicles, an admin account and
> the website itself are added in later milestones.

## Security notes

- Database credentials, passwords, API keys and server credentials are
  **never** committed to Git (see `.gitignore`).
- All SQL uses PDO prepared statements.
- Passwords are stored only as hashes from `password_hash()`.

## Documentation

- [INSTALLATION.md](INSTALLATION.md) — how to install and run the project
- `docs/` — Help / Wiki pages, and guides for non-programmers on how to
  update vehicles, images and videos (added in later milestones)

## Licence / media

All images, videos and audio used in this project are copyright-free and
credited in the documentation.

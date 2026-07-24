# AutoVault — Installation Guide

This guide explains how to set up AutoVault on your own computer for
development and testing. It is written for beginners.

---

## 1. What you need

Install **one** of the following all-in-one packages (they include PHP,
MySQL/MariaDB and a web server), or install the parts separately.

- **Windows:** [XAMPP](https://www.apachefriends.org/) or WAMP
- **macOS:** [MAMP](https://www.mamp.info/) or XAMPP
- **Linux:** Apache/Nginx + PHP + MySQL from your package manager

Minimum versions:

- PHP **8.0** or newer (check with `php -v`)
- Oracle **MySQL 8.0** (this project targets MySQL 8 specifically)

> This project was set up against **Oracle MySQL Server 8.0** running on
> `localhost:3306`. XAMPP's bundled MariaDB will also run the schema, but
> MySQL 8 is the intended target.

## 2. Get the project files

Clone the repository (or copy the folder into your web root, e.g. XAMPP's
`htdocs`):

```bash
git clone https://github.com/Khaled-Alhabbash1/autovault-php-mysql.git
cd autovault-php-mysql
```

## 3. Create the database

The file [`database/schema.sql`](database/schema.sql) creates the `autovault`
database and all tables. It contains **structure only — no data**.

### Option A — command line

```bash
mysql -h 127.0.0.1 -P 3306 -u root -p < database/schema.sql
```

Enter your MySQL password when prompted. That's it.

### Option B — phpMyAdmin (XAMPP / WAMP / MAMP)

1. Open phpMyAdmin (usually `http://localhost/phpmyadmin`).
2. Click the **SQL** tab.
3. Open `database/schema.sql`, copy all of its contents, paste it in, and
   click **Go**.

### Verify it worked

List the tables (you should see **10**):

```bash
mysql -h 127.0.0.1 -P 3306 -u root -p -e "USE autovault; SHOW TABLES;"
```

```
contact_messages
favourites
settings
system_services
test_drive_requests
themes
users
vehicle_images
vehicle_options
vehicles
```

Confirm every table uses **InnoDB** and **utf8mb4**:

```bash
mysql -h 127.0.0.1 -P 3306 -u root -p -e "SELECT TABLE_NAME, ENGINE, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA='autovault';"
```

Every row should show `InnoDB` and a `utf8mb4_...` collation. Because there is
no seed data, every table is empty at this stage (`SELECT COUNT(*)` returns 0).

## 4. Configure database credentials

> Credentials are **never** committed to Git. A `config.example.php` template
> and the database connection code are added in the next milestone. When they
> exist, you will copy the template and fill in your own values:
>
> ```bash
> cp includes/config.example.php includes/config.php
> ```
>
> Then edit `includes/config.php` with your database host, name, username and
> password. The real `config.php` is listed in `.gitignore`.

## 5. Run the site

Use whichever matches your setup:

- **XAMPP / WAMP / MAMP:** place the project inside the web root (`htdocs`)
  and open `http://localhost/autovault-php-mysql/`.
- **PHP built-in server** (from the project folder):
  ```bash
  php -S localhost:8000
  ```
  then open `http://localhost:8000`.

> Note: the public pages are empty placeholders until later milestones, so
> there is nothing to view in the browser yet. This milestone sets up the
> project structure and database only.

## 6. Accounts and data

This milestone creates the **database structure only**. There is **no seed
data and no user accounts** yet. User registration, an administrator account
and the sample vehicle catalogue are added in later milestones.

## 7. Resetting the database

`schema.sql` drops the tables before creating them, so you can safely re-run
it to start fresh. **This deletes all data.**

```bash
mysql -h 127.0.0.1 -P 3306 -u root -p < database/schema.sql
```

---

## Troubleshooting

| Problem | Fix |
|---|---|
| `Access denied for user` | Wrong MySQL username/password. Use the credentials you set when installing MySQL/XAMPP. |
| `Unknown database 'autovault'` | The schema did not run. Re-run step 3. |
| `php: command not found` | PHP is not on your PATH. Use `C:\xampp\php\php.exe`, or add PHP to your PATH. |
| Foreign key / syntax errors | Make sure you are on MySQL 8 and running the **whole** file, not a fragment. |

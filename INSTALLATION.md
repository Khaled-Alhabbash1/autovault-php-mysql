# AutoVault installation on Windows with XAMPP

This guide sets up AutoVault locally without placing credentials in Git.

## 1. Required software

- Windows 11
- XAMPP with Apache and PHP 8 or newer
- Oracle MySQL Server 8
- A modern browser
- Git, if cloning the repository

AutoVault targets Oracle MySQL 8. XAMPP may bundle MariaDB, but that is not the
database used for final compatibility testing.

## 2. Clone or copy the repository

Place the project inside XAMPP's web root. A typical location is:

```text
C:\xampp\htdocs\autovault-php-mysql
```

To clone:

```powershell
cd C:\xampp\htdocs
git clone https://github.com/Khaled-Alhabbash1/autovault-php-mysql.git
```

If the clone contains an additional nested project directory, use the
directory that directly contains `index.php`, `admin`, `includes`, and
`database` as the web root.

## 3. Start Apache and MySQL

1. Open the XAMPP Control Panel.
2. Start Apache.
3. Start the intended MySQL 8 service. If MySQL was installed separately,
   start it through Windows Services and make sure its port matches the
   configuration used below.
4. Resolve port conflicts before continuing.

## 4. Create and import the database

The schema creates the `autovault` database automatically. From Command Prompt,
while the project folder is the current directory:

```bat
mysql -h 127.0.0.1 -P 3306 -u root -p < database\schema.sql
```

Alternatively, open phpMyAdmin, choose the Import tab, and import
`database/schema.sql`. The file drops and recreates application tables, so
re-importing it deletes existing AutoVault data.

Import the sample catalogue second:

```bat
mysql -h 127.0.0.1 -P 3306 -u root -p autovault < database\sample-data.sql
```

In phpMyAdmin, select the new `autovault` database and import
`database/sample-data.sql`. It adds 20 fictional vehicles, 20 local image
assignments, and 40 selectable options. It creates no accounts or personal data.

## 5. Create the local configuration

Copy the template:

```powershell
Copy-Item includes\config.example.php includes\config.php
```

Open `includes/config.php` and set the local host, port, database name,
database username, and database password. Do not edit the example template
with real credentials. `includes/config.php` is ignored by Git.

## 6. Confirm the project location

Apache must serve the directory containing `index.php`. If the directory is
`C:\xampp\htdocs\autovault-php-mysql`, open:

```text
http://localhost/autovault-php-mysql/
```

Never open PHP files by double-clicking them or through a `file://` URL.

## 7. Register a normal user

1. Open `register.php` through the site navigation.
2. Enter a name, valid email, and password of at least eight characters.
3. Submit the form and log in.

Registration always creates the fixed `user` role. A browser cannot request
the administrator role.

## 8. Create an administrator safely

AutoVault intentionally has no public role-promotion form and the schema has
no default account. First register the account normally. Then, using a trusted
local MySQL administration tool, identify the intended account by its exact
record and change only its `role` column from `user` to `admin`.

Before changing it:

- Confirm the account ID and email belong to the intended administrator.
- Never change `password_hash` manually.
- Never publish the SQL command, email, or password.
- Keep at least one active administrator; the application prevents the final
  active administrator from being deactivated.

Log out and log in again after the role change so the session receives the
new role.

## 9. Verify the installation

Check:

- Home, About, Help, Media, and Catalogue load.
- Registration and login work.
- The Catalogue contains 20 sample vehicles with unique photographs.
- Light, dark, and showroom themes persist after reload.
- All three videos display controls and play only when requested.
- A normal user receives HTTP 403 for administrator pages.
- An administrator can open the dashboard and monitoring page.
- Monitoring shows `Connected` without exposing configuration values.

## Troubleshooting

| Problem | Resolution |
|---|---|
| Database connection failed | Confirm MySQL is running and that every local config value matches the server. |
| Unknown database or missing table | Import the complete `database/schema.sql` file. |
| Catalogue is empty | Import `database/sample-data.sql` after the schema. |
| Access denied for database user | Correct the local username/password and confirm that user has access to `autovault`. |
| Apache page not found | Confirm the project is under `htdocs` and use the folder containing `index.php`. |
| PHP source appears as text | Access the project through Apache, not as a local file. |
| Apache or MySQL will not start | Check XAMPP logs and resolve port conflicts. |
| Session-expired message | Reload the form and submit the new page token. |
| Administrator link is missing | Confirm the database role, then log out and back in to refresh the session. |
| Images are missing | Store repository-owned images below `assets/images/vehicles/` and use safe relative paths. |
| Video does not play | Confirm the MP4 exists in `assets/media/`, finished uploading, and is served with an MP4-compatible content type. |

For broader checks, follow [docs/TESTING.md](docs/TESTING.md).

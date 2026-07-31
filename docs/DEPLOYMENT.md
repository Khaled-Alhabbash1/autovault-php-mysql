# AutoVault deployment guide

## Production requirements

- A supported web server configured for PHP 8+
- Oracle MySQL 8 with InnoDB and `utf8mb4`
- PDO MySQL extension
- HTTPS certificate and automatic HTTP-to-HTTPS redirect
- A private database account limited to the AutoVault database
- Access to PHP/web-server logs outside the public web root
- A backup and restore process

The deployer must supply the host name, public URL, document root, database
host/port/name/user/password, TLS certificate, log locations, and backup
schedule. This project does not assume a hosting provider.

## Step-by-step: myweb.cs.uwindsor.ca

> **Confirm these details first** (they vary by account and are not guessed
> here): your **SSH/login host**, whether your public web folder is
> `public_html` or `www`, your **MySQL host name**, and your **MySQL database
> name / username / password** (often issued separately, sometimes managed
> through a departmental phpMyAdmin). Get these from the course page or the CS
> systems staff. Everywhere below, replace `YOURUSER`, `DBHOST`, `DBNAME`,
> `DBUSER`, and `DBPASS` with your real values.

### 1. Upload the files
Upload the whole project (the folder that contains `index.php`) into your web
folder, using SFTP (FileZilla/WinSCP) or `scp`. Do **not** upload
`includes/config.php` from your laptop, and do not upload the `.git` folder.

```bash
# Example with scp (run from your project folder on Windows Git Bash):
scp -r ./* YOURUSER@LOGINHOST:~/public_html/autovault/
```

Your public URL will then be something like
`http://myweb.cs.uwindsor.ca/~YOURUSER/autovault/`. (The app uses relative
asset paths and a `<base href="../">` on admin pages, so running inside a
`~YOURUSER/autovault/` subfolder is supported.)

### 2. Create the database and import the tables
Create (or use your issued) MySQL database, then import the schema and the
sample data **in this order**:

```bash
# From a shell on the server, or adjust -h/-P for a remote MySQL host:
mysql -h DBHOST -u DBUSER -p DBNAME < ~/public_html/autovault/database/schema.sql
mysql -h DBHOST -u DBUSER -p DBNAME < ~/public_html/autovault/database/sample-data.sql
```

If you only have phpMyAdmin: open your database, use the **Import** tab, import
`schema.sql` first, then `sample-data.sql`.

### 3. Create the production config
On the server, copy the template and fill in the real database values:

```bash
cd ~/public_html/autovault/includes
cp config.example.php config.php
nano config.php     # set DB_HOST=DBHOST, DB_NAME=DBNAME, DB_USER=DBUSER, DB_PASS=DBPASS
```

In that `config.php`, also **uncomment** the two production lines at the bottom
so PHP errors are hidden from visitors:

```php
error_reporting(E_ALL);
ini_set('display_errors', '0');
```

`includes/config.php` is git-ignored, so it exists only on the server.

### 4. Make the session folder writable
The app stores sessions in `storage/sessions` (it also falls back to the system
temp folder). Create it and allow PHP to write there:

```bash
cd ~/public_html/autovault
mkdir -p storage/sessions
chmod 700 storage/sessions
```

If logins still fail with a session/permission error, set `storage/sessions` to
`chmod 733` (or `777` as a last resort on a restrictive host).

### 5. Confirm the .htaccess is active
The included `.htaccess` blocks web access to `includes/`, `database/`,
`docs/`, `storage/`, and to `*.sql` / `*.md` / `config.php`, and serves `.mp4`
correctly. If the site returns HTTP 500 after upload, your host may disallow
some directives — comment out the `RewriteRule` block and retest, then re-enable
line by line. If `.htaccess` is ignored entirely (AllowOverride off), ask CS
staff to enable it or protect those folders another way.

### 6. Enable HTTPS if available
If `https://myweb.cs.uwindsor.ca/~YOURUSER/...` loads, uncomment the "force
HTTPS" block in `.htaccess`. HTTPS also lets the session cookie become Secure
automatically (the app already sets that based on the connection).

### 7. Test, then submit the URL
Work through **Post-deployment testing** below on the live URL. When every check
passes, submit the working public URL for grading (rubric item 11). Keep the URL
reachable until grading is complete.


## Deploy source and import the database

1. Deploy the repository directory that contains `index.php`.
2. Import `database/schema.sql` into a new empty database.
3. Import `database/sample-data.sql` if the fictional demonstration catalogue is required.
4. Do not re-import the schema over production data: it drops application tables.
5. Confirm all tables use InnoDB and `utf8mb4`.
6. Upload all files below `assets/images/vehicles/` and `assets/media/` in binary mode.
7. Do not deploy local dumps containing real or personal data.

## Configuration

Copy `includes/config.example.php` to `includes/config.php` on the server and
enter production values. Keep this file untracked, unreadable from the web,
and limited to the web-server account. Never place secrets in the template,
repository, screenshots, documentation, or error pages.

## Permissions

- Source code should be read-only for the web-server process where practical.
- The current application has no upload workflow and requires no writable
  public upload directory.
- Vehicle photographs and videos can remain read-only after deployment.
- Session and log locations must be writable by PHP but not publicly served.
- Do not grant broad write permission to the repository.

## Error handling

Production PHP configuration should set:

```ini
display_errors = Off
log_errors = On
```

Store logs outside the public document root, restrict their access, and rotate
them. Application pages intentionally show generic database messages and log
technical exceptions.

## Session and cookie security

- Serve only over HTTPS so session cookies receive the Secure attribute.
- Retain HttpOnly and SameSite protection.
- Choose a session lifetime appropriate to the deployment.
- Keep session storage private and avoid a shared-host session directory that
  exposes one site's sessions to another.
- Confirm session IDs regenerate after login.
- Review domain/path cookie scope if the site is hosted below a subdirectory.

## Backups

Back up the database before releases or schema work. Encrypt backups, restrict
access, define retention, and test restoration. A backup is not reliable until
its restore procedure has been tested.

## Post-deployment testing

Test through the real HTTPS URL:

- Home, About, Media, all Help pages, Catalogue, and vehicle details
- Registration, login, logout, and session expiry
- Logged-out, normal-user, and administrator authorization
- Favourites and test-drive requests
- All administrator workflows and monitoring
- Invalid IDs, invalid forms, CSRF rejection, and database-unavailable output
- Light/dark/showroom themes, keyboard navigation, mobile layout, and JavaScript disabled
- Every referenced image/video returns HTTP 200 and videos support seeking/playback
- Server logs contain technical errors while browser pages do not

## Common hosting problems

- If videos return 404, verify exact filename case and the `assets/media/` upload.
- If MP4 downloads instead of playing, configure the host to serve `.mp4` as `video/mp4`.
- If large uploads are incomplete, compare deployed byte sizes with the repository.
- If images fail, confirm their relative database paths and file permissions.
- If sessions do not persist, verify the private session directory and cookie scope.

See `docs/TESTING.md` for detailed cases.

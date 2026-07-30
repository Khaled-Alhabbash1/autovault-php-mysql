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

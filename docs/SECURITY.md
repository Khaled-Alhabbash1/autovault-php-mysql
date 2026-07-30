# Security

## Implemented controls

### Database queries

PDO uses exception mode, associative fetching, disabled emulated prepares,
and prepared parameters. Dynamic sorting is selected only from fixed
server-side maps. Validated integer LIMIT/OFFSET values are bound as integers.
Fixed SQL fragments created by catalogue/admin filter builders do not contain
raw visitor input.

### Passwords and sessions

Registration stores `password_hash()` output and login uses
`password_verify()`. Login regenerates the session ID. The session cookie is
HttpOnly and SameSite=Lax, with Secure enabled when the request uses HTTPS.
Logout clears session data, removes the cookie, and destroys the session.

### CSRF and HTTP methods

Every current state-changing form includes a random session token and every
handler verifies it. Logout, favourites, test-drive creation, account status,
request status, and vehicle create/edit/deactivation use POST. Direct GET
requests to dedicated account/request status handlers receive HTTP 405.
Vehicle deactivation uses GET only to display confirmation; the state change
requires POST.

### Authentication and authorization

Private favourites and test-drive pages require login. Every administrator
page calls the shared administrator role gate. Registration hard-codes the
normal user role. Ownership-sensitive favourites queries use the trusted
session user ID. Password hashes are selected only for login verification and
are never rendered.

### Validation and output

IDs use strict positive-integer parsers. Enumerated values and return
destinations use fixed whitelists. Form values have length, format, date, and
range validation. Dynamic HTML is escaped with `htmlspecialchars`; intentional
request-message line breaks are added only after escaping. Vehicle image
paths are limited to a repository-owned directory.

The public media page uses a fixed server-owned list and creates a video
element only when the corresponding local file physically exists. It does not
accept visitor-provided media paths. Theme names are limited to the fixed
light, dark, and showroom whitelist.

### Redirects and errors

Redirect destinations are fixed application routes built from validated IDs,
not arbitrary submitted URLs. User-facing database errors are generic.
Technical exceptions use `error_log()` and monitoring never renders their
messages, stack traces, queries, credentials, or environment data.

## Production recommendations

- Require HTTPS and redirect HTTP.
- Use a least-privilege database user, not a server administrator account.
- Keep `includes/config.php` outside version control and restrict file access.
- Disable `display_errors`; enable protected, rotated error logs.
- Configure private session storage and an appropriate session lifetime.
- Add rate limiting for authentication endpoints.
- Back up and test restoring the database.
- Review logs, dependencies, PHP/MySQL patch levels, and account access regularly.
- Verify supplied media provenance/licensing and create captions before public use.
- Perform penetration, browser, and accessibility testing before public launch.

## Remaining limitations

There is no advanced rate limiting, password reset, email verification,
administrator audit log, or multi-factor authentication. CSRF protection does
not replace HTTPS or protection against a successful same-origin XSS attack.
See `docs/KNOWN-LIMITATIONS.md` for the complete functional list.

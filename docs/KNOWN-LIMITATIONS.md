# Known limitations

These are limitations observed in the current application, not unresolved
claims about features that already exist.

- No password-reset workflow
- No email verification
- No email or SMS notifications for test-drive status
- No integrated payment or checkout
- No automatic image upload; administrators currently manage vehicle fields,
  while image records/paths must be prepared separately
- No administrator interface for selectable vehicle options
- No public contact form implementation, despite a supporting schema table
- No user profile-editing page
- No user-facing list of submitted test-drive requests
- No advanced rate limiting, login throttling, or CAPTCHA
- No audit-log table for administrator actions
- Monitoring is manual and request-time only; there are no background checks
  or external alerts
- No automated browser accessibility suite
- No committed automated test suite; current verification uses documented
  repeatable manual/static/integration procedures
- The supplied videos do not include caption or transcript files
- Video credit creator/licence/source details remain for the student to complete
- Field-specific error association (`aria-describedby`/`aria-invalid`) is not
  implemented consistently on every form; errors are presented in a shared
  alert summary
- Sample vehicle make/model/year assignments are visual best matches and require
  student confirmation before they are presented as verified real inventory
- `schema.sql` intentionally contains structure only; the sample catalogue is a
  separate optional import

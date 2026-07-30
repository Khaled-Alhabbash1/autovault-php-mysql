# AutoVault administrator guide

## Access requirements

Every file below `admin/` uses the shared administrator gate. Logged-out
visitors are redirected to login. Logged-in normal users receive a safe HTTP
403 page. Registration cannot assign the administrator role.

The initial administrator must be promoted through a trusted local database
administration process described in `INSTALLATION.md`. Log out and back in
after a legitimate role change.

## Dashboard

The dashboard summarizes total vehicles, available vehicles, users, and
pending requests. It links to vehicle, user, request, and monitoring tools.

## Vehicle management

- **Manage vehicles** lists all statuses with pagination.
- **Add vehicle** validates only known schema fields.
- **Edit** updates the fixed supported field set.
- **Deactivate** is a confirmation workflow that marks a vehicle sold.

Deactivation is a soft action: it removes the vehicle from public available
listings while preserving linked records. VIN values must remain unique when
present.

The current administrator interface does not upload images or edit selectable
options. Follow `docs/CONTENT-MAINTENANCE.md` for trusted image, option, sample
data, video, credit, backup, and redeployment procedures.

## User administration

Search by name/email and filter by role, active status, and registration date.
Sorting uses fixed options. Activation/deactivation uses protected POST forms.
An administrator cannot deactivate the current account, and the final active
administrator cannot be deactivated. The interface does not edit roles or
display password hashes.

## Request administration

Search by user name/email and filter by status, vehicle make, preferred date,
and submitted date. Open a record to review the submitted details. Status can
only be changed to pending, confirmed, completed, or cancelled through a
protected POST form.

## Monitoring

The read-only monitoring page displays:

- Application name, PHP version, and current server time
- Database status as Connected or Unavailable
- Database server version when available
- Presence of required local/template configuration files
- Aggregate user, available-vehicle, favourite, request, and pending counts
- Latest vehicle/request creation timestamps when records exist

It does not display credentials, DSNs, paths, session/cookie values, tokens,
hashes, SQL, logs, exception details, personal records, request messages, or
full VIN values. Database failures are logged server-side and shown only as
Unavailable.

## Safety warnings

- Confirm the target record before any state change.
- Do not share administrator accounts.
- Never edit password hashes manually.
- Do not expose monitoring or administrator pages publicly without HTTPS.
- Back up production data before schema imports or bulk maintenance.
- `database/schema.sql` drops existing tables; never re-import it over live
  data without an approved backup and recovery plan.
- Import `database/sample-data.sql` only after the schema and only where the
  fictional demonstration catalogue is appropriate.
- Confirm the student-supplied media credits before public use.

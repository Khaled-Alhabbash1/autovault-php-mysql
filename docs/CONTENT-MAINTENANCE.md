# AutoVault content maintenance

This guide is for a trusted maintainer. Work on a backup or test installation
first, keep credentials private, and verify every change before deployment.

## Add a vehicle

1. Sign in as an administrator.
2. Open **Admin > Manage vehicles > Add vehicle**.
3. Enter the supported catalogue details and choose a valid status.
4. Submit the protected form.
5. Open the new record and confirm every value.

The administrator form creates the vehicle record but does not upload images or
edit selectable options. Those supporting records require a trusted database
maintenance step until a dedicated interface is added.

## Edit a vehicle

Open **Manage vehicles**, choose **Edit**, update only the necessary fields, and
save. VIN is optional but must be unique when used. Check the public details page
afterward.

## Deactivate a vehicle

Choose **Deactivate**, review the confirmation, and submit. This marks the
vehicle sold and removes it from public available listings while preserving
linked history. The normal interface does not permanently delete vehicles.

## Add and assign a vehicle image

1. Prepare a genuine JPG, PNG, WebP, or safe SVG.
2. Use a descriptive lowercase filename containing only letters, numbers,
   hyphens, underscores, and a file extension.
3. Copy it to `assets/images/vehicles/`.
4. Confirm the file opens and is appropriately licensed.
5. Add a `vehicle_images` row through a trusted database tool:
   - Set `vehicle_id` to the intended record.
   - Use a path beginning `assets/images/vehicles/`.
   - Write concise, meaningful `alt_text`.
   - Set one image per vehicle as `is_primary = 1`.
   - Use `sort_order` for gallery order.
6. Never use an external URL, absolute path, `..`, backslash, script/data URL,
   or unrelated application file.
7. Verify the Catalogue, details, and Favourites pages.

The validated sample workflow is already represented in
`database/sample-data.sql`.

## Replace an image

Prefer a new descriptive filename so browser caches and rollback remain safe.
Copy the new file, update only the intended `vehicle_images.image_path`, test,
then remove the old file only after confirming no database row references it.
Do not overwrite unrelated images.

## Add or replace videos

1. Confirm the supplied file is a real browser-compatible MP4.
2. Copy it to `assets/media/` without disguising another file type.
3. Use a clear filename and keep file size appropriate for the host.
4. Update only the fixed `$mediaItems` entry in `media.php`.
5. Keep `<video controls preload="metadata">`; do not add autoplay or looping.
6. Add verified captions when available.
7. Test desktop, mobile, keyboard controls, volume, seeking, and full-screen.
8. Update `docs/MEDIA-CREDITS.md` with known source/licence information.

If a configured video file is missing, `media.php` shows a friendly message
instead of an empty player.

## Update media credits

Record the exact local filename, media type, usage, source, creator, licence,
source URL, and notes. Never guess. Use **To be completed by the student.** for
unknown supplied-media information.

## Import the schema and sample catalogue

Use a new/empty database. The schema drops AutoVault tables and deletes their
data, so back up first.

Run these commands from Command Prompt, with the project folder as the current
directory:

```bat
mysql -h 127.0.0.1 -P 3306 -u root -p < database\schema.sql
mysql -h 127.0.0.1 -P 3306 -u root -p autovault < database\sample-data.sql
```

Import in that order. `schema.sql` creates structure only. `sample-data.sql`
adds 20 fictional vehicle records, 20 image assignments, and 40 options. It
does not create users or personal records.

## Back up the database

Use a destination outside the public web root:

```powershell
mysqldump -h 127.0.0.1 -P 3306 -u root -p autovault > autovault-backup.sql
```

Protect the dump because a live backup may contain account and request data.
Test restoration into a separate database. Do not commit real-data dumps.

## Protect configuration

Keep real settings only in ignored `includes/config.php`. Never edit
`includes/config.example.php` with real values, publish screenshots of
credentials, or place configuration below a downloadable public directory.

## Test after content changes

- Confirm every referenced file exists with the exact letter case.
- Test Home, Catalogue, vehicle details, Favourites, Test drive, and Media.
- Check alternative text and video controls.
- Test light, dark, and showroom themes.
- Test desktop and narrow mobile widths.
- Confirm normal/admin authorization remains correct.
- Run PHP syntax checks and review browser/server logs.

## Redeploy updated content

1. Back up production data and current files.
2. Upload only reviewed source/media changes.
3. Apply database changes through an approved maintenance process.
4. Keep production configuration untouched.
5. Verify file permissions and HTTPS.
6. Clear only safe application/browser caches if necessary.
7. Run the post-deployment checklist in `docs/DEPLOYMENT.md`.
8. Roll back from the backup if verification fails.

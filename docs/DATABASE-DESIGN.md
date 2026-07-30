# AutoVault - Database Design

The `autovault` database is created by `database/schema.sql` (structure only,
no data) and populated for demonstration by `database/sample-data.sql`.

- **Engine:** InnoDB (supports foreign keys and transactions)
- **Character set:** `utf8mb4` / `utf8mb4_unicode_ci`
- **Access:** PHP PDO with native prepared statements only

## Tables at a glance

| Table | Purpose | Primary key | Key constraints |
|-------|---------|-------------|-----------------|
| `users` | Accounts for visitors who registered and for administrators | `id` | `UNIQUE(email)`; `role` enum; `is_active` flag |
| `themes` | The three selectable site themes | `id` | `UNIQUE(slug)`; `is_active` flag |
| `settings` | Simple key/value site settings | `id` | `UNIQUE(setting_key)` |
| `vehicles` | The vehicle catalogue (the "products") | `id` | `UNIQUE(vin)`; `status` enum; `is_featured` flag |
| `vehicle_images` | Photos for each vehicle | `id` | `FK vehicle_id` → `vehicles(id)` |
| `vehicle_options` | Selectable options/attributes per vehicle | `id` | `FK vehicle_id` → `vehicles(id)` |
| `favourites` | Vehicles a user has saved | `id` | `FK user_id`, `FK vehicle_id`; `UNIQUE(user_id, vehicle_id)` |
| `test_drive_requests` | Test-drive bookings | `id` | `FK vehicle_id`, `FK user_id` (nullable) |
| `contact_messages` | Public contact-form messages | `id` | `is_read` flag |
| `system_services` | Rows the monitoring page can report on | `id` | `UNIQUE(service_key)`; `status` enum |

## Column and relationship detail

### `users`
The account table. Passwords are stored **only** as a bcrypt hash in
`password_hash` (created by PHP `password_hash()`); no plain-text password is
ever stored. `email` is unique so one address maps to one account. `role` is an
`ENUM('user','admin')` defaulting to `user`, and it is **never** set from a
public form. `is_active` (`TINYINT(1)`) lets an administrator enable/disable an
account without deleting it. `created_at` / `updated_at` are timestamps.

### `vehicles`
The core "product" table. Attributes used across the site include `make`,
`model`, `year`, `price`, `mileage`, `fuel_type` (enum), `transmission` (enum),
`body_type`, `color`, `doors`, `seats`, `vin` (unique when present),
`description`, `status` and `is_featured`. The **status field**
`ENUM('available','reserved','sold')` controls visibility: the public catalogue,
vehicle page, and home strip only show `available` vehicles, while
"deactivating" a vehicle in the admin area sets `status = 'sold'` rather than
deleting the row. Indexes exist on `make`, `price`, `status` and `is_featured`
to keep catalogue filtering and sorting efficient.

### `vehicle_images`
One vehicle has many images (`FK vehicle_id` → `vehicles(id)`,
`ON DELETE CASCADE`). `is_primary` marks the photo used on catalogue/list cards;
`sort_order` orders the gallery. Application code still validates every stored
path before it is placed in an `<img>` tag.

### `vehicle_options`
One vehicle has many options/attributes (`FK vehicle_id` → `vehicles(id)`,
`ON DELETE CASCADE`). `option_group` and `option_name` describe the choice, and
`price_adjustment` can raise or lower the base price. Every demonstration
vehicle has at least two option rows.

### `favourites` (users ↔ vehicles)
A **many-to-many** link between users and vehicles. Each row has
`FK user_id` → `users(id)` and `FK vehicle_id` → `vehicles(id)`, both
`ON DELETE CASCADE`, so removing a user or vehicle removes the link.
`UNIQUE(user_id, vehicle_id)` prevents saving the same vehicle twice. Private
pages read favourites using the **session user id only**, so one user can never
see or change another user's list.

### `test_drive_requests` (users ↔ vehicles)
Each request records who wants to test which vehicle, plus `preferred_date`,
`preferred_time`, `phone`, `message`, and a workflow **status field**
`ENUM('pending','confirmed','completed','cancelled')` (new requests default to
`pending`). `user_id` and `vehicle_id` are foreign keys with `ON DELETE SET NULL`
so historical requests survive if the linked user or vehicle is later removed.
Only administrators change the status, always via POST + CSRF.

### `themes`, `settings`, `contact_messages`, `system_services`
Supporting tables. `themes` and `settings` back site configuration (the live
theme control also persists per-visitor choice in `localStorage`).
`system_services` gives the monitoring page named rows to report on. Each uses a
unique business key (`slug`, `setting_key`, `service_key`) and, where relevant,
a simple `status`/`is_*` flag.

## Relationship summary

```
users 1───* favourites *───1 vehicles          (unique user+vehicle pair)
users 1───* test_drive_requests *───1 vehicles  (nullable FKs, SET NULL)
vehicles 1───* vehicle_images                    (cascade delete)
vehicles 1───* vehicle_options                   (cascade delete)
```

## Import order

Foreign keys require the parent tables first, so always import in this order:

1. `database/schema.sql` (creates the database and all tables)
2. `database/sample-data.sql` (inserts demonstration vehicles, images, options)

`sample-data.sql` creates **no** user or administrator accounts and contains no
personal data.

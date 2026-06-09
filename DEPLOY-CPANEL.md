# Deploying to cPanel (samutkarshias.in) — no SSH

Tailored to your server (read from `info.php`): **PHP 8.3 / LiteSpeed / CloudLinux**,
behind **Cloudflare**, document root **`/home/goayu/samutkarshias`**, with
`exec`/`shell_exec` **disabled** and **no SSH**. So we **build locally, upload, and
run setup from a browser** — no terminal needed on the server.

> ⚠️ **Delete `info.php` now.** It publicly exposes server paths, your IP, and a live
> session id. Remove it from the document root before anything else.

---

## The directory layout we'll use

App code lives OUTSIDE the web root; only Laravel's `public/` contents are served:

```
/home/goayu/
├── samutkarsh_app/          ← the whole Laravel project (PRIVATE, not web-accessible)
│   ├── app/ bootstrap/ config/ database/ routes/ resources/ storage/ vendor/ .env …
└── samutkarshias/           ← DOCUMENT ROOT (already configured) = contents of public/
    ├── index.php  (edited — see step 4)
    ├── .htaccess  .user.ini  favicon.ico  robots.txt
    └── build/     (compiled CSS/JS)
```

---

## 1. Build locally (on your dev machine)

```powershell
# PHP deps WITHOUT dev packages (smaller, production-safe)
composer install --no-dev --optimize-autoloader
# Front-end assets (already built earlier; rebuild if you changed views)
npm run build
```
You now have working `vendor/` and `public/build/` folders to upload.

## 2. Upload

Zip the **entire project** and upload via cPanel **File Manager**, then extract to
`/home/goayu/samutkarsh_app/`. Include `vendor/` and `public/build/`. **Exclude**
`node_modules/`, the local `database/database.sqlite`, and any local `.env`.

## 3. Move the public files into the document root

Move **the contents of** `/home/goayu/samutkarsh_app/public/` into
`/home/goayu/samutkarshias/` (the doc root). That includes `index.php`, `.htaccess`,
`.user.ini`, `build/`, `favicon.ico`, `robots.txt`. Leave the rest of the app in
`samutkarsh_app/`.

## 4. Re-point `index.php`

Edit `/home/goayu/samutkarshias/index.php`. Change the two paths that point at the
app so they reach `../samutkarsh_app`:

```php
// require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../samutkarsh_app/vendor/autoload.php';

// $app = require_once __DIR__.'/../bootstrap/app.php';
$app = require_once __DIR__.'/../samutkarsh_app/bootstrap/app.php';
```
(Laravel 12's `index.php` also references `storage/framework/maintenance.php` — update
that line's path the same way if present.)

## 5. Configure `.env`

Copy `.env.cpanel.example` to `/home/goayu/samutkarsh_app/.env` and edit:
- `DB_PASSWORD` → your real MySQL password
- `INSTALL_TOKEN` → a long random string (≥ 20 chars), e.g. mash the keyboard
- `INSTALL_ADMIN_EMAIL` / `INSTALL_ADMIN_PASSWORD` → your first admin login
- (Mail already set to `sendmail`; `APP_KEY` is pre-filled.)

## 6. Permissions

In File Manager, ensure these are writable by the account (usually already 0755/0775):
`samutkarsh_app/storage/` (and everything under it) and `samutkarsh_app/bootstrap/cache/`.

## 7. Create the database (cPanel → MySQL Databases)

Your DB `goayu_samutkarsh-portal` and user already exist. Confirm the user is **added
to the database with ALL PRIVILEGES**.

## 8. Run the installer (browser, one time)

Visit:
```
https://samutkarshias.in/__setup?token=YOUR_INSTALL_TOKEN
```
You'll get a plain-text log: migrations run, roles seeded, Trust Admin created.

## 9. Lock it down

Edit `.env` and **delete the four `INSTALL_*` lines** (or blank `INSTALL_TOKEN`). The
`/__setup` URL then returns 404. Wait ~5 min (LiteSpeed `.user.ini`/env cache) or use
cPanel's "Restart PHP App" if available.

## 10. Public storage symlink (for uploaded images)

Admin-uploaded photos/feature images live in `samutkarsh_app/storage/app/public` and
are served from `/storage`. Create a symlink in the doc root pointing there. In cPanel
**Terminal** (if present) or via File Manager's symlink feature:
```
/home/goayu/samutkarshias/storage  ->  /home/goayu/samutkarsh_app/storage/app/public
```
If you can't make a symlink, this only affects displaying uploaded images — the rest
of the portal works regardless.

---

## Done — verify

- `https://samutkarshias.in/` → public home page
- `https://samutkarshias.in/admin` → log in with your INSTALL_ADMIN credentials
- Create a Center, then test `/register` and `/result`

## Background tasks (cPanel → Cron Jobs)

Add one cron so scheduled tasks (future nightly backup, etc.) run. Use the alt-php CLI
binary:
```
* * * * * /opt/alt/php83/usr/bin/php /home/goayu/samutkarsh_app/artisan schedule:run >/dev/null 2>&1
```
> Notifications use `QUEUE_CONNECTION=sync` (sent inline) — no worker needed.

## Payments go-live

- UPI QR / Cash work immediately. Set `UPI_VPA` to your real UPI ID.
- Razorpay: set `RAZORPAY_ENABLED=true` + keys, add a webhook in Razorpay to
  `https://samutkarshias.in/payments/webhook/razorpay` (events `payment.captured`,
  `order.paid`).

## Notes specific to this host

- **Upload limit was 2 MB** — the included `.user.ini` raises it to 16 MB. Or set it in
  cPanel **MultiPHP INI Editor**.
- **Cloudflare/LiteSpeed**: the app now trusts forwarded headers, so HTTPS detection and
  signed checkout URLs work correctly.
- **Updating later**: re-upload changed files; if you changed the DB, re-enable
  `INSTALL_TOKEN` briefly and hit `/__setup` again (migrations are safe to re-run), or
  run `artisan migrate --force` via a one-off cron.

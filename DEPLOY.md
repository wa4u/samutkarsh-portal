# Deploying the Samutkarsh Portal

A standard Laravel 12 + Filament 3 deployment. Target: a Linux server (cPanel/shared
host or VPS) with **PHP 8.2+** (your 8.5 is fine), **MySQL 8**, and **Composer**.
Node is only needed to build front-end assets — you can build locally and upload
instead (see step 4b).

---

## 0. Server requirements

- PHP **8.2+** with extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`,
  `xml`, `ctype`, `json`, `curl`, `fileinfo`, `gd`, `zip`, `bcmath`, `intl`.
- MySQL 8 (or MariaDB 10.4+) database + user.
- Composer 2.
- The web server's document root must point at the project's **`public/`** folder.

Quick extension check on the server:
```bash
php -m | grep -iE 'pdo_mysql|mbstring|gd|zip|intl|curl|fileinfo'
```

---

## 1. Get the code onto the server

```bash
# via git (recommended)
git clone <your-repo-url> samutkarsh && cd samutkarsh

# …or upload a zip of the project (exclude /vendor, /node_modules, /.env)
```

## 2. Configure the environment

```bash
cp .env.production.example .env
# edit .env: APP_URL, DB_*, MAIL_*, ADMISSION_*, UPI_*, RAZORPAY_*
```

## 3. Install PHP dependencies

```bash
composer install --no-dev --optimize-autoloader
```

## 4. Front-end assets (Tailwind/Vite)

**4a. Build on the server** (if Node 20+ is available):
```bash
npm ci && npm run build
```

**4b. Or build locally and upload** the generated `public/build/` directory.
Nothing else from `node_modules` is needed at runtime.

## 5. Run the installer

This is idempotent — migrates, seeds roles/permissions, creates the Trust Admin,
and links storage:
```bash
php artisan app:install \
  --admin-email="you@samutkarshias.in" \
  --admin-name="Trust Admin" \
  --admin-password="<strong-password>"
```

## 6. Cache for production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
> Re-run these after any `.env` or code change. To undo: `php artisan optimize:clear`.

## 7. Permissions (Linux)

The web-server user needs write access to `storage/` and `bootstrap/cache/`:
```bash
chmod -R ug+rw storage bootstrap/cache
# on many hosts: chown -R www-data:www-data storage bootstrap/cache
```

---

## Web server

### Apache (shared hosting / cPanel)
Point the domain's document root at `public/`. Laravel ships `public/.htaccess`
already — ensure `mod_rewrite` is enabled. Nothing else required.

### Nginx (VPS)
```nginx
server {
    listen 80;
    server_name samutkarshias.in;
    root /var/www/samutkarsh/public;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.5-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```
Then add HTTPS (Let's Encrypt / `certbot`). Set `APP_URL=https://…` — signed
checkout URLs and the Razorpay webhook depend on the correct scheme/host.

---

## Background processing

### Scheduler (one cron entry)
Needed for scheduled tasks (e.g. the nightly backup in a later phase):
```cron
* * * * * cd /var/www/samutkarsh && php artisan schedule:run >> /dev/null 2>&1
```

### Queue worker (only if you move off `QUEUE_CONNECTION=sync`)
Status-change emails are queued. With `sync` (default) they send inline — no
worker needed. For a dedicated queue, set `QUEUE_CONNECTION=database`, run
`php artisan queue:table && php artisan migrate`, and keep a worker alive with
Supervisor:
```ini
[program:samutkarsh-worker]
command=php /var/www/samutkarsh/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=1
```

---

## Payments go-live

1. Set `UPI_VPA` + `UPI_PAYEE_NAME` for the UPI QR.
2. To enable Razorpay: set `RAZORPAY_ENABLED=true` + `RAZORPAY_KEY_ID` /
   `RAZORPAY_KEY_SECRET` / `RAZORPAY_WEBHOOK_SECRET`, then in the Razorpay
   dashboard add a webhook to:
   `https://samutkarshias.in/payments/webhook/razorpay`
   subscribed to `payment.captured` and `order.paid`.
3. Re-run `php artisan config:cache`.

---

## Updating later

```bash
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build            # if assets changed
php artisan migrate --force
php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache
```

---

## WhatsApp archive (one-time content import)

The Activities diary, testimonials, and photo galleries were curated once from
the group's WhatsApp export. To load them on a fresh server:

```bash
# One shot — activities (214), testimonials (16), and photo galleries
# (36 albums / 339 images, fetched + optimised + watermarked on the server).
# Everything lands UNPUBLISHED / pending for review. Idempotent.
php artisan app:import-whatsapp

# Text content only (skip the photo download):
php artisan app:import-whatsapp --skip-photos

# Or run any piece individually:
php artisan db:seed --class=ActivitySeeder
php artisan db:seed --class=TestimonialSeeder
php artisan gallery:import-whatsapp --base-url=https://kamatrelocation.com/kiran/family/
```

Then review in admin: **Content → Activities / Testimonials**, and **Galleries**
(approve + publish the albums you want public). All three are idempotent — safe
to re-run, and they never overwrite edits you've made.

> The photo step depends on the source URL staying reachable. Run it before that
> host goes away. Re-processing is automatic and skips albums already imported.

---

## Go-live checklist

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` set
- [ ] `APP_URL` is the real HTTPS domain
- [ ] MySQL credentials correct; `php artisan app:install` completed
- [ ] Document root = `public/`; HTTPS active
- [ ] Assets built (`public/build/` present)
- [ ] Config/route/view caches warmed
- [ ] Mail sends (test a status-change notification)
- [ ] At least one active Center created; admin can log in at `/admin`
- [ ] Cron `schedule:run` installed
- [ ] Default seeded passwords changed

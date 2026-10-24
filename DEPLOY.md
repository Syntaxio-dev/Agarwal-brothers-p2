# Deploying the Agarwal Brothers site on Hostinger (hPanel)

## FASTEST WAY: one zip into public_html (used for the first upload)

`php tools/make-release.php` (run `npm run build` first, no `npm run dev` running) writes **`release/agarwal-site.zip`** (about 22 MB, about 14,500 files).
Everything is already inside: the site files, the `vendor` folder (no dev tools) and the built CSS/JS. It was rehearsed locally on an empty database before it was given out.

What is inside the zip (this is exactly what `public_html` will contain):

| In public_html | What it is |
|---|---|
| `index.php`, `.htaccess`, `build/`, `css/`, `js/`, `fonts/`, `images/` ... | the website files |
| `laravel_app/` | the application (code, packages, config). `laravel_app/.htaccess` and the main `.htaccess` refuse every request to it |

Steps:
1. hPanel > your website > **File Manager** > open `public_html`, delete Hostinger's default `index.html`/`default.php` if it is there.
2. Upload `agarwal-site.zip` into `public_html` and **Extract** it. Hostinger's File Manager may put everything into a NEW FOLDER: then open that folder, select everything (hidden `.htaccess` too), Move it up into `public_html` and delete the empty folder. The result must be `index.php`, `build/`, `laravel_app/` ... directly inside `public_html` (if `app/` and `vendor/` sit directly there, or the whole site answers 403, it is wrong).
3. Rename `laravel_app/.env.production.example` to `laravel_app/.env` (it MUST be renamed, the site reads only `.env`) and edit it. A database password with `#` or `&` must be in double quotes: `DB_PASSWORD="Ab#12&x"`. Edit: `APP_ENV=staging` (hidden from Google while you test), `APP_URL` = the temporary link, `DB_*` = the database from hPanel, mail later. Keep `SESSION_SECURE_COOKIE=true` only when the link starts with `https://`.
4. SSH (hPanel > Advanced > SSH Access), then:
   ```bash
   cd ~/domains/YOUR-SITE/public_html/laravel_app      # the path shown in File Manager, folder laravel_app
   php artisan key:generate --force
   ln -s laravel_app/storage/app/public ../storage     # run from inside laravel_app; PHP's symlink() is DISABLED on Hostinger, so `php artisan storage:link` fails there
   SKIP_COMPOSER=1 bash deploy.sh                      # migrate + the speed caches (makes the same link if it is missing)
   ```
5. Import the database (phpMyAdmin > Import) and extract the uploads zip of the admin Backup into `laravel_app/storage/app/public` (see below), then run `bash refresh-caches.sh`.
6. Cron job (hPanel > Advanced > Cron Jobs, every minute; there is no `crontab` command over SSH): `cd /home/USER/domains/YOUR-SITE/public_html/laravel_app && php artisan schedule:run >> /dev/null 2>&1`. Without it mail waits in the queue and the nightly backup does not run.
7. Photos and PDFs: pack `storage/app/public` of the laptop (without `livewire-tmp`) into a zip and unzip it into `laravel_app/storage/app/public` on the server (`unzip -o file.zip`, SSH has `unzip`). The admin Backup zip has them under `public/` instead.

The application finds `public_html` by itself because its folder is called `laravel_app` and `../index.php` exists (see `bootstrap/app.php`): `storage:link` and `filament:assets` therefore write into `public_html`. Do not rename `laravel_app`.

**Known limit:** the speed caches (`route:cache`) are tested for a site at the ROOT of a domain (the temporary link and agarwalbrothers.com both are). Do not put this zip in a sub-folder such as `public_html/site/`: with the route cache the home page then answers 405.

---

(The older, longer step-by-step below keeps the `laravel_app` outside `public_html`; the quick way above is enough for a normal Business plan.)


Written for someone who has not deployed a Laravel site before. Do the steps in order.
Do it first on a **demo sub-domain** (for example `demo.yourdomain.com`) to show the client; use the same steps again for the real domain later.

Items marked **(check in your hPanel)** can look slightly different depending on your Hostinger plan: if a menu name differs, look for the same idea.

---

## 0. What you need before you start

- A Hostinger plan with **SSH access** (Premium/Business/Cloud shared plans have it; the cheapest "Single" plan may not).
- A domain (or the demo sub-domain) added in hPanel with **free SSL** switched on.
- On your own computer: this project, Node/npm and PHP/Composer (you already have them).

## 1. Prepare the files on your computer

```bash
npm run build                                   # makes public/build (CSS + JS)
composer install --no-dev --optimize-autoloader # production packages only
```

Make one zip of the project **without** these (they must never go to the server):
`.env`, `.git`, `node_modules`, `storage/logs/*`, `public/hot`, `tests`, any backups.
(`vendor` and `public/build` MUST be inside the zip: the server cannot build them. They are not in Git on purpose.)

## 2. hPanel: PHP version and settings

1. hPanel → **Websites → your site → Advanced → PHP Configuration**.
2. Choose **PHP 8.2** (or newer).
3. **PHP extensions**: tick `gd`, `zip`, `mbstring`, `fileinfo`, `intl`, `curl`, `pdo_mysql`, `openssl`, `xml`, `bcmath`. (GD is needed for WebP images.)
4. **PHP options**: `upload_max_filesize = 32M`, `post_max_size = 40M`, `memory_limit = 256M`, `max_execution_time = 120`.

## 3. hPanel: database

1. **Databases → MySQL Databases → Create**. Note the database name, user and password (Hostinger adds a prefix like `u123456789_`).
2. Keep them for the `.env` file in step 6.

## 4. hPanel: upload the project

Easiest way for a beginner: **File Manager** (or SFTP/FileZilla).

1. In your home folder (the one that contains `domains/`), create a folder, for example `laravel_app` (**outside** `public_html`).
2. Upload the zip there and **Extract** it. You should now see `laravel_app/app`, `laravel_app/public`, `laravel_app/vendor` …
3. Make the website serve the `public` folder. Two ways (pick one):
   - **A. hPanel setting (check in your hPanel):** Websites → your site → look for "Change website root / document root" (on some plans under Advanced) and set it to `laravel_app/public`.
   - **B. Symlink via SSH (works on most plans).** Replace `u123456789` and `yourdomain.com` with your real names:
     ```bash
     cd ~/domains/yourdomain.com
     mv public_html public_html_old          # keep the Hostinger default page, just in case
     ln -s ~/laravel_app/public public_html
     ```

## 5. SSH: open a terminal

hPanel → **Advanced → SSH Access** → enable it and note the **IP, port (often 65002), username**. Then on your computer:

```bash
ssh -p 65002 u123456789@YOUR.SERVER.IP
cd ~/laravel_app
php -v          # must say 8.2 or newer. If it says 7.x, use the PHP 8.2 binary Hostinger gives you (e.g. php8.2) everywhere below.
```

## 6. Create the `.env` file (on the server)

```bash
cp .env.production.example .env
nano .env        # fill in the values (see comments inside), then Ctrl+O, Enter, Ctrl+X
php artisan key:generate
```

Important values:
- `APP_ENV=production` for the live site, **`APP_ENV=staging` for the demo** (the site only lets Google index itself when the environment is `production`, so a demo stays invisible to Google).
- `APP_DEBUG=false` (never `true` on a live site).
- `SESSION_SECURE_COOKIE=true` and `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local` (already in the example file). Switch on **free SSL** and **Force HTTPS** in hPanel; `public/.htaccess` also redirects http to https.
- `APP_URL=https://demo.yourdomain.com` (exact address, with https).
- `DB_*` = the database details from step 3 (`DB_HOST=localhost` on Hostinger).
- Mail (`MAIL_*`) = a mailbox you create in hPanel → **Emails** (use its SMTP details) and `CONTACT_INBOX` = the office address that should receive enquiries.

## 7. SSH: set the site up (one time)

```bash
cd ~/laravel_app
find storage bootstrap/cache -type d -exec chmod 775 {} + && find storage bootstrap/cache -type f -exec chmod 664 {} +
chmod 640 .env
php artisan migrate --force            # creates all tables
php artisan storage:link               # makes uploaded images visible
php artisan filament:assets            # admin panel CSS/JS
php artisan filament:cache-components  # needed after every deploy
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

If `storage:link` says the link already exists, that is fine.
(When using the symlink way in 4B, `storage:link` makes `public/storage` inside `laravel_app/public`; that is the right place.)

Admin user: if you imported the database from the admin Backup, your admin users are already there (change the password after the first login). Only on an EMPTY database create one (use a NEW strong password):

```bash
php artisan tinker
>>> \App\Models\User::create(['name' => 'Admin', 'email' => 'you@company.com', 'password' => bcrypt('A-NEW-STRONG-PASSWORD'), 'role' => 'admin', 'is_active' => true]);
>>> exit
rm -f ~/.config/psysh/psysh_history   # tinker keeps a history file: delete it so the password is not left on the server
```

## 8. hPanel: the cron job (mails, backups, clean-up)

hPanel → **Advanced → Cron Jobs → Create**: every minute (`* * * * *`):

```
cd /home/u123456789/laravel_app && php artisan schedule:run >> /dev/null 2>&1
```
(Use the PHP 8.2 command from step 5 instead of `php` if `php -v` was not 8.2 over there.)
Without this cron job, enquiry emails are saved but **never sent**.

## 9. Check that everything works

Open the site and test, in this order:
1. Home, a product page, search, compare, enquiry list.
2. **Send a real test enquiry** and a contact-form message. Within about a minute a mail must arrive in `CONTACT_INBOX` (if not: wrong SMTP details or the cron job is missing).
3. Log in to `/admin`, upload an image on a product and see it on the site (tests GD + `storage:link`).
4. `storage/logs/laravel.log` should have no new errors (`tail -n 50 storage/logs/laravel.log`).
5. Phone: open the site on your own mobile.

## 10. Updating the site later

1. On your computer: `npm run build`, make the zip again (or upload only the changed files + the `public/build` folder).
2. Upload and extract over the old files (never overwrite `.env` or `storage/app` or `storage/logs`).
3. On the server run `bash deploy.sh` (it migrates and rebuilds the caches; it also runs `filament:cache-components`).

**`php artisan filament:cache-components` MUST run after every deploy.** The admin panel reads its list of pages from that cache on the live site; without it new admin pages or widgets "do not exist" and every admin page is slower. `deploy.sh` does it for you (through `refresh-caches.sh`). After changing `.env` only, run `bash refresh-caches.sh`.

## 10b. Speed: what the scripts cache, and the server checklist

`refresh-caches.sh` (run by `deploy.sh`) clears and rebuilds, in this order: `config:cache`, `route:cache`, `view:cache`, `event:cache`, `filament:cache-components`, `icons:cache`. If the server has Composer, `deploy.sh` first runs `composer install --optimize-autoloader --no-dev` (otherwise upload `vendor` built on your computer with the same command).

The caches only help with **`APP_ENV=production`** in `.env` (`deploy.sh` warns if it is something else). On a development machine `AdminPanelProvider` deletes Filament's cached panel on every request on purpose, so new pages show at once; that happens only when `APP_ENV=local`.

Server checklist (ask Hostinger support or set it in hPanel > PHP configuration):
- [ ] PHP OPcache on: `opcache.enable=1`, and in production `opcache.validate_timestamps=0` (then PHP must be restarted, or OPcache reset in hPanel, after each deploy so it sees the new files).
- [ ] Gzip or Brotli compression on for HTML, CSS, JS and SVG (Hostinger: usually on by default; check a page in the browser's Network tab, `content-encoding: br` or `gzip`).
- [ ] Assets built with `npm run build` and the `public/build` folder uploaded. There must be **no `public/hot` file** on the server (it makes pages load assets from a Vite dev server that does not exist there).
- [ ] `APP_DEBUG=false`; no Debugbar or Telescope installed in production (this project has neither).
- [ ] If the plan offers Redis: `CACHE_STORE=redis` and `SESSION_DRIVER=redis` (the dashboard count cache and sessions then live in memory). Otherwise the default database cache is fine.

What the app itself does: the dashboard counts are cached for 90 seconds (`App\Support\DashboardCache`) and cleared at once when a matching record is saved or deleted, and the columns the admin lists and counts filter on have database indexes (migration `2026_10_22_120000`).

## 11. When the client says "go live"

- Replace the dummy data; the company email, phones, address and GSTIN in `config/contact.php` are already real (branch office and working hours are still placeholders); set the WhatsApp number in Site Settings, social links, the Terms page and have the privacy text checked.
- Add the real domain in hPanel, set `APP_ENV=production` and `APP_URL=https://yourdomain.com`, then run `bash deploy.sh` again.
- Change the admin password, keep a copy of the `.env` file somewhere safe (never in Git), and check that the nightly backup is created (admin → Backups).

## Common problems

| Problem | Reason / fix |
|---|---|
| White page or "500" | Look at `storage/logs/laravel.log`. Usually a wrong `.env`, missing `vendor`, or `storage` not writable (`chmod -R 775 storage bootstrap/cache`). |
| Page looks unstyled | `public/build` was not uploaded, or `APP_URL` is wrong. |
| Images missing | `php artisan storage:link` was not run, or GD extension is off. |
| Admin pages "do not exist" after an update | Run `php artisan filament:cache-components`. |
| Mails never arrive | Cron job missing, or wrong SMTP details, or a spam filter. |
| "Class not found" after upload | `vendor` folder is outdated: run `composer install --no-dev --optimize-autoloader` again before zipping. |

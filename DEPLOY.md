# Deploying the Agarwal Brothers site on Hostinger (hPanel)

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
- `APP_URL=https://demo.yourdomain.com` (exact address, with https).
- `DB_*` = the database details from step 3 (`DB_HOST=localhost` on Hostinger).
- Mail (`MAIL_*`) = a mailbox you create in hPanel → **Emails** (use its SMTP details) and `CONTACT_INBOX` = the office address that should receive enquiries.

## 7. SSH: set the site up (one time)

```bash
cd ~/laravel_app
chmod -R 775 storage bootstrap/cache
php artisan migrate --force            # creates all tables
php artisan storage:link               # makes uploaded images visible
php artisan filament:assets            # admin panel CSS/JS
php artisan filament:cache-components  # needed after every deploy
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

If `storage:link` says the link already exists, that is fine.
(When using the symlink way in 4B, `storage:link` makes `public/storage` inside `laravel_app/public`; that is the right place.)

Create the first admin user (use a NEW strong password, not the one from your computer):

```bash
php artisan tinker
>>> \App\Models\User::create(['name' => 'Admin', 'email' => 'you@company.com', 'password' => bcrypt('A-NEW-STRONG-PASSWORD'), 'role' => 'admin', 'is_active' => true]);
>>> exit
```
(If your users table uses other column names, check `app/Models/User.php` / the Users resource in the admin first.)

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

# CLAUDE.md — Agarwal Brothers website

Working notes for Claude (and any developer) on this project: what it is, how it is built, the tools we use,
the rules the owner has set, and a log of everything done so far. Keep this file up to date after every batch of work.

---

## 1. Project snapshot

- **What:** public website + admin panel for **Agarwal Brothers**, a laboratory-equipment / scientific-instruments / chemicals supplier in Jaipur (43+ years, 50+ global brands). Visitors browse verticals → brands → product lines → products, compare products, send enquiries; staff manage everything in the admin panel.
- **Stack:** Laravel 12.69 · PHP 8.2 · MySQL · Filament 5.9 (admin) · Livewire 3 · Alpine.js 3 · Tailwind CSS 4 · Vite 7.
- **Repo:** `https://github.com/Syntaxio-dev/Agarwal-brothers-p2.git`, branch `main`. Local path `C:\xampp\htdocs\AB-new-project1` (Windows, XAMPP).
- **Databases:** dev `ab_new_project`, tests `ab_new_project_testing` (a guard in `tests/TestCase.php` refuses any DB whose name does not end in `_testing`).
- **Owner/user:** non-English-first (writes Hinglish). Reply in the same language style: Hinglish, short, plain.

## 2. How the owner wants me to work (standing rules)

1. **Design uniformity above all.** Everything must look like the same site. Only the palette below, no new colours. No glassmorphism. Buttons look like the existing ones (solid navy with a cyan bottom edge, ghost buttons, mono eyebrow labels).
2. **Push only when told** ("push kr do"). Commit locally freely; never push on my own.
3. **Be honest.** Say what was verified and what was not. Report mistakes plainly. "Make no mistakes" = test in the real browser, not only in unit tests.
4. **Do it page by page / step by step** for big features (AOS animations, UX list). Ask only when a decision is genuinely the owner's; otherwise choose like a senior full-stack developer and explain briefly.
5. **Never write the admin password into any file or commit.** The admin user `admin@agarwalbrothers.com` exists (created at the owner's request). For tests use throwaway users.
6. **Never send real mail from tests** (`Mail::fake()` is set in `TestCase`). Never touch dev data from automated tests; E2E scripts that save data must restore it.
7. **Dummy data stays for now** (dummy products, brands, phone numbers `+91 00000 …`, footer placeholders). The owner will replace it at the end. Do not suggest it again.
8. Skipped on purpose: the "guided first-product checklist with screenshots" idea.

## 3. Design system

| Token | Hex | Use |
|---|---|---|
| navy | `#0B2545` | text, primary buttons, sidebar, footer |
| link | `#0077B6` | links, accents, "primary" in admin |
| cyan | `#00B4D8` | highlights, edges, focus on dark |
| cyan-ink | `#007C98` | cyan **text/icons on light backgrounds** (4.8:1 contrast; use `text-cyan-ink`, not `text-cyan`) |
| slate | `#6C757D` | muted text |
| ice | `#F4F9FB` | tinted section backgrounds |
| success | `#2A9D8F` | ok / done |
| alert | `#E76F51` | errors / attention (also Filament `danger` and `warning`) |

- **Fonts:** IBM Plex Sans (body) + IBM Plex Mono (eyebrows, labels, code-like values), loaded via `@fontsource`, preloaded in the layout head.
- **Signature components** (`resources/css/app.css`): `btn-primary`, `btn-ghost`, `btn-glass` (solid cyan button for dark backgrounds despite the name), `chip` / `chip-active`, `section-badge`, `.crumbs` (chevron breadcrumbs), `rich-text`, image shimmer `.img-load`, scroll-reveal keyframes.
- **Admin theme:** `resources/css/filament/admin/theme.css` (navy sidebar, ice background, navy buttons with cyan edge). Use Filament colour names (`primary`, `info`, `success`, `danger`) in admin views so colours stay on palette.
- **Motion:** balanced. Entrance ≈ 600 ms ease-out, 22 px travel (40 px / 700 ms for tall blocks), 70 ms stagger. Always respect `prefers-reduced-motion`.

## 4. Commands

```bash
php artisan serve                     # dev server (the project often has `npm run dev` running; public/hot points assets at Vite)
npm run dev | npm run build           # Vite (build before committing view/css/js changes that must work without the dev server)
php artisan test                      # PHP tests (uses ab_new_project_testing)
node --test tests/js/admin-drafts-core.test.mjs   # JS unit tests
php -d extension=gd vendor/phpunit/phpunit/phpunit --filter=ImageTest   # image tests need GD (XAMPP has it OFF in php.ini)
php artisan migrate
php artisan queue:work --stop-when-empty          # mail queue (production: the scheduler runs it every minute)
php artisan schedule:run                          # the ONE cron entry on the server
php artisan backup:run                            # full backup (DB + uploads), also nightly 02:30
php artisan housekeeping:run                      # clean-up, nightly 03:30
php artisan images:optimize [--apply]             # lighten already-stored JPEG/PNG (dry run by default)
php artisan filament:cache-components             # RUN AFTER EVERY DEPLOY (production uses Filament's cached component list)
```

Production `.env` essentials: `APP_ENV=production`, `APP_DEBUG=false`, real `APP_URL`, `QUEUE_CONNECTION=database`, SMTP credentials, `CONTACT_INBOX`, optional `ANALYTICS_ID` (GA4, only loads after cookie consent), `PRIVACY_EMAIL`. Server needs PHP GD (WebP), `zip`, `mbstring`, `fileinfo`, and the `schedule:run` cron.

## 5. Architecture map

**Public site** (`routes/web.php`, `app/Http/Controllers/PublicController.php`, `CareerController`, `ContactController`, `SeoController`)
Home · Our Story · Verticals (index/show) · Brand · Category · Product · Search (+ live JSON `/search/suggest`) · Compare `/compare` · Enquiry list `/enquiry-list` · Insights (blogs/news/webinars + show) · Application resources · Careers (+ apply) · Contact · Privacy `/privacy-policy` · sitemap/robots · branded 403/404/419/429/500/503.
Views: `resources/views/public/*`, layout `components/layouts/app.blade.php`, shared components in `resources/views/components` (compare-tray, recently-viewed, cookie-consent, search-suggest, help-links, form/*).

**Admin** (Filament, `app/Filament`): Resources for slides, clients, reviews, team, insights, application resources, verticals, brands, countries, categories, products, enquiries, contact messages, job openings/applications, site settings, users, email templates, activity log. Pages: Dashboard, Help Guide, Backups, Import products. Widgets: QuickUpdates, OverviewStats, LatestActivity, TeamActivity. Shared: `Concerns/RestrictedByRole`, `Support/{Uploads,ViewOnSite,ReplyByEmailAction,AdminSearchProvider,SeoSection}`, `RelationManagers/TeamNotesRelationManager`, `Resources/Products/Actions/DuplicateProductAction`.

**Roles** (`config/roles.php`, `User::canManage($area)`): `admin` (everything), `editor` (content + catalogue), `sales` (enquiries, contact messages, products), `hr` (jobs, applications). Admin-only areas: `users`, `site-settings`, `backups`, `email-templates`, `activity-log`. Users can be switched active/inactive; the last active admin and yourself are protected from lock-out/deletion.

**Key models:** Product (specs/features/advantages/faqs/documents JSON), Category, Brand, Vertical, Country, Insight (blog/news/webinar), Slide, Client, Review, TeamMember, JobOpening, JobApplication, Enquiry (+ EnquiryItem for group enquiries), ContactMessage, ApplicationResource, SiteSetting, User, Note (team notes, polymorphic), EmailTemplate, ActivityLog.
Traits: `HasSeo`, `HasTeamNotes` (relation is `teamNotes`, because JobApplication already has a `notes` column), `LogsActivity`.

**Support classes (`app/Support`)**: `Seo`, `Img` (width/height attributes), `ImageOptimizer` (WebP), `FormRules`/`DialCodes` (name + 10-digit phone + country), `HelpGuide` (admin guide text + search), `HelpLinks`, `BackupService`, `ProductImporter`, `ProductDuplicator`, `EnquiryExporter`, `EmailTemplates` (placeholders), `AutoReply`, `ActivityLogger`, `Preview`.

**Config:** `config/roles.php`, `config/contact.php` (head office, departments, phones, hours — all DUMMY numbers), `config/privacy.php` (retention text), `config/services.php` (`analytics_id`).

**JS (`resources/js`)**: `app.js` (Alpine stores: compare, enquiryList, recent; ghost search + live suggestions; back link), `form-guard.js` (inline errors, country picker), `catalogue-filter.js`, `privacy.js` (saved-data TTL, consent, optional analytics), `reveal.js` (scroll reveal), `brand-map.js`, `review-carousel.js`, `admin-drafts*.js` (admin draft safety net).

## 6. Tools we use

**Project tooling:** Laravel Artisan + Tinker · Composer · npm/Vite · Tailwind 4 (`@theme` tokens) · Filament 5 (+ Livewire testing helpers) · PHPUnit 11 via `php artisan test` · Node's built-in test runner (`node --test`) · MySQL (XAMPP) · GD (WebP) · OpenSpout (read/write CSV/XLSX; ships with Filament) · jsvectormap (world/Rajasthan maps) · Git + GitHub.

**How Claude works here (Claude Code tools):** Read / Edit / Write / Glob / Grep for code · Bash and PowerShell for commands · small **Python scripts** written to the scratchpad for multi-file edits (always assert the text being replaced exists) · `php artisan tinker <file>` for one-off checks · **Puppeteer-core + the installed Chrome** (installed only in the session scratchpad, never in the project) for real-browser end-to-end checks and screenshots · plan mode for big designs · optional agents/workflows only when the owner asks.

**Verification routine:** (1) `php -l` on changed PHP, (2) feature tests with `Mail::fake()`, (3) `npm run build`, (4) Puppeteer run on a temporary server (`php artisan serve --port=877x` with `APP_URL` set) using a **temporary admin user that is deleted afterwards**, (5) look at screenshots, (6) restore any dev data touched, (7) stop the test server.

## 7. Gotchas learned (read before changing things)

- **Bash heredocs with quotes/backslashes break often in this environment.** Write files with the Write tool; write Python edit scripts to the scratchpad and run them. Python in a heredoc collapses `\\` to `\` (use `chr(92)` or a script file).
- **Filament caches its component list** in `bootstrap/cache/filament` for web requests only (not console/tests). A stale list makes new admin pages "not exist" in the browser. `AdminPanelProvider` deletes it on local; on production run `filament:cache-components` after each deploy.
- **Eloquent already owns a `$changes` property** → the activity log column is `details`, not `changes`.
- **MySQL JSON columns reorder keys**; never assert key order of stored JSON.
- **Throttle (`5,1`) on forms** makes loops of test posts return 429: tests that post repeatedly use `withoutMiddleware(ThrottleRequests::class)`.
- **Tailwind v4 hover translate uses the `translate` property**, so reveal animations (which use `transform`) do not clash with card hovers. Reveal uses CSS *animations*, not transitions, for the same reason.
- **`html { scroll-behavior: smooth }`** makes `scrollIntoView`/`scrollTo` in test scripts animate; pass `behavior: 'instant'`.
- **Filament lazy-loads dashboard widgets**; test them with `Livewire::test(Widget::class)`, not by searching the page HTML.
- Filament field `wire:model.blur` only sends text on blur → the draft system copies on-screen text values.
- `public/hot` present = pages load assets from the Vite dev server; `npm run build` is still required for production.
- Sidebar labels in headless Chrome appear after a short Filament load animation (not a bug for real browsers).
- GD is disabled in this XAMPP, so 4 image tests are skipped locally; they pass with `-d extension=gd`.

## 8. Security & privacy decisions

SecurityHeaders middleware (CSP basics, nosniff, frame options, HSTS in production) · `SafeLink` rule for admin-entered URLs · upload whitelist (no SVG) + `.htaccess` in `storage/app/public` · HTML from the editor sanitised with `Str::sanitizeHtml` · resumes stored privately on the `local` disk · honeypots + throttles on forms · auto-reply limited to 3/day per address · exports neutralise spreadsheet formulas · passwords never logged · delete guards on Brand/Category (nothing with children can be deleted by accident) · last-admin protection · cookie notice + privacy page (retention numbers in `config/privacy.php` need legal review) · browser-saved lists expire (compare 7 d, enquiry list 30 d, recently viewed 30 d, consent 180 d, admin drafts 7 d).

## 9. Work log (chronological)

**28 Sep – 2 Oct 2026 — foundation**
- Initial Laravel + Filament setup, first catalogue models, seeder, first frontend attempt; pre-production critical fixes.
- Complete frontend rebuild with an Inkarp-inspired sidebar layout, global brands map and search.

**9 Oct 2026 — the big build-out (many requests, grouped)**
- *Pages:* Home rebuilt in a fixed order (hero carousel, who-we-are with 43+ years and animated counters, two-row brand marquee, verticals, precision picks selector, world map with admin-managed countries/pins, blogs, news, clients carousel, reviews). Our Story (timeline, values, leadership blocks, team, IBM Plex type system). Contact page with Rajasthan map, five cards and a quick-response form that mails the office. Insights: blogs/news/webinars with filters, PDF, cover, countdown. Application Resources. Careers module (openings, dynamic questions, applications, private resumes). Floating phone/WhatsApp buttons. Insights hover menu.
- *Catalogue:* verticals → brands → product lines (categories) → products flow with admin CRUD, specs/features/advantages/FAQs/documents/video, "Precision Picks" flag, spec section above features.
- *Style decisions:* palette cleanup, signature buttons/chips/breadcrumbs, glassmorphism tried then withdrawn, IBM Plex fonts.
- *SEO:* `Seo` helper, `HasSeo`, per-page titles/descriptions/OG/JSON-LD, dynamic sitemap + robots, noindex outside production.
- *Quality:* branded error pages, search and insight polish, full security review and fixes.
- *Admin:* role-based multi-user system (admin/editor/sales/hr, active/inactive), Users & Roles resource, dashboard Quick updates, Help Guide (simple-English notes for every area), Backups page (DB dump + uploads zip, nightly), admin theme in the site palette, queued mail with a scheduler-driven worker, real automated tests (separate `_testing` DB).
- Commits: `42bd563` … `ee30008`, `ea2650f`.

**10 Oct 2026 — UX improvements (`9568bbc`)**
Product **compare** (up to 4, side-by-side specs, differences-only) · **enquiry list / group enquiry** with quantities and a serial-numbered office email · **live search suggestions** · category **filters** (only for lists ≥ 8) · **breadcrumbs everywhere** with JSON-LD + smart "Back to results" · **recently viewed** card · branded **404/empty states** with popular verticals and phone cards · **form polish** (inline errors, loading state, names letters-only, 10-digit phone + country picker, values kept) · **images** (lazy, width/height, shimmer, preloaded fonts, WebP on upload, lighter logo/office photo) · **accessibility** (skip link, focus rings, cyan-ink contrast, reduced motion, required slide alt text) · **cookie notice + privacy policy**, expiring browser data, `housekeeping:run`, bandwidth cache rules.

**10 Oct 2026 — admin tools (`7f4168e`, `39e3f95`)**
Product **bulk actions** (activate/deactivate/move) · **CSV/XLSX import** (all-or-nothing validation, format guide + sample file) · **Duplicate product** · **View on site / staff-only preview** + card preview checklist · dashboard **to-do cards** that open pre-filtered lists · **admin search** (products, brands, enquiries + How-to answers, Ctrl+K) · quality-first **image conversion** (lossless WebP for PNG, q90 for JPEG, 2400 px) · fix for the stale Filament component cache.

**11–12 Oct 2026 — team workflow (`91ff084`)**
**Enquiry workflow** (owner, Quote-sent status, private team notes + automatic log, filters, bulk assign/status, CSV/Excel export) · **Reply by email** + **7 editable email templates** with automatic "we will connect shortly" replies and on/off switches · **unsaved-changes warning + browser-only drafts** (restore/discard, 7-day expiry, verified in real Chrome) · **Activity log** (who changed what, sign-ins, details with before/after, dashboard box, 6-month clean-up) · "Read the guide" bar no longer glued to the top bar.

**Scroll reveal (AOS-style) — in progress (`2c2ba0a`, `0ab3d0b`)**
Own `reveal.js` (no library): starts when 200 px / 60 % of an item is visible, stagger for items arriving together, bigger entrance for tall blocks, failsafe + reduced-motion handling. Home page done (40 animated items; blogs, precision picks, news, clients split into pieces). Marquee: each brand row now stops by itself on hover/focus.

**Our Story page animations (Balanced)** — base `data-reveal` on all sections plus: journey timeline "draw" (`data-reveal="draw"`: the dotted wave wipes in over 1.8 s, each year dot pops when the line reaches it, then its stem and card; phone shows a simple staggered list), value cards (icon pop-in, hover tilt + cyan top edge), team name cyan underline on hover, leadership portrait/quote slide from opposite sides + quote-mark pop, button arrows nudge on hover (`.arrow-nudge` class — add it to arrows on other pages when they are done), Mission/Vision/Goal tabs with a sliding navy pill (Alpine `place()`) and slide+fade panels, banner intro (photo slow zoom, title rise). Not built on purpose: clip-path photo reveal, count-up, parallax. All CSS sits in the `prefers-reduced-motion: no-preference` block of `app.css`.

**Catalogue pages reveal (done by a second agent, checked afterwards)** — `data-reveal` added to Verticals index, Vertical, Brand, Category and Product pages (headers, cards, sections, CTA). Checked in real Chrome on desktop and phone: every item ends visible, no layout shift, no sideways scroll, no console errors. One gap found and fixed: cards that appear without scrolling (a Category filter showing/hiding models, `x-show`) never got their turn, so `reveal.js` now also watches `style/class/hidden` changes (MutationObserver) and re-checks. The Category filter only shows with 8 or more models (the dummy data has none that large), so it was tested with a simulated hide/show.

**Notes for any new agent / helper**
- Start by reading this file. Uncommitted changes in `git status` may belong to another agent: look at `git diff` before touching those files.
- Never run two PHPUnit runs at the same time: they share `ab_new_project_testing` and corrupt it (symptom: "Base table or view not found" everywhere). Fix: drop and recreate that database (`DROP DATABASE ab_new_project_testing; CREATE DATABASE ab_new_project_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`), then run once.
- `php artisan tinker <file>` can hang in this environment; for one-off checks write a small PHP file that boots the app (`require 'vendor/autoload.php'; $app = require 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();`) and run it with `timeout 60 php file.php`.
- XAMPP MySQL can stop on its own; on "connection refused" at port 3306 start it again from the XAMPP control panel, or run `C:\xampp\mysql\bin\mysqld.exe --defaults-file=C:\xampp\mysql\bin\my.ini --standalone` (data stays intact).
- Slow shell commands get moved to the background automatically; read their output file instead of chaining sleeps.
- AOS pattern for a new page: add `data-reveal` (or `left`/`right`/`zoom`/`fade`) to headers, cards and CTA; never to the first screen (banner/hero); split very tall blocks into pieces; test with a Puppeteer scroll-through (all revealed, no box moved, no overflow), reduced-motion and a blocked-script run.

## 10. Test status (last full run)

146 PHP tests passing with GD enabled (4 skipped without GD) · 10 Node tests passing · real-Chrome E2E scripts (kept outside the repo) for drafts, scroll reveal (timing, layout shift, reduced motion, failsafe, phone), marquee hover, admin page geometry/overflow scans.

## 11. Next steps / backlog

- **AOS rollout, page by page:** Home ✅ → Our Story ✅ → Verticals/Brand/Category ✅ → Product ✅ → Insights/Careers/Contact (still to do, plus Compare, Enquiry list, Search, Application resources, Privacy). Use `data-reveal`; only fade things that already move.
- Before launch (owner will do last): replace dummy data and phone numbers (`config/contact.php`), real footer links/Terms page, WhatsApp number in Site Settings, production `.env`, GD on the server, cron for `schedule:run`, run `filament:cache-components` and `migrate`, legal review of the privacy text, change the admin password, compress remaining heavy images (`images:optimize`).
- Ideas not started: 2FA for admins, per-record "History" tab on edit pages, contact-message/application assignment, translation of the public site.

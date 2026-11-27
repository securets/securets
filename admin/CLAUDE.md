# CLAUDE.md — SecureTS Admin

Instructions for Claude Code in `admin/`. Start Claude Code from this folder (`D:\SecureTS\securets\admin`) so this file loads.

Precedence: the owner's message in chat > this file > `AGENTS.md` > defaults.

@../AGENTS.md
@../HANDOFF.md

## Working rules from the owner (always apply)

1. **Edit only inside `admin/`.** Everything outside it (`/web`, `/mobile`, `/src`, root files including `AGENTS.md` and `HANDOFF.md`) is read-only. Read it for context, never change it. Record handoff notes in this file instead of `HANDOFF.md`.
2. **Web admin only.** Work in `admin/web`. Don't change `admin/mobile` unless the owner asks.
3. **Understand first, build only on "go".** Restate the instruction, ask about anything unclear, give a short recap, and wait for an explicit go or confirm. Answering a question is not permission to start.
4. Ask in plain language. When offering options, show a small concrete example of each.
5. Never touch the shared Supabase data (migrations, writes, test data) without the owner's OK. Test on local SQLite or Postgres instead.

## Approved exception to AGENTS.md

Requirement 4 below (theme and personalization) was requested by the owner. It adds a light theme, accent colors, and a compact density to `dashboard.blade.php`. This overrides AGENTS.md UI hard rule 1 ("no light-mode variants / new colors") for the admin console only. Keep the default (dark + blue) matching the palette.

## Owner's requirements and status

| # | Requirement | Status |
|---|---|---|
| 1 | OTP login with expiration | Built. Password, then a 6-digit emailed code. 5 min expiry, 5 tries, 60 s resend wait. Admin email moves `user@admin.com` → `simoneoddy@gmail.com` (password unchanged). |
| 2 | Server-side tables | Built. Every admin table: 20 rows/page, unlimited pages; filter = "All" + column list, then a second dropdown with that column's values; plus search. |
| 3 | Dashboard statistics | Built. Subscription sales per tier: subscribers, share, est. monthly revenue, last-6-months table. |
| 4 | Theme and personalization | Built. Per admin: dark/light/system, 5 accents, comfortable/compact, start page. |
| 5 | Purchase history | Built. Admin-only Purchase History tab (month, tier, price, status, started, expiration). "View history" on each user opens it filtered to that user. |
| 7 | Printable proof of purchase | Built (2026-11-25). "Receipt" on each Purchase History row opens `/admin/purchases/{id}/receipt`, a light print page; the browser's Print → Save as PDF makes the file (no PDF library, by owner's choice). Shows date/time, plan, price, status, buyer name/email/ID, and card brand + last 4 + expiry read live from Stripe (never stored). Needs `STRIPE_SECRET` in `admin/web/.env`. Card path untested: no paid purchase and no key yet. |
| 8 | Scrollable filter dropdown | Built (2026-11-25). The second filter dropdown on every table is now a custom picker: search box + list capped at ~8 rows that scrolls. Lists ≤200 values filter in the browser; longer lists search the server (`?q=`). Purchase History's User filter lists every user, with `0` for users without purchases. Tested with 30 mock users in the browser; not yet in a logged-in session. |
| 6 | Mautic product marketing | **On hold.** Waiting for the owner's team to choose one shared server or a local install per PC. No Docker (must stay friendly to collaborators). Mautic 6 needs PHP 8.1–8.3 + MySQL/MariaDB, so it can't use this project's PHP 8.5 or Supabase. Planned location: `admin/mautic/`. |

Subscription tiers (mirrored from `/web/config/stripe.php` into `admin/web/config/admin.php` → `plans`): Free $0 (3 integrations), Pro $5 (10), Ultra $20 (20), Ultra Plus $50 (unlimited). Revenue is estimated from these prices because `subscriptions` stores no amount. Trials are not counted as revenue.

Supabase `subscriptions` has only: `id, user_id, plan, stripe_customer_id, stripe_subscription_id, stripe_price_id, status, ends_at, created_at, updated_at`. It has **no `stripe_status` or `trial_ends_at`**, although the `/web` migration does. Admin code uses `status` only; a trial is `status = 'trialing'`. Check real Supabase columns before using any column from the `/web` migrations.

## Owner to-do before the new features run

- [ ] `admin/web/.env`: set `ADMIN_EMAILS=simoneoddy@gmail.com` (as of 2026-11-25 still empty, so nobody can log in). Gmail SMTP `MAIL_*` lines are done (App Password, no spaces). Never read, print, or write `.env` values yourself.
- [x] `php artisan migrate` ran on Supabase (2026-11-25): `admin_login_otps` exists, admin email is now `simoneoddy@gmail.com`.
- [ ] Rebuild assets: `node node_modules\vite\bin\vite.js build` from `admin/web` (or run `composer dev` for the Vite dev server).
- [x] Dashboard summary and all 5 tables verified read-only against Supabase (2026-11-25), after removing `stripe_status`/`trial_ends_at`.
- [ ] A real Gmail OTP send and full browser login are not verified yet.

## admin/web map (new pieces marked ★)

- `routes/admin/admin.php`: all admin routes.
  - ★ OTP: `GET/POST /admin/login/verify`, `POST /admin/login/resend`.
  - ★ API: `GET /admin/api/summary`, `GET /admin/api/tables/{table}`, `GET /admin/api/tables/{table}/values`, `POST /admin/api/preferences`.
- `app/Http/Controllers/Admin/AdminAuthController.php`: `login()` checks the password with `Auth::validate()` (no session yet), ★ then sends the code. `verifyOtp()` signs in. Pending state is in session key `admin_otp_pending`.
- ★ `app/Admin/AdminOtp.php`: issues, emails and verifies codes. Stores an HMAC (`app.key`) hash, never the code. A new code cancels older ones.
- ★ `app/Admin/AdminTables.php`: table definitions `users`, `sessions`, `devices`, `audit`, `purchases`.
  - Each table is a base query of aliased columns wrapped as subquery `t`. Filter, search, count and paging are generic on top of it.
  - Add a table by adding a definition and a JS renderer.
  - Column `field` = filter/value-list column; `search` = searchable columns; `label_for` = display label; optional `values` = closure returning a query of `value` + `total` (custom value list, e.g. all users).
- ★ `app/Admin/PurchaseReceipt.php` + `AdminReceiptController` + `views/admin/receipt.blade.php`: printable receipt. Response is `Cache-Control: no-store`. Stripe errors are logged by exception class only.
- ★ `app/Admin/SubscriptionStats.php`: sales summary. ★ `app/Admin/AdminPreferences.php`: JSON in `system_controls` key `admin.preferences.{user_id}`.
- ★ `app/Http/Controllers/Admin/AdminTableController.php`: validates query params, returns JSON.
- `AdminDashboardController::overview()` returns the **full legacy payload used by `admin/mobile`. Don't change its shape.** The web dashboard uses `summary()` instead.
- `dashboard.blade.php` (root of `admin/web`): the single-page console.
  - Tabs: dashboard, users, purchases, sessions, recovery, audit, status, personalize.
  - Theme is set by `data-theme` / `data-accent` / `data-density` on `<html>`.
- `resources/js/app.js`: tab switching, `ServerTable` class (toolbar, pager, `filterBy()`), renderers per table, summary, preferences.
- `resources/css/app.css`: all colors come from `--ui-*` variables; light, accent and compact override them. Tailwind v4 `@theme inline` maps tokens to those variables.
- ★ Views: `resources/views/admin/otp.blade.php`, `resources/views/admin/emails/otp.blade.php`.
- ★ Migrations: `2026_11_25_000001_create_admin_login_otps_table.php`, `2026_11_25_000002_move_admin_account_to_gmail.php` (reversible, skips if the target email exists).

## Gotchas

- PHP is **8.5** (see `bootstrap/php85-carbon-compat.php`). `vendor/` and `node_modules/` are junctions to `/web`. Never run `composer install/update` or `npm install` here.
- Remote tools can't write `.env`. Give the owner the exact lines to paste.
- Collaborators without the Gmail inbox can set `MAIL_MAILER=log` locally and read the code in `storage/logs/laravel.log`.
- The admin mobile app (`POST /admin/api/login`) still signs in with password only. OTP is web-only for now.
- `admin/web` has no Pest tests yet (AGENTS.md requires them). The next test work should cover OTP (valid, wrong, expired, locked, resend wait), table filtering, and preferences, on SQLite, never Supabase.
- `notifications.blade.php` / `settings.blade.php` still use the legacy `public/admin-assets/admin.css` and don't follow the theme.

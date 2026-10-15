# Got a ticket? – Project status

Handover notes for the next working session. Last updated: 2026-10-15, end of day (passwords, own admin, mail theme, error pages, SEO/OG, favicon).

## Starting a new session

1. Open Claude Code in this folder (`~/Jamon.digital/Webroot/gotaticket.ch`) so Laravel Boost's MCP tools are available.
2. Read this file, then `CLAUDE.md`.
3. Check the app at https://gotaticket.ch.test (Herd). Run `composer run dev` for Vite + queue worker.
4. Check "Open points" below. Check for unpushed commits with `git log origin/main..main`.
5. Deploy with `./deploy.sh` after changes (the user has been fine with deploying each finished change; pushing to GitHub only when asked).

## What it is

A fan-run platform for FC Winterthur supporters: people who can't attend a home game pass their seat (season ticket or single ticket) on to fans looking for one. **No payments on the platform** – it only connects people; the ticket (PDF / wallet / club portal transfer) is handed over directly between the two fans. Rule of thumb on the platform: free or at face value.

Motivation for season ticket holders: they must attend at least 70% of home games to be eligible for next season's season ticket, so a passed-on seat that gets scanned still counts. No-shows therefore hurt the giver – the reliability record exists for this reason.

Not affiliated with the club (stated in the footer). Deliberately no club crest/logo on the site. Long-term idea: hand over to the club (`ticketboerse.fcwinterthur.ch`) once there is a track record.

- Domain: `gotaticket.ch` (locally `https://gotaticket.ch.test` via Herd, folder `~/Jamon.digital/Webroot/gotaticket.ch`)
- Repo: https://github.com/kimiyuch/gotaticket (branch `main`)
- Footer credit: "Mit ♥ gemacht von kimiyu.ch"
- Staging/production for now: https://gotaticket.kimiyu.ch (Hostpoint, see "Deployment")

## Stack

Laravel 13, Livewire 4 (single-file page components in `resources/views/pages`), Laravel Fortify (headless auth, our own views), Tailwind 4, Vite, Alpine (bundled with Livewire), Pest 5, SQLite, database queue, `laravel-notification-channels/webpush` (VAPID keys in `.env`), Phosphor icons (`codeat3/blade-phosphor-icons`, decorative by default via `config/blade-phosphor-icons.php`), Figtree self-hosted (`@fontsource-variable/figtree`), Laravel Boost (kept on purpose).

## Conventions

- **Tabs everywhere** (`.editorconfig`), except YAML and Markdown.
- PHP formatting: **PHP-CS-Fixer** (`.php-cs-fixer.dist.php`) via `composer format` / `composer format:check`. **Pint was removed** – it cannot indent with tabs; do not reinstall it.
- `public/build` **is committed**: run `npm run build` before committing frontend changes (we build locally and deploy the result).
- UI copy: standard German with Swiss German touches ("Ich han eis", "Ich bruuch eis", "Ich gang!", "Ich chan doch nöd"), Swiss spelling (ss, no ß). No emojis in UI.
- Spacing scale: 4/8/12/16/24/32/48/64 px (Tailwind 1,2,3,4,6,8,12,16) – documented in `resources/css/app.css`. One deliberate exception: `sm:mt-28` above "So funktioniert's" on home (`mt-16` on mobile).
- Control heights: buttons/inputs 40px, small buttons 32px, badges 24px.
- Brand colours (Tailwind theme): `red` #d10a10, `red-dark` #b5000d, `ink` #120a01, `paper` #f6f4f0, `line` #e5e3df.
- Commit after each change; push and deploy only when asked.
- Translations: `lang/de/*.php` (auth, passwords, validation, pagination; picked up as fallback for `de_CH`) and `lang/de_CH.json` (JSON strings do not fall back, hence the full locale name).

## Domain model

- `sectors` (code, name, kind standing/seated, sort, is_active). Seeded: A Sitzplatz Estrade, B Stehplatz Bierkurve, C Stehplatz Osttribüne, D2 Stehplatz Eulachkurve, Sitzplatz Haupttribüne, Stehplatz Sirupkurve, Stehplatz Klubhaus. **Codes for the last three are unknown.** Away sector excluded on purpose.
- `fixtures` (opponent, kickoff_at, competition). Seeded: home games until the winter break 2026 (Luzern cup 17.10., Nyonnais 23.10., Carouge 7.11., Rapperswil-Jona 27.11., Kriens 12.12.). Spring 2027 not published yet – add via `/admin/matches`.
- `offers` (fixture, giver, sector, seats 1–4, note, status open/taken/withdrawn).
- `claims` (offer, seeker, seats, status requested → accepted → sent → received, or declined/cancelled; outcome attended/no_show/ticket_invalid after kick-off).
- `sector_user` = sectors a seeker follows (for notifications). Users: email + password (Fortify), email must be verified before offering or requesting (`verified` middleware on `/ich-han-eis`, check in the request action and `RequestSeats`), name, phone (optional), wanted_seats, notify_by_mail/push, is_admin, blocked_at. Accounts created by the old magic link have no password and use "Passwort vergessen".
- Reliability record (`User::reliability()`) is derived from claims.
- Domain logic lives in `app/Actions/Claims/*` and `app/Actions/Offers/NotifyMatchingSeekers.php`; notifications in `app/Notifications` (base `FanNotification`: push + mail, queued).

## Pages

- `/` home – hero (eyebrow, headline, two buttons) + "So funktioniert's" for both roles.
- `/ich-bruuch-eis` free seats – one card per game (tinted header with red date, competition badge), all sectors by default, toggle "Nur meine Sektoren anzeigen". "Ich gang! Anfragen" opens a request dialog (summary, seat picker if >1 free, commitment note, "Bestätigen").
- `/ich-han-eis` offer a seat – game list, sector tiles, seats 1–4, note.
- `/meine-tickets` dashboard – own requests + own offers with the full handover flow (accept/decline, contact reveal, ticket sent/received, cancel, attendance/invalid-ticket reports after kick-off).
- `/profil` (4th tab "Profil"; `/einstellungen` redirects here) – name, email (change triggers re-verification), phone, sectors, wanted seats, push toggle (PWA, `public/sw.js`, `resources/js/push.js`), email toggle, password change/set, admin link for admins, logout.
- Auth (Fortify routes, views in `resources/views/auth`, own layout `layouts/auth.blade.php`: centered white card on a subtle neutral gray, no header/logo, left-aligned heading + one sentence): `/login`, `/register`, `/forgot-password`, `/reset-password/{token}`, `/email/verify`. Login/logout responses and blocked-user check in `FortifyServiceProvider`. Blocked fans get logged out on their next request (`LogoutBlockedUsers` middleware).
- `/admin` own Livewire pages (gate `admin` = `is_admin` and not blocked; sub nav `x-admin-nav`): Übersicht (stats, next matches), Matches (list, create/edit, delete only without offers), Sektoren (list, create/edit, active toggle), Fans (search, filter, reliability counts, block/unblock, admin toggle – never on the own account), Übergaben (all claims, filter by status and match). Reached via "Admin" in the desktop nav and a button on the profile page.

UI building blocks: `x-button` (primary/soft/neutral/plain, md/sm), `x-fixture`, `x-badge`, `x-dialog` (+ global `x-confirm-dialog` via `$store.confirm.ask()`), toasts via `$this->dispatch('toast', …)` / `session('toast')` / `$store.toasts.push()`, `x-form.*` (input, select, radio, toggle, choice), `x-contact`, `x-page-header`, `x-nav-link` (desktop, red underline), `x-tab-link` (mobile bottom tab bar).

## Local setup / testing

- Tests: `php artisan test --compact` (65 passing).
- Visual checks without the browser preview: headless Chrome (`--screenshot`) for guest pages; logged-in pages via a small Playwright script outside the repo (log in through the form with a test user).
- Queue: notifications are queued – run `composer run dev` (includes queue listener) or `php artisan queue:work`.
- Test users (admins, passwords in git-ignored `database/seeders/test-users.local.json`): patrick@, alex@, pippo@, marcel@example.com. `migrate:fresh --seed` recreates them if the file exists.

## Deployment

Same method as `joey.web`: `./deploy.sh` (zsh, from the Mac, own SSH key) refuses uncommitted changes, runs the tests, exports `HEAD` via `git archive`, runs `composer install --no-dev` in a temp folder, rsyncs to `pumutuxu@pumutuxu.myhostpoint.ch:~/www/gotaticket.kimiyu.ch`, then runs `migrate --force`, `optimize` and `queue:restart` on the server.

- Server PHP: always `/usr/local/php84/bin/php` (the default `php` on the CLI is 8.3).
- Bootstrap caches are not protected: stale ones (e.g. a removed service provider) would break `artisan`; `optimize` rebuilds them after every upload.
- Never touched by deploys: `.env`, `public/.htaccess` (first line `Use php-fpm php84`, managed by the Hostpoint panel; the rest is a copy of ours – update it by hand if ours changes), `storage/`, `database/database.sqlite`.
- Server `.env`: production, debug off, own `APP_KEY` and VAPID keys, mail via Hostpoint SMTP (`asmtp.mail.hostpoint.ch`, sender `bot@kimiyu.ch`, credentials copied from wallaby), `SESSION_SECURE_COOKIE=true`.
- Queue: crontab entry every minute, `queue:work --stop-when-empty` (php84). No `schedule:run` entry yet – add one (append only) when the first scheduled task exists.
- Mail: app notifications (`FanNotification`) are queued; Laravel's `VerifyEmail` and `ResetPassword` are sent synchronously in the request. Failed jobs land in `failed_jobs` without any alert.
- Server access: the Hostpoint user hosts many other live sites. Only write inside `~/www/gotaticket.kimiyu.ch`, crontab append-only, never print other sites' secrets, ask before anything destructive.
- Seeded on the server: sectors and fixtures only (no test users). Admins: register normally, then an existing admin promotes them under `/admin/fans` (the very first admin via tinker on the server).

## Decisions made (and why) – don't undo without asking

- Name "Got a ticket?" = the question fans actually ask at the stadium. Domain gotaticket.ch.
- No payments, no ticket files stored on the platform – it only matches and reveals contact details after the giver accepts.
- Request flow: seeker requests ("Ich gang!") → giver accepts → contacts revealed → giver marks "Ticket gesendet" → seeker confirms "Ticket erhalten". Requests are binding (no-shows hurt season ticket holders), hence the confirmation dialog with the commitment note on every request, even for one seat.
- Free seats show **all sectors by default**; the "Nur meine Sektoren anzeigen" toggle (right-aligned, small) filters. "Sektoren" links to settings.
- Header: light (no black bar), horizontal hand-drawn ticket logo without wordmark (`resources/svg/ticket-horizontal.svg`; original square `ticket.svg` is used for app icons). Desktop nav = plain text links, active = subtle red underline. Mobile = bottom tab bar, active = red icon only (no bar).
- Buttons: one solid red primary per page; secondary actions inside cards are **neutral grey buttons, never text-only/ghost**. Dialogs have an X (top right) plus Abbrechen; confirm label in the request dialog is "Bestätigen".
- Game display (`x-fixture`): red date line on top, opponent bold, competition as a grey badge top right (on "Meine Tickets" the slot shows seats/sector instead). Free-seats cards have a light red tinted header (`bg-red/4`), not solid red.
- Icons: Phosphor, light for large, regular for small; only the footer heart is filled.
- Confirmations as toasts (top right desktop, full-width flush top on mobile); browser `confirm()` replaced by the dialog component.
- Home hero: text full width + two buttons, no crest/club logo (trademark / would look official), no divider between sections.
- Accessibility pass done: pointer cursor on all clickables (global rule in `app.css`), ARIA labels with context on repeated buttons, skip link, switch roles, live regions.
- Favicon: Phosphor "heart-straight" (fill), solid brand red (`public/favicon.svg` + PNG fallback). App icons (`apple-touch-icon`, `icons/*`) are still the ticket on black.
- Auth: Fortify instead of Breeze (Breeze would bring its own views/components; frozen package). Magic link dropped. No social logins. UI built with the ui.sh design skill guidelines.
- No Filament: own admin so it matches the app and has no heavy dependency.
- SEO / link previews: `x-seo` in the shared head (title, description per page, canonical, Open Graph, X card). Only `/` and `/ich-bruuch-eis` are indexable, everything else (auth, profile, dashboard, admin, errors) is `noindex`. `robots.txt` and `sitemap.xml` are routes (they follow `APP_URL`). The preview image `public/images/og.png` is static: edit `resources/views/og-image.blade.php` (served at `/og-image` only locally), run `npm run build`, then `./scripts/og-image.sh` (same method as wallaby).
- Admin area is invisible to non-admins: `/admin/*` answers 404 for guests and fans (`EnsureUserIsAdmin`, also registered as Livewire persistent middleware). Error pages are own, German, standalone (`resources/views/errors`).
- Emails: Laravel markdown mail with own theme `resources/views/vendor/mail/html/themes/gotaticket.css` (config `mail.markdown.theme`), logo `public/mail/logo.png`, self-hosted Figtree `public/mail/figtree.woff2` (no Google Fonts), left-aligned red button, German footer with link to the profile. German texts for the verification/reset mails in `AppServiceProvider`.

## Open points / next steps

- GitHub is up to date as of 2026-10-15 evening (pushed after the session).
- Live state (2026-10-15): users table was reset locally and live; admin is m@marceli.to. A second, unverified fan account (marcel.stadelmann@gmail.com) was registered live afterwards. Locally, `migrate:fresh --seed` brings the test users back (and wipes the admin).
- Suggested, not yet done: queue the verification/reset mails too (so login/register never wait on SMTP), and send a real test mail to check delivery and the new mail design in an inbox.
- App icons (`apple-touch-icon`, `icons/*`, PWA manifest) are still the ticket on black; favicon is now the red heart. Ask whether to switch them.
- Missing sector codes for Haupttribüne, Sirupkurve, Klubhaus (editable under `/admin/sektoren`).
- Web push not yet verified on a real device (iOS only as installed PWA).
- Match-day reminder (needs a `schedule:run` cron line); smoother "pass on a received ticket" flow.
- Maybe: alert on failed queue jobs.
- Later: own sender address (e.g. hallo@gotaticket.ch) and the gotaticket.ch domain (then update `APP_URL`, regenerate nothing – sitemap/robots/canonical follow `APP_URL`).

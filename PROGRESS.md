# Got a ticket? – Project status

Handover notes for the next working session. Last updated: 2026-10-15.

## What it is

A fan-run platform for FC Winterthur supporters: people who can't attend a home game pass their seat (season ticket or single ticket) on to fans looking for one. **No payments on the platform** – it only connects people; the ticket (PDF / wallet / club portal transfer) is handed over directly between the two fans. Rule of thumb on the platform: free or at face value.

Motivation for season ticket holders: they must attend at least 70% of home games to be eligible for next season's season ticket, so a passed-on seat that gets scanned still counts. No-shows therefore hurt the giver – the reliability record exists for this reason.

Not affiliated with the club (stated in the footer). Deliberately no club crest/logo on the site. Long-term idea: hand over to the club (`ticketboerse.fcwinterthur.ch`) once there is a track record.

- Domain: `gotaticket.ch` (locally `https://gotaticket.ch.test` via Herd, folder `~/Jamon.digital/Webroot/gotaticket.ch`)
- Repo: https://github.com/kimiyuch/gotaticket (branch `main`)
- Footer credit: "Mit ♥ gemacht von kimiyu.ch"

## Stack

Laravel 13, Livewire 4 (single-file page components in `resources/views/pages`), Filament 5 admin (`/admin`), Tailwind 4, Vite, Alpine (bundled with Livewire), Pest 5, SQLite, database queue, `laravel-notification-channels/webpush` (VAPID keys in `.env`), Phosphor icons (`codeat3/blade-phosphor-icons`, decorative by default via `config/blade-phosphor-icons.php`), Figtree self-hosted (`@fontsource-variable/figtree`), Laravel Boost (kept on purpose).

## Conventions

- **Tabs everywhere** (`.editorconfig`), except YAML and Markdown.
- PHP formatting: **PHP-CS-Fixer** (`.php-cs-fixer.dist.php`) via `composer format` / `composer format:check`. **Pint was removed** – it cannot indent with tabs; do not reinstall it.
- `public/build` **is committed**: run `npm run build` before committing frontend changes (we build locally and deploy the result).
- UI copy: standard German with Swiss German touches ("Ich han eis", "Ich bruuch eis", "Ich gang!", "Ich chan doch nöd"), Swiss spelling (ss, no ß). No emojis in UI.
- Spacing scale: 4/8/12/16/24/32/48/64 px (Tailwind 1,2,3,4,6,8,12,16) – documented in `resources/css/app.css`. One deliberate exception: `mt-28` above "So funktioniert's" on home.
- Control heights: buttons/inputs 40px, small buttons 32px, badges 24px.
- Brand colours (Tailwind theme): `red` #d10a10, `red-dark` #b5000d, `ink` #120a01, `paper` #f6f4f0, `line` #e5e3df.
- Commit after each change; push only when asked.

## Domain model

- `sectors` (code, name, kind standing/seated, sort, is_active). Seeded: A Sitzplatz Estrade, B Stehplatz Bierkurve, C Stehplatz Osttribüne, D2 Stehplatz Eulachkurve, Sitzplatz Haupttribüne, Stehplatz Sirupkurve, Stehplatz Klubhaus. **Codes for the last three are unknown.** Away sector excluded on purpose.
- `fixtures` (opponent, kickoff_at, competition). Seeded: home games until the winter break 2026 (Luzern cup 17.10., Nyonnais 23.10., Carouge 7.11., Rapperswil-Jona 27.11., Kriens 12.12.). Spring 2027 not published yet – add via Filament.
- `offers` (fixture, giver, sector, seats 1–4, note, status open/taken/withdrawn).
- `claims` (offer, seeker, seats, status requested → accepted → sent → received, or declined/cancelled; outcome attended/no_show/ticket_invalid after kick-off).
- `sector_user` = sectors a seeker follows (for notifications). Users: magic-link login (no passwords except admins), name, phone (optional), wanted_seats, notify_by_mail/push, is_admin, blocked_at.
- Reliability record (`User::reliability()`) is derived from claims.
- Domain logic lives in `app/Actions/Claims/*` and `app/Actions/Offers/NotifyMatchingSeekers.php`; notifications in `app/Notifications` (base `FanNotification`: push + mail, queued).

## Pages

- `/` home – hero (eyebrow, headline, two buttons) + "So funktioniert's" for both roles.
- `/ich-bruuch-eis` free seats – one card per game (tinted header with red date, competition badge), all sectors by default, toggle "Nur meine Sektoren anzeigen". "Ich gang! Anfragen" opens a request dialog (summary, seat picker if >1 free, commitment note, "Bestätigen").
- `/ich-han-eis` offer a seat – game list, sector tiles, seats 1–4, note.
- `/meine-tickets` dashboard – own requests + own offers with the full handover flow (accept/decline, contact reveal, ticket sent/received, cancel, attendance/invalid-ticket reports after kick-off).
- `/einstellungen` – sectors, wanted seats, push toggle (PWA, `public/sw.js`, `resources/js/push.js`), email toggle, name/phone, logout.
- `/login` magic link (locally the page shows a direct login link).
- `/admin` Filament: fixtures, sectors.

UI building blocks: `x-button` (primary/soft/neutral/plain, md/sm), `x-fixture`, `x-badge`, `x-dialog` (+ global `x-confirm-dialog` via `$store.confirm.ask()`), toasts via `$this->dispatch('toast', …)` / `session('toast')` / `$store.toasts.push()`, `x-form.*`, `x-contact`, `x-page-header`, `x-nav-link` (desktop, red underline), `x-tab-link` (mobile bottom tab bar).

## Local setup / testing

- Tests: `php artisan test --compact` (37 passing).
- Queue: notifications are queued – run `composer run dev` (includes queue listener) or `php artisan queue:work`.
- Test users (admins, passwords in git-ignored `database/seeders/test-users.local.json`): patrick@, alex@, pippo@, marcel@example.com. `migrate:fresh --seed` recreates them if the file exists.

## Open points / next steps

- The user has **two bigger issues** to raise in the next session (not yet known).
- Missing sector codes for Haupttribüne, Sirupkurve, Klubhaus.
- Web push not yet verified on a real device (needs a deployed HTTPS host; iOS only as installed PWA).
- Admin: user list / blocking, overview of offers and handovers.
- Match-day reminder; smoother "pass on a received ticket" flow.
- Deployment: hosting, real mail delivery (Postmark or Infomaniak), production `.env` incl. VAPID keys.

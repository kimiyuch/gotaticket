# Got a ticket?

Fan-to-fan ticket passing for FC Winterthur home games. Fans who can't make it pass their seat (season or single ticket) on to fans looking for one. No payments: the platform only connects people, the ticket is handed over directly, for free or at face value. Not affiliated with the club.

Live at [gotaticket.kimiyu.ch](https://gotaticket.kimiyu.ch) (later gotaticket.ch).

Laravel 13, Livewire 4 (single-file page components), Fortify (auth with our own views), Tailwind CSS 4, SQLite, database queue, web push. Current status, decisions and open points are in [`PROGRESS.md`](PROGRESS.md).

## Local development

The site runs in [Herd](https://herd.laravel.com) at `https://gotaticket.ch.test`.

```sh
composer install
npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed   # sectors, home games, an admin; test users if database/seeders/test-users.local.json exists
php artisan webpush:vapid    # keys for push notifications
composer run dev             # Vite and the queue worker
```

Mail is written to the log locally (`MAIL_MAILER=log`).

## Tests and formatting

```sh
php artisan test --compact
composer format              # PHP-CS-Fixer with tabs, not Pint
```

## Deploying

```sh
npm run build                # if you changed the design; commit public/build
./deploy.sh
```

`deploy.sh` only deploys committed code. It runs the tests, exports `HEAD`, installs production dependencies and syncs it to Hostpoint over SSH with your own key. Then it runs the migrations, rebuilds the caches and restarts the queue. The server's `.env`, `public/.htaccess` (the PHP version is set there through the Hostpoint panel), `storage/` and the SQLite database are left alone. A cron job on the server works through the queue every minute.

## Where things are

| | |
|---|---|
| `resources/views/pages` | The pages (Livewire): free seats, offer a seat, my tickets, profile, admin |
| `resources/views/auth` | Login, registration, password reset, email confirmation |
| `resources/views/layouts` | `app` (header, tab bar), `auth` (centered card); the error pages have their own layout in `resources/views/errors` |
| `app/Actions/Claims`, `app/Actions/Offers` | The handover flow: request, accept, send, receive, cancel, report |
| `app/Notifications` | Push and mail notifications to fans |
| `resources/views/vendor/mail` | Mail layout and theme (`html/themes/gotaticket.css`) |
| `resources/css/app.css` | Theme colors (`red`, `ink`, `paper`), Figtree, spacing scale |
| `lang/de`, `lang/de_CH.json` | German texts for auth, validation and mail |
| `resources/views/og-image.blade.php` | The link preview image. Run `./scripts/og-image.sh` (needs Herd and `npm run build`) to render it to `public/images/og.png` |

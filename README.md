# DevOS

Personal admin workspace for tasks, projects, credentials, and settings. Built with **Laravel 13**, **Blade**, **Tailwind CSS 4**, and **Vite**.

Single admin user (`DevOS_admin`). Auth uses a custom session (`admin_logged_in`, `admin_id`), not Laravel's default `auth()` guard.

## Quick start

```bash
composer install
cp .env.example .env   # if .env does not exist
php artisan key:generate
php artisan migrate --force
npm install
npm run build
php artisan serve
```

Open [http://127.0.0.1:8000/secure-access](http://127.0.0.1:8000/secure-access)

### Dev mode (server + queue + logs + Vite)

```bash
composer dev
```

## Environment variables

| Variable | Purpose |
|----------|---------|
| `APP_NAME` | App label (e.g. `DevOS`) — used in emails and UI |
| `APP_URL` | Base URL for links |
| `DB_*` | Database connection (SQLite or MySQL) |
| `MAIL_*` | SMTP settings — **secrets stay in `.env` only** |
| `MAIL_FROM_ADDRESS` | Default sender / fallback forgot-password email |
| `HIGH_SECURITY_PASSWORD` | Master password for High Security Locker |

SMTP credentials are read from `.env`. Admin Settings shows read-only mail config and lets you test the SMTP connection.

## Documentation

| File | Contents |
|------|----------|
| [docs/PAGES.md](docs/PAGES.md) | All routes and pages (table auto-synced) |
| [docs/FLOWS.md](docs/FLOWS.md) | Auth, lockout, forgot password, SMTP flows |
| [config/docs.php](config/docs.php) | Route descriptions for the sync command |

### Keep docs updated

After adding routes, controllers, or changing auth/SMTP logic:

```bash
php artisan docs:sync
```

This updates:

- `docs/PAGES.md` — route table from `routes/web.php` + `config/docs.php`
- `docs/FLOWS.md` — lockout and OTP constants from code
- Sync timestamp in this README

Check-only mode (useful in CI):

```bash
php artisan docs:sync --check
```

<!-- docs:sync-time:start -->
Docs last synced: **Aug 18, 2026 10:52 AM UTC** — run `php artisan docs:sync` after route or flow changes.
<!-- docs:sync-time:end -->

## Tests

```bash
php artisan test
# or
composer test
```

## Project structure

```
app/Http/Controllers/   # Page and API controllers
app/Services/           # SMTP, forgot password, doc sync
resources/views/        # Blade templates
routes/web.php          # All web routes
config/docs.php         # Route metadata for documentation
docs/                   # Project documentation
```

## Security notes

- Never commit `.env` or real SMTP passwords
- Login attempts are logged in `login_logs`
- Failed login lockout: see [docs/FLOWS.md](docs/FLOWS.md)
- Forgot-password OTP is stored hashed in `password_reset_otps`

## License

MIT

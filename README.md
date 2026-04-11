# Opterius Mail

**Modern open-source webmail.** A fast, responsive replacement for Roundcube that works with any IMAP/SMTP server.

Ships as the default webmail in [Opterius Panel](https://github.com/opterius/panel) but runs independently with Dovecot, Postfix, Courier, Zimbra, Exchange, or any standard mail server.

---

## Features

- **IMAP authentication** — users log in with their email address and password; no separate user database
- **Responsive UI** — three-panel on desktop, adapts to tablet and mobile
- **Dark mode** — toggle in settings, persisted per user
- **Rich compose** — TipTap editor with formatting, attachments, CC/BCC, signatures, draft autosave
- **Threaded conversations** — grouped by In-Reply-To / References headers
- **Full folder support** — create, rename, delete, drag messages between folders
- **IMAP SEARCH** — search by subject, sender, recipient, body, date range
- **Address book** — contacts with autocomplete in compose, vCard import/export
- **Two-factor authentication** — TOTP (Google Authenticator, Authy, 1Password) for both webmail users and admins
- **Plugin system** — extend without forking: calendar, Sieve filters, PGP, white-label, and more
- **Swappable templates** — ship your own UI without touching application code
- **Admin mode** — optional `/admin` panel to manage domains, accounts, aliases, DKIM, spam, queue, and logs

## Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13 / PHP 8.3+ |
| Frontend | Alpine.js 3 + Tailwind CSS 4 |
| Editor | TipTap 2 (ProseMirror) |
| Build | Vite |
| Icons | Heroicons (SVG inline) |
| Auth | Custom IMAP guard — no Jetstream/Fortify/Breeze |
| Database | MySQL / MariaDB (default) or SQLite |

---

## Requirements

- PHP 8.3+ with `ext-pdo`, `ext-mbstring`, `ext-openssl`, `ext-sockets`
- MySQL 8+ / MariaDB 10.4+ (or SQLite for local dev)
- Any IMAP server (Dovecot recommended)
- Any SMTP server (Postfix recommended)
- Node.js 20+ and npm (for frontend assets)

---

## Quick start

```bash
git clone https://github.com/opterius/mail
cd mail
composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env` — set your database and IMAP/SMTP server:

```env
DB_CONNECTION=mysql
DB_DATABASE=opterius_mail
DB_USERNAME=root
DB_PASSWORD=

IMAP_HOST=127.0.0.1
IMAP_PORT=143
IMAP_ENCRYPTION=        # leave empty for plain, or: ssl, starttls

SMTP_HOST=127.0.0.1
SMTP_PORT=587
SMTP_ENCRYPTION=tls
```

Run migrations and build assets:

```bash
php artisan migrate
npm install && npm run build
```

Start the server:

```bash
php artisan serve --port=8090
```

Open `http://localhost:8090` and log in with your IMAP credentials.

---

## Admin mode

Admin mode enables a full mail server management panel at `/admin` — create accounts, manage domains, aliases, DKIM keys, and more.

Enable it in `.env`:

```env
MAIL_ADMIN=true
MAIL_BACKEND=passwd-file   # or: mysql
```

On first run, navigate to `http://localhost:8090/admin` to create the first admin account.

### Supported backends

| Backend | Description |
|---|---|
| `passwd-file` | Writes to `/etc/dovecot/users` and Postfix virtual maps — same format as Opterius Panel |
| `mysql` | Inserts into `virtual_users` / `virtual_domains` / `virtual_aliases` tables |

> When running alongside **Opterius Panel**, leave `MAIL_ADMIN=false`. The Panel manages accounts through its own agent — two systems writing to the same Dovecot config causes conflicts.

---

## Configuration

| Variable | Default | Description |
|---|---|---|
| `IMAP_HOST` | `127.0.0.1` | IMAP server hostname |
| `IMAP_PORT` | `143` | IMAP port (993 for SSL) |
| `IMAP_ENCRYPTION` | _(empty)_ | `ssl`, `starttls`, or empty for plain |
| `IMAP_VALIDATE_CERT` | `false` | Validate TLS certificate |
| `SMTP_HOST` | `127.0.0.1` | SMTP server hostname |
| `SMTP_PORT` | `587` | SMTP port |
| `SMTP_ENCRYPTION` | `tls` | `tls`, `ssl`, or empty |
| `MAIL_ADMIN` | `false` | Enable the admin panel |
| `MAIL_BACKEND` | `passwd-file` | Account backend when admin mode is on |
| `MAIL_UI_TEMPLATE` | `default` | Active UI template |
| `MAIL_SSO_SECRET` | _(empty)_ | Shared secret for Opterius Panel SSO |

---

## Template system

Opterius Mail supports swappable UI templates. The active template is set via `MAIL_UI_TEMPLATE` — no code changes required.

Templates live in `resources/views/templates/{name}/`. To create a custom template, copy the `default` folder, rename it, and set the env var. Each template must implement the [template contract](resources/views/templates/default/) (the set of view files controllers expect).

Template-specific assets go in `public/templates/{name}/css/app.css` and `js/app.js`.

---

## Plugin system

Plugins extend Opterius Mail without modifying core code. They are Composer packages or local folders under `plugins/`.

Each plugin has a `plugin.json` manifest declaring hooks, and a PHP class extending `App\Plugins\Plugin`. Core hook points include: `sidebar`, `toolbar`, `compose_toolbar`, `message_header`, `message_footer`, `settings`, `head`, `login_footer`.

Planned first-party plugins: `mail-calendar`, `mail-white-label`, `mail-sieve`, `mail-pgp`, `mail-notes`, `mail-contacts-sync`.

---

## Development

```bash
# Start all dev processes (server + queue + logs + Vite HMR)
composer dev

# Run tests
composer test

# Lint
./vendor/bin/pint
```

---

## Opterius Panel integration (SSO)

When installed alongside Opterius Panel, users can click "Open Webmail" and be auto-logged in via a signed token:

1. Panel generates: `HMAC-SHA256(email + timestamp, MAIL_SSO_SECRET)`
2. Panel redirects to `http://localhost:8090/sso?email=…&ts=…&sig=…`
3. Opterius Mail verifies the signature and creates a session
4. Token expires after 30 seconds (replay protection)

Set the same `MAIL_SSO_SECRET` in both `.env` files.

---

## License

Opterius Mail is open-source software licensed under the [GNU Affero General Public License v3.0](LICENSE).

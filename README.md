# Opterius Mail

**Modern open-source webmail.** A fast, lightweight replacement for Roundcube that works with any IMAP/SMTP server.

Ships as the default webmail in [Opterius Panel](https://github.com/opterius/panel) but runs independently with Dovecot, Postfix, Courier, Zimbra, Exchange, or any standard mail server.

---

## Features

- **IMAP authentication** — users log in with their real email address and password; no separate user database required
- **Read, compose, reply, forward** — full message threading support with quoted replies
- **Folder navigation** — all IMAP folders listed with unread counts
- **Dark mode** — toggle in user settings, stored per account
- **Signatures** — per-user plain-text signature appended to compose / reply / forward
- **Address book** — contact management with autocomplete in compose (To/CC/BCC)
- **IMAP search** — fast search by subject and sender via `UID SEARCH`
- **Keyboard shortcuts** — `c` compose, `/` search, `r` reply, `f` forward, `u` back, `Del` delete
- **Swappable templates** — ship your own UI without touching application code; includes a built-in `minimal` template as an example
- **Admin panel** — `/admin` with dashboard stats, mail groups with sending limits, outbound and login logs, and global settings
- **Sending limits** — per-group or global hourly / daily / weekly / monthly caps and per-message recipient limits, enforced before SMTP dispatch
- **Brute-force protection** — login rate-limited to 5 attempts per minute (webmail and admin)

## Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13 / PHP 8.3+ |
| Frontend | Alpine.js 3 + Tailwind CSS (CDN) |
| IMAP | Custom pure-PHP socket client (no ext-imap) |
| Auth | Custom IMAP guard — no Jetstream / Fortify / Breeze |
| Database | SQLite (default) or MySQL / MariaDB |

---

## Requirements

- PHP 8.3+ with extensions: `pdo`, `pdo_sqlite` (or `pdo_mysql`), `mbstring`, `openssl`, `sockets`
- Any IMAP server (Dovecot, Courier, Exchange, Gmail IMAP, …)
- Any SMTP server on the same credentials as IMAP

No Node.js, no build step — all frontend assets are loaded from CDN.

---

## Installation

### 1. Clone and install

```bash
git clone https://github.com/opterius/mail
cd mail
composer install --no-dev --optimize-autoloader
```

### 2. Configure

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and set your IMAP/SMTP server:

```env
APP_URL=https://mail.example.com
APP_DEBUG=false

IMAP_HOST=mail.example.com
IMAP_PORT=993
IMAP_ENCRYPTION=ssl
IMAP_VALIDATE_CERT=true

SMTP_HOST=mail.example.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
```

For SQLite (zero-config, great for small deployments), no DB changes are needed.
For MySQL, also set:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=opterius_mail
DB_USERNAME=root
DB_PASSWORD=secret
```

### 3. Migrate

```bash
php artisan migrate --force
```

### 4. Web server

#### Using the built-in server (dev / testing)

```bash
php artisan serve --port=8090
```

#### Using Nginx + PHP-FPM (production)

```nginx
server {
    listen 443 ssl;
    server_name mail.example.com;

    root /var/www/opterius-mail/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Set correct permissions:

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### 5. First login

Open your browser and log in with any email address and password that are valid on your IMAP server.

---

## Admin panel

The admin panel lives at `/admin`. Navigate there to create the first admin account (setup page appears automatically when no admin exists).

### Standalone mode (default — `MAIL_ADMIN=false`)

Gives you:
- Dashboard with send / login statistics
- Mail groups — assign users to groups and set sending limits
- Logs — outbound mail and login history (with IPs)
- Settings — global defaults for sending limits

### Panel-integrated mode (`MAIL_ADMIN=true`)

Adds the full mail server management sidebar:
- Domains, Accounts, Aliases, Autoresponders
- Spam rules, DKIM management
- Mail queue, server logs

> When running alongside **Opterius Panel**, keep `MAIL_ADMIN=false`. The Panel manages server accounts through its own agent — two systems writing to the same config causes conflicts.

---

## Mail groups

Groups let you apply different sending limits to different sets of users.

1. Go to **Admin → Groups** → create a group (e.g. "Standard", "Premium")
2. Set limits: emails per hour / day / week / month, max recipients per message
3. Assign users: **Admin → Logs**, find the user's email, set their group in User Settings

Users with no group get the global defaults from **Admin → Settings**.

---

## Template system

The active template is set via `MAIL_UI_TEMPLATE` in `.env`:

```env
MAIL_UI_TEMPLATE=minimal
```

Templates live in `resources/views/templates/{name}/`. Only `layouts/app.blade.php` is required; all other views fall back to the `default` template automatically.

See [_docs/template-system.md](resources/views/templates/default/) for the full template contract.

---

## Configuration reference

| Variable | Default | Description |
|---|---|---|
| `IMAP_HOST` | `127.0.0.1` | IMAP server hostname |
| `IMAP_PORT` | `993` | IMAP port |
| `IMAP_ENCRYPTION` | `ssl` | `ssl`, `tls`, or `none` |
| `IMAP_VALIDATE_CERT` | `false` | Validate TLS certificate |
| `IMAP_TIMEOUT` | `15` | Connection timeout (seconds) |
| `SMTP_HOST` | `127.0.0.1` | SMTP server hostname |
| `SMTP_PORT` | `587` | SMTP port |
| `SMTP_ENCRYPTION` | `tls` | `tls`, `ssl`, or `none` |
| `SMTP_VALIDATE_CERT` | `false` | Validate TLS certificate |
| `MAIL_ADMIN` | `false` | Enable admin panel panel-integrated mode |
| `MAIL_UI_TEMPLATE` | `default` | Active UI template name |

---

## Upgrading

```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan view:clear
php artisan config:clear
```

---

## License

Opterius Mail is open-source software licensed under the [GNU Affero General Public License v3.0](LICENSE).

The AGPL requires that if you run a modified version of Opterius Mail as a network service, you must make the source code of your modified version available to your users.

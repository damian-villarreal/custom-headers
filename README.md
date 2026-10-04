# Custom Headers

Configure custom HTTP response headers in WordPress using flexible rules based on request context and URI patterns.

**Author:** [Damian Villarreal](https://github.com/damian-villarreal)  
**Version:** 1.1.0  
**Requires WordPress:** 6.0+  
**Requires PHP:** 7.4+  
**Text domain:** `custom-headers`

## Description

Custom Headers sends HTTP headers on WordPress' `send_headers` hook. Define one or more rules; when a rule matches the current request, its headers are sent to the client.

Use it for security headers (`X-Frame-Options`, `Content-Security-Policy`), CORS, cache control, and any other custom response headers—without editing server config or theme files.

## Features

- **Rule-based configuration** — Match by context and optional URI pattern
- **Multiple headers per rule** — Add as many name/value pairs as needed
- **Multiple matching rules** — All matching rules apply on the same request
- **Drag-and-drop ordering** — Rules are evaluated top to bottom
- **Dedicated admin UI** — Own menu under **Custom Headers** (not buried in Settings)
- **Built-in Help page** — Documentation inside the WordPress admin
- **Security-first** — Input sanitization, hop-by-hop headers blocked
- **Translation-ready** — All strings use the `custom-headers` text domain

## Installation

1. Download or clone this repository.
2. Copy the `custom-headers` folder into `wp-content/plugins/`.
3. Activate **Custom Headers** from **Plugins** in the WordPress admin.
4. Go to **Custom Headers → Rules** to configure your headers.

### Via Git

```bash
cd wp-content/plugins
git clone https://github.com/damian-villarreal/custom-headers.git
```

Then activate the plugin in WordPress.

## Usage

1. Open **Custom Headers → Rules**.
2. Click **+ Add rule**.
3. Set **Context** and optionally a **Request URI** pattern.
4. Add one or more **Header** / **Value** pairs.
5. Click **Save Changes**.

For full documentation, open **Custom Headers → Help** in the admin.

## Contexts

| Context | Applies to |
|---------|------------|
| **All requests** | Front-end, admin, and login |
| **Front-end** | Public site pages |
| **Admin** | WordPress dashboard (`is_admin()`) |
| **Login** | `wp-login.php` |

## URI patterns

Patterns match the request path only (no domain). Leave empty to match any URI within the selected context.

| Pattern | Matches |
|---------|---------|
| `/contact/` | Exact path only |
| `/news/*` | Path and child paths |
| `/wp-json/my-plugin/v1/*` | Custom REST API routes |

Wildcards (`*`) are supported. Whitespace in patterns is not allowed.

## Examples

### X-Frame-Options on admin and login

| Context | URI | Header | Value |
|---------|-----|--------|-------|
| Admin | *(empty)* | `X-Frame-Options` | `DENY` |
| Login | *(empty)* | `X-Frame-Options` | `DENY` |

### CORS on a REST API namespace

| Context | URI | Header | Value |
|---------|-----|--------|-------|
| All | `/wp-json/my-plugin/v1/*` | `Access-Control-Allow-Origin` | `https://example.com` |

### No cache on checkout

| Context | URI | Header | Value |
|---------|-----|--------|-------|
| Front-end | `/checkout` | `Cache-Control` | `no-store, no-cache, must-revalidate, max-age=0` |

## Page cache

This plugin sends headers when WordPress runs PHP. If a page-cache plugin serves a stored HTML response, PHP may not execute and your headers will not apply on that request.

After purging cache, the next request typically runs WordPress and applies your rules. For paths served from cache, purge the cache or exclude those URLs from page cache to see changes immediately.

## Blocked headers

For security, hop-by-hop headers cannot be configured, including:

`Connection`, `Keep-Alive`, `Transfer-Encoding`, `Content-Length`, `Host`, and related proxy/upgrade headers.

## Project structure

```
custom-headers/
├── custom-headers.php          # Plugin bootstrap
├── uninstall.php               # Removes options on uninstall
├── assets/
│   ├── css/admin.css
│   └── js/admin.js
├── includes/
│   ├── class-custom-headers-admin.php
│   ├── class-custom-headers-matcher.php
│   ├── class-custom-headers-plugin.php
│   ├── class-custom-headers-sender.php
│   ├── class-custom-headers-settings.php
│   └── views/help-page.php
└── languages/                  # Translation files (.po / .mo)
```

## Development

Requirements:

- PHP 7.4+ with `declare(strict_types=1);`
- WordPress 6.0+

The plugin follows WordPress coding standards: hooks only, sanitized input, escaped output, and `$wpdb`-safe patterns where applicable.

### Translations

Place translation files in the `languages/` directory:

```
languages/custom-headers-es_ES.po
languages/custom-headers-es_ES.mo
```

## Uninstall

When the plugin is deleted from WordPress, `uninstall.php` removes the `custom_headers_settings` option from the database.

## License

GPL-2.0-or-later — same as WordPress.

## Contributing

Issues and pull requests are welcome on GitHub.

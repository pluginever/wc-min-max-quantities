# AGENTS.md — Min Max Quantities for WooCommerce

## Overview

WooCommerce plugin (`wc-min-max-quantities`) that enforces min/max quantity, step, and price limits on products and cart. Free + Pro model. Namespace: `WooCommerceMinMaxQuantities`. Text domain: `wc-min-max-quantities`.

## Architecture

- **Entry point:** `wc-min-max-quantities.php` → loads Composer autoloader + `includes/functions.php`, then boots `Plugin::create()`.
- **Plugin class** (`includes/Plugin.php`) extends `B8\Plugin\App` (vendored `byteever/plugin` framework). Services are registered on `woocommerce_loaded` via `$this->make()`.
- **Service layer:** `Cart` (frontend quantity enforcement + WC Store API block compatibility), `Installer` (versioned migrations), `Admin\Admin` (settings page + meta boxes).
- **Settings** extend `ByteKit\Admin\Settings` (vendored `byteever/bytekit-settings`). Settings page is under WooCommerce menu at `admin.php?page=wc-min-max-quantities`.
- **Functions file** (`includes/functions.php`) contains all `wcmmq_*` helper functions — these are the primary API surface used across the codebase.

## Key Data Model

Limits cascade: **product-level overrides → global options**. Resolved in `wcmmq_get_product_limits()`.

| Scope | Storage | Key examples |
|-------|---------|-------------|
| Global product | `wp_options` | `wcmmq_min_qty`, `wcmmq_max_qty`, `wcmmq_step` |
| Global cart | `wp_options` | `wcmmq_min_cart_qty`, `wcmmq_max_cart_qty`, `wcmmq_min_cart_total`, `wcmmq_max_cart_total` |
| Per-product | `wp_postmeta` | `_wcmmq_enable` (override toggle), `_wcmmq_disable` (exclude), `_wcmmq_min_qty`, `_wcmmq_max_qty`, `_wcmmq_step` |

Product limits are cached with `wp_cache_set("wcmmq-{$product_id}-{$variation_id}", ...)`.

## Conventions

- **PHP namespace:** `WooCommerceMinMaxQuantities` (PSR-4 mapped to `includes/`). Admin classes under `WooCommerceMinMaxQuantities\Admin`.
- **Prefix:** Options/meta use `wcmmq_` / `_wcmmq_`. Hooks use `wc_min_max_quantities_` (e.g., `wc_min_max_quantities_product_limits`).
- **Static vs instance methods:** Cart/Admin classes mix `__CLASS__` static callbacks and `$this` instance callbacks. Block-compatible Store API filters use `$this`; classic WC hooks use `__CLASS__`.
- **PHPCS:** Uses `ByteEver-Default` ruleset (see `phpcs.xml`). Run: `composer phpcs` / `composer phpcbf`.
- **JS/CSS:** WordPress coding standards via `@wordpress/scripts` + `@byteever/scripts`. ESLint extends `@wordpress/eslint-plugin/recommended-with-formatting`.
- **Indentation:** Tabs for PHP/JSON, spaces for YAML/MD (see `.editorconfig`).
- **i18n:** All user-facing strings must use `__()` / `esc_html__()` with domain `wc-min-max-quantities`.

## Build & Dev Commands

```bash
npm run start          # Watch mode (webpack dev)
npm run build          # Production build + makepot
composer phpcs         # Lint PHP
composer phpcbf        # Auto-fix PHP
composer strauss       # Vendor prefixing (namespace isolation)
```

## Vendor Prefixing (Strauss)

Dependencies `byteever/plugin` and `byteever/bytekit-settings` are namespace-prefixed into `WooCommerceMinMaxQuantities\` via Strauss. After `composer install/update`, Strauss runs automatically. The text domain in vendored files is replaced to `wc-min-max-quantities`.

## Adding New Functionality

- New service classes go in `includes/` and are registered in `Plugin::register_services()` with `$this->make(ClassName::class)`.
- Admin-only classes go in `includes/Admin/` and are guarded by `is_admin()`.
- Product limit logic should use `wcmmq_get_product_limits()` and `wcmmq_is_product_excluded()` — never read meta directly.
- Cart validation notices use `wcmmq_add_cart_notice()` which tags notices with `source => 'wcmmq'`.
- WC Cart/Checkout Block compatibility is handled via `woocommerce_store_api_product_quantity_*` filters in `Cart.php`.

## Migration System

`Installer::$updates` maps version strings to method names. Migrations run automatically when `db_version < current_version`. Add new migrations by adding entries to the `$updates` array in `Installer.php`.


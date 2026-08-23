# AGENTS.md — Min Max Quantities for WooCommerce

## Overview

WooCommerce plugin (`wc-min-max-quantities`) that enforces min/max quantity, step, and price limits on products and cart. Free + Pro model. Namespace: `PluginEver\MinMaxQuantities`. Text domain: `wc-min-max-quantities`.

## Architecture

- **Entry point:** `wc-min-max-quantities.php` → loads Composer autoloader + `includes/functions.php`, then `Plugin::create( __FILE__, $data )` + explicit `bootstrap()`. Activation/deactivation callbacks are wired via `on_activation`/`on_deactivation`.
- **Plugin class** (`includes/Plugin.php`) extends `B8\App` (vendored `byteever/plugin`, Strauss-prefixed). Components are declared in `protected array $components` and booted on `woocommerce_loaded`.
- **Component layer:** `Cart` (frontend quantity enforcement + WC Store API block compatibility), `Installer` (versioned migrations + activation/deactivation lifecycle), `Admin\Admin` (admin shell; children: `Menu`, `Settings`, `MetaBoxes`, `Actions`, `Notices`, `Feedback`).
- **Settings** extend `B8\SettingsUI`. Settings page is under WooCommerce menu at `admin.php?page=wc-min-max-quantities`. Fields render/save through WooCommerce's own `woocommerce_admin_fields()` / `woocommerce_update_options()`. Legacy filters/actions (`wc_min_max_quantities_settings_tabs`, `wc_min_max_quantities_settings_{tab}`) are bridged inside `Admin\Settings`.
- **Templates** live in `templates/` and are rendered via `$this->app->template->render('admin.pro-panel', $data)` / `->view('admin.notices.feedback', $data)` (dot paths map to directories).
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

- **PHP namespace:** `PluginEver\MinMaxQuantities` (PSR-4 mapped to `includes/`). Admin classes under `PluginEver\MinMaxQuantities\Admin`.
- **Prefix:** Options/meta use `wcmmq_` / `_wcmmq_`. Hooks use `wc_min_max_quantities_` (e.g., `wc_min_max_quantities_product_limits`).
- **Static vs instance methods:** Cart/Admin classes mix `__CLASS__` static callbacks and `$this` instance callbacks. Block-compatible Store API filters use `$this`; classic WC hooks use `__CLASS__`.
- **PHPCS:** Uses `ByteEver-Default` ruleset (see `phpcs.xml`). Run: `composer phpcs` / `composer phpcbf`.
- **PHPStan:** Level 8 (`phpstan.neon.dist`, baseline in `phpstan-baseline.neon` for legacy debt). Run: `composer phpstan`.
- **JS/CSS:** Plain SCSS compiled with `sass`; no JS build.
- **Indentation:** Tabs for PHP/JSON, spaces for YAML/MD (see `.editorconfig`).
- **i18n:** All user-facing strings must use `__()` / `esc_html__()` with domain `wc-min-max-quantities`.

## Build & Dev Commands

```bash
npm run start          # Watch SCSS (sass --watch)
npm run build          # Compile assets/src → assets/build (compressed)
composer phpcs         # Lint PHP
composer phpcbf        # Auto-fix PHP
composer phpstan       # Static analysis
composer strauss       # Vendor prefixing (namespace isolation)
```

## Vendor Prefixing (Strauss)

The `byteever/plugin` dependency is namespace-prefixed into `PluginEver\MinMaxQuantities\B8\` via Strauss (`vendor/byteever/plugin/src` is rewritten in place). After `composer install/update`, Strauss runs automatically. If the prefix looks doubled (e.g. `...\WooCommerceMinMaxQuantities\...`), delete `vendor/byteever/plugin` and run `composer update byteever/plugin` to force a pristine re-install before Strauss runs.

## Adding New Functionality

- New components go in `includes/` and are added to `$components` in `includes/Plugin.php`; admin-only components go under `includes/Admin/` and into `Admin\Admin::$components`.
- Product limit logic should use `wcmmq_get_product_limits()` and `wcmmq_is_product_excluded()` — never read meta directly.
- Cart validation notices use `wcmmq_add_cart_notice()` which tags notices with `source => 'wcmmq'`.
- WC Cart/Checkout Block compatibility is handled via `woocommerce_store_api_product_quantity_*` filters in `Cart.php`.

## Migration System

`Installer::$updates` maps version strings to method names. Migrations run automatically when `db_version < current_version`. Add new migrations by adding entries to the `$updates` array in `Installer.php`.


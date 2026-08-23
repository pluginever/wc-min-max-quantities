# Migrating a PluginEver plugin to the `byteever/plugin` framework

A reusable, **plugin-agnostic** playbook for moving any of our old plugins off the legacy framework
(`byteever/bytekit-plugin` + `byteever/bytekit-settings`) onto the current **`byteever/plugin`** framework.

It is written so a developer **or an AI agent** can execute it on any plugin by substituting the plugin's names
into the placeholders below. `wc-wholesale-manager` is referenced only as a **worked example**.

> **Golden rule — refactor, don't rewrite.** Keep every line of business logic (the WooCommerce behaviour)
> intact. Only the *framework plumbing* changes: base classes, the bootstrap, the component lifecycle, the
> settings layer, the template layer, and the namespace. If you start rewriting a feature's algorithm, stop —
> that's out of scope.

---

## 0. The one reference you need

**`wc-starter-plugin/`** is the canonical, up-to-date example of this framework. Open it side-by-side and copy
its shapes. Whenever this guide and the starter disagree, the starter wins.

Secondary references: `wc-serial-numbers-pro/` (main-file bootstrap), `wc-min-max-quantities/` (a free WC
plugin with the same kind of data keys).

---

## 1. Placeholders — fill these in once per plugin

Decide these values up front and substitute them everywhere in this guide. Example column = Wholesale Manager.

| Placeholder | Meaning | Example |
|---|---|---|
| `{SLUG}` | Plugin folder / main file slug | `wc-wholesale-manager` |
| `{NS}` | New PSR-4 root namespace (drop "WooCommerce", use `PluginEver\…`) | `PluginEver\WholesaleManager` |
| `{OLD_NS}` | Existing namespace being replaced | `WooCommerceWholesaleManager` |
| `{HELPER}` | Global accessor function (keep the existing one) | `wc_wholesale_manager()` |
| `{OPT}` | Option prefix (keep existing) | `wcwm` |
| `{HOOK}` | Hook prefix (keep existing) | `wc_wholesale_manager` |
| `{CONST}` | Constant prefix (keep existing) | `WCWM` |
| `{TD}` | Text domain (keep existing) | `wc-wholesale-manager` |
| `{NAME}` | Human-readable plugin name | `Wholesale Manager` |

**Never change** `{OPT}`, `{HOOK}`, `{TD}`, option keys (`{OPT}_*`), meta keys, taxonomies, or the
`{HELPER}()` function name — third parties and the database depend on them. **Do** change `{OLD_NS}` → `{NS}`.

---

## 2. Decide what to keep and drop

| Concern | Decision |
|---|---|
| `byteever/bytekit-plugin` (old App base) | **Drop** → replaced by `byteever/plugin`. |
| `byteever/bytekit-settings` (old settings page) | **Drop** → replaced by the framework's `B8\SettingsUI` + the `settings` filter. |
| `byteever/licensing` | Add **only for paid plugins**. Free plugins skip it. |
| `pluginever/framework-data` | Add only if the plugin actually used it; otherwise skip. |
| `includes/Admin/views/*` | **Move** into `templates/` and render through the Template service. |

---

## 3. Framework concepts (read `vendor/byteever/plugin/src/` after install)

Understand these before editing — they drive every decision below.

- **`App` (`B8\App`, abstract).** `App::create($file, $data)` builds the singleton (runs `configure()` +
  `preflight()`) but **does not** call `bootstrap()`. `bootstrap()` is `abstract public` — you implement it and
  the main file calls it explicitly. `configure()` derives config (`slug`, `version`, `option_prefix`,
  `hook_prefix`, `text_domain`, `namespace`, …) and stores any custom `$data` keys. `preflight()` binds all
  services and registers the framework styles/scripts (`b8-components`, `b8-layout`, `b8-settings`) on `init`.
- **Service properties** on the app (use these instead of the old helpers):
  `options scripts template notices flash logger cache queue request router fs settings`,
  plus config props `version file slug namespace option_prefix hook_prefix` and any custom `$data` key
  (`$app->docs_url`, `$app->get('name')`, …).
- **`Component` (`B8\Component`).** Base class for everything the plugin boots. Constructor receives `App $app`
  (stored as `protected $this->app`). Override:
  - `register(): void` — wire your hooks here (the old constructor body goes here);
  - `autoload(): bool` — gate by context, e.g. `return is_admin();` (default `true`);
  - `public array $components` — declare child components to boot under this one.
- **`App::boot(array $components)`.** For each class: `make()` it (constructor-injects `App`), and if it is a
  `Component` whose `autoload()` is true → call `register()` and recursively `boot()` its `$components`.
- **`make()`/`get()` share instances.** A class resolved via `make()` is cached; `$this->app->get(SomeComponent::class)`
  later returns the **same booted instance** (e.g. `Admin` reading `Menu::get_screen_ids()`).
- **`SettingsUI` (`B8\SettingsUI`, a `Component`).** Renders a tabbed settings page from the `settings` service
  and saves via `admin-post`. Before each tab's field form it fires `do_action("{HOOK}_settings_tab_{tab}")`
  (your hook for fully custom tab content). Override `render_fields()`/`save_fields()` to delegate to
  WooCommerce; override `render_sidebar()` for a sidebar; nav extras via the `{HOOK}_settings_nav_extras` action.
- **`Services\Settings` (`B8\Services\Settings`).** Holds tab/field definitions, fed through the `{HOOK}_settings`
  filter. The app auto-binds `settings` to `{NS}\Services\Settings` *if that class extends the base*, else to the
  base. **You usually don't need a `Services\Settings` class** — feed fields by hooking the `settings` filter
  from your `Admin\Settings`.
- **`Services\Template`.** `render($name, $data)` / `view($name, $data)`. Dot notation → files under
  `templates/`: `'admin.notices.upgrade'` → `templates/admin/notices/upgrade.php`. `$data` is `extract()`ed into
  the template. `view()` is the non-overridable variant; `render()` is filterable.
- **`Services\Options`.** `get($key, $default)`, `update($key, $v)`, `get_db_version()`,
  `update_db_version($v, $force)`. Keys are prefixed with `{OPT}_`.
- **`Services\Notices`.** `add(['message' => …, 'notice_id' => …, 'type' => …, 'dismissible' => …])`. `message`
  may be a string **or a template file path** (`$this->app->templates_path('admin/notices/x.php')`).
- **Hookable helpers** (on the app): `do_action($name, …)` / `apply_filters($name, $v, …)` /
  `on_action($name, $cb)` / `on_filter($name, $cb)` — all auto-prefixed with `{HOOK}`. `add_action`/`add_filter`
  remain raw (unprefixed). `on_activation($cb)` / `on_deactivation($cb)` register the WP (de)activation hooks and
  resolve `[Class::class, 'method']` through the container.

---

## 4. Target file layout (every plugin ends up like this)

```
{SLUG}.php                        # Plugin::create() + on_activation/on_deactivation + WC compat + bootstrap()
uninstall.php                     # @package only (delete-data logic unchanged)
composer.json                     # byteever/plugin (+licensing if paid); strauss prefix {NS}\
includes/
├── Plugin.php                    # extends B8\App; $components + boot(); is_pro_active()
├── functions.php                 # global {HELPER}()  (thin)
├── Installer.php                 # extends B8\Component
├── <Feature classes>.php         # extends B8\Component (one per subsystem)
├── Admin/
│   ├── Admin.php                 # extends B8\Component; autoload()=is_admin; $components=[Menu,Settings,Notices,…]
│   ├── Menu.php                  # extends B8\Component; registers pages from the admin_pages filter
│   ├── Settings.php              # extends B8\SettingsUI
│   └── Notices.php               # extends B8\Component
└── Emails/…                      # WC_Email subclasses (namespace only)
templates/
├── admin/…                       # everything that used to live in includes/**/views/
└── …                             # emails, my-account, etc. (unchanged)
```

No `includes/**/views/` and (usually) no `Services/Settings.php`.

---

## 5. Step-by-step

### Step 0 — Inventory the old plugin

```bash
cd wp-content/plugins/{SLUG}
grep -rn "^namespace " includes/                               # confirm {OLD_NS}
grep -rn "extends .*ByteKit" includes/                          # old base classes in use
grep -rn "__construct" includes/                                # constructors → become register()
ls includes/Admin/views includes/**/views 2>/dev/null           # views to relocate
# Old base-class calls to translate (see §6 mapping):
grep -rn "get_dir_path\|get_dir_url\|get_assets_url\|get_assets_path\|->get_version\|->get_file\|->get_basename\|is_plugin_active\|->services->\|->get_db_version\|update_db_version\|get_template_path\|->log(\|->get_name()\|get_docs_url\|get_review_url\|get_support_url\|->set( " includes/
```

### Step 1 — `composer.json`

Swap the framework, set PSR-4 + strauss to `{NS}\`:

```json
{
  "repositories": [
    { "type": "vcs", "url": "git@github.com:byteever/plugin.git" }
  ],
  "require": { "php": ">=7.4", "byteever/plugin": "dev-trunk" },
  "autoload": {
    "psr-4": { "{NS_ESCAPED}\\": "includes/" },
    "files": [ "includes/functions.php" ]
  },
  "extra": {
    "strauss": {
      "target_directory": "vendor",
      "namespace_prefix": "{NS_ESCAPED}\\",
      "classmap_prefix": "{NS_CLASSMAP}__",
      "packages": [ "byteever/plugin" ]
    }
  }
}
```

`{NS_ESCAPED}` = the namespace with `\\` (e.g. `PluginEver\\WholesaleManager`); `{NS_CLASSMAP}` = underscores
(e.g. `PluginEver_WholesaleManager`). Keep the strauss `post-install-cmd`/`post-update-cmd` scripts. **Paid
plugins** also add `byteever/licensing` to `require`, its repo, and the strauss `packages`. After install,
strauss rewrites the framework to `namespace {NS}\B8\…`, so inside a `namespace {NS};` file you reference base
classes relatively: `B8\App`, `B8\Component`, `B8\SettingsUI`.

### Step 2 — Main file `{SLUG}.php`

Header `@package` → `{NS}`. Body (model: `wc-serial-numbers-pro.php` / `wc-starter-plugin.php`):

```php
use {NS}\Installer;
use {NS}\Plugin;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/functions.php';

$data = array(
	'version'       => '{X.Y.Z}',
	'name'          => '{NAME}',
	'option_prefix' => '{OPT}',
	'hook_prefix'   => '{HOOK}',
	'settings_url'  => admin_url( 'admin.php?page={SLUG}' ),
	'support_url'   => 'https://pluginever.com/support/',
	'docs_url'      => 'https://pluginever.com/docs/{SLUG}/',
	'upgrade_url'   => 'https://pluginever.com/plugins/{pro-landing}/',
	'pro_basename'  => '{SLUG}-pro/{SLUG}-pro.php',
	'review_url'    => 'https://wordpress.org/support/plugin/{SLUG}/reviews/#new-post',
);

Plugin::create( __FILE__, $data );

{HELPER}()->on_activation( array( Installer::class, 'install' ) );
{HELPER}()->on_deactivation( array( Installer::class, 'deactivate' ) );

add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
	}
} );

// Paid plugins only — gate on the license before booting:
// use {NS}\B8\Licensing\License;
// if ( ! {HELPER}()->make( License::class )->is_valid() ) { return; }

{HELPER}()->bootstrap();
```

`Plugin::create()` does **not** auto-bootstrap, so the last line is required, and `Plugin::bootstrap()` must be
**public**. `on_activation([Installer::class,'install'])` works with a **non-static** `install()` — the
container resolves the pair and injects `App`.

### Step 3 — `includes/Plugin.php`

```php
namespace {NS};

defined( 'ABSPATH' ) || exit;

final class Plugin extends B8\App {

	protected array $components = array(
		Installer::class,
		// … one entry per top-level subsystem …
		Admin\Admin::class,
	);

	public function bootstrap(): void {
		define( '{CONST}_VERSION', $this->version );
		define( '{CONST}_FILE', $this->file );
		define( '{CONST}_PATH', $this->plugin_path() );
		define( '{CONST}_URL', $this->plugin_url() );
		define( '{CONST}_ASSETS_URL', $this->assets_url() );
		define( '{CONST}_ASSETS_PATH', $this->assets_path() );

		add_action( 'woocommerce_loaded', array( $this, 'woocommerce_loaded' ), 0 );
		add_filter( 'plugin_action_links_' . $this->basename(), array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
	}

	public function woocommerce_loaded(): void {
		$this->boot( $this->components );
		do_action( '{HOOK}_init' ); // back-compat if third parties used it
		$this->do_action( 'loaded' );
	}

	public function is_pro_active(): bool {
		return ! empty( $this->pro_basename ) && $this->plugin_active( (string) $this->pro_basename );
	}

	// plugin_action_links(): Settings link + Go Pro (when ! is_pro_active)
	// plugin_row_meta(): Docs + Support links
}
```

For **non-WooCommerce** plugins, boot on `plugins_loaded` (or directly in `bootstrap()`) instead of
`woocommerce_loaded`.

### Step 4 — Convert every service class to `Component`

For each class the plugin used to instantiate (old `$this->set(Class::class)` / `new Class()`):

1. `class X {` → `class X extends B8\Component {` (root namespace) or
   `use {NS}\B8\Component; class X extends Component {` (sub-namespaces like `Admin\*`).
2. **Delete** the custom `__construct()`.
3. Move the old constructor's `add_action`/`add_filter` calls verbatim into `public function register(): void {}`.
4. Add `public function autoload(): bool { return is_admin(); }` for admin-only components.
5. Add `public array $components = array( … );` for parents that own children.
6. Replace any `$this`/global access to the app with `$this->app->…` (services, `version`, `get('name')`, …).
   Static hook callbacks (`array( __CLASS__, 'method' )`) can stay as-is.

### Step 5 — `includes/Installer.php` (model: `wc-starter-plugin/includes/Installer.php`)

```php
class Installer extends B8\Component {
	const UPDATE_HOOK = '{HOOK}_run_update';
	protected array $updates = array();           // 'X.Y.Z' => callable

	public function register(): void {
		add_action( 'init', array( $this, 'maybe_update' ) );
		add_action( self::UPDATE_HOOK, array( $this, 'run_update' ) );
	}
	public function maybe_update(): void {
		if ( version_compare( $this->app->version, $this->app->options->get_db_version(), '>' ) ) {
			$this->install();
			if ( ! empty( $this->updates ) ) { $this->app->queue->add( self::UPDATE_HOOK ); }
		}
	}
	public function run_update(): void { /* iterate $this->updates, update_db_version() after each */ }
	public function install(): void {
		// keep any first-run setup (create pages/tables, seed data)…
		$this->app->options->update_db_version( $this->app->version, true );
		add_option( '{OPT}_install_date', current_time( 'mysql' ) );
		flush_rewrite_rules();
	}
	public function deactivate(): void { $this->app->queue->clear(); flush_rewrite_rules(); }
}
```

In the installer: `get_db_version()` → `$this->app->options->get_db_version()`; `update_db_version($v)` →
`$this->app->options->update_db_version($v, true)`; `->log()` → `$this->app->logger->info()`. **Drop the old
`Settings::instance()->save_defaults()`** — the new settings have no such method; defaults come from
`get_option('{OPT}_*', $default)` at read time (which the business logic already does).

### Step 6 — Settings (`includes/Admin/Settings.php`, the biggest change)

Replace `Settings extends ByteKit\Admin\Settings` (`get_tabs()`/`get_settings()`/`output_*`) with a
`B8\SettingsUI` subclass that **owns its page** and **renders/saves through WooCommerce** so existing `{OPT}_*`
option keys and custom field types keep working (model: `wc-starter-plugin/includes/Admin/Settings.php`):

```php
use {NS}\B8\SettingsUI;

class Settings extends SettingsUI {
	protected string $capability = 'manage_options'; // or 'manage_woocommerce'

	public function register(): void {
		$this->app->on_filter( 'admin_pages', array( $this, 'register_page' ) );
		$this->app->on_filter( 'settings',    array( $this, 'register_settings' ) );
		$this->app->on_action( 'settings_nav_extras', array( $this, 'render_nav_extras' ) ); // e.g. Docs link
		// For each fully-custom (non-field) tab:
		// $this->app->on_action( 'settings_tab_{tab}', array( $this, 'render_{tab}_tab' ) );
		// For each custom WC field type the old settings declared:
		// add_action( 'woocommerce_admin_field_{type}', array( $this, 'render_{type}' ) );
	}

	public function register_page( array $pages ): array {
		$pages[] = array( 'title' => '{NAME}', 'slug' => '{SLUG}', 'capability' => $this->capability,
		                  'callback' => array( $this, 'render' ), 'position' => 55 );
		return $pages;
	}

	public function register_settings( array $settings ): array {
		// One key per tab. Value = ['title' => …, 'fields' => [ WooCommerce field arrays, ids '{OPT}_*' ]].
		// A tab with 'fields' => array() shows no form — render its body from settings_tab_{tab}.
		return $settings;
	}

	protected function render_fields( array $fields ): void { woocommerce_admin_fields( $fields ); }
	protected function save_fields( array $fields, array $data ): bool { woocommerce_update_options( $fields ); return true; }

	protected function render_sidebar(): void { /* template->render('admin.pro-panel', …) when ! is_pro_active */ }
	public function render_nav_extras(): void { /* echo extra nav-tab anchors, e.g. Documentation */ }
}
```

Mapping the old settings → new:

- **Each old tab** in `get_tabs()` → a key in `register_settings()`. **Each field array** from
  `get_settings($tab)` moves under that tab's `fields` **unchanged** (ids stay `{OPT}_*`; `single_select_page`,
  `custom_attributes`, etc. work because WC renders/saves them).
- **`do_action('{HOOK}_settings_{tab}')`** (old custom-tab content) → **`{HOOK}_settings_tab_{tab}`** action; the
  tab is declared with empty `fields` so only the action body renders.
- **Custom WC field types** (rendered via `woocommerce_admin_field_{type}`) keep working — just re-register the
  same `add_action('woocommerce_admin_field_{type}', …)` callback in `Settings::register()`.
- **`output_premium_widget()`** → `render_sidebar()`. **`output_tabs()` extra links** → `render_nav_extras()`
  on the `settings_nav_extras` action.
- CSS `bk-*` → `b8-*`.
- Conditionally visible fields (a setting shown only when another setting holds a given value) use the
  `data-show-if` custom attribute — see §10.10 for the exact pattern and the checkbox value rules.

### Step 7 — `includes/Admin/Menu.php` (copy from `wc-starter-plugin`)

A `Component` that, on `admin_menu`, reads the `{HOOK}_admin_pages` filter and registers each page under its
parent (`woocommerce` for WC plugins), recording the returned hook as a screen ID. Other admin components read
those via `$this->app->get( Menu::class )->get_screen_ids()`.

### Step 8 — `includes/Admin/Admin.php`

Make it a `Component`: `autoload(): bool { return is_admin(); }`, `public array $components = [ Menu::class,
Settings::class, Notices::class, … ]`, and `register()` for whatever admin hooks aren't owned by a child
(product metaboxes, screen-id filter, footer text/version, asset enqueue). Settings-render methods move into
`Admin\Settings`. Replace `<?php include __DIR__ . '/views/x.php'; ?>` with
`$this->app->template->view( 'admin.x', array( … ) );`.

### Step 9 — `includes/Admin/Notices.php`

`Component`; `register()` hooks `admin_init`; each notice's `message` is a template path:
`$this->app->notices->add( array( 'message' => $this->app->templates_path( 'admin/notices/x.php' ), 'notice_id' => '…', 'dismissible' => false ) )`.
Gate pro-upsell notices on `! $this->app->is_pro_active()`.

### Step 10 — Move views → `templates/`

`git mv` (or copy + delete) every `includes/**/views/*.php` to `templates/admin/…` (mirror the sub-path). Render
each from code:

- in-form/markup partials → `$this->app->template->view( 'admin.<name>', $data )`;
- notice bodies → `notices->add(['message' => $this->app->templates_path('admin/notices/<name>.php')])`.

The Template service `extract()`s `$data` into scope, so each template reads its variables directly. A template
may keep its own `use {NS}\…;` statements and use the `{CONST}_ASSETS_URL` constant. Delete the now-empty
`includes/**/views/` directories.

### Step 11 — Namespace rename + leftovers

One global rename handles `namespace` / `use` / `@package` / strauss-prefixed FQNs:

```bash
find includes -name '*.php' -print0 | xargs -0 perl -i -pe 's/{OLD_NS}/{NS_BACKSLASH}/g'
perl -i -pe 's/{OLD_NS}/{NS_BACKSLASH}/g' uninstall.php
```

(`{NS_BACKSLASH}` = `PluginEver\\WholesaleManager` for the example — note the doubled backslash for perl.)
Then fix the few non-mechanical spots: `Plugin::instance()->get_template_path($f)` →
`{HELPER}()->templates_path($f)`; old data keys (`premium_url`/`premium_basename`) → new ones
(`upgrade_url`/`pro_basename`) and `is_pro_active()`. WC_Email subclasses under `includes/Emails/` only need the
namespace bump.

### Step 12 — `functions.php` and `uninstall.php`

`functions.php` keeps the thin `{HELPER}()` returning `Plugin::instance()` (update its `use`/`@return` to `{NS}`).
`uninstall.php`: bump `@package`; leave the `{OPT}_%` delete-data logic untouched.

---

## 6. Old → new API mapping (`$p = {HELPER}()`)

| Old (`bytekit-plugin`) | New (`B8\App`) |
|---|---|
| `extends \…\ByteKit\Plugin` | `extends B8\App` |
| `__construct()` + `define_constants/includes/init_hooks` | `public function bootstrap()` (+ a `woocommerce_loaded()` that boots components) |
| `$this->set( Class::class )` | declare in `$components` → `$this->boot()`; or `$this->make( Class::class )` |
| `$this->define('X',$v)` | `define('X',$v)` |
| `$p->get_version()` / `get_file()` | `$p->version` / `$p->file` |
| `$p->get_dir_path()` / `get_dir_url()` | `$p->plugin_path()` / `$p->plugin_url()` |
| `$p->get_assets_url()` / `get_assets_path()` | `$p->assets_url()` / `$p->assets_path()` |
| `$p->get_basename()` | `$p->basename()` |
| `$p->is_plugin_active($b)` | `$p->plugin_active($b)` (or `$p->is_pro_active()`) |
| `$p->get_db_version()` / `update_db_version($v)` | `$p->options->get_db_version()` / `update_db_version($v, true)` |
| `$p->log($m)` | `$p->logger->info($m)` |
| `$p->get_name()` | `$p->get('name')` |
| `$p->get_docs_url()` / `get_review_url()` / `get_support_url()` | `$p->docs_url` / `$p->review_url` / `$p->support_url` |
| `$p->services->add( X::instance() )` | child `Component` in `$components`, or `$p->share($instance)` |
| `$p->scripts->enqueue_style($h,$src)` | `$p->scripts->enqueue_style($h,$src,['b8-layout','b8-components'])` |
| `Plugin::instance()->get_template_path($f)` | `$p->templates_path($f)` / `$p->template->view($name,$data)` |
| `Settings extends ByteKit\Admin\Settings` + `get_tabs()/get_settings()/output_*` | `Settings extends B8\SettingsUI` + `admin_pages`/`settings` filters + `render_fields()/save_fields()` |
| `do_action('{HOOK}_settings_{tab}')` | `do_action('{HOOK}_settings_tab_{tab}')` (fired by `SettingsUI`) |
| `notices->add(['message'=>__DIR__.'/views/x.php'])` | `notices->add(['message'=>$p->templates_path('admin/notices/x.php')])` |
| CSS `bk-*` | `b8-*` |

---

## 7. Build & verify

```bash
composer install            # pulls byteever/plugin + runs strauss (prefixes vendor namespaces)
composer dump-autoload
composer phpcs               # after install registers the sniffs
```

Static gates (all must be clean):

```bash
# No old namespace / base classes / old framework left in source:
grep -rn "{OLD_NS}\|ByteKit\\\\\|bytekit" includes/ {SLUG}.php uninstall.php
# No old base-class calls left:
grep -rn "get_dir_path\|get_assets_url\|->get_version\|is_plugin_active\|->services->\|get_template_path\|->get_db_version\|Settings::instance\|/views/" includes/
# Syntax:
for f in {SLUG}.php $(find includes templates -name '*.php'); do php -l "$f" >/dev/null || echo "FAIL $f"; done
```

Class-load probe (define `ABSPATH`, require `vendor/autoload.php`, then `class_exists()` each plugin class and
assert `is_subclass_of(..., '{NS}\B8\App' | '{NS}\B8\Component' | '{NS}\B8\SettingsUI')`).

Runtime QA: activates with no fatals and runs first-run setup; the settings page shows every tab + any nav
extras + the `b8-card` sidebar; settings persist to `{OPT}_*`; custom tabs render and save; all front-end
behaviour, shortcodes, and emails work; the Plugins list shows the Settings / Go Pro / Docs / Support links.

---

## 8. Backward-compatibility checklist (must all hold)

- **Option keys** unchanged (`option_prefix => '{OPT}'`; field `id`s stay `{OPT}_*`; saved via
  `woocommerce_update_options`).
- **Hook names** unchanged (`hook_prefix => '{HOOK}'`), including any documented `{HOOK}_*` actions/filters and
  email triggers.
- **Meta keys / taxonomies / custom tables** untouched (written by business logic, not the framework).
- **`{HELPER}()`** keeps its name and signature.
- **`uninstall.php`** still deletes `{OPT}_%` gated by `{OPT}_delete_data` (or your equivalent).

A rollback to the old code leaves the database fully compatible — no data migration in either direction.

---

## 9. Suggested commit order (small, reversible)

1. `composer.json` + main file + `Plugin.php` + `functions.php` → boots with an empty `$components`.
2. Global namespace rename across `includes/**` + `uninstall.php`.
3. Convert feature classes + `Installer` to `Component`; populate `$components`.
4. `Admin\Menu` + `Admin\Admin` (Component) + `Admin\Notices`.
5. `Admin\Settings` (`SettingsUI`) + move views to `templates/` + delete `includes/**/views/`.
6. Build, verify, runtime QA.

End each commit message with:

```
Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>
```

---

## Appendix — Worked example: `wc-wholesale-manager`

| Placeholder | Value |
|---|---|
| `{SLUG}` | `wc-wholesale-manager` |
| `{NS}` | `PluginEver\WholesaleManager` |
| `{OLD_NS}` | `WooCommerceWholesaleManager` |
| `{HELPER}` | `wc_wholesale_manager()` |
| `{OPT}` / `{HOOK}` / `{CONST}` / `{TD}` | `wcwm` / `wc_wholesale_manager` / `WCWM` / `wc-wholesale-manager` |

Components booted: `Installer, Store, Roles, Emails, Frontend, Admin\Admin` (with children
`Menu, Settings, Notices`). Settings tabs: `general` + `advanced` (real fields), `emails` (custom
`wcwm_email_settings` WC field type), `roles` + `fields` (custom content via `settings_tab_roles` /
`settings_tab_fields`). Views relocated to `templates/admin/` (`discounts-table`, `role`, `edit-role`,
`pro-panel`, `notices/upgrade`, `notices/review`). It is a **free** plugin, so no licensing/framework-data.

---

## 10. Important notes for AI agents and manual migration

These notes capture real-world pitfalls encountered during migration. Read them **before** starting.

### 10.1 Do NOT strip existing doc blocks

Many AI models strip or simplify existing PHPDoc blocks during refactoring. This is wrong. The rule:

- **Every method that already has a doc block keeps it.** Do not remove `@param`, `@return`, `@since`, `@var`,
  or inline comments.
- **If a method has NO doc block, add one** following the wc-starter-plugin pattern:
  ```
  /**
   * Short description.
   *
   * @since 1.0.0
   * @param type $name Description.
   * @return type Description.
   */
  ```
- **Every file** must have a top-level `@package {NS}` doc block.
- Every class must have a class-level doc block with `@since` and `@package`.
- Translate but **never delete** existing translator comments (`/* translators: ... */`).

**Diff hygiene — do NOT mass-add doc blocks (GitHub PRs):**

- **Do NOT add a new doc block to a method/function your change does not otherwise touch.** An unchanged
  function keeps its code byte-for-byte — a missing or imperfect PHPDoc is **not** a reason to edit it.
- Add or update a doc block **only** when the function itself changed: it is newly created, its body/logic
  was modified, or its signature/return type changed.
- Never strip an existing doc block and replace it with a rewritten one on an unchanged function. Rewriting
  doc blocks across many files floods the GitHub PR diff, hides the real change, and is forbidden.
- The "add a doc block if missing" rule above applies **only** to methods you are already editing for the
  migration — it is never a license for repo-wide doc-block cleanup.

### 10.2 Settings conversion — the hardest part

AI models frequently botch the settings migration. Follow these rules precisely:

- `Settings extends ByteKit\Admin\Settings` → `Settings extends B8\SettingsUI`.
- The old `get_tabs()` array becomes **array keys** in `register_settings()`.
- The old `get_settings($tab)` array becomes the `fields` value under that tab key.
- **Field arrays must be copied verbatim** — do not rewrite field `id`s, `type`s, or option keys. The
  `{OPT}_*` prefix must survive unchanged.
- A tab with **empty `fields`** (`'fields' => array()`) renders via the `settings_tab_{tab}` action hook.
  This is how custom tab content (list tables, forms, non-WC UIs) works.
- `render_fields()` delegates to `woocommerce_admin_fields($fields)`.
- `save_fields()` delegates to `woocommerce_update_options($fields)`.
- **Do not** create a `Services/Settings` class — feed settings through the `settings` filter.
- CSS classes: `bk-*` → `b8-*`.
- **Conditional field visibility** (a field shown only when another field has a specific value) uses the
  `data-show-if` custom attribute — copy the exact pattern from §10.10. If the controlling field is a
  **checkbox**, pass `'value' => '1'` or `'value' => '0'`; for every other field type pass the input's
  exact value.

### 10.3 Assets — esbuild, not webpack

The starter plugin uses **esbuild** for JS bundling and **sass** for SCSS. Do NOT bring over webpack,
`@wordpress/scripts`, `.eslintrc.js`, or `.prettierrc.js` — they are not part of the framework.

Target `package.json`:
```json
{
  "name": "{SLUG}",
  "private": true,
  "scripts": {
    "css": "sass assets/src:assets/build",
    "js": "esbuild assets/src/*.js --bundle --outdir=assets/build",
    "start": "npm run css -- --watch & npm run js -- --watch",
    "build": "npm run css -- --style=compressed --no-source-map && npm run js -- --minify"
  },
  "devDependencies": {
    "esbuild": "^0.28.0",
    "sass": "^1.100.0"
  }
}
```

Asset directory layout (mirrors starter):
```
assets/
├── src/
│   ├── admin.js          # admin JS entry
│   ├── admin.scss        # admin SCSS entry
│   ├── frontend.js       # (optional) front-end JS
│   └── frontend.scss     # (optional) front-end SCSS
└── build/                # compiled output (git-ignored)
```

- Old `assets/src/js/` and `assets/src/css/` subdirectories → flatten into `assets/src/`.
- Build output goes to `assets/build/`.
- In `Admin\Admin::enqueue_scripts()`, reference `admin.js` / `admin.css` (no subdirectory) — the
  `scripts` service resolves them from `assets/build/`.
- Style dependencies: `array( 'b8-layout', 'b8-components' )` (replaces `bytekit-layout`, `bytekit-components`).

### 10.4 Add the Feedback component

Every migrated plugin **must** have a `Feedback` component for deactivation surveys. Copy it from
`wc-starter-plugin/includes/Admin/Feedback.php` and adapt:

- Change the class name space to `{NS}\Admin`.
- Change the AJAX action to `{HOOK}_feedback`.
- Change the nonce to `{HOOK}_feedback`.
- The feedback modal template goes in `templates/admin/notices/feedback.php`.
- Add `Feedback::class` to `Admin::$components`.

### 10.5 Premium / Pro support

If the plugin has a premium version (EDD item ID, licensing, upgrade URLs):

- Add `byteever/licensing` to `composer.json` `require` + strauss `packages` + repos.
- In the main file, register the license: `{HELPER}()->share('license', new License(__FILE__))`.
- Gate boot on valid license: `if (! {HELPER}()->license->is_valid()) { return; }`.
- Add `Premium::class` to `Admin::$components`.
- The `Premium` component must:
  - `autoload(): bool { return ! $this->app->is_pro_active(); }` — only load when Pro is inactive.
  - Register upgrade notices, green menu item, "Go Pro" action link, Pro settings tab, and sidebar.
  - Use `wcwn_upgrade_url($campaign, $medium)` helper (add to `functions.php`) for UTM-tagged URLs.
- Add `'pro_basename'`, `'upgrade_url'`, `'item_id'`, `'store_url'` to the `$data` array in the main file.

### 10.6 File alignment with wc-starter-plugin
If the project contains source files that need to be built, such as JSX, React, TypeScript, or other bundled assets, always use Webpack as the build tool. Do not use esbuild for projects that require a build process. Use esbuild only when no build step is required and the goal is simply to minify existing JavaScript files.
| Root file | Must exist | Notes |
|---|---|---|
| `{SLUG}.php` | Yes | Main bootstrap |
| `composer.json` | Yes | byteever/plugin + licensing |
| `package.json` | Yes | esbuild + sass |
| `uninstall.php` | Yes | Proper doc blocks + escaped queries |
| `phpcs.xml` | Yes | ByteEver-Default ruleset |
| `phpstan.neon.dist` | Yes | Level 8 |
| `phpunit.xml` | Yes | Test config |
| `.editorconfig` | Yes | Tabs, 4 spaces |
| `.gitignore` | Yes | vendor, node_modules, build |
| `.distignore` | Yes | Dev files excluded from dist |

**Remove** from root: `webpack.config.js`, `.eslintrc.js`, `.prettierrc.js`, `changelog.txt`,
`package-lock.json`, 


### 10.7 Template rendering

- Old `include __DIR__ . '/views/x.php'` → `$this->app->template->view('admin.x', $data)`.
- Old `include __DIR__ . '/views/x.php'` in notices → `$this->app->templates_path('admin/x.php')`.
- Templates live in `templates/` not `includes/**/views/`.
- The Template service `extract()`s `$data` into scope — templates read variables directly.
- Templates may keep their own `use {NS}\…;` and `{CONST}_ASSETS_URL` constant references.
- Delete empty `includes/**/views/` directories after moving.

### 10.8 Admin page registration pattern

Two valid approaches — pick one:

1. **Settings registers the page** (simpler, used when the page IS the settings page):
   `Settings::register_page()` hooks `admin_pages` filter → page slug = `'{SLUG}-settings'`.
2. **Menu registers the page** (used for multi-page admin UIs):
   `Menu::register_menu()` hooks `admin_menu` → reads `admin_pages` filter → registers submenus.

For **screen IDs**: whichever class registers the page must also provide `get_screen_ids()`. Other
components (Admin for enqueuing scripts, Actions for AJAX) read them via
`$this->app->get(ClassName::class)->get_screen_ids()`.

**Critical**: The `admin_pages` filter is a *custom framework filter* fired by the `Menu` class. If your
plugin does NOT have a `Menu` class, the filter is never fired and Settings pages will not register,
causing "Sorry, you are not allowed to access this page." errors. **Every migrated plugin must have a
`Menu` class** (copy from `wc-starter-plugin/includes/Admin/Menu.php`), even if it only has one page.
Add `Menu::class` to `Plugin::$components` **before** `Settings::class`.

### 10.9 Backward compatibility — never break these

- **Option keys** (`{OPT}_*`) — field IDs, option names.
- **Hook names** (`{HOOK}_*`) — documented actions/filters.
- **Meta keys** (`_wcwn_*`, `_wc_*`, etc.) — stored in database.
- **CPT slugs** (`wcwn_notification`, etc.) — queried by code.
- **Text domain** (`wc-whatsapp-notifications`) — translations.
- **`{HELPER}()` function** — third parties call it.
- **`uninstall.php`** — must still delete `{OPT}_%` options.

### 10.10 Conditional settings fields (`data-show-if`)

To render a setting **only when another setting has a given value**, attach a `data-show-if` attribute via
the WooCommerce field's `custom_attributes` array. The settings JS reads it and toggles the field row's
visibility. Always build the JSON with `wp_json_encode()` — never hand-write the string:

```php
array(
	'title'             => __( 'Picture in Picture Width (%)', 'wc-image-flip' ),
	'desc'              => __( 'Set the picture in picture width amount in (%).', 'wc-image-flip' ),
	'id'                => 'wcif_pip_width',
	'default'           => '20',
	'type'              => 'number',
	'custom_attributes' => array(
		'data-show-if' => wp_json_encode(
			array(
				'field' => 'wcif_image_effect',
				'value' => 'picture_in_picture',
			)
		),
	),
),
```

Rules:

- `field` — the `id` of the controlling field (the one that decides visibility).
- `value` — the value the controlling field must hold for this field to stay visible.
- **If the controlling field is a `checkbox`, its value is always `1` (checked) or `0` (unchecked)** — use
  `'value' => '1'` or `'value' => '0'` explicitly.
- **For any other field type** (`select`, `radio`, `text`, `number`, …), `value` must match the input's
  value exactly, e.g. `'value' => 'picture_in_picture'`.

Checkbox-parent example:

```php
'custom_attributes' => array(
	'data-show-if' => wp_json_encode(
		array(
			'field' => '{OPT}_enable_feature',
			'value' => '1', // checkbox values are always '1' or '0'.
		)
	),
),
```

*End of migration guide.*

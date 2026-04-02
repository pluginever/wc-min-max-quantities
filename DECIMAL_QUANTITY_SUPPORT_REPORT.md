# Decimal Quantity Support Report

## Scope
This report identifies where the `wc-min-max-quantities` plugin should be edited so decimal quantities (for example `0.25`, `1.5`) work correctly with WooCommerce.

## Key Findings
Decimal input is partially supported in UI fields, but backend logic still contains integer-only checks that break decimal quantities.

### Main blockers
1. Integer casting truncates decimal limits and quantities.
2. Modulo checks (`%`) are used for step validation, which is unreliable for decimals.
3. Some cart/order validations compare formatted strings/currency helpers instead of numeric values.
4. AJAX add-to-cart quantity is forced through `absint()`, removing decimals.
5. Legacy migration uses `absint()`, which strips decimal values.

---

## Edit Map (where to change)

### 1) Core utility functions
**File:** `wp-content/plugins/wc-min-max-quantities/includes/functions.php`

- **`wcmmq_get_cart_limits()` (around lines 82-85):**
  - Change `(int) get_option(...)` to decimal-safe parsing (float/`wc_format_decimal`).
  - Suggested: keep quantity limits numeric and non-negative using a centralized helper.
- Add helper functions for decimal-safe comparisons:
  - `wcmmq_to_decimal( $value )`
  - `wcmmq_is_multiple_of_step( $qty, $step, $precision = null )`
  - `wcmmq_decimal_compare( $a, $b, $precision = null )`

These helpers should be reused everywhere validation currently does `(int)` or `%`.

### 2) Frontend/cart validation and quantity args
**File:** `wp-content/plugins/wc-min-max-quantities/includes/Cart.php`

- **`set_quantity_args()` (around lines 202, 215, 226):**
  - Replace `absint(...)` divisibility logic with decimal-safe helper checks.
  - Keep `step` as decimal when valid (`0.25`, `0.5`, etc.).
- **`add_to_cart_validation()` (around line 298):**
  - Replace `(int) $quantity % (int) $product_limits['step']` with decimal-safe multiple-of-step check.
  - Format messages with WooCommerce decimal formatting instead of `number_format()` for quantity fields.
- **`check_cart_items()` (around lines 387, 400-426):**
  - Replace `%` and `(int)` comparisons with decimal-safe numeric comparisons.
  - Preserve decimal cart quantity totals for min/max cart quantity rules.
- **`set_cart_quantity()` (around line 447):**
  - Replace `absint( $_POST['quantity'] )` with decimal-safe parsing.
  - Keep requested quantity decimal in `$_REQUEST['quantity']` when adjusting to min/step.
- **`available_variation()` (around lines 556, 569, 581, 584):**
  - Replace `absint()` and `%` divisibility checks.
  - Ensure returned variation `min_qty`, `max_qty`, and `step` remain decimal.
- Remove debug logs in production path:
  - `error_log( print_r( $total_quantity, true ) );`
  - `error_log( print_r( $product_limits, true ) );`

### 3) Settings + data persistence (free plugin)
**Files:**
- `wp-content/plugins/wc-min-max-quantities/includes/Admin/Settings.php`
- `wp-content/plugins/wc-min-max-quantities/includes/Admin/MetaBoxes.php`
- `wp-content/plugins/wc-min-max-quantities/includes/Admin/Actions.php`

Recommended changes:
- Ensure all quantity-related fields use decimal-friendly attributes:
  - `type="number"`, `step="any"` (or configurable precision), `min="0"`.
- In save handlers, use WooCommerce decimal sanitization (`wc_format_decimal`) consistently.
- Keep saved meta/options as decimal-compatible numeric strings/floats, not ints.

### 4) Migration/update path
**File:** `wp-content/plugins/wc-min-max-quantities/includes/Installer.php`

- **`update_110_settings()` (around lines 165, 178):**
  - Replace `absint()` with decimal-safe sanitization.
  - Otherwise old decimal values are permanently truncated during upgrade/migration.

---

## Pro plugin impact (if `wc-min-max-quantities-pro` is active)

### 5) Pro limit source and validation logic
**File:** `wp-content/plugins/wc-min-max-quantities-pro/includes/Cart.php`

- **`set_product_limits()` (around lines 78-122):**
  - Replace all `(int)` casts for `step`, `min_qty`, `max_qty`, `min_total`, `max_total` with decimal-safe parsing.
- **`add_to_cart_validation()` (around line 235):**
  - Replace integer modulo step validation with decimal-safe multiple-of-step helper.
- **`apply_cart_limits()`**
  - If cart quantities should support decimals, ensure min/max cart qty are parsed as decimals.

### 6) Pro admin save + UI inputs
**Files:**
- `wp-content/plugins/wc-min-max-quantities-pro/includes/Admin/Actions.php`
- `wp-content/plugins/wc-min-max-quantities-pro/includes/Admin/MetaBoxes.php`
- `wp-content/plugins/wc-min-max-quantities-pro/includes/Admin/views/html-edit-limits.php`

Recommended changes:
- Keep quantity fields decimal-enabled (`step="any"`).
- Save handlers should use `wc_format_decimal` consistently across role/category/product/variation limits.

---

## Implementation notes

1. Use a single precision policy (recommended: WooCommerce quantity precision filter or fixed epsilon like `1e-6`).
2. Do not compare currency strings (`wc_price`) to numeric cart totals.
3. Keep error messages quantity-aware with decimal formatting (locale-safe).
4. Ensure Store API/Blocks filters return decimal-capable `minimum`, `maximum`, and `multiple_of` values.

---

## Suggested rollout order

1. Add decimal helper functions in `includes/functions.php`.
2. Refactor `includes/Cart.php` validation and quantity adjustment logic.
3. Update admin sanitization/saving paths.
4. Fix migration truncation in `includes/Installer.php`.
5. Apply equivalent fixes to pro plugin files (if pro is enabled).
6. Run manual test scenarios below.

## Manual test checklist

- Product with `step=0.25`, `min=0.5`, `max=2.5` can be added with `0.5`, `0.75`, `1.0`, etc.
- Invalid step values (for example `0.6` with `step=0.25`) are blocked.
- AJAX add-to-cart preserves decimal quantity.
- Variation-level decimal limits are respected.
- Cart-level min/max quantity checks work with decimal totals.
- Notices display decimal values correctly for locale.
- Blocks/store API quantity controls behave the same as classic cart.


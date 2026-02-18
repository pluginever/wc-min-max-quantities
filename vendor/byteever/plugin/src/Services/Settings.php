<?php

namespace WooCommerceMinMaxQuantities\B8\Plugin\Services;

use ArrayAccess;
use WooCommerceMinMaxQuantities\B8\Plugin\App;
defined('ABSPATH') || exit;
/**
 * Settings registry service.
 *
 * Provides access to plugin settings definitions.
 * Settings are provided via filter hook.
 *
 * @since 1.0.0
 * @package \B8\Plugin
 */
class Settings implements ArrayAccess
{
    /**
     * Application instance.
     *
     * @since 1.0.0
     * @var App
     */
    protected App $app;
    /**
     * Registered settings.
     *
     * @since 1.0.0
     * @var array<string, array>|null
     */
    protected ?array $settings = null;
    /**
     * Constructor.
     *
     * @since 1.0.0
     *
     * @param App $app Application instance.
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }
    /**
     * Get all registered settings.
     *
     * Settings are provided via filter in nested structure:
     * [
     *     'general' => [
     *         'api_key' => [ 'type' => 'text', 'default' => '' ],
     *     ],
     * ]
     *
     * @since 1.0.0
     *
     * @param string|null $group Optional. Filter by group.
     *
     * @return array<string, array> Settings definitions keyed by ID.
     */
    public function settings(?string $group = null): array
    {
        if (null === $this->settings) {
            /**
             * Filter the settings.
             *
             * @since 1.0.0
             *
             * @param array $raw Nested settings array keyed by group.
             */
            $raw = $this->app->apply_filters('settings', array());
            $settings = array();
            foreach (array_keys($raw) as $group_id) {
                /**
                 * Filter settings fields for a specific group.
                 *
                 * @since 1.0.0
                 * @param array $fields Fields for the group.
                 * @param string $group_id Group ID.
                 */
                $fields = $this->app->apply_filters("{$group_id}_settings", $raw[$group_id] ?? array(), $group_id);
                foreach ($fields as $field) {
                    if (!is_array($field) || empty($field['id'])) {
                        continue;
                    }
                    $field = wp_parse_args($field, array('name' => $field['id'], 'group' => $group_id, 'type' => 'text', 'label' => '', 'description' => '', 'placeholder' => '', 'default' => null, 'sanitize' => '', 'priority' => 10));
                    $settings[$field['id']] = $field;
                }
            }
            uasort($settings, static fn($a, $b) => ($a['priority'] ?? 10) <=> ($b['priority'] ?? 10));
            $this->settings = $settings;
        }
        if (!empty($group)) {
            return wp_list_filter($this->settings, array('group' => $group));
        }
        return $this->settings;
    }
    /**
     * Get a setting value.
     *
     * @since 1.0.0
     *
     * @param string $key      Field key.
     * @param mixed  $fallback Fallback value if field not registered.
     *
     * @return mixed Setting value or fallback.
     */
    public function value(string $key, $fallback = null)
    {
        if (!isset($this[$key])) {
            return $fallback;
        }
        $field = $this[$key];
        return $this->app->options->get($key, $field['default']);
    }
    /**
     * Update setting value(s) with sanitization.
     *
     * @since 1.0.0
     *
     * @param string|array $key   Field key or array of key-value pairs.
     * @param mixed        $value New value (ignored if $key is array).
     *
     * @return bool True on success, false if field not registered.
     */
    public function update($key, $value = null): bool
    {
        if (is_array($key)) {
            $success = true;
            foreach ($key as $k => $v) {
                if (!$this->update($k, $v)) {
                    $success = false;
                }
            }
            return $success;
        }
        if (!isset($this[$key])) {
            return false;
        }
        $sanitized = $this->sanitize($value, $this[$key]);
        return $this->app->options->update($key, $sanitized);
    }
    /**
     * Get all current setting values.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function values(): array
    {
        $values = array();
        foreach ($this->settings() as $id => $field) {
            $values[$id] = $this->app->options->get($id, $field['default']);
        }
        return $values;
    }
    /**
     * Get all default values.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function defaults(): array
    {
        return array_map(static fn($f) => $f['default'], $this->settings());
    }
    /**
     * Reset setting(s) to default value.
     *
     * @since 1.0.0
     *
     * @param string $key Optional. Field key. Resets all if empty.
     *
     * @return bool True on success.
     */
    public function reset(string $key = ''): bool
    {
        if (empty($key)) {
            $success = true;
            foreach ($this->settings() as $id => $field) {
                if (!$this->app->options->update($id, $field['default'])) {
                    $success = false;
                }
            }
            return $success;
        }
        if (!isset($this[$key])) {
            return false;
        }
        return $this->app->options->update($key, $this[$key]['default']);
    }
    /**
     * Sanitize a value using field rules.
     *
     * @since 1.0.0
     *
     * @param mixed $value Value to sanitize.
     * @param array $field Field definition.
     *
     * @return mixed Sanitized value.
     */
    public function sanitize($value, array $field)
    {
        $sanitize = $field['sanitize'];
        if (is_callable($sanitize)) {
            return is_array($value) ? array_map($sanitize, $value) : call_user_func($sanitize, $value);
        }
        if (empty($sanitize)) {
            $defaults = array('textarea' => 'textarea', 'editor' => 'html', 'email' => 'email', 'url' => 'url', 'number' => 'int');
            $sanitize = $defaults[$field['type']] ?? 'text';
        }
        $result = $this->app->sanitizer->sanitize(array('value' => $value), array('value' => $sanitize));
        return $result['value'];
    }
    /**
     * Check if offset exists.
     *
     * @since 1.0.0
     *
     * @param mixed $offset Offset key.
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->settings());
    }
    /**
     * Get offset value.
     *
     * @since 1.0.0
     *
     * @param mixed $offset Offset key.
     *
     * @return array|null
     */
    public function offsetGet($offset): ?array
    {
        return $this->settings()[$offset] ?? null;
    }
    /**
     * Set offset - no-op, registry is read-only.
     *
     * @since 1.0.0
     *
     * @param mixed $offset Offset key.
     * @param mixed $value  Offset value.
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
    }
    /**
     * Unset offset - no-op, registry is read-only.
     *
     * @since 1.0.0
     *
     * @param mixed $offset Offset key.
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
    }
}
<?php

namespace WooCommerceMinMaxQuantities\B8\Plugin\Services;

use WooCommerceMinMaxQuantities\B8\Plugin\App;
defined('ABSPATH') || exit;
/**
 * WordPress options management with prefixing and array access.
 *
 * Provides unified interface for WordPress options with automatic prefixing,
 * dot notation support, and bulk operations.
 *
 * @since 1.0.0
 * @package \B8\Plugin
 */
class Options implements \ArrayAccess
{
    /**
     * Application instance.
     *
     * @since 1.0.0
     * @var App
     */
    protected $app;
    /**
     * Option key prefix.
     *
     * @since 1.0.0
     * @var string
     */
    protected $prefix = '';
    /**
     * Constructor.
     *
     * @since 1.0.0
     * @param App $app Application instance.
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->prefix = $this->app->option_prefix . '_';
    }
    // ==============================================
    // Basic CRUD Operations
    // ==============================================
    /**
     * Add option only if it doesn't exist.
     *
     * @since 1.0.0
     *
     * @param string    $key The option key.
     * @param mixed     $value The option value.
     * @param bool|null $autoload Whether to load the option when WordPress starts up.
     *
     * @return bool True if added, false if exists or on failure.
     */
    public function add($key, $value, $autoload = null): bool
    {
        return null === $autoload ? add_option($this->key($key), $value) : add_option($this->key($key), $value, '', $autoload);
    }
    /**
     * Get option value.
     *
     * @since 1.0.0
     *
     * @param string $key The option key.
     * @param mixed  $fallback Fallback value if option doesn't exist.
     *
     * @return mixed The option value or fallback.
     */
    public function get($key, $fallback = null)
    {
        return get_option($this->key($key), $fallback);
    }
    /**
     * Update option value (creates if doesn't exist).
     *
     * @since 1.0.0
     *
     * @param string    $key The option key.
     * @param mixed     $value The option value.
     * @param bool|null $autoload Whether to load the option when WordPress starts up.
     *
     * @return bool True on success, false on failure.
     */
    public function update($key, $value, $autoload = null): bool
    {
        return null === $autoload ? update_option($this->key($key), $value) : update_option($this->key($key), $value, $autoload);
    }
    /**
     * Delete option.
     *
     * @since 1.0.0
     *
     * @param string $key The option key.
     *
     * @return bool True on success, false on failure.
     */
    public function delete($key): bool
    {
        return delete_option($this->key($key));
    }
    /**
     * Check if option exists.
     *
     * @since 1.0.0
     *
     * @param string $key The option key.
     *
     * @return bool True if exists, false otherwise.
     */
    public function has($key): bool
    {
        return get_option($this->key($key), '__OPTION_HAS_FALLBACK__') !== '__OPTION_HAS_FALLBACK__';
    }
    // ==============================================
    // Database Version Helpers
    // ==============================================
    /**
     * Get plugin database version.
     *
     * @since 1.0.0
     * @return string The database version.
     */
    public function get_db_version(): string
    {
        return $this->get('db_version', '1.0.0');
    }
    /**
     * Update plugin database version.
     *
     * @since 1.0.0
     *
     * @param string $version The version to set.
     * @param bool   $force Force update even if the version is the same.
     *
     * @return bool True if the value was updated, false otherwise.
     */
    public function update_db_version($version, $force = false): bool
    {
        return $force ? $this->update('db_version', $version, true) : $this->add('db_version', $version, true);
    }
    // ==============================================
    // Cleanup Operations
    // ==============================================
    /**
     * Delete all plugin options from database.
     *
     * @since 1.0.0
     *
     * @return int Number of options deleted.
     */
    public function flush(): int
    {
        global $wpdb;
        $pattern = $wpdb->esc_like(rtrim($this->prefix, '_')) . '_%';
        $deleted = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $pattern));
        wp_cache_flush();
        return (int) $deleted;
    }
    // ==============================================
    // Utility Methods
    // ==============================================
    /**
     * Get option key with prefix.
     *
     * @since 1.0.0
     *
     * @param string $key The option key (with or without prefix).
     *
     * @return string The prefixed option key.
     */
    public function key($key): string
    {
        $clean_prefix = rtrim($this->prefix, '_');
        if (str_starts_with($key, $clean_prefix . '_')) {
            return $key;
        }
        return $clean_prefix . '_' . $key;
    }
    // ==============================================
    // ArrayAccess Implementation
    // ==============================================
    /**
     * Get the value at a given offset.
     *
     * @param string $offset The key to get.
     *
     * @since 1.0.0
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- this is required by the ArrayAccess interface.
        return $this->get($offset);
    }
    /**
     * Set the value at a given offset.
     *
     * @param string $offset The key to set.
     * @param mixed  $value The value to set.
     *
     * @since 1.0.0
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- this is required by the ArrayAccess interface.
        $this->update($offset, $value);
    }
    /**
     * Remove the value at a given offset.
     *
     * @param string $offset The key to remove.
     *
     * @since 1.0.0
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- this is required by the ArrayAccess interface.
        $this->delete($offset);
    }
    /**
     * Whether an offset exists.
     *
     * @param mixed $offset The key to check.
     *
     * @since 1.0.0
     * @return bool True if the offset exists, false otherwise.
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- this is required by the ArrayAccess interface.
        return $this->has($offset);
    }
}
<?php

namespace WooCommerceMinMaxQuantities\B8\Plugin\Traits;

defined('ABSPATH') || exit;
/**
 * Provides WordPress hook management with prefixed hook support.
 *
 * @since 1.0.0
 * @package \B8\Plugin
 */
trait HookableTrait
{
    /**
     * Generates a prefixed hook name.
     *
     * @since 1.0.0
     * @param string $name The hook name (without prefix).
     * @return string The prefixed hook name.
     */
    public function hook_name(string $name): string
    {
        $sep = $this->hook_separator;
        $hook = $this->hook_prefix . $sep . $name;
        $hook = preg_replace('/[^A-Za-z0-9]/', $sep, $hook);
        $hook = preg_replace('/[' . preg_quote($sep, '/') . ']+/', $sep, $hook);
        return strtolower(trim($hook, $sep));
    }
    /**
     * Fires a prefixed action hook.
     *
     * @since 1.0.0
     * @param string $hook The hook name (without prefix).
     * @param mixed  ...$args Arguments to pass to the hook.
     * @return void
     */
    public function do_action(string $hook, ...$args): void
    {
        do_action($this->hook_name($hook), ...$args);
    }
    /**
     * Adds an action hook with container support.
     *
     * @since 1.0.0
     * @param string $hook The hook name.
     * @param mixed  $callback The callback to execute.
     * @param int    $priority Optional. Hook priority. Default 10.
     * @param int    $accepted_args Optional. Number of arguments. Default 1.
     * @return bool True on success, false on failure.
     */
    public function add_action($hook, $callback, $priority = 10, $accepted_args = 1): bool
    {
        if (is_callable($callback) && !is_string($callback)) {
            return add_action($hook, $callback, $priority, $accepted_args);
        }
        return add_action($hook, $this->callback($callback), $priority, $accepted_args);
    }
    /**
     * Adds a callback to a prefixed action hook.
     *
     * @since 1.0.0
     * @param string $hook          The hook name (without prefix).
     * @param mixed  $callback      The callback to execute.
     * @param int    $priority      Optional. Hook priority. Default 10.
     * @param int    $accepted_args Optional. Number of arguments. Default 1.
     * @return bool True on success, false on failure.
     */
    public function on_action(string $hook, $callback, int $priority = 10, int $accepted_args = 1): bool
    {
        return $this->add_action($this->hook_name($hook), $callback, $priority, $accepted_args);
    }
    /**
     * Removes an action hook.
     *
     * @since 1.0.0
     * @param string $hook The hook name.
     * @param mixed  $callback The callback to remove.
     * @param int    $priority Optional. Hook priority. Default 10.
     * @return bool True on success, false on failure.
     */
    public function remove_action($hook, $callback, $priority = 10): bool
    {
        if (is_callable($callback) && !is_string($callback)) {
            return remove_action($hook, $callback, $priority);
        }
        return remove_action($hook, $this->callback($callback), $priority);
    }
    /**
     * Applies a prefixed filter hook.
     *
     * @since 1.0.0
     * @param string $hook  The hook name (without prefix).
     * @param mixed  $value The value to filter.
     * @param mixed  ...$args Additional arguments to pass to the hook.
     * @return mixed The filtered value.
     */
    public function apply_filters(string $hook, $value, ...$args)
    {
        return apply_filters($this->hook_name($hook), $value, ...$args);
    }
    /**
     * Adds a filter hook with container support.
     *
     * @since 1.0.0
     * @param string $hook The hook name.
     * @param mixed  $callback The callback to execute.
     * @param int    $priority Optional. Filter priority. Default 10.
     * @param int    $accepted_args Optional. Number of arguments. Default 1.
     * @return bool True on success, false on failure.
     */
    public function add_filter($hook, $callback, $priority = 10, $accepted_args = 1): bool
    {
        if (is_callable($callback) && !is_string($callback)) {
            return add_filter($hook, $callback, $priority, $accepted_args);
        }
        return add_filter($hook, $this->callback($callback), $priority, $accepted_args);
    }
    /**
     * Adds a callback to a prefixed filter hook.
     *
     * @since 1.0.0
     * @param string $hook          The hook name (without prefix).
     * @param mixed  $callback      The callback to execute.
     * @param int    $priority      Optional. Hook priority. Default 10.
     * @param int    $accepted_args Optional. Number of arguments. Default 1.
     * @return bool True on success, false on failure.
     */
    public function on_filter(string $hook, $callback, int $priority = 10, int $accepted_args = 1): bool
    {
        return $this->add_filter($this->hook_name($hook), $callback, $priority, $accepted_args);
    }
    /**
     * Removes a filter hook.
     *
     * @since 1.0.0
     * @param string $hook The hook name.
     * @param mixed  $callback The callback to remove.
     * @param int    $priority Optional. Hook priority. Default 10.
     * @return bool True on success, false on failure.
     */
    public function remove_filter($hook, $callback, $priority = 10): bool
    {
        if (is_callable($callback) && !is_string($callback)) {
            return remove_filter($hook, $callback, $priority);
        }
        return remove_filter($hook, $this->callback($callback), $priority);
    }
}
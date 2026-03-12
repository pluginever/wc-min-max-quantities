<?php

namespace WooCommerceMinMaxQuantities\B8\Plugin\Traits;

defined('ABSPATH') || exit;
/**
 * Provides plugin path and URL resolution methods.
 *
 * @since 1.0.0
 * @package \B8\Plugin
 */
trait PathableTrait
{
    /**
     * Get the 'basename' for the plugin (e.g. my-plugin/my-plugin.php).
     *
     * @since  1.0.0
     * @return string The plugin basename.
     */
    public function basename()
    {
        return plugin_basename($this->file);
    }
    /**
     * Retrieves the plugin directory path.
     *
     * @since 1.0.0
     * @param string $path Optional. Path relative to the plugin directory.
     * @return string
     */
    public function plugin_path($path = ''): string
    {
        return $this->join_path(plugin_dir_path($this->file), $path);
    }
    /**
     * Retrieves the plugin directory URL.
     *
     * @since 1.0.0
     * @param string $path Optional. Path relative to the plugin directory.
     * @return string
     */
    public function plugin_url($path = ''): string
    {
        return $this->join_path(plugin_dir_url($this->file), $path);
    }
    /**
     * Retrieves the assets directory path.
     *
     * @since 1.0.0
     * @param string $path Optional. Path relative to the assets directory.
     * @return string
     */
    public function assets_path($path = ''): string
    {
        return $this->join_path($this->plugin_path(), $this->assets_dir, $path);
    }
    /**
     * Retrieves the assets directory URL.
     *
     * @since 1.0.0
     * @param string $path Optional. Path relative to the assets directory.
     * @return string
     */
    public function assets_url($path = ''): string
    {
        return $this->join_path($this->plugin_url(), $this->assets_dir, $path);
    }
    /**
     * Retrieves the templates directory path.
     *
     * @since 1.0.0
     * @param string $path Optional. Path relative to the templates directory.
     * @return string
     */
    public function templates_path($path = ''): string
    {
        return $this->join_path($this->plugin_path(), $this->templates_dir, $path);
    }
    /**
     * Join path segments into a complete path.
     *
     * @since 1.0.0
     * @param string $base     Base path or URL.
     * @param string ...$segments Path segments to append.
     * @return string The joined path.
     */
    protected function join_path(string $base, string ...$segments): string
    {
        $path = rtrim($base, '/');
        foreach ($segments as $segment) {
            if ('' !== $segment) {
                $path .= '/' . ltrim($segment, '/');
            }
        }
        return $path;
    }
}
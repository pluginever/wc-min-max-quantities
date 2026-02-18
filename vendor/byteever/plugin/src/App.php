<?php

namespace WooCommerceMinMaxQuantities\B8\Plugin;

use WooCommerceMinMaxQuantities\B8\Plugin\Container\Container;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Cache;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Flash;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Router;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Logger;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Notices;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Options;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Queue;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Sanitizer;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Scripts;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Settings;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Template;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Validator;
defined('ABSPATH') || exit;
/**
 * Main App class that need to be extended by the plugin.
 *
 * @since 1.0.0
 * @package \B8\Plugin
 *
 * @property string    $file               Main plugin file path.
 * @property string    $slug               Plugin slug (directory name).
 * @property string    $version            Plugin version.
 * @property string    $short_name         Base alphanumeric identifier used to generate default prefixes (e.g., 'myplugin').
 * @property string    $rest_prefix        REST API namespace prefix.
 * @property string    $rest_version       REST API version (e.g. 'v1'). When set, automatically appended to namespace.
 * @property string    $option_prefix      Database options prefix.
 * @property string    $cache_group        Object cache group identifier.
 * @property string    $hook_prefix        Custom WordPress hooks prefix.
 * @property string    $text_domain        Plugin text domain.
 * @property string    $domain_path        Domain path for translations.
 * @property string    $assets_dir         Assets directory name.
 * @property string    $templates_dir      Templates directory name.
 * @property int       $cache_ttl          Cache lifetime in seconds.
 *
 * @property Router    $router             REST API router.
 * @property Flash     $flash              Flash messages service.
 * @property Logger    $logger             Logger service.
 * @property Notices   $notices            Admin notices service.
 * @property Cache     $cache              Cache service.
 * @property Options   $options            Options service.
 * @property Queue     $queue              Background queue service.
 * @property Utils     $utils              Utilities service.
 * @property Validator $validator          Validator service.
 * @property Template  $template           Template service.
 * @property Sanitizer $sanitizer          Sanitizer service.
 * @property Scripts   $scripts            Scripts service.
 * @property Settings  $settings           Settings service.
 */
abstract class App extends Container
{
    /**
     * Framework version.
     *
     * @since 1.0.0
     * @var string
     */
    const FW_VERSION = '0.0.1';
    /**
     * Singleton instances.
     *
     * @since 1.0.0
     * @var array<string, self>
     */
    protected static $instances = array();
    /**
     * Creates and returns the singleton instance.
     *
     * @since 1.0.0
     * @param string $file The main plugin file path (__FILE__).
     * @param array  $data Plugin configuration array.
     *
     * @return static The instance of the plugin.
     */
    public static function create($file, array $data = array())
    {
        $p = get_called_class();
        if (!isset(static::$instances[$p])) {
            $data['file'] = $file;
            static::$instances[$p] = new static($data);
            static::$instances[$p]->bootstrap();
        }
        return static::$instances[$p];
    }
    /**
     * Retrieves the singleton instance.
     *
     * @since 1.0.0
     * @return self The instance of the plugin.
     */
    public static function instance(): self
    {
        $p = get_called_class();
        if (!isset(static::$instances[$p])) {
            wp_die('Plugin not initialized.');
        }
        return static::$instances[$p];
    }
    /**
     * Plugin constructor.
     *
     * @param array $data The plugin data.
     *
     * @since 1.0.0
     */
    protected function __construct($data)
    {
        $this->configure($data);
        $this->preflight();
    }
    /**
     * Configures application properties.
     *
     * @since 1.0.0
     * @param array $data The plugin data.
     * @return void
     */
    protected function configure(array $data): void
    {
        $slug = basename(dirname($data['file']));
        $short_slug = strtolower(preg_replace('/[^a-z0-9]/i', '', $slug));
        $short_name = !empty($data['short_name']) ? $data['short_name'] : $short_slug;
        $defaults = array('file' => $data['file'], 'slug' => $slug, 'version' => '1.0.0', 'short_name' => $short_name, 'rest_prefix' => $short_name, 'rest_version' => '', 'option_prefix' => $short_name, 'cache_group' => $short_name, 'hook_prefix' => str_replace('-', '_', $slug), 'hook_separator' => '_', 'text_domain' => str_replace('_', '-', $slug), 'domain_path' => '/languages', 'assets_dir' => 'assets/build', 'templates_dir' => 'templates', 'cache_ttl' => 3600);
        $config = array_merge($defaults, $data);
        foreach ($config as $key => $value) {
            $this->set($key, $value);
        }
        $required = array('file', 'version', 'short_name', 'rest_prefix', 'option_prefix', 'cache_group', 'hook_prefix');
        foreach ($required as $key) {
            if (empty($this->get($key))) {
                wp_die(sprintf('Plugin error: "%s" is required.', esc_html($key)));
            }
        }
    }
    /**
     * Registers framework services.
     *
     * @since 1.0.0
     * @return void
     */
    protected function preflight(): void
    {
        // Register application instance with alias.
        $this->share(static::class, $this);
        $this->alias(static::class, 'app');
        $this->alias(static::class, __CLASS__);
        // Core Services.
        $this->bind('flash', Flash::class);
        $this->bind('logger', Logger::class);
        $this->bind('notices', Notices::class);
        $this->bind('router', Router::class);
        // Utility Services.
        $this->bind('cache', Cache::class);
        $this->bind('options', Options::class);
        $this->bind('queue', Queue::class);
        $this->bind('sanitizer', Sanitizer::class);
        $this->bind('scripts', Scripts::class);
        $this->bind('settings', Settings::class);
        $this->bind('template', Template::class);
        $this->bind('utils', Utils::class);
        $this->bind('validator', Validator::class);
        // Initialize services with hooks.
        $this->make('flash');
        $this->make('logger');
        $this->make('notices');
        $this->make('queue');
        $this->make('router');
        $this->make('scripts');
        add_action('init', function () {
            if (wp_style_is('b8-components', 'registered')) {
                return;
            }
            $css_url = plugin_dir_url(__FILE__) . 'assets/css/';
            $this->scripts->register_style('b8-components', $css_url . 'components.css');
            $this->scripts->register_style('b8-layout', $css_url . 'layout.css');
        }, 1);
    }
    /**
     * Bootstraps the plugin.
     *
     * @since 1.0.0
     * @return void
     */
    protected function bootstrap(): void
    {
        // Implement in the extending class.
    }
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
        return $this->utils->path_join(plugin_dir_path($this->file), array($path));
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
        return $this->utils->path_join(plugin_dir_url($this->file), array($path));
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
        return $this->utils->path_join($this->plugin_path(), array($this->assets_dir, $path));
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
        return $this->utils->path_join($this->plugin_url(), array($this->assets_dir, $path));
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
        return $this->utils->path_join($this->plugin_path(), array($this->templates_dir, $path));
    }
    /**
     * Generates a prefixed hook name.
     *
     * @since 1.0.0
     * @param string $name The hook name (without prefix).
     * @return string The prefixed hook name.
     */
    public function hook_name(string $name): string
    {
        return $this->utils->str_join(array($this->hook_prefix, $name), $this->hook_separator);
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
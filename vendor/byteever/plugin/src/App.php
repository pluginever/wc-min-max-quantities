<?php

namespace WooCommerceMinMaxQuantities\B8\Plugin;

use WooCommerceMinMaxQuantities\B8\Plugin\Container\Container;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Cache;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Filesystem;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Flash;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Router;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Logger;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Notices;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Options;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Queue;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Request;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Scripts;
use WooCommerceMinMaxQuantities\B8\Plugin\Services\Template;
use WooCommerceMinMaxQuantities\B8\Plugin\Traits\HookableTrait;
use WooCommerceMinMaxQuantities\B8\Plugin\Traits\PathableTrait;
defined('ABSPATH') || exit;
/**
 * Main App class that need to be extended by the plugin.
 *
 * @since 1.0.0
 * @package \B8\Plugin
 *
 * @property string     $file               Main plugin file path.
 * @property string     $slug               Plugin slug (directory name).
 * @property string     $version            Plugin version.
 * @property string     $short_name         Base alphanumeric identifier used to generate default prefixes (e.g., 'myplugin').
 * @property string     $rest_prefix        REST API namespace prefix.
 * @property string     $rest_version       REST API version (e.g. 'v1'). When set, automatically appended to namespace.
 * @property string     $option_prefix      Database options prefix.
 * @property string     $cache_group        Object cache group identifier.
 * @property string     $hook_prefix        Custom WordPress hooks prefix.
 * @property string     $text_domain        Plugin text domain.
 * @property string     $domain_path        Domain path for translations.
 * @property string     $assets_dir         Assets directory name.
 * @property string     $templates_dir      Templates directory name.
 * @property int        $cache_ttl          Cache lifetime in seconds.
 * @property string     $log_level          Minimum log level (default: 'error').
 * @property int        $log_max_size       Maximum log file size in bytes (default: 5MB).
 *
 * @property Router     $router             REST API router.
 * @property Flash      $flash              Flash messages service.
 * @property Logger     $logger             Logger service.
 * @property Notices    $notices            Admin notices service.
 * @property Cache      $cache              Cache service.
 * @property Options    $options            Options service.
 * @property Queue      $queue              Background queue service.
 * @property Filesystem $fs                 Filesystem service.
 * @property Template   $template           Template service.
 * @property Request    $request            Request service.
 * @property Scripts    $scripts            Scripts service.
 */
abstract class App extends Container
{
    use HookableTrait;
    use PathableTrait;
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
        $defaults = array('file' => $data['file'], 'slug' => $slug, 'version' => '1.0.0', 'short_name' => $short_name, 'rest_prefix' => $short_name, 'rest_version' => '', 'option_prefix' => $short_name, 'cache_group' => $short_name, 'hook_prefix' => str_replace('-', '_', $slug), 'hook_separator' => '_', 'text_domain' => str_replace('_', '-', $slug), 'domain_path' => '/languages', 'assets_dir' => 'assets', 'build_dir' => 'build', 'templates_dir' => 'templates', 'cache_ttl' => 3600, 'log_level' => 'error', 'log_max_size' => 5 * 1024 * 1024);
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
        $this->bind('fs', Filesystem::class);
        $this->bind('options', Options::class);
        $this->bind('queue', Queue::class);
        $this->bind('request', Request::class);
        $this->bind('scripts', Scripts::class);
        $this->bind('template', Template::class);
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
     * Check if a plugin is installed.
     *
     * @since 1.0.0
     * @param string $plugin The plugin slug or basename.
     * @return bool
     */
    public function plugin_exists($plugin): bool
    {
        if (!str_contains($plugin, '/')) {
            $plugin = $plugin . '/' . $plugin . '.php';
        }
        $plugins = get_plugins();
        return array_key_exists($plugin, $plugins);
    }
    /**
     * Check if a plugin is active.
     *
     * @since 1.0.0
     * @param string $plugin The plugin slug or basename.
     * @return bool
     */
    public function plugin_active($plugin): bool
    {
        if (!str_contains($plugin, '/')) {
            $plugin = $plugin . '/' . $plugin . '.php';
        }
        $active_plugins = (array) get_option('active_plugins', array());
        if (is_multisite()) {
            $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
        }
        return in_array($plugin, $active_plugins, true) || array_key_exists($plugin, $active_plugins);
    }
}
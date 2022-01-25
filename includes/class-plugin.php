<?php
/**
 * Main plugin file.
 *
 * @version  1.1.0
 * @since    1.1.0
 * @package  WC_Min_Max_Quantities
 */

namespace WC_Min_Max_Quantities;

defined( 'ABSPATH' ) || exit();

/**
 * Final Plugin class.
 */
final class Plugin {
	/**
	 * Plugin version number.
	 *
	 * @since 1.1.0
	 * @const string
	 */
	protected $version = '1.1.0';

	/**
	 * Plugin data container.
	 *
	 * @since 1.1.0
	 * @var array|bool[]|int[]|string[]|object[]
	 */
	protected $container = [];

	/**
	 * The single instance of the class.
	 *
	 * @since 1.1.0
	 * @var Plugin
	 */
	protected static $instance;

	/**
	 * Main Plugin Instance.
	 *
	 * Ensures only one instance of Plugin is loaded or can be loaded.
	 *
	 * @since 1.1.0
	 * @return Plugin - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'wc-min-max-quantities' ), '1.1.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'wc-min-max-quantities' ), '1.1.0' );
	}

	/**
	 * This method will be called when somebody will try to invoke a method in object
	 * context, which does not exist, like:
	 *
	 * $plugin->method($arg, $arg1);
	 *
	 * @param string $method Method name.
	 * @param array $arguments Array of arguments passed to the method.
	 */
	public function __call( $method, $arguments ) {
		switch ( $method ) {
			case "get":
				return $this->get( $arguments[0] );
			case "set":
				return $this->set( $arguments[0], $arguments[1] );
			case "has":
				return $this->has( $arguments[0] );
			default:
				throw new \BadMethodCallException( "Undefined method $method" );
		}
	}

	/**
	 * Magic method for calling methods on the container.
	 *
	 * @param string $method Method name.
	 * @param mixed $args Method arguments.
	 *
	 * @mathod static get_version
	 *
	 * @return bool|mixed|null
	 */
	public static function __callStatic( $method, $args ) {
		return self::$instance->__call( $method, $args );
	}

	/**
	 * Call method or properties on demand.
	 *
	 * @param string $prop Property to return the value for.
	 * @param mixed $default The value that should be returned if the variable key is not a registered one.
	 *
	 * @since 1.1.0
	 * @return mixed Either the registered value or the default value if the variable is not registered.
	 */
	protected function get( $prop, $default = null ) {
		if ( isset( $this->container[ $prop ] ) ) {
			return $this->container[ $prop ];
		}

		return $default;
	}

	/**
	 * Call method or properties on demand.
	 *
	 * @param string $prop Property to set a value for.
	 * @param mixed $value Value to set.
	 *
	 * @since 1.1.0
	 * @return Plugin
	 */
	protected function set( $prop, $value = null ) {
		if ( empty( $prop ) ) {
			return $this;
		}

		if ( $this->has( $prop ) ) {
			return $this;
		}

		if ( is_null( $value ) ) {
			$value = $prop;
		}

		$this->container[ $prop ] = $value;

		return $this;
	}

	/**
	 * Method for checking the existence of a certain key in the container.
	 *
	 * @param string $key Key to check the set status for.
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	protected function has( $key ) {
		return ! empty( $this->container[ $key ] );
	}

	/**
	 * Returns if the plugin is in PRO version
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	public function is_pro_exists() {
		return apply_filters( 'wc_min_max_quantities_is_pro_exists', false );
	}

	/**
	 * Plugin Constructor.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	protected function __construct() {
		$this->setup_plugin();
		$this->includes();
		$this->register_hooks();
	}

	/**
	 * Setup plugin specific data.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	protected function setup_plugin() {
		// Plugin data.
		$this->set( 'version', $this->version );
		$this->set( 'file', WC_MIN_MAX_QUANTITIES_PLUGIN_FILE );
		$this->set( 'name', 'WC Min Max Quantities' );
		$this->set( 'slug', basename( dirname( $this->get( 'file' ) ) ) );
		$this->set( 'basename', plugin_basename( $this->get( 'file' ) ) );
		$this->set( 'plugin_id', str_replace( '-', '_', $this->get( 'slug' ) ) );
		$this->set( 'plugin_path', untrailingslashit( plugin_dir_path( $this->get( 'file' ) ) ) );
		$this->set( 'plugin_url', untrailingslashit( plugin_dir_url( $this->get( 'file' ) ) ) );
		$this->set( 'pro_exist', $this->is_pro_exists() );

		// Meta URLs.
		$this->set( 'docs_url', 'http://pluginever.com/docs' );
		$this->set( 'support_url', 'http://pluginever.com/support' );
		$this->set( 'reviews_url', 'https://wordpress.org/support/plugin/wc-min-max-quantities/reviews/?filter=5' );
		$this->set( 'pro_url', 'https://pluginever.com/plugins/wc-min-max-quantities-pro/' );
		$this->set( 'settings_url', admin_url( 'admin.php?page=wc-min-max-quantities-settings' ) );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	protected function includes() {
		include_once __DIR__ . '/class-autoloader.php';
	}

	/**
	 * Register plugin hooks.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	protected function register_hooks() {
		add_filter( 'plugin_action_links_' . $this->get( 'basename' ), array( $this, 'action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );

		// Init the plugin after WordPress inits.
		add_action( 'init', array( $this, 'init' ), 0);
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function i18n() {
		load_plugin_textdomain( 'wc-min-max-quantities', false, plugin_basename( $this->get( 'plugin_path' ) ) . '/i18n/languages/' );
	}

	/**
	 * Return the plugin action links.  This will only be called if the plugin
	 * is active.
	 *
	 * @param array $links associative array of action names to anchor tags.
	 *
	 * @since 1.1.0
	 * @return array associative array of plugin action links.
	 */
	public function action_links( $links ) {
		if ( $this->has( 'settings_url' ) ) {
			array_unshift( $links, sprintf( '<a href="%1$s">%2$s</a>', esc_url( $this->get( 'settings_url' ) ), __( 'Settings', 'wc-min-max-quantities' ) ) );
		}

		if ( ! $this->is_pro_exists() && $this->has( 'pro_url' ) ) {
			array_unshift( $links, sprintf( '<a href="%1$s" style="color:red;" target="_blank">%2$s</a>', esc_url( Plugin::instance()->get( 'pro_url' ) ), __( 'Upgrade', 'wc-min-max-quantities' ) ) );
		}

		return $links;
	}

	/**
	 * Filters the array of row meta for each plugin in the Plugins list table.
	 *
	 * @param string[] $links An array of the plugin's metadata.
	 * @param string $file Path to the plugin file relative to the plugins' directory.
	 *
	 * @since 1.1.0
	 * @return string[] An array of the plugin's metadata.
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( $file !== $this->get( 'basename' ) ) {
			return $links;
		}

		$custom_links = array();

		// documentation url if any.
		if ( $this->has( 'docs_url' ) ) {
			/* translators: Docs as in Documentation */
			$custom_links['docs'] = sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $this->get( 'docs_url' ) ), esc_html__( 'Docs', 'wc-min-max-quantities' ) );
		}

		// support url if any.
		if ( $this->has( 'support_url' ) ) {
			$custom_links['support'] = sprintf( '<a href="%s">%s</a>', $this->get( 'support_url' ), esc_html_x( 'Support', 'noun', 'wc-min-max-quantities' ) );
		}

		// review url if any.
		if ( $this->has( 'reviews_url' ) ) {
			$custom_links['reviews'] = sprintf( '<a href="%s">%s</a>', $this->get( 'reviews_url' ), esc_html_x( 'Reviews', 'verb', 'wc-min-max-quantities' ) );
		}

		return array_merge( $links, $custom_links );
	}

	/**
	 * Initialize plugin.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function init() {
		// Set up localisation.
		$this->i18n();

		$this->set( 'background_updater', new Utilities\Background_Updater() );
		$this->set( 'lifecycle', new Lifecycle() );
		$this->set( 'settings', new Settings() );
		$this->set( 'cart_manager', new Cart_Manager() );

		if ( is_admin() ) {
			$this->set( 'admin_manager', new Admin\Admin_Manager() );
		}

		do_action( 'wc_min_max_quantities_init' );
	}
}

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
 *
 * @property string $version Plugin version.
 * @property string $slug Plugin slug.
 * @property string $id Plugin unique id.
 * @property string $name Plugin name.
 * @property string $file Plugin file.
 * @property string $basename Plugin basename.
 * @property string $dir Plugin directory path.
 * @property string $url Plugin directory url.
 */
final class Plugin {
	/**
	 * The single instance of the class.
	 *
	 * @since 1.1.0
	 * @var Plugin
	 */
	protected static $instance;

	/**
	 * Plugin data container.
	 *
	 * @since 1.1.0
	 * @var array
	 */
	protected $container = [];

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
	 * Magic method for checking the existence of a certain container.
	 *
	 * @param string $key Key to check the set status for.
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	public function __isset( $key ) {
		return isset( $this->container[ $key ] );
	}

	/**
	 * Call method or properties on demand.
	 *
	 * @param string $key Key to return the value for.
	 *
	 * @since 1.1.0
	 * @return mixed
	 */
	public function __get( $key ) {
		return isset( $this->container[ $key ] ) ? $this->container[ $key ] : null;
	}

	/**
	 * Call method or properties on demand.
	 *
	 * @param string $key Key to set a value for.
	 * @param mixed $value Value to set.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function __set( $key, $value ) {
		$this->container[ $key ] = $value;
	}

	/**
	 * Magic method for unsetting variables.
	 *
	 * @param string $key Key to unset a value for.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function __unset( $key ) {
		if ( isset( $this->container[ $key ] ) ) {
			unset( $this->container[ $key ] );
		}
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
		$sub_method = substr( $method, 0, 3 );
		// Drop method name.
		$property_name = substr( $method, 4 );
		switch ( $sub_method ) {
			case "get":
				return $this->get( $property_name );
			case "set":
				$this->set( $property_name, $arguments[0] );
				break;
			case "has":
				return $this->has( $property_name );
			default:
				throw new \BadMethodCallException( "Undefined method $method" );
		}

		return null;
	}

	/**
	 * Call method or properties on demand.
	 *
	 * @param string $key Key to return the value for.
	 *
	 * @since 1.1.0
	 * @return mixed
	 */
	public function get( $key ) {
		return $this->__get( $key );
	}

	/**
	 * Call method or properties on demand.
	 *
	 * @param string $key Key to set a value for.
	 * @param mixed $value Value to set.
	 *
	 * @since 1.1.0
	 * @return Plugin
	 */
	public function set( $key, $value ) {
		$this->__set( $key, $value );

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
	public function has( $key ) {
		return $this->__isset( $key );
	}

	/**
	 * Get the full path to the plugin folder, with trailing slash (e.g. /wp-content/plugins/my-plugin/).
	 *
	 * @param string $path Relative path.
	 *
	 * @since 1.1.0
	 * @return string The plugin directory path.
	 */
	public function get_path( $path = '' ) {
		$plugin_path = plugin_dir_path( $this->file );
		if ( ! empty( $plugin_path ) && ! empty( $path ) && is_string( $path ) ) {
			$plugin_path = trailingslashit( $plugin_path );
			$plugin_path .= ltrim( $path, '/' );
		}

		return $plugin_path;
	}

	/**
	 * Get the URL to the plugin folder, with trailing slash.
	 *
	 * @param string $path Relative path.
	 *
	 * @since 1.1.0
	 * @return string (URL)
	 */
	public function get_url( $path = '' ) {
		$url = plugin_dir_url( $this->file );
		if ( ! empty( $url ) && ! empty( $path ) && is_string( $path ) ) {
			$url = trailingslashit( $url );
			$url .= ltrim( $path, '/' );
		}

		return $url;
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
		$this->version  = '1.1.0';
		$this->slug     = 'wc-min-max-quantities';
		$this->id       = 'wc_min_max_quantities';
		$this->name     = 'WC Min Max Quantities';
		$this->file     = WC_MIN_MAX_QUANTITIES_FILE;
		$this->basename = plugin_basename( $this->file );
		$this->dir      = plugin_dir_path( $this->file );
		$this->url      = plugin_dir_url( $this->file );

		// Meta URLs.
		$this->docs_url    = 'http://pluginever.com/docs';
		$this->support_url = 'http://pluginever.com/support';
		$this->reviews_url = 'https://wordpress.org/support/plugin/wc-min-max-quantities/reviews/#new-post';
		$this->upgrade_url = 'https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/';
		$this->settings_url = admin_url( 'admin.php?page=wc-min-max-quantities-settings' );
	}

	/**
	 * Register plugin hooks.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	protected function register_hooks() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

		add_filter( 'plugin_action_links_' . $this->basename, array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );

		add_action( 'init', [ $this, 'i18n' ] );
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Trigger plugin loaded hook.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function plugins_loaded() {
		do_action( 'wc_min_max_quantities_loaded' );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function i18n() {
		load_plugin_textdomain( 'wc-min-max-quantities', false, dirname( $this->basename ) . '/i18n/languages' );
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
	public function plugin_action_links( $links ) {
		if ( $this->has( 'settings_url' ) ) {
			array_unshift( $links, sprintf( '<a href="%1$s">%2$s</a>', esc_url( $this->get( 'settings_url' ) ), __( 'Settings', 'wc-min-max-quantities' ) ) );
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
		if ( $file !== $this->basename ) {
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

		if ( ! $this->is_pro_exists() && $this->has( 'upgrade_url' ) ) {
			$links['upgrade_url'] = '<a href="' . esc_url( $this->get( 'upgrade_url' ) ) . '" title="' . esc_attr( __( 'Upgrade to Pro', 'wc-min-max-quantities' ) ) . '" style="color:red;">' . esc_html__( 'Upgrade to Pro', 'wc-min-max-quantities' ) . '</a>';
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
		$this->lifecycle     = new Lifecycle();
		$this->admin_notices = new Admin\Admin_Notices();
		// WooCommerce
		$this->wc_cart_manager = new WC\Cart_Manager();

		if ( is_admin() ) {
			$this->admin_manager    = new Admin\Admin_Manager();
			$this->wc_admin_manager = new WC\Admin_Manager();
		}
	}
}

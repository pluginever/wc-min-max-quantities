<?php
/**
 * Plugin Name: WC Min Max Quantities
 * Plugin URI:  https://www.pluginever.com/wc-minmax-qunatities
 * Description: The plugin allows you to Set minimum and maximum allowable product quantities and price per product and order.
 * Version:     1.0.2
 * Author:      pluginever
 * Author URI:  https://www.pluginever.com
 * Donate link: https://www.pluginever.com
 * License:     GPLv2+
 * Text Domain: wc-minmax-quantities
 * Domain Path: /i18n/languages/
 * Tested up to: 5.2.3
 * WC requires at least: 3.0.0
 * WC tested up to: 3.7.0
 */

/**
 * Copyright (c) 2018 pluginever (email : support@pluginever.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Main initiation class
 *
 * @since 1.0.0
 */

/**
 * Main WC_MINMAX Class.
 *
 * @class WC_MINMAX
 */
final class WC_MINMAX {
	/**
	 * The single instance of the class.
	 *
	 * @var WC_MINMAX
	 * @since 1.0.0
	 */
	protected static $instance = null;
	/**
	 * WC_MINMAX version.
	 *
	 * @var string
	 */
	public $version = '1.0.2';
	/**
	 * Minimum PHP version required
	 *
	 * @var string
	 */
	private $min_php = '5.6.0';

	/**
	 * Main WC_MINMAX Instance.
	 *
	 * Ensures only one instance of WC_MINMAX is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return WC_MINMAX - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'wc-minmax-quantities' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'wc-minmax-quantities' ), '1.0.0' );
	}


	/**
	 * EverProjects Constructor.
	 */
	public function setup() {
		$this->check_environment();
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
		$this->plugin_init();
		do_action( 'wc_minmax_quantities_loaded' );
	}

	/**
	 * Ensure theme and server variable compatibility
	 */
	public function check_environment() {
		if ( version_compare( PHP_VERSION, $this->min_php, '<=' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );

			wp_die( "Unsupported PHP version Min required PHP Version:{$this->min_php}" );
		}
	}

	/**
	 * Define EverProjects Constants.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function define_constants() {
		//$upload_dir = wp_upload_dir( null, false );
		define( 'WC_MINMAX_VERSION', $this->version );
		define( 'WC_MINMAX_FILE', __FILE__ );
		define( 'WC_MINMAX_PATH', dirname( WC_MINMAX_FILE ) );
		define( 'WC_MINMAX_INCLUDES', WC_MINMAX_PATH . '/includes' );
		define( 'WC_MINMAX_URL', plugins_url( '', WC_MINMAX_INCLUDES ) );
		define( 'WC_MINMAX_ASSETS_URL', WC_MINMAX_URL . '/assets' );
		define( 'WC_MINMAX_TEMPLATES_DIR', WC_MINMAX_PATH . '/templates' );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		//core includes
		include_once WC_MINMAX_INCLUDES . '/core-functions.php';
		include_once WC_MINMAX_INCLUDES . '/class-install.php';

		//admin includes
		if ( $this->is_request( 'admin' ) ) {
			require_once WC_MINMAX_INCLUDES . '/admin/class-settings-api.php';
			require_once WC_MINMAX_INCLUDES . '/admin/class-settings.php';
			require_once WC_MINMAX_INCLUDES . '/admin/metabox-functions.php';
		}
	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 *
	 * @return string
	 */

	private function is_request($type) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! defined( 'REST_REQUEST' );
		}
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 2.3
	 */
	private function init_hooks() {
		// Localize our plugin
		add_action( 'init', array( $this, 'localization_setup' ) );

		//add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
	}

	public function plugin_init() {

	}

	/**
	 * Initialize plugin for localization
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'wc-minmax-quantities', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Plugin action links
	 *
	 * @param  array $links
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		//$links[] = '<a href="' . admin_url( 'admin.php?page=' ) . '">' . __( 'Settings', '' ) . '</a>';
		return $links;
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', WC_MINMAX_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( WC_MINMAX_FILE ) );
	}

	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public function template_path() {
		return WC_MINMAX_TEMPLATES_DIR;
	}

}

function wc_minmax_quantities() {
	return WC_MINMAX::instance();
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	//fire off the plugin
	wc_minmax_quantities();
}

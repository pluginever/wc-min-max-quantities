<?php

namespace PluginEver\WC_Min_Max_Quantities;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Main plugin file.
 *
 * @since    1.1.0
 * @version  1.1.0
 * @package  WC_Min_Max_Quantities
 */
final class Plugin extends Framework\AbstractPlugin {

	/**
	 * Setup plugin.
	 *
	 * @return void
	 * @since 1.1.1
	 */
	public function setup() {
		// initialize the plugin.
		if ( ! self::is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			add_action( 'admin_notices', array( $this, 'dependency_notice' ) );

			return;
		}
		add_action( 'woocommerce_loaded', array( $this, 'init_plugin' ) );
	}

	/**
	 * Missing dependency notice.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function dependency_notice() {
		$notice = sprintf(
		/* translators: %s Plugin Name, %s Missing Plugin Name, %s Download URL link. */
			__( '%1$s requires %2$s to be installed and active. You can download %3$s from here.', 'wc-min-max-quantities' ),
			'<strong>' . $this->get_plugin_name() . '</strong>',
			'<strong>WooCommerce</strong>',
			'<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>'
		);
		echo wp_kses_post( '<div class="notice notice-error"><p>' . $notice . '</p></div>' );
	}

	/**
	 * Initializes the plugin.
	 *
	 * Plugins can override this to set up any handlers after WordPress is ready.
	 *
	 * @return void
	 * @since 1.1.1
	 */
	public function init_plugin() {
		include_once __DIR__ . '/class-install.php';
		include_once __DIR__ . '/class-helper.php';
		include_once __DIR__. '/class-cart-manager.php';

		if ( self::is_request( 'admin' ) ) {
			include_once __DIR__ . '/admin/class-admin-manager.php';
			include_once __DIR__ . '/admin/class-admin-settings.php';
			include_once __DIR__ . '/admin/class-admin-product.php';
		}

		do_action( 'wc_min_mx_quantities_loaded' );
	}
}

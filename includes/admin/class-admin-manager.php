<?php

namespace PluginEver\WooCommerceMinMaxQuantities;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Class Admin_Manager.
 *
 * @since 1.1.0
 * @package PluginEver\WooCommerceMinMaxQuantities
 */
class Admin_Manager {

	/**
	 * Construct Admin_Manager.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 55 );

	}

	/**
	 * Add menu item.
	 *
	 * @since 1.1.0
	 */
	public function settings_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Min Max Quantities Settings', 'wc-min-max-quantities' ),
			__( 'Min Max Quantities', 'wc-min-max-quantities' ),
			'manage_options',
			'wc-min-max-quantities-settings',
			array( Admin_Settings::class, 'output' )
		);
	}
}
return new Admin_Manager();

<?php

defined( 'ABSPATH' ) || exit;

class WC_MINMAX_Settings {
	private $settings_api;

	function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 99 );
	}

	public function admin_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'WooCommerce Min Max Quantities', 'wc-minmax-quantities' ),
			__( 'Min Max Quantities', 'wc-minmax-quantities' ),
			'manage_options',
			'wc-minmax-quantities',
			//array( $this, 'settings_page' )
			array( 'WC_Minmax_Quantites_Admin_Settings', 'output' )
		);
	}

}

new WC_MINMAX_Settings();

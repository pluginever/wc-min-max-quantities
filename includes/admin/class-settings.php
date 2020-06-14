<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class WC_MINMAX_Settings {

	private $settings_api;

	function __construct() {

		$this->settings_api = new \Ever_Settings_API();
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 99 );

	}

	function admin_init() {

		//set the settings
		$this->settings_api->set_sections( $this->get_settings_sections() );
		$this->settings_api->set_fields( $this->get_settings_fields() );

		//initialize settings
		$this->settings_api->admin_init();
	}

	function get_settings_sections() {

		$sections = array(

			array(
				'id'    => 'wc_minmax_quantity_general_settings',
				'title' => __( 'General Settings', 'wc-minmax-quantities' )
			),
			array(
				'id'    => 'wc_minmax_quantity_advanced_settings',
				'title' => __( 'Advanced Settings', 'wc-minmax-quantities' )
			),

			array(
				'id'    => 'wc_minmax_quantity_translate_settings',
				'title' => __( 'Translate Settings', 'wc-minmax-quantities' )
			)

		);

		return apply_filters( 'wc_minmax_quantity_settings_sections', $sections );
	}

	/**
	 * Returns all the settings fields
	 *
	 * @return array settings fields
	 */

	function get_settings_fields() {

		$settings_fields = array(

			'wc_minmax_quantity_general_settings' => array(

				array(
					'label' => __( 'Minimum Order Quantity', 'wc-minmax-quantities' ),
					'desc'  => __( 'Enter a quantity to prevent  user from buying this product if they have fewer than the allowed quantity in their cart.', 'wc-minmax-quantities' ),
					'name'  => 'min_product_quantity',
					'type'  => 'number',
					'min'   => 0,
				),
				array(
					'label' => __( 'Maximum Order Quantity', 'wc-minmax-quantities' ),
					'desc'  => __( 'Enter a quantity to prevent  user from buying this product if they have more than the allowed quantity in their cart.', 'wc-minmax-quantities' ),
					'name'  => 'max_product_quantity',
					'type'  => 'number',
					'min'   => 0,
				),
				array(
					'label' => __( 'Minimum Order Price', 'wc-minmax-quantities' ),
					'desc'  => __( 'Enter an amount of Price to prevent  users from buying, if they have lower than the allowed product price in their cart.', 'wc-minmax-quantities' ),
					'name'  => 'min_cart_price',
					'type'  => 'number',
					'min'   => 0,
				),
				array(
					'label' => __( 'Maximum Order Price', 'wc-minmax-quantities' ),
					'desc'  => __( 'Enter an amount of Price to prevent users from buying, if they have more than the allowed product price in their cart.', 'wc-minmax-quantities' ),
					'name'  => 'max_cart_price',
					'type'  => 'number',
					'min'   => 0,
				),
				array(
					'label'   => __( 'Hide Checkout Button', 'wc-minmax-quantities' ),
					'desc'    => __( 'Hide checkout button if Min/Max condition not passed.', 'wc-minmax-quantities' ),
					'name'    => 'hide_checkout',
					'type'    => 'checkbox',
					'default' => 'on',
				)
			),
			'wc_minmax_quantity_advanced_settings' => array(
				array(
					'label' => __( 'Minimum Cart Total', 'wc-minmax-quantities' ),
					'desc'  => __( 'Enter an amount of Price to prevent  users from buying, if they have lower than the allowed price in their cart total.', 'wc-minmax-quantities' ),
					'name'  => 'min_cart_total_price',
					'type'  => 'number',
					'min'   => 0,
				),
				array(
					'label' => __( 'Maximum Cart Total', 'wc-minmax-quantities' ),
					'desc'  => __( 'Enter an amount of Price to prevent users from buying, if they have more than the allowed price in their cart total.', 'wc-minmax-quantities' ),
					'name'  => 'max_cart_total_price',
					'type'  => 'number',
					'min'   => 0,
				),
			),
			'wc_minmax_quantity_translate_settings' => array(
				
				array(
					'label' => __( 'Minimum Order Quantity Error Message', 'wc-minmax-quantities' ),
					'desc'  => __( 'Must use %s twice to display minimum order quantity and product name', 'wc-minmax-quantities' ),
					'name'  => 'min_order_quantity_error_message',
					'type'  => 'text',
					'placeholder' 	=> 'You have to buy at least %s quantities of %s'
				),

				array(
					'label' => __( 'Maximum Order Quantity Error Message', 'wc-minmax-quantities' ),
					'desc'  => __( 'Must use %s twice to display maximum order quantity and product name', 'wc-minmax-quantities' ),
					'name'  => 'max_order_quantity_error_message',
					'type'  => 'text',
					'placeholder' 	=> 'You can\'t buy more than %s quantities of %s'
				),

				array(
					'label' => __( 'Minimum Order Price Error Message', 'wc-minmax-quantities' ),
					'desc'  => __( 'Must use %s twice to display minimum order price and product name', 'wc-minmax-quantities' ),
					'name'  => 'min_order_price_error_message',
					'type'  => 'text',
					'placeholder' 	=> 'Minimum total price should be %s or more for %s'
				),

				array(
					'label' => __( 'Maximum Order Price Error Message', 'wc-minmax-quantities' ),
					'desc'  => __( 'Must use %s twice to display maximum order price and product name.', 'wc-minmax-quantities' ),
					'name'  => 'max_order_price_error_message',
					'type'  => 'text',
					'placeholder' 	=> 'Maximum total price can not be more than %s for %s'
				),
				
				array(
					'label' => __( 'Minimum Cart Total Error Message', 'wc-minmax-quantities' ),
					'desc'  => __( 'Must use %s to display minimum cart total price', 'wc-minmax-quantities' ),
					'name'  => 'min_cart_total_error_message',
					'type'  => 'text',
					'placeholder' 	=> 'Minimum cart total price should be %s or more'
				),

				array(
					'label' => __( 'Maximum Cart Total Error Message', 'wc-minmax-quantities' ),
					'desc'  => __( 'Must use %s to display maximum cart total price', 'wc-minmax-quantities' ),
					'name'  => 'max_cart_total_error_message',
					'type'  => 'text',
					'placeholder' 	=> 'Maximum cart total price can not be more than %s'
				),
			),

			
			
		);

		return apply_filters( 'wc_minmax_quantity_settings_fields', $settings_fields );
	}

	function admin_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'WooCommerce Min Max Quantities', 'wc-minmax-quantities' ),
			__( 'Min Max Quantities', 'wc-minmax-quantities' ),
			'manage_options',
			'wc-minmax-quantities',
			array( $this, 'settings_page' )
		);
	}

	function settings_page() {

		echo '<div class="wrap">';
		echo sprintf( "<h2>%s</h2>", __( 'WC Min Max Settings', 'wc-minmax-quantities' ) );
		$this->settings_api->show_settings();
		echo '</div>';

	}

}

new WC_MINMAX_Settings();

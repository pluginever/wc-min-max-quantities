<?php

namespace Pluginever\WCMinMaxQuantities\Admin;

class Settings {

	public function __construct() {
		add_filter( 'woocommerce_get_settings_products', array( $this, 'min_max_all_settings' ), 10, 2 );
	}

	public function min_max_all_settings( $settings, $current_section ) {

		$fields = array();
		// Add Title to the Settings
		$fields[] = array( 'name' => __( 'Min/Max Quantities', 'wc-minmax-quantities' ), 'type' => 'title', 'id' => 'wc_minmax_quantities_simple' );
		// Add first checkbox option
		$fields[] = array(
			'name' => __( 'Minimum Order Quantity', 'wc-minmax-quantities' ),
			'id'   => 'min_product_quantity',
			'type' => 'number',

		);
		$fields[] = array(
			'name' => __( 'Maximum Order Quantity', 'wc-minmax-quantities' ),
			'id'   => 'max_product_quantity',
			'type' => 'number',

		);
		$fields[] = array(
			'name' => __( 'Minimum Order Value', 'wc-minmax-quantities' ),
			'id'   => 'min_cart_price',
			'type' => 'number',

		);
		$fields[] = array(
			'name' => __( 'Maximum Order Value', 'wc-minmax-quantities' ),
			'id'   => 'max_cart_price',
			'type' => 'number',
		);

		$fields[] = array( 'type' => 'sectionend', 'id' => 'wc_minmax_quantities_simple' );

		return $fields;
	}
}

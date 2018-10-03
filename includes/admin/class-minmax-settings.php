<?php

namespace Pluginever\WCMinMaxQuantities\Admin;

class Minmax_Settings{

	public function __construct() { 
       add_filter( 'woocommerce_get_settings_products', array($this, 'min_max_all_settings'), 10, 2 );
    }

    public function min_max_all_settings( $settings, $current_section ) {

		$settings_slider = array();
		// Add Title to the Settings
		$settings_slider[] = array( 'name' => __( 'Min/Max Quantities', 'wc-min-max-quantities' ), 'type' => 'title', 'id' => 'wc_min_max_quantities_simple' );
		// Add first checkbox option
		$settings_slider[] = array(
			'name'     => __( 'Minimum Order Quantity', 'wc-min-max-quantities' ),
			'id'       => 'min_product_quantity',
			'type'     => 'number',

		);
		$settings_slider[] = array(
			'name'     => __( 'Maximum Order Quantity', 'wc-min-max-quantities' ),
			'id'       => 'max_product_quantity',
			'type'     => 'number',

		);
		$settings_slider[] = array(
			'name'     => __( 'Minimum Order Value', 'wc-min-max-quantities' ),
			'id'       => 'min_cart_price',
			'type'     => 'number',

		);
		$settings_slider[] = array(
			'name'     => __( 'Maximum Order Value', 'wc-min-max-quantities' ),
			'id'       => 'max_cart_price',
			'type'     => 'number',
		);
		
		$settings_slider[] = array( 'type' => 'sectionend', 'id' => 'wc_min_max_quantities_simple' );
		return $settings_slider;
	}
}
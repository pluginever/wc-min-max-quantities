<?php

function wcmm_update_1_0_9() {

	$general_settings = get_option( 'wc_minmax_quantity_general_settings' );
	$advance_settings = get_option( 'wc_minmax_quantity_advanced_settings' );

	$min_product_quantity = isset( $general_settings['min_product_quantity'] ) ? $general_settings['min_product_quantity'] : false;
	update_option( 'wc_minmax_quantities_min_product_quantity', $min_product_quantity );

	$max_product_quantity = isset( $general_settings['max_product_quantity'] ) ? $general_settings['max_product_quantity'] : false;
	update_option( 'wc_minmax_quantities_max_product_quantity', $max_product_quantity );

	$min_product_price = isset( $general_settings['min_cart_price'] ) ? $general_settings['min_cart_price'] : false;
	update_option( 'wc_minmax_quantities_min_product_price', $min_product_price );

	$max_product_price = isset( $general_settings['max_cart_price'] ) ? $general_settings['max_cart_price'] : false;
	update_option( 'wc_minmax_quantities_max_product_price', $max_product_price );

	$hide_checkout = isset( $general_settings['hide_checkout'] ) ? $general_settings['hide_checkout'] : false;
	if ( $hide_checkout == 'on' ) {
		$hide_checkout = 'yes';
	}
	update_option( 'wc_minmax_quantities_hide_checkout', $hide_checkout );

	$min_cart_total_price = isset( $advance_settings['min_cart_total_price'] ) ? $advance_settings['min_cart_total_price'] : false;
	update_option( 'wc_minmax_quantities_min_cart_total_price', $min_cart_total_price );

	$max_cart_total_price = isset( $advance_settings['max_cart_total_price'] ) ? $advance_settings['max_cart_total_price'] : false;
	update_option( 'wc_minmax_quantities_max_cart_total_price', $max_cart_total_price );


	$force_add_minimum_quantity = isset( $advance_settings['force_add_minimum_quantity'] ) ? $advance_settings['force_add_minimum_quantity'] : false;
	if ( $force_add_minimum_quantity == 'on' ) {
		$force_add_minimum_quantity = 'yes';
	}
	update_option( 'wc_minmax_quantities_force_add_minimum_quantity', $force_add_minimum_quantity );

	$prevent_add_to_cart = isset( $advance_settings['prevent_add_to_cart'] ) ? $advance_settings['prevent_add_to_cart'] : false;
	if ( $prevent_add_to_cart == 'on' ) {
		$prevent_add_to_cart = 'yes';
	}
	update_option( 'wc_minmax_quantities_prevent_add_to_cart', $prevent_add_to_cart );

	$remove_item_checkout = isset( $advance_settings['remove_item_checkout'] ) ? $advance_settings['remove_item_checkout'] : false;
	if ( $remove_item_checkout == 'on' ) {
		$remove_item_checkout = 'yes';
	}
	update_option( 'wc_minmax_quantities_remove_item_checkout', $remove_item_checkout );

}

wcmm_update_1_0_9();

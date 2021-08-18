<?php
/**
 * This file will work for the updater script
 *
 * @package WCMinMax
 */

/**
 * Updates for version 1.1.0
 *
 * @since 1.1.0
 */
function wc_minmax_quantities_update_1_1_0() {

	if ( ! wp_next_scheduled( 'wc_minmax_quantities_hourly_event' ) ) {
		wp_schedule_event( time(), 'hourly', 'wc_minmax_quantities_hourly_event' );
	}

	if ( ! wp_next_scheduled( 'wc_minmax_quantities_daily_event' ) ) {
		wp_schedule_event( time(), 'daily', 'wc_minmax_quantities_daily_event' );
	}

	$general_settings  = get_option( 'wc_minmax_quantity_general_settings' );
	$advanced_settings = get_option( 'wc_minmax_quantity_advanced_settings' );

	$updated_settings = get_option( 'wc_minmax_settings' );

	$min_product_quantity = isset( $general_settings['min_product_quantity'] ) ? $general_settings['min_product_quantity'] : false;
	update_option( $updated_settings['wc_minmax_quantities_min_product_quantity'], $min_product_quantity );

	$max_product_quantity = isset( $general_settings['max_product_quantity'] ) ? $general_settings['max_product_quantity'] : false;
	update_option( $updated_settings['wc_minmax_quantities_max_product_quantity'], $max_product_quantity );

	$min_cart_price = isset( $general_settings['min_cart_price'] ) ? $general_settings['min_cart_price'] : false;
	update_option( $updated_settings['wc_minmax_quantities_min_product_price'], $min_cart_price );

	$max_cart_price = isset( $general_settings['max_cart_price'] ) ? $general_settings['max_cart_price'] : false;
	update_option( $updated_settings['wc_minmax_quantities_max_product_price'], $max_cart_price );

	$hide_checkout = isset( $general_settings['hide_checkout'] ) ? $general_settings['hide_checkout'] : false;
	if ( 'on' === $hide_checkout ) {
		$hide_checkout = 'yes';
	}
	update_option( $updated_settings['wc_minmax_quantities_hide_checkout'], $hide_checkout );

	$min_cart_total_price = isset( $advanced_settings['min_cart_total_price'] ) ? $advanced_settings['min_cart_total_price'] : false;
	update_option( $updated_settings['wc_minmax_quantities_min_cart_total_price'], $min_cart_total_price );

	$max_cart_total_price = isset( $advanced_settings['max_cart_total_price'] ) ? $advanced_settings['max_cart_total_price'] : false;
	update_option( $updated_settings['wc_minmax_quantities_max_cart_total_price'], $max_cart_total_price );

}


wc_minmax_quantities_update_1_1_0();

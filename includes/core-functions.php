<?php

//function prefix wc_min_max_quantities

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


/**
 * Get WC MIN MAX Quantities settings
 *
 * @param $key
 * @param bool $default
 * @param string $section
 *
 * @return bool|string|array
 */

function wc_minmax_quantities_get_settings( $key, $default = false, $section = 'wc_minmax_quantity_general_settings' ) {
	$settings = get_option( $section, [] );

	return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
}

/**
 * Get notice messages if min max condition check failed
 *
 * @param $args
 *
 * @return bool|string
 */

function wc_minmax_quantities_get_notice_message( $args ) {

	extract( wp_parse_args( $args, array(
		'type'      => 'min_qty',
		'min_qty'   => false,
		'max_qty'   => false,
		'min_price' => false,
		'max_price' => false,
		'name'      => false,
	) ) );

	switch ( $type ) {
		case 'min_qty':
			return sprintf( __( "You have to buy at least %s quantities of %s", 'wc-minmax-quantities' ), $min_qty, $name );
		case 'max_qty':
			return sprintf( __( "You can't buy more than %s quantities of %s", 'wc-minmax-quantities' ), $max_qty, $name );
		case 'min_price':
			return sprintf( __( "Minimum cart total price should be %s or more", 'wc-minmax-quantities' ), wc_price( $min_price ) );
		case 'max_price':
			return sprintf( __( "Maximum cart total price can not be more than %s ", 'wc-minmax-quantities' ), wc_price( $max_price ) );
		default:
			return false;
	}
}

/**
 * Check min max rules to proceed checkout
 *
 * @since 3.1.3
 */

function wc_min_max_quantities_proceed_to_checkout_conditions() {

	$checkout_url = wc_get_checkout_url();

	global $woocommerce;

	$total_quantity = $woocommerce->cart->cart_contents_count;
	$total_amount   = floatval( WC()->cart->cart_contents_total );


	$items = WC()->cart->get_cart();

	foreach ( $items as $item ) {

		$product_id    = $item['product_id'];
		$qty           = $item['quantity'];
		$product_name  = $item['data']->get_title();
		$ignore_global = get_post_meta( $product_id, '_minmax_ignore_global', true );

		if ( $ignore_global == 'yes' ) {
			$min_quantity = (int) get_post_meta( $product_id, '_minmax_product_min_quantity', true );
			$max_quantity = (int) get_post_meta( $product_id, '_minmax_product_max_quantity', true );
			$min_price    = (int) get_post_meta( $product_id, '_minmax_product_min_price', true );
			$max_price    = (int) get_post_meta( $product_id, '_minmax_product_max_price', true );
		} else {
			$min_quantity = wc_minmax_quantities_get_settings( 'min_product_quantity', 0 );
			$max_quantity = wc_minmax_quantities_get_settings( 'max_product_quantity', 0 );
			$min_price    = wc_minmax_quantities_get_settings( 'min_cart_price', 0 );
			$max_price    = wc_minmax_quantities_get_settings( 'max_cart_price', 0 );
		}

		//=== Check minimum quantity ===
		if ( ! empty( $min_quantity ) ) {
			if ( $qty < $min_quantity ) {
				wc_add_notice( wc_minmax_quantities_get_notice_message( array(
					'type'    => 'min_qty',
					'min_qty' => $min_quantity,
					'name'    => $product_name,
				) ), 'error' );
			}
		}

		//=== Check maximum quantity ===
		if ( ! empty( $max_quantity ) ) {
			if ( $qty > $max_quantity ) {
				wc_add_notice( wc_minmax_quantities_get_notice_message( array(
					'type'    => 'max_qty',
					'max_qty' => $max_quantity,
					'name'    => $product_name,
				) ), 'error' );
			}
		}

		//=== Check minimum Price ===
		if ( ! empty( $min_price ) && $total_amount < $min_price ) {
			wc_add_notice( wc_minmax_quantities_get_notice_message( array(
				'type'      => 'min_price',
				'min_price' => $min_price,
				'name'      => $product_name,
			) ), 'error' );
		}

		//=== Check maximum Price ===
		if ( ! empty( $max_price ) && $total_amount > $max_price ) {
			wc_add_notice( wc_minmax_quantities_get_notice_message( array(
				'type'      => 'max_price',
				'max_price' => $max_price,
				'name'      => $product_name,
			) ), 'error' );
		}

		if ( empty( $total_quantity ) || empty( $total_amount ) ) {
			return;
		}

	}
}

add_action( 'woocommerce_check_cart_items', 'wc_min_max_quantities_proceed_to_checkout_conditions' );



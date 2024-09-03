<?php

use WooCommerceMinMaxQuantities\Plugin;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

/**
 * Get the plugin instance.
 *
 * @since 1.0.0
 * @return \WooCommerceMinMaxQuantities\Plugin
 */
function wc_min_max_quantities() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return Plugin::instance();
}

/**
 * Check if the product is excluded from min/max quantity.
 *
 * @param int $product_id Product ID.
 * @param int $variation_id Variation ID.
 *
 * @return bool
 */
function wcmmq_is_product_excluded( $product_id, $variation_id = 0 ) {
	if ( ! is_null( $variation_id ) && get_post_meta( $variation_id, '_wcmmq_disable', true ) === 'yes' ) {
		return true;
	}

	return 'yes' === get_post_meta( $product_id, '_wcmmq_disable', true );
}

/**
 * Get product level limits.
 *
 * @param int $product_id Product ID.
 * @param int $variation_id Variation ID.
 *
 * @return array|bool Array of limits or false if no limits.
 */
function wcmmq_get_product_limits( $product_id, $variation_id = 0 ) {
	$limits = wp_cache_get( "wcmmq-{$product_id}-{$variation_id}", 'wc-min-max-quantities' );
	if ( false === $limits ) {
		$product = wc_get_product( $product_id );
		// If the product lever overrides are enabled, use them otherwise use the global settings.
		$override = 'yes' === get_post_meta( $product->get_id(), '_wcmmq_enable', true );
		if ( $override ) {
			$limits = array(
				'step'    => (int) get_post_meta( $product->get_id(), '_wcmmq_step', true ),
				'min_qty' => (int) get_post_meta( $product->get_id(), '_wcmmq_min_qty', true ),
				'max_qty' => (int) get_post_meta( $product->get_id(), '_wcmmq_max_qty', true ),
				'rule'    => 'product',
			);
		} else {
			$limits = array(
				'step'    => (int) get_option( 'wcmmq_step', 1 ),
				'min_qty' => (int) get_option( 'wcmmq_min_qty', 1 ),
				'max_qty' => (int) get_option( 'wcmmq_max_qty', 0 ),
				'rule'    => 'global',
			);
		}
		$limits['min_total'] = 0;
		$limits['max_total'] = 0;
		$limits              = apply_filters( 'wc_min_max_quantities_product_limits', $limits, $product_id, $variation_id );
		wp_cache_set( "wcmmq-{$product_id}-{$variation_id}", $limits, 'wc-min-max-quantities' );
	}

	return $limits;
}

/**
 * Get cart level limits.
 *
 * @return array Array of limits.
 */
function wcmmq_get_cart_limits() {
	$limits = apply_filters(
		'wc_min_max_quantities_cart_limits',
		array(
			'min_qty'   => (int) get_option( 'wcmmq_min_cart_qty' ),
			'max_qty'   => (int) get_option( 'wcmmq_max_cart_qty' ),
			'min_total' => (int) get_option( 'wcmmq_min_cart_total' ),
			'max_total' => (int) get_option( 'wcmmq_max_cart_total' ),
		)
	);

	return $limits;
}

/**
 * Get product categories.
 *
 * @param int $product_id Product id.
 *
 * @return array
 */
function wcmmq_get_product_categories( $product_id ) {
	$category_ids = wp_cache_get( "wcmmq-{$product_id}-categories", 'wc-min-max-quantities' );
	if ( false === $category_ids ) {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return array();
		}
		$category_ids      = $product->get_category_ids();
		$parent_categories = array();
		$child_categories  = array();

		foreach ( $category_ids as $category_id ) {
			$category = get_term( $category_id );
			if ( empty( $category->parent ) ) {
				$parent_categories[] = $category_id;
			} else {
				$child_categories[] = $category_id;
			}
		}

		$category_ids = array_merge( $child_categories, $parent_categories );
		wp_cache_add( "wcmmq-{$product_id}-categories", $category_ids, 'wc-min-max-quantities' );
	}

	return $category_ids;
}

/**
 * Return cart quantity for specified product.
 *
 * @param integer $product_id The product ID.
 * @param boolean $is_variation Check if is a variation.
 *
 * @since  1.0.0
 * @return int
 */
function wcmmq_get_cart_quantity( $product_id, $is_variation = false ) {
	$items = WC()->cart->get_cart();
	$qty   = 0;

	foreach ( $items as $item_id => $item ) {

		if ( $is_variation && (int) $item['variation_id'] === (int) $product_id ) {
			return $item['quantity'];
		}

		if ( (int) $item['product_id'] === (int) $product_id ) {
			$qty += $item['quantity'];
		}
	}

	return $qty;
}

/**
 * Get cart total.
 *
 * @param integer $product_id The product ID.
 * @param boolean $is_variation Check if is a variation.
 *
 * @since  1.0.0
 * @return int
 */
function wcmmq_get_cart_total( $product_id, $is_variation = false ) {
	// Get the cart total for the product.
	$items = WC()->cart->get_cart();
	$total = 0;

	foreach ( $items as $item_id => $item ) {
		if ( $is_variation && (int) $item['variation_id'] === (int) $product_id ) {
			return $item['line_total'];
		}

		if ( (int) $item['product_id'] === (int) $product_id ) {
			$total += $item['line_total'];
		}
	}

	return $total;
}

/**
 * Check if the product supports min/max allow combination.
 *
 * @param int $product_id Product ID.
 *
 * @return bool
 */
function wcmmq_is_allow_combination( $product_id ) {
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return false;
	}

	$allow = 'yes' === $product->get_meta( '_wcmmq_allow_combination' );
	return apply_filters( 'wc_min_max_quantities_allow_combination', $allow, $product_id );
}

/**
 * Add a notice to the cart.
 *
 * @param string $message The message to display.
 * @param string $type The type of notice.
 *
 * @since 1.0.0
 * @return void
 */
function wcmmq_add_cart_notice( $message, $type = 'error' ) {
	if ( ! empty( $message ) && ! wc_has_notice( $message, $type ) ) {
		wc_add_notice( $message, $type, array( 'source' => 'wcmmq' ) );
	}
}

<?php
/**
 * Helper function.
 *
 * @since 1.1.0
 * @package PluginEver\WooCommerce\WCMinMaxQuantities
 */

namespace PluginEver\WooCommerce\WCMinMaxQuantities;

// don't call the file directly
defined( 'ABSPATH' ) || exit();

/**
 * Class Helper
 *
 * @since 1.1.0
 * @package PluginEver\WooCommerce\WCMinMaxQuantities
 */
class Helper {
	/**
	 * Add an error.
	 *
	 * @param string $error Error text.
	 *
	 * @since 1.0.0
	 */
	public static function add_error( $error = '' ) {
		if ( $error && ! wc_has_notice( $error, 'error' ) ) {
			wc_add_notice( $error, 'error', array( 'source' => 'wc-min-max-quantities' ) );
		}
	}

	/**
	 * Return cart quantity for specified product.
	 *
	 * @param integer $product_id The product ID.
	 * @param boolean $is_variation Check if is a variation.
	 *
	 * @return int
	 * @since  1.0.0
	 */
	public static function get_cart_item_qty( $product_id, $is_variation = false ) {
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
}

<?php
/**
 * Helper function.
 *
 * @since 1.1.0
 * @package PluginEver\WC_Min_Max_Quantities
 */

namespace PluginEver\WC_Min_Max_Quantities;

// don't call the file directly
defined( 'ABSPATH' ) || exit();

/**
 * Class Helper
 *
 * @since 1.1.0
 * @package PluginEver\WC_Min_Max_Quantities
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
	 * Get product limits.
	 *
	 * @param int $product_id Product Id.
	 * @param int $variation_id Variation Id.
	 *
	 * @return array
	 */
	public static function get_product_limits( $product_id, $variation_id = 0 ) {
		$limits = array(
			'step'    => 1,
			'min_qty' => 0,
			'max_qty' => 0,
		);
		$key              = "min-max-{$product_id}-{$variation_id}";
		if( false === wp_cache_get($key) ){
			$product          = wc_get_product( $product_id );
			$product_override = 'yes' === get_post_meta( $product->get_id(), '_minmax_quantities_override', true );

			if ( $product_override ) {
				$limits['step']    = (int)$product->get_meta( '_minmax_quantities_step' );
				$limits['min_qty'] = (int)$product->get_meta( '_minmax_quantities_min_qty' );
				$limits['max_qty'] = (int)$product->get_meta( '_minmax_quantities_max_qty' );
			} else {
				$limits['step']    = (int)Plugin::instance()::get_option( 'wc_min_max_quantities_settings[product_quantity_step]', 0 );
				$limits['min_qty'] = (int)Plugin::instance()::get_option( 'wc_min_max_quantities_settings[min_product_quantity]', 0 );
				$limits['max_qty'] = (int)Plugin::instance()::get_option( 'wc_min_max_quantities_settings[max_product_quantity]', 0 );
			}

			$limits = apply_filters( 'wc_min_max_quantities_product_limits', $limits, $product_id, $variation_id );
			wp_cache_add( $key, $limits, 'wc-min-max-quantities');
		}

		return $limits;
	}

	/**
	 * Get product categories.
	 *
	 * @param int $product_id Product id.
	 *
	 * @return int[]
	 */
	public static function get_product_categories( $product_id ) {
		$terms      = wp_list_pluck( get_the_terms( $product_id, 'product_cat' ), 'term_id' );
		$categories = [];
		foreach ( $terms as $term_id ) {
			$categories[] = $term_id;
			$parents      = get_ancestors( $term_id, 'product_cat' );
			foreach ( $parents as $parent ) {
				$categories[] = $parent;
			}
		}

		return array_unique( array_filter( $categories ) );
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

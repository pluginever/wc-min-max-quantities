<?php
/**
 * WC_Min_Max_Quantities Helper functions handlers
 *
 * @version  1.1.0
 * @since    1.1.0
 * @package  WC_Min_Max_Quantities
 */

namespace WC_Min_Max_Quantities;

defined( 'ABSPATH' ) || exit();

/**
 * Helper class.
 */
class Helper {
	/**
	 * Log messages.
	 *
	 * @param mixed $message Log message.
	 *
	 * @since 1.1.0
	 */
	public static function log( $message ) {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		if ( ! is_string( $message ) ) {
			$message = var_export( $message, true );
		}

		error_log( $message );
	}

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
	 * @param int $product_id Product ID.
	 * @param int $variation_id Variation ID.
	 *
	 * @return array
	 */
	public static function get_product_limits( $product_id, $variation_id = 0 ) {
		$key = "wc-min-max-{$product_id}-{$variation_id}";
		if ( self::is_product_excluded( $product_id, $variation_id ) ) {
			return array(
				'step'      => 0,
				'min_qty'   => 0,
				'max_qty'   => 0,
				'min_total' => 0,
			);
		}

		$limits = wp_cache_get( $key );
		if ( false === $limits ) {
			$product  = wc_get_product( $product_id );
			$override = 'yes' === get_post_meta( $product->get_id(), '_wc_min_max_quantities_override', true );
			if ( $override ) {
				$limits['step']    = (int) $product->get_meta( '_wc_min_max_quantities_step' );
				$limits['min_qty'] = (int) $product->get_meta( '_wc_min_max_quantities_min_qty' );
				$limits['max_qty'] = (int) $product->get_meta( '_wc_min_max_quantities_max_qty' );
			} else {
				$limits['step']    = (int) Plugin::get( 'settings' )->get_option( 'general_product_quantity_step' );
				$limits['min_qty'] = (int) Plugin::get( 'settings' )->get_option( 'general_min_product_quantity' );
				$limits['max_qty'] = (int) Plugin::get( 'settings' )->get_option( 'general_max_product_quantity' );
			}

			$limits = apply_filters( 'wc_min_max_quantities_product_limits', $limits, $product_id, $variation_id );
			wp_cache_add( $key, $limits, 'wc-min-max-quantities' );
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
	 * @since  1.0.0
	 * @return int
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

	/**
	 * Check if the product is excluded from min/max quantity.
	 *
	 * @param int $product_id Product ID.
	 * @param int $variation_id Variation ID.
	 *
	 * @return bool
	 */
	public static function is_product_excluded( $product_id, $variation_id = null ) {
		if ( $variation_id !== null && 'yes' === get_post_meta( $variation_id, '_wc_min_max_quantities_excluded', true ) ) {
			return true;
		}

		return 'yes' === get_post_meta( $product_id, '_wc_min_max_quantities_excluded', true );
	}

	/**
	 * Check if the product supports min/max allow combination.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return bool
	 */
	public static function is_allow_combination( $product_id ) {
		return apply_filters( 'wc_min_max_quantities_allow_combination', false, $product_id );
	}
}

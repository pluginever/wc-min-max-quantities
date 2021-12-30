<?php
/**
 * WC_Min_Max_Quantities Helper functions handlers
 *
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
	 * Format a case to lower case with specified dashes
	 *
	 * @param string|array $string String to format.
	 * @param string $separator Separator.
	 *
	 * @since 1.1.0
	 * @return string Formatted String.
	 */
	public static function dasherize( $string, $separator = '-' ) {
		if ( is_array( $string ) ) {
			$string = implode( $string, $separator );
		}
		if ( preg_match( '#\w+[_-]\w+#', $string ) ) {
			$string = preg_replace( '#[_-]#', ' ', $string );
		}
		$string = preg_split( '#\s+#', trim( $string ) );

		return strtolower( implode( $separator, $string ) );
	}

	/**
	 * Converts a string (e.g. 'yes' or 'no') to a bool.
	 *
	 * @param string|bool $string String to convert. If a bool is passed it will be returned as-is.
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	public static function string_to_bool( $string ) {
		return is_bool( $string ) ? $string : ( 'yes' === strtolower( $string ) || 1 === $string || 'true' === strtolower( $string ) || '1' === $string );
	}

	/**
	 * Converts a bool to a 'yes' or 'no'.
	 *
	 * @param bool|string $bool Bool to convert. If a string is passed it will first be converted to a bool.
	 *
	 * @since 1.1.0
	 * @return string
	 */
	public static function bool_to_string( $bool ) {
		if ( ! is_bool( $bool ) ) {
			$bool = self::string_to_bool( $bool );
		}

		return true === $bool ? 'yes' : 'no';
	}

	/**
	 * Get the path to the plugin file relative to the plugins' directory from the plugin slug.
	 *
	 * E.g. 'wc-serial-numbers' returns 'wc-serial-numbers/wc-serial-numbers.php'
	 *
	 * @param string $slug Plugin slug to get path for.
	 *
	 * @since 1.1.0
	 * @return string|false
	 */
	public static function get_plugin_path_from_slug( $slug ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$plugins = get_plugins();

		if ( strpos( $slug, '/' ) !== false ) {
			// The slug is already a plugin path.
			return $slug;
		}

		foreach ( $plugins as $plugin_path => $data ) {
			$path_parts = explode( '/', $plugin_path );
			if ( $path_parts[0] === $slug ) {
				return $plugin_path;
			}
		}

		return false;
	}

	/**
	 * Checks if a plugin is installed.
	 *
	 * @param string $plugin Path to the plugin file relative to the plugins directory or the plugin directory name.
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	public static function is_plugin_installed( $plugin ) {
		$plugin_path = self::get_plugin_path_from_slug( $plugin );

		return $plugin_path && array_key_exists( $plugin_path, get_plugins() );
	}

	/**
	 * Checks if a plugin is active.
	 *
	 * @param string $plugin Path to the plugin file relative to the plugins directory or the plugin directory name.
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	public static function is_plugin_active( $plugin ) {
		$plugin_path = self::get_plugin_path_from_slug( $plugin );

		return $plugin_path && in_array( $plugin_path, get_option( 'active_plugins', array() ), true );
	}

	/**
	 * Get a setting from the settings API.
	 *
	 * @param string $key Option name.
	 * @param mixed $default Default value.
	 *
	 * @since 1.1.0
	 * @return mixed
	 */
	public static function get_option( $key, $default = '' ) {
		if ( empty( $key ) ) {
			return $default;
		}

		// Get value.
		$option_values = get_option( 'wc_min_max_quantities_settings', array() );
		if ( isset( $option_values[ $key ] ) ) {
			$option_value = $option_values[ $key ];
		} else {
			$option_value = null;
		}

		if ( is_array( $option_value ) ) {
			$option_value = wp_unslash( $option_value );
		} elseif ( ! is_null( $option_value ) ) {
			$option_value = stripslashes( $option_value );
		}

		return ( null === $key ) ? $default : $option_value;
	}

	/**
	 * Update option.
	 *
	 * @param string $key Option name.
	 * @param mixed $option_value Option value.
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	public static function update_option( $key, $option_value ) {
		if ( empty( $key ) ) {
			return false;
		}
		if ( is_array( $option_value ) ) {
			$option_value = wp_slash( $option_value );
		} elseif ( ! is_null( $option_value ) ) {
			$option_value = addslashes( $option_value );
		}

		$option_values         = get_option( 'wc_min_max_quantities_settings', array() );
		$option_values[ $key ] = $option_value;

		return update_option( 'wc_min_max_quantities_settings', $option_values );
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
		$limits = array(
			'step'    => 1,
			'min_qty' => 0,
			'max_qty' => 0,
		);
		$key    = "min-max-{$product_id}-{$variation_id}";
		if ( false === wp_cache_get( $key ) ) {
			$product          = wc_get_product( $product_id );
			$product_override = 'yes' === get_post_meta( $product->get_id(), '_minmax_quantities_override', true );

			if ( $product_override ) {
				$limits['step']    = (int) $product->get_meta( '_minmax_quantities_step' );
				$limits['min_qty'] = (int) $product->get_meta( '_minmax_quantities_min_qty' );
				$limits['max_qty'] = (int) $product->get_meta( '_minmax_quantities_max_qty' );
			} else {
				$limits['step']    = (int) self::get_option( 'product_quantity_step', 0 );
				$limits['min_qty'] = (int) self::get_option( 'min_product_quantity', 0 );
				$limits['max_qty'] = (int) self::get_option( 'max_product_quantity', 0 );
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
}

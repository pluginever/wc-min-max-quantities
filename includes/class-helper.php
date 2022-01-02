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
}

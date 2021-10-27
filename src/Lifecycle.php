<?php


namespace PluginEver\WC_Min_Max_Quantities;

use \ByteEver\PluginFramework\v1_0_0 as Framework;

class Lifecycle extends Framework\Lifecycle {
	/**
	 * Updates and callbacks that need to be run per version.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $updates = array(
		'1.0.8',
		'1.1.0',
	);

	/**
	 * Performs any install tasks.
	 *
	 * @since 1.0.0
	 */
	public function install() {
		$this->set_installed_version( $this->get_plugin()->get_version() );
		update_option( $this->get_plugin()->get_option_key( 'install_date' ), current_time( 'mysql' ) );
	}

	/**
	 * Upgrade to 1.0.8
	 */
	public function upgrade_to_1_0_8() {
		wp_clear_scheduled_hook( 'wcsn_per_minute_event' );
		wp_clear_scheduled_hook( 'wcsn_daily_event' );
		wp_clear_scheduled_hook( 'wcsn_hourly_event' );

		if ( ! wp_next_scheduled( 'wc_minmax_quantities_hourly_event' ) ) {
			wp_schedule_event( time(), 'hourly', 'wc_minmax_quantities_hourly_event' );
		}

		if ( ! wp_next_scheduled( 'wc_minmax_quantities_daily_event' ) ) {
			wp_schedule_event( time(), 'daily', 'wc_minmax_quantities_daily_event' );
		}

		$general_settings = get_option( 'wc_minmax_quantity_general_settings' );
		$advnace_settings = get_option( 'wc_minmax_quantity_advanced_settings' );

		$min_product_quantity = isset( $general_settings['min_product_quantity'] ) ? $general_settings['min_product_quantity'] : false;
		update_option( 'wc_minmax_quantities_min_product_quantity', $min_product_quantity );

		$max_product_quantity = isset( $general_settings['max_product_quantity'] ) ? $general_settings['max_product_quantity'] : false;
		update_option( 'wc_minmax_quantities_max_product_quantity', $max_product_quantity );

		$min_cart_price = isset( $general_settings['min_cart_price'] ) ? $general_settings['min_cart_price'] : false;
		update_option( 'wc_minmax_quantities_min_product_price', $min_cart_price );

		$max_cart_price = isset( $general_settings['max_cart_price'] ) ? $general_settings['max_cart_price'] : false;
		update_option( 'wc_minmax_quantities_max_product_price', $max_cart_price );

		$hide_checkout = isset( $general_settings['hide_checkout'] ) ? $general_settings['hide_checkout'] : false;
		if ( $hide_checkout === 'on' ) {
			$hide_checkout = 'yes';
		}
		update_option( 'wc_minmax_quantities_hide_checkout', $hide_checkout );

		$min_cart_total_price = isset( $advnace_settings['min_cart_total_price'] ) ? $advnace_settings['min_cart_total_price'] : false;
		update_option( 'wc_minmax_quantities_min_cart_total_price', $min_cart_total_price );

		$max_cart_total_price = isset( $advnace_settings['max_cart_total_price'] ) ? $advnace_settings['max_cart_total_price'] : false;
		update_option( 'wc_minmax_quantities_max_cart_total_price', $max_cart_total_price );
	}

	/**
	 * Upgrade to 1.1.0
	 */
	public function upgrade_to_1_1_0() {
		global $wpdb;
		// Simple product.
		$products_query = $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = %s", 'product' );
		$products       = $wpdb->get_results( $products_query );
		foreach ( $products as $single ) {
			$_minmax_product_min_quantity = get_post_meta( $single, '_minmax_product_min_quantity', true );
			$_minmax_product_max_quantity = get_post_meta( $single, '_minmax_product_max_quantity', true );
			$_minmax_ignore_global        = get_post_meta( $single, '_minmax_ignore_global', true );

			update_post_meta( $single, '_minmax_quantities_min_qty', $_minmax_product_min_quantity );
			update_post_meta( $single, '_minmax_quantities_max_qty', $_minmax_product_max_quantity );
			update_post_meta( $single, '_minmax_quantities_override', $_minmax_ignore_global );
		}

		$general_settings  = get_option( 'wc_minmax_quantity_general_settings' );
		$advanced_settings = get_option( 'wc_minmax_quantity_advanced_settings' );

		$new_settings = get_option( 'wc_min_max_quantities_settings' );

		$min_product_quantity = isset( $general_settings['min_product_quantity'] ) ? $general_settings['min_product_quantity'] : 0;
		update_option( $new_settings['min_product_quantity'], $min_product_quantity );

		$max_product_quantity = isset( $general_settings['max_product_quantity'] ) ? $general_settings['max_product_quantity'] : 0;
		update_option( $new_settings['max_product_quantity'], $max_product_quantity );

		$min_cart_total_price = isset( $advanced_settings['min_cart_total_price'] ) ? $advanced_settings['min_cart_total_price'] : 0;
		update_option( $new_settings['min_order_amount'], $min_cart_total_price );

		$max_cart_total_price = isset( $advanced_settings['max_cart_total_price'] ) ? $advanced_settings['max_cart_total_price'] : 0;
		update_option( $new_settings['max_order_amount'], $max_cart_total_price );
	}
}

<?php

namespace WooCommerceMinMaxQuantities\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Admin
 *
 * @package WooCommerceMinMaxQuantities\Admin
 * @since 1.1.4
 */
class Actions {

	/**
	 * Actions constructor.
	 *
	 * @since 1.1.4
	 */
	public function __construct() {
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_product_meta' ) );
	}

	/**
	 * Save meta fields.
	 *
	 * @param int $post_id product ID.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public static function save_product_meta( $post_id ) {
		$product        = wc_get_product( $post_id );
		$numeric_fields = array(
			'_wcmmq_min_qty',
			'_wcmmq_max_qty',
			'_wcmmq_step',
		);
		foreach ( $numeric_fields as $numeric_field ) {
			$value = isset( $_POST[ $numeric_field ] ) ? floatval( wp_unslash( $_POST[ $numeric_field ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$product->update_meta_data( $numeric_field, (float) $value );
		}

		$check_fields = array(
			'_wcmmq_disable',
			'_wcmmq_enable',
		);
		foreach ( $check_fields as $check_field ) {
			$value = isset( $_POST[ $check_field ] ) ? true : false;
			$product->update_meta_data( $check_field, empty( $value ) ? 'no' : 'yes' );
		}

		$product->save();
	}
}

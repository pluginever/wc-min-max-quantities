<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


function wc_min_max_quantities_add_product_data_tab() {
	woocommerce_wp_text_input(
		array(
			'id'    => '_minmax_product_min_quantity',
			'label' => __( 'Product Min Quantity', 'wc-minmax-quantities' ),
			'type'  => 'number',
			'min'   => '0',
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'    => '_minmax_product_max_quantity',
			'label' => __( 'Product Max Quantity', 'wc-minmax-quantities' ),
			'type'  => 'number',
			'min'   => '0',
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'    => '_minmax_product_min_price',
			'label' => __( 'Product Min Price', 'wc-minmax-quantities' ),
			'type'  => 'number',
			'min'   => '0',
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'    => '_minmax_product_max_price',
			'label' => __( 'Product Max Price', 'wc-minmax-quantities' ),
			'type'  => 'number',
			'min'   => '0',
		)
	);

	woocommerce_wp_checkbox(
		array(
			'id'      => '_minmax_ignore_global',
			'label'   => __( 'Ignore Global Min-Max Rules', 'wc-minmax-quantities' ),
			'default' => '0',
		)
	);
}

add_action( 'woocommerce_product_options_inventory_product_data', 'wc_min_max_quantities_add_product_data_tab' );

/**
 * Save product min max meta settings
 *
 * @since 3.1.3
 *
 * @return bool|null
 */

function wc_min_max_quantities_save_product_data_tab( $post_id ) {
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		return false;
	}

	$min_quantity  = ! empty( $_REQUEST['_minmax_product_min_quantity'] ) ? intval( $_REQUEST['_minmax_product_min_quantity'] ) : 0;
	$max_quantity  = ! empty( $_REQUEST['_minmax_product_max_quantity'] ) ? intval( $_REQUEST['_minmax_product_max_quantity'] ) : 0;
	$min_price     = ! empty( $_REQUEST['_minmax_product_min_price'] ) ? intval( $_REQUEST['_minmax_product_min_price'] ) : 0;
	$max_price     = ! empty( $_REQUEST['_minmax_product_max_price'] ) ? intval( $_REQUEST['_minmax_product_max_price'] ) : 0;
	$ignore_global = ! empty( $_REQUEST['_minmax_ignore_global'] ) ? sanitize_key( $_REQUEST['_minmax_ignore_global'] ) : 'no';

	update_post_meta( $post_id, '_minmax_product_min_quantity', $min_quantity );
	update_post_meta( $post_id, '_minmax_product_max_quantity', $max_quantity );
	update_post_meta( $post_id, '_minmax_product_min_price', $min_price );
	update_post_meta( $post_id, '_minmax_product_max_price', $max_price );
	update_post_meta( $post_id, '_minmax_ignore_global', $ignore_global );
}

add_action( 'save_post_product', 'wc_min_max_quantities_save_product_data_tab' );

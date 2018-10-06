<?php

namespace Pluginever\WCMinMaxQuantities\Admin;
class MetaBox {
	public function __construct() {
		add_action( 'woocommerce_product_options_inventory_product_data', array( $this, 'add_product_data_tab' ) );
		add_action( 'save_post_product', array( $this, 'save_product_data_tab' ), 10, 1 );
	}

	public function add_product_data_tab( $product_data_tabs ) {
		woocommerce_wp_text_input(
			array(
				'id'       => '_minmax_product_min_quantity',
				'label'    => __( 'Product Minimum Quantity', 'wc-minmax-quantities' ),
				'type'     => 'number',
				'min'      => '0',
				'desc_tip' => 'true',

				'description' => __( 'Enter a quantity to prevent  user from buying this product if they have fewer than the allowed quantity in their cart.', 'wc-minmax-quantities' ),

			)
		);
		woocommerce_wp_text_input(
			array(
				'id'          => '_minmax_product_max_quantity',
				'label'       => __( 'Product Maximum Quantity', 'wc-minmax-quantities' ),
				'type'        => 'number',
				'desc_tip'    => 'true',
				'description' => __( 'Enter a quantity to prevent  user from buying this product if they have more than the allowed quantity in their cart.', 'wc-minmax-quantities' ),
			)
		);
		woocommerce_wp_checkbox(
			array(
				'id'          => '_minmax_ignore_global',
				'label'       => __( 'Ignore Global Rules', 'wc-minmax-quantities' ),
				'default'     => '0',
				'desc_tip'    => 'true',
				'description' => __( 'Exclude this product from global minimum/maximum order quantity/value rules.',
					'wc-minmax-quantities' ),
			)
		);

	}

	public function save_product_data_tab( $product_id ) {
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return false;
		}

		$product_min_q = isset( $_POST['_minmax_product_min_quantity'] ) ? intval( $_POST['_minmax_product_min_quantity'] ) : 0;
		$product_max_q = isset( $_POST['_minmax_product_max_quantity'] ) ? intval( $_POST['_minmax_product_max_quantity'] ) : 0;
		$ignore_global = isset( $_POST['_minmax_ignore_global'] ) ? esc_attr( $_POST['_minmax_ignore_global'] ) : 'no';

		update_post_meta( $product_id, '_minmax_product_min_quantity', $product_min_q );
		update_post_meta( $product_id, '_minmax_product_max_quantity', $product_max_q );
		update_post_meta( $product_id, '_minmax_ignore_global', $ignore_global );
	}
}

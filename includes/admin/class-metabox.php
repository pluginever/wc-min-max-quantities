<?php

namespace Pluginever\WCMinMaxQuantities\Admin;
class MetaBox {
	public function __construct(){
		add_action( 'woocommerce_product_options_inventory_product_data', array($this, 'add_product_data_tab' ) );
		add_action( 'save_post', array($this, 'save_product_data_tab' ) );
	}

	public function add_product_data_tab( $product_data_tabs ) { 
		woocommerce_wp_text_input(
			array(
				'id'          => 'simple_product_min_quantity',
				'label'       => __( 'Product Minimum Quantity', 'wc-min-max-quantities' ),
				'type'        => 'number',
				'min'		  => '0',
				'desc_tip'    => 'true',
				'description' => __('Enter a quantity to prevent  user from buying this product if the have fewer 					than the allowed quantity in their cart.', 'wc-min-max-quantities'),
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'          => 'simple_product_max_quantity',
				'label'       => __( 'Product Maximum Quantity', 'wc-min-max-quantities' ),
				'type'        => 'number',
				'desc_tip'    => 'true',
				'description' => __('Enter a quantity to prevent  user from buying this product if the have more					than the allowed quantity in their cart.', 'wc-min-max-quantities'),
			)
		);
		woocommerce_wp_checkbox(
			array(
				'id'          => 'check_status',
				'label'       => __( 'Ignore Global Min/Max Rules', 'wc-min-max-quantities' ),
				'default'     => '0',
				'desc_tip'    => 'true',
				'description' => __('Exclude this product from minimum order quantity/value rules.',
									'wc-min-max-quantities'),
			)
		);
		
	}

	public function save_product_data_tab(){
		global $product;
		$product_id       = get_the_id($product);
		$product_min_q    = isset($_POST['simple_product_min_quantity']) ? $_POST['simple_product_min_quantity'] : null;
		$product_max_q    = isset($_POST['simple_product_max_quantity']) ? $_POST['simple_product_max_quantity'] : null;
		$check_status     = isset($_POST['check_status']) ? $_POST['check_status'] : 'no';

		update_post_meta( $product_id, 'simple_product_min_quantity', $product_min_q);
		update_post_meta( $product_id, 'simple_product_max_quantity', $product_max_q);
		update_post_meta( $product_id, 'check_status', $check_status);
	}
}

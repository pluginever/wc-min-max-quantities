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
				'label'       => __( 'Product Minimum Quantity', 'woocommerce' ),
				'type'        => 'number',
				'min'		  => '0'
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'          => 'simple_product_max_quantity',
				'label'       => __( 'Product Maximum Quantity', 'woocommerce' ),
				'type'        => 'number',
			)
		);
	}

	public function save_product_data_tab(){
		global $product;
		$product_id       = get_the_id($product);
		$product_min_q    = isset($_POST['simple_product_min_quantity']) ? $_POST['simple_product_min_quantity'] : null;
		$product_max_q    = isset($_POST['simple_product_max_quantity']) ? $_POST['simple_product_max_quantity'] : null;

		if( empty($product_min_q) || empty($product_max_q) ){
			return;
		}
		if($product_min_q > $product_max_q || $product_min_q == $product_max_q){
			return;
		}
		update_post_meta( $product_id, 'simple_product_min_quantity', $product_min_q);
		update_post_meta( $product_id, 'simple_product_max_quantity', $product_max_q);
	}
}

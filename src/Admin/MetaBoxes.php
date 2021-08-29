<?php

namespace PluginEver\MinMaxQuantities\Admin;

class MetaBoxes {
	/**
	 * Register hooks.
	 */
	public function register() {
		add_action( 'woocommerce_product_options_inventory_product_data', [ __CLASS__, 'product_data_tab' ] );
	}

	/**
	 * Register meta fields.
	 */
	public static function product_data_tab() {
		woocommerce_wp_text_input(
			array(
				'id'    => '_minmax_product_min_quantity',
				'label' => __( 'Min Allowed Quantity', 'wc-minmax-quantities' ),
				'type'  => 'number',
				'min'   => '0',
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'    => '_minmax_product_max_quantity',
				'label' => __( 'Max Allowed Quantity', 'wc-minmax-quantities' ),
				'type'  => 'number',
				'min'   => '0',
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'    => '_minmax_product_min_price',
				'label' => __( 'Min Allowed Price', 'wc-minmax-quantities' ),
				'type'  => 'number',
				'min'   => '0',
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'    => '_minmax_product_max_price',
				'label' => __( 'Max Allowed Price', 'wc-minmax-quantities' ),
				'type'  => 'number',
				'min'   => '0',
			)
		);

		woocommerce_wp_checkbox(
			array(
				'id'      => '_minmax_ignore_global',
				'label'   => __( 'Ignore Global Rule', 'wc-minmax-quantities' ),
				'desc'    => __( 'Ignore Global Rule', 'wc-minmax-quantities' ),
				'default' => '0',
			)
		);
	}
}

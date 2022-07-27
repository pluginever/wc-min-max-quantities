<?php

namespace PluginEver\WC_Min_Max_Quantities\Admin;

use PluginEver\WC_Min_Max_Quantities\Framework;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Class Settings Page.
 *
 * @since   1.0.0
 * @package PluginEver\WC_Min_Max_Quantities
 */
class Admin_Settings extends Framework\AdminSettings {
	/**
	 * Hook Prefix.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	const HOOK_PREFIX = 'wc_min_max_quantities';

	/**
	 * Get general settings.
	 *
	 * @param string $section_id Section ID.
	 *
	 * @return array
	 * @since 1.1.0
	 */
	public static function get_settings_for_general_tab( $section_id ) {

		$settings = [
			array(
				'id'    => 'section_product_restrictions',
				'title' => esc_html__( 'Product restrictions', 'wc-min-max-quantities' ),
				'type'  => 'title',
				'desc'  => esc_html__( 'The following options are for adding minimum maximum rules for products globally.', 'wc-min-max-quantities' ),
			),

			array(
				'title'             => esc_html__( 'Minimum product quantity', 'wc-min-max-quantities' ),
				'id'                => 'minmax_quantities_min_product_quantity',
				'desc'              => esc_html__( 'Set an allowed minimum number of items for each product. For no restrictions, set 0.', 'wc-min-max-quantities' ),
				'type'              => 'number',
				'default'           => '0',
				'sanitize_callback' => 'floatval',
				'desc_tip'          => true,
			),
			array(
				'title'             => esc_html__( 'Maximum product quantity', 'wc-min-max-quantities' ),
				'id'                => 'minmax_quantities_max_product_quantity',
				'desc'              => esc_html__( 'Set an allowed maximum number of items for each product. For no restrictions, set 0.', 'wc-min-max-quantities' ),
				'type'              => 'number',
				'default'           => '0',
				'sanitize_callback' => 'floatval',
				'desc_tip'          => true,
			),
			array(
				'title'             => esc_html__( 'Quantity group of', 'wc-min-max-quantities' ),
				'id'                => 'minmax_quantities_product_quantity_step',
				'desc'              => esc_html__( 'Enter a number that will increment or decrement every time a quantity is changed.', 'wc-min-max-quantities' ),
				'type'              => 'number',
				'default'           => '0',
				'sanitize_callback' => 'floatval',
				'desc_tip'          => true,
			),
			[
				'type' => 'sectionend',
				'id'   => 'section_product_restrictions'
			],
			array(
				'title' => esc_html__( 'Order restrictions', 'wc-min-max-quantities' ),
				'type'  => 'title',
				'desc'  => esc_html__( 'The following options can be applied to the cart only.', 'wc-min-max-quantities' ),
				'id'    => 'cart_restrictions',
			),
			array(
				'title'             => esc_html__( 'Minimum order quantity', 'wc-min-max-quantities' ),
				'id'                => 'minmax_quantities_min_order_quantity',
				'desc'              => esc_html__( 'Set an allowed minimum number of products customers can add to the cart. For no restrictions, set 0.', 'wc-min-max-quantities' ),
				'type'              => 'number',
				'default'           => '0',
				'sanitize_callback' => 'floatval',
				'desc_tip'          => true,
			),
			array(
				'title'             => esc_html__( 'Maximum order quantity', 'wc-min-max-quantities' ),
				'id'                => 'minmax_quantities_max_order_quantity',
				'desc'              => esc_html__( 'Set an allowed maximum number of products customers can add to the cart. For no restrictions, set 0.', 'wc-min-max-quantities' ),
				'type'              => 'number',
				'min'               => 0,
				'default'           => '0',
				'sanitize_callback' => 'floatval',
				'desc_tip'          => true,
			),
			array(
				'title'             => esc_html__( 'Minimum order amount', 'wc-min-max-quantities' ),
				'id'                => 'minmax_quantities_min_order_amount',
				'desc'              => esc_html__( 'Set an allowed minimum total order amount customers can add to the cart. For no restrictions, set 0.', 'wc-min-max-quantities' ),
				'type'              => 'number',
				'default'           => '0',
				'sanitize_callback' => 'floatval',
				'desc_tip'          => true,
			),
			array(
				'title'             => esc_html__( 'Maximum order amount', 'wc-min-max-quantities' ),
				'id'                => 'minmax_quantities_max_order_amount',
				'desc'              => esc_html__( 'Set an allowed maximum total order amount customers can add to the cart. For no restrictions, set 0.', 'wc-min-max-quantities' ),
				'type'              => 'number',
				'default'           => '0',
				'sanitize_callback' => 'floatval',
				'desc_tip'          => true,
			),
			[
				'type' => 'sectionend',
				'id'   => 'cart_restrictions'
			],
		];

		return $settings;
	}
}



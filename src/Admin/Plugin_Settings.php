<?php
namespace PluginEver\WC_MinMax_Quantities\Admin;

use \ByteEver\Settings\Settings_Page;

/**
 * Class SettingsPage
 *
 * @package PluginEver\WC_MinMax_Quantities\Admin
 */
class Plugin_Settings extends Settings_Page {
	/**
	 * Register hooks.
	 */
	public function register() {
		parent::register();
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ], 99 );
	}

	/**
	 * Get settings page title.
	 *
	 * Retrieve the title for the settings page.
	 *
	 * @since 1.0.0
	 */
	protected function get_page_title() {
		return esc_html__( 'WC Min Max Settings', 'wc-minmax-quantities' );
	}

	/**
	 * Get settings page slug.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	protected function get_page_slug() {
		return 'wc-minmax-settings';
	}

	/**
	 * Get Settings.
	 *
	 * Register settings page.
	 *
	 * @since 1.0.0
	 */
	public function register_admin_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Min Max Quantities', 'wc-minmax-quantities' ),
			__( 'Min Max Quantities', 'wc-minmax-quantities' ),
			'manage_options',
			$this->get_page_slug(),
			array( $this, 'display_settings_page' )
		);
	}

	/**
	 * Get Settings.
	 *
	 * Return the settings page tabs, sections and fields.
	 *
	 * @since 1.0.0
	 */
	protected function get_settings() {
		$settings = array(
			'general' => array(
				'title'    => __( 'General', 'wc-minmax-quantities' ),
				'sections' => array(
					'main' => array(
						'title'  => __( 'General', 'wc-minmax-quantities' ),
						'fields' => array(
							array(
								'title' => esc_html__( 'Product Restrictions', 'wc-minmax-quantities' ),
								'type'  => 'section',
								'desc'  => esc_html__( 'The following options are for adding minimum maximum rules for products globally.', 'wc-minmax-quantities' ),
								'id'    => 'section_product_restrictions',
							),
							array(
								'title'   => esc_html__( 'Minimum product quantity', 'wc-minmax-quantities' ),
								'id'      => 'min_product_quantity',
								'desc'    => esc_html__( 'Minimum number of items required for each product. Set zero for no restrictions.', 'wc-minmax-quantities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title'   => esc_html__( 'Maximum product quantity', 'wc-minmax-quantities' ),
								'id'      => 'max_product_quantity',
								'desc'    => esc_html__( 'Maximum quantity allowed for each single product. Set zero for no restrictions.', 'wc-minmax-quantities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title'   => esc_html__( 'Minimum product total', 'wc-minmax-quantities' ),
								'id'      => 'min_product_total',
								'desc'    => esc_html__( 'Minimum price total of items required for each product. Set zero for no restrictions.', 'wc-minmax-quantities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title'   => esc_html__( 'Maximum product total', 'wc-minmax-quantities' ),
								'id'      => 'max_product_total',
								'desc'    => esc_html__( 'Maximum allowed price total of items for each product. Set zero for no restrictions.', 'wc-minmax-quantities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title'   => esc_html__( 'Quantity groups of', 'wc-minmax-quantities' ),
								'id'      => 'product_quantity_step',
								'desc'    => esc_html__( 'Enter a quantity to only allow product to be purchased in groups of X. Set zero for no restrictions.', 'wc-minmax-quantities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title' => esc_html__( 'Order Restriction', 'wc-minmax-quantities' ),
								'type'  => 'section',
								'desc'  => esc_html__( 'The following options are for cart restrictions', 'wc-minmax-quantities' ),
								'id'    => 'cart_restrictions',
							),
							array(
								'title' => esc_html__( 'Minimum order quantity', 'wc-minmax-quantities' ),
								'id'    => 'min_order_quantity',
								'desc'  => esc_html__( 'Minimum number of items in cart. Set zero for no restrictions.', 'wc-minmax-quantities' ),
								'type'  => 'number',
								'min'   => 0,
							),
							array(
								'title' => esc_html__( 'Maximum order quantity', 'wc-minmax-quantities' ),
								'id'    => 'max_order_quantity',
								'desc'  => esc_html__( 'Maximum number of items in cart. Set zero for no restrictions.', 'wc-minmax-quantities' ),
								'type'  => 'number',
								'min'   => 0,
							),
							array(
								'title'   => esc_html__( 'Minimum order amount', 'wc-minmax-quantities' ),
								'id'      => 'min_order_amount',
								'desc'    => esc_html__( 'Minimum order total. Set zero for no restrictions.', 'wc-minmax-quantities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title'   => esc_html__( 'Maximum order amount', 'wc-minmax-quantities' ),
								'id'      => 'max_order_amount',
								'desc'    => esc_html__( 'Maximum order total.Set zero for no restrictions.', 'wc-minmax-quantities' ),
								'type'    => 'number',
								'default' => '0',
							),
						),
					),
				),
			),
		);

		return apply_filters( 'wc_minmax_settings', $settings );
	}
}

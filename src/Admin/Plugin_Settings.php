<?php
namespace PluginEver\WooCommerce\WCMinMaxQuantities\Admin;



use ByteEver\PluginFramework\v1_0_0 as Framework;

/**
 * Class SettingsPage
 *
 * @package PluginEver\WC_MinMax_Quantities\Admin
 */
class Plugin_Settings extends Framework\Admin\Settings_Page {

	/**
	 * Hook into actions and filters.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function register_hooks() {
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
		return esc_html__( 'WC Min Max Settings', 'wc-min-max-qunatities' );
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
			__( 'Min Max Quantities', 'wc-min-max-qunatities' ),
			__( 'Min Max Quantities', 'wc-min-max-qunatities' ),
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
				'title'    => __( 'General', 'wc-min-max-qunatities' ),
				'sections' => array(
					'main' => array(
						'title'  => __( 'General', 'wc-min-max-qunatities' ),
						'fields' => array(
							array(
								'title' => esc_html__( 'Product Restrictions', 'wc-min-max-qunatities' ),
								'type'  => 'section',
								'desc'  => esc_html__( 'The following options are for adding minimum maximum rules for products globally.', 'wc-min-max-qunatities' ),
								'id'    => 'section_product_restrictions',
							),
							array(
								'title'   => esc_html__( 'Minimum product quantity', 'wc-min-max-qunatities' ),
								'id'      => 'min_product_quantity',
								'desc'    => esc_html__( 'Minimum number of items required for each product. Set zero for no restrictions.', 'wc-min-max-qunatities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title'   => esc_html__( 'Maximum product quantity', 'wc-min-max-qunatities' ),
								'id'      => 'max_product_quantity',
								'desc'    => esc_html__( 'Maximum quantity allowed for each single product. Set zero for no restrictions.', 'wc-min-max-qunatities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title'   => esc_html__( 'Minimum product total', 'wc-min-max-qunatities' ),
								'id'      => 'min_product_total',
								'desc'    => esc_html__( 'Minimum price total of items required for each product. Set zero for no restrictions.', 'wc-min-max-qunatities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title'   => esc_html__( 'Maximum product total', 'wc-min-max-qunatities' ),
								'id'      => 'max_product_total',
								'desc'    => esc_html__( 'Maximum allowed price total of items for each product. Set zero for no restrictions.', 'wc-min-max-qunatities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title'   => esc_html__( 'Quantity groups of', 'wc-min-max-qunatities' ),
								'id'      => 'product_quantity_step',
								'desc'    => esc_html__( 'Enter a quantity to only allow product to be purchased in groups of X. Set zero for no restrictions.', 'wc-min-max-qunatities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title' => esc_html__( 'Order Restriction', 'wc-min-max-qunatities' ),
								'type'  => 'section',
								'desc'  => esc_html__( 'The following options are for cart restrictions', 'wc-min-max-qunatities' ),
								'id'    => 'cart_restrictions',
							),
							array(
								'title' => esc_html__( 'Minimum order quantity', 'wc-min-max-qunatities' ),
								'id'    => 'min_order_quantity',
								'desc'  => esc_html__( 'Minimum number of items in cart. Set zero for no restrictions.', 'wc-min-max-qunatities' ),
								'type'  => 'number',
								'min'   => 0,
							),
							array(
								'title' => esc_html__( 'Maximum order quantity', 'wc-min-max-qunatities' ),
								'id'    => 'max_order_quantity',
								'desc'  => esc_html__( 'Maximum number of items in cart. Set zero for no restrictions.', 'wc-min-max-qunatities' ),
								'type'  => 'number',
								'min'   => 0,
							),
							array(
								'title'   => esc_html__( 'Minimum order amount', 'wc-min-max-qunatities' ),
								'id'      => 'min_order_amount',
								'desc'    => esc_html__( 'Minimum order total. Set zero for no restrictions.', 'wc-min-max-qunatities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title'   => esc_html__( 'Maximum order amount', 'wc-min-max-qunatities' ),
								'id'      => 'max_order_amount',
								'desc'    => esc_html__( 'Maximum order total.Set zero for no restrictions.', 'wc-min-max-qunatities' ),
								'type'    => 'number',
								'default' => '0',
							),
						),
					),
				),
			),
		);

		return apply_filters( 'wc_min_max_settings', $settings );
	}
}

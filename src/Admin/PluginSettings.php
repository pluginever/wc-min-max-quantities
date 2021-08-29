<?php
namespace PluginEver\MinMaxQuantities\Admin;

use \ByteEver\Settings\SettingsPage;

/**
 * Class SettingsPage
 *
 * @package PluginEver\MinMaxQuantities\Admin
 */
class PluginSettings extends SettingsPage {
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
		return esc_html__('WC Min Max Settings', 'wc-minmax-quantities');
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
								'title' => esc_html__( 'Cart Restriction', 'wc-minmax-quantities' ),
								'type'  => 'section',
								'desc'  => esc_html__( 'The following options are for cart restrictions', 'wc-minmax-quantities' ),
								'id'    => 'cart_restrictions',
							),
							array(
								'title'   => esc_html__( 'Minimum Cart Total Price', 'wc-minmax-quantities' ),
								'id'      => 'min_cart_total_price',
								'desc'    => esc_html__( 'Enter an amount of Price to prevent  users from buying, if they have lower than the allowed price in their cart total.', 'wc-minmax-quantities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title'   => esc_html__( 'Maximum Cart Total Price', 'wc-minmax-quantities' ),
								'id'      => 'max_cart_total_price',
								'desc'    => esc_html__( 'Enter an amount of Price to prevent users from buying, if they have more than the allowed price in their cart total.', 'wc-minmax-quantities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title'    => esc_html__( 'Minimum Cart Quantity', 'wc-minmax-quantities' ),
								'id'       => 'min_cart_total_quantity',
								'desc'     => esc_html__( 'Enter a quantity to prevent  user from buying this product if they have fewer than the allowed total quantity in their cart.', 'wc-minmax-quantities' ),
								'type'     => 'number',
								'min'      => 0,
								'disabled' => true,
							),
							array(
								'title'    => esc_html__( 'Maximum Cart Quantity', 'wc-minmax-quantities' ),
								'id'       => 'max_cart_total_quantity',
								'desc'     => esc_html__( 'Enter a quantity to prevent  user from buying this product if they have more than the allowed total quantity in their cart.', 'wc-minmax-quantities' ),
								'type'     => 'number',
								'min'      => 0,
								'disabled' => true,
							),
							array(
								'title' => esc_html__( 'Other Settings', 'wc-minmax-quantities' ),
								'type'  => 'section',
								'id'    => 'other_settings',
							),
							array(
								'title'   => esc_html__( 'Hide Checkout Button', 'wc-minmax-quantities' ),
								'id'      => 'hide_checkout',
								'desc'    => esc_html__( 'Hide checkout button if Min/Max condition not passed.', 'wc-minmax-quantities' ),
								'type'    => 'checkbox',
								'default' => 'yes',
							),
							array(
								'title'    => esc_html__( 'Force Minimum Quantity', 'wc-minmax-quantities' ),
								'id'       => 'force_add_minimum_quantity',
								'desc'     => esc_html__( 'Force to add minimum quantity in product cart', 'wc-minmax-quantities' ),
								'type'     => 'checkbox',
								'disabled' => true,
							),
							array(
								'title'    => esc_html__( 'Prevent Add to Cart', 'wc-minmax-quantities' ),
								'id'       => 'prevent_add_to_cart',
								'desc'     => esc_html__( 'Prevent add product in cart when reach the product quantity/price maximum limit', 'wc-minmax-quantities' ),
								'type'     => 'checkbox',
								'disabled' => true,
							),
							array(
								'title'    => esc_html__( 'Remove Item from Checkout', 'wc-minmax-quantities' ),
								'id'       => 'remove_item_checkout',
								'desc'     => esc_html__( 'Enable option for remove item from checkout page', 'wc-minmax-quantities' ),
								'type'     => 'checkbox',
								'disabled' => true,
							),
							array(
								'title' => esc_html__( 'Product Restrictions', 'wc-minmax-quantities' ),
								'type'  => 'section',
								'desc'  => esc_html__( 'The following options are for adding minimum maximum rules for products globally', 'wc-minmax-quantities' ),
								'id'    => 'section_product_restrictions',
							),
							array(
								'title'   => esc_html__( 'Minimum Product Quantity', 'wc-minmax-quantities' ),
								'id'      => 'min_product_quantity',
								'desc'    => esc_html__( 'Enter a quantity to prevent  user from buying this product if they have fewer than the allowed quantity in their cart.', 'wc-minmax-quantities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title'   => esc_html__( 'Maximum Product Quantity', 'wc-minmax-quantities' ),
								'id'      => 'max_product_quantity',
								'desc'    => esc_html__( 'Enter a quantity to prevent  user from buying this product if they have more than the allowed quantity in their cart.', 'wc-minmax-quantities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title'   => esc_html__( 'Minimum Product Price', 'wc-minmax-quantities' ),
								'id'      => 'min_product_price',
								'desc'    => esc_html__( 'Enter an amount of price to prevent  users from buying, if they have lower than the allowed product price in their cart.', 'wc-minmax-quantities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title'   => esc_html__( 'Maximum Product Price', 'wc-minmax-quantities' ),
								'id'      => 'max_product_price',
								'desc'    => esc_html__( 'Enter an amount of Price to prevent users from buying, if they have more than the allowed product price in their cart.', 'wc-minmax-quantities' ),
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

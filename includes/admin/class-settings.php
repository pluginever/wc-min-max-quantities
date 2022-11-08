<?php

namespace WC_Min_Max_Quantities\Admin;

use WC_Min_Max_Quantities\Framework;

defined( 'ABSPATH' ) || exit();

/**
 * Settings class.
 *
 * @since 1.0.0
 * @package WC_Min_Max_Quantities\Admin
 */
class Settings extends Framework\Settings {
	/**
	 * Set up the controller.
	 *
	 * Load files or register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function init() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 55 );
		add_action( 'wc_min_max_quantities_settings_tabs', array( $this, 'add_extra_tabs' ), 20 );
		add_action( 'wc_min_max_quantities_activated', array( $this, 'save_defaults' ) );
		add_action( 'wc_min_max_quantities_settings_sidebar', array( $this, 'output_upgrade_widget' ) );
		add_action( 'wc_min_max_quantities_settings_sidebar', array( $this, 'output_about_widget' ) );
		add_action( 'wc_min_max_quantities_settings_sidebar', array( $this, 'output_help_widget' ) );
		add_action( 'wc_min_max_quantities_settings_sidebar', array( $this, 'output_recommended_widget' ) );
	}

	/**
	 * Admin menu.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu() {
		$load = add_submenu_page(
			'woocommerce',
			__( 'Min Max Quantities', 'wc-min-max-quantities' ),
			__( 'Min Max Quantities', 'wc-min-max-quantities' ),
			'manage_options',
			'wc-min-max-quantities',
			array( $this, 'output' )
		);
		add_action( 'load-' . $load, array( $this, 'save_settings' ) );
	}

	/**
	 * Add extra tabs.
	 *
	 * @since 1.0.0
	 */
	public function add_extra_tabs() {
		if ( $this->get_plugin()->get_doc_url() ) {
			echo '<a href="' . esc_url( $this->get_plugin()->get_doc_url() ) . '" target="_blank" class="nav-tab">' . esc_html__( 'Documentation', 'wc-min-max-quantities' ) . '</a>';
		}
	}

	/**
	 * Get tabs.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_settings_tabs() {
		$tabs = [
			'general'       => __( 'General', 'wc-min-max-quantities' ),
			'localizations' => __( 'Localizations', 'wc-min-max-quantities' ),
		];

		return apply_filters( 'wc_min_max_quantities_settings_tabs_array', $tabs );
	}

	/**
	 * Get general settings.
	 *
	 * @since 1.0.0
	 * @return array General settings.
	 */
	public function get_general_tab_settings() {
		return array(
			array(
				'id'    => 'section_product_restrictions',
				'title' => esc_html__( 'Product restrictions', 'wc-min-max-quantities' ),
				'type'  => 'title',
				'desc'  => esc_html__( 'The following options are for adding minimum maximum rules for products globally.', 'wc-min-max-quantities' ),
			),
			array(
				'id'   => 'pluginever_license_key_' . $this->get_plugin()->get_item_id(),
				'type' => 'pluginever_license_key_' . $this->get_plugin()->get_item_id(),
			),
			array(
				'title'             => esc_html__( 'Minimum product quantity', 'wc-min-max-quantities' ),
				'id'                => 'wcmmq_min_product_quantity',
				'desc'              => esc_html__( 'Set an allowed minimum number of items for each product. For no restrictions, set 0.', 'wc-min-max-quantities' ),
				'type'              => 'number',
				'default'           => '0',
				'sanitize_callback' => 'floatval',
			),
			array(
				'title'             => esc_html__( 'Maximum product quantity', 'wc-min-max-quantities' ),
				'id'                => 'wcmmq_max_product_quantity',
				'desc'              => esc_html__( 'Set an allowed maximum number of items for each product. For no restrictions, set 0.', 'wc-min-max-quantities' ),
				'type'              => 'number',
				'default'           => '0',
				'sanitize_callback' => 'floatval',
			),
			array(
				'title'             => esc_html__( 'Quantity group of', 'wc-min-max-quantities' ),
				'id'                => 'wcmmq_product_quantity_step',
				'desc'              => esc_html__( 'Enter a number that will increment or decrement every time a quantity is changed.', 'wc-min-max-quantities' ),
				'type'              => 'number',
				'default'           => '0',
				'sanitize_callback' => 'floatval',
			),
			// end product restrictions.
			array(
				'type' => 'sectionend',
				'id'   => 'section_product_restrictions',
			),
			array(
				'title' => esc_html__( 'Order restrictions', 'wc-min-max-quantities' ),
				'type'  => 'title',
				'desc'  => esc_html__( 'The following options can be applied to the cart only.', 'wc-min-max-quantities' ),
				'id'    => 'cart_restrictions',
			),
			array(
				'title'             => esc_html__( 'Minimum order quantity', 'wc-min-max-quantities' ),
				'id'                => 'wcmmq_min_order_quantity',
				'desc'              => esc_html__( 'Set an allowed minimum number of products customers can add to the cart. For no restrictions, set 0.', 'wc-min-max-quantities' ),
				'type'              => 'number',
				'default'           => '0',
				'sanitize_callback' => 'floatval',
			),
			array(
				'title'             => esc_html__( 'Maximum order quantity', 'wc-min-max-quantities' ),
				'id'                => 'wcmmq_max_order_quantity',
				'desc'              => esc_html__( 'Set an allowed maximum number of products customers can add to the cart. For no restrictions, set 0.', 'wc-min-max-quantities' ),
				'type'              => 'number',
				'min'               => 0,
				'default'           => '0',
				'sanitize_callback' => 'floatval',
			),
			array(
				'title'             => esc_html__( 'Minimum order amount', 'wc-min-max-quantities' ),
				'id'                => 'wcmmq_min_order_amount',
				'desc'              => esc_html__( 'Set an allowed minimum total order amount customers can add to the cart. For no restrictions, set 0.', 'wc-min-max-quantities' ),
				'type'              => 'number',
				'default'           => '0',
				'sanitize_callback' => 'floatval',
			),
			array(
				'title'             => esc_html__( 'Maximum order amount', 'wc-min-max-quantities' ),
				'id'                => 'wcmmq_max_order_amount',
				'desc'              => esc_html__( 'Set an allowed maximum total order amount customers can add to the cart. For no restrictions, set 0.', 'wc-min-max-quantities' ),
				'type'              => 'number',
				'default'           => '0',
				'sanitize_callback' => 'floatval',
			),
			// end cart restrictions.
			array(
				'type' => 'sectionend',
				'id'   => 'cart_restrictions',
			),
		);
	}

	/**
	 * Get localizations settings.
	 *
	 * @since 1.0.0
	 * @return array Localizations settings.
	 */
	public function get_localizations_tab_settings() {

	}
}

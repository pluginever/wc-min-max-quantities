<?php

namespace WooCommerceMinMaxQuantities\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Admin
 *
 * @since 1.1.4
 * @package WooCommerceMinMaxQuantities\Admin
 */
class Admin {
	/**
	 * Admin constructor.
	 *
	 * @since 1.1.4
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 55 );
		add_filter( 'woocommerce_screen_ids', array( $this, 'screen_ids' ) );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), PHP_INT_MAX );
		add_filter( 'update_footer', array( $this, 'update_footer' ), PHP_INT_MAX );
	}

	/**
	 * Init.
	 *
	 * @since 1.1.4
	 */
	public function init() {
		wc_min_max_quantities()->set( 'settings', Settings::instance() );
		wc_min_max_quantities()->set( 'meta_boxes', MetaBoxes::class );
		wc_min_max_quantities()->set( 'actions', Actions::class );
		// TODO: Need to include Notices class: wc_min_max_quantities()->set( 'notices', Notices::class );.
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook Hook name.
	 *
	 * @since 1.1.4
	 */
	public function enqueue_scripts( $hook ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		// Enqueue scripts.
		wc_min_max_quantities()->scripts->enqueue_style( 'wcmmq-admin', 'css/admin.css', array( 'bytekit-layout', 'bytekit-components' ) );
	}

	/**
	 * Add menu item.
	 *
	 * @since 1.1.0
	 */
	public function settings_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Min Max Quantities Settings', 'wc-min-max-quantities' ),
			__( 'Min Max Quantities', 'wc-min-max-quantities' ),
			'manage_options',
			'wc-min-max-quantities',
			array( Settings::class, 'output' )
		);
	}

	/**
	 * Add the plugin screens to the WooCommerce screens.
	 * This will load the WooCommerce admin styles and scripts.
	 *
	 * @param array $ids Screen ids.
	 *
	 * @return array
	 */
	public function screen_ids( $ids ) {
		return array_merge( $ids, self::get_screen_ids() );
	}

	/**
	 * Admin footer text.
	 *
	 * @param string $footer_text Footer text.
	 *
	 * @since 1.1.4
	 * @return string
	 */
	public function admin_footer_text( $footer_text ) {
		if ( wc_min_max_quantities()->get_review_url() && in_array( get_current_screen()->id, self::get_screen_ids(), true ) ) {
			$footer_text = sprintf(
			/* translators: 1: Plugin name 2: WordPress */
				__( 'Thank you for using %1$s. If you like it, please leave us a %2$s rating. A huge thank you from PluginEver in advance!', 'wc-min-max-quantities' ),
				'<strong>' . esc_html( wc_min_max_quantities()->get_name() ) . '</strong>',
				'<a href="' . esc_url( wc_min_max_quantities()->get_review_url() ) . '" target="_blank" class="wc-min-max-quantities-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'wc-min-max-quantities' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
			);
		}

		return $footer_text;
	}

	/**
	 * Update footer.
	 *
	 * @param string $footer_text Footer text.
	 *
	 * @since 1.1.4
	 * @return string
	 */
	public function update_footer( $footer_text ) {
		if ( in_array( get_current_screen()->id, self::get_screen_ids(), true ) ) {
			/* translators: 1: Plugin version */
			$footer_text = sprintf( esc_html__( 'Version %s', 'wc-min-max-quantities' ), wc_min_max_quantities()->get_version() );
		}

		return $footer_text;
	}

	/**
	 * Get screen ids.
	 *
	 * @since 1.1.4
	 * @return array
	 */
	public static function get_screen_ids() {
		$screen_ids = array(
			'woocommerce_page_wc-min-max-quantities',
		);

		return apply_filters( 'wc_min_max_quantities_screen_ids', $screen_ids );
	}
}

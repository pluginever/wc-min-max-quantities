<?php

namespace WooCommerceMinMaxQuantities\Admin;

use WooCommerceMinMaxQuantities\Controller;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Admin class
 *
 * @package PluginEver\WooCommerceMinMaxQuantities\Admin
 */
class Admin extends Controller {

	/**
	 * Set up the controller.
	 *
	 * Load files or register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function init() {
		add_action( 'init', array( $this, 'add_controllers' ) );
		add_filter( 'woocommerce_screen_ids', array( $this, 'screen_ids' ) );
		add_action( 'admin_menu', array( $this, 'register_nav_items' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		//add_filter( 'woocommerce_display_admin_footer_text', array( $this, 'admin_footer_text' ), 20 );
	}

	/**
	 * Register admin controllers.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_controllers() {
		// Register admin controllers here.
		$this->add_controller(
			[
				'admin_settings'   => Settings::class,
				'admin_meta_boxes' => Meta_Boxes::class,
			]
		);
	}

	/**
	 * Add the plugin screens to the WooCommerce screens
	 *
	 * @param  array $ids Screen ids.
	 * @return array
	 */
	public function screen_ids( $ids ) {
		$ids[] = 'woocommerce_page_wc-min-max-quantities-settings';
		return $ids;
	}

	/**
	 * Registers the navigation items in the WC Navigation Menu.
	 *
	 * @since 1.0.0
	 */
	public static function register_nav_items() {
		if ( function_exists( 'wc_admin_connect_page' ) ) {
			wc_admin_connect_page(
				array(
					'id'        => 'woocommerce_page_wc-min-max-quantities',
					'parent'    => 'woocommerce_page_wc',
					'screen_id' => 'woocommerce_page_wc-min-max-quantities',
					'title'     => __( 'WooCommerce Min Max Quantities Settings', 'wc-min-max-quantities' ),
				)
			);
		}
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts() {
		$this->get_plugin()->register_style( 'wc-min-max-quantities-admin', 'css/admin.css' );
		$this->get_plugin()->register_script( 'wc-min-max-quantities-admin', 'js/admin.js' );
	}


	/**
	 * Add footer text.
	 *
	 * @since 1.0.0
	 * @param string $footer_text Footer text.
	 * @return string
	 */
	public function admin_footer_text( $footer_text ) {
		if ( ! current_user_can( 'manage_woocommerce' ) || ! function_exists( 'get_current_screen' ) ) {
			return $footer_text;
		}

		$current_screen = get_current_screen();
		if ( $current_screen && isset( $current_screen->id ) && 'woocommerce_page_wc-min-max-quantities-settings' === $current_screen->id ) {
			$footer_text = sprintf(
			/* translators: 1: plugin name 2: WordPress */
				__( 'If you like <strong>%1$s</strong> please leave us a %2$s rating. A huge thanks in advance!', 'wc-min-max-quantities' ),
				__( 'WooCommerce Min Max Quantities', 'wc-min-max-quantities' ),
				'<a href="https://wordpress.org/support/plugin/wc-min-max-quantities/reviews/?filter=5#new-post" target="_blank" class="wc-min-max-quantities-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'wc-min-max-quantities' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
			);
		}

		return $footer_text;
	}
}

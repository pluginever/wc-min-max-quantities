<?php

namespace WC_Min_Max_Quantities\Admin;

use WC_Min_Max_Quantities\Controller;
use WC_Min_Max_Quantities\Framework;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Admin class
 *
 * @package PluginEver\WC_Min_Max_Quantities\Admin
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

}

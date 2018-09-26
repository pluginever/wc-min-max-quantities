<?php
namespace Pluginever\WCMinMaxQuantities;

class Scripts{

	/**
	 * Constructor for the class
	 *
	 * Sets up all the appropriate hooks and actions
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'load_public_assets') );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_assets') );
    }

   	/**
	 * Add all the assets of the public side
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function load_public_assets(){
		$suffix = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '' : '.min';
		wp_register_style('wc-min-max-quantities', WPWMMQ_ASSETS."/css/wc-min-max-quantities-public{$suffix}.css", [], WPWMMQ_VERSION);
		wp_register_script('wc-min-max-quantities', WPWMMQ_ASSETS."/js/wc-min-max-quantities-public{$suffix}.js", ['jquery'], WPWMMQ_VERSION, true);
		wp_localize_script('wc-min-max-quantities', 'wpwmmq', ['ajaxurl' => admin_url( 'admin-ajax.php' ), 'nonce' => 'wc-min-max-quantities']);
		wp_enqueue_style('wc-min-max-quantities');
		wp_enqueue_script('wc-min-max-quantities');
	}

	 /**
	 * Add all the assets required by the plugin
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function load_admin_assets(){
		$suffix = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '' : '.min';
		wp_register_style('wc-min-max-quantities', WPWMMQ_ASSETS."/css/wc-min-max-quantities-admin{$suffix}.css", [], WPWMMQ_VERSION);
		wp_register_script('wc-min-max-quantities', WPWMMQ_ASSETS."/js/wc-min-max-quantities-admin{$suffix}.js", ['jquery'], WPWMMQ_VERSION, true);
		wp_localize_script('wc-min-max-quantities', 'wpwmmq', ['ajaxurl' => admin_url( 'admin-ajax.php' ), 'nonce' => 'wc-min-max-quantities']);
		wp_enqueue_style('wc-min-max-quantities');
		wp_enqueue_script('wc-min-max-quantities');
	}



}
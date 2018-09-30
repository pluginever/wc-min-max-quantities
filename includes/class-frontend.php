<?php

namespace Pluginever\WCMinMaxQuantities;

class Frontend {
	/**
	 * The single instance of the class.
	 *
	 * @var Frontend
	 * @since 1.0.0
	 */
	protected static $init = null;

	/**
	 * Frontend Instance.
	 *
	 * @since 1.0.0
	 * @static
	 * @return Frontend - Main instance.
	 */
	public static function init() {
		if ( is_null( self::$init ) ) {
			self::$init = new self();
			self::$init->setup();
		}

		return self::$init;
	}

	/**
	 * Initialize all frontend related stuff
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function setup() {
		$this->includes();
		$this->init_hooks();
		$this->instance();
	}

	/**
	 * Includes all frontend related files
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function includes() {
		require_once dirname( __FILE__ ) . '/template-functions.php';
		require_once dirname( __FILE__ ) . '/class-shortcode.php';
		require_once dirname( __FILE__ ) . '/class-user-cart.php';
	}

	/**
	 * Register all frontend related hooks
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Fire off all the instances
	 *
	 * @since 1.0.0
	 */
	protected function instance() {
		new ShortCode();
		new User_Cart();
	}

	/**
	 * Loads all frontend scripts/styles
	 *
	 * @param $hook
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		$suffix = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '' : '.min';
		wp_register_style('wc-min-max-quantities', WPWMMQ_ASSETS_URL."/css/frontend{$suffix}.css", [], WPWMMQ_VERSION);
		wp_register_script('wc-min-max-quantities', WPWMMQ_ASSETS_URL."/js/frontend/frontend{$suffix}.js", ['jquery'], WPWMMQ_VERSION, true);
		wp_localize_script('wc-min-max-quantities', 'wpwmmq', 
		[
			'ajaxurl' => admin_url( 'admin-ajax.php' ), 
			'nonce' => wp_create_nonce('wc-min-max-quantities')
		]);		
		
		wp_enqueue_style('wc-min-max-quantities');
		wp_enqueue_script('wc-min-max-quantities');
	}

}

Frontend::init();

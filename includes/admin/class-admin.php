<?php

namespace Pluginever\WCMinMaxQuantities\Admin;

class Admin {
	/**
	 * The single instance of the class.
	 *
	 * @var Admin
	 * @since 1.0.0
	 */
	protected static $init = null;

	/**
	 * Frontend Instance.
	 *
	 * @since 1.0.0
	 * @static
	 * @return Admin - Main instance.
	 */
	public static function init() {
		if ( is_null( self::$init ) ) {
			self::$init = new self();
			self::$init->setup();
		}

		return self::$init;
	}

	/**
	 * Initialize all Admin related stuff
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
	 * Includes all files related to admin
	 */
	public function includes() {
		require_once dirname( __FILE__ ) . '/class-admin-menu.php';
		require_once dirname( __FILE__ ) . '/class-metabox.php';
		require_once dirname( __FILE__ ) . '/class-minmax-settings.php';
	}

	private function init_hooks() {
		add_action( 'admin_init', array( $this, 'buffer' ), 1 );
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}


	/**
	 * Fire off all the instances
	 *
	 * @since 1.0.0
	 */
	protected function instance() {
		new Admin_Menu();
		new MetaBox();
		new Minmax_Settings();
	}

	/**
	 * Output buffering allows admin screens to make redirects later on.
	 *
	 * @since 1.0.0
	 */
	public function buffer() {
		ob_start();
	}


	public function enqueue_scripts( $hook ) {
		$suffix = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '' : '.min';
		wp_register_style('wc-min-max-quantities', WPWMMQ_ASSETS_URL."/css/admin.css", [], WPWMMQ_VERSION);
		wp_register_script('wc-min-max-quantities', WPWMMQ_ASSETS_URL."/js/admin/admin{$suffix}.js", ['jquery'], WPWMMQ_VERSION, true);
		wp_localize_script('wc-min-max-quantities', 'wpwmmq', ['ajaxurl' => admin_url( 'admin-ajax.php' ), 'nonce' => 'wc-min-max-quantities']);
		wp_enqueue_style('wc-min-max-quantities');
		wp_enqueue_script('wc-min-max-quantities');
	}


}

Admin::init();

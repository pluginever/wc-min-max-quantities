<?php

namespace WooCommerceMinMaxQuantities;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin.
 *
 * @since 1.1.4
 * @package WooCommerceMinMaxQuantities
 */
class Plugin extends Lib\Plugin {
	/**
	 * Plugin constructor.
	 *
	 * @param array $data The plugin data.
	 *
	 * @since 1.1.4
	 */
	protected function __construct( $data ) {
		parent::__construct( $data );
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Define constants.
	 *
	 * @since 1.1.4
	 * @return void
	 */
	public function define_constants() {
		$this->define( 'WCMMQ_FILE', $this->get_file() );
		$this->define( 'WCMMQ_VERSION', $this->get_version() );
		$this->define( 'WCMMQ_PLUGIN_PATH', $this->get_dir_path() );
		$this->define( 'WCMMQ_PLUGIN_URL', $this->get_dir_url() );
		$this->define( 'WCMMQ_ASSETS_PATH', $this->get_assets_path() );
		$this->define( 'WCMMQ_ASSETS_URL', $this->get_assets_url() );
	}

	/**
	 * Include required files.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function includes() {
		require_once __DIR__ . '/Functions.php';
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_hooks() {
		register_activation_hook( $this->get_file(), array( Installer::class, 'install' ) );
		add_action( 'admin_notices', array( $this, 'dependencies_notices' ) );
		add_action( 'before_woocommerce_init', array( $this, 'enable_hpos_support' ) );
		add_action( 'woocommerce_loaded', array( $this, 'init' ), 0 );
	}

	/**
	 * Missing dependencies notice.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function dependencies_notices() {
		if ( $this->is_plugin_active( 'woocommerce' ) ) {
			return;
		}
		$notice = sprintf(
		/* translators: 1: plugin name 2: WooCommerce */
			__( '%1$s requires %2$s to be installed and active.', 'wc-min-max-quantities' ),
			'<strong>' . esc_html( $this->get_name() ) . '</strong>',
			'<strong>' . esc_html__( 'WooCommerce', 'wc-min-max-quantities' ) . '</strong>'
		);

		echo '<div class="notice notice-error"><p>' . wp_kses_post( $notice ) . '</p></div>';
	}

	/**
	 * Enable HPOS support.
	 *
	 * @since 1.1.5
	 * @return void
	 */
	public function enable_hpos_support() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->get_file(), true );
		}
	}

	/**
	 * Init the plugin after plugins_loaded so environment variables are set.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		$this->services->add( Installer::class );
		$this->services->add( Cart::class );
		if ( $this->is_request( 'admin' ) ) {
			$this->services->add( Admin\Admin::class );
		}

		do_action( 'wc_min_max_quantities_loaded' );
	}
}

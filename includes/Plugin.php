<?php

namespace WooCommerceMinMaxQuantities;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin.
 *
 * @since 1.1.4
 * @package WooCommerceMinMaxQuantities
 */
final class Plugin extends \WooCommerceMinMaxQuantities\ByteKit\Plugin {

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
		require_once __DIR__ . '/functions.php';
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_hooks() {
		register_activation_hook( $this->get_file(), array( Installer::class, 'install' ) );
		add_filter( 'plugin_action_links_' . $this->get_basename(), array( $this, 'plugin_action_links' ) );
		add_action( 'before_woocommerce_init', array( $this, 'enable_hpos_support' ) );
		add_action( 'woocommerce_loaded', array( $this, 'init' ), 0 );
	}

	/**
	 * Add plugin action links.
	 *
	 * @param array $links The plugin action links.
	 *
	 * @since 2.0.3
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		if ( ! $this->is_plugin_active( 'wc-min-max-quantities-pro/wc-min-max-quantities-pro.php' ) ) {
			$links['go_pro'] = '<a href="https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/" target="_blank" style="color: #39b54a; font-weight: bold;">' . esc_html__( 'Go Pro', 'wc-min-max-quantities' ) . '</a>';
		}

		return $links;
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
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', $this->get_file(), true );
		}
	}

	/**
	 * Init the plugin after plugins_loaded so environment variables are set.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		// Common classes.
		$this->set( Installer::class );
		$this->set( Cart::class );

		// Admin only classes.
		if ( is_admin() ) {
			$this->set( Admin\Admin::class );
		}

		// Do action after plugin loaded.
		do_action( 'wc_min_max_quantities_loaded' );
	}

	/**
	 * Get assets path.
	 *
	 * @param string $file Optional. File name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_assets_path( $file = '' ) {
		return $this->get_dir_path( 'assets/' . $file );
	}

	/**
	 * Get assets url.
	 *
	 * @param string $file Optional. File name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_assets_url( $file = '' ) {
		return $this->get_dir_url( 'assets/' . $file );
	}
}

<?php

namespace WooCommerceMinMaxQuantities;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin.
 *
 * @since 1.1.4
 * @package WooCommerceMinMaxQuantities
 */
final class Plugin extends B8\Plugin\App {

	/**
	 * Bootstraps the plugin.
	 *
	 * @since 1.1.4
	 * @return void
	 */
	protected function bootstrap(): void {
		define( 'WCMMQ_FILE', $this->file );
		define( 'WCMMQ_VERSION', $this->version );
		define( 'WCMMQ_PLUGIN_PATH', $this->plugin_path() );
		define( 'WCMMQ_PLUGIN_URL', $this->plugin_url() );
		define( 'WCMMQ_ASSETS_PATH', $this->assets_path() );
		define( 'WCMMQ_ASSETS_URL', $this->assets_url() );

		register_activation_hook( $this->file, array( Installer::class, 'install' ) );
		add_filter( 'plugin_action_links_' . $this->basename(), array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_action( 'before_woocommerce_init', array( $this, 'declare_compatibility' ) );
		add_action( 'woocommerce_loaded', array( $this, 'register_services' ), 0 );
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
		$plugin_links = array(
			'settings' => sprintf(
				'<a href="%s">%s</a>',
				esc_url( $this->settings_url ),
				esc_html__( 'Settings', 'wc-min-max-quantities' )
			),
		);

		if ( ! $this->utils->plugin_active( 'wc-min-max-quantities-pro/wc-min-max-quantities-pro.php' ) ) {
			$plugin_links['go_pro'] = '<a href="https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/" target="_blank" style="color: #39b54a; font-weight: bold;">' . esc_html__( 'Go Pro', 'wc-min-max-quantities' ) . '</a>';
		}

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Add plugin row meta links.
	 *
	 * @param array  $links Plugin row meta links.
	 * @param string $file  Plugin file.
	 *
	 * @since 2.2.4
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( $this->basename() !== $file ) {
			return $links;
		}

		$row_meta = array(
			'docs'    => sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( $this->docs_url ),
				esc_html__( 'Documentation', 'wc-min-max-quantities' )
			),
			'support' => sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( $this->support_url ),
				esc_html__( 'Support', 'wc-min-max-quantities' )
			),
		);

		return array_merge( $links, $row_meta );
	}

	/**
	 * Declare WooCommerce compatibility.
	 *
	 * @since 1.1.5
	 * @return void
	 */
	public function declare_compatibility() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->file, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', $this->file, true );
		}
	}

	/**
	 * Register plugin services.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_services(): void {
		$this->make( Installer::class );
		$this->make( Cart::class );

		if ( is_admin() ) {
			$this->make( Admin\Admin::class );
		}

		do_action( 'wc_min_max_quantities_loaded' );
	}
}

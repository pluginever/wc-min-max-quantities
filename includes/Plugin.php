<?php
/**
 * Main plugin class.
 *
 * @since   1.1.4
 * @package PluginEver\MinMaxQuantities
 */

namespace PluginEver\MinMaxQuantities;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin.
 *
 * @since 1.1.4
 * @package PluginEver\MinMaxQuantities
 *
 * @property-read string $settings_url Settings page URL.
 * @property-read string $docs_url     Documentation URL.
 * @property-read string $support_url  Support page URL.
 * @property-read string $review_url   Review URL.
 * @property-read string $upgrade_url  Upgrade URL.
 */
final class Plugin extends B8\App {

	/**
	 * Components to boot.
	 *
	 * @since 2.3.2
	 * @var array<int, class-string>
	 */
	protected array $components = array(
		Installer::class,
		Cart::class,
		Admin\Admin::class,
	);

	/**
	 * Bootstraps the plugin.
	 *
	 * @since 2.2.4
	 * @return void
	 */
	public function bootstrap(): void {
		define( 'WCMMQ_FILE', $this->file );
		define( 'WCMMQ_VERSION', $this->version );
		define( 'WCMMQ_PLUGIN_PATH', $this->plugin_path() );
		define( 'WCMMQ_PLUGIN_URL', $this->plugin_url() );
		define( 'WCMMQ_ASSETS_PATH', $this->assets_path() );
		define( 'WCMMQ_ASSETS_URL', $this->assets_url() );

		// Keep our caches request-scoped. The cached product limits pass through
		// filters whose output can depend on the current user (e.g. role-based
		// overrides from the Pro add-on), so persisting them across requests in
		// Redis/Memcached/etc. would leak one user's limits to another.
		wp_cache_add_non_persistent_groups( array( 'wc-min-max-quantities' ) );

		add_action( 'woocommerce_loaded', array( $this, 'woocommerce_loaded' ), 0 );
		add_filter( 'plugin_action_links_' . $this->basename(), array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
	}

	/**
	 * Initialize the plugin.
	 *
	 * Boots every component and fires the legacy loaded action.
	 *
	 * @since 2.3.2
	 * @return void
	 */
	public function woocommerce_loaded(): void {
		$this->boot( $this->components );

		/**
		 * Fires after the plugin has registered its services.
		 *
		 * @since 2.0.0
		 */
		do_action( 'wc_min_max_quantities_loaded' );

		$this->do_action( 'loaded' );
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

		if ( ! $this->is_pro_active() ) {
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
	 * Whether the Pro add-on is active.
	 *
	 * @since 2.3.2
	 * @return bool True when the Pro add-on is active.
	 */
	public function is_pro_active(): bool {
		$pro_basename = $this->get( 'pro_basename', '' );
		return ! empty( $pro_basename ) && $this->plugin_active( (string) $pro_basename );
	}
}

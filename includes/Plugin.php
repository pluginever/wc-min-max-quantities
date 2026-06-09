<?php

namespace PluginEver\MinMaxQuantities;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 *
 * @since   1.1.4
 * @package PluginEver\MinMaxQuantities
 *
 * @property-read string $settings_url Settings page URL.
 * @property-read string $docs_url     Documentation URL.
 * @property-read string $support_url  Support page URL.
 */
final class Plugin extends B8\App {

	/**
	 * Components to register.
	 *
	 * @since 2.3.0
	 * @var array<int|string, class-string>
	 */
	protected array $components = array(
		Installer::class,
		Cart::class,
		Admin\Settings::class,
		Admin\Admin::class,
	);

	/**
	 * Bootstraps the plugin.
	 *
	 * @since 2.2.4
	 * @return void
	 */
	public function bootstrap(): void {
		// Keep our caches request-scoped. The cached product limits pass through
		// filters whose output can depend on the current user (e.g. role-based
		// overrides from the Pro add-on), so persisting them across requests in
		// Redis/Memcached/etc. would leak one user's limits to another.
		wp_cache_add_non_persistent_groups( array( 'wc-min-max-quantities' ) );

		add_action( 'before_woocommerce_init', array( $this, 'declare_compatibility' ) );
		add_action( 'woocommerce_loaded', array( $this, 'woocommerce_loaded' ), 0 );
	}

	/**
	 * Declare WooCommerce compatibility.
	 *
	 * @since 1.1.5
	 * @return void
	 */
	public function declare_compatibility(): void {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->file, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', $this->file, true );
		}
	}

	/**
	 * Boot the plugin components.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function woocommerce_loaded(): void {
		$this->boot( $this->components );

		/**
		 * Fires after the plugin has booted its components.
		 *
		 * @since 1.0.0
		 */
		$this->do_action( 'loaded' );
	}

	/**
	 * Whether the Pro add-on is active.
	 *
	 * @since 2.3.0
	 * @return bool True when the Pro add-on is active.
	 */
	public function is_pro_active(): bool {
		return ! empty( $this->pro_basename ) && $this->plugin_active( $this->pro_basename );
	}
}

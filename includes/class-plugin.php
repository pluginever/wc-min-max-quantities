<?php

namespace WooCommerceMinMaxQuantities;

// don't call the file directly.

defined( 'ABSPATH' ) || exit();

/**
 * Main plugin class.
 *
 * @since 1.0.0
 * @package WooCommerceMinMaxQuantities
 */
final class Plugin extends Framework\Plugin {
	/**
	 * Setup plugin constants.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function define_constants() {
		define( 'WC_MIN_MAX_QUANTITIES_VERSION', $this->get_version() );
		define( 'WC_MIN_MAX_QUANTITIES_FILE', $this->get_file() );
		define( 'WC_MIN_MAX_QUANTITIES_PATH', $this->get_plugin_path() );
		define( 'WC_MIN_MAX_QUANTITIES_URL', $this->get_plugin_url() );
		define( 'WC_MIN_MAX_QUANTITIES_ASSETS', $this->get_assets_url() );
	}

	/**
	 * Setup plugin hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'add_controllers' ) );
	}

	/**
	 * Initialize controllers.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_controllers() {
		$controllers = array(
			'installer' => Installer::class,
			'store'     => Store::class,
		);

		// If is admin, add admin controllers.
		if ( self::is_request( 'admin' ) ) {
			$controllers['admin'] = Admin\Admin::class;
		}
		$this->add_controller( $controllers );
	}

	/**
	 * Registers plugin actions on blog pages.
	 *
	 * @param string[] $actions An array of plugin action links.
	 *
	 * @since  1.0.0
	 * @return string[]
	 */
	public function register_plugin_actions( $actions ) {
		$actions = parent::register_plugin_actions( $actions );
		// add upgrade to pro link.
		if ( ! $this->is_premium_active() ) {
			$actions['upgrade_to_pro'] = sprintf(
				'<a href="%s" target="_blank" style="color: #39b54a; font-weight: bold;">%s</a>',
				self::generate_utm_url( 'https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/', 'upgrade-to-pro' ),
				__( 'Upgrade to Pro', 'wc-min-max-quantities' )
			);
		}

		return $actions;
	}

	/**
	 * Is premium version active.
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	public function is_premium_active() {
		return self::is_plugin_active( 'wc-min-max-quantities-pro/wc-min-max-quantities-pro.php' );
	}
}

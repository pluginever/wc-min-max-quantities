<?php

namespace WC_Min_Max_Quantities;

// don't call the file directly.

defined( 'ABSPATH' ) || exit();

/**
 * Main plugin class.
 *
 * @since 1.0.0
 * @package WC_Min_Max_Quantities
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
		$this->add_controller(
			[
				'installer' => Installer::class,
				'store'     => Store::class,
				'admin'     => Admin\Admin::class,
			]
		);
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
		if ( ! self::is_plugin_active( 'wc-min-max-quantities-pro/wc-min-max-quantities-pro.php' ) ) {
			$actions['upgrade_to_pro'] = sprintf(
				'<a href="%s" target="_blank" style="color: #39b54a; font-weight: bold;">%s</a>',
				self::generate_utm_url( 'https://pluginever.com/products/plugins/wc-min-max-quantities-pro/', 'upgrade-to-pro' ),
				__( 'Upgrade to Pro', 'wc-min-max-quantities' )
			);
		}

		return $actions;
	}
}

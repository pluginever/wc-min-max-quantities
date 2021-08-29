<?php
/**
 * Plugin setup
 *
 * @package ByteEver\PluginScaffold
 * @since   1.1.0
 */

namespace PluginEver\MinMaxQuantities;

use ByteEver\Container\Container;
use ByteEver\Plugin\App;
use ByteEver\Plugin\Utils;
use ByteEver\Settings\Options;
use PluginEver\MinMaxQuantities\Admin\AdminManager;

/**
 * Class Plugin
 *
 * @package PluginEver\MinMaxQuantities
 */
final class Plugin extends App {
	/**
	 * The single instance of the class.
	 *
	 * @since 1.0.0
	 * @var Plugin
	 */
	protected static $instance = null;

	/**
	 * Service container.
	 *
	 * @since 1.0.0
	 * @var Container
	 */
	public $container;

	/**
	 * Options container.
	 *
	 * @since 1.0.0
	 * @var Options
	 */
	public $options;

	/**
	 * Main plugin Instance.
	 *
	 * Insures that only one instance of plugin exists in memory at any
	 * time. Also prevents needing to define globals all over the place
	 *
	 * @return Plugin - Main instance.
	 * @since 1.0.0
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance            = new self();
			self::$instance->container = new Container();
			self::$instance->init_services();
		}

		return self::$instance;
	}

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		parent::__construct(
			[
				'name'          => 'WooCommerce Min Max Quantities',
				'version'       => WC_MINMAX_VERSION,
				'file'          => WC_MINMAX_FILE,
				'docs_url'      => 'https://pluginever.com/docs/min-max-quantities-for-woocommerce/',
				'support_url'   => 'https://pluginever.com/my-account/support-ticket/',
				'settings_path' => 'admin.php?page=wc-minmax-settings',
			]
		);
	}


	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'wc-minmax-quantities' ), '1.0.0' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'wc-minmax-quantities' ), '1.0.0' );
	}

	/**
	 * Init DI Container, set all services as globals
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function init_services() {
		$container = &$this->container;
		if ( ! Utils::is_plugin_active( 'woocommerce' ) ) {
			add_action( 'admin_notices', [ $this, 'wc_missing_notice' ] );
			deactivate_plugins( $this->get_plugin_file(), true );
			return;
		}

		$this->options = new Options( 'wc_minmax_settings' );
		if ( is_admin() ) {
			$container->addProvider( AdminManager::class );
		}

		// Register hooks.
		$this->register();
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function register() {
		// Localize our plugin
		add_action( 'init', [ $this, 'localization_setup' ] );
	}

	/**
	 * Show error message if WooCommerce is disabled
	 *
	 * @return  void
	 * @since   1.1.0
	 */
	public function wc_missing_notice() {
		?>
		<div class="error">
			<p>
				<?php
				/* translators: %s name of the plugin */
				echo sprintf( esc_html__( '%s is disabled. In order to work, it requires WooCommerce.', 'wc-minmax-quantities' ), 'WooCommerce Min Max Quantities' );
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Initialize plugin for localization
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'wc-minmax-quantities', false, plugin_basename( dirname( WC_MINMAX_FILE ) ) . '/languages' );
	}
}

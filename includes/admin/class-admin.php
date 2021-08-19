<?php
/**
 * MinMax Admin.
 *
 * @package WCMinMax
 */

defined( 'ABSPATH' ) || exit();

/**
 * Class Admin
 */
class WC_MINMAX_Admin {
	/**
	 * Class Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'wc_minmax_settings_tab_help_main_content', array( $this, 'help_page_content' ) );
	}

	/**
	 * Include Files
	 */
	public function includes() {
		require_once dirname( __FILE__ ) . '/class-settings.php';
		require_once dirname( __FILE__ ) . '/metabox-functions.php';

		if ( ! wc_minmax_quantities()::is_pro_installed() ) {
			require_once dirname( __FILE__ ) . '/class-promotion.php';
		}
	}

	/**
	 * help Page content
	 */
	public function help_page_content() {
		$GLOBALS['hide_save_button'] = true;
		require_once dirname( __FILE__ ) . '/settings/views/html-admin-help.php';
	}
}

new WC_MINMAX_Admin();

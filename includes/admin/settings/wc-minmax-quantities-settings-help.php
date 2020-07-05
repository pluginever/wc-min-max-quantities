<?php
/**
 * WooCommerce CRM Updates Settings
 *
 * @author      Actuality Extensions
 * @category    Admin
 * @package     WC_CRM/Admin
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Minmax_Quantities_Settings_Help extends WC_Settings_Page {

	/**
	 * constructor.
	 */
	public function __construct() {
		$this->id    = 'help';
		$this->label = __( 'Help', 'wc-minmax-quantities' );
		add_filter('wc_minmax_quantities_settings_tabs_array', array($this, 'add_settings_page'), 99);
		add_action('wc_minmax_quantities_settings_' . $this->id, array($this, 'output'));
		add_action('wc_minmax_quantities_settings_save_' . $this->id, array($this, 'save'));
	}

	public function output() {
		$GLOBALS['hide_save_button'] = true;
		include dirname( __DIR__ ) . '/settings/views/html-admin-help.php';
	}

	public function save() {

	}
}

return new WC_Minmax_Quantities_Settings_Help();

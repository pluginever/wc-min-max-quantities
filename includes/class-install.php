<?php

namespace Pluginever\WCMinMaxQuantities;

class Install {
	/**
	 * Install constructor.
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'install' ) );
	}

	public static function install() {
		if ( get_option( 'wc_minmax_quantitiess_install_date' ) ) {
			return;
		}

		if ( ! is_blog_installed() ) {
			return;
		}

		// Check if we are not already running this routine.
		if ( 'yes' === get_transient( 'wc_minmax_quantitiess_installing' ) ) {
			return;
		}

		self::create_options();

		delete_transient( 'wc_minmax_quantitiess_installing' );
	}

	/**
	 * Save option data
	 */
	private static function create_options() {
		//save db version
		update_option( 'wpcp_version', WC_MINMAX_VERSION );

		//save install date
		$installed = get_option( 'wc_minmax_quantitiess_install_date', '' );
		if ( empty( $installed ) ) {
			update_option( 'wc_minmax_quantitiess_install_date', current_time( 'timestamp' ) );
		}
	}
}

new Install();

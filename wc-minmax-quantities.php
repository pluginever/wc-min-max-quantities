<?php
/**
 * Backwards compat.
 *
 * @since 1.2.0
 */

defined( 'ABSPATH' ) || exit;

$active_plugins = get_option( 'active_plugins', array() );
foreach ( $active_plugins as $key => $active_plugin ) {
	if ( strpos( $active_plugin, '/wc-minmax-quantities.php' ) !== false ) {
		$active_plugins[ $key ] = str_replace( '/wc-minmax-quantities.php', '/wc-min-max-quantities.php', $active_plugin );
		// Update legacy data.
		$legacy_date = get_option( 'wc_minmax_quantitiess_install_date' );
		if ( ! empty( $legacy_date ) ) {
			if ( ! is_numeric( $legacy_date ) ) {
				$legacy_date = strtotime( $legacy_date );
			}
			update_option( 'wc_min_max_quantities_install_date', $legacy_date );
			delete_option( 'wc_minmax_quantitiess_install_date' );
			update_option( 'wc_min_max_quantities_version', '1.0.9' );
		}
	}
}
update_option( 'active_plugins', $active_plugins );

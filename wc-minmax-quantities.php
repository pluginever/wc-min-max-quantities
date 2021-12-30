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
	}
}
update_option( 'active_plugins', $active_plugins );

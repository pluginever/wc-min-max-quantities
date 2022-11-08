<?php
/**
 * Starter Plugin Uninstall
 *
 * Uninstalling Starter Plugin deletes user roles, pages, tables, and options.
 *
 * @package     WC_Min_Max_Quantities
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// remove all the options starting with wc_min_max_quantities.
$delete_all_options = get_option( 'wc_min_max_quantities_delete_data' );
if ( empty( $delete_all_options ) ) {
	return;
}
// Delete all the options.
global $wpdb;
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'wc_min_max_quantities%';" );


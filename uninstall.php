<?php
/**
 * Plugin uninstall cleanup.
 *
 * Data deletion only happens when the site owner opted in through the
 * "Delete data on uninstall" setting (option: wcmmq_delete_data).
 * User-generated content is intentionally NOT removed here.
 *
 * @since   2.3.2
 * @package PluginEver\MinMaxQuantities
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

// Bail out unless data deletion is explicitly enabled.
if ( 'yes' !== get_option( 'wcmmq_delete_data', 'no' ) ) {
	return;
}

// Remove plugin options.
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wcmmq\\_%' ESCAPE '\\\\'" );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wc\\_min\\_max\\_quantities\\_%' ESCAPE '\\\\'" );

// Remove product level limits.
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '\\_wcmmq\\_%' ESCAPE '\\\\'" );

// Remove plugin transients (single-site).
$wpdb->query(
	"DELETE FROM {$wpdb->options}
	 WHERE option_name LIKE '\\_transient\\_wcmmq\\_%' ESCAPE '\\\\'
	    OR option_name LIKE '\\_transient\\_timeout\\_wcmmq\\_%' ESCAPE '\\\\'"
);
$wpdb->query(
	"DELETE FROM {$wpdb->options}
	 WHERE option_name LIKE '\\_transient\\_wc\\_min\\_max\\_quantities\\_%' ESCAPE '\\\\'
	    OR option_name LIKE '\\_transient\\_timeout\\_wc\\_min\\_max\\_quantities\\_%' ESCAPE '\\\\'"
);

// Clear the background queue's recurring health-check cron event.
wp_clear_scheduled_hook( 'wc_min_max_quantities_queue_cron' );

// Flush object cache.
wp_cache_flush();

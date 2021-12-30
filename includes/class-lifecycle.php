<?php
/**
 * Class Lifecycle
 *
 * @since    1.1.0
 * @package  WC_Min_Max_Quantities
 */

namespace WC_Min_Max_Quantities;

use WC_Min_Max_Quantities\Utilities\Background_Updater;

defined( 'ABSPATH' ) || exit();

/**
 * LifeCycle class.
 */
class Lifecycle {

	/**
	 * Updates and callbacks that need to be run per version.
	 *
	 * @since 1.1.0
	 * @var array
	 */
	protected $updates = array(
		'1.0.8' => array( __CLASS__, 'update_108' ),
	);

	/**
	 * Plugin background updater.
	 *
	 * @since 1.1.0
	 * @var Background_Updater
	 */
	protected $background_updater;

	/**
	 * Lifecycle constructor.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init_classes' ) );
		add_action( 'init', array( $this, 'maybe_install' ) );
		add_action( 'admin_init', array( $this, 'maybe_update' ) );
	}

	/**
	 * Initialize the dependent classes.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function init_classes(){
		$this->background_updater = new Background_Updater();
	}

	/**
	 * Check version and run the installer if necessary.
	 *
	 * @since  1.1.0
	 */
	public function maybe_install() {
		if ( ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && ! defined( 'IFRAME_REQUEST' ) && empty( self::get_db_version() ) ) {
			self::install();
		}
	}

	/**
	 * Perform all the necessary upgrade routines.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function maybe_update() {
		if ( $this->needs_db_update() ) {
			$installed_version = self::get_db_version();
			foreach ( $this->updates as $version => $update_callbacks ) {
				if ( version_compare( $installed_version, $version, '<' ) ) {
					foreach ( (array) $update_callbacks as $update_callback ) {
						$this->background_updater->push_to_queue( $update_callback );
					}
				}
			}
			$this->background_updater->save()->dispatch();
			self::update_db_version();
		}
	}

	/**
	 * Is a DB update needed?
	 *
	 * @since  1.1.0
	 * @return boolean
	 */
	public function needs_db_update() {
		$current_db_version = self::get_db_version();

		return ! empty( $current_db_version ) && version_compare( $current_db_version, Plugin::instance()->version, '<' );
	}

	/**
	 * Gets the currently installed plugin database version.
	 *
	 * @since 1.1.0
	 * @return string
	 */
	public static function get_db_version() {
		return get_option( 'wc_min_max_quantities_version', null );
	}

	/**
	 * Update the installed plugin database version.
	 *
	 * @param string $version version to set
	 *
	 * @since 1.1.0
	 */
	public static function update_db_version( $version = null ) {
		update_option( 'wc_min_max_quantities_version', is_null( $version ) ? Plugin::instance()->version : $version );
	}

	/**
	 * Performs any install tasks.
	 *
	 * @since 1.1.0
	 */
	public static function install() {
		if ( ! self::get_db_version() ) {
			self::update_db_version();
		}
		if ( ! get_option( 'wc_min_max_quantities_install_date' ) ) {
			update_option( 'wc_min_max_quantities_install_date', current_time( 'mysql' ) );
		}

		// self::create_tables();
	}

	/**
	 * Get Table schema.
	 *
	 * When adding or removing a table, make sure to update the list of tables in get_tables().
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}
		/*
		 * Indexes have a maximum size of 767 bytes. Historically, we haven't need to be concerned about that.
		 * As of WP 4.2, however, they moved to utf8mb4, which uses 4 bytes per character. This means that an index which
		 * used to have room for floor(767/3) = 255 characters, now only has room for floor(767/4) = 191 characters.
		 */
		$max_index_length = 191;

		$tables = array(
			"CREATE TABLE {$wpdb->prefix}sp_customers(
            `id` bigINT(20) NOT NULL AUTO_INCREMENT,
			`first_name` VARCHAR(191) NOT NULL,
			`last_name` VARCHAR(191) NOT NULL,
			`email` VARCHAR(100) NOT NULL,
			`phone` VARCHAR(50) NOT NULL,
		    `date_created` DATETIME NULL DEFAULT NULL,
		    `date_updated` DATETIME NULL DEFAULT NULL,
		    PRIMARY KEY (`id`)
            ) $collate",

			"CREATE TABLE {$wpdb->prefix}sp_customermeta(
			`meta_id` bigINT(20) NOT NULL AUTO_INCREMENT,
			`sp_customer_id` bigint(20) unsigned NOT NULL default '0',
			`meta_key` varchar(255) default NULL,
			`meta_value` longtext,
			 PRIMARY KEY (`meta_id`),
		    KEY `sp_customer_id`(`sp_customer_id`),
			KEY `meta_key` (meta_key($max_index_length))
			) $collate",
		);

		foreach ( $tables as $table ) {
			dbDelta( $table );
		}
	}

	/**
	 * Remove plugin related cron options.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public static function uninstall(){
		// placeholder function.
	}


	/**
	 * Upgrade to 1.0.8
	 *
	 * @since 1.0.8
	 * @return void
	 */
	public static function update_108(){
		$general_settings = get_option('wc_minmax_quantity_general_settings');
		$advnace_settings = get_option('wc_minmax_quantity_advanced_settings');

		$min_product_quantity = isset( $general_settings['min_product_quantity'] ) ? $general_settings['min_product_quantity'] : false;
		update_option('wc_minmax_quantities_min_product_quantity', $min_product_quantity);

		$max_product_quantity = isset( $general_settings['max_product_quantity'] ) ? $general_settings['max_product_quantity'] : false;
		update_option('wc_minmax_quantities_max_product_quantity', $max_product_quantity);

		$min_cart_price = isset( $general_settings['min_cart_price'] ) ? $general_settings['min_cart_price'] : false;
		update_option('wc_minmax_quantities_min_product_price', $min_cart_price);

		$max_cart_price = isset( $general_settings['max_cart_price'] ) ? $general_settings['max_cart_price'] : false;
		update_option('wc_minmax_quantities_max_product_price', $max_cart_price);

		$hide_checkout = isset( $general_settings['hide_checkout'] ) ? $general_settings['hide_checkout'] : false;
		if( $hide_checkout === 'on'){
			$hide_checkout = 'yes';
		}
		update_option('wc_minmax_quantities_hide_checkout', $hide_checkout);

		$min_cart_total_price = isset( $advnace_settings['min_cart_total_price'] ) ? $advnace_settings['min_cart_total_price'] : false;
		update_option('wc_minmax_quantities_min_cart_total_price', $min_cart_total_price);

		$max_cart_total_price = isset( $advnace_settings['max_cart_total_price'] ) ? $advnace_settings['max_cart_total_price'] : false;
		update_option('wc_minmax_quantities_max_cart_total_price', $max_cart_total_price);
	}
}

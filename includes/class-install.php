<?php

namespace PluginEver\WooCommerceMinMaxQuantities;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Class Install
 *
 * @since    1.1.0
 * @version  1.1.0
 * @package  PluginEver\WooCommerceMinMaxQuantities
 */
class Install {

	/**
	 * Updates and callbacks that need to be run per version.
	 *
	 * @since 1.1.0
	 * @var array
	 */
	protected static $updates = array(
//		'1.0.8' => array( __CLASS__, 'update_108' ),
//		'1.1.0' => array(
//			array( __CLASS__, 'update_110_settings' ),
//			array( __CLASS__, 'update_110_categories' ),
//			array( __CLASS__, 'update_110_products' ),
//		),
	);

	/**
	 * Register hooks.
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'init_classes' ) );
		add_action( 'init', array( __CLASS__, 'maybe_install' ) );
		add_action( 'init', array( __CLASS__, 'maybe_update' ) );
		add_action( 'admin_init', [ __CLASS__, 'admin_init' ] );
	}

	/**
	 * Initialize the dependent classes.
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public static function init_classes() {
	}

	/**
	 * Check version and run the installer if necessary.
	 *
	 * @since  1.1.0
	 */
	public static function maybe_install() {
		if ( ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && ! defined( 'IFRAME_REQUEST' ) && version_compare( self::get_db_version(), Plugin::instance()->get_plugin_version(), '!=' ) ) {
			self::install();
		}
	}

	/**
	 * Perform all the necessary upgrade routines.
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public static function maybe_update() {
		$current_db_version = self::get_db_version();
		if ( ! empty( $current_db_version ) && version_compare( $current_db_version, Plugin::instance()->get_plugin_version(), '<' ) ) {
			self::install();
			$logger = wc_get_logger();
			foreach ( self::$updates as $version => $update_callbacks ) {
				if ( version_compare( $current_db_version, $version, '<' ) ) {
					if ( is_callable( $update_callbacks ) ) {
						$update_callbacks = [ $update_callbacks ];
					}
					foreach ( $update_callbacks as $update_callback ) {
						$logger->info(
							sprintf( 'Queuing %s - %s', $version, $update_callback ),
							array( 'source' => 'wc_min_max_quantities' )
						);
						call_user_func( $update_callback );
					}
				}
			}
			self::update_db_version();
		}
	}

	/**
	 * Is a DB update needed?
	 *
	 * @return boolean
	 * @since  1.1.0
	 */
	protected static function needs_db_update() {
		$current_db_version = self::get_db_version();

		return ! empty( $current_db_version ) && version_compare( $current_db_version, Plugin::instance()->get( 'version' ), '<' );
	}

	/**
	 * Gets the currently installed plugin database version.
	 *
	 * @return string
	 * @since 1.1.0
	 */
	protected static function get_db_version() {
		return get_option( 'wc_min_max_quantities_version', null );
	}

	/**
	 * Update the installed plugin database version.
	 *
	 * @param string $version version to set
	 *
	 * @since 1.1.0
	 */
	protected static function update_db_version( $version = null ) {
		update_option( 'wc_min_max_quantities_version', is_null( $version ) ? Plugin::instance()->get( 'version' ) : $version );
	}

	/**
	 * Redirect to settings page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function admin_init() {
		if ( Plugin::instance()->get_plugin_settings_url() && 'yes' === get_transient( 'wc_min_max_quantities_activated' ) ) {
			delete_transient( 'wc_min_max_quantities_activated' );
			wp_safe_redirect( Plugin::instance()->get_plugin_settings_url() );
			exit();
		}
	}

	/**
	 * Install Plugin.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public static function install() {
		if ( ! is_blog_installed() ) {
			return;
		}

		// Set transient.
		set_transient( 'wc_min_max_quantities_activated', 'yes', MINUTE_IN_SECONDS * 10 );

		// Use add_option() here to avoid overwriting this value with each.
		// plugin version update. We base plugin age off of this value.
		add_option( 'wc_min_max_quantities_install_timestamp', time() );
		add_option( 'wc_min_max_quantities_version', Plugin::instance()->get_plugin_version() );
		self::create_options();
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 *
	 * @since 1.0.0
	 */
	private static function create_options() {
		include_once Plugin::instance()->get_plugin_path() . '/includes/admin/class-admin-settings.php';
		$tabs = Admin_Settings::get_tabs();

		foreach ( array_keys( $tabs ) as $tab_id ) {
			$subsections = array_unique( array_merge( array( '' ), array_keys( Admin_Settings::get_sections( $tab_id ) ) ) );
			foreach ( array_keys( $subsections ) as $subsection ) {
				foreach ( Admin_Settings::get_settings_for_tab_section( $tab_id, $subsection ) as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
		}
	}

	/**
	 * Remove plugin related options.
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public static function uninstall() {
		// placeholder function.
	}

	/**
	 * Upgrade to 1.0.8
	 *
	 * @return void
	 * @since 1.0.8
	 */
	public static function update_108() {
		$general_settings = get_option( 'wc_minmax_quantity_general_settings' );
		$advnace_settings = get_option( 'wc_minmax_quantity_advanced_settings' );

		$min_product_quantity = isset( $general_settings['min_product_quantity'] ) ? $general_settings['min_product_quantity'] : false;
		update_option( 'wc_minmax_quantities_min_product_quantity', $min_product_quantity );

		$max_product_quantity = isset( $general_settings['max_product_quantity'] ) ? $general_settings['max_product_quantity'] : false;
		update_option( 'wc_minmax_quantities_max_product_quantity', $max_product_quantity );

		$min_cart_price = isset( $general_settings['min_cart_price'] ) ? $general_settings['min_cart_price'] : false;
		update_option( 'wc_minmax_quantities_min_product_price', $min_cart_price );

		$max_cart_price = isset( $general_settings['max_cart_price'] ) ? $general_settings['max_cart_price'] : false;
		update_option( 'wc_minmax_quantities_max_product_price', $max_cart_price );

		$hide_checkout = isset( $general_settings['hide_checkout'] ) ? $general_settings['hide_checkout'] : false;
		if ( $hide_checkout === 'on' ) {
			$hide_checkout = 'yes';
		}
		update_option( 'wc_minmax_quantities_hide_checkout', $hide_checkout );

		$min_cart_total_price = isset( $advnace_settings['min_cart_total_price'] ) ? $advnace_settings['min_cart_total_price'] : false;
		update_option( 'wc_minmax_quantities_min_cart_total_price', $min_cart_total_price );

		$max_cart_total_price = isset( $advnace_settings['max_cart_total_price'] ) ? $advnace_settings['max_cart_total_price'] : false;
		update_option( 'wc_minmax_quantities_max_cart_total_price', $max_cart_total_price );
	}

	/**
	 * Upgrade to 1.1.0
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public static function update_110_settings() {
		$updated_settings  = [];
		$general_settings  = get_option( 'wc_minmax_quantity_general_settings', array() );
		$advanced_settings = get_option( 'wc_minmax_quantity_advanced_settings', array() );

		$general_keys = array(
			'min_product_quantity' => 'general_min_product_quantity',
			'max_product_quantity' => 'general_max_product_quantity',
			'min_cart_price'       => 'general_min_order_quantity',
			'max_cart_price'       => 'general_max_order_quantity',
			'min_cart_total_price' => 'general_min_order_amount',
			'max_cart_total_price' => 'general_max_order_amount',
		);
		foreach ( $general_keys as $old_key => $new_key ) {
			if ( ! empty( $general_settings[ $old_key ] ) ) {
				$updated_settings[ $new_key ] = absint( $general_settings[ $old_key ] );
			}
		}

		$advanced_keys = array(
			'min_cart_total_price'    => 'general_min_order_amount',
			'max_cart_total_price'    => 'general_max_order_amount',
			'min_cart_total_quantity' => 'general_max_order_amount',
			'max_cart_total_quantity' => 'general_max_order_amount',
		);

		foreach ( $advanced_keys as $old_key => $new_key ) {
			if ( ! empty( $advanced_settings[ $old_key ] ) ) {
				$updated_settings[ $new_key ] = absint( $advanced_settings[ $old_key ] );
			}
		}

		foreach ( $updated_settings as $key => $value ) {
			Plugin::get( 'settings' )->update_option( $key, $value );
		}

	}

	/**
	 * Upgrade to 1.1.0
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public static function update_110_categories() {
		$categories = get_categories(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'fields'     => 'ids',
			)
		);
		$term_metas = array(
			'cat_min_quantity' => '_wc_min_max_quantities_min_qty',
			'cat_max_quantity' => '_wc_min_max_quantities_max_qty',
			'cat_min_price'    => '_wc_min_max_quantities_min_total',
			'cat_max_price'    => '_wc_min_max_quantities_max_total',
		);

		foreach ( $categories as $term_id ) {
			foreach ( $term_metas as $old_key => $new_key ) {
				$value = get_term_meta( $term_id, $old_key, true );
				if ( ! empty( $value ) ) {
					update_term_meta( $term_id, $new_key, $value );
				}
				delete_term_meta( $term_id, $old_key );
			}
		}
	}

	/**
	 * Upgrade to 1.1.0
	 *
	 * @return bool
	 * @since 1.1.0
	 */
	public static function update_110_products() {
		$migrated = get_option( 'wc_minmax_quantities_migrated_products', array() );
		$products = get_posts(
			array(
				'post_type'   => [ 'product', 'product_variation' ],
				'post_status' => 'any',
				'exclude'     => wp_parse_id_list( $migrated ),
				'fields'      => 'ids',
			)
		);

		if ( empty( $products ) ) {
			delete_option( 'wc_minmax_quantities_migrated_products' );

			return false;
		}

		foreach ( $products as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( 'variation' === $product->get_type() ) {
				$post_metas = array(
					'manage_minmax_quantities'       => '_wc_min_max_quantities_override',
					'vr_minmax_product_min_quantity' => '_wc_min_max_quantities_min_qty',
					'vr_minmax_product_max_quantity' => '_wc_min_max_quantities_max_qty',
					'vr_minmax_product_min_price'    => '_wc_min_max_quantities_min_total',
					'vr_minmax_product_max_price'    => '_wc_min_max_quantities_max_total',
				);
			} else {
				$post_metas = array(
					'_minmax_product_min_quantity' => '_wc_min_max_quantities_min_qty',
					'_minmax_product_max_quantity' => '_wc_min_max_quantities_max_qty',
					'_minmax_product_min_price'    => '_wc_min_max_quantities_min_total',
					'_minmax_product_max_price'    => '_wc_min_max_quantities_max_total',
					'_minmax_ignore_global'        => '_wc_min_max_quantities_override',
				);
			}

			foreach ( $post_metas as $old_key => $new_key ) {
				$value = get_post_meta( $product_id, $old_key, true );
				if ( $value ) {
					update_post_meta( $product_id, $new_key, $value );
				}

				delete_post_meta( $product_id, $old_key );
			}
		}
		$migrated = array_merge( $migrated, $products );

		update_option( 'wc_minmax_quantities_migrated_products', $migrated );

		return true;
	}
}

Install::init();

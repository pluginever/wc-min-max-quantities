<?php

namespace WooCommerceMinMaxQuantities;

defined( 'ABSPATH' ) || exit;

/**
 * Class Installer.
 *
 * @since 1.1.4
 * @package WooCommerceMinMaxQuantities
 */
class Installer {

	/**
	 * Update callbacks.
	 *
	 * @since 1.1.4
	 * @var array
	 */
	protected $updates = array(
		'1.0.8' => 'update_108',
		'1.1.0' => array( 'update_110_settings', 'update_110_categories', 'update_110_products' ),
		'1.1.5' => 'update_115',
	);

	/**
	 * Installer constructor.
	 *
	 * @since 1.1.4
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'check_update' ), 0 );
	}

	/**
	 * Check the plugin version and run the updater if necessary.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 *
	 * @since 1.1.4
	 * @return void
	 */
	public function check_update() {
		$db_version      = wc_min_max_quantities()->get_db_version();
		$current_version = wc_min_max_quantities()->get_version();
		$requires_update = version_compare( $db_version, $current_version, '<' );
		$can_install     = ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && ! defined( 'IFRAME_REQUEST' );
		if ( $can_install && $requires_update ) {
			static::install();
			$update_versions = array_keys( $this->updates );
			usort( $update_versions, 'version_compare' );
			if ( ! is_null( $db_version ) && version_compare( $db_version, end( $update_versions ), '<' ) ) {
				$this->update();
			} else {
				wc_min_max_quantities()->update_db_version( $current_version );
			}
		}
	}

	/**
	 * Update the plugin.
	 *
	 * @since 1.1.4
	 * @return void
	 */
	public function update() {
		$db_version = wc_min_max_quantities()->get_db_version();
		foreach ( $this->updates as $version => $callbacks ) {
			$callbacks = (array) $callbacks;
			if ( version_compare( $db_version, $version, '<' ) ) {
				foreach ( $callbacks as $callback ) {
					wc_min_max_quantities()->log( sprintf( 'Updating to %s from %s', $version, $db_version ) );
					// if the callback return false then we need to update the db version.
					$continue = call_user_func( array( $this, $callback ) );
					if ( ! $continue ) {
						wc_min_max_quantities()->update_db_version( $version );
						$notice = sprintf(
						/* translators: 1: plugin name 2: version number */
							__( '%1$s updated to version %2$s successfully.', 'wc-min-max-quantities' ),
							'<strong>' . wc_min_max_quantities()->get_name() . '</strong>',
							'<strong>' . $version . '</strong>'
						);
						wc_min_max_quantities()->flash->success( $notice );
					}
				}
			}
		}
	}

	/**
	 * Install the plugin.
	 *
	 * @since 1.1.4
	 * @return void
	 */
	public static function install() {
		if ( ! is_blog_installed() ) {
			return;
		}

		Admin\Settings::instance()->save_defaults();
		wc_min_max_quantities()->update_db_version( wc_min_max_quantities()->get_version(), false );
		add_option( 'wc_min_max_quantities_install_date', current_time( 'mysql' ) );
		set_transient( 'wc_min_max_quantities_activated', true, 30 );
		set_transient( 'wc_min_max_quantities_activation_redirect', true, 30 );
		add_option( 'wc_min_max_quantities_installed', wp_date( 'U' ) );
	}

	/**
	 * Upgrade to 1.0.8
	 *
	 * @since 1.0.8
	 * @return void
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
		if ( 'on' === $hide_checkout ) {
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
	 * @since 1.1.0
	 * @return void
	 */
	public static function update_110_settings() {
		$updated_settings  = array();
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
			update_option( $key, $value );
		}
	}

	/**
	 * Upgrade to 1.1.0
	 *
	 * @since 1.1.0
	 * @return void
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
	 * @since 1.1.0
	 * @return bool
	 */
	public static function update_110_products() {
		$migrated = get_option( 'wc_minmax_quantities_migrated_products', array() );
		$products = get_posts(
			array(
				'post_type'   => array( 'product', 'product_variation' ),
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

	/**
	 * Upgrade to 1.1.4
	 *
	 * @since 1.1.4
	 * @return void
	 */
	public static function update_115() {
		global $wpdb;
		$settings = get_option( 'wc_min_max_quantities_settings', array() );
		$map      = array(
			'general_min_product_quantity'  => 'wcmmq_min_qty',
			'general_max_product_quantity'  => 'wcmmq_max_qty',
			'general_product_quantity_step' => 'wcmmq_step',
			'general_min_order_quantity'    => 'wcmmq_min_cart_qty',
			'general_max_order_quantity'    => 'wcmmq_max_cart_qty',
			'general_min_order_amount'      => 'wcmmq_min_cart_total',
			'general_max_order_amount'      => 'wcmmq_max_cart_total',
		);

		foreach ( $map as $old_key => $new_key ) {
			if ( isset( $settings[ $old_key ] ) ) {
				$value = $settings[ $old_key ];
				update_option( $new_key, $value );
			}
		}

		$post_meta = array(
			'_wc_min_max_quantities_min_qty'  => '_wcmmq_min_qty',
			'_wc_min_max_quantities_max_qty'  => '_wcmmq_max_qty',
			'_wc_min_max_quantities_step'     => '_wcmmq_step',
			'_wc_min_max_quantities_excluded' => '_wcmmq_disable',
			'_wc_min_max_quantities_override' => '_wcmmq_enable',
		);
		foreach ( $post_meta as $old_key => $new_key ) {
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->postmeta} SET meta_key = %s WHERE meta_key = %s",
					$new_key,
					$old_key
				)
			);
		}
	}
}

<?php

namespace WooCommerceMinMaxQuantities;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Class Installer.
 *
 * @since 1.0.0
 * @package WooCommerceMinMaxQuantities
 */
class Installer extends Controller {

	/**
	 * Update callbacks.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $updates = array(
		'1.0.0' => 'update_100',
		'1.0.8' => 'update_108',
		'1.1.0' => array(
			'update_110_settings',
			'update_110_categories',
			'update_110_products',
		),
		'1.1.3' => 'update_113',
	);

	/**
	 * Set up the controller.
	 *
	 * Load files or register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function init() {
		add_action( 'wc_min_max_quantities_activated', array( $this, 'install' ) );
		add_action( 'init', array( $this, 'check_version' ), 5 );
	}

	/**
	 * Install the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function install() {
		global $wpdb;
		$wpdb->hide_errors();
		$db_version = $this->get_plugin()->get_db_version();
		$collate    = $wpdb->has_cap( 'collation' ) ? $wpdb->get_charset_collate() : '';
		if ( ! is_blog_installed() ) {
			return;
		}

		add_option( $this->get_plugin()->get_db_version_name(), $this->get_plugin()->get_version() );
		add_option( $this->get_plugin()->get_activation_date_name(), current_time( 'mysql' ) );

		if ( ! $db_version ) {
			/**
			 * Fires after the plugin is installed for the first time.
			 *
			 * @since 1.0.0
			 */
			do_action( $this->get_plugin()->get_id() . '_newly_installed' );
			set_transient( $this->get_plugin()->get_id() . '_activation_redirect', 1, 30 );
		}
	}

	/**
	 * Check plugin version and run the updater if necessary.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function check_version() {
		$db_version      = $this->get_plugin()->get_db_version();
		$current_version = $this->get_plugin()->get_version();
		$requires_update = version_compare( $db_version, $current_version, '<' );

		if ( ! defined( 'IFRAME_REQUEST' ) && $requires_update ) {
			$this->install();

			$update_versions = array_keys( $this->updates );
			usort( $update_versions, 'version_compare' );
			$needs_update = ! is_null( $db_version ) && version_compare( $db_version, end( $update_versions ), '<' );
			if ( $needs_update ) {
				$this->update();
				/**
				 * Fires after the plugin is updated.
				 *
				 * @since 1.0.0
				 */
				do_action( $this->get_plugin()->get_id() . '_updated' );
			} else {
				$this->get_plugin()->update_db_version();
			}
		}
	}

	/**
	 * Update the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update() {
		$db_version      = $this->get_plugin()->get_db_version();
		$current_version = $this->get_plugin()->get_version();
		foreach ( $this->updates as $version => $callbacks ) {
			$callbacks = (array) $callbacks;
			if ( version_compare( $db_version, $version, '<' ) ) {
				foreach ( $callbacks as $callback ) {
					$this->get_plugin()->log( sprintf( 'Updating to %s from %s', $version, $db_version ) );
					// if the callback return false then we need to update the db version.
					$continue = call_user_func( array( $this, $callback ) );
					if ( ! $continue ) {
						$this->get_plugin()->update_db_version( $version );
						$notice = sprintf(
						/* translators: 1: plugin name 2: version number */
							__( '%1$s updated to version %2$s successfully.', 'wc-min-max-quantities' ),
							'<strong>' . $this->get_plugin()->get_name() . '</strong>',
							'<strong>' . $version . '</strong>'
						);
						$this->add_notice( $notice );
					}
				}
			}
		}
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
	 * @since 1.1.0
	 * @return void
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
			update_option( 'wc_minmax_quantities_' . $key, $value );
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

	/**
	 * Upgrade to 1.1.3
	 *
	 * @since 1.1.3
	 * @return void
	 */
	public static function update_113() {
		$options = [
			'general_min_product_quantity'       => 'wcmmq_min_product_quantity',
			'general_max_product_quantity'       => 'wcmmq_max_product_quantity',
			'general_product_quantity_step'      => 'wcmmq_product_quantity_step',
			'general_min_product_total'          => 'wcmmq_min_product_total',
			'general_max_product_total'          => 'wcmmq_max_product_total',
			'general_min_order_quantity'         => 'wcmmq_min_order_quantity',
			'general_max_order_quantity'         => 'wcmmq_max_order_quantity',
			'general_min_order_amount'           => 'wcmmq_min_order_amount',
			'general_max_order_amount'           => 'wcmmq_max_order_amount',
			'translations_min_product_quantity'  => 'wcmmq_min_product_quantity_text',
			'translations_max_product_quantity'  => 'wcmmq_max_product_quantity_text',
			'translations_product_quantity_step' => 'wcmmq_product_quantity_step_text',
			'translations_min_product_total'     => 'wcmmq_min_product_total_text',
			'translations_max_product_total'     => 'wcmmq_max_product_total_text',
			'translations_min_order_quantity'    => 'wcmmq_min_order_quantity_text',
			'translations_max_order_quantity'    => 'wcmmq_max_order_quantity_text',
			'translations_min_order_amount'      => 'wcmmq_min_order_amount_text',
			'translations_max_order_amount'      => 'wcmmq_max_order_amount_text',
			'translations_min_cat_quantity'      => 'wcmmq_min_cat_quantity_text',
			'translations_max_cat_quantity'      => 'wcmmq_max_cat_quantity_text',
			'general_disable_category_rules'     => 'wcmmq_disable_category_rules',
		];

		$settings = get_option( 'wc_min_max_quantities_settings', array() );
		foreach ( $options as $old_key => $new_key ) {
			if ( isset( $settings[ $old_key ] ) ) {
				$settings[ $new_key ] = $settings[ $old_key ];
				update_option( $new_key, $settings[ $old_key ] );
			}
		}
	}

}

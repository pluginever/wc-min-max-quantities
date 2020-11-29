<?php
defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Serial_Numbers_Settings_General' ) ) :
	/**
	 * WC_Serial_Numbers_Settings_General
	 */
	class WC_Minmax_Quantities_Settings_Translate extends WC_Settings_Page {
		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'translate';
			$this->label = __( 'Translate Settings', 'wc-minmax-quantities' );

			add_filter( 'wc_minmax_quantities_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'wc_minmax_quantities_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'wc_minmax_quantities_settings_save_' . $this->id, array( $this, 'save' ) );
		}

		/**
		 * Get settings array
		 *
		 * @return array
		 */
		public function get_settings() {
			global $woocommerce, $wp_roles;
			$settings = array(
				[
					'title' => __( 'Translate Settings', 'wc-minmax-quantities' ),
					'type'  => 'title',
					'id'    => 'section_translate_settings'
				],
				[
					'title'       => __( 'Minimum Product Quantity Error Message', 'wc-minmax-quantities' ),
					'id'          => 'wc_minmax_quantities_min_product_quantity_error_message',
					'desc'        => __( 'Must use {min_qty} and {product_name} to display minimum order quantity and product name respectively.', 'wc-minmax-quantities' ),
					'type'        => 'text',
					'placeholder' => __( 'You have to buy at least {min_qty} quantities of {product_name}.', 'wc-minmax-quantities' )
				],
				[
					'title'       => __( 'Maximum Product Quantity Error Message', 'wc-minmax-quantities' ),
					'id'          => 'wc_minmax_quantities_max_order_quantity_error_message',
					'desc'        => __( 'Must use {max_qty} and {product_name} to display maximum order quantity and product name respectively.', 'wc-minmax-quantities' ),
					'type'        => 'text',
					'placeholder' => __( 'You can\'t buy more than {max_qty} quantities of {product_name}.', 'wc-minmax-quantities' ),
				],
				[
					'title'       => __( 'Minimum Product Price Error Message', 'wc-minmax-quantities' ),
					'id'          => 'wc_minmax_quantities_min_order_price_error_message',
					'desc'        => __( 'Must use {min_price} and {product_name} to display minimum order price and product name respectively.', 'wc-minmax-quantities' ),
					'type'        => 'text',
					'placeholder' => __( 'Minimum total price should be {min_price} or more for {product_name}.', 'wc-minmax-quantities' ),
				],
				[
					'title'       => __( 'Maximum Product Price Error Message', 'wc-minmax-quantities' ),
					'id'          => 'wc_minmax_quantities_max_order_price_error_message',
					'desc'        => __( 'Must use {max_price} and {product_name} to display maximum order price and product name respectively.', 'wc-minmax-quantities' ),
					'type'        => 'text',
					'placeholder' => __( 'Maximum total price can not be more than {max_price} for {product_name}.', 'wc-minmax-quantities' ),
				],

				[
					'title'       => __( 'Minimum Cart Total Error Message', 'wc-minmax-quantities' ),
					'id'          => 'wc_minmax_quantities_min_cart_total_error_message',
					'desc'        => __( 'Must use {min_cart_total_price} to display minimum cart total price', 'wc-minmax-quantities' ),
					'type'        => 'text',
					'placeholder' => __( 'Minimum cart total price should be {min_cart_total_price} or more', 'wc-minmax-quantities' ),
				],

				[
					'title'       => __( 'Maximum Cart Total Error Message', 'wc-minmax-quantities' ),
					'id'          => 'wc_minmax_quantities_max_cart_total_error_message',
					'desc'        => __( 'Must use {max_cart_total_price} to display maximum cart total price', 'wc-minmax-quantities' ),
					'type'        => 'text',
					'placeholder' => __( 'Maximum cart total price can not be more than {max_cart_total_price}', 'wc-minmax-quantities' ),
				],

				[
					'type' => 'sectionend',
					'id'   => 'section_translate_settings'
				],

			);

			return apply_filters( 'wc_minmax_quantities_translate_settings_fields', $settings );
		}

		/**
		 * Save settings
		 */
		public function save() {
			$settings = $this->get_settings();
			WC_Minmax_Quantites_Admin_Settings::save_fields( $settings );
		}
	}

endif;

return new WC_Minmax_Quantities_Settings_Translate();

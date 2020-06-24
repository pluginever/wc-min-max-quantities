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
					'title'   => __( 'Minimum Product Quantity Error Message', 'wc-minmax-quantities' ),
					'id'      => 'wc_minmax_quantities_min_product_quantity_error_message',
					'desc'    => __( 'Must use %s twice to display minimum order quantity and product name', 'wc-minmax-quantities' ),
					'type'    => 'text',
					'placeholder' 	=> 'You have to buy at least %s quantities of %s'
                ],
                [
					'title'   => __( 'Maximum Product Quantity Error Message', 'wc-minmax-quantities' ),
					'id'      => 'wc_minmax_quantities_max_order_quantity_error_message',
					'desc'    => __( 'Must use %s twice to display maximum order quantity and product name', 'wc-minmax-quantities' ),
					'type'    => 'text',
					'placeholder' 	=> 'You can\'t buy more than %s quantities of %s'
                ],
                [
					'title'   => __( 'Minimum Product Price Error Message', 'wc-minmax-quantities' ),
					'id'      => 'wc_minmax_quantities_min_order_price_error_message',
					'desc'    => __( 'Must use %s twice to display minimum order price and product name', 'wc-minmax-quantities' ),
					'type'    => 'text',
					'placeholder' 	=> 'Minimum total price should be %s or more for %s'
                ],
                [
					'title'   => __( 'Maximum Product Price Error Message', 'wc-minmax-quantities' ),
					'id'      => 'wc_minmax_quantities_max_order_price_error_message',
					'desc'    => __( 'Must use %s twice to display maximum order price and product name.', 'wc-minmax-quantities' ),
					'type'    => 'text',
					'placeholder' 	=> 'Maximum total price can not be more than %s for %s'
                ],
                
                [
					'title'   => __( 'Minimum Cart Total Error Message', 'wc-minmax-quantities' ),
					'id'      => 'wc_minmax_quantities_min_cart_total_error_message',
					'desc'    => __( 'Must use %s to display minimum cart total price', 'wc-minmax-quantities' ),
					'type'    => 'text',
					'placeholder' 	=> 'Minimum cart total price should be %s or more'
                ],
                
                [
					'title'   => __( 'Maximum Cart Total Error Message', 'wc-minmax-quantities' ),
					'id'      => 'wc_minmax_quantities_max_cart_total_error_message',
					'desc'    => __( 'Must use %s to display maximum cart total price', 'wc-minmax-quantities' ),
					'type'    => 'text',
					'placeholder' 	=> 'Maximum cart total price can not be more than %s'
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
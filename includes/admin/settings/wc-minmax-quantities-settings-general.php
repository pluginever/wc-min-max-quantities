<?php
defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Serial_Numbers_Settings_General' ) ) :
	/**
	 * WC_Serial_Numbers_Settings_General
	 */
	class WC_Minmax_Quantities_Settings_General extends WC_Settings_Page {
		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'general';
			$this->label = __( 'General Settings', 'wc-minmax-quantities' );

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
					'title' => __( 'Product Restrictions', 'wc-minmax-quantities' ),
					'type'  => 'title',
					'desc'  => __( 'The following options are for adding minimum maximum rules for products globally', 'wc-minmax-quantities' ),
					'id'    => 'section_product_restrictions'
				],
				[
					'title'   => __( 'Minimum Product Quantity', 'wc-minmax-quantities' ),
					'id'      => 'wc_minmax_quantities_min_product_quantity',
					'desc'    => __( 'Enter a quantity to prevent  user from buying this product if they have fewer than the allowed quantity in their cart.', 'wc-minmax-quantities' ),
					'type'    => 'number',
					'default' => '0',
				],
				[
					'title'   => __( 'Maximum Product Quantity', 'wc-minmax-quantities' ),
					'id'      => 'wc_minmax_quantities_max_product_quantity',
					'desc'    => __( 'Enter a quantity to prevent  user from buying this product if they have more than the allowed quantity in their cart.', 'wc-minmax-quantities' ),
					'type'    => 'number',
					'default' => '0',
				],
				[
					'title'   => __( 'Minimum Product Price', 'wc-minmax-quantities' ),
					'id'      => 'wc_minmax_quantities_min_product_price',
					'desc'    => __( 'Enter an amount of price to prevent  users from buying, if they have lower than the allowed product price in their cart.', 'wc-minmax-quantities' ),
					'type'    => 'number',
					'default' => '0',
				],
				[
					'title'   => __( 'Maximum Product Price', 'wc-minmax-quantities' ),
					'id'      => 'wc_minmax_quantities_max_product_price',
					'desc'    => __( 'Enter an amount of Price to prevent users from buying, if they have more than the allowed product price in their cart.', 'wc-minmax-quantities' ),
					'type'    => 'number',
					'default' => '0',
				],
				[
					'type' => 'sectionend',
					'id'   => 'section_product_restrictions'
				],
				[
					'title' => __( 'Cart Restriction', 'wc-minmax-quantities' ),
					'type'  => 'title',
					'desc'  => __( 'The following options are for cart restrictions', 'wc-minmax-quantities' ),
					'id'    => 'cart_restrictions'
				],
				[
					'title'             => __( 'Minimum Cart Total Price', 'wc-minmax-quantities' ),
					'id'                => 'wc_minmax_quantities_min_cart_total_price',
					'desc'              => __( 'Enter an amount of Price to prevent  users from buying, if they have lower than the allowed price in their cart total.', 'wc-minmax-quantities' ),
					'type'              => 'number',
					'default' 			=> '0',
				],
				[
					'title'             => __( 'Maximum Cart Total Price', 'wc-minmax-quantities' ),
					'id'                => 'wc_minmax_quantities_max_cart_total_price',
					'desc'              => __( 'Enter an amount of Price to prevent users from buying, if they have more than the allowed price in their cart total.', 'wc-minmax-quantities' ),
					'type'              => 'number',
					'default' 			=> '0',
				],
				[
					'type' => 'sectionend',
					'id'   => 'cart_restrictions'
				],
				[
					'title' => __( 'Other Settings', 'wc-minmax-quantities' ),
					'type'  => 'title',
					'id'    => 'other_settings'
				],
				[
					'title'             => __( 'Hide Checkout Button', 'wc-minmax-quantities' ),
					'id'                => 'wc_minmax_quantities_hide_checkout',
					'desc'              => __( 'Hide checkout button if Min/Max condition not passed.', 'wc-minmax-quantities' ),
					'type'              => 'checkbox',
					'default' 			=> 'on',
				],
				[
					'type' => 'sectionend',
					'id'   => 'other_settings'
				],
			);

			return apply_filters( 'wc_minmax_quantities_general_settings_fields', $settings );
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

return new WC_Minmax_Quantities_Settings_General();

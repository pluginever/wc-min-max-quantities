<?php

namespace Pluginever\WCMinMaxQuantities;

class User_Cart {

	/**
	 * User Cart Constructor
	 */
	public function __construct() {
		add_action( 'woocommerce_check_cart_items', array( $this, 'minmax_proceed_to_checkout_conditions' ), 10 );
	}

	public function minmax_proceed_to_checkout_conditions() {

		$checkout_url = wc_get_checkout_url();

		$min_product_quantity = get_option( 'min_product_quantity' );
		$min_product_quantity = isset( $min_product_quantity ) ? $min_product_quantity : '0';
		$min_product_quantity = (int) $min_product_quantity;
		$max_product_quantity = get_option( 'max_product_quantity' );
		$max_product_quantity = isset( $max_product_quantity ) ? $max_product_quantity : '0';
		$max_product_quantity = (int) $max_product_quantity;
		$min_cart_price       = get_option( 'min_cart_price' );
		$min_cart_price       = isset( $min_cart_price ) ? $min_cart_price : '0';
		$min_cart_price       = (int) $min_cart_price;
		$max_cart_price       = get_option( 'max_cart_price' );
		$max_cart_price       = isset( $max_cart_price ) ? $max_cart_price : '0';
		$max_cart_price       = (int) $max_cart_price;

		global $woocommerce;

		$total_cart_quantity   = $woocommerce->cart->cart_contents_count;
		$total_amount_quantity = floatval( WC()->cart->cart_contents_total );


		$items = WC()->cart->get_cart();
		foreach ( $items as $item ) {
			$product_id           = $item['product_id'];
			$qty                  = $item['quantity'];
			$product_name         = $item['data']->get_title();
			$product_min_qunatity = (int) get_post_meta( $product_id, '_minmax_product_min_quantity', true );
			$product_max_quantity = (int) get_post_meta( $product_id, '_minmax_product_max_quantity', true );
			$ignore_global        = get_post_meta( $product_id, '_minmax_ignore_global', true );

			if ( $ignore_global == '' || $ignore_global == 'no' ) {

				if ( ! empty( $product_min_qunatity ) || $product_min_qunatity != '' ) {
					if ( $qty < $product_min_qunatity ) {
						wc_add_notice( sprintf( __( "You have to buy at least %s quantities of %s", 'wc-minmax-quantities' ), $product_min_qunatity, $product_name ), 'error' );

					}
				}

				if ( ! empty( $product_max_quantity ) || $product_max_quantity != '' ) {
					if ( $qty > $product_max_quantity ) {
						wc_add_notice( sprintf( __( "You can't buy more than %s quantities of %s", 'wc-minmax-quantities' ), $product_max_quantity, $product_name ), 'error' );
					}
				}

				if ( empty( $total_cart_quantity ) || empty( $total_amount_quantity ) ) {
					return;
				}

				if ( $total_cart_quantity < $min_product_quantity ) {
					wc_add_notice( sprintf( __( "Quantity of products in cart must be %s or more ", 'wc-minmax-quantities' ), $min_product_quantity ), 'error' );
					$this->remove_checkout_button();

					return;
				}

				if ( ! empty( $max_product_quantity ) && $total_cart_quantity > $max_product_quantity ) {
					wc_add_notice( sprintf( __( "Quantity of products in cart must not be more than %s ", 'wc-minmax-quantities' ), $max_product_quantity ), 'error' );
					$this->remove_checkout_button();

					return;
				}

				if ( ! empty( $min_cart_price ) && $total_amount_quantity < $min_cart_price ) {
					wc_add_notice( sprintf( __( "Minimum cart total should be %s or more", 'wc-minmax-quantities' ), wc_price( $min_cart_price ) ), 'error' );
					$this->remove_checkout_button();

					return;
				}

				if ( ! empty( $max_cart_price ) && $total_amount_quantity > $max_cart_price ) {
					wc_add_notice( sprintf( __( "Maximum cart total can not be more than %s ", 'wc-minmax-quantities' ), wc_price( $min_cart_price ) ), 'error' );
					$this->remove_checkout_button();

					return;
				}

				if ( $min_product_quantity == $total_cart_quantity ) {
					add_action( 'woocommerce_proceed_to_checkout', array( $this, 'woocommerce_button_proceed_to_checkout' ), 10 );
					$this->remove_checkout_button();

					return;
				}

				if ( ( $total_cart_quantity < $min_product_quantity ) || ( $total_cart_quantity > $max_product_quantity ) || ( $total_amount_quantity < $min_cart_price ) || ( $total_amount_quantity > $max_cart_price ) ) {

				}
			} else {
				if ( ! empty( $product_min_qunatity ) || $product_min_qunatity != '' ) {
					if ( $qty < $product_min_qunatity ) {
						wc_add_notice( sprintf( __( "You have to buy at least %s quantities of %s", 'wc-minmax-quantities' ), $product_min_qunatity, $product_name ), 'error' );
						$this->remove_checkout_button();

						return;
					}
				}

				if ( ! empty( $product_max_quantity ) || $product_max_quantity != '' ) {
					if ( $qty > $product_max_quantity ) {
						wc_add_notice( sprintf( __( "You can't buy more than %s quantities of %s", 'wc-minmax-quantities' ), $product_max_quantity, $product_name ), 'error' );
						$this->remove_checkout_button();

						return;
					}
				}
			}
		}
	}

	public function remove_checkout_button() {
		remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
	}

}




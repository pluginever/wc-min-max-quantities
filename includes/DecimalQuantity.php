<?php

namespace WooCommerceMinMaxQuantities;

defined( 'ABSPATH' ) || exit;

/**
 * class DecimalQuantity
 */
class DecimalQuantity {
	private static $decimal_qty_override = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'woocommerce_stock_amount', array( __CLASS__, 'stock_amount' ) );
		add_action( 'woocommerce_update_cart_action_cart_updated', array( __CLASS__, 'fix_cart_quantities' ) );
		add_filter( 'woocommerce_add_to_cart_quantity', array( $this, 'add_to_cart_quantity' ) );
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'fix_order_item_quantity' ), 10, 4 );
	}

	/**
	 * Update the stock quantity to float value.
	 * This is make sure that the stock quantity is in float value, so that the decimal quantity can be added to cart.
	 *
	 * @param mixed $qty The quantity.
	 *
	 * @return float
	 */
	public static function stock_amount( $qty ) {
		if ( null !== self::$decimal_qty_override ) {
			return self::$decimal_qty_override;
		}

		if ( isset( $_POST['quantity'] ) ) {
			return floatval( wp_unslash( $_POST['quantity'] ) );
		}

		return floatval( $qty );
	}

	/**
	 * Update the stock quantity to float value after cart updated.
	 *
	 * @param mixed $cart_updated Mixed values of cart updates.
	 *
	 * @return mixed
	 */
	public static function fix_cart_quantities( $cart_updated ) {
		if ( isset( $_POST['cart'] ) && is_array( $_POST['cart'] ) ) {
			foreach ( $_POST['cart'] as $cart_item_key => $values ) {
				if ( isset( $values['qty'] ) ) {
					WC()->cart->set_quantity( $cart_item_key, floatval( $values['qty'] ), false );
				}
			}
		}

		return $cart_updated;
	}

	/**
	 * Make sure the quantity is in float value.
	 *
	 * @param int $quantity The quantity.
	 *
	 * @return float
	 */
	public function add_to_cart_quantity( $quantity ) {
		if ( isset( $_POST['quantity'] ) ) {
			$quantity = floatval( wp_unslash( $_POST['quantity'] ) );
		}

		return $quantity;
	}

	/**
	 * TO be fixed the item quantity to support the decimal.
	 *
	 * @param $order_item
	 * @param $cart_item_key
	 * @param $values
	 * @param $order
	 *
	 * @return void
	 */
	public static function fix_order_item_quantity( $item, $cart_item_key, $values, $order ) {
		if ( ! isset( $values['quantity'] ) ) {
			return;
		}

		self::$decimal_qty_override = floatval( $values['quantity'] );
		$item->set_quantity( $values['quantity'] );
		self::$decimal_qty_override = null;
	}
}

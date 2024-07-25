<?php

namespace WooCommerceMinMaxQuantities;

defined( 'ABSPATH' ) || exit;

/**
 * Class Cart
 *
 * @since   1.1.4
 * @package WooCommerceMinMaxQuantities
 */
class Cart {
	/**
	 * Restrictions constructor.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'woocommerce_cart_has_errors', array( __CLASS__, 'output_errors' ) );
		add_filter( 'woocommerce_loop_add_to_cart_link', array( __CLASS__, 'add_to_cart_link' ), 10, 2 );
		add_filter( 'woocommerce_quantity_input_args', array( __CLASS__, 'set_quantity_args' ), 10, 2 );
		add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'add_to_cart_validation' ), 20, 4 );
		add_action( 'woocommerce_check_cart_items', array( __CLASS__, 'check_cart_items' ), 20 );
		add_filter( 'woocommerce_add_to_cart_product_id', array( __CLASS__, 'set_cart_quantity' ) );
		add_filter( 'woocommerce_get_availability', array( $this, 'maybe_show_backorder_message' ), 10, 2 );
		add_filter( 'woocommerce_available_variation', array( __CLASS__, 'available_variation' ), 10, 3 );

		// wc-cart Block compatibility.
		add_filter( 'woocommerce_store_api_product_quantity_multiple_of', array( $this, 'filter_cart_item_quantity_multiple_of' ), 10, 2 );
		add_filter( 'woocommerce_store_api_product_quantity_minimum', array( $this, 'filter_cart_item_quantity_minimum' ), 10, 2 );
		add_filter( 'woocommerce_store_api_product_quantity_maximum', array( $this, 'filter_cart_item_quantity_maximum' ), 10, 2 );
	}

	/**
	 * Filter the multiple of value for cart items.
	 *
	 * @param int         $multiple_of The multiple of value.
	 * @param \WC_Product $cart_item The cart item.
	 *
	 * @return int
	 */
	public function filter_cart_item_quantity_multiple_of( $multiple_of, $cart_item ) {
		$product_id = is_callable( array( $cart_item, 'get_id' ) ) ? $cart_item->get_id() : null;
		if ( ! wcmmq_is_product_excluded( $product_id ) && ! empty( $product_id ) ) {
			$limits = wcmmq_get_product_limits( $product_id );
			if ( ! empty( $limits['step'] ) ) {
				$multiple_of = $limits['step'];
			}
		}

		return $multiple_of;
	}

	/**
	 * Filter the minimum value for cart items.
	 *
	 * @param int         $minimum The minimum value.
	 * @param \WC_Product $cart_item The cart item.
	 *
	 * @return int
	 */
	public function filter_cart_item_quantity_minimum( $minimum, $cart_item ) {
		$product_id = is_callable( array( $cart_item, 'get_id' ) ) ? $cart_item->get_id() : null;
		if ( ! wcmmq_is_product_excluded( $product_id ) && ! empty( $product_id ) ) {
			$limits = wcmmq_get_product_limits( $product_id );
			if ( ! empty( $limits['min_qty'] ) ) {
				$minimum = $limits['min_qty'];
			}
		}

		return $minimum;
	}

	/**
	 * Filter the maximum value for cart items.
	 *
	 * @param int         $maximum The maximum value.
	 * @param \WC_Product $cart_item The cart item.
	 *
	 * @return int
	 */
	public function filter_cart_item_quantity_maximum( $maximum, $cart_item ) {
		$product_id = is_callable( array( $cart_item, 'get_id' ) ) ? $cart_item->get_id() : null;
		if ( ! wcmmq_is_product_excluded( $product_id ) && ! empty( $product_id ) ) {
			$limits = wcmmq_get_product_limits( $product_id );
			if ( ! empty( $limits['max_qty'] ) ) {
				$maximum = $limits['max_qty'];
			}
		}

		return $maximum;
	}


	/**
	 * Output any plugin specific error messages
	 *
	 * We use this instead of wc_print_notices so we
	 * can remove any error notices that aren't from us.
	 */
	public static function output_errors() {
		$notices  = wc_get_notices( 'error' );
		$messages = array();

		foreach ( $notices as $i => $notice ) {
			if ( isset( $notice['notice'] ) && isset( $notice['data']['source'] ) && 'wcmmq' === $notice['data']['source'] ) {
				$messages[] = $notice['notice'];
			} else {
				unset( $notice[ $i ] );
			}
		}

		if ( ! empty( $messages ) ) {
			ob_start();

			wc_get_template(
				'notices/error.php',
				array(
					'messages' => array_filter( $messages ),
					'notices'  => array_filter( $notices ),
				)
			);

			echo wc_kses_notice( ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Add quantity property to add to cart button on shop loop for simple products.
	 *
	 * @param string      $html Add to cart link.
	 * @param \WC_Product $product Product object.
	 *
	 * @return string
	 */
	public static function add_to_cart_link( $html, $product ) {

		if ( 'variable' !== $product->get_type() && ! wcmmq_is_product_excluded( $product->get_id() ) ) {
			$limits = wcmmq_get_product_limits( $product->get_id() );
			if ( ! empty( $limits['min_qty'] ) || ! empty( $limits['step'] ) ) {
				$quantity_attribute = $limits['min_qty'];
				if ( $limits['step'] > 0 && $limits['min_qty'] < $limits['step'] ) {
					$quantity_attribute = $limits['step'];
				}

				$html = str_replace( '<a ', '<a data-quantity="' . esc_attr( $quantity_attribute ) . '" ', $html );
			}
		}

		return $html;
	}

	/**
	 * Updates the quantity arguments.
	 *
	 * @param array       $data List of data to update.
	 * @param \WC_Product $product Product object.
	 *
	 * @return array
	 */
	public static function set_quantity_args( $data, $product ) {
		$product_id   = $product->get_id();
		$variation_id = 0;

		if ( $product->is_type( 'variation' ) ) {
			$product_id   = $product->get_parent_id();
			$variation_id = $product->get_id();
		}

		if ( wcmmq_is_product_excluded( $product_id, $variation_id ) || wcmmq_is_allow_combination( $product_id ) ) {
			return $data;
		}

		$limits = wcmmq_get_product_limits( $product_id, $variation_id );
		if ( $limits['min_qty'] > 0 ) {

			if ( $product->managing_stock() && ! $product->backorders_allowed() && absint( $limits['min_qty'] ) > $product->get_stock_quantity() ) {
				$data['min_value'] = $product->get_stock_quantity();

			} else {
				$data['min_value'] = $limits['min_qty'];
			}
		}

		if ( $limits['max_qty'] > 0 ) {

			if ( $product->managing_stock() && $product->backorders_allowed() ) {
				$data['max_value'] = $limits['max_qty'];

			} elseif ( $product->managing_stock() && absint( $limits['max_qty'] ) > $product->get_stock_quantity() ) {
				$data['max_value'] = $product->get_stock_quantity();

			} else {
				$data['max_value'] = $limits['max_qty'];
			}
		}

		if ( $limits['step'] > 0 ) {
			$data['step'] = 1;
			// If both minimum and maximum quantity are set, make sure both are equally divisible by group of quantity.
			if ( ( empty( $limits['max_qty'] ) || absint( $limits['max_qty'] ) % absint( $limits['step'] ) === 0 ) && ( empty( $limits['min_qty'] ) || absint( $limits['min_qty'] ) % absint( $limits['step'] ) === 0 ) ) {
				$data['step'] = $limits['step'];
			}
		}

		if ( empty( $limits['min_qty'] ) && ! $product->is_type( 'group' ) && $limits['step'] > 0 ) {
			$data['min_value'] = $limits['step'];
		}

		return $data;
	}

	/**
	 * Add to cart validation
	 *
	 * @param mixed $pass Filter value.
	 * @param mixed $product_id Product ID.
	 * @param mixed $quantity Quantity.
	 * @param int   $variation_id Variation ID (default none).
	 *
	 * @return mixed
	 */
	public static function add_to_cart_validation( $pass, $product_id, $quantity, $variation_id = 0 ) {
		if ( wcmmq_is_allow_combination( $product_id ) || wcmmq_is_product_excluded( $product_id, $variation_id ) ) {
			return $pass;
		}

		$product_limits = wcmmq_get_product_limits( $product_id, $variation_id );

		// If it's a variation and overridden from variation level we will use conditions
		// from variation otherwise will check parent if that is not overridden fall back to global.
		$minmax_product_id = $product_id;
		if ( ! empty( $variation_id ) && 'yes' === get_post_meta( $variation_id, '_wcmmq_enable', true ) ) {
			$minmax_product_id = $variation_id;
		}

		$product = wc_get_product( $minmax_product_id );

		// Count items.
		$total_quantity = $quantity;
		foreach ( WC()->cart->get_cart() as $item ) {
			if ( $item['product_id'] === $product_id ) {
				$total_quantity += $item['quantity'];
			}
		}

		if ( $product_limits['max_qty'] > 0 && ( $total_quantity > $product_limits['max_qty'] ) ) {
			/* translators: %1$s: Product name, %2$d: Maximum quantity */
			$message = sprintf( __( 'The maximum allowed quantity for %1$s is %2$s.', 'wc-min-max-quantities' ), esc_html( $product->get_formatted_name() ), number_format( $product_limits['max_qty'] ) );
			wcmmq_add_cart_notice( $message );

			return false;
		}

		if ( ! wcmmq_is_allow_combination( $product_id ) && $product_limits['min_qty'] > 0 && $total_quantity < $product_limits['min_qty'] ) {
			/* translators: %1$s: Product name, %2$d: Minimum quantity */
			wcmmq_add_cart_notice( sprintf( __( 'The minimum required quantity for %1$s is %2$s.', 'wc-min-max-quantities' ), $product->get_formatted_name(), number_format( $product_limits['min_qty'] ) ) );

			return false;
		}

		if ( ! wcmmq_is_allow_combination( $product_id ) && $product_limits['step'] > 0 && ( (int) $quantity % (int) $product_limits['step'] > 0 ) ) {
			/* translators: %1$s: Product name, %2$d: Group amount */
			wcmmq_add_cart_notice( sprintf( __( 'The quantity of %1$s must be purchased in groups of %2$s.', 'wc-min-max-quantities' ), $product->get_formatted_name(), $product_limits['step'], $product_limits['step'] - ( $quantity % $product_limits['step'] ) ) );

			return false;
		}

		// For cart level check for maximum only.
		$cart_limits = wcmmq_get_cart_limits();

		if ( $cart_limits['max_qty'] > 1 && WC()->cart->cart_contents_count > $cart_limits['max_qty'] ) {
			/* translators: %d: Maximum quantity */
			wcmmq_add_cart_notice( sprintf( __( 'The maximum allowed order quantity is %s.', 'wc-min-max-quantities' ), number_format( $cart_limits['max_qty'] ) ) );

			return false;
		}

		if ( $cart_limits['max_total'] > 1 && (int) WC()->cart->get_cart_total() > (int) wc_price( $cart_limits['max_total'] ) ) {
			/* translators: %s: Maximum amount */
			wcmmq_add_cart_notice( sprintf( __( 'The maximum allowed order total is %s.', 'wc-min-max-quantities' ), wc_price( $cart_limits['max_total'] ) ) );

			return false;
		}

		return apply_filters( 'wc_min_max_quantities_add_to_cart_validation', $pass, $product_id, $quantity, $variation_id );
	}

	/**
	 * Check cart items.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public static function check_cart_items() {
		$product_ids = array();
		$quantities  = array();
		$line_amount = array();

		// if all the products in the cart is excluded product.
		$cart_excluded = true;

		foreach ( WC()->cart->get_cart() as $item ) {
			$product_id = $item['product_id'];
			if ( ! isset( $quantities[ $product_id ] ) ) {
				$quantities[ $product_id ] = $item['quantity'];
			} else {
				$quantities[ $product_id ] += $item['quantity'];
			}

			if ( ! isset( $line_amount[ $product_id ] ) ) {
				$line_amount[ $product_id ] = $item['data']->get_price() * $item['quantity'];
			} else {
				$line_amount[ $product_id ] += $item['data']->get_price() * $item['quantity'];
			}

			if ( wcmmq_is_product_excluded( $product_id ) ) {
				continue;
			}
			$cart_excluded = false;
			$product_ids[] = $product_id;
		}

		// bail if all the items in the cart is excluded.
		if ( $cart_excluded ) {
			return;
		}

		foreach ( $product_ids as $product_id ) {
			$product        = wc_get_product( $product_id );
			$product_limits = wcmmq_get_product_limits( $product_id );

			$quantity = ! empty( $quantities[ $product_id ] ) ? $quantities[ $product_id ] : 0;

			if ( $product_limits['max_qty'] > 0 && ( $quantity > $product_limits['max_qty'] ) ) {
				/* translators: %1$s: Product name, %2$d: Maximum quantity */
				$message = sprintf( __( 'The maximum allowed quantity for %1$s is %2$s.', 'wc-min-max-quantities' ), esc_html( $product->get_title() ), number_format( $product_limits['max_qty'] ) );
				remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
				wcmmq_add_cart_notice( $message );

				return;
			}

			if ( $product_limits['min_qty'] > 0 && $quantity < $product_limits['min_qty'] ) {
				/* translators: %1$s: Product name, %2$d: Minimum quantity */
				wcmmq_add_cart_notice( sprintf( __( 'The minimum required quantity for %1$s is %2$s.', 'wc-min-max-quantities' ), esc_html( $product->get_formatted_name() ), number_format( $product_limits['min_qty'] ) ) );
				remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

				return;
			}
			if ( $product_limits['step'] > 0 && ( (float) $quantity % (float) $product_limits['step'] > 0 ) ) {
				/* translators: %1$s: Product name, %2$d: quantity amount */
				wcmmq_add_cart_notice( sprintf( __( '%1$s must be bought in groups of %2$s. Please increase or decrease the quantity to continue.', 'wc-min-max-quantities' ), $product->get_formatted_name(), $product_limits['step'], $product_limits['step'] - ( $quantity % $product_limits['step'] ) ) );
				remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

				return;
			}
		}

		$order_quantity = array_sum( array_values( $quantities ) );
		$order_total    = array_sum( array_values( $line_amount ) );
		$cart_limits    = wcmmq_get_cart_limits();

		if ( (int) $cart_limits['min_qty'] > 0 && $order_quantity < (int) $cart_limits['min_qty'] ) {
			/* translators: %d: Minimum amount of items in the cart */
			wcmmq_add_cart_notice( sprintf( __( 'The minimum required quantity in the cart is %s. Please consider increasing the quantity in your cart.', 'wc-min-max-quantities' ), (int) $cart_limits['min_qty'] ) );
			remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

			return;

		}

		if ( (int) $cart_limits['max_qty'] > 0 && $order_quantity > (int) $cart_limits['max_qty'] ) {
			/* translators: %d: Maximum amount of items in the cart */
			wcmmq_add_cart_notice( sprintf( __( 'The maximum allowed order quantity is %s. Please reduce the quantity in your cart.', 'wc-min-max-quantities' ), (int) $cart_limits['max_qty'] ) );
			remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

			return;
		}

		if ( (int) $cart_limits['min_total'] > 0 && $order_total < (int) $cart_limits['min_total'] ) {
			/* translators: %d: Minimum amount of items in the cart */
			wcmmq_add_cart_notice( sprintf( __( 'The minimum allowed order total value is %s. Please consider increasing the quantity in your cart.', 'wc-min-max-quantities' ), wc_price( $cart_limits['min_total'] ) ) );
			remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

			return;

		}

		if ( (int) $cart_limits['max_total'] > 0 && $order_total > (int) $cart_limits['max_total'] ) {
			/* translators: %d: Maximum amount of items in the cart */
			wcmmq_add_cart_notice( sprintf( __( 'The maximum allowed order total value is %s. Please reduce the quantity in your cart.', 'wc-min-max-quantities' ), wc_price( $cart_limits['max_total'] ) ) );
			remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

			return;
		}
	}

	/**
	 * Modify quantity for add to cart action inside loop to respect minimum rules.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return int
	 */
	public static function set_cart_quantity( $product_id ) {
		$add_to_cart = filter_input( INPUT_GET, 'wc-ajax' );
		if ( 'add_to_cart' !== $add_to_cart ) {
			return $product_id;
		}
		$quantity = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;
		if ( empty( $quantity ) ) {
			return $quantity;
		}
		$product = wc_get_product( $product_id );

		if ( ! $product instanceof \WC_Product || 'variable' === $product->get_type() ) {
			return $product_id;
		}

		if ( wcmmq_is_product_excluded( $product_id ) ) {
			return $product_id;
		}

		$product_limits = wcmmq_get_product_limits( $product_id );
		$quantity       = 0;

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( (int) $product_id === (int) $cart_item['product_id'] ) {
				$quantity = $cart_item['quantity'];
				break; // stop the loop if product is found.
			}
		}

		if ( $quantity < $product_limits['min_qty'] ) {
			$_REQUEST['quantity'] = $product_limits['min_qty'] - $quantity;

			return $product_id;
		}

		if ( $product_limits['step'] ) {
			if ( $product_limits['step'] > $quantity ) {
				$_REQUEST['quantity'] = $product_limits['step'] - $quantity;

				return $product_id;
			}

			$remainder = $quantity % $product_limits['step'];

			if ( 0 === $remainder ) {
				$_REQUEST['quantity'] = $product_limits['step'];
			} else {
				$_REQUEST['quantity'] = $product_limits['step'] - $remainder;
			}

			return $product_id;
		}

		return $product_id;
	}

	/**
	 * If the minimum allowed quantity for purchase is lower than the current stock, we need to
	 * let the user know that they are on backorder, or out of stock.
	 *
	 * @param array       $args List of arguments.
	 * @param \WC_Product $product Product object.
	 */
	public function maybe_show_backorder_message( $args, $product ) {
		$product_id   = $product->get_id();
		$variation_id = null;

		if ( $product->is_type( 'variation' ) ) {
			$product_id   = $product->get_parent_id();
			$variation_id = $product->get_id();
		}

		if ( ! $product->managing_stock() || wcmmq_is_product_excluded( $product_id, $variation_id ) ) {
			return $args;
		}

		$product_limits = wcmmq_get_product_limits( $product_id, $variation_id );

		// If the minimum quantity allowed for purchase is smaller than the amount in stock, we need
		// clearer messaging.
		if ( $product_limits['min_qty'] > 0 && $product->get_stock_quantity() < $product_limits['min_qty'] ) {
			if ( $product->backorders_allowed() ) {
				return array(
					'availability' => __( 'Available on backorder', 'wc-min-max-quantities' ),
					'class'        => 'available-on-backorder',
				);
			}

			return array(
				'availability' => __( 'Out of stock', 'wc-min-max-quantities' ),
				'class'        => 'out-of-stock',
			);
		}

		return $args;
	}

	/**
	 * Adds variation min max settings to be used by JS.
	 *
	 * @param array                $data Available variation data.
	 * @param \WC_Product          $product Product object.
	 * @param \WC_Product_Variable $variation Variation object.
	 *
	 * @return array $data
	 */
	public static function available_variation( $data, $product, $variation ) {
		if ( wcmmq_is_product_excluded( $product->get_id(), $variation->get_id() ) || wcmmq_is_allow_combination( $product->get_id() ) ) {
			return $data;
		}

		$product_limits = wcmmq_get_product_limits( $product->get_id(), $variation->get_id() );

		if ( ! empty( $product_limits['min_qty'] ) ) {
			if ( $product->managing_stock() && $product->backorders_allowed() && absint( $product_limits['min_qty'] ) > $product->get_stock_quantity() ) {
				$data['min_qty'] = $product->get_stock_quantity();

			} else {
				$data['min_qty'] = $product_limits['min_qty'];
			}
		}

		if ( ! empty( $product_limits['max_qty'] ) ) {

			if ( $product->managing_stock() && $product->backorders_allowed() ) {
				$data['max_qty'] = $product_limits['max_qty'];

			} elseif ( $product->managing_stock() && absint( $product_limits['max_qty'] ) > $product->get_stock_quantity() ) {
				$data['max_qty'] = $product->get_stock_quantity();

			} else {
				$data['max_qty'] = $product_limits['max_qty'];
			}
		}

		if ( ! empty( $product_limits['step'] ) ) {
			$data['step'] = 1;
			// If both minimum and maximum quantity are set, make sure both are equally divisible by quantity step of quantity.
			if ( $product_limits['max_qty'] && $product_limits['min_qty'] ) {
				if ( absint( $product_limits['max_qty'] ) % absint( $product_limits['min_qty'] ) === 0 && absint( $product_limits['max_qty'] ) % absint( $product_limits['step'] ) === 0 ) {
					$data['step'] = $product_limits['step'];
				}
			} elseif ( ! $product_limits['max_qty'] || absint( $product_limits['max_qty'] ) % absint( $product_limits['step'] ) === 0 ) {
				$data['step'] = $product_limits['step'];
			}

			// Set the minimum only when minimum is not set.
			if ( ! $product_limits['min_qty'] ) {
				$data['min_qty'] = $product_limits['step'];
			}
		}

		if ( ! is_cart() ) {
			if ( ! $product_limits['min_qty'] && $product_limits['step'] ) {
				$data['input_value'] = $product_limits['step'];
			} else {
				$data['input_value'] = ! empty( $product_limits['min_qty'] ) ? $product_limits['min_qty'] : 1;
			}
		}

		return $data;
	}
}

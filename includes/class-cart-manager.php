<?php
/**
 * Handles WooCommerce cart related functionalities.
 *
 * @version  1.1.0
 * @since    1.1.0
 * @package  WC_Min_Max_Quantities\WC
 */

namespace WC_Min_Max_Quantities;

defined( 'ABSPATH' ) || exit();

/**
 * Class Cart_Manager.
 */
class Cart_Manager {

	/**
	 * Cart_Manager constructor.
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
			if ( isset( $notice['notice'] ) && isset( $notice['data']['source'] ) && 'wc-min-max-quantities' === $notice['data']['source'] ) {
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

		if ( 'variable' !== $product->get_type() && ! Helper::is_product_excluded( $product->get_id() ) ) {
			$limits = Helper::get_product_limits( $product->get_id() );
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

		if ( Helper::is_product_excluded( $product_id, $variation_id ) || Helper::is_allow_combination( $product_id ) ) {
			return $data;
		}

		$limits = Helper::get_product_limits( $product_id, $variation_id );
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

		if ( Helper::is_allow_combination( $product_id ) || Helper::is_product_excluded( $product_id, $variation_id ) ) {
			return $pass;
		}

		$limits = Helper::get_product_limits( $product_id, $variation_id );

		// If it's a variation and overridden from variation level we will use conditions
		// from variation otherwise will check parent if that is not overridden fall back to global.
		$minmax_product_id = $product_id;
		if ( ! empty( $variation_id ) && 'yes' === get_post_meta( $variation_id, '_wc_min_max_quantities_override', true ) ) {
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

		if ( $limits['max_qty'] > 0 && ( $total_quantity > $limits['max_qty'] ) ) {
			/* translators: %1$s: Product name, %2$d: Maximum quantity */
			$message = sprintf( __( 'The maximum allowed quantity for %1$s is %2$d.', 'wc-min-max-quantities' ), esc_html( $product->get_formatted_name() ), number_format( $limits['max_qty'] ) );
			Helper::add_error( $message );

			return false;
		}

		if ( ! Helper::is_allow_combination( $product_id ) && $limits['min_qty'] > 0 && $total_quantity < $limits['min_qty'] ) {
			/* translators: %1$s: Product name, %2$d: Minimum quantity */
			Helper::add_error( sprintf( __( 'The minimum required quantity for %1$s is %2$d.', 'wc-min-max-quantities' ), $product->get_formatted_name(), number_format( $limits['min_qty'] ) ) );

			return false;
		}

		if ( ! Helper::is_allow_combination( $product_id ) && $limits['step'] > 0 && ( (int) $quantity % (int) $limits['step'] > 0 ) ) {
			/* translators: %1$s: Product name, %2$d: Group amount */
			Helper::add_error( sprintf( __( '%1$s must be bought in groups of %2$d.', 'wc-min-max-quantities' ), $product->get_formatted_name(), $limits['step'], $limits['step'] - ( $quantity % $limits['step'] ) ) );

			return false;
		}

		// For cart level check for maximum only.
		$maximum_order_quantity = Plugin::get( 'settings' )->get_option( 'general_max_order_quantity' );
		$maximum_order_total    = Plugin::get( 'settings' )->get_option( 'general_max_order_amount' );

		if ( $maximum_order_quantity > 1 && WC()->cart->cart_contents_count > $maximum_order_quantity ) {
			/* translators: %d: Maximum quantity */
			Helper::add_error( sprintf( __( 'The maximum allowed order quantity is %s.', 'wc-min-max-quantities' ), number_format( $maximum_order_quantity ) ) );

			return false;
		}

		if ( $maximum_order_total > 1 && (int) WC()->cart->get_cart_total() > (int) wc_price( $maximum_order_total ) ) {
			/* translators: %s: Maximum amount */
			Helper::add_error( sprintf( __( 'The maximum allowed order total is %s.', 'wc-min-max-quantities' ), wc_price( $maximum_order_total ) ) );

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
		$product_ids        = array();
		$quantities         = array();
		$line_amount        = array();
		$max_order_quantity = Plugin::get( 'settings' )->get_option( 'general_max_order_quantity' );
		$min_order_quantity = Plugin::get( 'settings' )->get_option( 'general_min_order_quantity' );
		$max_order_amount   = Plugin::get( 'settings' )->get_option( 'general_max_order_amount' );
		$min_order_amount   = Plugin::get( 'settings' )->get_option( 'general_min_order_amount' );
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

			if ( Helper::is_product_excluded( $product_id ) ) {
				continue;
			}

			$product_ids[] = $product_id;
		}

		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			$limits  = Helper::get_product_limits( $product_id );

			$quantity = ! empty( $quantities[ $product_id ] ) ? $quantities[ $product_id ] : 0;

			if ( $limits['max_qty'] > 0 && ( $quantity > $limits['max_qty'] ) ) {
				/* translators: %1$s: Product name, %2$d: Maximum quantity */
				$message = sprintf( __( 'The maximum allowed quantity for %1$s is %2$d.', 'wc-min-max-quantities' ), $product->get_title(), $limits['max_qty'] );
				remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
				Helper::add_error( $message );

				return;
			}

			if ( $limits['min_qty'] > 0 && $quantity < $limits['min_qty'] ) {
				/* translators: %1$s: Product name, %2$d: Minimum quantity */
				Helper::add_error( sprintf( __( 'The minimum required quantity for %1$s is %2$d.', 'wc-min-max-quantities' ), $product->get_formatted_name(), $limits['min_qty'] ) );
				remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

				return;
			}
			if ( $limits['step'] > 0 && ( (float) $quantity % (float) $limits['step'] > 0 ) ) {
				/* translators: %1$s: Product name, %2$d: quantity amount */
				Helper::add_error( sprintf( __( '%1$s must be bought in groups of %2$d. Please increase or decrease the quantity to continue.', 'wc-min-max-quantities' ), $product->get_formatted_name(), $limits['step'], $limits['step'] - ( $quantity % $limits['step'] ) ) );
				remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

				return;
			}
		}

		$order_quantity = array_sum( array_values( $quantities ) );
		$order_total    = array_sum( array_values( $line_amount ) );

		if ( (int) $min_order_quantity > 0 && $order_quantity < (int) $min_order_quantity ) {
			/* translators: %d: Minimum amount of items in the cart */
			Helper::add_error( sprintf( __( 'The minimum required items in cart is %d. Please increase the quantity in your cart.', 'wc-min-max-quantities' ), (int) $min_order_quantity ) );
			remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

			return;

		}

		if ( (int) $max_order_quantity > 0 && $order_quantity > (int) $max_order_quantity ) {
			/* translators: %d: Maximum amount of items in the cart */
			Helper::add_error( sprintf( __( 'The maximum allowed order quantity is %d. Please decrease the quantity in your cart.', 'wc-min-max-quantities' ), (int) $max_order_quantity ) );
			remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

			return;
		}

		if ( (int) $min_order_amount > 0 && $order_total < (int) $min_order_amount ) {
			/* translators: %d: Minimum amount of items in the cart */
			Helper::add_error( sprintf( __( 'The minimum allowed order value %s. Please increase the quantity in your cart.', 'wc-min-max-quantities' ), wc_price( $min_order_amount ) ) );
			remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

			return;

		}

		if ( (int) $max_order_amount > 0 && $order_total > (int) $max_order_amount ) {
			/* translators: %d: Maximum amount of items in the cart */
			Helper::add_error( sprintf( __( 'The maximum allowed order value is %s. Please decrease the quantity in your cart.', 'wc-min-max-quantities' ), wc_price( $max_order_amount ) ) );
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
		$add_to_cart = filter_input( INPUT_GET, 'wc-ajax', FILTER_SANITIZE_NUMBER_FLOAT );
		if ( 'add_to_cart' !== $add_to_cart ) {
			return $product_id;
		}
		$quantity = filter_input( INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_FLOAT );
		if ( empty( $quantity ) ) {
			return $quantity;
		}
		$product = wc_get_product( $product_id );

		if ( ! $product instanceof \WC_Product || 'variable' === $product->get_type() ) {
			return $product_id;
		}

		if ( Helper::is_product_excluded( $product_id ) ) {
			return $product_id;
		}

		$limits   = Helper::get_product_limits( $product_id );
		$quantity = 0;

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( (int) $product_id === (int) $cart_item['product_id'] ) {
				$quantity = $cart_item['quantity'];
				break; // stop the loop if product is found.
			}
		}

		if ( $quantity < $limits['min_qty'] ) {
			$_REQUEST['quantity'] = $limits['min_qty'] - $quantity;

			return $product_id;
		}

		if ( $limits['step'] ) {
			if ( $limits['step'] > $quantity ) {
				$_REQUEST['quantity'] = $limits['step'] - $quantity;

				return $product_id;
			}

			$remainder = $quantity % $limits['step'];

			if ( 0 === $remainder ) {
				$_REQUEST['quantity'] = $limits['step'];
			} else {
				$_REQUEST['quantity'] = $limits['step'] - $remainder;
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

		if ( ! $product->managing_stock() || Helper::is_product_excluded( $product_id, $variation_id ) ) {
			return $args;
		}

		$limits = Helper::get_product_limits( $product_id, $variation_id );

		// If the minimum quantity allowed for purchase is smaller than the amount in stock, we need
		// clearer messaging.
		if ( $limits['min_qty'] > 0 && $product->get_stock_quantity() < $limits['min_qty'] ) {
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
		if ( Helper::is_product_excluded( $product->get_id(), $variation->get_id() ) || Helper::is_allow_combination( $product->get_id() ) ) {
			return $data;
		}

		$limits = Helper::get_product_limits( $product->get_id(), $variation->get_id() );

		if ( ! empty( $limits['min_qty'] ) ) {
			if ( $product->managing_stock() && $product->backorders_allowed() && absint( $limits['min_qty'] ) > $product->get_stock_quantity() ) {
				$data['min_qty'] = $product->get_stock_quantity();

			} else {
				$data['min_qty'] = $limits['min_qty'];
			}
		}

		if ( ! empty( $limits['max_qty'] ) ) {

			if ( $product->managing_stock() && $product->backorders_allowed() ) {
				$data['max_qty'] = $limits['max_qty'];

			} elseif ( $product->managing_stock() && absint( $limits['max_qty'] ) > $product->get_stock_quantity() ) {
				$data['max_qty'] = $product->get_stock_quantity();

			} else {
				$data['max_qty'] = $limits['max_qty'];
			}
		}

		if ( ! empty( $limits['step'] ) ) {
			$data['step'] = 1;
			// If both minimum and maximum quantity are set, make sure both are equally divisible by quantity step of quantity.
			if ( $limits['max_qty'] && $limits['min_qty'] ) {
				if ( absint( $limits['max_qty'] ) % absint( $limits['min_qty'] ) === 0 && absint( $limits['max_qty'] ) % absint( $limits['step'] ) === 0 ) {
					$data['step'] = $limits['step'];
				}
			} elseif ( ! $limits['max_qty'] || absint( $limits['max_qty'] ) % absint( $limits['step'] ) === 0 ) {

				$data['step'] = $limits['step'];
			}

			// Set the minimum only when minimum is not set.
			if ( ! $limits['min_qty'] ) {
				$data['min_qty'] = $limits['step'];
			}
		}

		if ( ! is_cart() ) {
			if ( ! $limits['min_qty'] && $limits['step'] ) {
				$data['input_value'] = $limits['step'];
			} else {
				$data['input_value'] = ! empty( $limits['min_qty'] ) ? $limits['min_qty'] : 1;
			}
		}

		return $data;
	}
}

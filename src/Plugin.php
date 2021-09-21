<?php
/**
 * WooCommerce MinMax Quantities: Plugin main class.
 */

namespace PluginEver\WooCommerce\WCMinMaxQuantities;

use \ByteEver\PluginFramework\v1_0_0 as Framework;
use PluginEver\WooCommerce\WCMinMaxQuantities\Admin\Plugin_Settings;

defined( 'ABSPATH' ) || exit();

/**
 * Class Plugin
 * @package PluginEver\WooCommerce\WCMinMaxQuantities
 */
class Plugin extends Framework\Plugin {
	/**
	 * Single instance of plugin.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	protected static $instance;

	/**
	 * Settings options.
	 *
	 * @since 1.0.0
	 * @var Framework\Options
	 */
	public $options;

	/**
	 * Returns the main Plugin instance.
	 *
	 * Ensures only one instance is loaded at one time.
	 *
	 * @return Plugin
	 * @since 1.0.0
	 */
	public static function instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Checks the environment on loading WordPress.
	 *
	 * Check the required environment, dependencies
	 * if not met then add admin error and return false.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function is_environment_compatible() {
		$ret = parent::is_environment_compatible();

		if ( $ret && ! $this->is_plugin_active( 'woocommerce' ) ) {
			$this->add_admin_notice( 'install_woocommerce', 'error', sprintf(
					'%s requires WooCommerce to function. Please %sinstall WooCommerce &raquo;%s',
					'<strong>' . $this->get_plugin_name() . '</strong>',
					'<a href="' . esc_url( admin_url( 'plugin-install.php' ) ) . '">', '</a>'
			) );
			$this->deactivate_plugin();

			return false;
		}

		return $ret;
	}

	/**
	 * Gets the main plugin file.
	 *
	 * return __FILE__;
	 *
	 * @return string the full path and filename of the plugin file
	 * @since 1.0.0
	 */
	protected function get_plugin_file() {
		return PLUGIN_FILE;
	}

	/**
	 * Initialize the plugin.
	 *
	 * The method is automatically called as soon
	 * the class instance is created.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function init() {
		$options       = new Framework\Options( 'wc_min_max_quantities_settings' );
		$this->options = $options;

		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'write_tab_options' ) );
		add_action( 'woocommerce_process_product_meta', [ __CLASS__, 'save_product_meta' ] );

		add_filter( 'woocommerce_loop_add_to_cart_link', array( __CLASS__, 'add_to_cart_link' ), 10, 2 );
		add_action( 'woocommerce_cart_has_errors', array( __CLASS__, 'output_errors' ) );
		add_filter( 'woocommerce_quantity_input_args', array( __CLASS__, 'set_quantity_args' ), 10, 2 );
		add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'add_to_cart' ), 10, 4 );
		add_action( 'woocommerce_check_cart_items', array( __CLASS__, 'check_cart_items' ) );
		add_filter( 'woocommerce_add_to_cart_product_id', array( __CLASS__, 'modify_add_to_cart_quantity' ) );
		add_filter( 'woocommerce_get_availability', array( $this, 'maybe_show_backorder_message' ), 10, 2 );

		if ( is_admin() ) {
			$settings = new Plugin_Settings( $options );
			$settings->register_hooks();
		}
	}


	/**
	 * Add tab content in product edit page
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public static function write_tab_options() {
		global $post;
		?>
		<div class="options_group wc-min-max-quantities-product-settings">
			<?php
			do_action( 'wc_min_max_quantities_data_panel_top' );

			woocommerce_wp_checkbox(
					array(
							'id'          => '_minmax_quantities_exclude',
							'label'       => __( 'Ignore min/max rules', 'wc-min-max-quantities' ),
							'description' => __( 'Do not apply any of the min max restrictions to this product.', 'wc-min-max-quantities' ),
					)
			);

			woocommerce_wp_checkbox(
					array(
							'id'          => '_minmax_quantities_override',
							'label'       => esc_html__( 'Override global', 'wc-min-max-quantities' ),
							'description' => esc_html__( 'Global settings will be overridden by per product settings. Set zero for no restrictions.', 'wc-min-max-quantities' ),
					)
			);

			woocommerce_wp_text_input(
					array(
							'id'                => '_minmax_quantities_min_qty',
							'label'             => __( 'Minimum quantity', 'wc-min-max-quantities' ),
							'description'       => __( 'Enter a quantity to prevent the user buying this product if they have fewer than the allowed quantity in their cart.', 'wc-min-max-quantities' ),
							'desc_tip'          => true,
							'type'              => 'number',
							'custom_attributes' => array(
									'step' => 'any',
									'min'  => '0',
							),
					)
			);
			woocommerce_wp_text_input(
					array(
							'id'                => '_minmax_quantities_max_qty',
							'label'             => __( 'Maximum quantity', 'wc-min-max-quantities' ),
							'description'       => __( 'Enter a quantity to prevent the user buying this product if they have more than the allowed quantity in their cart.', 'wc-min-max-quantities' ),
							'desc_tip'          => true,
							'type'              => 'number',
							'custom_attributes' => array(
									'step' => 'any',
									'min'  => '0',
							),
					)
			);

			woocommerce_wp_text_input(
					array(
							'id'          => '_minmax_quantities_step',
							'label'       => __( 'Quantity groups of', 'wc-min-max-quantities' ),
							'description' => __( 'Enter a quantity to only allow this product to be purchased in groups of X.', 'wc-min-max-quantities' ),
							'desc_tip'    => true,
							'type'        => 'number',
							'min'         => '0',
					)
			);

			do_action( 'wc_min_max_quantities_data_panel_bottom' );
			?>
		</div>
		<?php
	}

	/**
	 * Save meta fields.
	 *
	 * @param int $post_id product ID.
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public static function save_product_meta( $post_id ) {
		$product        = wc_get_product( $post_id );
		$numeric_fields = [
				'_minmax_quantities_min_qty',
				'_minmax_quantities_max_qty',
				'_minmax_quantities_step',
		];
		foreach ( $numeric_fields as $numeric_field ) {
			$value = filter_input( INPUT_POST, $numeric_field, FILTER_SANITIZE_NUMBER_FLOAT );
			$product->update_meta_data( $numeric_field, floatval( $value ) );
		}
		$boolean_fields = [
				'_minmax_quantities_exclude',
				'_minmax_quantities_override',
		];
		foreach ( $boolean_fields as $boolean_field ) {
			$value = filter_input( INPUT_POST, $boolean_field, FILTER_SANITIZE_STRING );
			$product->update_meta_data( $boolean_field, 'yes' === $value ? 'yes' : 'no' );
		}
		$product->save();
	}

	/**
	 * Add quantity property to add to cart button on shop loop for simple products.
	 *
	 * @param string $html Add to cart link.
	 * @param \WC_Product $product Product object.
	 *
	 * @return string
	 */
	public static function add_to_cart_link( $html, $product ) {

		if ( 'variable' !== $product->get_type() && 'yes' !== get_post_meta( $product->get_id(), '_minmax_quantities_exclude', true ) ) {
			$limits = Helper::get_product_limits( $product->get_id() );

			if ( $limits['min_qty'] || $limits['step'] ) {

				$quantity_attribute = $limits['min_qty'];

				if ( $limits['step'] > 0 && $limits['min_qty'] < $limits['step'] ) {
					$quantity_attribute = $limits['step'];
				}

				$html = str_replace( '<a ', '<a data-quantity="' . $quantity_attribute . '" ', $html );
			}
		}

		return $html;
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
			if ( isset( $notice['notice'] ) && isset( $notice['data']['source'] ) && 'wc-minmax-quantities' === $notice['data']['source'] ) {
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
							'messages' => array_filter( $messages ), // @deprecated 3.9.0
							'notices'  => array_filter( $notices ),
					)
			);

			echo wc_kses_notice( ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Updates the quantity arguments.
	 *
	 * @param array $data List of data to update.
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

		if ( 'yes' === get_post_meta( $product_id, '_minmax_quantities_exclude', true )
			 || ( $variation_id && 'yes' === get_post_meta( $variation_id, '_minmax_quantities_exclude', true ) )
			 || 'yes' === get_post_meta( $product_id, '_minmax_quantities_allow_combination', true ) ) {
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
	 * Check cart items.
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public static function check_cart_items() {
		$product_ids = [];
		$quantities  = [];
		$line_amount = [];
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

			if ( 'yes' === get_post_meta( $product_id, '_minmax_quantities_exclude', true ) ) {
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

		if ( (int) self::instance()->options->get( 'min_order_quantity' ) > 0 && $order_quantity < (int) self::instance()->options->get( 'min_order_quantity' ) ) {
			/* translators: %d: Minimum amount of items in the cart */
			Helper::add_error( sprintf( __( 'The minimum required items in cart is %d. Please increase the quantity in your cart.', 'wc-min-max-quantities' ), (int) self::instance()->options->get( 'min_order_quantity' ) ) );
			remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

			return;

		}

		if ( (int) self::instance()->options->get( 'max_order_quantity' ) > 0 && $order_quantity > (int) self::instance()->options->get( 'max_order_quantity' ) ) {
			/* translators: %d: Maximum amount of items in the cart */
			Helper::add_error( sprintf( __( 'The maximum allowed order quantity is %d. Please decrease the quantity in your cart.', 'wc-min-max-quantities' ), (int) self::instance()->options->get( 'max_order_quantity' ) ) );
			remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

			return;
		}

		if ( (int) self::instance()->options->get( 'min_order_amount' ) > 0 && $order_total < (int) self::instance()->options->get( 'min_order_amount' ) ) {
			/* translators: %d: Minimum amount of items in the cart */
			Helper::add_error( sprintf( __( 'The minimum allowed order value %s. Please increase the quantity in your cart.', 'wc-min-max-quantities' ), wc_price( self::instance()->options->get( 'min_order_amount' ) ) ) );
			remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

			return;

		}

		if ( (int) self::instance()->options->get( 'max_order_amount' ) > 0 && $order_total > (int) self::instance()->options->get( 'max_order_amount' ) ) {
			/* translators: %d: Maximum amount of items in the cart */
			Helper::add_error( sprintf( __( 'The maximum allowed order value is %s. Please decrease the quantity in your cart.', 'wc-min-max-quantities' ), wc_price( self::instance()->options->get( 'max_order_amount' ) ) ) );
			remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

			return;
		}
	}

	/**
	 * Add to cart validation
	 *
	 * @param mixed $pass Filter value.
	 * @param mixed $product_id Product ID.
	 * @param mixed $quantity Quantity.
	 * @param int $variation_id Variation ID (default none).
	 *
	 * @return mixed
	 */
	public static function add_to_cart( $pass, $product_id, $quantity, $variation_id = 0 ) {
		if ( 'yes' === get_post_meta( $product_id, '_minmax_quantities_exclude', true )
			 || ( $variation_id && 'yes' === get_post_meta( $variation_id, '_minmax_quantities_exclude', true ) )
			 || 'yes' === get_post_meta( $product_id, '_minmax_quantities_allow_combination', true ) ) {
			return $pass;
		}

		$limits = Helper::get_product_limits( $product_id, $variation_id );

		// If its a variation and overridden from variation level we will use conditions
		// from variation otherwise will check parent if that is not not overridden fall back to global.
		$minmax_product_id = $product_id;
		if ( ! empty( $variation_id ) && 'yes' === get_post_meta( $variation_id, '_minmax_quantities_override', true ) ) {
			$minmax_product_id = $variation_id;
		}

		$product           = wc_get_product( $minmax_product_id );
		$allow_combination = 'yes' === get_post_meta( $product->get_id(), '_minmax_quantities_allow_combination', true );


		// Count items.
		$total_quantity = $quantity;
		foreach ( WC()->cart->get_cart() as $item ) {
			if ( $item['product_id'] === $product_id ) {
				$total_quantity += $item['quantity'];
			}
		}

		if ( $limits['max_qty'] > 0 && ( $total_quantity > $limits['max_qty'] ) ) {
			/* translators: %1$s: Product name, %2$d: Maximum quantity */
			$message = sprintf( __( 'The maximum allowed quantity for %1$s is %2$d.', 'wc-min-max-quantities' ), $product->get_formatted_name(), number_format( $limits['max_qty'] ) );
			Helper::add_error( $message );

			return false;
		}

		if ( ! $allow_combination && $limits['min_qty'] > 0 && $total_quantity < $limits['min_qty'] ) {
			/* translators: %1$s: Product name, %2$d: Minimum quantity */
			Helper::add_error( sprintf( __( 'The minimum required quantity for %1$s is %2$d.', 'wc-min-max-quantities' ), $product->get_formatted_name(), number_format( $limits['min_qty'] ) ) );

			return false;
		}

		if ( ! $allow_combination && $limits['step'] > 0 && ( (int) $quantity % (int) $limits['step'] > 0 ) ) {
			/* translators: %1$s: Product name, %2$d: Group amount */
			Helper::add_error( sprintf( __( '%1$s must be bought in groups of %2$d.', 'wc-minmax-quantities' ), $product->get_formatted_name(), $limits['step'], $limits['step'] - ( $quantity % $limits['step'] ) ) );

			return false;
		}

		//For cart level check for maximum only.
		$maximum_order_quantity = self::instance()->options->get( 'max_order_quantity', 0 );
		$maximum_order_total    = self::instance()->options->get( 'max_order_amount', 0 );

		if ( $maximum_order_quantity > 1 && WC()->cart->cart_contents_count > $maximum_order_quantity ) {
			Helper::add_error( sprintf( __( 'The maximum allowed order quantity is %s.', 'wc-minmax-quantities' ), number_format( $maximum_order_quantity ) ) );

			return false;
		}

		if ( $maximum_order_total > 1 && WC()->cart->get_cart_total() > wc_price( $maximum_order_total ) ) {
			Helper::add_error( sprintf( __( 'The maximum allowed order total is %s.', 'wc-minmax-quantities' ), wc_price( $maximum_order_total ) ) );

			return false;
		}


		return $pass;
	}

	/**
	 * Modify quantity for add to cart action inside loop to respect minimum rules.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return int
	 */
	public static function modify_add_to_cart_quantity( $product_id ) {
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

		$is_excluded = 'yes' === get_post_meta( $product_id, '_minmax_quantities_exclude', true );
		if ( $is_excluded ) {
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
	 * If the minimum allowed quantity for purchase is lower then the current stock, we need to
	 * let the user know that they are on backorder, or out of stock.
	 *
	 * @param array $args List of arguments.
	 * @param \WC_Product $product Product object.
	 */
	public function maybe_show_backorder_message( $args, $product ) {
		if ( ! $product->managing_stock() || 'yes' === get_post_meta( $product->get_id(), '_minmax_quantities_exclude', true )
			 || ( $product->is_type( 'variation' ) && 'yes' === get_post_meta( $product->get_parent_id(), '_minmax_quantities_exclude', true ) ) ) {
			return $args;
		}

		// Figure out what our minimum_quantity is.
		$product_id   = $product->get_id();
		$variation_id = 0;
		if ( $product->is_type( 'variation' ) ) {
			$product_id   = $product->get_parent_id();
			$variation_id = $product->get_id();
		}
		$limits = Helper::get_product_limits( $product_id, $variation_id );

		// If the minimum quantity allowed for purchase is smaller then the amount in stock, we need
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
}

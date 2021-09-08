<?php
/**
 * WooCommerce MinMax Quantities: Plugin main class.
 */

namespace PluginEver\WC_MinMax_Quantities;

use ByteEver\Container\Container;
use ByteEver\Plugin\Plugin_Loader;
use ByteEver\Settings\Options;
use PluginEver\WC_MinMax_Quantities\Admin\Plugin_Settings;

// don't call the file directly
defined( 'ABSPATH' ) || exit();

/**
 * Class Plugin
 *
 * @package PluginEver\WC_MinMax_Quantities
 */
final class Plugin extends Plugin_Loader {
	/**
	 * The single instance of the class.
	 *
	 * @since 1.0.0
	 * @var Plugin
	 */
	protected static $instance = null;

	/**
	 * Service container.
	 *
	 * @since 1.0.0
	 * @var Container
	 */
	public $container;

	/**
	 * Options container.
	 *
	 * @since 1.0.0
	 * @var Options
	 */
	public $options;

	/**
	 * Create plugin instance if not exist.
	 *
	 * @param string $file Plugin file.
	 *
	 * @return Plugin
	 * @since 1.0.0
	 */
	public static function create( $file ) {
		if ( null === static::$instance ) {
			static::$instance = new static( $file );
		}

		return static::$instance;
	}

	/**
	 * Get an array of dependency error messages.
	 *
	 * @return array
	 */
	protected function get_dependency_errors() {
		$errors = array();
		if ( ! self::is_plugin_active( 'woocommerce' ) ) {
			$errors[] = sprintf(
			/* translators: 1: URL of WordPress.org, 2: The minimum WordPress version number */
				__( 'The WooCommerce MinMax Quantities plugin is disabled. In order to work, it requires <a href="%1$s">WooCommerce</a> installed and active.', 'wc-minmax-quantities' ),
				'https://wordpress.org/plugin/woocommerce'
			);
		}

		return $errors;
	}

	/**
	 * Init the plugin.
	 */
	public function register() {
		add_action( 'init', [ $this, 'localization_setup' ] );
		add_action( 'plugins_loaded', [ $this, 'init_services' ] );
		add_action( 'woocommerce_cart_has_errors', array( __CLASS__, 'output_errors' ) );
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'write_tab_options' ) );
		add_action( 'woocommerce_process_product_meta', [ __CLASS__, 'save_product_meta' ] );
		// Quantity
		add_filter( 'woocommerce_quantity_input_args', array( $this, 'set_quantity_args' ), 10, 2 );
		// Prevent add to cart.
		add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'add_to_cart' ), 10, 4 );
		// Check items.
		add_action( 'woocommerce_check_cart_items', array( $this, 'check_cart_items' ) );
	}

	/**
	 * Initialize plugin for localization
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'wc-minmax-quantities', false, plugin_basename( dirname( $this->plugin_file ) ) . '/languages' );
	}

	/**
	 * Init DI Container, set all services as globals
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function init_services() {
		$this->options = new Options( 'wc_minmax_settings' );
		if ( self::is_admin() ) {
			$settings = new Plugin_Settings( $this->options );
			$settings->register();
		}
	}

	/**
	 * Output any plugin specific error messages
	 *
	 * We use this instead of wc_print_notices so we
	 * can remove any error notices that aren't from us.
	 */
	public function output_errors() {
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
	 * Add tab content in product edit page
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public static function write_tab_options() {
		global $post;
		?>
		<div class="options_group">
			<?php
			do_action( 'wc_min_max_quantities_data_panel_top' );

			woocommerce_wp_checkbox(
				array(
					'id'          => '_minmax_quantities_exclude',
					'label'       => __( 'Ignore min/max rules', 'wc-minmax-quantities' ),
					'description' => __( 'Do not apply any of the min max restrictions to this product.', 'wc-minmax-quantities' ),
				)
			);

			woocommerce_wp_checkbox(
				array(
					'id'          => '_minmax_quantities_override',
					'label'       => esc_html__( 'Override global', 'wc-min-max-quantities' ),
					'description' => esc_html__( 'Global settings will be overridden by these ones. Set zero for no restrictions.', 'wc-min-max-quantities' ),
				)
			);

			woocommerce_wp_text_input(
				array(
					'id'                => '_minmax_quantities_min_qty',
					'label'             => __( 'Minimum quantity', 'wc-minmax-quantities' ),
					'description'       => __( 'Enter a quantity to prevent the user buying this product if they have fewer than the allowed quantity in their cart.', 'wc-minmax-quantities' ),
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
					'label'             => __( 'Maximum quantity', 'wc-minmax-quantities' ),
					'description'       => __( 'Enter a quantity to prevent the user buying this product if they have more than the allowed quantity in their cart.', 'wc-minmax-quantities' ),
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
					'label'       => __( 'Quantity groups of', 'wc-minmax-quantities' ),
					'description' => __( 'Enter a quantity to only allow this product to be purchased in groups of X.', 'wc-minmax-quantities' ),
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
	 * Updates the quantity arguments.
	 *
	 * @param array       $data    List of data to update.
	 * @param \WC_Product $product Product object.
	 * @return array
	 */
	public function set_quantity_args( $data, $product ) {
		$is_excluded = 'yes' === get_post_meta( $product->get_id(), '_minmax_quantities_exclude', true );
		if ( $is_excluded || $product->is_type( 'variation' ) ) {
			return $data;
		}

		if ( 'yes' === get_post_meta( $product->get_id(), '_minmax_quantities_override', true ) ) {
			$minimum_quantity = absint( get_post_meta( $product->get_id(), '_minmax_quantities_min_qty', true ) );
			$maximum_quantity = absint( get_post_meta( $product->get_id(), '_minmax_quantities_max_qty', true ) );
			$quantity_step    = absint( get_post_meta( $product->get_id(), '_minmax_quantities_step', true ) );
		} else {
			$minimum_quantity = wc_minmax_quantities()->options->get( 'min_product_quantity', 0 );
			$maximum_quantity = wc_minmax_quantities()->options->get( 'max_product_quantity', 0 );
			$quantity_step    = wc_minmax_quantities()->options->get( 'product_quantity_step', 0 );
		}

		if ( $minimum_quantity > 0 ) {

			if ( $product->managing_stock() && ! $product->backorders_allowed() && absint( $minimum_quantity ) > $product->get_stock_quantity() ) {
				$data['min_value'] = $product->get_stock_quantity();

			} else {
				$data['min_value'] = $minimum_quantity;
			}
		}

		if ( $maximum_quantity > 0 ) {

			if ( $product->managing_stock() && $product->backorders_allowed() ) {
				$data['max_value'] = $maximum_quantity;

			} elseif ( $product->managing_stock() && absint( $maximum_quantity ) > $product->get_stock_quantity() ) {
				$data['max_value'] = $product->get_stock_quantity();

			} else {
				$data['max_value'] = $maximum_quantity;
			}
		}

		if ( $quantity_step > 0 ) {
			$data['step'] = 1;
			// If both minimum and maximum quantity are set, make sure both are equally divisible by group of quantity.
			// if ( ( $maximum_quantity > 0 && $minimum_quantity > 0 && absint( $maximum_quantity ) % absint( $quantity_step ) === 0 && absint( $minimum_quantity ) % absint( $quantity_step ) === 0 ) ) {
			if ( ( empty( $maximum_quantity ) || absint( $maximum_quantity ) % absint( $quantity_step ) === 0 ) && ( empty( $minimum_quantity ) || absint( $minimum_quantity ) % absint( $quantity_step ) === 0 ) ) {
				$data['step'] = $quantity_step;
			}
		}

		if ( empty( $minimum_quantity ) && ! $product->is_type( 'group' ) && $quantity_step > 0 ) {
			$data['min_value'] = $quantity_step;
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
	public static function add_to_cart( $pass, $product_id, $quantity, $variation_id = 0 ) {
		$is_excluded = get_post_meta( $product_id, '_minmax_quantities_exclude', true );
		if ( 'yes' === apply_filters( 'wc_minmax_quantities_is_excluded', $is_excluded, $product_id, $quantity, $variation_id ) ) {
			return $pass;
		}

		if ( 'yes' === get_post_meta( $product_id, '_minmax_quantities_override', true ) ) {
			$minimum_quantity = absint( get_post_meta( $product_id, '_minmax_quantities_min_qty', true ) );
			$maximum_quantity = absint( get_post_meta( $product_id, '_minmax_quantities_max_qty', true ) );
		} else {
			$minimum_quantity = wc_minmax_quantities()->options->get( 'min_product_quantity', 0 );
			$maximum_quantity = wc_minmax_quantities()->options->get( 'max_product_quantity', 0 );
		}

		$total_quantity  = $quantity;
		$total_quantity += Helper::get_cart_item_qty( $product_id, ! empty( $variation_id ) );

		if ( $maximum_quantity > 0 && ( $total_quantity > $maximum_quantity ) ) {
			$_product = wc_get_product( $product_id );

			/* translators: %1$s: Product name, %2$d: Maximum quantity */
			$message = sprintf( __( 'The maximum allowed quantity for %1$s is %2$d.', 'woocommerce-min-max-quantities' ), $_product->get_title(), $maximum_quantity );

			Helper::add_error( $message );

			$pass = false;
		}

		if ( $pass && $minimum_quantity > 0 && $total_quantity < $minimum_quantity ) {
			$_product = wc_get_product( $product_id );

			/* translators: %1$s: Product name, %2$d: Minimum quantity */
			Helper::add_error( sprintf( __( 'The minimum required quantity for %1$s is %2$d.', 'wc-minmax-quantities' ), $_product->get_title(), $minimum_quantity ) );

			$pass = false;
		}

		return apply_filters( 'wc_minmax_quantities_validate_add_to_cart', $pass, $product_id, $quantity, $variation_id );
	}

	/**
	 * Check cart items.
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public function check_cart_items() {
		$quantities  = [];
		$line_amount = [];
		foreach ( WC()->cart->get_cart() as $item ) {
			$product_id  = apply_filters( 'wc_minmax_quantities_checking_id', $item['product_id'], $item );
			$is_excluded = 'yes' === get_post_meta( $product_id, '_minmax_quantities_exclude', true );
			if ( $is_excluded ) {
				continue;
			}
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
		}

		foreach ( $quantities as $product_id => $quantity ) {
			$product = wc_get_product( $product_id );
			if ( 'yes' === get_post_meta( $product_id, '_minmax_quantities_override', true ) ) {
				$minimum_quantity = absint( get_post_meta( $product_id, '_minmax_quantities_min_qty', true ) );
				$maximum_quantity = absint( get_post_meta( $product_id, '_minmax_quantities_max_qty', true ) );
				$quantity_step    = absint( get_post_meta( $product_id, '_minmax_quantities_step', true ) );
			} else {
				$minimum_quantity = wc_minmax_quantities()->options->get( 'min_product_quantity', 0 );
				$maximum_quantity = wc_minmax_quantities()->options->get( 'max_product_quantity', 0 );
				$quantity_step    = wc_minmax_quantities()->options->get( 'product_quantity_step', 0 );
			}

			if ( $maximum_quantity > 0 && ( $quantity > $maximum_quantity ) ) {

				/* translators: %1$s: Product name, %2$d: Maximum quantity */
				$message = sprintf( __( 'The maximum allowed quantity for %1$s is %2$d.', 'wc-minmax-quantities' ), $product->get_title(), $maximum_quantity );
				remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
				Helper::add_error( $message );
				break;
			}

			if ( $minimum_quantity > 0 && $quantity < $minimum_quantity ) {
				/* translators: %1$s: Product name, %2$d: Minimum quantity */
				Helper::add_error( sprintf( __( 'The minimum required quantity for %1$s is %2$d.', 'wc-minmax-quantities' ), $product->get_title(), $minimum_quantity ) );
				remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

				break;
			}

			if ( $quantity_step > 0 && ( intval( $quantity ) % intval( $quantity_step ) > 0 ) ) {
				/* translators: %1$s: Product name, %2$d: Group amount */
				Helper::add_error( sprintf( __( '%1$s must be bought in groups of %2$d. Please increase or decrease the quantity to continue.', 'wc-minmax-quantities' ), $product->get_title(), $quantity_step, $quantity_step - ( $quantity % $quantity_step ) ) );
				remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

				break;
			}
		}

		$total_quantity = array_sum( array_values( $quantities ) );
		$total_amount   = array_sum( array_values( $quantities ) );

		if ( (int) $this->options->get( 'min_order_quantity' ) > 0 && $total_quantity < (int) $this->options->get( 'min_order_quantity' ) ) {
			/* translators: %d: Minimum amount of items in the cart */
			Helper::add_error( sprintf( __( 'The minimum required items in cart is %d. Please increase the quantity in your cart.', 'wc-minmax-quantities' ), (int) $this->options->get( 'min_order_quantity' ) ) );
			remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

			return;

		}

		if ( (int) $this->options->get( 'max_order_quantity' ) > 0 && $total_quantity > (int) $this->options->get( 'max_order_quantity' ) ) {
			/* translators: %d: Maximum amount of items in the cart */
			Helper::add_error( sprintf( __( 'The maximum allowed order quantity is %d. Please decrease the quantity in your cart.', 'wc-minmax-quantities' ), (int) $this->options->get( 'max_order_quantity' ) ) );
			remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

			return;
		}

		if ( (int) $this->options->get( 'min_order_amount' ) > 0 && $total_amount < (int) $this->options->get( 'min_order_amount' ) ) {
			/* translators: %d: Minimum amount of items in the cart */
			Helper::add_error( sprintf( __( 'The minimum required items in cart is %s. Please increase the quantity in your cart.', 'wc-minmax-quantities' ), wc_price( $this->options->get( 'min_order_amount' ) ) ) );
			remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

			return;

		}

		if ( (int) $this->options->get( 'max_order_amount' ) > 0 && $total_amount > (int) $this->options->get( 'max_order_amount' ) ) {
			/* translators: %d: Maximum amount of items in the cart */
			Helper::add_error( sprintf( __( 'The maximum allowed order quantity is %s. Please decrease the quantity in your cart.', 'wc-minmax-quantities' ), wc_price( $this->options->get( 'max_order_amount' ) ) ) );
			remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

			return;
		}
	}
}

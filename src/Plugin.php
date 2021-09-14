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
class Plugin extends Framework\Plugin{
	/**
	 * Single instance of plugin.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	protected static $instance;

	/**
	 *
	 * @since 1.0.0
	 * @var Framework\Options
	 */
	protected $options;

	/**
	 * Returns the main Plugin instance.
	 *
	 * Ensures only one instance is loaded at one time.
	 *
	 * @return Plugin
	 * @since 1.0.0
	 */
	public static function instance(){

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

		if( $ret && !$this->is_plugin_active('woocommerce')){
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
		$options = new Framework\Options('wc_min_max_settings');
		$this->container['options'] = $options;
		if( is_admin() ){
			$settings = new Plugin_Settings($options);
			$settings->register_hooks();
		}
	}

	/**
	 * Hook into actions and filters.
	 *
	 * The method is automatically called when WordPress
	 * triggers `plugins_loaded` hook.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function plugins_loaded() {
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'write_tab_options' ) );
		add_action( 'woocommerce_process_product_meta', [ __CLASS__, 'save_product_meta' ] );
		add_action( 'woocommerce_cart_has_errors', array( __CLASS__, 'output_errors' ) );
		add_filter( 'woocommerce_quantity_input_args', array( __CLASS__, 'set_quantity_args' ), 10, 2 );
		add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'add_to_cart' ), 10, 4 );
		add_action( 'woocommerce_check_cart_items', array( __CLASS__, 'check_cart_items' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_scripts') );
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
					'label'       => __( 'Ignore min/max rules', 'wc-min-max-qunatities' ),
					'description' => __( 'Do not apply any of the min max restrictions to this product.', 'wc-min-max-qunatities' ),
				)
			);

			woocommerce_wp_checkbox(
				array(
					'id'          => '_minmax_quantities_override',
					'label'       => esc_html__( 'Override global', 'wc-min-max-qunatities' ),
					'description' => esc_html__( 'Global settings will be overridden by these ones. Set zero for no restrictions.', 'wc-min-max-qunatities' ),
				)
			);

			woocommerce_wp_text_input(
				array(
					'id'                => '_minmax_quantities_min_qty',
					'label'             => __( 'Minimum quantity', 'wc-min-max-qunatities' ),
					'description'       => __( 'Enter a quantity to prevent the user buying this product if they have fewer than the allowed quantity in their cart.', 'wc-min-max-qunatities' ),
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
					'label'             => __( 'Maximum quantity', 'wc-min-max-qunatities' ),
					'description'       => __( 'Enter a quantity to prevent the user buying this product if they have more than the allowed quantity in their cart.', 'wc-min-max-qunatities' ),
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
					'label'       => __( 'Quantity groups of', 'wc-min-max-qunatities' ),
					'description' => __( 'Enter a quantity to only allow this product to be purchased in groups of X.', 'wc-min-max-qunatities' ),
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
	 * Updates the quantity arguments.
	 *
	 * @param array       $data    List of data to update.
	 * @param \WC_Product $product Product object.
	 * @return array
	 */
	public static function set_quantity_args( $data, $product ) {
		// wp_die(1);
		$is_excluded = 'yes' === get_post_meta( $product->get_id(), '_minmax_quantities_exclude', true );
		if ( $is_excluded || $product->is_type( 'variation' ) ) {
			return $data;
		}

		if ( 'yes' === get_post_meta( $product->get_id(), '_minmax_quantities_override', true ) ) {
			$minimum_quantity = absint( get_post_meta( $product->get_id(), '_minmax_quantities_min_qty', true ) );
			$maximum_quantity = absint( get_post_meta( $product->get_id(), '_minmax_quantities_max_qty', true ) );
			$quantity_step    = absint( get_post_meta( $product->get_id(), '_minmax_quantities_step', true ) );
		} else {
			$minimum_quantity = static::$instance->get( 'min_product_quantity', 0 );
			$maximum_quantity = static::$instance->get( 'max_product_quantity', 0 );
			$quantity_step    = static::$instance->get( 'product_quantity_step', 0 );
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
			$minimum_quantity = static::$instance->get( 'min_product_quantity', 0 );
			$maximum_quantity = static::$instance->get( 'max_product_quantity', 0 );
		}

		$total_quantity  = $quantity;
		$total_quantity += Helper::get_cart_item_qty( $product_id, ! empty( $variation_id ) );

		if ( $maximum_quantity > 0 && ( $total_quantity > $maximum_quantity ) ) {
			$_product = wc_get_product( $product_id );

			/* translators: %1$s: Product name, %2$d: Maximum quantity */
			$message = sprintf( __( 'The maximum allowed quantity for %1$s is %2$d.', 'wc-min-max-qunatities' ), $_product->get_title(), $maximum_quantity );

			Helper::add_error( $message );

			$pass = false;
		}

		if ( $pass && $minimum_quantity > 0 && $total_quantity < $minimum_quantity ) {
			$_product = wc_get_product( $product_id );

			/* translators: %1$s: Product name, %2$d: Minimum quantity */
			Helper::add_error( sprintf( __( 'The minimum required quantity for %1$s is %2$d.', 'wc-min-max-qunatities' ), $_product->get_title(), $minimum_quantity ) );

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
	public static function check_cart_items() {
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
				$minimum_quantity = static::$instance->get( 'min_product_quantity', 0 );
				$maximum_quantity = static::$instance->get( 'max_product_quantity', 0 );
				$quantity_step    = static::$instance->get( 'product_quantity_step', 0 );
			}

			if ( $maximum_quantity > 0 && ( $quantity > $maximum_quantity ) ) {

				/* translators: %1$s: Product name, %2$d: Maximum quantity */
				$message = sprintf( __( 'The maximum allowed quantity for %1$s is %2$d.', 'wc-min-max-qunatities' ), $product->get_title(), $maximum_quantity );
				remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
				Helper::add_error( $message );
				break;
			}

			if ( $minimum_quantity > 0 && $quantity < $minimum_quantity ) {
				/* translators: %1$s: Product name, %2$d: Minimum quantity */
				Helper::add_error( sprintf( __( 'The minimum required quantity for %1$s is %2$d.', 'wc-min-max-qunatities' ), $product->get_title(), $minimum_quantity ) );
				remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

				break;
			}

			if ( $quantity_step > 0 && ( intval( $quantity ) % intval( $quantity_step ) > 0 ) ) {
				/* translators: %1$s: Product name, %2$d: Group amount */
				Helper::add_error( sprintf( __( '%1$s must be bought in groups of %2$d. Please increase or decrease the quantity to continue.', 'wc-min-max-qunatities' ), $product->get_title(), $quantity_step, $quantity_step - ( $quantity % $quantity_step ) ) );
				remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

				break;
			}
		}

		$total_quantity = array_sum( array_values( $quantities ) );
		$total_amount   = array_sum( array_values( $quantities ) );

		if ( (int) static::$instance->options->get( 'min_order_quantity' ) > 0 && $total_quantity < (int) static::$instance->options->get( 'min_order_quantity' ) ) {
			/* translators: %d: Minimum amount of items in the cart */
			Helper::add_error( sprintf( __( 'The minimum required items in cart is %d. Please increase the quantity in your cart.', 'wc-min-max-qunatities' ), (int) static::$instance->options->get( 'min_order_quantity' ) ) );
			remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

			return;

		}

		if ( (int) static::$instance->options->get( 'max_order_quantity' ) > 0 && $total_quantity > (int) static::$instance->options->get( 'max_order_quantity' ) ) {
			/* translators: %d: Maximum amount of items in the cart */
			Helper::add_error( sprintf( __( 'The maximum allowed order quantity is %d. Please decrease the quantity in your cart.', 'wc-min-max-qunatities' ), (int) static::$instance->options->get( 'max_order_quantity' ) ) );
			remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

			return;
		}

		if ( (int) static::$instance->options->get( 'min_order_amount' ) > 0 && $total_amount < (int) static::$instance->options->get( 'min_order_amount' ) ) {
			/* translators: %d: Minimum amount of items in the cart */
			Helper::add_error( sprintf( __( 'The minimum required items in cart is %s. Please increase the quantity in your cart.', 'wc-min-max-qunatities' ), wc_price( static::$instance->options->get( 'min_order_amount' ) ) ) );
			remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

			return;

		}

		if ( (int) static::$instance->options->get( 'max_order_amount' ) > 0 && $total_amount > (int) static::$instance->options->get( 'max_order_amount' ) ) {
			/* translators: %d: Maximum amount of items in the cart */
			Helper::add_error( sprintf( __( 'The maximum allowed order quantity is %s. Please decrease the quantity in your cart.', 'wc-min-max-qunatities' ), wc_price( static::$instance->options->get( 'max_order_amount' ) ) ) );
			remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

			return;
		}
	}

	/**
	 * Enqueue public scripts
	 */
	public function enqueue_public_scripts(){
		$this->register_script('wc-min-max-quantities-script', 'js/frontend.js', ['jquery']);
		wp_enqueue_script('wc-min-max-quantities-script');
		$this->register_style('wc-min-max-quantities-style', 'css/frontend-style.css');
		wp_enqueue_style('wc-min-max-quantities-style');
	}

}

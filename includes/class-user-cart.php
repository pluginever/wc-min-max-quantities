<?php

namespace Pluginever\WCMinMaxQuantities;

class User_Cart {

	/**
	 * User Cart Constructor
	 */
	public function __construct() {
		add_action( 'woocommerce_check_cart_items', array( $this, 'woocommerce_proceed_to_checkout_conditions' ), 1 );
		//add_action( 'woocommerce_proceed_to_checkout', array($this, 'woocommerce_button_proceed_to_checkout'), 10);
	}

	public function woocommerce_proceed_to_checkout_conditions() {
		$checkout_url              = wc_get_checkout_url();
		$simple_settings           = get_option('wc_min_max_quantities_simple');
		if($simple_settings        == ''){
			$simple_settings        = array(
										'min_product_quantity' => '0',
										'max_product_quantity' => '0',
										'min_cart_price'       => '0',
										'max_cart_price'       => '0',
									 );

			$min_product_quantity              = $simple_settings['min_product_quantity'];
			$max_product_quantity              = $simple_settings['max_product_quantity'];
			$min_cart_price                    = $simple_settings['min_cart_price'];
			$max_cart_price                    = $simple_settings['max_cart_price'];
		} else {
			if(array_key_exists( 'min_product_quantity', $simple_settings)){
				$min_product_quantity          = esc_html($simple_settings['min_product_quantity']);
				$min_product_quantity          = (int)$min_product_quantity;
			}

			if(array_key_exists( 'max_product_quantity', $simple_settings)){
				$max_product_quantity          = esc_html($simple_settings['max_product_quantity']);
				$max_product_quantity          = (int)$max_product_quantity;
			}

			if(array_key_exists( 'min_cart_price', $simple_settings)){
				$min_cart_price                = esc_html($simple_settings['min_cart_price']);
				$min_cart_price                = (int)$min_cart_price;
			}

			if(array_key_exists( 'max_cart_price', $simple_settings)){
				$max_cart_price                = esc_html($simple_settings['max_cart_price']);
				$max_cart_price                = (int)$max_cart_price;
			}
		}

		if(empty($min_product_quantity) || empty($max_product_quantity) || empty($min_cart_price) || empty($max_cart_price)){
			return;
		}

		global $woocommerce; 

    	$total_cart_quantity                   = $woocommerce->cart->cart_contents_count;
    	$total_amount_quantity                 = floatval( WC()->cart->cart_contents_total );

    	if(empty($total_cart_quantity) || empty($total_amount_quantity)){
    		return;
    	}

    	if( $min_product_quantity == $total_cart_quantity ){
    		add_action( 'woocommerce_proceed_to_checkout', array($this, 'woocommerce_button_proceed_to_checkout'), 10);
    		return;
    	}

    	if( $total_cart_quantity   < $min_product_quantity ){
    		wc_add_notice( sprintf( __( "Minimum amount is %s ", 'woocommerce' ), $min_product_quantity ), 'error' );
    	}

    	if( $total_cart_quantity   > $max_product_quantity ){
    		wc_add_notice( sprintf( __( "Maximum amount is %s ", 'woocommerce' ), $max_product_quantity ), 'error' );
    	}

    	if( $total_amount_quantity < $min_cart_price ){
    		wc_add_notice( sprintf( __( "Minimum cart total is %s ", 'woocommerce' ), $min_cart_price ), 'error' );
    	}

    	if( $total_amount_quantity > $max_cart_price ){
    		wc_add_notice( sprintf( __( "Maximum cart total is %s ", 'woocommerce' ), $max_cart_price ), 'error' );
    	}

    	if(($total_cart_quantity < $min_product_quantity) || ($total_cart_quantity > $max_product_quantity) || ($total_amount_quantity < $min_cart_price) || ($total_amount_quantity > $max_cart_price)){
    		
    		
    	} else {
    		add_action( 'woocommerce_proceed_to_checkout', array($this, 'woocommerce_button_proceed_to_checkout'), 10);
    	}
	}
	public function woocommerce_button_proceed_to_checkout(){
		?>
			<a class="checkout-button button alt">
	            <?php esc_html_e( 'Secure Account', 'woocommerce' ); ?>
	        </a>
       <?php	
	}
}




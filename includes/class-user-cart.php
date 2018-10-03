<?php

namespace Pluginever\WCMinMaxQuantities;

class User_Cart {

	/**
	 * User Cart Constructor
	 */
	public function __construct() {
	    add_action( 'woocommerce_check_cart_items', array( $this, 'minmax_proceed_to_checkout_conditions' ), 1 );	
	}

	public function minmax_proceed_to_checkout_conditions() {		

		$checkout_url                         = wc_get_checkout_url();

		$min_product_quantity                 = get_option('min_product_quantity');
		$min_product_quantity                 = isset($min_product_quantity) ? $min_product_quantity :'0';
		$min_product_quantity  				  = (int) $min_product_quantity;
		$max_product_quantity                 = get_option('max_product_quantity');
		$max_product_quantity                 = isset($max_product_quantity) ? $max_product_quantity :'0';
		$max_product_quantity  				  = (int) $max_product_quantity;
		$min_cart_price 					  = get_option('min_cart_price');
		$min_cart_price 					  = isset($min_cart_price) ? $min_cart_price :'0';
		$min_cart_price 					  = (int) $min_cart_price;
		$max_cart_price						  = get_option('max_cart_price');
		$max_cart_price						  = isset($max_cart_price) ? $max_cart_price : '0';
		$max_cart_price						  = (int) $max_cart_price;

		global $woocommerce; 

    	$total_cart_quantity                  = $woocommerce->cart->cart_contents_count;
    	$total_amount_quantity                = floatval( WC()->cart->cart_contents_total );


		$items                                = WC()->cart->get_cart(); 
		foreach( $items as $item ){
		    $product_id                       = $item['product_id'];
		    $qty 							  = $item['quantity']; 
		    $product_name 					  = $item['data']->get_title(); 
		    $single_product_min_quantity      = (int) get_post_meta( $product_id, 'simple_product_min_quantity', true );
		    $single_product_max_quantity      = (int) get_post_meta( $product_id, 'simple_product_max_quantity', true );
		    $single_product_check             = get_post_meta( $product_id, 'check_status', true );
		   
		    if($single_product_check == '' || $single_product_check == 'no' ){

		   		if(!empty($single_product_min_quantity) || $single_product_min_quantity != ''){
			    	if( $qty   < $single_product_min_quantity ){
			    		wc_add_notice( sprintf( __( "You have to buy at least %s quantities of %s", 'wc-min-max-quantities' ), $single_product_min_quantity, $product_name ), 'error' );
			    	}
	    		}

	    		if(!empty($single_product_max_quantity) || $single_product_max_quantity != ''){
			    	if( $qty   > $single_product_max_quantity ){
			    		wc_add_notice( sprintf( __( "You can't buy more than %s quantities of %s", 'wc-min-max-quantities' ), $single_product_max_quantity, $product_name ), 'error' );
			    	}
	    		}

	    		if(empty($total_cart_quantity) || empty($total_amount_quantity)){
		    		return;
		    	}

		    	if( $total_cart_quantity < $min_product_quantity ){
		    		wc_add_notice( sprintf( __( "Quantity of products in cart must be %s or more ", 'wc-min-max-quantities' ), $min_product_quantity ), 'error' );
		    		return;
		    	}

		    	if( $total_cart_quantity > $max_product_quantity ){
		    		wc_add_notice( sprintf( __( "Quantity of products in cart must be not more than %s ", 'wc-min-max-quantities' ), $max_product_quantity ), 'error' );
		    		return;
		    	}

		    	if( $total_amount_quantity < $min_cart_price ){
		    		wc_add_notice( sprintf( __( "Minimum cart total should be %s or more", 'wc-min-max-quantities' ), $min_cart_price ), 'error' );
		    		return;
		    	}

		    	if( $total_amount_quantity > $max_cart_price ){
		    		wc_add_notice( sprintf( __( "Maximum cart total is %s ", 'wc-min-max-quantities' ), $max_cart_price ), 'error' );
		    		return;
		    	}

		    	if( $min_product_quantity == $total_cart_quantity ){
		    		add_action( 'woocommerce_proceed_to_checkout', array($this, 'woocommerce_button_proceed_to_checkout'), 10);
		    		return;
		    	}

		    	if(($total_cart_quantity < $min_product_quantity) || ($total_cart_quantity > $max_product_quantity) || ($total_amount_quantity < $min_cart_price) || ($total_amount_quantity > $max_cart_price)){
	    				
			    } else { 
			    	
			    	add_action( 'woocommerce_proceed_to_checkout', array($this, 'woocommerce_button_proceed_to_checkout'), 10);
			    }
		    } else {
		   		if(!empty($single_product_min_quantity) || $single_product_min_quantity != ''){
			    	if( $qty   < $single_product_min_quantity ){
			    		wc_add_notice( sprintf( __( "You have to buy at least %s quantities of %s", 'wc-min-max-quantities' ), $single_product_min_quantity, $product_name ), 'error' );
			    	}
	    		}

	    		if(!empty($single_product_max_quantity) || $single_product_max_quantity != ''){
			    	if( $qty   > $single_product_max_quantity ){
			    		wc_add_notice( sprintf( __( "You can't buy more than %s quantities of %s", 'wc-min-max-quantities' ), $single_product_max_quantity, $product_name ), 'error' );
			    	}
	    		}

	    		if(($qty > $single_product_max_quantity) || ($qty < $single_product_min_quantity)){
	    					
			    } else { 
			    	add_action( 'woocommerce_proceed_to_checkout', array($this, 'woocommerce_button_proceed_to_checkout'), 10);
			    }
		    }
		}    	    	
	}
	public function woocommerce_button_proceed_to_checkout(){
		?>
			<a class="checkout-button button alt">
	            <?php esc_html_e( 'Proceed To Checkout', 'wc-min-max-quantities' ); ?>
	        </a>
       <?php	
	}
}




<?php

namespace Pluginever\WCMinMaxQuantities;

class User_Cart {

	/**
	 * User Cart Constructor
	 */
	public function __construct() {
		add_action('plugins_loaded',array($this, 'disable_checkout_button') );
	}

	public function disable_checkout_button() {
	   remove_action( 'woocommerce_proceed_to_checkout', array($this, 'woocommerce_button_proceed_to_checkout'), 10);  
	}
}




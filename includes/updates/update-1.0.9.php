<?php

function wcmm_update_1_0_9() {

    $general_settings = get_option('wc_minmax_quantity_general_settings');
    $advnace_settings = get_option('wc_minmax_quantity_advanced_settings');

    $min_product_quantity = isset( $general_settings['min_product_quantity'] ) ? $general_settings['min_product_quantity'] : false;
    update_option('wc_minmax_quantities_min_product_quantity', $min_product_quantity);

    $max_product_quantity = isset( $general_settings['max_product_quantity'] ) ? $general_settings['max_product_quantity'] : false;
    update_option('wc_minmax_quantities_max_product_quantity', $max_product_quantity);

    $min_cart_price = isset( $general_settings['min_cart_price'] ) ? $general_settings['min_cart_price'] : false;
    update_option('wc_minmax_quantities_min_product_price', $min_cart_price);

    $max_cart_price = isset( $general_settings['max_cart_price'] ) ? $general_settings['max_cart_price'] : false;
    update_option('wc_minmax_quantities_max_product_price', $max_cart_price);

    $hide_checkout = isset( $general_settings['hide_checkout'] ) ? $general_settings['hide_checkout'] : false;
    if($hide_checkout=='on'){
        $hide_checkout = 'yes';
    }
    update_option('wc_minmax_quantities_hide_checkout', $hide_checkout);

    $min_cart_total_price = isset( $advnace_settings['min_cart_total_price'] ) ? $advnace_settings['min_cart_total_price'] : false;
    update_option('wc_minmax_quantities_min_cart_total_price', $min_cart_total_price);

    $max_cart_total_price = isset( $advnace_settings['max_cart_total_price'] ) ? $advnace_settings['max_cart_total_price'] : false;
    update_option('wc_minmax_quantities_max_cart_total_price', $max_cart_total_price);

}
wcmm_update_1_0_9();

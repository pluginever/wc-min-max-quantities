<?php

namespace Pluginever\WCMinMaxQuantities\Admin;

use Pluginever\WCMinMaxQuantities\Admin\WPWMMQ_Settings_API;

class Settings {
	private $settings_api;

    function __construct() {
        $this->settings_api = new WPWMMQ_Settings_API();
        add_action( 'admin_init', array( $this, 'admin_init' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    }

    function admin_init() {
        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );
        //initialize settings
        $this->settings_api->admin_init();
    }

    function admin_menu() {
        add_menu_page(
	        __( 'Min/Max Quantities', 'wc-min-max-quantities' ),
	        'Min/Max Quantities',
	        'manage_options',
	        'myplugin/myplugin-admin.php',
	        array($this,'settings_page'),
	        'dashicons-sos',
	        59
	    );
    }

    function get_settings_sections() {
        $sections = array(
            array(
                'id'    => 'wc_min_max_quantities_simple',
                'title' => __( 'Simple Settings', 'wc-min-max-quantities' )
            ),
            array(
                'id'    => 'wc_min_max_quantities_advance',
                'title' => __( 'Advance Settings', 'wc-min-max-quantities' )
            ),
        );

        return apply_filters( 'wc-min-max-quantities_settings_sections', $sections );
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields() {
        $settings_fields = array(
            'wc_min_max_quantities_simple' => array(
                array(
                    'name'        => 'min_product_quantity',
                    'label'       => __( 'Minimum Product Quantity', 'wc-min-max-quantities' ),
                    'type'        => 'number',
                    'min'         => '0'
                ),
                array(
                    'name'        => 'max_product_quantity',
                    'label'       => __( 'Maximum Product Quantity', 'wc-min-max-quantities' ),
                    'type'        => 'number',
                    'min'         => '0'
                ),
                array(
                    'name'        => 'min_cart_price',
                    'label'       => __( 'Minimum Cart Price', 'wc-min-max-quantities' ),
                    'type'        => 'number',
                    'min'         => '0'
                ),
                array(
                    'name'        => 'max_cart_price',
                    'label'       => __( 'Maximum Cart Price', 'wc-min-max-quantities' ),
                    'type'        => 'number',
                    'min'         => '0'
                ),
            ),
            'wc_min_max_quantities_advance' => array(
                array(
                    'name'        => 'width',
                    'label'       => __( 'Width', 'wc-min-max-quantities' ),
                    'desc'        => __( 'Variation Item Width.', 'wc-min-max-quantities' ),
                    'type'        => 'text',
                    'default'     => '30px',
                ),
                array(
                    'name'        => 'height',
                    'label'       => __( 'Height', 'wc-min-max-quantities' ),
                    'desc'        => __( 'Variation Item Height.', 'wc-min-max-quantities' ),
                    'type'        => 'text',
                    'default'     => '30px',
                ),
                array(
                    'name'        => 'font_size',
                    'label'       => __( 'Tooltip Font Size', 'wc-min-max-quantities' ),
                    'desc'        => __( 'Tooltip Font Size.', 'wc-min-max-quantities' ),
                    'type'        => 'text',
                    'default'     => '15px',
                ),
                array(
                    'name'        => 'tooltip_bg_color',
                    'label'       => __( 'Tooltip Background Color', 'wc-min-max-quantities' ),
                    'type'        => 'color',
                    'default'     => '#555',
                ),
                array(
                    'name'        => 'tooltip_text_color',
                    'label'       => __( 'Tooltip Text Color', 'wc-min-max-quantities' ),
                    'type'        => 'color',
                    'default'     => '#fff',
                ),
                array(
                    'name'        => 'border_style',
                    'label'       => __( 'Border Style', 'wc-min-max-quantities' ),
                    'desc'        => __( 'Enable/Disable Border.', 'wc-min-max-quantities' ),
                    'type'        => 'radio',
                    'options'     => array(
                                        'enable'         => 'Enable Border',
                                        'disable'        => 'Disable Border'
                                     ),
                    'default'     => 'enable',

                ),
            ),
        );

        return apply_filters( 'wc-min-max-quantities_settings_fields', $settings_fields );
    }

    function settings_page() {
        ?>
        <?php
        echo '<div class="wrap">';
        echo sprintf( "<h2>%s</h2>", __( 'WC Min Max Quantities', 'wc-min-max-quantities' ) );
        $this->settings_api->show_settings();
        echo '</div>';
    }

    /**
     * Get all the pages
     *
     * @return array page names with key value pairs
     */
    function get_pages() {
        $pages         = get_pages();
        $pages_options = array();
        if ( $pages ) {
            foreach ( $pages as $page ) {
                $pages_options[ $page->ID ] = $page->post_title;
            }
        }

        return $pages_options;
    }
}

<?php
/*
Plugin Name: Add to Cart Button Rules for WooCommerce
Description: Adds a custom tab in the WooCommerce product edit screen to control the visibility of the Add to Cart button.
Version: 1.0
Author: Your Name
Text Domain: add-to-cart-button-rules
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Add_To_Cart_Button_Rules {

    public function __construct() {
        add_action('woocommerce_product_data_tabs', [$this, 'add_custom_product_tab']);
        add_action('woocommerce_product_data_panels', [$this, 'add_custom_product_tab_content']);
        add_action('woocommerce_process_product_meta', [$this, 'save_custom_product_tab_content']);
        add_action('woocommerce_after_shop_loop_item', [$this, 'conditionally_hide_add_to_cart_button'], 10);
        add_action('woocommerce_single_product_summary', [$this, 'conditionally_hide_add_to_cart_button'], 10);
    }
    
    public function add_custom_product_tab($tabs) {
        $tabs['add_to_cart_rules'] = [
            'label' => __('Add to Cart Rules', 'add-to-cart-button-rules'),
            'target' => 'add_to_cart_rules_data',
            'class' => ['show_if_simple', 'show_if_variable'],
        ];
        return $tabs;
    }

    public function add_custom_product_tab_content() {
        global $post;
        $product = wc_get_product($post->ID);
        $disable_button = $product->get_meta('_disable_add_to_cart_button', true);

        echo '<div id="add_to_cart_rules_data" class="panel woocommerce_options_panel">';
        echo '<div class="options_group">';
        
        woocommerce_wp_checkbox([
            'id' => '_disable_add_to_cart_button',
            'label' => __('Disable Add to Cart Button', 'add-to-cart-button-rules'),
            'description' => __('Check this box to disable the Add to Cart button on this product.', 'add-to-cart-button-rules'),
            'value' => $disable_button === 'yes' ? 'yes' : 'no',
        ]);

        echo '</div></div>';
    }

    public function save_custom_product_tab_content($post_id) {
        $product = wc_get_product($post_id);
        $disable_button = isset($_POST['_disable_add_to_cart_button']) ? 'yes' : 'no';
        $product->update_meta_data('_disable_add_to_cart_button', $disable_button);
        $product->save(); // Save changes to the product
    }

    public function conditionally_hide_add_to_cart_button() {
        global $product;

        if (is_product() || is_shop()) {
            $disable_button = $product->get_meta('_disable_add_to_cart_button', true);

            if ($disable_button === 'yes') {
                remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
                remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
            }
        }
    }
}

new Add_To_Cart_Button_Rules();

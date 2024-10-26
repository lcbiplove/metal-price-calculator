<?php
/*
Plugin Name: Metal Price Calculator
Description: WooCommerce plugin to adjust product prices based on metal rates.
Version: 1.0
Author: Biplove Lamichhane
*/

if (!defined('ABSPATH')) exit; // Prevent direct access

// Include admin settings and price calculation functions
include_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';
include_once plugin_dir_path(__FILE__) . 'includes/price-calculations.php';

// Modify product price display based on metal attributes
function metal_price_calculator_modify_price_display($price, $product) {
    if (!has_term(['Gold', 'Silver', 'Platinum', 'Palladium'], 'product_cat', $product->get_id())) {
        return $price;
    }

    $metal = strtolower($product->get_attribute('metal'));
    $weight = floatval($product->get_attribute('weight'));
    $purity = floatval($product->get_attribute('purity'));

    $adjusted_price = metal_price_calculator_adjust_price($product->get_price(), $metal, $weight, $purity);
    return wc_price($adjusted_price);
}
add_filter('woocommerce_get_price_html', 'metal_price_calculator_modify_price_display', 10, 2);

// Update cart item prices based on metal attributes
function metal_price_calculator_update_cart_prices($cart) {
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $product_id = $cart_item['product_id'];

        if (!has_term(['Gold', 'Silver', 'Platinum', 'Palladium'], 'product_cat', $product_id)) {
            continue;
        }

        $metal = strtolower($cart_item['data']->get_attribute('metal'));
        $weight = floatval($cart_item['data']->get_attribute('weight'));
        $purity = floatval($cart_item['data']->get_attribute('purity'));

        $adjusted_price = metal_price_calculator_adjust_price($cart_item['data']->get_price(), $metal, $weight, $purity);
        $cart_item['data']->set_price($adjusted_price);
    }
}
add_action('woocommerce_before_calculate_totals', 'metal_price_calculator_update_cart_prices');

// Add settings link to the plugin page
function metal_price_calculator_action_links($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=metal-price-calculator') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'metal_price_calculator_action_links');

// Create product attributes and categories on plugin activation
function metal_price_calculator_create_attributes_and_categories() {
    wc_create_attribute([
        'name' => 'Metal',
        'slug' => 'metal',
        'type' => 'select',
    ]);
    wc_create_attribute([
        'name' => 'Weight',
        'slug' => 'weight',
        'type' => 'number',
    ]);
    wc_create_attribute([
        'name' => 'Purity',
        'slug' => 'purity',
        'type' => 'number',
    ]);

    // Create metal categories if they don't exist
    wp_insert_term('Gold', 'product_cat');
    wp_insert_term('Silver', 'product_cat');
    wp_insert_term('Platinum', 'product_cat');
    wp_insert_term('Palladium', 'product_cat');
}
register_activation_hook(__FILE__, 'metal_price_calculator_create_attributes_and_categories');

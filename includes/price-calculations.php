<?php
// Get current metal prices from external API
function metal_price_calculator_get_current_price($metal, $unit = 'oz') {
    $response = wp_remote_get("https://api.nfusionsolutions.biz/api/v1/Metals/spot/summary?metals=$metal&unitofmeasure=$unit&currency=aud&format=json&token=a1f2ffe5-6b4f-4cad-9947-0bdba9ea8af0");
    if (is_wp_error($response)) return false;
    $data = json_decode(wp_remote_retrieve_body($response), true);
    return $data['price'] ?? false;
}

// Calculate adjusted product price for display
function metal_price_calculator_adjust_price($product_price, $metal, $weight, $purity) {
    $settings = get_option('metal_price_calculator_data');
    if (!isset($settings[$metal])) return $product_price;

    $adjustment_price = $settings[$metal]['adjustment_price'] ?? 0;
    $adjusted_price = ($adjustment_price / 31.10) * $weight * $purity - $product_price;
    return $adjusted_price;
}

// Calculate buy back price
function metal_price_calculator_buy_back_price($metal, $weight, $purity) {
    $settings = get_option('metal_price_calculator_data');
    $current_price = metal_price_calculator_get_current_price($metal, 'g');
    $buy_back_price = $settings[$metal]['buy_back_price'] ?? 0;

    return ($current_price - $buy_back_price) * $weight * $purity;
}
<?php
// Register the settings page
function metal_price_calculator_register_settings() {
    register_setting('metal_price_calculator_settings', 'metal_price_calculator_data');
}
add_action('admin_init', 'metal_price_calculator_register_settings');

function metal_price_calculator_add_settings_page() {
    add_menu_page(
        'Metal Price Calculator',
        'Metal Price Calc',
        'manage_options',
        'metal-price-calculator',
        'metal_price_calculator_render_settings_page',
        'dashicons-money-alt',
        100
    );
}
add_action('admin_menu', 'metal_price_calculator_add_settings_page');

// Enqueue styles and scripts
function metal_price_calculator_enqueue_assets() {
    wp_enqueue_style('metal-price-calculator-style', plugin_dir_url(__FILE__) . '../assets/css/style.css');
    wp_enqueue_script('metal-price-calculator-script', plugin_dir_url(__FILE__) . '../assets/js/script.js', [], null, true);
}
add_action('admin_enqueue_scripts', 'metal_price_calculator_enqueue_assets');

// Render settings page with tabs
function metal_price_calculator_render_settings_page() {
    // Retrieve existing option data
    $data = get_option('metal_price_calculator_data', [
        'gold' => ['adjustment_price' => 0.00, 'buy_back_price' => 0.00],
        'silver' => ['adjustment_price' => 0.00, 'buy_back_price' => 0.00],
        'platinum' => ['adjustment_price' => 0.00, 'buy_back_price' => 0.00],
        'palladium' => ['adjustment_price' => 0.00, 'buy_back_price' => 0.00],
    ]);

    // Initialize active tab
    $active_tab = 'adjustment-prices'; // Default active tab

    // Form submission handling
    if (isset($_POST['adjustment_prices_submit']) || isset($_POST['buy_back_prices_submit'])) {
        $submitted_data = $_POST['metal_price_calculator_data'];
        
        foreach ($submitted_data as $metal => $prices) {
            if (isset($prices['adjustment_price'])) {
                $data[$metal]['adjustment_price'] = floatval($prices['adjustment_price']);
            }
            if (isset($prices['buy_back_price'])) {
                $data[$metal]['buy_back_price'] = floatval($prices['buy_back_price']);
            }
        }
        update_option('metal_price_calculator_data', $data);
        
        // Determine which tab to keep active after submission
        $active_tab = isset($_POST['adjustment_prices_submit']) ? 'adjustment-prices' : 'buy-back-prices';
    }

    // Check for the active tab from form submission
    if (isset($_POST['active_tab'])) {
        $active_tab = sanitize_text_field($_POST['active_tab']);
    }

    ?>
    <div class="wrap">
        <h1>Metal Price Calculator Settings</h1>
        <h2 class="nav-tab-wrapper">
            <a href="#adjustment-prices" class="nav-tab <?php echo $active_tab === 'adjustment-prices' ? 'nav-tab-active' : ''; ?>">Spot Price Adjustment</a>
            <a href="#buy-back-prices" class="nav-tab <?php echo $active_tab === 'buy-back-prices' ? 'nav-tab-active' : ''; ?>">Spot Price Buy Adjustment</a>
        </h2>

        <!-- Adjustment Prices Tab -->
        <div id="adjustment-prices" class="tab-content" style="display: <?php echo $active_tab === 'adjustment-prices' ? 'block' : 'none'; ?>;">
            <h2>Adjustment Prices</h2>
            <form method="post" action="" onsubmit="return confirmSubmit();">
                <?php settings_fields('metal_price_calculator_settings'); ?>
                <input type="hidden" name="active_tab" value="adjustment-prices">
                <?php foreach ($data as $metal => $prices): ?>
                    <div style="margin-bottom: 20px;">
                        <label><strong><?php echo ucfirst($metal); ?> Adjustment Price</strong></label>
                        <div class="price-input-wrap">
                            <span class="input-dollar">$</span>
                            <input class="price-input" type="number" step="0.01" name="metal_price_calculator_data[<?php echo $metal; ?>][adjustment_price]" value="<?php echo esc_attr(number_format((float)$prices['adjustment_price'], 2, '.', '')); ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
                <input type="submit" name="adjustment_prices_submit" class="button button-primary" value="Save Adjustment Prices">
            </form>
        </div>

        <!-- Buy Back Prices Tab -->
        <div id="buy-back-prices" class="tab-content" style="display: <?php echo $active_tab === 'buy-back-prices' ? 'block' : 'none'; ?>;">
            <h2>Buy Back Prices</h2>
            <form method="post" action="" onsubmit="return confirmSubmit();">
                <?php settings_fields('metal_price_calculator_settings'); ?>
                <input type="hidden" name="active_tab" value="buy-back-prices">
                <?php foreach ($data as $metal => $prices): ?>
                    <div style="margin-bottom: 20px;">
                        <label><strong><?php echo ucfirst($metal); ?> Buy Back Price</strong></label>
                        <div class="price-input-wrap">
                            <span class="input-dollar">$</span>
                            <input class="price-input" type="number" step="0.01" name="metal_price_calculator_data[<?php echo $metal; ?>][buy_back_price]" value="<?php echo esc_attr(number_format((float)$prices['buy_back_price'], 2, '.', '')); ?>">
                        </div>
                        <p><span style="font-size: 16px;">ⓘ</span> Price should be for ounces in AUD.</p>
                    </div>
                <?php endforeach; ?>
                <input type="submit" name="buy_back_prices_submit" class="button button-primary" value="Save Buy Back Prices">
            </form>
        </div>
    </div>
    <?php
}

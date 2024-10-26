<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('metal_price_calculator_data');

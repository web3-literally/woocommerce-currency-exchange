<?php

require_once 'connect_fixer_io.php';
require_once 'connect_xignite.php';
require_once 'global.php';

/**
 * Class PriceHelper
 * @package             WoocommerceRealTimePrices
 * @author              Mike Castro Demaria
 * @copyright           2020 Supersonique Studio
 * @license             GPL-2.0-or-later
 *
 * @uses                Get currency and metal rates from 3rd rest api and implement modules regarding with symbol rate
 */
class PriceHelper
{
    private $_metal_rates;
    private $_currency_rates;

    /**
     * @uses Get currency and metal rates from fixer.io and xignite.com
     */
    public function init() {
        // Get metal prices from xignite.com
        $this->_metal_rates = dump_metal_rates();

        // Get currency exchange rates from fixer.io
        $this->_currency_rates = dump_currency_rates();
    }

    /**
     * @uses Return the type and rate from the given symbol
     * @param $symbol
     * @param $default_type
     * @param $default_rate
     * @return array of symbol type and rate
     */
    public function check_symbol($symbol, $default_type, $default_rate) {
        $symbol_type = '';
        $symbol_rate = 0;
        if (array_key_exists($symbol, $this->_currency_rates)) {
            $symbol_type = CURRENCY;
            $symbol_rate = floatval($this->_currency_rates[$symbol]);
        } else if (array_key_exists($symbol, $this->_metal_rates)) {
            $symbol_type = METAL;
            $symbol_rate = floatval($this->_metal_rates[$symbol]);
        } else {
            $symbol_type = $default_type;
            $symbol_rate = $default_rate;
        }

        return array($symbol_type, $symbol_rate);
    }

    /**
     * @uses Return ratio from the given margin values, and if no margin exist, then return false
     * @param $category_margin
     * @param $product_margin
     * @param $variant_margin
     * @return float ratio
     */
    public function get_ratio($category_margin, $product_margin, $variant_margin) {
        if (floatval($variant_margin) > 0) {
            return (1 + floatval($variant_margin) / 100);
        } else if (floatval($product_margin) > 0) {
            return (1 + floatval($product_margin) / 100);
        } else if (floatval($category_margin) > 0) {
            return (1 + floatval($category_margin) / 100);
        } else {
            return floatval(0);
        }
    }
}


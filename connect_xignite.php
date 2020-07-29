<?php

require_once 'global.php';

/**
 * https://www.xignite.com/product/gold-metal#/DeveloperResources/request
 * token : 33924F17F8BA4943A98569602B00B44F
 * API Endpoints : https://globalmetals.xignite.com/xGlobalMetals.json/ListMetals?&_token=
 * https://globalmetals.xignite.com/xGlobalMetals.json/GetRealTimeMetalQuotes?Symbols=XAU, XAG, XPD&Currency=EUR&_token=33924F17F8BA4943A98569602B00B44F
 */

/**
 * "listmetals" endpoint - request metals list
 * https://globalmetals.xignite.com/xGlobalMetals.json/ListMetals?&_token=token
 * ? _token = YOUR_TOKEN
 */

/**
 * "get realtime meta quotes" endpoint - request realtime meta quotes of given symbols
 * https://globalmetals.xignite.com/xGlobalMetals.json/GetRealTimeMetalQuotes?Symbols=symbols&Currency=currency&_token=token
 * ? symbols = XAU,XAG,XPD,etc
 * & _token = YOUR_TOKEN
 * & currency = EUR
 */

function extract_metal_symbol($metal_info) {
    return trim($metal_info['Symbol']);
}

function extract_metal_price($metal_info) {
    return [trim($metal_info['Symbol']) => $metal_info['Ask']];
}

// Dump metal symbols https://xignite.com into array format
function dump_metal_symbols() {

    $token = '33924F17F8BA4943A98569602B00B44F';

    // Initialize CURL:
    $ch = curl_init('https://globalmetals.xignite.com/xGlobalMetals.json/ListMetals?&_token='.$token.'');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Store the data:
    $json = curl_exec($ch);
    curl_close($ch);

    // Decode JSON response:
    $listmetals = json_decode($json, true);

    $metalsymbols = array_map('extract_metal_symbol', $listmetals['MetalList']);

    return $metalsymbols;
}

// Dump realtime metal quotes from https://xignite.com into json format
function dump_realtime_metal_quotes($symbols) {

    $token = '33924F17F8BA4943A98569602B00B44F';

    // Initialize CURL:
    $ch = curl_init('https://globalmetals.xignite.com/xGlobalMetals.json/GetRealTimeMetalQuotes?Symbols='.$symbols.'&Currency=EUR&_token='.$token.'');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Store the data:
    $json = curl_exec($ch);
    curl_close($ch);

    // Decode JSON response:
    $metal_quotes = json_decode($json, true);
    $metal_prices = new stdClass();
    foreach ($metal_quotes as $metal_quote) {
        $symbol = $metal_quote['Symbol'];
        $metal_prices->$symbol = floatval($metal_quote['Ask']) * OZ_2_KG;
    }
    return $metal_prices;
}

// Dump all metal quotes
function dump_metal_rates() {
    $metal_symbols = dump_metal_symbols();
    $metal_prices = dump_realtime_metal_quotes(implode(',', $metal_symbols));
    return $metal_prices;
}


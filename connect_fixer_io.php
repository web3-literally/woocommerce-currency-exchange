<?php

// Dump currency exchange rates from https://fixer.io into json format
function dump_exchange_rates() {
    /**
     * https://fixer.io/documentation
     * API key : 07158c4311bf7350065e78993c83bec5
     * API Endpoints : http://data.fixer.io/api/
     */

    $access_key = '07158c4311bf7350065e78993c83bec5';

    /**
     * // "latest" endpoint - request the most recent exchange rate data
     * http://data.fixer.io/api/latest
     *  ? access_key = YOUR_ACCESS_KEY
     *  & base = GBP
     *  & symbols = USD,AUD,CAD,PLN,MXN
     */

     // set API Endpoint and API key
    $endpoint = 'latest';

    /**
     * // "historical" endpoint - request historical rates for a specific day
     * http://data.fixer.io/api/YYYY-MM-DD
     * ? access_key = YOUR_ACCESS_KEY
     * & base = JPY
     * & symbols = USD,AUD,CAD,PLN,MXN
     */

    /**
     * "convert" endpoint - convert any amount from one currency to another
     * using real-time exchange rates
     *
     * http://data.fixer.io/api/convert
     * ? access_key = YOUR_ACCESS_KEY
     * & from = USD
     * & to = EUR
     * & amount = 25
     *
     * append an additional "date" parameter if you want to use
     * historical rates for your conversion
     * & date = YYYY-MM-DD
     * WARNING current subscription does not support this API function.
     */

    /**
     * "timeseries" endpoint - request exchange rates for a specific period of time
     * http://data.fixer.io/api/timeseries
     * ? access_key = YOUR_ACCESS_KEY
     * & start_date = YYYY-MM-DD
     * & end_date = YYYY-MM-DD
     * & base = EUR
     * & symbols = USD,AUD,CAD,PLN,MXN
     * WARNING : current subscription does not support this API function.
     *
     * append an additional "date" parameter if you want to use
     * historical rates for your conversion
     *
     * & date = YYYY-MM-DD
     *
     * WARNING current subscription does not support this API function.
     */

    // Initialize CURL:
    $ch = curl_init('http://data.fixer.io/api/'.$endpoint.'?access_key='.$access_key.'');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Store the data:
    $json = curl_exec($ch);
    curl_close($ch);

    // Decode JSON response:
    $exchangeRates = json_decode($json, true);

    return $exchangeRates;
}

// Access the exchange rate values, e.g. GBP:
// echo $exchangeRates['rates']['GBP'];

/**
 * http://data.fixer.io/api/latest
 * ?access_key=07158c4311bf7350065e78993c83bec5
 * &symbols=USD,AUD,CAD,PLN,MXN
 * &format=1
 *
 * result  :
 * { "success":true,
 *  "timestamp":1594211226,
 *  "base":"EUR",
 *  "date":"2020-07-08",
 *  "rates":{
 *      "USD":1.128955,
 *      "AUD":1.624863,
 *      "CAD":1.533928,
 *      "PLN":4.476543,
 *      "MXN":25.67039
 *      }
 *  }
 */


?>

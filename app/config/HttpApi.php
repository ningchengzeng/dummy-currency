<?php
/**
 * Created by PhpStorm.
 * User: ningchengzeng
 * Date: 2018/3/16
 * Time: 下午10:12
 */

namespace App\Config;

use flight;

class HttpApi {
    public function init(){
        Flight::register("currency", "\App\Controllers\api\CurrencyController");
        Flight::route("/api/currency/currencyindexAll", function(){
            $currency = Flight::currency();
            Flight::json($currency->indexAll());
        });
    }
}
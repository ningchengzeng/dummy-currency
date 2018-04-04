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
        Flight::route("/api/currency/getCointradesPercent", function(){
            $currency = Flight::currency();
            Flight::json($currency->getCointradesPercent());
        });
        Flight::route("/api/currency/getCoinhisdata", function(){
            $currency = Flight::currency();
            Flight::json($currency->getCoinhisdata());
        });
        Flight::route("/api/currency/getCoinevent", function(){
            $currency = Flight::currency();
            Flight::json($currency->getCoinevent());
        });
        Flight::route("/api/currency/getCoinrank", function(){
            $currency = Flight::currency();
            Flight::json($currency->getCoinrank());
        });
        Flight::route("/api/currency/homenewcoin", function(){
            $currency = Flight::currency();
            Flight::json($currency->homenewcoin());
        });
        Flight::route("/api/currency/homevolrank", function(){
            $currency = Flight::currency();
            Flight::json($currency->homevolrank());
        });
        Flight::route("/api/currency/HomeCoinMaxChange", function(){
            $currency = Flight::currency();
            Flight::json($currency->HomeCoinMaxChange());
        });
        Flight::route("/api/currency/hotconcept", function(){
            $currency = Flight::currency();
            Flight::json($currency->hotconcept());
        });
        Flight::route("/api/currency/getDetails", function(){
            $currency = Flight::currency();
            Flight::json($currency->getDetails());
        });


    }



}
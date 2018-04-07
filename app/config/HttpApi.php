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
        Flight::route("/api/currency/indexAll", function(){
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
        Flight::route("/api/currency/getCurrencies", function(){
            $currency = Flight::currency();
            Flight::json($currency->getCurrencies());
        });
        Flight::route("/api/currency/getNewCoin", function(){
            $currency = Flight::currency();
            Flight::json($currency->getNewCoin());
        });
        Flight::route("/api/currency/getConcept", function(){
            $currency = Flight::currency();
            Flight::json($currency->getConcept());
        });
        Flight::route("/api/currency/getConceptCoin", function(){
            $currency = Flight::currency();
            Flight::json($currency->getConceptCoin());
        });
        Flight::route("/api/currency/getExchange", function(){
            $currency = Flight::currency();
            Flight::json($currency->getExchange());
        });
        Flight::route("/api/currency/getupdown", function(){
            $currency = Flight::currency();
            Flight::json($currency->gettup());
        });
        Flight::route("/api/currency/getcharts", function(){
            $currency = Flight::currency();
            Flight::json($currency->getcharts());
        });
        Flight::route("/api/currency/getvol", function(){
            $currency = Flight::currency();
            Flight::json($currency->getvol());
        });
        Flight::route("/api/currency/getvolexchange", function(){
            $currency = Flight::currency();
            Flight::json($currency->getvolexchange());
        });
        Flight::route("/api/currency/getMonthMxchange", function(){
            $currency = Flight::currency();
            Flight::json($currency->getMonthMxchange());
        });
    }



}
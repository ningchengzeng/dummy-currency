<?php
/**
 * Created by PhpStorm.
 * User: ningchengzeng
 * Date: 2018/3/14
 * Time: 下午4:09
 */

namespace App\Controllers\Api;

use App\Controllers\Controller;
use flight;

class CurrencyController extends Controller {

    public function __construct() {
    }

    /**
     * @return array
     *
     */
    public function indexAll() {
        $request = Flight::request();
        $pageData = $request->data;

        $pageSize = $pageData["pageSize"];
        $page = $pageData["page"];
        $keyword = $pageData["keyword"];
        $sort = $pageData["sort"];

        return array(
            "code" => 0,
            "page" => 1,
            "error" => null,
            "items" => array(
                array(
                    "id" => "**",
                    "currencyName" => "BTC-比特币",
                    "currencyEN" => "bitcoin",
                    "currencyIconSmall" => "",
                    "currencyIcon" => "",
                    "currencyType" => 1,
                    "marketCap" => 123123.000,
                    "marketAll" => 12312,
                    "price" => 123123.000,
                    "circulationNum" => 123123,
                    "circulationAllNum" => 123123.000,
                    "turnover" => 123123,
                    "rose" => 5.00,        //百分比数据
                    "trend" => [1,2,3,4,5,6,7,8,8],
                    "platformName" => "***",
                    "platformUrl" => "****"
                )
            )
        );
    }
}

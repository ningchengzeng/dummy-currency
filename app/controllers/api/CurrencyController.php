<?php
/**
 * Created by PhpStorm.
 * User: ningchengzeng
 * Date: 2018/3/14
 * Time: 下午4:09
 */

namespace App\Controllers\Api;

use App\Config\Database;
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

    public function getDetails()
    {
        $request = Flight::request();
        $pageData = $request->data;
        //$currency = $_GET['currency'];
        //$collection = Flight::db()->Currencies->title;
//        $connnect = new Database();
//        $table = $connnect->local;
        //$result=$connnect->Currencies;
        //$m = new Database();

        $collection = Flight::db()->Currencies;

        $query=array("code"=>"bitcoin");
        $cursor = $collection->find($query);

        $array = array();
        while($cursor->hasNext()) {
            $array[] = $cursor->getNext();
        }
        return $array;
    }


    /***
     * 调用接口 api.feixiaohao.com/cointrades_percent/bitcoin
     * @return json
     */
    public function getCointradesPercent()
    {
        $currency = $_GET['currency'];
        $url = 'api.feixiaohao.com/cointrades_percent/' . $currency . '/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        return json_decode(curl_exec($ch));
        //return $currency;
    }

    /***
     * 调用接口 api.feixiaohao.com/coinhisdata/bitcoin
     * @return json
     */
    public function getCoinhisdata()
    {
        $currency = $_GET['currency'];
        //$time = $_GET['time'];
        $url = 'api.feixiaohao.com/coinhisdata/' . $currency;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        return json_decode(curl_exec($ch));
    }

    /***
     * 调用接口 api.feixiaohao.com/coinrank/bitcoin
     * @return json
     */
    public function getCoinrank()
    {
        $currency = $_GET['currency'];
        //$time=$_GET['time'];
        $url = 'api.feixiaohao.com/coinrank/' . $currency;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        return json_decode(curl_exec($ch));
    }

    /**
     * 首页成交量排行榜
     */
    public function homevolrank(){
        $url = 'api.feixiaohao.com/vol/homevolrank/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/html; charset=utf-8"));
        $result = curl_exec($ch);
        $result = str_replace("//static.feixiaohao.com","themes",$result);
        $result = str_replace("platimages","coin",$result);
        $result = preg_replace("#/\d{8}/#", "/time/", $result);
        $result = str_replace("/currencies/","currencies.html?currency=",$result);
        return json_decode($result);
    }


    /**
     * 首页涨幅跌幅榜
     * @return mixed
     */
    public function HomeCoinMaxChange(){
        $url = 'api.feixiaohao.com/coins/HomeCoinMaxChange/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/html; charset=utf-8"));
        $result = curl_exec($ch);
        $result = str_replace("//static.feixiaohao.com","themes",$result);
        $result = preg_replace("#/\d{8}/#", "/time/", $result);
        $result = str_replace("/currencies/","currencies.html?currency=",$result);
        return json_decode($result);
    }


    /**
     * 首页OR内页热门概念
     * @return mixed
     */
    public function hotconcept()
    {
        $conceptid = $_GET['conceptid'];
        $url = 'api.feixiaohao.com/hotconcept/' . $conceptid . '/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/html; charset=utf-8"));
        $result = curl_exec($ch);
        $result = str_replace("//static.feixiaohao.com","themes",$result);
        $result = preg_replace("#/\d{8}/#", "/time/", $result);
        $result = str_replace("/currencies/","currencies.html?currency=",$result);
        return json_decode($result);
    }


    /***
     * 调用接口 api.feixiaohao.com/coinevent/bitcoin
     * 不做格式化直接返回HTML页面
     * @return json
     */
    public function getCoinevent()
    {
        $currency = $_GET['currency'];
        $url = 'api.feixiaohao.com/coinevent/' . $currency;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/html"));
        return curl_exec($ch);

    }




    /***
     *最新上市
     */
    public function homenewcoin()
    {
        $url = 'api.feixiaohao.com/coins/homenewcoin/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/html"));
        $result = curl_exec($ch);
        $result = preg_replace("#/\d{8}/#", "/time/", $result);
        $result = str_replace("//static.feixiaohao.com/coin", "themes/coin", $result);
        $result = str_replace("/currencies/", "currencies.html?currency=", $result);
        return $result;
    }





}

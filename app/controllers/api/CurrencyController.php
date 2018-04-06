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
        $pageData = $request->query;
        $pageSize = $pageData["pageSize"];
        $page = $pageData["page"];

        $keyword = $pageData["keyword"];
        $sort = $pageData["sort"];

        $collection = Flight::db()->Currencies_Price;

        ini_set('mongo.long_as_object', 1);

        $col = $collection->find();
        $col->limit($pageSize);
        $col->skip(($page-1) * $pageSize);

        $result = array();

        $index = 1;
        foreach($col as $document){
            $document["icon"] = str_replace("//static.feixiaohao.com","themes", $document["icon"]);
            $document["icon"] = preg_replace("#/\d{8}/#", "/time/", $document["icon"]);
            $document["index"] = $index + ($page-1) * $pageSize;
            $index ++;
            array_push($result, $document);
        }

        return array(
            "result" => $result,
            "count" => $collection->count()
        );
    }

    /**
     * 获取详情页数据查询mongo数据
     * @return mixed
     */
    public function getCurrencies()
    {
        $currency = $_GET['currency'];
        $query = array("code"=>$currency);
        $collection= Flight::db()->Currencies;
        ini_set('mongo.long_as_object', 1);
        return $collection->findOne($query);
    }

    /**
     * 最新上市
     * @return mixed
     */
    public function getNewCoin()
    {
        $collection= Flight::db()->Currencies_Grounding;
        ini_set('mongo.long_as_object', 1);
        $col = $collection->find();
        $result = array();
        foreach($col as $document){
            array_push($result,$document);
        }
        return $result;
    }

    /**
     * 交易平台
     * @return mixed
     */
    public function getExchange()
    {
        $pagesize = $_GET['pagesize'];
        $page=$_GET['page'];
        $collection= Flight::db()->Exchange;
        ini_set('mongo.long_as_object', 1);
        $col = $collection->find();
//        $col->sort(['star' => 1]);
        $col->skip(($page-1)*$pagesize);
        $col->limit($pagesize);

        $result = array();
        foreach($col as $document){
            $document["icon"] = str_replace("//static.feixiaohao.com", "themes", $document["icon"]);
            $document["icon"] = str_replace("platimages", "coin", $document["icon"]);
            //var_dump($document['icon']);
            $document["icon"] = preg_replace("#/\d{8}/#", "/time/", $document["icon"]);
            array_push($result,$document);
        }
        return $result;
    }


    public function gettup(){
        $col24up= Flight::db()->Currencies_Hour24_Up;
        $col24down= Flight::db()->Currencies_Hour24_Down;
        $colup= Flight::db()->Currencies_Hour_Up;
        $coldown= Flight::db()->Currencies_Hour_Down;
        $colwup= Flight::db()->Currencies_Week_Up;
        $colwdown= Flight::db()->Currencies_Week_Down;

        ini_set('mongo.long_as_object', 1);

        $col1 = $col24up->find();
        $col2 = $col24down->find();
        $col3 = $colup->find();
        $col4 = $coldown->find();
        $col5 = $colwup->find();
        $col6 = $colwdown->find();

//        $col->sort(['star' => 1]);
        $col24uplist=array();
        $col24downlist=array();
        $coluplist=array();
        $coldownlist=array();
        $colwuplist=array();
        $colwdownlist=array();

        $result = array();
        foreach($col1 as $document){
            $document["icon"] = str_replace("//static.feixiaohao.com","themes",$document["icon"]);
            $document["icon"] = preg_replace("#/\d{8}/#", "/time/", $document["icon"]);
            array_push($col24uplist,$document);
        }
        foreach($col2 as $document){
            $document["icon"] = str_replace("//static.feixiaohao.com","themes",$document["icon"]);
            $document["icon"] = preg_replace("#/\d{8}/#", "/time/", $document["icon"]);
            array_push($col24downlist,$document);
        }
        foreach($col3 as $document){
            $document["icon"] = str_replace("//static.feixiaohao.com","themes",$document["icon"]);
            $document["icon"] = preg_replace("#/\d{8}/#", "/time/", $document["icon"]);
            array_push($coluplist,$document);
        }
        foreach($col4 as $document){
            $document["icon"] = str_replace("//static.feixiaohao.com","themes",$document["icon"]);
            $document["icon"] = preg_replace("#/\d{8}/#", "/time/", $document["icon"]);
            array_push($coldownlist,$document);
        }
        foreach($col5 as $document){
            $document["icon"] = str_replace("//static.feixiaohao.com","themes",$document["icon"]);
            $document["icon"] = preg_replace("#/\d{8}/#", "/time/", $document["icon"]);
            array_push($colwuplist,$document);
        }
        foreach($col6 as $document){
            $document["icon"] = str_replace("//static.feixiaohao.com","themes",$document["icon"]);
            $document["icon"] = preg_replace("#/\d{8}/#", "/time/", $document["icon"]);
            array_push($colwdownlist,$document);
        }

        array_push($result,$col24uplist);
        array_push($result,$col24downlist);
        array_push($result,$coluplist);
        array_push($result,$coldownlist);
        array_push($result,$colwuplist);
        array_push($result,$colwdownlist);
        return $result;
    }

    public function getExchangeCount()
    {
        $collection= Flight::db()->Exchange;
        ini_set('mongo.long_as_object', 1);
        $col = $collection->find();
        return $col->count();
    }


    /**
     * 市值趋势
     * @return mixed
     */
    public function getcharts(){
        $dataType = $_GET['dataType'];
        $url = 'api.feixiaohao.com/charts/?dataType=0' . $dataType . '/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        return json_decode(curl_exec($ch));
    }


    /**
     * 24消失成交额排行榜(币种)
     * @return mixed
     */
    public function getvol(){
        $num = $_GET['page'];
        $url = 'api.feixiaohao.com/currencies/volrank/'.$num.'/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/html"));
        $result = curl_exec($ch);
        $result = str_replace("/currencies/","currencies.html?currency=",$result);
        $result = str_replace("/exchange/","exchangedetails.html?currency=",$result);
        return $result;
    }

    /**
     * 24消失成交额排行榜(交易平台)
     * @return mixed
     */
    public function getvolexchange(){
        $num = $_GET['page'];
        $url = 'api.feixiaohao.com/exchange/volrank/'.$num.'/?exchangeType=0';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/html"));
        return curl_exec($ch);
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
        $result = str_replace("/exchange/","exchangedetails.html?currency=",$result);
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
        $result = str_replace("/currencies/", "currencies.html?=", $result);
        return $result;
    }





}

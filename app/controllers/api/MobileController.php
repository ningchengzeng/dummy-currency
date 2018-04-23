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

class MobileController extends Controller {

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
//        $currency = $_GET['currency'];
//        $query = array("code"=>$currency);
//        $collection= Flight::db()->Currencies;
//        ini_set('mongo.long_as_object', 1);
//        $result = $collection->findOne($query);
//        $result['icon']=str_replace("//static.feixiaohao.com/coin/", "themes/coin/mid/", $result["icon"]);
//        return $result;

        $data = Flight::request()->data;
        $currency = $data['currency'];
        $psession = $data["psession"];

        $query = array("code"=>$currency);
        $collection= Flight::db()->Currencies;
        $userCurrency = Flight::db()->User_Currencies;
        $collectionExchange = Flight::db()->Exchange_Price;

        ini_set('mongo.long_as_object', 1);
        $colExchange = $collectionExchange->find(array('coinCode'=>$currency))->sort(array("price.cny"=> -1));
        $userCurrencyCount = $userCurrency->count(array("userid"=>$psession, "code"=> $currency));

        $exchangeList = array();
        $index = 0;
        foreach ($colExchange as $item){
            $index ++;
            $item["index"] = $index;
            $item["exchangeIcon"] = $this->purl($item["exchangeIcon"]);
            array_push($exchangeList, $item);
        }

        return array(
            "detail" => $collection->findOne($query),
            "focus" => $userCurrencyCount > 0,
            "exchange" => $exchangeList
        );
    }

    /**
     * ICO信息
     * @return array
     */
    public function getICO(){
        $currency = $_GET['currency'];
        $query = array("code"=>$currency);
        $collection= Flight::db()->Currencies_ICO;
        ini_set('mongo.long_as_object', 1);
        $col = $collection->findOne($query);
        return array("result"=>$col);
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
            $document["icon"] = str_replace("//static.feixiaohao.com", "themes", $document["icon"]);
            $document["icon"] = preg_replace("#/\d{8}/#", "/time/", $document["icon"]);
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
        $url = 'mapi.feixiaohao.com/v2/vol/morevol/?page='.$num;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/html"));
        $result = curl_exec($ch);
        $result = str_replace("/currencies/","currencies.html?currency=",$result);
        $result = str_replace("/exchange/","exchangedetails.html?currency=",$result);
        //static.feixiaohao.com/coin/eced1e28da4f16e117f471b08ad6e_mid.png
        $result = str_replace("//static.feixiaohao.com/coin/","themes/coin/mid/",$result);
        return $result;
    }

    /**
     * 24消失成交额排行榜(交易平台)
     * @return mixed
     */
    public function getvolexchange(){
        $num = $_GET['page'];
        //$url = 'api.feixiaohao.com/exchange/volrank/'.$num.'/?exchangeType=0';
        $url= 'mapi.feixiaohao.com/v2/vol/moreexchange/?exchangeType=0&page='.$num;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/html"));
        $result=curl_exec($ch);
        $result = str_replace("/currencies/","currencies.html?currency=",$result);
        $result = str_replace("/exchange/","exchangedetails.html?currency=",$result);
        $result = str_replace("//static.feixiaohao.com/platimages/","themes/coin/",$result);
        $result = str_replace(".png",".png".".jpg",$result);
        $result = preg_replace("#/\d{8}/#", "/time/", $result);
        return $result;
    }

    public function getMonthMxchange(){
        $num = $_GET['page'];
        $url= 'mapi.feixiaohao.com/vol/moremonthrank/?page='.$num;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/html"));
        $result=curl_exec($ch);
        $result = str_replace("//static.feixiaohao.com","themes",$result);
        $result = preg_replace("#/\d{8}/#", "/time/", $result);
        $result = str_replace("/currencies/","currencies.html?currency=",$result);
        $result = str_replace("/exchange/","exchangedetails.html?currency=",$result);
        return json_decode($result);
    }


    /***
     * 饼图
     * 调用接口 api.feixiaohao.com/cointrades_percent/bitcoin
     * @return json
     */
    public function getCointradesPercent()
    {
        $currency = $_GET['currency'];
        $url = 'mapi.feixiaohao.com/cointrades_percent/' . $currency . '/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        return json_decode(curl_exec($ch));
        //return $currency;
    }

    /***
     * 价格趋势
     * 调用接口 mapi.feixiaohao.com/coinhisdata/bitcoin
     * @return json
     */
    public function getCoinhisdata()
    {
        $currency = $_GET['currency'];
        //$time = $_GET['time'];
        $url = 'mapi.feixiaohao.com/coinhisdata/' . $currency;
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
        $url = 'mapi.feixiaohao.com/coinrank/' . $currency;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        return json_decode(curl_exec($ch));
    }


    /**
     * 首页成交量排行榜
     */
    public function homevolrank(){
        $CurrenciesPrice= Flight::db()->Currencies_Price;
        $Exchange= Flight::db()->Exchange;
        ini_set('mongo.long_as_object', 1);
        $col = $CurrenciesPrice->find()->sort(array("volume.cny"=> -1))->limit(10);
        $colExchange = $Exchange->find(array("price"=>array("\$exists"=> true)))->sort(array("rank"=> 1))->limit(10);

        $result1 = "<thead><tr><th>排名</th><th>名称</th><th>成交量</th></tr></thead><tbody>";
        $result2 = "<thead><tr><th>排名</th><th>交易所</th><th>成交量</th></tr></thead>";
        $index = 0;

        foreach ($col as $document){
            $icon = $this->purl($document["icon"]);

            $result1 = $result1."<tr>
                    <td><span>".++$index."</span></td>
                    <td><a href=\"currencies.html?currency=".$document["code"]." \" target='_blank'>
                        <img src=\"".$icon."\" alt=\"".$document["title"]."\">
                        ".$document["title"]."</a></td>
                    <td>".$document["volume"]["init"]."</td>
                   </tr>";
        }

        $index = 0;
        setlocale(LC_MONETARY,"en_US");
        foreach ($colExchange as $document){
            $icon = $this->purl($document["icon"]);
            $volume = money_format("%i",  $document["price"]["cny"]/10000);
            $volume = str_replace("USD ", "¥", $volume);

            $result2 = $result2."<tr>
                    <td><span>".++$index."</span></td>
                    <td><a href=\"exchangedetails.html?currenty=".$document["code"]." \" target='_blank'>
                        <img src=\"".$icon."\" alt=\"".$document["title"]."\">
                        ".$document["title"]."</a></td>
                    <td>".$volume."万</td>
                   </tr>";
        }
        return array(
            "result1" => $result1,
            "result2" => $result2
        );
    }


    /**
     * 首页涨幅跌幅榜
     * @return mixed
     */
    public function HomeCoinMaxChange(){
//        $url = 'api.feixiaohao.com/coins/HomeCoinMaxChange/';
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/html; charset=utf-8"));
//        $result = curl_exec($ch);
//        $result = str_replace("//static.feixiaohao.com","themes",$result);
//        $result = preg_replace("#/\d{8}/#", "/time/", $result);
//        $result = str_replace("/currencies/","currencies.html?currency=",$result);
//        return json_decode($result);

        $colup= Flight::db()->Currencies_Hour_Up;
        $col24up= Flight::db()->Currencies_Hour24_Up;
        $colwup= Flight::db()->Currencies_Week_Up;

        $coldown= Flight::db()->Currencies_Hour_Down;
        $col24down= Flight::db()->Currencies_Hour24_Down;
        $colwdown= Flight::db()->Currencies_Week_Down;

        ini_set('mongo.long_as_object', 1);

        $col1 = $col24up->find()->limit(8);
        $col2 = $col24down->find()->limit(8);

        $col3 = $colup->find()->limit(8);
        $col4 = $coldown->find()->limit(8);

        $col5 = $colwup->find()->limit(8);
        $col6 = $colwdown->find()->limit(8);

        $header = "<thead><tr><th>排名</th><th>名称</th><th> 价格</th><th>涨幅</th></tr></thead>";
        $table = "<table class=\"table table-rank noBg maxchange\" style=\"display: none\">$header<tbody>";
        $tableShow = "<table class=\"table table-rank noBg maxchange\">$header<tbody>";

        $col24upResult = $tableShow;
        $col24downResult = $tableShow;
        $colupResult = $table;
        $coldownResult = $table;
        $colwupResult = $table;
        $colwdownResult = $table;

        $index = 0;
        foreach($col1 as $document){
            $icon = $this->purl($document["icon"]);
            $tr = "<tr>
                        <td><span>".++$index."</span></td>
                        <td>
                            <a href=\"currencies.html?currency=".$document["code"]."\">
                                <img src=\"".$icon."\" alt=\"". $document["title"]["cn"]."\">".$document["title"]["cn"]."
                            </a>
                        </td>
                        <td>".$document["price"]["init"]."</td>
                        <td><span class=\"text-green\">".$document["proportion"]."</span></td>
                   </tr>";
            $col24upResult = $col24upResult.$tr;
        }
        $index = 0;
        foreach($col2 as $document){
            $icon = $this->purl($document["icon"]);
            $tr = "<tr>
                        <td><span>".++$index."</span></td>
                        <td>
                            <a href=\"currencies.html?currency=".$document["code"]."\">
                                <img src=\"".$icon."\" alt=\"". $document["title"]["cn"]."\">".$document["title"]["cn"]."
                            </a>
                        </td>
                        <td>".$document["price"]["init"]."</td>
                        <td><span class=\"text-green\">".$document["proportion"]."</span></td>
                   </tr>";
            $col24downResult = $col24downResult.$tr;
        }
        $index = 0;
        foreach($col3 as $document){
            $icon = $this->purl($document["icon"]);
            $tr = "<tr>
                        <td><span>".++$index."</span></td>
                        <td>
                            <a href=\"currencies.html?currency=".$document["code"]."\">
                                <img src=\"".$icon."\" alt=\"". $document["title"]["cn"]."\">".$document["title"]["cn"]."
                            </a>
                        </td>
                        <td>".$document["price"]["init"]."</td>
                        <td><span class=\"text-green\">".$document["proportion"]."</span></td>
                   </tr>";
            $colupResult = $colupResult.$tr;
        }
        $index = 0;
        foreach($col4 as $document){
            $icon = $this->purl($document["icon"]);
            $tr = "<tr>
                        <td><span>".++$index."</span></td>
                        <td>
                            <a href=\"currencies.html?currency=".$document["code"]."\">
                                <img src=\"".$icon."\" alt=\"". $document["title"]["cn"]."\">".$document["title"]["cn"]."
                            </a>
                        </td>
                        <td>".$document["price"]["init"]."</td>
                        <td><span class=\"text-green\">".$document["proportion"]."</span></td>
                   </tr>";
            $coldownResult = $coldownResult.$tr;
        }
        $index = 0;
        foreach($col5 as $document){
            $icon = $this->purl($document["icon"]);
            $tr = "<tr>
                        <td><span>".++$index."</span></td>
                        <td>
                            <a href=\"currencies.html?currency=".$document["code"]."\">
                                <img src=\"".$icon."\" alt=\"". $document["title"]["cn"]."\">".$document["title"]["cn"]."
                            </a>
                        </td>
                        <td>".$document["price"]["init"]."</td>
                        <td><span class=\"text-green\">".$document["proportion"]."</span></td>
                   </tr>";
            $colwupResult = $colwupResult.$tr;
        }
        $index = 0;
        foreach($col6 as $document){
            $icon = $this->purl($document["icon"]);
            $tr = "<tr>
                        <td><span>".++$index."</span></td>
                        <td>
                            <a href=\"currencies.html?currency=".$document["code"]."\">
                                <img src=\"".$icon."\" alt=\"". $document["title"]["cn"]."\">".$document["title"]["cn"]."
                            </a>
                        </td>
                        <td>".$document["price"]["init"]."</td>
                        <td><span class=\"text-green\">".$document["proportion"]."</span></td>
                   </tr>";
            $colwdownResult = $colwdownResult.$tr;
        }

        $colupResult = "$colupResult</tbody></table>";
        $col24upResult = "$col24upResult</tbody></table>";
        $colwupResult = "$colwupResult</tbody></table>";

        $col24downResult = "$col24downResult</tbody></table>";
        $coldownResult = "$coldownResult</tbody></table>";
        $colwdownResult = "$colwdownResult</tbody></table>";

        return array(
            "result1" => "$colupResult$col24upResult$colwupResult",
            "result2" => "$coldownResult$col24downResult$colwdownResult"
        );

    }


    /**
     * 首页OR内页热门概念
     * @return mixed
     */
    public function hotconcept()
    {
        $params = Flight::request()->query;
        $concept = Flight::db()->Concept;
        ini_set('mongo.long_as_object', 1);
        $title = "";
        $defaultConceptid = $params["conceptid"];
        if($defaultConceptid == 0){
            $col = $concept->find()->limit(8);
            $flage = true;
            foreach ($col as $document) {
                if($flage){
                    $defaultConceptid = $document["index"];
                    $title = $title."<a href=\"javascript:void(0);\" onclick=\"util.loadconcept(" .$document["index"]. ")\" class=\"active\">" .$document["title"]. "</a>";
                }
                else{
                    $title = $title."<a href=\"javascript:void(0);\" onclick=\"util.loadconcept(" .$document["index"]. ")\">" .$document["title"]. "</a>";
                }

                $flage = false;
            }
        }

        $trs = "";
        $currencies = Flight::db()->Currencies;
        $col = $currencies->find(array("concept.index"=>$defaultConceptid))->limit(6);
        foreach ($col as $document) {
            $icon = $this->purl($document["icon"]);

            $floatRate = $document["floatRate"];
            $updown = "tags-green";
            if($floatRate<0){
                $updown = "tags-red";
            }

            $trs = "$trs<tr>
                <td>
                    <a href=\"currencies.html?currency=".$document["code"]."/\" target=\"_blank\" title=\"".$document["title"]["cn"]."\">
                    <img src=\"".$icon."\">
                        ".$document["title"]["cn"]."
                    </a>
                </td>
                <td>￥".$document["price"]["cny"]."</td>
                <td><span class=\"".$updown."\">".$floatRate."%</span>
                </td>
                </tr>";
        }

        return array(
            "result1" => $title,
            "result2" => $trs
        );
    }


    /***
     * 调用接口 api.feixiaohao.com/coinevent/bitcoin
     * 不做格式化直接返回HTML页面
     * @return json
     */
    public function getCoinevent()
    {
        $currency = $_GET['currency'];
        $url = 'mapi.feixiaohao.com/coinevent/' . $currency;
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
        $thead = "<thead>
                    <tr><th>名称</th>
                    <th>价格</th>
                    <th>涨跌幅</th>
                    <th>时间</th>
                    </tr>
                  </thead>";

        $trs = "";
        $collection= Flight::db()->Currencies_Grounding;
        ini_set('mongo.long_as_object', 1);
        $col = $collection->find()->limit(10);

        foreach($col as $document){
            $document["icon"] = $this->purl($document["icon"]);

            $code = $document["code"];
            $icon = $document["icon"];
            $title = $document["title"]["cn"];
            $title_short = $document["title"]["short"];
            $price = $document["price"]["init"];
            $updown = $document["updown"];
            $updown_value = doubleval(str_replace("%","",$updown));
            $text_tag = "text-green";
            if($updown_value<0){
                $text_tag = "text-red";
            }

            $date = $document["date"];

            $trs = $trs.
                "<tr>
                    <td>
                        <a href=\"/currencies/currencies.html?=$code/\" target=\"_blank\">
                            <img src=\"$icon\" alt=\"$title\">$$title_short
                        </a>
                    </td>
                    <td>$price</td>
                    <td><span class=\"$text_tag\">$updown</span></td>
                    <td>$date</td></tr>
                <tr>";
        }
        return $thead. "<tbody>$trs</tbody>";
    }



    public function getmhotconcept()
    {
        $conceptid = $_GET["conceptid"];
        $url = 'mapi.feixiaohao.com/v2/mhotconcept/' . $conceptid .'/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $result = str_replace("//static.feixiaohao.com","themes",$result);
        $result = preg_replace("#/\d{8}/#", "/time/", $result);
        $result = str_replace("/currencies/","currencies.html?currency=",$result);
        return json_decode($result);
    }


    public function getmExchange()
    {
        $page = $_GET["page"];
        $url = 'mapi.feixiaohao.com/exchange/more_V2/?page='.$page;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $result = str_replace("//static.feixiaohao.com","themes",$result);
        $result = preg_replace("#/\d{8}/#", "/time/", $result);
        $result = str_replace("platimages", "coin", $result);
        $result = str_replace("png", "png.jpg", $result);
        $result = str_replace("/exchange/","exchangedetails.html?currency=",$result);

        return json_decode($result);
    }

    public function getGbi(){
        $time = $_GET['time'];
        //$time = $_GET['time'];
        $url = 'mapi.feixiaohao.com/gbi/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        return json_decode(curl_exec($ch));

    }

}

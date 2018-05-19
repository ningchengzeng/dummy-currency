<?php
/**
 * Created by PhpStorm.
 * User: ningchengzeng
 * Date: 2018/3/14
 * Time: 下午4:09
 */

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Controllers\VerifyImage;
use flight;

class CurrencyController extends Controller {

    public function __construct() {
    }

    public function loadhander(){
        $collection = Flight::db()->Global;
        ini_set('mongo.long_as_object', 1);
        $col = $collection->findOne();

        //GBI指数：<a  id=\"total_gbi\" class=\"totalvolcap\" href =\"gbi.html\">".$col["gbi"]["price"]."</a></div>
        $result = "<div class=\"leftSise\"> 
                        虚拟币：<a href=\"index.html?type=dummcy\">".$col["dummcy"]."</a><i class=\"space\"></i>
                        代币：<a href=\"index.html?type=token\">".$col["token"]."</a><i class=\"space\"></i>
                        交易平台：<a href=\"exchange.html\" >".$col["exchange"]."</a><i class=\"space\"></i>
                        24小时成交量：<a id=\"total_vol\" class=\"totalvolcap\" href =\"charts.html\" >".$col["amount"]["price"]."</a><i class=\"space\"></i>
                        总市值：<a  id=\"total_cap\" class=\"totalvolcap\" href =\"charts.html\" >".$col["market"]["price"]."</a><i class=\"space\"></i>
                        GBI指数：<a  id=\"total_gbi\" class=\"totalvolcap\" href =\"gbi.html\">".$col["gbi"]["price"]."</a><i class=\"space\"></i>
                        <div class=\"leftSise\"></div>
                    </div>
                    <div class=\"rightSise loginbar\">
                        <div class=\"userLinks userinfo\" style=\"display: none\">
                            <a class=\"username\" href=\"setting.html\"></a>&nbsp;&nbsp;|&nbsp;&nbsp;
                            <a rel=\"nofollow\" onclick=\"javascript: logout(); return false; \">退出</a>
                        </div>&nbsp;&nbsp;
                        <div class=\"userLinks unlogin\" style=\"display: none\">
                            <a rel =\"nofollow\" class=\"login\">登陆</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                            <a rel = \"nofollow\" class=\"signup\">注册</a>
                        </div>
                    </div>
                    <script src=\"scripts/loginwindow.js\"></script>
                                <script>
                                    var seting = {
                                        \"login\":\".login\",
                                        \"signup\":\".signup\",
                                        \"findPassword\":\"findpwd.html\",
                                        \"loginUrl\":\"登陆表单提交地址\",
                                        \"signupUrl\":\"注册表单提交地址\"}
                                outerWindow(seting);
                                getUserInfo();
                                </script>";
        return array("result"=>$result);
    }

    /**
     * gbi指数
     * @return mixed
     */
    public function gbi(){
        $url = 'api.feixiaohao.com/gbi';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->setUrlOption($ch);
        return json_decode(curl_exec($ch));
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

        $type = $pageData["type"];
        $limit = $pageData["limit"];
        $price = $pageData["price"];

        $collection = Flight::db()->Currencies_Price;
        ini_set('mongo.long_as_object', 1);

        $col = null;
        $count = 0;
        if($type == null){
            $col = $collection->find()->sort(array("marketCap.usd"=>-1));
            $col->limit($pageSize);
            $col->skip(($page-1) * $pageSize);
            $count = $collection->count();
        }
        else if($type=="token"){
            $col = $collection->find(array("assets"=>true))->sort(array("volume.usd"=>-1));
            if($limit=="true"){
                $col->limit(100);
                $count = 100;
            }
            else {
                if($price == "true"){
                    $col->sort(array("marketCap.usd"=> -1));
                }

                $col->limit($pageSize);
                $col->skip(($page-1) * $pageSize);
                $count = $collection->count(array("assets"=>true));
            }
        }
        else if($type=="dummcy"){
            $col = $collection->find(
                array(
                    "\$or"=> array(
                        array("assets"=>false),
                        array("assets"=> array("\$exists"=> false))
                    )
                )
            )->sort(array("volume.usd"=>-1));
            if($limit=="true"){
                $col->limit(100);
                $count = 100;
            }
            else {
                if($price == "true"){
                    $col->sort(array("marketCap.usd"=> -1));
                }

                $col->limit($pageSize);
                $col->skip(($page-1) * $pageSize);

                $count = $collection->count(array(
                    "\$or"=> array(
                        array("assets"=>false),
                        array("assets"=> array("\$exists"=> false))
                    )
                ));
            }
        }

        $result = array();

        $index = 1;
        if($col!=null){
            foreach($col as $document){
                $document["icon"] = $this->purl($document["icon"]);
                $document["index"] = $index + ($page-1) * $pageSize;
                $index ++;
                array_push($result, $document);
            }
        }


        return array(
            "result" => $result,
            "count" => $count
        );
    }

    /**
     * 导出数据
     */
    public function exportIndex(){
        $request = Flight::request();
        $pageData = $request->query;
        $type = $pageData["type"];

        $collection = Flight::db()->Currencies_Price;
        ini_set('mongo.long_as_object', 1);

        $col = null;
        if($type == null){
            $col = $collection->find()->sort(array("marketCap.usd"=>-1));
        }
        else if($type=="token"){
            $col = $collection->find(array("assets"=>true))->sort(array("volume.usd"=>-1));
        }
        else if($type=="dummcy"){
            $col = $collection->find(
                array(
                    "\$or"=> array(
                        array("assets"=>false),
                        array("assets"=> array("\$exists"=> false))
                    )
                )
            )->sort(array("volume.usd"=>-1));
        }

        $string =
            iconv('utf-8','gb2312',implode(",",
                array("序号","编号","名称","价格","流通价值","ifo","24小时涨跌"))). "\n";

        $index = 1;
        if($col!=null){
            foreach($col as $document){
                $string .=
                    implode(",",array(
                        $index,
                        $document["code"],
                        iconv('utf-8','gb2312',$document["title"]),
                        $document["price"]["cny"],
                        $document["marketCap"]['cny'],
                        $document["ifo"],
                        $document["updown24H"]
                    )). "\n";
                $index ++;
            }
        }

        $filename = date('Ymd').'.csv';
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        Flight::response()->write($string);
    }

    /**
     * @return array
     */
    public function getNotes(){
        $pageData = Flight::request()->query;
        $pageSize = $pageData["pageSize"];
        $page = $pageData["page"];

        $notice = Flight::db()->Notice;
        ini_set('mongo.long_as_object', 1);

        $col = $notice->find()->sort(array("time" => -1));
        $col->limit($pageSize);
        $col->skip(($page-1) * $pageSize);

        $index = 1;
        $result = array();
        foreach($col as $document){
            $document["exchange"]["icon"] = $this->purl($document["exchange"]["icon"]);
            $document["time"] = $document["time"]->toDateTime()->format('Y-m-d h:i');
            $index ++;
            array_push($result, $document);
        }

        return array(
            "result" => $result,
            "count" => $notice->count()
        );
    }

    /**
     * 获取详情页数据查询mongo数据
     * @return mixed
     */
    public function getCurrencies() {
        $data = Flight::request()->data;
        $currency = $data['currency'];
        $psession = $data["psession"];

        $query = array("code"=>$currency);
        $collection= Flight::db()->Currencies;
        $userCurrency = Flight::db()->User_Currencies;
        $collectionExchange = Flight::db()->Exchange_Price;

        ini_set('mongo.long_as_object', 1);
        $colExchange = $collectionExchange->find(array('coin.code'=>$currency))->sort(array("volume.cny"=> -1));
        $userCurrencyCount = $userCurrency->count(array("userid"=>$psession, "code"=> $currency));

        $exchangeList = array();
        $index = 0;
        foreach ($colExchange as $item){
            $index ++;
            $item["index"] = $index;
            $item["exchange"]["icon"] = $this->purl($item["exchange"]["icon"]);
            array_push($exchangeList, $item);
        }

        $currencie = $collection->findOne($query);
        $currencie["icon"] = $this->purl($currencie["icon"]);
        return array(
            "detail" => $currencie,
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
            $document["icon"] = $this->purl($document["icon"]);
            array_push($result,$document);
        }
        return $result;
    }

    /**
     * 概念行情
     * @return array
     */
    public function getConcept(){
        $collection= Flight::db()->Concept;
        ini_set('mongo.long_as_object', 1);

        $col = $collection->find();
        $result = array();
        foreach($col as $document){
            array_push($result,$document);
        }
        return $result;
    }

    /**
     * 概念行情内容
     * @return array
     */
    public function getConceptCoin(){
        $query = Flight::request()->query;
        $collection = Flight::db()->Concept;
        $currencies = Flight::db()->Currencies_Price;

        ini_set('mongo.long_as_object', 1);

        $document = $collection->findOne(array("index"=> $query["index"]));
        $colCon = $currencies->find(array("concept.index"=> $query["index"]))->sort(array("volume.usd"=>-1));;
        $list = array();
        $index = 0;
        foreach($colCon as $documentItem){
            $documentItem["index"] = ++$index;
            array_push($list, $documentItem);
        }
        return array(
            "list"=> $list,
            "desc" => $document
        );
    }

    /**
     * 交易平台
     * @return mixed
     */
    public function getExchange() {
        $query = Flight::request()->query;
        $pagesize = $query['pagesize'];
        $page= $query['page'];
        $filter = $query["filter"];
        $code = $query["code"];
        $type = $query["type"];

        $mongoQuery = array("description"=> array("\$exists"=> true));
        if($code != ""){
            $mongoQuery = array_merge($mongoQuery, array("country.code"=> $code));
        }

        if($type != ""){
            $mongoQuery = array_merge($mongoQuery, array("tags"=> $type));
        }

        $mongoSort = array();
        if($filter != ""){
            if($filter == 'title'){
                $mongoSort = array_merge($mongoSort, array('title' => 1));
            }

            if($filter == 'star'){
                $mongoSort = array_merge($mongoSort, array('star' => -1));
            }

            if($filter == 'coinCount'){
                $mongoSort = array_merge($mongoSort, array('transactionPairCount' => -1));
            }
        }else{
            $mongoSort = array("price.cny" => -1);
        }

        $collection = Flight::db()->Exchange;
        ini_set('mongo.long_as_object', 1);

        $col = $collection->find($mongoQuery)->sort($mongoSort);
        $col->skip(($page-1) * $pagesize);
        $col->limit($pagesize);

        $result = array();
        $index = 1;
        foreach($col as $document){
            $document["index"] = $index++;
            if(isset($document["icon"])){
                $document["icon"] = $this->purl($document["icon"]);
                array_push($result,$document);
            }
        }
        return array(
            "result" => $result,
            "count" => $collection->count()
        );
    }

    /**
     * 交易所详情
     * @return array
     */
    public function getExchangeDetail(){
        $query = Flight::request()->query;

        $collection = Flight::db()->Exchange;
        $collectionExchange = Flight::db()->Exchange_Price;
        ini_set('mongo.long_as_object', 1);

        $col = $collection->findOne(array("code" => $query["currenty"]));
        $colResult = $collectionExchange->find(array("exchange.code"=> $query["currenty"]))->sort(array("volume.cny" => -1));

        $col["icon"] = $this->purl($col["icon"]);;

        $result = array();
        foreach($colResult as $document){
            if(isset($document["coin"]["icon"])){
                $document["exchange"]['icon'] = $this->purl($document["exchange"]['icon']);;
                $document["coin"]['icon'] = $this->purl($document["coin"]['icon']);;
                array_push($result, $document);
            }
        }
        return array(
            "coin" => $result,
            "detail" => $col
        );

    }

    /**
     * 月交易排行版
     *
     * @return array
     */
    public function getMonthMxchange(){
        $collection = Flight::db()->Month_Exchange;
        ini_set('mongo.long_as_object', 1);
        $col = $collection->find();
        $result = array();
        $index = 1;
        foreach($col as $document){
            if(isset($document["icon"])){
                $document['index'] = $index++;

                $document["icon"] = $this->purl($document["icon"]);
                array_push($result,$document);
            }
        }
        return $result;
    }

    /**
     * 涨跌榜
     *
     * @return array
     */
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
            $document["icon"] = $this->purl($document["icon"]);
            array_push($col24uplist,$document);
        }
        foreach($col2 as $document){
            $document["icon"] = $this->purl($document["icon"]);
            array_push($col24downlist,$document);
        }
        foreach($col3 as $document){
            $document["icon"] = $this->purl($document["icon"]);
            array_push($coluplist,$document);
        }
        foreach($col4 as $document){
            $document["icon"] = $this->purl($document["icon"]);
            array_push($coldownlist,$document);
        }
        foreach($col5 as $document){
            $document["icon"] = $this->purl($document["icon"]);
            array_push($colwuplist,$document);
        }
        foreach($col6 as $document){
            $document["icon"] = $this->purl($document["icon"]);
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

    /**
     * 各大交易所最新公告
     */
    public function getloadnotes(){
        $notice = Flight::db()->Notice;
        ini_set('mongo.long_as_object', 1);

        $col = $notice->find()->sort(array("time" => -1))->limit(10);
        $index = 0;
        $result1 = "";
        $result2 = "";
        foreach ($col as $document){
            $index ++;
            $icon = $this->purl($document["exchange"]["icon"]);

            if($index<5){
                $result1 = "$result1<li>
                    <a href=\"".$document["href"]."\" target=\"_blank\" rel=\"nofollow\" title=\"".$document["exchange"]["title"].$document["title"]."\">
                        <span class=\"tit\">
                                <img src=\"".$icon."\" alt=\"".$document["exchange"]["title"]."\">
                            ".$document["title"]."
                        </span><span class=\"time\">".$document["time"]->toDateTime()->format('Y-m-d h:i')."</span>
                    </a>
                </li>";
            }else{
                $result2 = "$result2<li>
                    <a href=\"".$document["href"]."\" target=\"_blank\" rel=\"nofollow\" title=\"".$document["exchange"]["title"].$document["title"]."\">
                        <span class=\"tit\">
                                <img src=\"".$icon."\" alt=\"".$document["exchange"]["title"]."\">
                            ".$document["title"]."
                        </span><span class=\"time\">".$document["time"]->toDateTime()->format('Y-m-d h:i')."</span>
                    </a>
                </li>";
            }
        }

        return array(
            "result1" => $result1,
            "result2" => $result2
        );
    }

    /**
     * 市值趋势
     *
     * @return mixed
     */
    public function getcharts(){
        $dataType = $_GET['dataType'];
        $url = 'api.feixiaohao.com/charts/?dataType='.$dataType;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $this->setUrlOption($ch);
        return json_decode(curl_exec($ch));
    }

    /**
     * 24消失成交额排行榜(币种)
     * @return mixed
     */
    public function getvol(){
        $num = $_GET['page'];

        $url = 'api.feixiaohao.com/currencies/volrank/'.$num;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->setUrlOption($ch);
        $data = curl_exec($ch);

        $data = preg_replace("/\/currencies\/([\w\d]*)\//i","currencies.html?currency=$1", $data);
        $data = preg_replace("/\/exchange\/([\w\d]*)\//i","exchangedetails.html?currenty=$1",$data);
        return $data;
    }

    /**
     * 24消失成交额排行榜(交易平台)
     * @return mixed
     */
    public function getvolexchange(){
        $num = $_GET['page'];
        $url = 'api.feixiaohao.com/exchange/volrank/'.$num;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->setUrlOption($ch);
        $data = curl_exec($ch);

        $data = preg_replace("/\/currencies\/([\w\d]*)\//i","currencies.html?currency=$1", $data);
        $data = preg_replace("/\/exchange\/([\w\d]*)\//i","exchangedetails.html?currenty=$1",$data);
        return $data;
    }

    /***
     * 调用接口 api.feixiaohao.com/cointrades_percent/bitcoin
     * @return json
     */
    public function getCointradesPercent()
    {
        $currency = $_GET['currency'];
        $url = 'api.feixiaohao.com/cointrades_percent/' . $currency;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->setUrlOption($ch);
        return json_decode(curl_exec($ch));
    }

    /***
     * 调用接口 api.feixiaohao.com/coinhisdata/bitcoin
     * @return json
     */
    public function getCoinhisdata()
    {
        $currency = $_GET['currency'];
        $url = 'api.feixiaohao.com/coinhisdata/' . $currency;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->setUrlOption($ch);
        return json_decode(curl_exec($ch));
    }

    /***
     * 调用接口 api.feixiaohao.com/coinrank/bitcoin
     * @return json
     */
    public function getCoinrank()
    {
        $currency = $_GET['currency'];
        $url = 'api.feixiaohao.com/coinrank/' . $currency;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->setUrlOption($ch);
        return json_decode(curl_exec($ch));
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
        $this->setUrlOption($ch);
        return curl_exec($ch);
    }

    /**
     * @return mixed
     */
    public function exchangeCoinvol(){
        $currency = $_GET['currency'];
        $url = 'api.feixiaohao.com/exchange_coinvol/' . $currency;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->setUrlOption($ch);
        return json_decode(curl_exec($ch));
    }

    /**
     *
     * @return mixed
     */
    public function platformrank(){
        $currency = $_GET['currency'];
        $url = 'api.feixiaohao.com/platformrank/' . $currency;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->setUrlOption($ch);
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
    public function hotconcept(){
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
                    <a href=\"currencies.html?currency=".$document["code"]."/\" target=\"_blank\" title=\"".$document["title"]["en"] . $document["title"]["cn"]."\">
                    <img src=\"".$icon."\">
                        ". $document["title"]["en"] . $document["title"]["cn"]."
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

    /**
     *
     */
    public function hostconceptList(){
        $concept = Flight::db()->Concept;
        ini_set('mongo.long_as_object', 1);

        $col = $concept->find()->limit(8);
        $list = "<table class=\"table3 Hotidea\">
                 <thead>
                    <tr>
                        <th>名称</th>
                        <th>涨幅</th>
                        <th>表现最强</th>
                    </tr>
                </thead>
            <tbody>";
        foreach ($col as $document) {
            $list = $list."<tr>
                <td><a href=\"conceptcoin.html?id=".$document["index"]."\" target=\"_blank\">".$document["title"]."</a></td>
                <td><span class=\"text-green\">".$document["avrUpDown"]."</span></td>
                <td><a href=\"currencies.html?currency=".$document["up"]["code"]."\" target=\"_blank\">".$document["up"]["title"]."<span class=\"tags-green\">".$document["up"]["amount"]."</span></a></td>
                </tr>";
        }

        $list="$list</tbody></table>";
        return array(
            "result" => $list
        );
    }

    /**
     * @return array
     */
    public function showmarket(){
        $showmarkets = Flight::db()->Showmarkets;
        ini_set('mongo.long_as_object', 1);

        $col = $showmarkets->find();
        $result = "";
        foreach ($col as $item){
            $result = "$result<div class=\"cell\">
                            <div class=\"tit\"><a id=\"".$item["id"]."\" href=\"".$item["href"]."\" target=\"_blank\" rel=\"nofollow\">".$item["title"]."</a></div>
                            <div class=\"num\">".$item["num"]."</div>
                            <div class=\"char\">
                                <span class=\"line\">".$item["char"]."</span>
                            </div>
                        </div>";
        }

        return array(
            "result"=> $result
        );
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
                            <img src=\"$icon\" alt=\"$title\">$title_short
                        </a>
                    </td>
                    <td>$price</td>
                    <td><span class=\"$text_tag\">$updown</span></td>
                    <td>$date</td></tr>
                <tr>";
        }
        return $thead. "<tbody>$trs</tbody>";
    }
}

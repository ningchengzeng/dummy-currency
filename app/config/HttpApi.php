<?php
/**
 * Created by PhpStorm.
 * User: ningchengzeng
 * Date: 2018/3/16
 * Time: 下午10:12
 */

namespace App\Config;

use flight;
use MongoDB;

class HttpApi {
    public function init(){
        /**
         * 获取账户信息
         */
        Flight::route("/user/getUserInfo", function(){
            $request = Flight::request();
            $psession = $request->data["psession"];
            $user = Flight::db()->User;
            ini_set('mongo.long_as_object', 1);
            $col = $user->findOne(array("userid" => $psession));
            $userid = "";
            $username = "";

            if($col != null){
                $userid = $col["userid"] . "";
                $username = $col["username"];
            }

            Flight::json(array(
                "status"=>"success",
                "userid"=> $userid,
                "username" => Utils::hidtel($username)
            ));
        });

        /**
         * 获取账户详细信息
         */
        Flight::route("/user/main/userInfo", function(){
            $request = Flight::request();
            $psession = $request->data["psession"];
            $user = Flight::db()->User;
            ini_set('mongo.long_as_object', 1);
            $col = $user->findOne(array("userid" => $psession));

            if($col != null){
                $userid = $col["userid"] . "";
                $username = $col["username"];
                if(array_key_exists("usernick", $col)){
                    $usernick = $col['usernick'];
                }else{
                    $usernick = Utils::hidtel($username);
                }

                Flight::json(array(
                    "status"=>"success",
                    "userid"=> $userid,
                    "usernick" => $usernick,
                    "username" => $username,
                    "time" => date("Y年m月d日", $col['ts']->value)
                ));
            }
            else{
                Flight::json(array(
                    "status"=> "error"
                ));
            }
        });

        /**
         * 修改昵称
         */
        Flight::route("/user/main/usernick", function(){
            $request = Flight::request();
            $psession = $request->data["psession"];
            $usernick = $request->data["usernick"];
            $user = Flight::db()->User;
            ini_set('mongo.long_as_object', 1);

            $user->update(array("userid" => $psession),
                array("\$set"=>array(
                    "usernick" => $usernick
                )));

            Flight::json(array(
                "status"=>"success"
            ));
        });

        Flight::route("/user/main/modifypassword", function(){
            $request = Flight::request();
            $psession = $request->data["psession"];
            $passowrd = $request->data["password"];
            $user = Flight::db()->User;
            ini_set('mongo.long_as_object', 1);

            $col = $user->findOne(array("userid" => $psession));
            if($col != null){
                $oldpassword = $col['password'];
                if(md5($passowrd["old"]) != $oldpassword){
                    Flight::json(array(
                        "content" => "旧密码不正确！",
                        "status" => "error"
                    ));
                    return;
                }

                if($passowrd["new1"] != $passowrd["new2"]){
                    Flight::json(array(
                        "content" => "两次密码不相同！",
                        "status" => "error"
                    ));
                    return;
                }

                if($passowrd["old"] == $passowrd["new1"]){
                    Flight::json(array(
                        "content" => "旧密码和新密码相同,无法修改！",
                        "status" => "error"
                    ));
                    return;
                }

                $user->update(array("userid" => $psession), array("\$set"=>array(
                    "password" => md5($passowrd["new1"])
                )));
                Flight::json(array(
                    "status" => "success"
                ));
            }
            else{
                Flight::json(array(
                    "content" => "账户不存在或者没有登录！",
                    "status"=> "error"
                ));
            }
        });

        /**
         * 获取注册时的验证码
         */
        Flight::route("/user/GetSms", function(){
            $query = Flight::request()->query;
            $telno = $query["telno"];

            $user = Flight::db()->User;
            $sms = Flight::db()->Sms;
            ini_set('mongo.long_as_object', 1);

            $col = $user->findOne(array("username"=> $telno));
            $result = "1";
            if($col != null){
                $result = "3";
            }
            else{
                $captcha = Utils::generate_code();
                $smsCol = $sms->findOne(array("telno"=> $telno));
                if($col["ts"] > (time() - 60 * 3)){
                    $result = "2";
                }else{
                    $code = Utils::sendSmsCode($captcha, $telno);
                    if($code){
                        if($smsCol != null){
                            $sms->remove(array("telno" => $telno));
                        }
                        $document = array(
                            "telno" => $telno,
                            "captcha" => $captcha,
                            "ts" => time()
                        );
                        $sms->insert($document);
                        $result = "1";
                    }
                    else{
                        $result = "0";
                    }
                }
            }
            Flight::json(array("result"=> $result));
        });
        /**
         * 注册账户
         */
        Flight::route("POST /user/register", function(){
            $data = Flight::request()->data;
            $user = Flight::db()->User;
            $sms = Flight::db()->Sms;

            ini_set('mongo.long_as_object', 1);

            $telno = $data["userid"];
            $col = $user->findOne(array("username"=> $telno));

            if($col != null){
                Flight::json(array(
                    "content" => "账户已经存在！",
                    "status" => "error"
                ));
                return;
            }

            $password = $data["password"];
            $confirmPwd = $data["confirmPwd"];
            if($password != $confirmPwd){
                Flight::json(array(
                    "content" => "确认密码错误！",
                    "status" => "error"
                ));
                return;
            }

            $smsCol = $sms->findOne(array("telno"=> $telno));
            if($smsCol == null){
                Flight::json(array(
                    "content" => "请输入验证码！",
                    "status" => "error"
                ));
                return;
            }

            if($smsCol["captcha"] != $data["verifyCode"]){
                Flight::json(array(
                    "content" => "验证码不正确！",
                    "status" => "error"
                ));
                return;
            }

            $document = array(
                "userid" => uniqid(),
                "username" => $data["userid"],
                "password" => md5($data["password"]),
                "ts" => time()
            );
            $user->insert($document);
            Flight::json(array(
                "status" => "success"
            ));
        });
        /**
         * 账户登录
         */
        Flight::route("POST /user/login", function(){
            $data = Flight::request()->data;
            $user = Flight::db()->User;
            $userid = $data["userid"];
            $password = $data["password"];

            $col = $user->findOne(array("username"=> $userid));
            if($col == null){
                Flight::json(array(
                    "content" => "账户或密码错误！",
                    "status" => "error"
                ));
                return;
            }
            if($col["password"] != md5($password)){
                Flight::json(array(
                    "content" => "账户或密码错误！",
                    "status" => "error"
                ));
                return;
            }

            Flight::json(array(
                "status" => "success",
                "content" => $col["userid"] . "",
            ));
        });
        /**
         * 关注数据
         */
        Flight::route("POST /user/addfocus", function(){
            $request = Flight::request();
            $psession = $request->data["psession"];
            $currencyCode = $request->data["currency"];

            $user = Flight::db()->User;
            $currency = Flight::db()->Currencies;
            $currencyPrice = Flight::db()->Currencies_Price;
            $userCurrency = Flight::db()->User_Currencies;
            ini_set('mongo.long_as_object', 1);

            $colUser = $user->findOne(array("userid" => $psession));
            $currencyCount = $currency->count(array("code" => $currencyCode));
            $currencyDocument = $currencyPrice->findOne(array("code" => $currencyCode));

            if($currencyCount == 0){
                Flight::json(array(
                    "status" => "error",
                    "code" => "1",
                    "context" => "关注货币不存在！"
                ));
                return;
            }

            if($colUser == null){
                Flight::json(array(
                    "status" => "error",
                    "code" => "2",
                    "context" => "请重新登录后，在进行关注！"
                ));
                return;
            }

            $userCurrencyCount = $userCurrency->count(array("userid"=>$psession, "currency"=> $currencyCode));
            if($userCurrencyCount == 0){
                $currencyDocument["userid"] = $psession;

                $userCurrency->insert($currencyDocument);
                Flight::json(array(
                    "status" => "success",
                    "context" => "关注成功！"
                ));
                return;
            }else{
                Flight::json(array(
                    "status" => "success",
                    "context" => "已经被关注！"
                ));
                return;
            }
        });

        /**
         * 关注列表
         */
        Flight::route("POST /user/currency", function(){
            $pageData = Flight::request()->data;
            $pageSize = $pageData["pageSize"];
            $page = $pageData["page"];

            $type = $pageData["type"];
            $limit = $pageData["limit"];
            $psession = $pageData["psession"];

            $price = $pageData["price"];

            $collection = Flight::db()->User_Currencies;
            ini_set('mongo.long_as_object', 1);
            $col = null;
            $count = 0;
            if($type == null){
                $col = $collection->find(array("userid" => $psession))->sort(array("volume.usd"=>-1));
                $col->limit($pageSize);
                $col->skip(($page-1) * $pageSize);
                $count = $collection->count(array("userid" => $psession));
            }
            else if($type=="token"){
                $col = $collection->find(array("assets"=>true,"userid" => $psession))->sort(array("volume.usd"=>-1));
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
                    $count = $collection->count(array("assets"=>true,"userid" => $psession));
                }
            }
            else if($type=="dummcy"){
                $col = $collection->find(
                    array(
                        "\$or"=> array(
                            array("assets"=>false),
                            array("assets"=> array("\$exists"=> false))
                        ),
                        "userid" => $psession
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
                        ),"userid" => $psession

                    ));
                }
            }

            $result = array();
            $index = 1;
            if($col!=null){
                foreach($col as $document){
                    $document["icon"] = Utils::purl($document["icon"]);
                    $document["index"] = $index + ($page-1) * $pageSize;
                    $index ++;
                    array_push($result, $document);
                }
            }
            Flight::json(array(
                "result" => $result,
                "count" => $count
            ));
        });
        Flight::route("POST /user/unfocus", function(){
            $request = Flight::request();
            $psession = $request->data["psession"];
            $currencyCode = $request->data["currency"];

            $collection = Flight::db()->User_Currencies;
            ini_set('mongo.long_as_object', 1);
            $userCurrencyCount = $collection->count(array("userid"=>$psession, "currency"=> $currencyCode));
            if($userCurrencyCount == 0){
                $collection->remove(array("userid"=>$psession, "code"=> $currencyCode));
                Flight::json(array(
                    "status" => "success",
                    "context" => "取消关注成功！"
                ));
                return;
            }else{
                Flight::json(array(
                    "status" => "error",
                    "context" => "取消关注失败！"
                ));
                return;
            }
        });

        /**
         * 获取汇率信息
         */
        Flight::route("public/currency", function(){
            $request = Flight::request();
            $currency = Flight::db()->Currency;
            ini_set('mongo.long_as_object', 1);
        });

        Flight::register("currency", "\App\Controllers\api\CurrencyController");
        Flight::route("/api/currency/indexAll", function(){
            $currency = Flight::currency();
            Flight::json($currency->indexAll());
        });
        Flight::route("GET /api/currency/exportIndex", function (){
            Flight::currency()->exportIndex();
        });
        Flight::route("/api/currency/notes", function (){
            $currency = Flight::currency();
            Flight::json($currency->getNotes());
        });
        Flight::route("/api/currency/getICO", function(){
            $currency = Flight::currency();
            Flight::json($currency->getICO());
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
        Flight::route("/api/currency/getExchangeDetail",function(){
            $currency = Flight::currency();
            Flight::json($currency->getExchangeDetail());
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
        Flight::route("/api/currency/loadnotes", function(){
            $currency = Flight::currency();
            Flight::json($currency->getloadnotes());
        });
        Flight::route("/api/currency/loadhander", function(){
            $currency = Flight::currency();
            Flight::json($currency->loadhander());
        });
        Flight::route("/api/currency/hostconceptList", function(){
            $currency = Flight::currency();
            Flight::json($currency->hostconceptList());
        });
        Flight::route("/api/currency/showmarket", function(){
            $currency = Flight::currency();
            Flight::json($currency->showmarket());
        });


        Flight::register("mobile", "\App\Controllers\api\MobileController");
        Flight::route("/mapi/mobile/currencyindexAll", function(){
            $currency = Flight::mobile();
            Flight::json($currency->indexAll());
        });
        Flight::route("/mapi/mobile/indexHeader", function(){
            $currency = Flight::mobile();
            Flight::json($currency->indexHeader());
        });
        Flight::route("/mapi/mobile/search", function(){
            $currency = Flight::mobile();
            Flight::json($currency->search());
        });

        Flight::route("/mapi/mobile/getCointradesPercent", function(){
            $currency = Flight::mobile();
            Flight::json($currency->getCointradesPercent());
        });
        Flight::route("/mapi/mobile/getCoinhisdata", function(){
            $currency = Flight::mobile();
            Flight::json($currency->getCoinhisdata());
        });
        Flight::route("/mapi/mobile/getCoinevent", function(){
            $currency = Flight::mobile();
            Flight::json($currency->getCoinevent());
        });
        Flight::route("/mapi/mobile/getCoinrank", function(){
            $currency = Flight::mobile();
            Flight::json($currency->getCoinrank());
        });
        Flight::route("/mapi/mobile/homenewcoin", function(){
            $currency = Flight::mobile();
            Flight::json($currency->homenewcoin());
        });
        Flight::route("/mapi/mobile/homevolrank", function(){
            $currency = Flight::mobile();
            Flight::json($currency->homevolrank());
        });
        Flight::route("/mapi/mobile/HomeCoinMaxChange", function(){
            $currency = Flight::mobile();
            Flight::json($currency->HomeCoinMaxChange());
        });
        Flight::route("/mapi/mobile/hotconcept", function(){
            $currency = Flight::mobile();
            Flight::json($currency->hotconcept());
        });
        Flight::route("/mapi/mobile/getCurrencies", function(){
            $currency = Flight::mobile();
            Flight::json($currency->getCurrencies());
        });
        Flight::route("/mapi/mobile/getNewCoin", function(){
            $currency = Flight::mobile();
            Flight::json($currency->getNewCoin());
        });
        Flight::route("/mapi/mobile/getExchange", function(){
            $currency = Flight::mobile();
            Flight::json($currency->getExchange());
        });
        Flight::route("/mapi/mobile/getExchangeCount", function(){
            $currency = Flight::mobile();
            Flight::json($currency->getExchangeCount());
        });
        Flight::route("/mapi/mobile/getupdown", function(){
            $currency = Flight::mobile();
            Flight::json($currency->gettup());
        });
        Flight::route("/mapi/mobile/getcharts", function(){
            $currency = Flight::mobile();
            Flight::json($currency->getcharts());
        });
        Flight::route("/mapi/mobile/getvol", function(){
            $currency = Flight::mobile();
            Flight::json($currency->getvol());
        });
        Flight::route("/mapi/mobile/getvolexchange", function(){
            $currency = Flight::mobile();
            Flight::json($currency->getvolexchange());
        });
        Flight::route("/mapi/mobile/mhotconcept", function(){
            $currency = Flight::mobile();
            Flight::json($currency->getmhotconcept());
        });
        Flight::route("/mapi/mobile/monthmxchange", function(){
            $currency = Flight::mobile();
            Flight::json($currency->getMonthMxchange());
        });
        Flight::route("/mapi/mobile/mexchange", function(){
            $currency = Flight::mobile();
            Flight::json($currency->getmExchange());
        });
        Flight::route("/mapi/mobile/gbi", function(){
            $currency = Flight::mobile();
            Flight::json($currency->getGbi());
        });
        Flight::route("/mapi/mobile/getico", function(){
            $currency = Flight::mobile();
            Flight::json($currency->getICO());
        });
        Flight::route("/mapi/mobile/getConceptNew", function(){
            $currency = Flight::mobile();
            Flight::json($currency->getConceptNew());
        });
        Flight::route("/mapi/mobile/getExchangeDetail", function(){
            $currency = Flight::mobile();
            Flight::json($currency->getExchangeDetail());
        });
        Flight::route("/mapi/mobile/getConceptCoin", function(){
            $currency = Flight::mobile();
            Flight::json($currency->getConceptCoin());
        });
        
        
    }



}
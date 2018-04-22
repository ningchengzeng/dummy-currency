<?php
/**
 * Created by PhpStorm.
 * User: ningchengzeng
 * Date: 2018/4/22
 * Time: 下午5:33
 */

namespace App\Config;
use \Yunpian\Sdk\YunpianClient;

class Utils {
    static $URL = "https://sms.yunpian.com/v2/sms/single_send.json";
    static $KEY = "21eac3065ab4ea1594ad693a02afe484";

    static function generate_code($length = 4) {
        return rand(pow(10,($length-1)), pow(10,$length)-1);
    }

    static function hidtel($phone){
        $IsWhat = preg_match('/(0[0-9]{2,3}[\-]?[2-9][0-9]{6,7}[\-]?[0-9]?)/i',$phone); //固定电话
        if($IsWhat == 1){
            return preg_replace('/(0[0-9]{2,3}[\-]?[2-9])[0-9]{3,4}([0-9]{3}[\-]?[0-9]?)/i','$1****$2',$phone);
        }else{
            return  preg_replace('/(1[358]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$phone);
        }
    }

    /**
     * @param $path
     * @return mixed
     */
    static function purl($path){
        $replace = 'http://'.$_SERVER['HTTP_HOST'].':'.$_SERVER["SERVER_PORT"]."/image/";
        return str_replace("//static.feixiaohao.com", $replace, $path);
    }

    /**
     * 发送验证码
     */
    static function sendSmsCode($code, $phone){
        $message = "【币小金】验证码：$code ，10分钟内输入有效。";

        $clnt = YunpianClient::create(Utils::$KEY);
        $param = array(YunpianClient::MOBILE => $phone, YunpianClient::TEXT => '$message');
        $r = $clnt->sms()->single_send($param);

        var_dump($r);

        $resultString = Utils::doCurlPostRequest(Utils::$URL,array(
            "apikey" => Utils::$KEY,
            "text" => $message,
            "mobile" => $phone
        ));
        var_dump($resultString);
        $result = json_decode($resultString);

        return $result["code"];
    }

    static function doCurlPostRequest($url, $request = array(), $timeout = 5){
        var_dump($request);
        if($url == '' || $timeout <=0){
            return false;
        }
        $con = curl_init((string)$url);
        curl_setopt($con, CURLOPT_HEADER, false);
        curl_setopt($con, CURLOPT_POSTFIELDS, $request);
        curl_setopt($con, CURLOPT_POST,true);
        curl_setopt($con, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($con, CURLOPT_TIMEOUT,(int)$timeout);
        return curl_exec($con);
    }
}

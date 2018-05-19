<?php
/**
 * Created by PhpStorm.
 * User: ningchengzeng
 * Date: 2018/3/14
 * Time: 下午4:08
 */

namespace App\Controllers;


class Controller{

    public function __construct() {
    }

    public function purl($path){
        $replace = 'http://'.$_SERVER['HTTP_HOST'].':'.$_SERVER["SERVER_PORT"]."/image/";
        return str_replace("//static.feixiaohao.com", $replace, $path);
    }

    public function setUrlOption($ch){
        $time = time();
        $orderno = "VDT2018040901544209XhJ8Tk";
        $secret = "8a176456e5e431e3a817e07a14495280";

        $txt="orderno=".$orderno.",secret=".$secret.",timestamp=".$time;
        $sign = strtoupper(md5($txt));
        $auth = 'sign='.$sign.'&orderno='.$orderno.'&timestamp='.$time;

        curl_setopt($ch, CURLOPT_PROXY, "http://dynamic.xiongmaodaili.com:8088");
        curl_setopt($ch, CURLOPT_TIMEOUT,120);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Proxy-Authorization:".$auth,
            "Connection: keep-alive",
        ));
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_0) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11');
        curl_setopt($ch, CURLOPT_PROXY, "http://dynamic.xiongmaodaili.com:8088");
        curl_setopt($ch, CURLOPT_TIMEOUT,120);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,6);
    }
}
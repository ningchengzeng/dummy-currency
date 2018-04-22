<?php
/**
 * Created by PhpStorm.
 * User: ningchengzeng
 * Date: 2018/4/22
 * Time: 下午5:33
 */

namespace App\Config;

class Utils {
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

    static function purl($path){
        $replace = 'http://'.$_SERVER['HTTP_HOST'].':'.$_SERVER["SERVER_PORT"]."/image/";
        return str_replace("//static.feixiaohao.com", $replace, $path);
    }
}

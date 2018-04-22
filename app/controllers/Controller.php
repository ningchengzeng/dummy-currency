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
}
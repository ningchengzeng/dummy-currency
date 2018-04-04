<?php
/**
 * Created by PhpStorm.
 * User: ningchengzeng
 * Date: 2018/3/16
 * Time: 下午10:39
 */

namespace App\Config;

use flight;
use MongoDB\Client;

class Database {
    private $config = array(
        "url" => 'mongodb://127.0.0.1/',
        "database" => "local"
    );

    public function init(){
        Flight::map("db", function(){
            $client = new Client($this->config["url"]);
            return $client->selectDatabase($this->config["database"]);
        });
    }
}
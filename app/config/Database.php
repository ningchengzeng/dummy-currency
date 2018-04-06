<?php
/**
 * Created by PhpStorm.
 * User: ningchengzeng
 * Date: 2018/3/16
 * Time: 下午10:39
 */

namespace App\Config;

use flight;
use MongoClient;

class Database {
    private $config = array(
        "url" => 'mongodb://127.0.0.1:27017',
        "database" => "dummcy"
    );

    public function init(){
        Flight::map("db", function(){
             $client = new MongoClient($this->config["url"]);
            return $client->selectDB($this->config["database"]);
        });
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: ningchengzeng
 * Date: 2018/3/13
 * Time: 下午9:47
 */

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

header('x-powered-by: PHP');
header('Server: CentOS');
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Content-type: text/html; charset=UTF-8');

require_once __DIR__ . "/vendor/autoload.php";

use App\Config\HttpApi;
use App\Config\HttpAdmin;

$database = new App\Config\Database();
$database->init();

$httpApi = new HttpApi();
$httpApi->init();

$httpAdmin = new HttpAdmin();
$httpAdmin->init();

Flight::map('notFound', function(){
    Flight::json(array(
        "code" => 404,
        "message"=> "not found"
    ));
});

Flight::start();
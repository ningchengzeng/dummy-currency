<?php
/**
 * Created by PhpStorm.
 * User: ningchengzeng
 * Date: 2018/4/21
 * Time: 上午12:08
 */
namespace App\Config;

use flight;

class HttpImage {
    public function init(){
        Flight::route("/image/*", function(){
            $request_url = Flight::request()->url;
            $urls = explode(".", $request_url,3);

            $request_url = str_replace("//","/", $urls[0].".".$urls[1]);
            $request_url = preg_replace("#^/image/#", "", $request_url);

            $path = __DIR__."/../../views/image/";
            $filename = pathinfo($request_url, PATHINFO_BASENAME);
            $image_file = "$path$filename";
            if(!file_exists($image_file)){
                $http_image = "http://static.feixiaohao.com/$request_url";

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $http_image);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
                $file = curl_exec($ch);
                curl_close($ch);
                $resource = fopen($path . $filename, 'a');
                fwrite($resource, $file);
                fclose($resource);
            }

            $size = filesize("$image_file");
            $content = fread(fopen($image_file, "r"), $size);

            header("Content-Length: $size");
            if(strtolower(pathinfo($image_file)['extension']) == "png"){
                header('Content-type: image/png');
            }
            if(strtolower(pathinfo($image_file)['extension']) == "jpg"){
                header('Content-type: image/jpg');
            }

            echo $content;
        });
    }
}
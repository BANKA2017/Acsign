<?php
/**
 * Author: BANKA2017
 * Version: 4.1
 */
header('Content-Type: text/txt; charset=UTF-8');
set_time_limit(0);
ignore_user_abort(true);
require(dirname(__FILE__) . "/acsign.php");
echo "Acsign\n";

PHP_SAPI == "cli" ? define('IS_CLI',true) : define('IS_CLI',false);

if(file_exists(dirname(__FILE__) . '/task.json')){
    $data = $data = json_decode(file_get_contents(dirname(__FILE__) . '/task.json') , true);
    $ut = 0;//usertype
}elseif(IS_CLI){
    //TODO 多用户
    if(isset($argv[1]) && isset($argv[2])){
        $data = [["status" => 1, "date" => 0, "account" => ["username" => $argv[1], "password" => $argv[2]], "token" => ["access_token" => ""]]];
        $ut = 1;
    }else{
        die("Acsign : 缺少有效参数\n");
    }
}elseif(isset($_REQUEST["username"]) && isset($_REQUEST["password"])){
    $data = [["status" => 1, "date" => 0, "account" => ["username" => $_REQUEST["username"], "password" => $_REQUEST["password"]], "token" => ["access_token" => ""]]];
    $ut = 2;
}else{
    die("Acsign : 缺少有效参数\n");
}
$sign = new Acsign;
foreach($data as $key => $value){
    //检查登录情况
    if($value["status"] && $value["date"] < $sign -> get_date()){
        $sign -> username = $value["account"]["username"];
        $sign -> password = $value["account"]["password"];
        $sign -> access_token = $value["token"]["access_token"];
        $login_state = false;
        if(!$sign -> access_token || !$sign -> c_sign()){
            if($sign -> username && $sign -> password){
                $login_state = $sign -> mo_login();
            }else{
                $login_state = false;
            }
        }
        if($login_state){
            $sign -> display();
            if(!$ut){
                $data[$key]["cookie"]["access_token"] = $sign -> access_token;
                $data[$key]["date"] = $sign -> signed_date;
            }
        }
    }
}
if(!$ut){
    file_put_contents(dirname(__FILE__) . '/task.json', json_encode($data,JSON_UNESCAPED_UNICODE));
}
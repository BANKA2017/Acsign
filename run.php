<?php
/**
 * Author: BANKA2017
 * Version: 5.0
 */
header('Content-Type: text/txt; charset=UTF-8');
set_time_limit(0);
ignore_user_abort(true);
require(__DIR__ . '/acsign.php');
echo "Acsign\n";

$ut = -1;//usertype
$userInfo = [];
$dataTemplate = [
    "status" => 1,
    "date" => 0,
    "account" => [
        "username" => "",
        "password" => ""
    ],
    "token" => [
        "access_token" => "",
        "acPasstoken" => "",
        "auth_key" => ""
    ]
];

if((getenv("ACSIGN_USERNAME") && getenv("ACSIGN_PASSWORD")) || (isset($argv[1]) && isset($argv[2])) || (isset($_REQUEST["username"]) && isset($_REQUEST["password"]))){
    //TODO 多用户
    $userInfo[] = $dataTemplate;
    $userInfo[0]["account"]["username"] = getenv("ACSIGN_USERNAME")??$argv[1]??$_REQUEST["username"];
    $userInfo[0]["account"]["password"] = getenv("ACSIGN_PASSWORD")??$argv[2]??$_REQUEST["username"];
    $ut = 1;
}elseif(file_exists(__DIR__ . '/task.json')){
    $userInfo = json_decode(file_get_contents(__DIR__ . '/task.json'), true);
    $ut = 0;
}else{
    die("Acsign : 缺少有效参数或文件\n");
}

$sign = new Acsign;
foreach($userInfo as $order => $value){
    if($value["status"] && $value["date"] < date("Ymd")){
        $sign->username = $value["account"]["username"];
        $sign->password = $value["account"]["password"];
        $sign->access_token = $value["token"]["access_token"]??null;
        $sign->acPasstoken = $value["token"]["acPasstoken"]??null;
        $sign->auth_key = $value["token"]["auth_key"]??null;
        $checkSign = $sign->c_sign();
        if($checkSign["result"] == -401){
            if((bool)$sign->username && (bool)$sign->password){
                $checkSign["result"] = (int)$sign->mo_login();
            }else{
                $checkSign["result"] = 1;
            }
        }
        if(!$checkSign["result"]){
            echo "[{$order}]" . $sign->username . " -> sign:" . $sign->mo_nsign() . "\n";
            if(!$ut){
                $userInfo[$order]["token"]["access_token"] = $sign->access_token;
                $userInfo[$order]["token"]["acPasstoken"] = $sign->acPasstoken;
                $userInfo[$order]["token"]["auth_key"] = $sign->auth_key;
                $userInfo[$order]["date"] = date("Ymd");
            }
        }
    }else{
        echo "[{$order}]{$value["account"]["username"]} -> 已跳过\n";
    }
}
if(!$ut){
    file_put_contents(__DIR__ . '/task.json', json_encode($userInfo, JSON_UNESCAPED_UNICODE));
}
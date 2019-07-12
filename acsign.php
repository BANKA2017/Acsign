<?php

/**
 * Author: BANKA2017
 * Version: 4.1
 */
class Acsign{
    public $username, $password, $date, $access_token;
    private $ch;
    private function scurl($url) {
        $this -> ch = curl_init();
        curl_setopt($this -> ch, CURLOPT_URL, $url);
        curl_setopt($this -> ch, CURLOPT_USERAGENT, 'acvideo core/5.14.0.655');
        curl_setopt($this -> ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($this -> ch, CURLOPT_RETURNTRANSFER, true);
        return $this;
    }

    /*获取日期*/
    public function get_date() {
        return date("Ymd");
    }

    /*客户端登录*/
    public function mo_login() {
        self::scurl('http://account.app.acfun.cn/api/account/signin/normal');
        curl_setopt($this -> ch, CURLOPT_POST, true);
        curl_setopt($this -> ch, CURLOPT_POSTFIELDS, ["username" => $this -> username, "password" => $this -> password, "cid" => "ELSH6ruK0qva88DD"]);
        $json = json_decode(curl_exec($this -> ch), true);
        if(!$json["errorid"]){
            $this->access_token = $json["vdata"]["token"];
        }
        curl_close($this -> ch);
        return $json["errorid"];
    }

    /*客户端签到接口*/
    public function mo_nsign()
    {
        if ($this->get_date() > $this -> date) {
            self::scurl('http://api.new-app.acfun.cn/rest/app/user/signIn');
            curl_setopt($this -> ch, CURLOPT_POST, true);
            curl_setopt($this -> ch, CURLOPT_POSTFIELDS, array("access_token" => $this->access_token));
            curl_setopt($this -> ch, CURLOPT_ENCODING, "gzip");
            curl_setopt($this -> ch, CURLOPT_HTTPHEADER, ["acPlatform: ANDROID_PHONE"]);
            $sign = json_decode(curl_exec($this -> ch), true);
            curl_close($this -> ch);
            $this->signed_date = $this->get_date();
            return $sign["msg"];
        } else {
            return "今日已签到";
        }
    }

    /*客户端检查签到接口*/
    public function c_sign() {
        self::scurl('http://api.new-app.acfun.cn/rest/app/user/hasSignedIn');
        curl_setopt($this -> ch, CURLOPT_POST, true);
        curl_setopt($this -> ch, CURLOPT_POSTFIELDS, array("access_token" => $this->access_token));
        curl_setopt($this -> ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($this -> ch, CURLOPT_HTTPHEADER, ["acPlatform: ANDROID_PHONE"]);
        $sign = json_decode(curl_exec($this -> ch), true);
        curl_close($this -> ch);
        return !(!isset($sign["hasSignedIn"]) || !$sign["hasSignedIn"]);
    }

    /*显示*/
    public function display() {
        echo $this->username . " -> sign:" . self::mo_nsign() . "\n";
    }
}
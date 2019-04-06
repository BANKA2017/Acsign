<?php
/**
 * Author: BANKA2017
 * Version: v4
 */
class Acsign{
    public $username, $password, $date, $access_token, $acPasstoken;
    private $ch;
    private function scurl($url, $cookie = '') {
        $this -> ch = curl_init();
        curl_setopt($this -> ch, CURLOPT_URL, $url);
        curl_setopt($this -> ch, CURLOPT_USERAGENT, "acvideo core/5.14.0.655");
        curl_setopt($this -> ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($this -> ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this -> ch, CURLOPT_COOKIE, $cookie);
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
        $content = curl_exec($this -> ch);
        curl_close($this -> ch);
        $json = json_decode($content, true);
        $this->access_token = $json["vdata"]["token"];
        $this->acPasstoken = $json["vdata"]["acPasstoken"];
        return true;
    }

    /*客户端签到接口*/
    public function mo_sign()
    {
        if ($this->get_date() > $this -> date) {
            self::scurl('http://api.new-app.acfun.cn/rest/app/user/signIn', "acPasstoken=" . $this->acPasstoken);
            curl_setopt($this -> ch, CURLOPT_POST, true);
            curl_setopt($this -> ch, CURLOPT_POSTFIELDS, array("access_token" => $this->access_token));
            curl_setopt($this -> ch, CURLOPT_ENCODING, "gzip");
            curl_setopt($this -> ch, CURLOPT_HTTPHEADER, ["acPlatform: ANDROID_PHONE", "appVersion: 5.14.0.655"]);
            $sign = json_decode(curl_exec($this -> ch), true);
            curl_close($this -> ch);
            $this -> date = $this->get_date();
            return $sign["msg"];
        } else {
            return "今日已签到";
        }
    }

    /*客户端检查签到接口*/
    public function c_sign() {
        self::scurl('http://api.new-app.acfun.cn/rest/app/user/hasSignedIn', "acPasstoken=" . $this -> acPasstoken);
        curl_setopt($this -> ch, CURLOPT_POST, true);
        curl_setopt($this -> ch, CURLOPT_POSTFIELDS, array("access_token" => $this->access_token));
        curl_setopt($this -> ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($this -> ch, CURLOPT_HTTPHEADER, ["acPlatform: ANDROID_PHONE"]);
        $sign = json_decode(curl_exec($this -> ch), true);
        curl_close($this -> ch);
        if (!isset($sign["hasSignedIn"]) || (isset($sign["hasSignedIn"]) || !$sign["hasSignedIn"])) {
            return false;
        } else {
            return true;
        }
    }

    /*显示*/
    public function display() {
        echo $this->username . " -> sign:" . self::mo_sign() . "\n";
    }

    public function fp($path, $text) {
        if (file_put_contents($path, $text)) {
            return true;
        } else {
            return false;
        }
    }
}
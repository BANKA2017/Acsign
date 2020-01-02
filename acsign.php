<?php
/**
 * Author: BANKA2017
 * Version: 5.0
 */
class Acsign{
    public $username, $password, $access_token, $auth_key, $acPasstoken;
    private function scurl(string $url = "localhost", string $type = "get", array $header = [], array $data = []): array {
        $ch = curl_init();
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => "acvideo core/6.11.1.822",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => $header,
        ];
        if(strtolower($type) == "post"){
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($data);
        }
        curl_setopt_array($ch, $options);
        return json_decode(curl_exec($ch), true);
    }

    /*客户端登录*/
    public function mo_login(): bool {
        $loginResult = self::scurl("https://id.app.acfun.cn/rest/app/login/signin", "post", ["deviceType: 1"], ["username" => $this->username, "password" => $this->password]);
        if(!($loginResult["result"]??true)){
            $this->access_token = $loginResult["token"];
            $this->acPasstoken = $loginResult["acPassToken"];
            $this->auth_key = $loginResult["auth_key"];
        }
        return (bool)$loginResult["result"]??false;
    }

    /*客户端检查签到接口*/
    public function c_sign(): array {
        $checkSign = self::scurl("https://api-new.acfunchina.com/rest/app/user/hasSignedIn", "post", ["acPlatform: ANDROID_PHONE", "Cookie: auth_key={$this->auth_key};acPasstoken={$this->acPasstoken}"], ["access_token" => $this->access_token]);
        return ["result" => $checkSign["result"] ?? -401, "hasSignedIn" => $checkSign["hasSignedIn"] ?? false];
    }

    /*客户端签到接口*/
    public function mo_nsign(): string {
        $signInfo = self::scurl("https://api-new.acfunchina.com/rest/app/user/signIn", "post", ["acPlatform: ANDROID_PHONE", "Cookie: auth_key={$this->auth_key};acPasstoken={$this->acPasstoken}"], ["access_token" => $this->access_token]);
        return $signInfo["msg"] ?? "网络请求失败";
    }
}
<?php
/**
 * Author: BANKA2017
 * Version: 3.2
 */
header('Content-Type: text/txt; charset=UTF-8');
set_time_limit(0);
ignore_user_abort(true);
$data = json_decode(file_get_contents(dirname(__FILE__) . '/task.json') , true);
class Acsign
{
    private function scurl($url, $cookie) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        return $ch;
    }
    /*获取日期*/
    public function get_date() {
        return date("ymd");
    }
    /*检查在线状态*/
    public function online() {
        $ch = $this->scurl('http://www.acfun.cn/online.aspx', $this->cookie);
        $content = curl_exec($ch);
        curl_close($ch);
        return json_decode($content, true);
    }
    /*食我大蕉*/
    public function banana() {
        $ch = $this->scurl('http://www.acfun.cn/banana/throwBanana.aspx', $this->cookie);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.acfun.cn/v/ac' . $this->data["banana"]["acid"]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array("contentId" => $this->data["banana"]["acid"], "count" => $this->data["banana"]["number"], "userId" => $this->data["banana"]["userid"]));
        @$content = curl_exec($ch);
        curl_close($ch);
        if (json_decode($content, 1)["success"] == true) {
            $this->data["banana"]["date"] = $this->get_date();
        } else {
            echo "失败原因:" . @json_decode($content, 1)["result"] . "\n";
        }
    }
    /*网页登录*/
    public function login() {
        $ch = $this->scurl('http://www.acfun.cn/login.aspx', '');
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.acfun.cn/login/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array("username" => $this->data["account"]["username"], "password" => $this->data["account"]["password"]));
        $content = curl_exec($ch);
        curl_close($ch);
        $cookie = '';
        preg_match_all('/Set-Cookie:(.*;)/iU', $content, $str);
        foreach ($str[1] as $key) {
            $cookie .= $key;
        }
        $this->cookie = $cookie;
    }
    /*客户端登录*/
    public function mologin() {
        $ch = $this->scurl('http://account.app.acfun.cn/api/account/signin/normal', '');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array("username" => $this->data["account"]["username"], "password" => $this->data["account"]["password"], "cid" => "ELSH6ruK0qva88DD"));
        $content = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($content, true);
        $this->uid = $json["vdata"]["info"]["userid"];
        $this->access_token = $json["vdata"]["token"];
    }
    /*网页旧签到接口*/
    public function sign() {
        $ch = $this->scurl('http://www.acfun.cn/webapi/record/actions/signin?channel=0', $this->cookie);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.acfun.cn/menber/');
        curl_setopt($ch, CURLOPT_POST, true);
        $sign = json_decode(curl_exec($ch) , true);
        curl_close($ch);
        switch ($sign["code"]) {
            case 200:
                $r = "签到成功";
                break;
            case 410004:
                $r = "今日已签到";
                break;
            case 401:
                $r = "请先登录";
                break;
            default:
                $r = "未知错误#" . $sign["code"];
        }
        return $r;
    }
    /*网页新签到接口*/
    public function nsign() {
        $l = base64_encode(substr(base_convert(lcg_value(),10,36),2));
        $ch = $this->scurl('http://www.acfun.cn/nd/pst?locationPath=signin&certified=' . $l . '&channel=0&data=' . time() . '000', $this->cookie.'stochastic=' . $l . ';');
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.acfun.cn/menber/');
        curl_setopt($ch, CURLOPT_POST, true);
        $sign = @json_decode(curl_exec($ch) , true);
        curl_close($ch);
        switch ($sign["code"]) {
            case 200:
                $r = "签到成功";
                break;
            case 410004:
                $r = "今日已签到";
                break;
            case 401:
                $r = "请先登录";
                break;
            default:
                $r = "未知错误#" . $sign["code"];
        }
        return $r;
    }
    /*客户端签到接口*/
    public function nnsign()//本来不想更新的，看到6蕉想想还是更吧
    {
        //$this->relogin_times = 0;
        //retry:
        if ($this->get_date() > $this->signed_date) {
            $ch = $this -> scurl('http://api.new-app.acfun.cn/rest/app/user/signIn', $this->cookie);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array("access_token" => $this->access_token));
            curl_setopt($ch,CURLOPT_ENCODING , "gzip");
            curl_setopt($ch,CURLOPT_HTTPHEADER,["acPlatform: ANDROID_PHONE"]);
            $sign = json_decode(curl_exec($ch) , true);
            curl_close($ch);
            //if($sign["result"] == -401){//token失效的时候我也很绝望啊
            //    $a->mologin();
            //    $a->nsign();
            //    $this->relogin_times++;
            //    goto retry;
            //}
            $this->signed_date = $this->get_date();
            return $sign["msg"];
        } else {
            return "今日已签到";
        }
    }
    /*客户端检查签到接口*/
    public function c_sign()
    {
            $ch = $this -> scurl('http://api.new-app.acfun.cn/rest/app/user/hasSignedIn', $this->cookie);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array("access_token" => $this->access_token));
            curl_setopt($ch,CURLOPT_ENCODING , "gzip");
            curl_setopt($ch,CURLOPT_HTTPHEADER,["acPlatform: ANDROID_PHONE"]);
            $sign = json_decode(curl_exec($ch) , true);
            curl_close($ch);
            if($sign["result"] == 0){//token失效的时候我也很绝望啊
                return true;
            }else{
                return false;
            }
    }
    /*显示*/
    public function display() {
        echo $this->data["account"]["username"] . "\n - sign:{$this->nnsign()}\n - level:{$this->online()["level"]}\n - online:{$this->online()["duration"]}\n";
        if ($this->data["banana"]["number"] > 0 && $this->data["banana"]["number"] <= 5 && $this->data["banana"]["date"] == $this->get_date()) {
            echo " - ThrowBanana:今日已投蕉\n";
        } else {
            echo " - ThrowBanana:今日未投蕉\n";
        }
    }
    public function fp($path, $text) {
        if (file_put_contents($path, $text)) {
            return 0;
        } else {
            return -1;
        }
    }
}
$a = new Acsign();
/*这才是真正的开始*/
for ($a->x = 0; $a->x < count($data); $a->x++) {
    if ($data[$a->x]["status"] == 1) {
        $a->data = $data[$a->x];
        $a->signed_date = $a->data["signed_date"];
        $a->cookie = $a->data["account"]["cookie"];
        $a->access_token = $a -> data["account"]["access_token"];
        $a->uid = $a->data["banana"]["userid"];
        if ($a->c_sign() != true) {
            $a->login();
            $a->mologin();
        }
        if ($a->data["banana"]["number"] > 0 && $a->data["banana"]["number"] <= 5 && $a->data["banana"]["date"] < $a->get_date()) {
            $a->banana();
            $data[$a->x]["banana"]["date"] = $a->data["banana"]["date"];
        }
        $a->display();
        $data[$a->x]["account"]["cookie"] = $a->cookie;
        $data[$a->x]["account"]["access_token"] = $a->access_token;
        $data[$a->x]["banana"]["userid"] = $a->uid;
        $data[$a->x]["signed_date"] = $a->signed_date;
    }
}
$a->fp(dirname(__FILE__) . '/task.json', json_encode($data));

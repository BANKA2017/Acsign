<?php
/**
* Author: BANKA2017
* Version: 3.0
*/
header('Content-Type: text/txt; charset=UTF-8');
set_time_limit(0);
ignore_user_abort(true);
$data = json_decode(file_get_contents(dirname(__FILE__) . '/task.json'), 1);
class Acsign
{
    private function scurl($url, $cookie)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        return $ch;
    }
    /*获取日期*/
    public function getdate()
    {
        return date("y") . date("m") . date("d");
    }
    /*检查在线状态*/
    public function online()
    {
        $ch = $this->scurl('http://www.acfun.cn/online.aspx', $this->cookie);
        $content = curl_exec($ch);
        curl_close($ch);
        return json_decode($content, 1);
    }
    /*食我大蕉*/
    public function banana()
    {
        $ch = $this->scurl('http://www.acfun.cn/banana/throwBanana.aspx', $this->cookie);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.acfun.cn/v/ac' . $this->data["banana"]["acid"]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array("contentId" => $this->data["banana"]["acid"], "count" => $this->data["banana"]["number"], "userId" => $this->data["banana"]["userid"]));
        $content = curl_exec($ch);
        curl_close($ch);
        if (json_decode($content, 1)["success"] == true) {
            $this->data["banana"]["date"] = $this->getdate();
        } else {
            echo "失败原因:" . json_decode($content, 1)["result"] . "\n";
        }
    }
    /*登录获取cookie*/
    public function login()
    {
        $ch = $this->scurl('http://www.acfun.cn/login.aspx', '');
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.acfun.cn/login/');
        curl_setopt($ch, CURLOPT_POST, 1);
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
    /*移动端登录获取userid*/
    public function mologin()
    {
        $ch = $this->scurl('http://account.app.acfun.cn/api/account/signin/normal', '');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array("username" => $this->data["account"]["username"], "password" => $this->data["account"]["password"], "cid" => "ELSH6ruK0qva88DD"));
        $content = curl_exec($ch);
        curl_close($ch);
        $this->uid = json_decode($content, 1)["vdata"]["info"]["userid"];
    }
    /*签到*/
    public function sign()
    {
        $ch = $this->scurl('http://www.acfun.cn/webapi/record/actions/signin?channel=0', $this->cookie);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.acfun.cn/menber/');
        curl_setopt($ch, CURLOPT_POST, 1);
        $sign = json_decode(curl_exec($ch), 1);
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
    /*显示*/
    public function display()
    {
        echo $this->data["account"]["username"] . "\n - sign:{$this->sign()}\n - level:{$this->online()["level"]}\n - online:{$this->online()["duration"]}\n";
        if ($this->data["banana"]["number"] > 0 && $this->data["banana"]["number"] <= 5 && $this->data["banana"]["date"] == $this->getdate()) {
            echo " - ThrowBanana:今日已投蕉\n";
        } else {
            echo " - ThrowBanana:今日未投蕉\n";
        }
    }
    public function fp($path, $text)
    {
        if (file_put_contents($path, $text)) {
            return 0;
        } else {
            return -1;
        }
    }
    /*全局刷新*/
    public function refresh()
    {
        for ($this->x = 0; $this->x < count($this->datas); $this->x++) {
            $this->data = $this->datas[$this->x];
            $this->login();
            $this->mologin();
            $this->datas[$this->x]["account"]["cookie"] = $this->cookie;
            $this->datas[$this->x]["banana"]["userid"] = $this->uid;
        }
        return $this->datas;
    }
}
$a = new Acsign();
/*如果要投蕉必须执行以获取静态id，不投蕉可不执行*/
if (@$argv[1] == "refresh" || @$_GET["m"] == "refresh") {
    $a->datas = $data;
    $a->refresh();
    $a->fp(dirname(__FILE__) . '/task.json', json_encode($a->datas));
    die("system:刷新完成\n");
}
/*这才是真正的开始*/
for ($a->x = 0; $a->x < count($data); $a->x++) {
    if ($data[$a->x]["status"] == 1) {
        $a->data = $data[$a->x];
        $a->cookie = $a->data["account"]["cookie"];
        //$a->banana = $a->data["banana"];
        if ($a->online()["success"] != 1) {
            $a->login();
        }
        if ($a->data["banana"]["number"] > 0 && $a->data["banana"]["number"] <= 5 && $a->data["banana"]["date"] < $a->getdate()) {
            $a->banana();
            $data[$a->x]["banana"]["date"] = $a->data["banana"]["date"];
        }
        $a->display();
        $data[$a->x]["account"]["cookie"] = $a->cookie;
    }
}
$a->fp(dirname(__FILE__) . '/task.json', json_encode($data));

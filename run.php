<?php
/**
* Author： BANKA2017
* Version: 2.0
*/
set_time_limit(0);
ignore_user_abort(true);
require dirname(__FILE__) . '/settings.php';

$data = ["username" => 'YOUR_EMAIL_OR_PHONE_NUMBER', "password" => 'YOUR_PASSWORD'];//填写邮箱/手机号码，密码

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
    /*检查在线状态*/
    public function online()
    {
        $ch = $this->scurl('http://www.acfun.cn/online.aspx', $this->cookie);
        $online = json_decode(curl_exec($ch), 1);
        curl_close($ch);
        if ($online["success"] != 1) {
            return array("success" => 0, "level" => "", "duration" => "");
        } else {
            return $online;
        }
    }
    /*登录获取cookie*/
    public function login()
    {
        $ch = $this->scurl('http://www.acfun.cn/login.aspx', '');
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.acfun.cn/login/');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data);
        $content = curl_exec($ch);
        curl_close($ch);
        $cookie = '';
        preg_match_all('/Set-Cookie:(.*;)/iU', $content, $str);
        foreach ($str[1] as $key) {
            if (strpos($key, 'deleted') == false) {
                $cookie .= $key;
            }
        }
        $this->cookie = $cookie;
        return $cookie;
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
                return "签到成功";
                break;
            case 410004:
                return "今日已签到";
                break;
            default:
                return "未知错误#" . $sign["code"];
                break;
        }
    }
    public function display()
    {
        header('Content-Type: text/txt; charset=UTF-8');
        echo "===============================\n>sign:{$this->sign()}\n>level:" . $this->online()["level"] . "\n>online:" . $this->online()["duration"] . "\n===============================\n";
    }
    public function fp($path, $text)
    {
        if (file_put_contents($path, $text))
            return 0;
         else 
            return -1;
    }
}
$a = new Acsign($cookie, $data);
$a->cookie = $cookie;
$a->data = $data;
if ($a->online()["success"] != 1) {
    $a->fp(dirname(__FILE__) . '/settings.php', '<?php' . "\r\n" . '$cookie=' . "'" . $a->login() . "';\n");
    $a->display();
} else 
    $a->display();

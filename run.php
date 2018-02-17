<?php
/**
* Author: BANKA2017
* Version: 2.0
*/
header('Content-Type: text/txt; charset=UTF-8');
set_time_limit(0);
ignore_user_abort(true);
require dirname(__FILE__) . '/settings.php';
require dirname(__FILE__) . '/account.php';
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
        return json_decode(curl_exec($ch), 1);
        curl_close($ch);
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
            $cookie .= $key;
        }
        $this->cookie = $cookie;
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
            case 401:
                return "请先登录";
                break;
            default:
                return "未知错误#" . $sign["code"];
        }
    }
    public function display()
    {
        echo "第个{$this->x}账号\n>sign:{$this->sign()}\n>level:{$this->online()["level"]}\n>online:{$this->online()["duration"]}\n===============================\n";
    }
    public function fp($path, $text)
    {
        if (file_put_contents($path, $text)) {
            return 0;
        } else {
            return -1;
        }
    }
}
$cookie = json_decode($cookie, 1);
echo "===============================\n";
$a = new Acsign();
for ($a->x = 0; $a->x < count($data); $a->x++) {
    $a->cookie = @$cookie[$a->x];
    $a->data = $data[$a->x];
    if ($a->online()["success"] != 1) {
        $a->login();
    }
    $a->display();
    $cookie[$a->x] = $a->cookie;
}
$a->fp(dirname(__FILE__) . '/settings.php', '<?php' . "\n" . '$cookie=' . "'" . json_encode($cookie) . "';\n");

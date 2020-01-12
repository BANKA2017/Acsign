# Acsign

acfun签到脚本

## 文本配置（使用task.json）

请在**task.json**中填写`account`的内容`username`（账号、邮箱、手机号码）及`password`（密码），若不需要使用该配置请将`status`修改为`0`后在
crontab添加

```bash
0 0 * * * php [YOUR_PATH]/run.php
```

其中`YOUR_PATH`为脚本所在目录（为了您的帐号安全，建议使用本方式时**task.json**文件不可被外界直接访问）

## CLI

使用命令

```bash
php run.php [YOUR_USERNAME] [YOUR_PASSWORD]
```

其中`[YOUR_USERNAME]`及`[YOUR_PASSWORD]`参考第一条

亦可设置环境变量

```yml
ACSIGN_USERNAME: "USERNAME|EMAIL|PHONE_NUMBER"
ACSIGN_PASSWORD: "PASSWORD"
```

后直接执行

```bash
php run.php
```

*已集成GitHub Actions脚本，可点击 **Use this template** 按钮后在GitHub **私有仓库** 使用（使用方法参考[hostloc-auto-get-points](https://github.com/xirikm/hostloc-auto-get-points)）

## Webhook

将文件传入主机后，请求

><http://example.com/run.php?username=[YOUR_USERNAME]&password=[YOUR_PASSWORD]>

或者POST（参数一致，不再列出）

其中`[YOUR_USERNAME]`及`[YOUR_PASSWORD]`参考第一条

## 环境要求

```txt
php 7.x
php-curl

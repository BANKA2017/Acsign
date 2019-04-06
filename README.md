# Acsign
acfun签到脚本

#### 文本数据库（使用task.json）
请在**task.json**中填写`account`的内容`username`(账号、邮箱、手机号码)及`password`(密码)，若不需要使用该配置请将`status`修改为`0`
crontab添加
> 0 0 * * * php [YOUR_PATH]/run.php

其中`YOUR_PATH`为脚本所在目录(为了您的帐号安全，建议使用本方式时**task.json**文件不可被外界直接访问)
#### CLI
请删除**task.json**文件，使用命令
>php run.php [YOUR_USERNAME] [YOUR_PASSWORD]

其中`[YOUR_USERNAME]`及`[YOUR_PASSWORD]`参考第一条

#### Webhook
将文件传入主机后，删除**task.json**文件，GET
>http://example.com/run.php?username=[YOUR_USERNAME]&password=[YOUR_PASSWORD]

或者POST(参数一致，不再列出)

其中`[YOUR_USERNAME]`及`[YOUR_PASSWORD]`参考第一条

### About
本次更新把全部无关的花哨功能都砍了，因为没有必要了，以前留着挂机只是为了拿到额外的2根香蕉，但是现在客户端签到得到的香蕉数量变成随机数，这个变化使香蕉贬值加速，所以只留下核心功能，这个脚本以后还会更新，但仅限不能用的时候或发现严重bug。
我们后会有期。
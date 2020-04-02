# isipinchina
这个项目用来判断一个ip是否属于中国境内的ip


downchinaip.sh负责下载ip地址，需要把它放到crond中定时运行
例如:
[root@blog conf]# crontab -l
30 0 * * * sh /data/web/think_cmd/chinaip/downchinaip.sh >> /data/logs/cronlogs/downchinaiplogs.log 2>&1

需要修改配置的程序:

1,downchinaip.sh


指定下载后保存到本地的ip地址段文件

ip_txt_path=/data/data/ipdata/china_ip.txt;

apnic官网的ip地址段下载url

ip_url='http://ftp.apnic.net/apnic/stats/apnic/delegated-apnic-latest';

二进制的php文件的路径

php_path=/usr/local/soft/php7/bin/php

putip2redis.php保存到的路径

script_path=/data/web/think_cmd/chinaip/putip2redis.php

2,putip2redis.php(负责解析ip地址，转成数字后保存到redis)

redis服务器的ip

define("REDIS_SERVER", "127.0.0.1");

redis服务器的port

define("REDIS_PORT", "6379");

下载后保存到本地的ip地址段文件,注意和downchinaip.sh中保持一致

define("IP_FILE", "/data/data/ipdata/china_ip.txt");

保存到redis中的key名字

define("IP_HASH_NAME", "china_ip_hash");


3,isipinchina.php(判断ip是否属于国内)

redis服务器的ip

define("REDIS_SERVER", "127.0.0.1");

redis服务器的port

define("REDIS_PORT", "6379");

保存到redis中的key名字

define("IP_HASH_NAME", "china_ip_hash");



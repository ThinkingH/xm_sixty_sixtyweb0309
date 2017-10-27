<?php
header('Content-Type:text/html;charset=utf-8');
//定义项目名
define('APP_NAME', 'Index');

//定义项目所在路径
define('APP_PATH', './Index/');

//定义可以跨域的域名
$allow_origin = array(
    'http://localhost:8080',
    'http://127.0.0.1:8080',
    'http://192.168.1.50:8080',
    'http://192.168.1.52:8080',
    'http://localhost/',
    'http://127.0.0.1',
    'http://192.168.1.50',
    'http://192.168.1.52',

);
define('ALLOW_ORIGIN_STR', json_encode($allow_origin));

//通讯配置参数定义
define('CONFIG_TXURL', 'http://114.215.222.75:8005/sixty/interface/sixtyinit.php');
//define('CONFIG_TXURL', 'http://192.168.1.51:8009/sixty/interface/sixtyinit.php');
define('CONFIG_VERSION', '100');
define('CONFIG_CKEY', 'fd5112f036eea77f23bac0bbbadbe592');
define('CONFIG_SYSTEM', 'PC');
define('CONFIG_SYSVERSION', '100');

define('ERROE_JSON', '{"error":"后台出错！"}');

//公共文件地址
define('STATIC_PATH', '');

//开启调试模式，建议新手开启
define('APP_DEBUG', true);

//加载入口文件
require './ThinkPHP/ThinkPHP.php';

?>

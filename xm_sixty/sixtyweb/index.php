<?php
	
	//自定义session前缀，主要用于系统区分
	define('HYSESSQZ','sixtyiii');
	
	//定义平台名称
	define('HY_SYSTEM_NAME','60秒视频');
	
	//平台名称后是否显示IP---true/false
	define('HY_SHOW_IP',true);
	
	//定义网站访问路径ip地址---外网
	define('HY_WAI_URLIP','http://127.0.0.1:8001/');
	
	//定义网站访问路径ip地址---内网
	define('HY_NEI_URLIP','http://127.0.0.1:8001/');
	
	//定义项目名
	define('APP_NAME','Index');
	
	//定义项目所在路径
	define('APP_PATH','./Index/');
	
	//开启调试模式，建议新手开启
	define('APP_DEBUG',true);
	
	//加载入口文件
	require './ThinkPHP/ThinkPHP.php';


?>

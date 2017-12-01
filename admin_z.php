<?php

	define('BASEDIR',dirname(__FILE__).'/');

	//自定义session前缀，主要用于系统区分
	define('HYSESSQZ','sixty_');
	
	//定义平台名称
	define('HY_SYSTEM_NAME','60秒视频后台');
	
	//平台名称后是否显示IP---true/false
	define('HY_SHOW_IP',true);
	
	//定义网站访问路径ip地址---外网
	define('HY_WAI_URLIP','http://127.0.0.1:8009/');
	
	//定义网站访问路径ip地址---内网
	define('HY_NEI_URLIP','http://127.0.0.1:8009/');
	
	//七牛bucket定义存储正式
//	$bucketarr = array(
//		'sixty-basic'      => 'http://oys7hzyf8.bkt.clouddn.com/',  //基础公共图片存放，循环展示图片，默认图片等公共静态资源图片
//		'sixty-user'       => 'http://oys7i4dcy.bkt.clouddn.com/',  //用户图片存放，头像，用户其他数据
//		'sixty-video'      => 'http://oys78eqga.bkt.clouddn.com/',  //视频存放
//		'sixty-videoimage' => 'http://oys7tcwkg.bkt.clouddn.com/',  //视频封面图片存放
//		'sixty-imgpinglun' => 'http://oys72yckt.bkt.clouddn.com/',  //带图片投稿评论存放
//		'sixty-jihemsg'    => 'http://oys7xme11.bkt.clouddn.com/',  //集合封面图片存放
//	);
//七牛bucket定义存储 测试用
$bucketarr = array(
    'sixty-basic'      => 'http://p05s45h9l.bkt.clouddn.com/',  //基础公共图片存放，循环展示图片，默认图片等公共静态资源图片
    'sixty-user'       => 'http://p05srrm5u.bkt.clouddn.com/',  //用户图片存放，头像，用户其他数据
    'sixty-video'      => 'http://p05sfdtdh.bkt.clouddn.com/',  //视频存放
    'sixty-videoimage' => 'http://p05samtwb.bkt.clouddn.com/',  //视频封面图片存放
    'sixty-imgpinglun' => 'http://p05syy7rg.bkt.clouddn.com/',  //带图片投稿评论存放
    'sixty-jihemsg'    => 'http://p05svs60z.bkt.clouddn.com/',  //集合封面图片存放

);
	$bucketstr = json_encode($bucketarr);
	define('QINIUBUCKETSTR',$bucketstr);
	
	//七牛封装处理通讯地址
	define('QINIUURL','http://127.0.0.1/hyqiniu/init/');
	
	//定义项目名
	define('APP_NAME','Adminz');
	
	//定义项目所在路径
	define('APP_PATH','./Adminz/');
	
	//开启调试模式，建议新手开启
	define('APP_DEBUG',true);



	//加载入口文件
	require './ThinkPHP/ThinkPHP.php';


?>

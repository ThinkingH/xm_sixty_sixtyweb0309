<?php

return array(

	//'配置项'=>'配置值'
	
	// 开启日志记录
	'LOG_RECORD' => true,
	'LOG_LEVEL'  =>'EMERG,ALERT,CRIT,ERR,WARN',
	
		
	'OUTPUT_ENCODE' => false,
	
	//检查文件大小写
	'APP_FILE_CASE' => true,
	
	
	'URL_MODEL'	=> 1,
	
	
	// 添加数据库配置信息
	'DB_TYPE'   => 'mysql', // 数据库类型
	
	
	'DB_HOST'   => '127.0.0.1', // 服务器地址
	
	'DB_NAME'   => 'sixtysecond', // 数据库名
	'DB_USER'   => 'root', // 用户名
	'DB_PWD'    => 'root', // 密码
	'DB_PORT'   => 3306, // 端口
	'DB_PREFIX' => '', // 数据库表前缀
	
	
	
	
// 	 'SHOW_RUN_TIME'    => true, // 运行时间显示
// 	 'SHOW_ADV_TIME'    => true, // 显示详细的运行时间
// 	 'SHOW_DB_TIMES'    => true, // 显示数据库查询和写入次数
// 	 'SHOW_CACHE_TIMES' => true, // 显示缓存操作次数
// 	 'SHOW_USE_MEM'     => true, // 显示内存开销
// 	 'SHOW_LOAD_FILE'   => true, // 显示加载文件数
// 	 'SHOW_FUN_TIMES'   => true, // 显示函数调用次数
	
);



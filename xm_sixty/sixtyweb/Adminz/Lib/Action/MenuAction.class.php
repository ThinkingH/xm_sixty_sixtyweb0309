<?php


class MenuAction extends Action {
	//菜单模板模块
	
	
	//定义各模块锁定级别
	private $lock_index = '97531';
	
	
	
	
	public function index() {
		
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		//判断用户是否登陆
		$this->loginjudgeshow($this->lock_index);
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		
		
		
		//-------------------------------------------
		//获取用户session标识，主要用于配合日志判断用户访问
		$userri   = session(HYSESSQZ.'username');
		$rootflag = session(HYSESSQZ.'rootflag'); //权限标识
		//-------------------------------------------
		
		
		
		//菜单链接数据存放数组
		//----------------------------------------------------------------------------------------------------
		$urlarr9 = array(
				
				array(
						'murl_name' => '用户状态',
						'curl_name' => array(
								array('f', '用户在线状态' ,    '/Taskrelease/userstate' , ),
						),
				),
				array(
						'murl_name' => '数据处理工具',
						'curl_name' => array(
								array('f', '省份去重求和统计' ,  '/Hytool/provincehebingsum' , ),
								array('f', '数据去重工具' ,  '/Hytool/dataunique' , ),
								array('f', '数据切割提取' ,  '/Hytool/dataexplode' , ),
						),
				),
				array(
						'murl_name' => '配置信息',
						'curl_name' => array(
								array('f', '运营商数据管理' ,   '/Operatorcode/index' , ),
								array('q', '--------------------' , '#' , ),
								
						),
				),
				array(
						'murl_name' => '网关数据',
						'curl_name' => array(
								array('f', '网关黑名单添加' ,   '/Operatordata/blackphoneadd' , ),
								array('f', '网关黑名单查询' ,   '/Operatordata/blackphoneselect' , ),
								array('f', '网关黑名单删除' ,   '/Operatordata/blackphonedelete' , ),
								array('q', '--------------------' , '#' , ),
								array('f', '网关白名单添加' ,   '/Operatordata/writephoneadd' , ),
								array('q', '--------------------' , '#' , ),
								array('f', '包月用户统计' ,    '/Operatordata/baoyuecount' , ),
								array('f', '包月用户浏览' ,    '/Operatordata/baoyueuser' , ),
								array('q', '--------------------' , '#' , ),
								array('f', '网关上下行统计' , '/Operatordata/shangxiaxingcount' , ),
								array('f', '手机号上下行记录' , '/Operatordata/shangxiaxingphonedata' , ),
								array('f', '网关对内同步日志' ,   '/Operatordata/operator_sendlog' , ),
								array('q', '--------------------' , '#' , ),
								array('f', '包月话单查看' ,   '/Operatordata/huadanchakan' , ),
								array('f', '包月话单统计' ,   '/Operatordata/huadantongji' , ),
								array('q', '--------------------' , '#' , ),
								array('f', '单条指定下行推送' ,   '/Operatordata/xiaxing_phonelinkid' , ),
								array('f', '反向订购退订操作' ,   '/Operatordata/fanxiang_servicecodemo' , ),
				
						),
				),
				array(
						'murl_name' => '数据迁移',
						'curl_name' => array(
								array('f', '通用数据表迁移' ,   '/Hydataweihu/index' , ),
						),
				),
				array(
						'murl_name' => '系统管理',
						'curl_name' => array(
								array('f', '编辑操作用户' ,   '/Root/editoruser' , ),
								array('f', '用户操作日志记录' , '/Root/caozuo_log' , ),
								array('f', '管理员操作文档' ,  '/Root/roottext' , ),
								array('f', '管理公告修改' ,   '/Root/gonggao' , ),
						),
				),
				array(
						'murl_name' => '用户操作',
						'curl_name' => array(
								array('f', '当前用户信息' ,  '/Main/index' , ),
								array('f', '用户密码修改' ,  '/Passwdrewrite/index' , ),
								array('t', '退出系统'    ,  '/Login/logout' , ),
						),
				),
		);
		
		
		//----------------------------------------------------------------------------------------------------
		$urlarr7 = array(
				
				array(
						'murl_name' => '用户状态',
						'curl_name' => array(
								array('f', '用户在线状态' ,    '/Taskrelease/userstate' , ),
						),
				),
				array(
						'murl_name' => '数据处理工具',
						'curl_name' => array(
								array('f', '省份去重求和统计' ,  '/Hytool/provincehebingsum' , ),
								array('f', '数据去重工具' ,  '/Hytool/dataunique' , ),
								array('f', '数据切割提取' ,  '/Hytool/dataexplode' , ),
						),
				),
				array(
						'murl_name' => '配置信息',
						'curl_name' => array(
								array('f', '运营商数据管理' ,   '/Operatorcode/index' , ),
								array('q', '--------------------' , '#' , ),
								
						),
				),
				array(
						'murl_name' => '网关数据',
						'curl_name' => array(
								array('f', '网关黑名单添加' ,   '/Operatordata/blackphoneadd' , ),
								array('f', '网关黑名单查询' ,   '/Operatordata/blackphoneselect' , ),
								array('f', '网关黑名单删除' ,   '/Operatordata/blackphonedelete' , ),
								array('q', '--------------------' , '#' , ),
								array('f', '网关白名单添加' ,   '/Operatordata/writephoneadd' , ),
								array('q', '--------------------' , '#' , ),
								array('f', '包月用户统计' ,    '/Operatordata/baoyuecount' , ),
								array('f', '包月用户浏览' ,    '/Operatordata/baoyueuser' , ),
								array('q', '--------------------' , '#' , ),
								array('f', '网关上下行统计' , '/Operatordata/shangxiaxingcount' , ),
								array('f', '手机号上下行记录' , '/Operatordata/shangxiaxingphonedata' , ),
								array('f', '网关对内同步日志' ,   '/Operatordata/operator_sendlog' , ),
								array('q', '--------------------' , '#' , ),
								array('f', '包月话单查看' ,   '/Operatordata/huadanchakan' , ),
								array('f', '包月话单统计' ,   '/Operatordata/huadantongji' , ),
								array('q', '--------------------' , '#' , ),
								array('f', '单条指定下行推送' ,   '/Operatordata/xiaxing_phonelinkid' , ),
								array('f', '反向订购退订操作' ,   '/Operatordata/fanxiang_servicecodemo' , ),
				
						),
				),
				array(
						'murl_name' => '数据迁移',
						'curl_name' => array(
								
						),
				),
				array(
						'murl_name' => '系统管理',
						'curl_name' => array(
								array('f', '编辑操作用户' ,   '/Root/editoruser' , ),
								array('f', '用户操作日志记录' , '/Root/caozuo_log' , ),
								array('f', '管理员操作文档' ,  '/Root/roottext' , ),
								array('f', '管理公告修改' ,   '/Root/gonggao' , ),
						),
				),
				array(
						'murl_name' => '用户操作',
						'curl_name' => array(
								array('f', '当前用户信息' ,  '/Main/index' , ),
								array('f', '用户密码修改' ,  '/Passwdrewrite/index' , ),
								array('t', '退出系统'    ,  '/Login/logout' , ),
						),
				),
				
		);
		
		
		//----------------------------------------------------------------------------------------------------
		$urlarr5 = array(
				array(
						'murl_name' => '数据处理工具',
						'curl_name' => array(
								array('f', '省份去重求和统计' ,  '/Hytool/provincehebingsum' , ),
								array('f', '数据去重工具' ,  '/Hytool/dataunique' , ),
								array('f', '数据切割提取' ,  '/Hytool/dataexplode' , ),
						),
				),
				array(
						'murl_name' => '配置信息',
						'curl_name' => array(
								array('f', '运营商数据管理' ,   '/Operatorcode/index' , ),
								array('q', '--------------------' , '#' , ),
								
						),
				),
				array(
						'murl_name' => '网关数据',
						'curl_name' => array(
								array('f', '网关黑名单添加' ,   '/Operatordata/blackphoneadd' , ),
								array('f', '网关黑名单查询' ,   '/Operatordata/blackphoneselect' , ),
								array('f', '网关黑名单删除' ,   '/Operatordata/blackphonedelete' , ),
								array('q', '--------------------' , '#' , ),
								array('f', '包月用户统计' ,    '/Operatordata/baoyuecount' , ),
								array('f', '包月用户浏览' ,    '/Operatordata/baoyueuser' , ),
								array('q', '--------------------' , '#' , ),
								array('f', '网关上下行统计' , '/Operatordata/shangxiaxingcount' , ),
								array('f', '手机号上下行记录' , '/Operatordata/shangxiaxingphonedata' , ),
								array('f', '网关对内同步日志' ,   '/Operatordata/operator_sendlog' , ),
								array('q', '--------------------' , '#' , ),
								array('f', '包月话单查看' ,   '/Operatordata/huadanchakan' , ),
								array('f', '包月话单统计' ,   '/Operatordata/huadantongji' , ),
								array('q', '--------------------' , '#' , ),
								array('f', '单条指定下行推送' ,   '/Operatordata/xiaxing_phonelinkid' , ),
								array('f', '反向订购退订操作' ,   '/Operatordata/fanxiang_servicecodemo' , ),
				
						),
				),
				array(
						'murl_name' => '用户操作',
						'curl_name' => array(
								array('f', '当前用户信息' ,  '/Main/index' , ),
								array('f', '用户密码修改' ,  '/Passwdrewrite/index' , ),
								array('t', '退出系统'    ,  '/Login/logout' , ),
						),
				),
				
		);
		//----------------------------------------------------------------------------------------------------
		$urlarr3 = array(
				array(
						'murl_name' => '数据处理工具',
						'curl_name' => array(
								array('f', '省份去重求和统计' ,  '/Hytool/provincehebingsum' , ),
								array('f', '数据去重工具' ,  '/Hytool/dataunique' , ),
						),
				),
				array(
						'murl_name' => '网关数据',
						'curl_name' => array(
								array('f', '网关黑名单添加' ,   '/Operatordata/blackphoneadd' , ),
								array('f', '网关黑名单查询' ,   '/Operatordata/blackphoneselect' , ),
								array('q', '--------------------' , '#' , ),
								array('f', '包月用户统计' ,    '/Operatordata/baoyuecount' , ),
								array('f', '包月用户浏览' ,    '/Operatordata/baoyueuser' , ),
								array('q', '--------------------' , '#' , ),
								array('f', '网关上下行统计' , '/Operatordata/shangxiaxingcount' , ),
								array('f', '手机号上下行记录' , '/Operatordata/shangxiaxingphonedata' , ),
								array('f', '网关对内同步日志' ,   '/Operatordata/operator_sendlog' , ),
								array('q', '--------------------' , '#' , ),
								array('f', '包月话单查看' ,   '/Operatordata/huadanchakan' , ),
								array('f', '包月话单统计' ,   '/Operatordata/huadantongji' , ),
								array('q', '--------------------' , '#' , ),
// 								array('f', '单条指定下行推送' ,   '/Operatordata/xiaxing_phonelinkid' , ),
								array('f', '反向订购退订操作' ,   '/Operatordata/fanxiang_servicecodemo' , ),
				
						),
				),
				array(
						'murl_name' => '用户操作',
						'curl_name' => array(
								array('f', '当前用户信息' ,  '/Main/index' , ),
								array('f', '用户密码修改' ,  '/Passwdrewrite/index' , ),
								array('t', '退出系统'    ,  '/Login/logout' , ),
						),
				),
				
		);
		
		
		//----------------------------------------------------------------------------------------------------
		$urlarr1 = array(
				array(
						'murl_name' => '数据处理工具',
						'curl_name' => array(
								array('f', '省份去重求和统计' ,  '/Hytool/provincehebingsum' , ),
								array('f', '数据去重工具' ,  '/Hytool/dataunique' , ),
						),
				),
				array(
						'murl_name' => '网关数据',
						'curl_name' => array(
								array('f', '网关黑名单查询' ,   '/Operatordata/blackphoneselect' , ),
								array('q', '--------------------' , '#' , ),
								array('f', '包月用户统计' ,    '/Operatordata/baoyuecount' , ),
								array('f', '包月用户浏览' ,    '/Operatordata/baoyueuser' , ),
								array('q', '--------------------' , '#' , ),
								array('f', '网关上下行统计' , '/Operatordata/shangxiaxingcount' , ),
								array('f', '手机号上下行记录' , '/Operatordata/shangxiaxingphonedata' , ),
								array('f', '网关对内同步日志' ,   '/Operatordata/operator_sendlog' , ),
								array('q', '--------------------' , '#' , ),
								array('f', '包月话单查看' ,   '/Operatordata/huadanchakan' , ),
								array('f', '包月话单统计' ,   '/Operatordata/huadantongji' , ),
								array('q', '--------------------' , '#' , ),
				
						),
				),
				array(
						'murl_name' => '用户操作',
						'curl_name' => array(
								array('f', '当前用户信息' ,  '/Main/index' , ),
								array('f', '用户密码修改' ,  '/Passwdrewrite/index' , ),
								array('t', '退出系统'    ,  '/Login/logout' , ),
						),
				),
				
				
		);
		
		
		//----------------------------------------------------------------------------------------------------
		
		$urlarr = array();
		if($rootflag==9) {
			$urlarr = $urlarr9;
		}else if($rootflag==7) {
			$urlarr = $urlarr7;
		}else if($rootflag==5) {
			$urlarr = $urlarr5;
		}else if($rootflag==3) {
			$urlarr = $urlarr3;
		}else if($rootflag==1) {
			$urlarr = $urlarr1;
		}else {
			
		}
		
		//----------------------------------------------------------------------------------
		//循环遍历导航数据数组，对数据进行进一步处理操作
		foreach($urlarr as $keyu => $valu) {
			//判断链接字符长度，并对较短的链接字符补充空格到指定长度
			$len = strlen($urlarr[$keyu]['murl_name']);
			if($len<=15) {
				$urlarr[$keyu]['murl_name'] = $urlarr[$keyu]['murl_name'].str_repeat('&nbsp;', (15-$len));
			}
			
			//遍历所有子url链接，为所有链接添加对应用户标识，补充较短的链接长度
			foreach($urlarr[$keyu]['curl_name'] as $keyc => $valc) {
				
				$lencc = strlen($urlarr[$keyu]['curl_name'][$keyc][1]);
				if($lencc<=15) {
					$urlarr[$keyu]['curl_name'][$keyc][1] = $urlarr[$keyu]['curl_name'][$keyc][1].str_repeat('&nbsp;', (15-$len));
				}
				
				if($urlarr[$keyu]['curl_name'][$keyc][0]=='u') {
					//远程url
					$urlarr[$keyu]['curl_name'][$keyc][2] = $urlarr[$keyu]['curl_name'][$keyc][2];
						
					
				}else {
					$urlarr[$keyu]['curl_name'][$keyc][2] = __APP__ . $urlarr[$keyu]['curl_name'][$keyc][2] . '?userxr='.$userri;
					
					
				}
				
				
				
			}
			
		}
		//----------------------------------------------------------------------------------
		
		
		
		
		$this -> assign('urlarr',$urlarr);
		
		
		
		
		// 输出模板
		$this->display();
		
		
		
		
	}
	
	
	
	
	//判断用户是否登陆的前台展现封装模块
	private function loginjudgeshow($lock_key) {
	
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		//判断用户是否登陆
		$lockarr = loginjudge($lock_key);
		if($lockarr['grade']=='C') {
			//通过
		}else if($lockarr['grade']=='B') {
			exit($lockarr['exitmsg']);
		}else if($lockarr['grade']=='A') {
			echo $lockarr['alertmsg'];
			$this -> error($lockarr['errormsg'],'__APP__/Login/index');
		}else {
			exit('系统错误，为确保系统安全，禁止登入系统');
		}
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	
	
	}
	
	
	
}
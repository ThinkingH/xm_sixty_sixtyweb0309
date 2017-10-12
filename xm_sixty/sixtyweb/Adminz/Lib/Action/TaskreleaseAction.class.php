<?php


class TaskreleaseAction extends Action {
	//任务发布系统
	
	
	//定义各模块锁定级别
	private $lock_typeshow    = '97531';
	private $lock_userstate   = '97531';
	
	
	
	
	public function typeshow() {
		
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		//判断用户是否登陆
		$this->loginjudgeshow($this->lock_typeshow);
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		
		$Model = new Model();
		
		$sql_typedata = "select * from basic_type order by id";
		$list_typedata = $Model->query($sql_typedata);
		
		$this->assign('typedata',$list_typedata);
		
		// 输出模板
		$this->display();
		
		
	}
	
	
	
	//用户登陆记录模块
	public function userstate() {
		
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		//判断用户是否登陆
		$this->loginjudgeshow($this->lock_userstate);
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		
		
		$sql = "select xingming,lastLoginIp,lastLoginTime,
				loginflag,null as state
				from user_admin
				where lockflag=1
				and username<>'root'
				order by xingming";
		
		$Model = new Model();
		$list = $Model -> query($sql);
		
		foreach($list as $key => $val) {
			if($list[$key]['loginflag']==1) {
				$list[$key]['state'] = '<font color="red">已经下线</font>';
			}else {
				$time = time();
				$lasttime = strtotime($list[$key]['lastLoginTime']);
				$spantime = $time - $lasttime;
				if($spantime>660) {
					$list[$key]['state'] = '<font color="red">可能已经下线</font>';
				}else {
					$list[$key]['state'] = '<font color="blue">可能在线</font>';
				}
			}
			
			if(trim($list[$key]['qq'])!='') {
				
				$list[$key]['qq'] = '<a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin='.$list[$key]['qq'].'&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:'.$list[$key]['qq'].':51" alt="点击这里给我发消息" title="点击这里给 ['.$list[$key]['xingming'].'] 发消息"/></a>';
				
			}
			
		}
		
		
		$this -> assign('list',$list);
		unset($list,$sql);
		
		// 输出模板
		$this->display();
		
		printf(' memory usage: %01.2f MB', memory_get_usage()/1024/1024);
		
	}
	
	
	
	
	//生成url拼接参数
	private function createurl($get) {
	
		$yuurl = '?';
		foreach($get as $keyg => $valg) {
			if(substr($keyg,0,6)!='submit' && $keyg!='_URL_' && $keyg!='sel_submit') {
				if(is_array($valg)) {
					foreach($valg as $valcc) {
						$yuurl .= $keyg.'[]='.urlencode($valcc).'&';
					}
				
				}else {
					$yuurl .= $keyg.'='.urlencode($valg).'&';
				}
			}
		}
		$yuurl = rtrim($yuurl,'&');
	
		if(strlen($yuurl)>1) {
			return $yuurl;
		}else {
			return '';
		}
	
	
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
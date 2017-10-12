<?php


class MainAction extends Action {
	
	
	//定义各模块锁定级别
	private $lock_index = '97531';
	
	
	public function index() {
		
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		//判断用户是否登陆
		$this->loginjudgeshow($this->lock_index);
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		
		
		$username = session(HYSESSQZ.'username');
		$xingming = session(HYSESSQZ.'xingming');
		
		
		//为变量赋值
		$this -> assign('username',$username);
		$this -> assign('xingming',$xingming);
		
		$Model = new Model();
		$list = $Model -> table('user_admin') -> field('lastLoginTime,lastLoginIp') -> where("username='".$username."'") -> find();
		
		$lastLoginTime = substr($list['lastLoginTime'],0,19);
		$lastLoginIp   = $list['lastLoginIp'];
		
		$this -> assign('lastLoginTime',$lastLoginTime);
		$this -> assign('lastLoginIp',$lastLoginIp);
		
		
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
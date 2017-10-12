<?php

class HeaderAction extends Action {
	//头部模板模块
	
	
	//定义各模块锁定级别
	private $lock_index = '97531';
	
	
	public function index() {
		
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		//判断用户是否登陆
		$this->loginjudgeshow($this->lock_index);
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		
		
		$username = session(HYSESSQZ.'username');
		$xingming = session(HYSESSQZ.'xingming');
		
		$this->assign('username',$username);
		$this->assign('xingming',$xingming);
		
		
		//-------------------------------------------
		//添加用户标识到apache日志记录
		$userri  = session(HYSESSQZ.'username');
		$this -> assign('ruser','userxr='.$userri);
		//-------------------------------------------
		
		
		//输出模板
		$this->display();
		
		
	}
	
	
	
	
	public function ajax() {
		
		
		$Model = new Model();
		
		
		//输出公告内容
		if($this->_post('gonggao')=='gonggao') {
		
			$gonggao = file_get_contents('./Public/mailto/gonggao_m');
			if(empty($gonggao)) {
				$gonggao = '无对应公告';
			}
			echo $gonggao;
			
			//更新用户阅读状态
			$this->userread('ok');
			
		}
		
		
		//输出公告名称
		if($this->_post('gonggao_show')=='gonggao_show') {
			
			//获取用户的阅读状态
			$isread = $this->userread('yes');
			$carr = 'blue';
		
			$gtime = file_get_contents('./Public/mailto/gonggao_t');
			if(empty($gtime)) {
				$gtime = '无';
			}
			$gonggao_show = '<a href="#"><font style="color:'.$carr.'" id="welcome" yuflag="'.$isread.'" ><b>管理公告--'.$gtime.'</b></font></a>';
			
			if($gtime!='无') {
				echo $gonggao_show;
			}
			
		}
		
		
		//更新用户在线状态，强制用户密码修改
		if($this->_post('updatetimeip')=='updatetimeip') {
			
			//更新用户在线时间和ip
			$username = session(HYSESSQZ.'username');
			//检测到的用户数据更新到数据库
			$data['lastLoginTime'] = date('Y-m-d H:i:s');
			$data['lastLoginIp']   = get_client_ip(); //获取用户ip地址
			$data['loginflag']     = array('exp','loginflag+1'); //获取用户ip地址
			//更新数据
			$Model -> table('user_admin') -> where("username='".$username."'")->save($data);
			
			
			$xnow = time();
			$xtt  = strtotime('2015-04-01');
			if($xnow>$xtt) {
				//强制性密码修改启动：
				$this -> resetpasswd();
			}
			
		}
		
		
		
	}
	
	
	
	
	
	public function tanchukuang() {
		
		//获取当前用户名
		$username = session(HYSESSQZ.'username');
		
		
		if($username!='') {
			
			//判断对应文件是否存在
			
			$tongzhifile = './Public/mailto/tanchukuang_'.$username;
			
			if(file_exists($tongzhifile)) {
				
				$tanchuneirong = file_get_contents($tongzhifile);
				
				if(trim($tanchuneirong)!='') {
					echo $tanchuneirong;
				}
				
				
			}
			
			
		}
		
		
		
	}
	
	
	
	
	
	
	
	//用户是否读取了管理员公告判断封装函数
	private function userread($ff='no') {
		//规定$ff变量有两种状态，no代表只读取读取列表，并判断当前用户是否度去过此管理公告
		//yes除以上操作外，还会执行用户浏览的更新操作
		
		//获取此文件的浏览者数据
		$lluser = file_get_contents('./Public/mailto/gonggao_p');
		
		$readflag = 'no';
		$duser = session(HYSESSQZ.'username');
		if(!empty($lluser)) {
			//取出所有浏览过的用户，并方入对应数组，查找是否有当前用户
			$lluserarr = explode('|', $lluser);
			foreach($lluserarr as $val) {
				if($val!='' && $val==$duser) {
					$readflag = 'ok';
				}
			}
			
		}
		
		if($ff=='ok') {
			//如果用户之前未读过管理员公告，更新用户读取状态
			if($readflag=='no') {
				$temparr = array();
				foreach($lluserarr as $val) {
					if(!empty($val)) {
						array_push($temparr,$val);
					}
				}
				array_push($temparr,$duser);
				$tempstr = implode('|', $temparr);
				file_put_contents('./Public/mailto/gonggao_p', $tempstr);
			}
		}
		
		
		return $readflag;
		
	}
	
	
	
	
	//强制所有用户修改密码
	private function resetpasswd(){
		
		$resetflag = session(HYSESSQZ.'resetflag');
		if($resetflag==999){
			//手动修改数据库所有数值为999
			echo '本系统出于安全性考虑要求所有用户必须修改一次密码，可以修改为原密码，但是必须作出修改。';
			echo '单击确认按钮立即跳转到密码修改页面。';
			
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
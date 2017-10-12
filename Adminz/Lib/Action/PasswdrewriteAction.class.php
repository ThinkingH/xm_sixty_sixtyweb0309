<?php


class PasswdrewriteAction extends Action {
	//密码修改模块
	
	
	//定义各模块锁定级别
	private $lock_index         = '97531';
	private $lock_rewritepasswd = '97531';
	
	
	public function index() {
		
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		//判断用户是否登陆
		$this->loginjudgeshow($this->lock_index);
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		
		
		$username = session(HYSESSQZ.'username');
		$xingming = session(HYSESSQZ.'xingming');
			
		$this -> assign('username',$username);
		$this -> assign('xingming',$xingming);
		
		// 输出模板
		$this->display();
		
		printf(' memory usage: %01.2f MB', memory_get_usage()/1024/1024);
		
	}
	
	
	
	
	public function rewritepasswd() {

		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		//判断用户是否登陆
		$this->loginjudgeshow($this->lock_rewritepasswd);
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		
		//从session获取用户名
		$username  = session(HYSESSQZ.'username');
		
		$submit    = trim($this->_post('submit'));
		$oldpasswd = trim($this->_post('oldpasswd'));
		$passwd    = trim($this->_post('passwd'));
		$repasswd  = trim($this->_post('repasswd'));
		
		if($submit=='') {
			$this->error('非法操作');
			
		}else {
			
			if(''==$oldpasswd) {
				echo "<script>alert('原密码不能为空！');history.go(-1);</script>";
				$this -> error('原密码不能为空！');
			}
			if(''==$passwd) {
				echo "<script>alert('新密码不能为空！');history.go(-1);</script>";
				$this -> error('新密码不能为空！');
			}
			if(''==$repasswd) {
				echo "<script>alert('重复新密码不能为空！');history.go(-1);</script>";
				$this -> error('重复新密码不能为空！');
			}
			//判断两次输入密码是否一致
			if($passwd!=$repasswd) {
				echo "<script>alert('两次密码不一致！');history.go(-1);</script>";
				$this -> error('两次密码不一致！');
			}
			
			
			//对原密码进行MD5
			$oldpasswd_md5 = md5($oldpasswd);
			
			//对新密码进行MD5
			$passwd_md5 = md5($passwd);
			
			
			//数据库初始化
			$Model = new Model();
			
			
			
			//查询原密码是否输入正确
			$list = $Model->table('user_admin')->where("username='".$username."'")->find();
			
			
			if(count($list)<=0) {
				echo "<script>alert('用户名不存在，系统错误！');history.go(-1);</script>";
				$this->error('用户名不存在，系统错误！');
				
			}else {
				
				if($oldpasswd_md5!=$list['passwd']) {
					echo "<script>alert('原密码错误！');history.go(-1);</script>";
					$this -> error('原密码错误！');
					
				}else {
					//修改密码
					
					//将新密码写入数组变量
					$pas = array();
					$pas['passwd']    = $passwd_md5;
					$pas['resetflag'] = 9999;
					
					$ret = $Model->table('user_admin')->where("username='".$username."'")->save($pas);
					
					$templogs = $Model->getlastsql();
					hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);
					
					
					if($ret) {
						
						//重新设置session
						session(HYSESSQZ.'username',$username);
						session(HYSESSQZ.'password',$passwd_md5);
						session(HYSESSQZ.'resetflag',9999);
						
						echo "<script>alert('密码修改成功!');window.location.href='".__APP__."/Passwdrewrite/index';</script>";
						$this ->success('密码修改成功!','__APP__/Passwdrewrite/index');
							
					}else {
						echo "<script>alert('密码修改失败，系统错误!');history.go(-1);</script>";
						$this -> error('密码修改失败，系统错误!');
					}
					
					
				}
				
				
			}
			
			
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
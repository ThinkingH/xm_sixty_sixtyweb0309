<?php


class LoginAction extends Action {
	
	public function index() {

		$username  = session(HYSESSQZ.'username');
		$password  = session(HYSESSQZ.'password');
		$rootflag  = session(HYSESSQZ.'rootflag');
		$lockflag  = session(HYSESSQZ.'lockflag');
		$xingming  = session(HYSESSQZ.'xingming');
		
		if($username!='' && $password!='') {
			//说明用户已经登陆了，session中保存有用户的信息
			
			//判断用户是否被禁用
			if($rootflag==9) {
				//超级用户不会被禁止，无条件通过
			}else {
				//对于非超级用户需要判断其是否被禁用
				if($lockflag==-1) {
					//清空session，强制用户下线
					session(HYSESSQZ.'username',null);
					session(HYSESSQZ.'password',null);
					session(HYSESSQZ.'xingming',null); //用户姓名
					session(HYSESSQZ.'rootflag',null); //权限标识
					session(HYSESSQZ.'lockflag',null); //禁用标识
					session(HYSESSQZ.'resetflag',null); //强制用户重置密码标识
					session(HYSESSQZ.'lastLoginTime',null);
					session(HYSESSQZ.'lastLoginIp',null);
					
						
					//提示用户账号被禁用
					echo "<script>alert('您的账号被禁用，请联系超级管理员解锁后再次进行登陆！');top.location.href='".__APP__."' </script>";
					$this -> error('您的账号被禁用，请联系超级管理员解锁后再次进行登陆！','__APP__/Login/index');
				}
			}
			
			//提示用户不能二次登陆
			echo "<script>alert('您已经登录，不能再次进行登陆!');top.location.href='".__APP__."/Index' </script>";
			$this -> error('您已经登录，不能再次进行登陆!','__APP__/?userxr='.$username);
			
		}
		
		
		// 输出模板
		$this->display();
		
		
	}
	
	
	
	
	//判断用户提交的登录数据
	public function login_x() {
		
		if($this->_post('submit')!=null) {
			$username = '';
			$passwd   = '';
			
			if($this->_post('username')!=null) {
				$username = strtolower($this -> _post('username'));
			}else {
				$this -> error('用户名不能为空！','__APP__/Login/index');
			}
			if($this->_post('passwd')!=null) {
				$passwd = $this -> _post('passwd');
			}else {
				$this -> error('密码不能为空！','__APP__/Login/index');
			}
			
			//连接数据库，查询用户名所对应的数据
			$Model = new Model();
			
			$list = $Model -> table('user_admin') -> where("username='".$username."'")->find();
			
			$passwd_md5 = md5($passwd);
			if(!empty($list)) {
				if($passwd_md5==$list['passwd']) {
					//设置session
					session(HYSESSQZ.'username',$username);
					session(HYSESSQZ.'password',$passwd_md5);
					session(HYSESSQZ.'xingming',$list['xingming']); //用户姓名
					session(HYSESSQZ.'rootflag',$list['rootflag']); //权限标识
					session(HYSESSQZ.'lockflag',$list['lockflag']); //禁用标识
					session(HYSESSQZ.'resetflag',$list['resetflag']); //强制用户重置密码标识
					
					
					//将上次登录时间写入session中
					session(HYSESSQZ.'lastLoginTime',substr($list['lastLoginTime'],0,19));
					//将上次登录IP地址写入session中
					session(HYSESSQZ.'lastLoginIp',$list['lastLoginIp']);
					
					//------------------------------------------------------------
					//判断用户是否被禁用
					if($list['rootflag']==9) {
						//超级用户不会被禁止，无条件通过
					}else {
						//对于非超级用户需要判断其是否被禁用
						if($list['lockflag']==-1) {
							//清空session，强制用户下线
							session(HYSESSQZ.'username',null);
							session(HYSESSQZ.'password',null);
							session(HYSESSQZ.'xingming',null); //用户姓名
							session(HYSESSQZ.'rootflag',null); //权限标识
							session(HYSESSQZ.'lockflag',null); //禁用标识
							session(HYSESSQZ.'resetflag',null); //强制用户重置密码标识
							session(HYSESSQZ.'lastLoginTime',null);
							session(HYSESSQZ.'lastLoginIp',null);
							
								
							//提示用户账号被禁用
							echo "<script>alert('您的账号被禁用，请联系超级管理员解锁后再次进行登陆！');top.location.href='".__APP__."' </script>";
							$this -> error('您的账号被禁用，请联系超级管理员解锁后再次进行登陆！','__APP__/Login/index');
						}
					}
					//------------------------------------------------------------
					
					
					//更新本次登录时间和ip地址
					//获取本次登录时间
					$time = time();
					$updateTime = date('Y-m-d H:i:s',$time);
					
					//将更新后的登陆标识更新到数据库
					$data['lastLoginTime'] = $updateTime;
					$data['lastLoginIp']   = get_client_ip(); //获取用户ip地址
					$data['loginflag']     = $list['loginflag'] + 1; //更新登录标识
					
					//更新数据
					$Model = new Model();
					$Model -> table('user_admin') -> where("username='".$username."'")->save($data);
					
					//登陆成功，直接跳转到主页面
					$this->redirect("__APP__/Index/index?userxr=".$username);
					
				}else {
					$this -> error('密码错误！','__APP__/Login/index');
				}
				
			}else {
				$this -> error('用户名不存在！','__APP__/Login/index');
			}
			
		}
		
	}
	
	
	
	//判断用户提交的退出数据
	public function logout() {
		
		$username = session(HYSESSQZ.'username');
		$password = session(HYSESSQZ.'password');
		
		if($username!='' && $password!='') {
			//说明用户已经登陆了，session中保存有用户的信息
			//清空当前session
			session(HYSESSQZ.'username',null);
			session(HYSESSQZ.'password',null);
			session(HYSESSQZ.'xingming',null); //用户姓名
			session(HYSESSQZ.'rootflag',null); //权限标识
			session(HYSESSQZ.'lockflag',null); //禁用标识
			session(HYSESSQZ.'resetflag',null); //强制用户重置密码标识
			session(HYSESSQZ.'lastLoginTime',null);
			session(HYSESSQZ.'lastLoginIp',null);
			
			
			$logoutsql = "update user_admin set loginflag=1 where username='".$username."'";
			$Model = new Model();
			$Model ->execute($logoutsql);
			
			echo "<script>alert('退出成功！');window.location.href='".__APP__."/Login/index';</script>";
			$this -> success('退出成功','__APP__/Login/index');
			
		}else {
			echo "<script>alert('您尚未登录，无法退出!');window.location.href='".__APP__."/Login/index';</script>";
			$this -> error('您尚未登录，无法退出!','__APP__/Login/index');
		}
		
		
	}
	
	
	
}


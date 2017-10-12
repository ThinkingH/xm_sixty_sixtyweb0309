<?php

class HytoolAction extends Action {
	//hy工具集
	
	//定义各模块锁定级别
	private $lock_provincehebingsum  = '97531';
	private $lock_dataunique         = '97531';
	private $lock_dataexplode        = '975';
	
	
	
	
	//省份归类统计函数
	public function provincehebingsum() {
		
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		//判断用户是否登陆
		$this->loginjudgeshow($this->lock_provincehebingsum);
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		
		if($this->_post('submit')!='') {
			
			//执行数据处理
			//要求第一列为省份，第二列及以后为数字，全部以制表符分隔
			$data = $this->_post('data');
			$this->assign('data',$data);
			
			$data_hang = explode("\n",$data);
			
			$provincearr = array();
			
			foreach($data_hang as $valh) {
				$valh = trim($valh);
				$valhc = explode("\t",$valh);
				//只处理4列
				$lie0 = isset($valhc[0])?trim($valhc[0]):'';
				$lie1 = isset($valhc[1])?trim($valhc[1]):'';
				$lie2 = isset($valhc[2])?trim($valhc[2]):'';
				$lie3 = isset($valhc[3])?trim($valhc[3]):'';
				$lie4 = isset($valhc[4])?trim($valhc[4]):'';
				$lie5 = isset($valhc[5])?trim($valhc[5]):'';
				$lie6 = isset($valhc[6])?trim($valhc[6]):'';
				
				if($lie0!='') {
					//$provincearr[$lie0][0] = $lie0;
					$provincearr[$lie0][1] += $lie1;
					$provincearr[$lie0][2] += $lie2;
					$provincearr[$lie0][3] += $lie3;
					$provincearr[$lie0][4] += $lie4;
					$provincearr[$lie0][5] += $lie5;
					$provincearr[$lie0][6] += $lie6;
				}
				
				
			}
			
			$showdata = '';
			
			foreach($provincearr as $keyp => $valp) {
				$showdata .= $keyp."\t".$valp[1]."\t".$valp[2]."\t".$valp[3]."\t".$valp[4]."\t".$valp[5]."\t".$valp[6]."\n";
			}
			
			$this->assign('showdata',$showdata);
			
		}
		
		
		
		
		$this->display(); // 输出模板
		
		printf(' memory usage: %01.2f MB', memory_get_usage()/1024/1024);
		
	}
	
	
	
	//数据去重工具
	public function dataunique() {
		
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		//判断用户是否登陆
		$this->loginjudgeshow($this->lock_dataunique);
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		
		
		$edit_submit = $this->_post('edit_submit');
		$thedata     = $this->_post('thedata');
		
		
		$this->assign('thedata',$thedata);
		
		
		if(''!=$edit_submit) {
			
			$newdataarr = array();
			
			$data_hang = explode("\n",$thedata);
			
			foreach($data_hang as $valdh) {
				$valdh = trim($valdh);
				if(''!=$valdh) {
					if(isset($newdataarr[$valdh])) {
						//已经存在，跳过
					}else {
						$newdataarr[$valdh] = 1;
					}
				}
			}
			
			$count_hang = count($data_hang);
			$count_unique = count($newdataarr);
			
			$echo_newstr = implode("\n",array_keys($newdataarr));
			
			$this->assign('echo_newstr',$echo_newstr);
			$this->assign('count_hang',$count_hang);
			$this->assign('count_unique',$count_unique);
			
			
			
		}
		
		
		
		
		$this->display(); // 输出模板
		
		printf(' memory usage: %01.2f MB', memory_get_usage()/1024/1024);
		
	}
	
	
	
	
	

	//数据分割处理
	public function dataexplode() {
	
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		//判断用户是否登陆
		$this->loginjudgeshow($this->lock_dataexplode);
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	
		//执行数据处理
		$data     = $this->_post('data');
		$hangfengefu = $this->_post('hangfengefu'); //行分隔符号
		$fengefu  = $this->_post('fengefu'); //列分隔符号
		$quchulie = $this->_post('quchulie'); //取出列,用|分隔
		$lianjiefu  = $this->_post('lianjiefu'); //连接符号
			
		if($hangfengefu=='') {
			$hangfengefu = '\n';
		}
		if($fengefu=='') {
			$fengefu = '\t';
		}
		if($lianjiefu=='') {
			$lianjiefu = '\t';
		}
	
	
		$this->assign('data',$data);
		$this->assign('hangfengefu',$hangfengefu);
		$this->assign('fengefu',$fengefu);
		$this->assign('quchulie',$quchulie);
		$this->assign('lianjiefu',$lianjiefu);
	
	
	
		if($this->_post('submit')!='') {
				
			//数据库初始化
			$Model = new Model();
				
				
				
			//字符替换
			if($lianjiefu=='\t') {
				$lianjiefu = "\t";
			}
			if($lianjiefu=='\t\t') {
				$lianjiefu = "\t\t";
			}
				
			if($hangfengefu=='\t') {
				$hangfengefu = "\t";
			}
			if($hangfengefu=='\t\t') {
				$hangfengefu = "\t\t";
			}
				
			if($hangfengefu=='\n') {
				$hangfengefu = "\n";
			}
			if($hangfengefu=='\n\n') {
				$hangfengefu = "\n\n";
			}
				
			if($fengefu=='\t') {
				$fengefu = "\t";
			}
			if($fengefu=='\t\t') {
				$fengefu = "\t\t";
			}
				
				
				
			$mainarray = array();
			$mainechostring = '';
				
				
			$quchuliearr = array();
			$quchulietemp = explode('|',$quchulie);
			foreach($quchulietemp as $valqt) {
				$valqt = trim($valqt);
				if(is_numeric($valqt)) {
					array_push($quchuliearr,$valqt);
				}
			}
				
				
			$data_hang = explode($hangfengefu,$data);
	
			foreach($data_hang as $keylie => $vallie) {
	
				$data_liearr = explode($fengefu,$vallie);
	
				foreach($data_liearr as $keydl => $valdl) {
					$valdl = trim($valdl);
					$mainarray[$keylie][$keydl] = $valdl;
				}
	
			}
				
				
				
			foreach($mainarray as $valm) {
				if(count($quchuliearr)>0) {
						
					foreach($quchuliearr as $valqq) {
						if(isset($valm[$valqq])) {
							$mainechostring .= $valm[$valqq].$lianjiefu;
						}else {
							$mainechostring .= $lianjiefu;
						}
					}
						
						
				}else {
					foreach($valm as $valmm) {
						$mainechostring .= $valmm.$lianjiefu;
					}
						
						
						
				}
	
				$mainechostring .= "\n";
	
			}
				
				
				
				
			$this->assign('mainechostring',$mainechostring);
				
		}
	
	
	
	
		$this->display(); // 输出模板
	
		printf(' memory usage: %01.2f MB', memory_get_usage()/1024/1024);
	
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
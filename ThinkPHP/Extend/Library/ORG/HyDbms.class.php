<?php


/**
 * 基于微软mssql的数据库操作类
 * 
 * @author yu
 *
 */
class HyDbms {
	
	
	protected $mssql; //mysqli实例对象  
	protected $rs; //结果集存放变量  
	protected $fetch_mode = MSSQL_ASSOC; //获取模式  
	protected $proc; //存储过程调用数据存放变量
	protected $mshostport; 
	protected $msusername; 
	protected $mspassword; 
	protected $msdbname; //选择数据库名称
	
	
	
	
	//构造函数：主要用来返回一个mysqli对象  
	public function  __construct() {
		
		$this->mshostport = 'sql-server-2008';
		$this->msusername = 'sa';
		$this->mspassword = 'Xyxksql@)!$';
		$this->msdbname   = 'sms';
		
		$this->mssql = mssql_connect($this->mshostport, $this->msusername, $this->mspassword);
		
		if(!mssql_select_db($this->msdbname,$this->mssql)){
			exit("SQL ERROR:".$this->msdbname);
		}
		
		
	}
	
	
	//析构函数：主要用来释放结果集和关闭数据库连接  
	public function __destruct() {
		if(!empty($this->rs)){
			$this->free();
		}
		
		$this->close();
	}
	
	
	//释放结果集所占资源  
	protected function free() {
		@mssql_free_result($this->rs);
		//@$this->rs->free();
	}

	//关闭数据库连接  
	protected function close() {
		//$this->mysqli->close();
		mssql_close($this->mssql);
	}
	
	
	//更改数据库  
	protected function change_dbname($dnname='') {
		
		if(trim($dnname)=='') {
			return false;
		}else {
			
			$this->msdbname = $dnname;
			if(mssql_select_db($db_database)) {
				return true;
			}else {
				return false;
			}
			
		}
		
	}
	
	
	//执行查询语句
	protected function query($sql) {
		
		if(trim($sql)=='') {
			return false;
		}else {
			$this->rs = mssql_query($sql);
			if($this->rs) {
				return true;
			}else {
				//echo 'error_sql:'.$sql;
				return false;
			}
			
		}
		
	}
	
	
	//获取结果集
	protected function fetch() {
		return mssql_fetch_array($this->rs, $this->fetch_mode);
	}
	
	
	//返回所有的结果集  
	public function get_all($sql, $fetch_mode = MSSQL_ASSOC ) {  
		$t = $this->query($sql);
		if($t===true) {
			$all_rows = array();
			$this->fetch_mode = $fetch_mode;
			while($rows = $this->fetch()) {
				$all_rows[] = $rows;
			}
			$this->free();
			return $all_rows;
		}else {
			return array();
		}
		
	}
	
	
	//数据插入更新sql语句执行函数
	public function execute($sql) {
		if(trim($sql) == '') {
			//如果提交的语句为空，直接返回false
			return false;
		}else {
			//执行sql语句
			if( mssql_query($sql) ) {
				return mssql_rows_affected();
			}else {
				return false;
			}
			
		}
		
	}
	
	
	public function produceexec($proname='',$arr=array()) {
		
		$finalprodata = array();
		
		$proname = trim($proname);
		if($proname=='') {
			return false;
		}else {
			
			$this->proc = mssql_init($proname);  //初始化一个存储过程
			if(count($arr)>0) {
				foreach($arr as $val) {
					//mssql_bind($this->proc, "@info",$info,SQLVARCHAR,TRUE,FALSE,30);//例子
					mssql_bind(
						$this->proc,
						isset($val[0])?$val[0]:'', //@user_name为参数名
						isset($val[1])?$val[1]:'', //$user_name为参数对应的php变量
						isset($val[2])?$val[2]:'', //SQLVARCHAR表明该参数类型为sqlserver的varchar类型
						isset($val[3])?$val[3]:'', //第一个false表示该参数不是输出参数，即该参数是输入参数
						isset($val[4])?$val[4]:'', //第二个false表示该参数不允许为null
						isset($val[5])?$val[5]:''  //最后的30表示该变量的长度为30
					); //为存储过程添加一个输出参数
					 //为存储过程添加一个输出参数
							
				}
			}
			
			mssql_execute($this->proc); //执行该存储过程
			
			foreach($arr as $keys => $vals) {
				$finalprodata[$keys] = $vals[1];
			}
			
			return $finalprodata;
			
		}
		
		
	}
	
	
	
	
	
	
}

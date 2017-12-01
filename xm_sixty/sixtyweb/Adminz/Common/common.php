<?php


//图片检测，判断是否为图片
function func_isImage($filename){
	$types = '.gif|.jpeg|.png|.bmp|.jpg';//定义检查的图片类型
	if(file_exists($filename)){
		$info = getimagesize($filename);
		$ext = image_type_to_extension($info['2']);
		return stripos($types,$ext);
	}else{
		return false;
	}
}


//七牛图片上传
function upload_qiniu($bucket,$filepath,$savename,$rewrite='no'){
	$qiniurl = QINIUURL.'hy_upload.php';
	$dataarr = array(
			'bucket'   => $bucket,
			'filepath' => $filepath,
			'savename' => $savename,
			'rewrite' => $rewrite,
	);
	$datastr = hy_urlcreate($dataarr);
	//模拟数据访问
	$res = hy_vpost($qiniurl,$datastr,$header=array(),$timeout=5000 );
//	print_r($res);
	if(''!=$res && substr($res,0,1)!='#'){
		$truepath = json_decode($res, true);
		//$arr = unserialize(BUCKETSTR);//获取七牛访问链接
		$filename= $truepath['key'];
		return $filename;
	}else{
//	    var_dump($res);
		return false;
	}
}


//七牛图片删除
function delete_qiniu($bucket,$delname){
	$qiniurl = QINIUURL.'hy_delete.php';
	$dataarr = array(
			'delbucket'   => $bucket,
			'delname' => $delname,
	);
	$datastr = hy_urlcreate($dataarr);
	//模拟数据访问
	$res = hy_vpost($qiniurl,$datastr,$header=array(),$timeout=5000 );
	if(''!=$res && substr($res,0,1)!='#'){
		return true;
	}else{
		return false;
	}
}


//自动获取文件类型并返回新的后缀文件路径名称
function hy_getfiletype($filepathname='') {
	if(''==$filepathname || !file_exists($filepathname)) {
		return false;
	}else {
		$path     = dirname($filepathname).'/';
		$basename = pathinfo($filepathname, PATHINFO_FILENAME);
		$extname  = pathinfo($filepathname, PATHINFO_EXTENSION);
		
		$file = fopen($filepathname, "rb");
		$bin = fread($file, 2); //只读2字节
		fclose($file);
		$strInfo = @unpack("C2chars", $bin);
		$typeCode = intval($strInfo['chars1'].$strInfo['chars2']);
		$fileType = 'jpg';
		switch ($typeCode)
		{
			case 7790:
				$fileType = 'exe';
				break;
			case 7784:
				$fileType = 'midi';
				break;
			case 8297:
				$fileType = 'rar';
				break;
			case 8075:
				$fileType = 'zip';
				break;
			case 255216:
				$fileType = 'jpg';
				break;
			case 7173:
				$fileType = 'gif';
				break;
			case 6677:
				$fileType = 'bmp';
				break;
			case 13780:
				$fileType = 'png';
				break;
			default:
				$fileType = 'jpg'; //unknown
		}
		$newpathname = $path.$basename.'.'.$fileType;
		
		return $newpathname;
	}
	
}


/**
 * 将图片以自定义品质，另存为JPG格式,将会删除源图片
 * @param string $filepathname 图片名称，包含路径
 * @param int    $quality  图片品质，0到100，默认90，100为最高品质
 */
function hy_resave2jpg($filepathname='', $quality = 100) {
	if(''==$filepathname) {
		return false;
	}else {
		
		$path     = dirname($filepathname).'/';
		$basename = pathinfo($filepathname, PATHINFO_FILENAME);
		$extname  = pathinfo($filepathname, PATHINFO_EXTENSION);
		$im = null;
		switch($extname) {
			case 'jpg':
				$im = imagecreatefromjpeg($filepathname);
				break;
			case 'png':
				$im = imagecreatefrompng($filepathname);
				break;
			case 'gif':
				$im = imagecreatefromgif($filepathname);
				break;
		}
		$newpathname = $path.$basename.'.jpg';
		$r = imagejpeg($im, $newpathname, $quality);
		imagedestroy($im);
		if($r) {
			if(in_array($extname, array('png','gif'))) {
				@unlink($filepathname);
			}
			
			return $newpathname;
		}else {
			return false;
		}
		
	}
	
}



//返回七牛云图片读取地址
function hy_qiniuimgurl($bucketname='',$imgname='',$width='',$height='',$canshu=true) {
	$qiniubucketarr = json_decode(QINIUBUCKETSTR,true);
	$returnimgurl = '';
	if(''==$imgname) {
		$bucketurl = isset($qiniubucketarr['sixty-basic'])?$qiniubucketarr['sixty-basic']:'';
		if(''==$bucketurl) {
			return '';
		}else {
			$returnimgurl = $bucketurl.'notfounddata.png';
			if($canshu) {
				$returnimgurl .= '?imageView2/1';
				if($width!='') {
					$returnimgurl .= '/w/'.$width;
				}
				if($height!='') {
					$returnimgurl .= '/h/'.$height;
				}
				$returnimgurl .= '/q/75';
				$returnimgurl .= '|imageslim';
			}
			return $returnimgurl;
		}
		
	}else {
		
		$bucketurl = isset($qiniubucketarr[$bucketname])?$qiniubucketarr[$bucketname]:'';
		if($bucketurl!='') {
			if(substr($imgname,0,4)=='http') {
				$returnimgurl = $imgname;
			}else {
				$returnimgurl = $bucketurl.$imgname;
			}
			if($canshu) {
				$returnimgurl .= '?imageView2/1';
				if($width!='') {
					$returnimgurl .= '/w/'.$width;
				}
				if($height!='') {
					$returnimgurl .= '/h/'.$height;
				}
				$returnimgurl .= '/q/75';
				$returnimgurl .= '|imageslim';
			}
		}
		return $returnimgurl;
	}
	
}

//七牛云bucket存储内容获取,返回视频地址
function hy_qiniubucketurl($bucketname='',$dataname='') {
	$returnurl = '';
	$qiniubucketarr = json_decode(QINIUBUCKETSTR,true);
	$bucketurl = isset($qiniubucketarr[$bucketname])?$qiniubucketarr[$bucketname]:'';
	if($bucketurl!='') {
		if(substr($dataname,0,4)=='http') {
			$returnurl = $dataname;
		}else {
			$returnurl = $bucketurl.$dataname;
		}
	}
	return $returnurl;
	
}



//封装url参数拼接
function hy_urlcreate( $urlarr=array()) {
	$baseurl = '';
	if( is_array($urlarr) && count($urlarr)>0 ) {
		foreach($urlarr as $key => $val) {
			$baseurl .= $key.'='.urlencode($val).'&';
		}
		$baseurl = substr($baseurl,0,(strlen($baseurl)-1));
	}
	return $baseurl;
}



function hy_vget( $url, $timeout=5000, $header=array(), $useragent='' ) {

	if( !function_exists('curl_init') ){
		return false;
	}

	if(substr($url,0,7)!='http://') {
		return 'url_error';
	}

	//对传递的header数组进行整理
	$headerArr = array();
	foreach( $header as $n => $v ) {
		$headerArr[] = $n.':'.$v;
	}

	$curl = curl_init(); // 启动一个CURL会话

	curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
	curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
	curl_setopt($curl, CURLOPT_NOBODY, 0); // 显示返回的body区域内容

	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在

	if(trim($useragent)!='') {
		//当传递useragent参数时，模拟用户使用的浏览器
		curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
	}

	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
	curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer

	curl_setopt($curl, CURLOPT_NOSIGNAL,1); //注意，毫秒超时一定要设置这个
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS,$timeout); //设置连接等待毫秒数
	curl_setopt($curl, CURLOPT_TIMEOUT_MS,$timeout); //设置超时毫秒数

	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); // 获取的信息以文件流的形式返回
	if(count($headerArr)>0) {
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArr);//设置HTTP头
	}
	$content  = curl_exec($curl); //返回结果
	$httpcode = curl_getinfo($curl,CURLINFO_HTTP_CODE); //页面状态码
	$run_time = (curl_getinfo($curl,CURLINFO_TOTAL_TIME)*1000); //所用毫秒数
	$errorno  = curl_errno($curl);

	//关闭curl
	curl_close($curl);

// 	//定义return数组变量
// 	$retarr = array();
// 	$retarr['content']  = $content;
// 	$retarr['httpcode'] = $httpcode;
// 	$retarr['run_time'] = $run_time;
// 	$retarr['errorno']  = $errorno;

	return $content;

}




function hy_vpost( $url, $data, $timeout=5000, $header=array(), $useragent='' ) {

	if( ! function_exists('curl_init') ){
		return FALSE;
	}

	if(substr($url,0,7)!='http://') {
		return 'url_error';
	}

	$headerArr = array();
	foreach( $header as $n => $v ) {
		$headerArr[] = $n.':'.$v;
	}

	$curl = curl_init(); // 启动一个CURL会话

	curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
	curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
	curl_setopt($curl, CURLOPT_NOBODY, 0); // 显示返回的body区域内容

	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 2); // 对认证证书来源的检查
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在

	if(trim($useragent)!='') {
		//当传递useragent参数时，模拟用户使用的浏览器
		curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
	}

	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
	curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer

	curl_setopt($curl, CURLOPT_NOSIGNAL,1); //注意，毫秒超时一定要设置这个
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS,$timeout); //设置连接等待毫秒数
	curl_setopt($curl, CURLOPT_TIMEOUT_MS,$timeout); //设置超时毫秒数

	curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); // 获取的信息以文件流的形式返回
	if(count($headerArr)>0) {
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArr);//设置HTTP头
	}

	$content  = curl_exec($curl); //返回结果
	$httpcode = curl_getinfo($curl,CURLINFO_HTTP_CODE); //页面状态码
	$run_time = (curl_getinfo($curl,CURLINFO_TOTAL_TIME)*1000); //所用毫秒数
	$errorno  = curl_errno($curl);

	//关闭curl
	curl_close($curl);

// 	//定义return数组变量
 	$retarr = array();
 	$retarr['content']  = $content;
 	$retarr['httpcode'] = $httpcode;
 	$retarr['run_time'] = $run_time;
 	$retarr['errorno']  = $errorno;

	return $content;

}





function hy_province_config() {
	
	$arr = array (
			'上海','云南','北京','吉林',
			'四川','天津','宁夏','安徽','山东',
			'山西','广东','广西','新疆','江苏',
			'江西','河北','河南','浙江','海南',
			'湖北','湖南','甘肃','福建','西藏',
			'贵州','辽宁','重庆','陕西','青海',
			'内蒙古','黑龙江',
	);
	
	return $arr;
	
}



function hy_caozuo_logwrite($logstr='',$ttype='unknown') {
	//ttype，操作模块标识名称
	$logstr = trim($logstr);
	if($logstr=='') {
		return false;
	}else {
		$logstr = base64_encode($logstr);
		$who = session(HYSESSQZ.'username');
		
		$sql_insertlog   = "insert into caozuo_log 
							(who,ttype,content,create_datetime) values(
							'".$who."','".$ttype."','".$logstr."','".date('Y-m-d H:i:s')."')";
		$Model = new Model();
		$Model->execute($sql_insertlog);
		
		return true;
		
	}
	
	
	
}



//密码强度判断函数
function yu_passwdStrength($str) {
	
	$score = 0;
	if(preg_match("/[0-9]+/",$str)) {
		$score ++;
	}
	if(preg_match("/[0-9]{3,}/",$str)) {
		$score ++;
	}
	if(preg_match("/[a-z]+/",$str)) {
		$score ++;
	}
	if(preg_match("/[a-z]{3,}/",$str)) {
		$score ++;
	}
	if(preg_match("/[A-Z]+/",$str)) {
		$score ++;
	}
	if(preg_match("/[A-Z]{3,}/",$str)) {
		$score ++;
	}
	if(preg_match("/[_|\-|+|=|*|!|@|#|$|%|^|&|(|)|.|,|:|;]+/",$str)) {
		$score += 2;
	}
	if(preg_match("/[_|\-|+|=|*|!|@|#|$|%|^|&|(|)|.|,|:|;]{3,}/",$str)) {
		$score ++ ;
	}
	if(strlen($str) >= 10) {
		$score ++;
	}
	
	return $score;
	
}


//登陆判断封装模块
function loginjudge($ylock = '135') {

	//定义返回数据的数组
	$lockarr['grade']    = 'B';  //A代表alert+error  B代表exit  C代表成功
	$lockarr['errormsg'] = '';   //error信息
	$lockarr['alertmsg'] = '';   //alert信息
	$lockarr['exitmsg']  = '';   //exit信息
	
	
	//判断用户是否登录------start------
	$username  = session(HYSESSQZ.'username');
	$password  = session(HYSESSQZ.'password');
	$rootflag  = session(HYSESSQZ.'rootflag'); //用户权限标识字段1，3，5，7，9---root>9
	$lockflag  = session(HYSESSQZ.'lockflag');
	
	if($username!='' && $password!='') {
		//说明用户已经登陆了，session中保存有用户的信息
		
		if($username=='root') {
			//root用户不做权限判断，直接通过
			$lockarr['grade'] = 'C';
			
		}else {
			
			//首先判断该账号是否被禁用
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
				$lockarr['grade']    = 'A';
				$lockarr['alertmsg'] = "<script>alert('您的账号被禁用，请联系超级管理员解锁后再次进行登陆！');top.location.href='".__APP__."' </script>";
				$lockarr['errormsg'] = '您的账号被禁用，请联系超级管理员解锁后再次进行登陆！';
				
			}else {
				
				//start权限===============================================
				//判断用户是否有进入此页面的权限
				
				//------------------------------------------------------------------------------
				//判断用户的标识字段是否为大于0小于9的数字
				if(is_numeric($rootflag) && $rootflag>0 && $rootflag<10) {
					//权限标识判断通过
					
				}else {
					//提示权限标识不正确
					$lockarr['grade']    = 'A';
					$lockarr['alertmsg'] = "<script>alert('您的账号权限判断存在错误，系统将强制下线，请重新登录！');top.location.href='".__APP__."' </script>";
					$lockarr['errormsg'] = '您的账号权限判断存在错误，系统将强制下线，请重新登录！';
					
					//清空session,禁止非法标识用户登录系统
					session(HYSESSQZ.'username',null);
					session(HYSESSQZ.'password',null);
					session(HYSESSQZ.'xingming',null); //用户姓名
					session(HYSESSQZ.'rootflag',null); //权限标识
					session(HYSESSQZ.'lockflag',null); //禁用标识
					session(HYSESSQZ.'resetflag',null); //强制用户重置密码标识
					session(HYSESSQZ.'lastLoginTime',null);
					session(HYSESSQZ.'lastLoginIp',null);
					
				}
				
				
				//------------------------------------------------------------------------------
				//将权限字符串切割成数组
				$quanxianarr = str_split($ylock);
				
				if(in_array($rootflag,$quanxianarr)) {
					//通过页面权限判断
					$lockarr['grade'] = 'C';
					
				}else {
					//没有对应页面操作权限
					$lockarr['grade'] = 'B';
					$lockarr['exitmsg'] = '<h1><br/>&nbsp;&nbsp;&nbsp;权限错误，您没有进入该页面的权限&nbsp;&nbsp;&nbsp;'.date('Y-m-d H:i:s').'</h1>';
					
				}
				
				//end权限===============================================
				
				
			}
			
			
		}
		
		
	}else {
		//用户非法进入页面
		$lockarr['grade']    = 'A';
		$lockarr['alertmsg'] = "<script>alert('您尚未登陆，请登录后再进入此页面！');top.location.href='".__APP__."' </script>";
		$lockarr['errormsg'] = '您尚未登陆不能进入此页面!';
		
	}
	
	
	//返回判断执行结果数组
	return $lockarr;
	
	
}

//get参数传入url数据
function func_baseurlcreate($get=array()) {
    if(!is_array($get) || count($get)<=0) {
        return '';
    }else {
        foreach($get as $keyg => $valg) {
            if(substr($keyg,0,6)!='submit' && $keyg!='_URL_') {
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

        return '?'.$yuurl;

    }
}

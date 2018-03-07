<?php

/*** 极光推送  通知推送*****/
class JiPush{
	protected $app_key = 'd1b2b47af477612cd4d12048';        //待发送的应用程序(appKey)，只能填一个。
	protected $master_secret ='f1421b8da3749230b38171e0';    //主密码
	protected $url = "https://api.jpush.cn/v3/push";      //推送的地址
	protected $timing_url = "https://api.jpush.cn/v3/schedules";      //定时推送的地址
	protected $get_cid_url = "https://api.jpush.cn/v3/push/cid?count=1&type=schedule";      //定时推送的地址
    protected $schedule_list = "https://api.jpush.cn/v3/schedules?page=1";
    protected $schedule_one = "https://api.jpush.cn/v3/schedules/";
    protected $schedule_del = "https://api.jpush.cn/v3/schedules/";
    protected $schedule_edit = "https://api.jpush.cn/v3/schedules/";


	//若实例化的时候传入相应的值则按新的相应值进行
	public function __construct($app_key=null, $master_secret=null,$url=null) {
		if ($app_key) $this->app_key = $app_key;
		if ($master_secret) $this->master_secret = $master_secret;
		if ($url) $this->url = $url;
	}
	
	
	
	//极光推送的类
	//文档见：http://docs.jpush.cn/display/dev/Push-API-v3


	/*  $receiver 接收者的信息
	 all 字符串 该产品下面的所有用户. 对app_key下的所有用户推送消息
	 tag(20个)Array标签组(并集): tag=>array('昆明','北京','曲靖','上海');
	 tag_and(20个)Array标签组(交集): tag_and=>array('广州','女');
	 alias(1000)Array别名(并集): alias=>array('93d78b73611d886a74*****88497f501','606d05090896228f66ae10d1*****310');
	 registration_id(1000)注册ID设备标识(并集): registration_id=>array('20effc071de0b45c1a**********2824746e1ff2001bd80308a467d800bed39e');
	 */
	//$content 推送的内容。
	//$m_type 推送附加字段的类型(可不填) http,tips,chat....
	//$m_txt 推送附加字段的类型对应的内容(可不填) 可能是url,可能是一段文字。
	//$m_time 保存离线时间的秒数默认为一天(可不传)单位为秒
	public function push($receiver='all',$content='',$m_type='',$m_txt='',$m_time='86400'){
		$base64=base64_encode("$this->app_key:$this->master_secret");
		$header=array("Authorization:Basic $base64","Content-Type:application/json");
		$data = array();
		$data['platform'] = 'all';          //目标用户终端手机的平台类型android,ios,winphone
		$data['audience'] = $receiver;      //目标用户

		$data['notification'] = array(
				
				//统一的模式--标准模式
				//"alert"=>$content,
				//安卓自定义
				"android"=>array(
						"alert"=>$content,
						"title"=>"",
						"builder_id"=>1,
						"extras"=>array("action"=>$m_type, "value"=>$m_txt,)
				),
				//ios的自定义
				"ios"=>array(
						"alert"=>$content,
						"badge"=>"1",
						"sound"=>"default",
						"extras"=>array("action"=>$m_type, "value"=>$m_txt)
				)
		);

		//苹果自定义---为了弹出值方便调测
		$data['message'] = array(
				"msg_content"=>$content,
				"extras"=>array("action"=>$m_type, "value"=>$m_txt)
		);

		//附加选项
		$data['options'] = array(
				"sendno"=>time(),
				"time_to_live"=> intval($m_time), //保存离线时间的秒数默认为一天
				"apns_production"=>true, //布尔类型   指定 APNS 通知发送环境：0开发环境，1生产环境。或者传递false和true
		);
		$param = json_encode($data);
		$res = $this->push_curl($param,$header);
//return $param;exit;

		if($res){       //得到返回值--成功已否后面判断
			return $res;
		}else{          //未得到返回值--返回失败
			return false;
		}
	}




	//提交定时推送任务
	public function timing_push($receiver='all',$content='',$m_type='',$m_txt='',$m_time='86400',$timing=''){
		$base64=base64_encode("$this->app_key:$this->master_secret");
		$header=array("Authorization:Basic $base64","Content-Type:application/json","Accept:application/json");
		$cid_header=array("Authorization:Basic $base64","Content-Type:text/plain","Accept:application/json");


		$data = array();
		$data['platform'] = 'all';          //目标用户终端手机的平台类型android,ios,winphone

        if($receiver != 'all'){
            $receive = array();//别名
            $receive['alias'] = $receiver;//别名
        }else{
            $receive = 'all';
        }
		$data['audience'] = $receive;      //目标用户

		$data['notification'] = array(
				
				//统一的模式--标准模式
				//"alert"=>$content,
				//安卓自定义
				"android"=>array(
						"alert"=>$content,
						"title"=>"",
						"builder_id"=>1,
						"extras"=>array("action"=>$m_type, "value"=>$m_txt,)
				),
				//ios的自定义
				"ios"=>array(
						"alert"=>$content,
						"badge"=>"1",
						"sound"=>"default",
						"extras"=>array("action"=>$m_type, "value"=>$m_txt)
				)
		);

		//苹果自定义---为了弹出值方便调测
		$data['message'] = array(
				"msg_content"=>$content,
				"extras"=>array("action"=>$m_type, "value"=>$m_txt)
		);

		//附加选项
		$data['options'] = array(
				"sendno"=>time(),
				"time_to_live"=> intval($m_time), //保存离线时间的秒数默认为一天
				"apns_production"=>true, //布尔类型   指定 APNS 通知发送环境：0开发环境，1生产环境。或者传递false和true
		);


        $cid = $this->get_cid_curl($cid_header);
        $rep_data = array(
            'cid' => $cid,
            'name' => date('Ymd_His'),
            'enabled' => true,
            'trigger' => array(
                'single' => array(
                    'time' => $timing,
                ),
            ),
            'push' => $data,
        );

		$param = json_encode($rep_data);
		$res = $this->push_curl($param,$header,2);


		if($res){       //得到返回值--成功已否后面判断
			return $res;
		}else{          //未得到返回值--返回失败
			return false;
		}
	}





	//推送的Curl方法
	public function push_curl($param="",$header="",$type='') {
		if (empty($param)) { return false; }
		if($type == 2){
            $postUrl = $this->timing_url;
        }else{
            $postUrl = $this->url;
        }

		$curlPost = $param;
		$ch = curl_init();                                      //初始化curl
		curl_setopt($ch, CURLOPT_URL,$postUrl);                 //抓取指定网页
		curl_setopt($ch, CURLOPT_HEADER, 0);                    //设置header
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);            //要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_POST, 1);                      //post提交方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
		curl_setopt($ch, CURLOPT_HTTPHEADER,$header);           // 增加 HTTP Header（头）里的字段
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);        // 终止从服务端进行验证
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		$data = curl_exec($ch);                                 //运行curl
		curl_close($ch);
		return $data;
	}


    //定时推送的get 获取cid的 curl方法
    public function get_cid_curl($header="") {

        $postUrl = $this->get_cid_url;

        $curl = curl_init(); // 启动一个CURL会话
//        curl_setopt($curl, CURLOPT_URL, $postUrl);
////        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_URL, $postUrl);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        $tmpInfo = curl_exec($curl);     //返回api的json对象
        //关闭URL请求
        curl_close($curl);
//        var_dump(getallheaders());    //返回json对象
        $headArr = explode("\r\n", $tmpInfo);    //返回json对象

        $cidlist = $headArr[10];

        $rel = '/\["(.*)"\]/i';
        preg_match($rel,$cidlist,$cid_arr);
        $cid =$cid_arr[1];
//        var_dump($a[1]);die;
//        die;
        return $cid;

    }

    //获取定时推送列表
    //仅在开发中用于获取列表
    public function schedule_list() {
        $base64=base64_encode("$this->app_key:$this->master_secret");
        $header=array("Authorization:Basic $base64","Content-Type:application/json","Accept:application/json");
        $postUrl = $this->schedule_list;

        $curl = curl_init(); // 启动一个CURL会话
//        curl_setopt($curl, CURLOPT_URL, $postUrl);
////        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_URL, $postUrl);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        $tmpInfo = curl_exec($curl);     //返回api的json对象
        //关闭URL请求
        curl_close($curl);
//        var_dump(getallheaders());    //返回json对象
        $headArr = explode("\r\n", $tmpInfo);    //返回json对象

        $cidlist = $headArr[7];
        $cidlist = json_decode($cidlist,true);

        return $cidlist;

    }

    //获取一条定时推送信息
    public function schedule_one($schedule_id='') {
        $base64=base64_encode("$this->app_key:$this->master_secret");
        $header=array("Authorization:Basic $base64","Content-Type:application/json","Accept:application/json");
        $postUrl = $this->schedule_one.$schedule_id;

        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_URL, $postUrl);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        $tmpInfo = curl_exec($curl);     //返回api的json对象
        //关闭URL请求
        curl_close($curl);
//        var_dump(getallheaders());    //返回json对象
        $headArr = explode("\r\n", $tmpInfo);    //返回json对象
//
        $cidlist = $headArr[7];

        $cidlist = json_decode($cidlist,true);

        return $cidlist;

    }

    //删除定时推送
    public function schedule_del($schedule_id='') {
        $base64=base64_encode("$this->app_key:$this->master_secret");
        $header=array("Authorization:Basic $base64","Content-Type:application/json","Accept:application/json");

        $postUrl = $this->schedule_del.$schedule_id;

        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $postUrl);//设置要提交的url地址
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


//        curl_setopt($curl, CURLOPT_POSTFIELDS,'{'.$schedule_id.'}');
        $tmpInfo = curl_exec($curl);     //返回api的json对象
        //关闭URL请求
        curl_close($curl);
//        var_dump(getallheaders());    //返回json对象
        $headArr = explode("\r\n", $tmpInfo);    //返回json对象


        return $headArr[7];

    }


    //修改定时推送任务
    public function timing_edit($receiver='all',$content='',$m_type='',$m_txt='',$m_time='86400',$timing='',$schedule_id=''){

        $data = array();
        $data['platform'] = 'all';          //目标用户终端手机的平台类型android,ios,winphone
        if($receiver != 'all'){
            $receive = array();//别名
            $receive['alias'] = $receiver;//别名
        }else{
            $receive = 'all';
        }

        $data['audience'] = $receive;      //目标用户

        $data['notification'] = array(

            //统一的模式--标准模式
            //"alert"=>$content,
            //安卓自定义
            "android"=>array(
                "alert"=>$content,
                "title"=>"",
                "builder_id"=>1,
                "extras"=>array("action"=>$m_type, "value"=>$m_txt,)
            ),
            //ios的自定义
            "ios"=>array(
                "alert"=>$content,
                "badge"=>"1",
                "sound"=>"default",
                "extras"=>array("action"=>$m_type, "value"=>$m_txt)
            )
        );

        //苹果自定义---为了弹出值方便调测
        $data['message'] = array(
            "msg_content"=>$content,
            "extras"=>array("action"=>$m_type, "value"=>$m_txt)
        );

        //附加选项
        $data['options'] = array(
            "sendno"=>time(),
            "time_to_live"=> intval($m_time), //保存离线时间的秒数默认为一天
            "apns_production"=>true, //布尔类型   指定 APNS 通知发送环境：0开发环境，1生产环境。或者传递false和true
        );


        $rep_data = array(
            'name' => date('Ymd_His'),
            'enabled' => true,
            'trigger' => array(
                'single' => array(
                    'time' => $timing,
                ),
            ),
            'push' => $data,
        );

        $param = json_encode($rep_data);
//        var_dump($param);die;
//        var_dump($param);die;
        $res = $this->schedule_put($param,$schedule_id);

        if(isset($res['error'])){
            return false;
        }else{
            return true;
        }

//        if($res){       //得到返回值--成功已否后面判断
//            return $res;
//        }else{          //未得到返回值--返回失败
//            return false;
//        }
    }

    //PUT curl请求 修改定时推送信息
    public function schedule_put($param,$schedule_id=''){
        $base64=base64_encode("$this->app_key:$this->master_secret");
        $header=array("Authorization:Basic $base64","application/x-www-form-urlencoded","Accept:application/json");
        $postUrl = $this->schedule_edit.$schedule_id;

        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_URL, $postUrl);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
        $tmpInfo = curl_exec($curl);     //返回api的json对象
        //关闭URL请求
        curl_close($curl);
//        var_dump(getallheaders());    //返回json对象
        $headArr = explode("\r\n", $tmpInfo);    //返回json对象
//
//var_dump($tmpInfo);die;
        $cidlist = $headArr[7];

        $cidlist = json_decode($cidlist,true);

        return $cidlist;


    }



}


<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/10
 * Time: 18:07
 */

class ReplyAction extends Action{
    //定义各模块锁定级别
    private $index = '97';
    private $delreply_do = '97';
    private $reply_do = '97';
    private $detail = '97';

    protected $JiPush;

    //回复列表
    public function index(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->index);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收查询数据
        $id = trim($this->_get('find_id'));
        $vid = trim($this->_get('find_vid'));
        $uid = trim($this->_get('find_uid'));
        $find_date_sta = trim($this->_get('find_date_sta'));
        $find_date_end = trim($this->_get('find_date_end'));

        if($find_date_sta == ''){
            $find_date_sta = date('Y-m-d',strtotime('-1 months', time()));
        }

        if($find_date_end == ''){
            $find_date_end = date('Y-m-d',time());
        }

        $find_where = array(
            'find_id' => $id,
            'find_vid' => $vid,
            'find_uid' => $uid,
            'find_date_sta' => $find_date_sta,
            'find_date_end' => $find_date_end,
        );

        $this->assign('find_where', $find_where);

        //实例方法
        $Model = new Model();

        $where = "create_datetime >= '".$find_date_sta." 00:00:00' and create_datetime <= '".$find_date_end." 23:59:59'";
        if($id != ''){

            $where .= " and id = '".$id."'";
        }

        if($vid != ''){
            $where .= " and vid = '".$vid."'";
        }

        if($uid != ''){
            $where .= " and userid = '".$uid."'";
        }




        //分页
        import('ORG.Page');// 导入分页类
        $count = $Model->table('sixty_video')
            ->where($where)
            ->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('page', $show);// 赋值分页输出


        //查询评论回复表
        $list_all = $Model -> table('sixty_pinglun_back') -> field('id, content, create_datetime, plid, vid, dianzan,
        userdata, userid, fplid') -> where($where) -> order('create_datetime desc') -> limit($Page->firstRow . ',' . $Page->listRows) ->select();

        if(count($list_all) > 0){
            //遍历回复结果集，获取所有用户id
            $user_id = array();
            $video_id = array();
            foreach($list_all as $k_all => $v_all){
                $user_id[] = $v_all['userid'];
                $video_id[] = $v_all['vid'];
            }
            $user_id = array_unique($user_id);
            $video_id = array_unique($video_id);

            //根据用户ID查询用户表数据
            $where_u['id'] = array('in',$user_id);

            $list_user = $Model -> table('sixty_user') -> field('nickname, id') -> where($where_u) -> order('create_datetime desc')-> select();

            //根据视频ID查询视频表数据
            $where_v['id'] = array('in',$video_id);
            $list_video = $Model -> table('sixty_video') -> field('biaoti, id') -> where($where_v) -> order('create_datetime desc')-> select();


            //遍历结果集，把视频信息和用户信息插入对应回复信息
            foreach($list_all as $k_all => $v_all){
                foreach($list_user as $k_user => $v_user){
                    if($v_all['userid'] == $v_user['id']){
                        $list_all[$k_all]['nickname'] = $v_user['nickname'];
                        break;
                    }
                }

                foreach($list_video as $k_video => $v_video){
                    if($v_all['vid'] == $v_video['id']){
                        $list_all[$k_all]['biaoti'] = $v_video['biaoti'];
                        break;
                    }
                }

                $list_all[$k_all]['content'] = base64_decode($v_all['content']);
            }

        }

        $this->assign('list', $list_all);
        $this->display();

    }

    public function detail(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->detail);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $plid = trim($this->_get('id'));
        $vid = trim($this->_get('vid'));
        $fplid = trim($this->_get('fplid'));

        if($fplid == '' || $vid == ''){
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }

        $Model = new Model();
        //根据id查询视频表数据
        $list_video = $Model -> table('sixty_video') -> field('id,biaoti,showimg,videosavename')
            -> where("id = '".$vid."'") -> find();


        //根据id查询评论表数据
        $list_conmment = $Model -> table('sixty_video_pinglun') -> field('id, dianzan, userid, content, create_datetime')
            -> where("id = '".$fplid."'") -> find();
        $list_conmment['content'] = base64_decode($list_conmment['content']);


        //根据fplid查询回复表数据
        $list_reply = $Model -> table('sixty_pinglun_back') -> field('id, plid, content, create_datetime, userdata, userid')
            -> where("fplid = '".$fplid."'") -> order('create_datetime desc') -> select();


        //遍历结果集
        $user_id_arr = array();//获取用户id
        foreach($list_reply as $k_reply => $v_reply){
            $list_reply[$k_reply]['content'] = base64_decode($v_reply['content']);
            $user_id_arr[] = $v_reply['userdata'];
            $user_id_arr[] = $v_reply['userid'];
        }

        //插入评论用户id
        $user_id_arr[] = $list_conmment['userid'];
        //去重
        $user_id_arr = array_unique($user_id_arr);
        //数组转字符串
        $user_id_str = implode(',',$user_id_arr);


        //根据用户ID查询用户表数据
        $where_u['id'] = array('in',$user_id_str);

        $list_user = $Model -> table('sixty_user') -> field('nickname, id') -> where($where_u) -> select();


        //遍历用户结果集
        foreach($list_user as $k_user => $v_user) {
            //遍历回复结果集
            foreach($list_reply as $k_reply => $v_reply) {
                //判断评论表用户id是否等于用户表id
                if($list_conmment['userid'] == $v_user['id']){
                    $list_conmment['nickname'] = $v_user['nickname'];
                }

                if($v_user['id'] == $v_reply['userdata']){
                    $list_reply[$k_reply]['to_name'] = $v_user['nickname'];

                }
                if($v_user['id'] == $v_reply['userid']){
                    $list_reply[$k_reply]['nickname'] = $v_user['nickname'];

                }
            }

        }

        $this->assign('list_r', $list_reply);
        $this->assign('list_c', $list_conmment);
        $this->assign('list_v', $list_video);
        $this->display();
//        var_dump($list_reply);die;

    }

    public function reply_do(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->reply_do);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
//        var_dump($_POST);die;
        $p_id = trim($this->_post('p_id'));
        $f_id = trim($this->_post('f_id'));
        $v_id = trim($this->_post('v_id'));
        $u_id = trim($this->_post('u_id'));
        $cont = trim($this->_post('reply_con'));

        if($p_id == '' || $f_id == '' || $v_id == '' || $u_id == '' || $cont == ''){
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }

        $Model = new Model();

        //根据用户ID查询用户昵称
        $res_user = $Model -> table('sixty_user') -> field('nickname, jiguangid') -> where("id='".$u_id."'") -> find();
        $user_1 = $Model -> table('sixty_user') -> field('nickname, jiguangid') -> where("id='1'") -> find();


        if(count($res_user) <= 0){
            echo "<script>alert('被回复的用户未找到，回复失败！');history.go(-1);</script>";
            $this -> error('被回复的用户未找到，回复失败！');
        }


        //准备插入评论表数据
        $data = array(
            'plid' => $p_id,
            'fplid' => $f_id,
            'vid' => $v_id,
            'userdata' => $u_id,
            'create_datetime' => date('Y-m-d H:i:s',time()),
            'userid' => 1,
            'content' => base64_encode('@'.$res_user['nickname'].':'.$cont),
            'dianzan' => 0,
        );



        $res = $Model -> table('sixty_pinglun_back') -> add($data);


        //准备插入用户消息表
        $data_news = array(
            'userid' => 1,
            'to_userid' => $u_id,
            'create_datetime' => date('Y-m-d H:i:s',time()),
            'vid' => $v_id,
            'flag' => 2,
            'message' => base64_encode('@'.$res_user['nickname'].':'.$cont),
        );

        $res_news = $Model -> table('sixty_user_news') -> add($data_news);


        include(THINK_PATH.'Common/JiPush.php');
        $this->JiPush = new JiPush();
        $message = $user_1['nickname'].'回复了您的评论';
        $res_push = $this->func_jgpush($res_user['jiguangid'],$message,'liuyan');

        if($res){

            echo "<script>alert('回复成功！');window.location.href=window.location.href='".__APP__."/Reply/detail".$echourl."';</script>";
            $this -> error('回复成功！');

        }else{
            echo "<script>alert('回复失败！');history.go(-1);</script>";
            $this -> error('回复失败！');
        }

    }

    /*
     * 删除评论
     * */
    public function delreply_do(){

        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->delreply_do);
        //拼接URL地址，并返回到页面
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $id = trim($this->_post('reply_id'));


        if($id == '')
        {
            //返回错误
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }

        //查询ID判断此条评论是否存在
        $Model = new Model();
        $res = $Model -> table('sixty_pinglun_back') -> field('id') -> where("id = '" . $id . "'") -> find();
        if($res == '')
        {
            //返回错误
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }

        //执行删除操作
        $result = $Model -> table('sixty_pinglun_back') -> where("id = '" . $id . "'") -> delete();

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //判断删除结果
        if($result == '')
        {
            //返回错误
            echo "<script>alert('删除失败！');history.go(-1);</script>";
            $this -> error('删除失败！');
        }else{

            //返回成功
            echo "<script>alert('评论删除成功!');window.location.href='".__APP__.'/Reply/detail'. $echourl ."';</script>";
            $this -> success('评论删除成功!','__APP__'.$echourl);

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

//极光推送
    public function func_jgpush($jiguangid,$messagee,$m_type='',$m_txt='',$m_time='86400'){



        //极光推送的设置
        /* $m_type = '';//推送附加字段的类型
        $m_txt = '';//推送附加字段的类型对应的内容(可不填) 可能是url,可能是一段文字。
        $m_time = '86400';//离线保留时间 */
        $receive = array('alias'=>array($jiguangid));//别名
        //$receive = array('alias'=>array('073dc8672c25d8d023328d06dbbd1230'));//别名
        $content = $messagee;
        //$message="";//存储推送状态
        $result = $this->JiPush->push($receive,$content,$m_type,$m_txt,$m_time);

        if($result){
            $res_arr = json_decode($result, true);

            if(isset($res_arr['error'])){                       //如果返回了error则证明失败
                echo $res_arr['error']['message'];          //错误信息
                $error_code=$res_arr['error']['code'];             //错误码
                switch ($error_code) {
                    case 200:
                        $message= '发送成功！';
                        break;
                    case 1000:
                        $message= '失败(系统内部错误)';
                        break;
                    case 1001:
                        $message = '失败(只支持 HTTP Post 方法，不支持 Get 方法)';
                        break;
                    case 1002:
                        $message= '失败(缺少了必须的参数)';
                        break;
                    case 1003:
                        $message= '失败(参数值不合法)';
                        break;
                    case 1004:
                        $message= '失败(验证失败)';
                        break;
                    case 1005:
                        $message= '失败(消息体太大)';
                        break;
                    case 1008:
                        $message= '失败(appkey参数非法)';
                        break;
                    case 1020:
                        $message= '失败(只支持 HTTPS 请求)';
                        break;
                    case 1030:
                        $message= '失败(内部服务超时)';
                        break;
                    default:
                        $message= '失败(返回其他状态，目前不清楚额，请联系开发人员！)';
                        break;
                }
            }else{
                $message="ok";
            }
        }else{//接口调用失败或无响应
            $message='接口调用失败或无响应';
        }

        //return $message;
    }

}
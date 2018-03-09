<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/13
 * Time: 10:11
 */
class NoticeAction extends Action {
    //定义各模块锁定级别
    private $lock_index         = '97';
    private $lock_addnotice_do   = '97';
    private $lock_editnotice   = '97';
    private $lock_editnotice_do   = '97';
    private $lock_delnotice_do   = '97';
    private $fabu   = '97';
    private $lock_indextiming   = '97';
    private $lock_addtiming   = '97';
    private $lock_addtiming_do   = '97';
    private $lock_edittiming   = '97';
    private $lock_edittiming_do  = '97';
    private $lock_deltiming   = '97';

    protected $JiPush;
    //显示通知列表
    public function index() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //实例化方法
        $Model = new Model();
        $where = 'push_type = 1';
        //分页
        import('ORG.Page');// 导入分页类
        $count = $Model->table('sixty_tongzhi') -> where($where)
            ->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('page', $show);// 赋值分页输出

        //执行查询通知表数据
        $list = $Model -> table('sixty_tongzhi') -> field('id, type, remark, sex, message, create_datetime, push_datetime, flag')
            -> where($where) -> order('create_datetime desc') -> limit($Page->firstRow . ',' . $Page->listRows) -> select();

        //遍历结果集
        foreach($list as $k_l => $v_l){

            //把发送状态值改为文字显示
            if($v_l['flag'] == 1){
                $list[$k_l]['flag'] = '<span style="background-color:#33FF66;padding:3px;">1-已发送</span>';
            }else if($v_l['flag'] == 2){
                $list[$k_l]['flag'] = '<span style="background-color:#FF82A5;padding:3px;">2-未发送</span>';
            }

            //把接收人群改为文字显示
            if($v_l['sex'] == 1){
                $list[$k_l]['sex'] = '男';
            }else if($v_l['sex'] == 2){
                $list[$k_l]['sex'] = '女';
            }else if($v_l['sex'] == 0){
                $list[$k_l]['sex'] = '全部';
            }

            //把接收人群改为文字显示
            if($v_l['type'] == 1){
                $list[$k_l]['type'] = '文字';
            }else if($v_l['type'] == 2){
                $list[$k_l]['type'] = '视频';
            }else if($v_l['type'] == 3){
                $list[$k_l]['type'] = '小贴士';
            }else if($v_l['type'] == 4){
                $list[$k_l]['type'] = '合集';
            }

            //判断最后发送时间，如果是默认值 0000-00-00 00:00:00，显示未发送。否则显示发送时间
            if($v_l['push_datetime'] == '0000-00-00 00:00:00'){
                $list[$k_l]['push_datetime'] = '<span style="background-color:#FF82A5;padding:3px;">尚未发送</span>';
            }

        }

        //输出到模板
        $this -> assign('list', $list);
        $this -> display();

    }

    public function addnotice(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addnotice_do);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //准备下拉列表
        $arr_sex = array(
            '0' => '全选',
            '1' => '男',
            '2' => '女',
            '3' => '保密',
        );

        $list_sex = $this->downlist($arr_sex,'0');


        //三级联动显示
        //获取所有视频和小贴士
        $arr_top = array(
            '1' => '文字',
            '2' => '视频',
            '3' => '小贴士',
            '4' => '合集',
        );
        $list_top = $this->downlist($arr_top);

        $list_middle = "<option value=''>无</option>>";

        $this->assign('list_sex',$list_sex);
        $this->assign('list_top',$list_top);
        $this->assign('list_middle',$list_middle);
        $this->display();



    }



    //执行系统通知添加
    public function addnotice_do() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addnotice_do);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $message = trim($this->_post('message'));
        $sex = trim($this->_post('sex'));
        $type = trim($this->_post('type'));
        $up_id = trim($this->_post('up_id'));
        $remark = trim($this->_post('remark'));

        //判断数据是否为空
        if($message == '') {
            echo "<script>alert('系统通知不能为空');history.go(-1);</script>";
            $this -> error('系统通知不能为空!');
        }

        if($type == '' || ($type != 1 && $up_id == '')){
            echo "<script>alert('类型选择不正确');history.go(-1);</script>";
            $this -> error('类型选择不正确!');
        }


        //实例化方法
        $Model = new Model();

        //判断通知类型
        if($type == 1){//文字
            $up_name['name'] = '';
        }else{
            if($type == 2){//视频
                $up_name = $Model -> table('sixty_video') -> field('biaoti as name') -> where("id = '".$up_id."'") -> find();
            }else if($type == 3){//小贴士
                $up_name = $Model -> table('sixty_tieshi_video') -> field('biaoti as name') -> where("id = '".$up_id."'") -> find();
            }else if($type == 4){//合集
                $up_name = $Model -> table('sixty_jihemsg') -> field('name') -> where("id = '".$up_id."'") -> find();
            }


            if($up_name['name'] == ''){
                echo "<script>alert('素材未找到');history.go(-1);</script>";
                $this -> error('素材未找到!');
            }
        }


        //准备添加数组
        $create_datetime = date('Y-m-d H:i:s', time());
        $arr = array(
            'message' => $message,
            'create_datetime' => $create_datetime,
            'sex' => $sex,
            'vid' => $up_id,
            'type' => $type,
            'videoname' => $up_name['name'],
            'remark' => $remark,
            'push_type' => 1,
            );

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        $res = $Model -> table('sixty_tongzhi') -> add($arr);

        if($res) {
            //成功返回成功
            echo "<script>alert('通知添加成功!');window.location.href='".__APP__.'/Notice/index'.$echourl."';</script>";
            $this -> success('通知添加成功!','__APP__'.$echourl);
        }else {
            echo "<script>alert('通知添加失败！');history.go(-1);</script>";
            $this -> error('通知添加失败！');
        }
    }

    //跳转编辑页面
    public function editnotice() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editnotice);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $id = trim($this->_post('notice_id'));

        //判断数据来源
        if($id == '') {
            echo "<script>alert('非法进入此页面！');history.go(-1);</script>";
            $this -> error('非法进入此页面！');
        }


        //实例化方法
        $Model = new Model();

        //执行查询
        $list = $Model -> table('sixty_tongzhi') -> field('vid, type, remark, videoname, id, sex, message, create_datetime')
            -> where("id='" . $id . "'") -> find();

        //如果没查出数据返回非法进入
        if($list == '') {
            echo "<script>alert('非法进入此页面！');history.go(-1);</script>";
            $this -> error('非法进入此页面！');
        }

        //准备下拉列表
        $arr_sex = array(
            '0' => '全选',
            '1' => '男',
            '2' => '女',
        );

        $list_sex = $this->downlist($arr_sex,$list['sex']);

        $this->assign('list_sex',$list_sex);

        //分类下拉菜单
        $arr_top = array(
            '1' => '文字',
            '2' => '视频',
            '3' => '小贴士',
            '4' => '合集',
        );
        $list_top = $this->downlist($arr_top, $list['type']);



        if($list['type'] == 2){
            $middle_arr = $Model -> table('sixty_video') -> field('id,biaoti') -> select();
        }else if($list['type'] == 3){
            $middle_arr = $Model -> table('sixty_tieshi_video') -> field('id, biaoti') -> select();
        }else if($list['type'] == 4){
            $middle_arr = $Model -> table('sixty_jihemsg') -> field('id, name as biaoti') -> select();
        }

        if($list['type'] == 1){
            $list_middle = "<option value=''>无</option>>";
        }else{
            $middle = array();
            foreach($middle_arr as $k_m => $v_m){
                $middle[$v_m['id']] = $v_m['biaoti'];
            }
            $list_middle = $this->downlist($middle, $list['vid']);
        }



        $this->assign('list_top',$list_top);
        $this->assign('list_middle',$list_middle);


        //输出模板
        $this -> assign('list', $list);
        $this -> display();

    }


    //执行编辑操作
    public function editnotice_do()
    {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editnotice_do);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl', $echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $message = trim($this->_post('message'));
        $sex = trim($this->_post('sex'));
        $type = trim($this->_post('type'));
        $up_id = trim($this->_post('up_id'));
        $remark = trim($this->_post('remark'));
        $id = trim($this->_post('editnotice_id'));

        //判断数据是否为空
        if($message == '') {
            echo "<script>alert('系统通知不能为空');history.go(-1);</script>";
            $this -> error('系统通知不能为空!');
        }

        if($type == '' || ($type != 1 && $up_id == '')){
            echo "<script>alert('类型选择不正确');history.go(-1);</script>";
            $this -> error('类型选择不正确!');
        }


        //实例化方法
        $Model = new Model();

        //判断通知类型
        if($type == 1){//文字
            $up_name['name'] = '';
        }else{
            if($type == 2){//视频
                $up_name = $Model -> table('sixty_video') -> field('biaoti as name') -> where("id = '".$up_id."'") -> find();
            }else if($type == 3){//小贴士
                $up_name = $Model -> table('sixty_tieshi_video') -> field('biaoti as name') -> where("id = '".$up_id."'") -> find();
            }else if($type == 4){//合集
                $up_name = $Model -> table('sixty_jihemsg') -> field('name') -> where("id = '".$up_id."'") -> find();
            }

            if($up_name['name'] == ''){
                echo "<script>alert('素材未找到');history.go(-1);</script>";
                $this -> error('素材未找到!');
            }
        }

        //准备添加数组
        $arr = array(
            'message' => $message,
            'sex' => $sex,
            'vid' => $up_id,
            'type' => $type,
            'videoname' => $up_name['name'],
            'remark' => $remark,
        );


        //执行编辑
        $res = $Model->table('sixty_tongzhi')->where("id='" . $id . "'")->save($arr);
//        var_dump($Model->getLastSql());die;
        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //判断结果
        if ($res) {
            //成功返回成功
            echo "<script>alert('通知修改成功!');window.location.href='" . __APP__ . '/Notice/index' . $echourl . "';</script>";
            $this->success('通知修改成功!', '__APP__' . $echourl);
        } else {
            echo "<script>alert('通知修改失败！');history.go(-1);</script>";
            $this->error('通知修改失败！');

        }
    }


    //删除通知
    public function delnotice_do() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_delnotice_do);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl', $echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $id = trim($this->_post('delnotice_id'));
        $submitdel = trim($this->_post('submitdel'));

        //判断数据来源
        if ($id == '') {
            echo "<script>alert('非法进入此页面！');history.go(-1);</script>";
            $this->error('非法进入此页面！');
        }

        if ($submitdel == '') {
            echo "<script>alert('非法进入此页面！');history.go(-1);</script>";
            $this->error('非法进入此页面！');
        }


        //根据ID删除数据
        $Model = new Model();

        //执行删除
        $res = $Model -> table('sixty_tongzhi') -> where("id='" . $id . "'") -> delete();

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //判断结果
        if ($res) {
            //成功返回成功
            echo "<script>alert('通知删除成功!');window.location.href='" . __APP__ . '/Notice/index' . $echourl . "';</script>";
            $this->success('通知删除成功!', '__APP__' . $echourl);
        } else {
            echo "<script>alert('通知删除失败！');history.go(-1);</script>";
            $this->error('通知删除失败！');

        }
    }


    public function fabu(){

        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->fabu);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl', $echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //加载极光推送文件
        include(THINK_PATH.'Common/JiPush.php');
        $this->JiPush = new JiPush();

//        $a[] = '88b2255aad7d0a863de7c72f0e26bb11';
        //接收上传数据
        $id = trim($this->_post('notice_id'));

        if($id == ''){
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this->error('非法进入！');
        }


        //取出通知内容
        $Model = new Model();
        $res_tongzhi = $Model -> table('sixty_tongzhi') ->
        field('id, type,message,create_datetime,sex, vid, videoname, push_type, timing_date')
            -> where("id='".$id."' and id != 1") -> find();

        if(count($res_tongzhi) <= 0){
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this->error('非法进入！');
        }

        $schedule_id = '';
        $schedule_name = '';
        //判断是推送所有人还是推送部分人
        if($res_tongzhi['sex'] != 0){//部分推送

            $where_user = 'push_state = 1 ';
            //性别查询
            if($res_tongzhi['sex'] == 1){
                $where_user .= 'and sex in (1,3)';
            }else if($res_tongzhi['sex'] == 2){
                $where_user .= 'and sex in (2,3)';
            }

            //取出用户极光ID
            $res_user = $Model -> table('sixty_user') -> field('jiguangid') -> where($where_user) -> select();

            if(count($res_user) <= 0){
                echo "<script>alert('通知失败，用户未找到!');window.location.href='" . __APP__ . '/Notice/index' . $echourl . "';</script>";
                $this->success('通知失败，用户未找到!', '__APP__' . $echourl);
            }

            //把极光id放入一个数组
            $jg_arr = array();
            foreach($res_user as $k_r => $v_r){
                if($v_r['jiguangid'] != ''){
                    $jg_arr[] = $v_r['jiguangid'];
                }

            }

            //判断极光ID是否为空
            if(count($jg_arr) >0){
                //判断推送类型 1：文字 2：视频
                if($res_tongzhi['type'] == 2){
                    //把视频id和视频名称放入输出数组
                    $txt = array(
                        'vtitle'=>$res_tongzhi['videoname'],
                        'vid' =>$res_tongzhi['vid']
                    );
                    //判断是即时推送还是定时推送
                    if($res_tongzhi['push_type'] == 1){//即时推送、

//                        $res_push = $this->JiPush->push($a,$res_tongzhi['message'],'details',$txt,'');
                        $res_push = $this->JiPush->push($jg_arr,$res_tongzhi['message'],'details',$txt,'');
                    }else if($res_tongzhi['push_type'] == 2){//定时推送

//                        $res_push = $this->JiPush->timing_push($a,$res_tongzhi['message'],'details',$txt,'',$res_tongzhi['timing_date']);
                        $res_push = $this->JiPush->timing_push($jg_arr,$res_tongzhi['message'],'details',$txt,'',$res_tongzhi['timing_date']);
                        $res = json_decode($res_push,true);
                        if(isset($res['error'])) {
                            echo "<script>alert('推送失败！');history.go(-1);</script>";
                            $this->error('推送失败！');
                        }
                        $schedule_id = $res['schedule_id'];
                        $schedule_name = $res['schedule_name'];
                    }

                }else if($res_tongzhi['type'] == 1){//文字推送

                    //判断推送类型 1：即时推送 2：定时推送
                    if($res_tongzhi['push_type'] == 1){

                        $jgid_arr['alias'] = $jg_arr;
//                        $res_push = $this->JiPush->push($a,$res_tongzhi['message'],'message');
                        $res_push = $this->JiPush->push($jgid_arr,$res_tongzhi['message'],'message');
                    }else if($res_tongzhi['push_type'] == 2){

//                        $res_push = $this->JiPush->timing_push($a,$res_tongzhi['message'],'message', '','',$res_tongzhi['timing_date']);
                        $res_push = $this->JiPush->timing_push($jg_arr,$res_tongzhi['message'],'message', '','',$res_tongzhi['timing_date']);
                        $res = json_decode($res_push,true);
                        if(isset($res['error'])) {
                            echo "<script>alert('推送失败！');history.go(-1);</script>";
                            $this->error('推送失败！');
                        }
                        $schedule_id = $res['schedule_id'];
                        $schedule_name = $res['schedule_name'];
                    }
                    //执行推送
//                    $res_push = $this->func_jgpush($jg_arr,$res_tongzhi['message'],'message', '','',$res_tongzhi['push_type']);
                }

            }else{
                echo "<script>alert('通知失败，用户未找到!');window.location.href='" . __APP__ . '/Notice/index' . $echourl . "';</script>";
                $this->success('通知失败，用户未找到!', '__APP__' . $echourl);
            }
        }else{//推送全部用户

            //判断推送类型 1：文字 2：视频
            if($res_tongzhi['type'] == 2){//推送视频

                //把视频id和视频名称放入输出数组
                $txt = array(
                    'vtitle'=>$res_tongzhi['videoname'],
                    'vid' =>$res_tongzhi['vid']
                );
                //判断是即时推送还是定时推送
//                $a['alias'][] = '396010ef8aab8261363352a9e93a7b6f';
                if($res_tongzhi['push_type'] == 1){//即时推送
                    $res_push = $this->JiPush->push('all',$res_tongzhi['message'],'details',$txt,'');
                }else if($res_tongzhi['push_type'] == 2){//定时推送
//                    $a['alias'][] = '396010ef8aab8261363352a9e93a7b6f';
                    $res_push = $this->JiPush->timing_push('all',$res_tongzhi['message'],'details',$txt,'',$res_tongzhi['timing_date']);
                    $res = json_decode($res_push,true);
                    if(isset($res['error'])) {
                        echo "<script>alert('推送失败！');history.go(-1);</script>";
                        $this->error('推送失败！');
                    }
                    $schedule_id = $res['schedule_id'];
                    $schedule_name = $res['schedule_name'];
                }

            }else if($res_tongzhi['type'] == 1){//推送文字

//                $a['alias'][] = '396010ef8aab8261363352a9e93a7b6f';
                //判断推送类型 1：即时推送 2：定时推送
                if($res_tongzhi['push_type'] == 1){

                    $res_push = $this->JiPush->push('all',$res_tongzhi['message'],'message');
                }else if($res_tongzhi['push_type'] == 2){

                    $res_push = $this->JiPush->timing_push('all',$res_tongzhi['message'],'message', '','',$res_tongzhi['timing_date']);
                    $res = json_decode($res_push,true);
                    if(isset($res['error'])) {
                        echo "<script>alert('推送失败！');history.go(-1);</script>";
                        $this->error('推送失败！');
                    }
                    $schedule_id = $res['schedule_id'];
                    $schedule_name = $res['schedule_name'];
                }

            }
        }


//        if(!is_dir(LOGPATH.'jiguang.log')) {
//            //创建该目录
//            mkdir(LOGPATH.'jiguang.log', 0777, true);
//        }
//
//        $fp = fopen(LOGPATH.'jiguang.log','a'); //打开句柄
//        fwrite($fp, $res_push);  //将文件内容写入字符串

        //修改通知状态，改为已发送，并修改最后发送时间
        $update = array(
            'flag' => 1,
            'push_datetime' => date('Y-m-d H:i:s', time()),
            'schedule_id' => $schedule_id,
            'schedule_name' => $schedule_name,
        );
        $res_flag = $Model -> table('sixty_tongzhi') -> where("id='".$id."'") -> save($update);


        if($res_tongzhi['push_type'] == 1){
            echo "<script>alert('推送已发送!');window.location.href='" . __APP__ . '/Notice/index' . $echourl . "';</script>";
            $this->success('推送已发送!', '__APP__' . $echourl);
        }else if($res_tongzhi['push_type'] == 2){
            echo "<script>alert('推送已发送!');window.location.href='" . __APP__ . '/Notice/indextiming' . $echourl . "';</script>";
            $this->success('推送已发送!', '__APP__' . $echourl);
        }


    }



    //定时发布推送消息
    public function get_timing_list(){



        include(THINK_PATH.'Common/JiPush.php');
        $this->JiPush = new JiPush();

        $list_schedules = $this->JiPush->schedule_list();

        return json_encode($list_schedules);

        $list = array();
        foreach($list_schedules['schedules'] as $key => $val){
            $list['name'] = $val['name'];
            $list['schedules_id'] = $val['schedules_id'];
            $list['to_time'] = $val['trigger']['single']['time'];
        }
    }


    //定时推送首页展示
    public function indextiming(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_indextiming);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //实例化方法
        $Model = new Model();

        $where = 'push_type = 2';
        //分页
        import('ORG.Page');// 导入分页类
        $count = $Model->table('sixty_tongzhi') -> where($where)
            ->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('page', $show);// 赋值分页输出

        //执行查询通知表数据
        $list = $Model -> table('sixty_tongzhi') ->
        field('id, type, remark, sex, message, create_datetime, push_datetime, flag, timing_date, push_type')
            -> where($where)
            -> order('create_datetime desc') -> limit($Page->firstRow . ',' . $Page->listRows) -> select();
        //遍历结果集
        foreach($list as $k_l => $v_l){

            //把发送状态值改为文字显示
            if($v_l['flag'] == 1){
                $list[$k_l]['flag'] = '<span style="background-color:#33FF66;padding:3px;">1-已发送</span>';
            }else if($v_l['flag'] == 2){
                $list[$k_l]['flag'] = '<span style="background-color:#FF82A5;padding:3px;">2-未发送</span>';
            }

            //把接收人群改为文字显示
            if($v_l['sex'] == 1){
                $list[$k_l]['sex'] = '男';
            }else if($v_l['sex'] == 2){
                $list[$k_l]['sex'] = '女';
            }else if($v_l['sex'] == 0){
                $list[$k_l]['sex'] = '全部';
            }

            //把接收人群改为文字显示
            if($v_l['type'] == 1){
                $list[$k_l]['type'] = '文字';
            }else if($v_l['type'] == 2){
                $list[$k_l]['type'] = '视频';
            }else if($v_l['type'] == 3){
                $list[$k_l]['type'] = '小贴士';
            }else if($v_l['type'] == 4){
                $list[$k_l]['type'] = '合集';
            }

            //判断最后发送时间，如果是默认值 0000-00-00 00:00:00，显示未发送。否则显示发送时间
            if($v_l['push_datetime'] == '0000-00-00 00:00:00'){
                $list[$k_l]['push_datetime'] = '<span style="background-color:#FF82A5;padding:3px;">尚未发送</span>';
            }

        }

        //输出到模板
        $this -> assign('list', $list);
        $this -> display();
    }


    //添加定时推送
    public function addtiming(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addtiming);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //准备下拉列表
        $arr_sex = array(
            '0' => '全选',
            '1' => '男',
            '2' => '女',
            '3' => '保密',
        );

        $list_sex = $this->downlist($arr_sex,'0');


        //三级联动显示
        //获取所有视频和小贴士
        $arr_top = array(
            '1' => '文字',
            '2' => '视频',
            '3' => '小贴士',
            '4' => '合集',
        );
        $list_top = $this->downlist($arr_top);

        $list_middle = "<option value=''>无</option>>";

        $this->assign('list_sex',$list_sex);
        $this->assign('list_top',$list_top);
        $this->assign('list_middle',$list_middle);
        $this->display();
    }

//执行系统通知添加
    public function addtiming_do() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addtiming_do);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $message = trim($this->_post('message'));
        $sex = trim($this->_post('sex'));
        $type = trim($this->_post('type'));
        $up_id = trim($this->_post('up_id'));
        $remark = trim($this->_post('remark'));
        $push_time = trim($this->_post('push_time'));

        //判断数据是否为空
        if($message == '') {
            echo "<script>alert('系统通知不能为空');history.go(-1);</script>";
            $this -> error('系统通知不能为空!');
        }

        if($type == '' || ($type != 1 && $up_id == '')){
            echo "<script>alert('类型选择不正确');history.go(-1);</script>";
            $this -> error('类型选择不正确!');
        }


        //实例化方法
        $Model = new Model();

        //判断通知类型
        if($type == 1){//文字
            $up_name['name'] = '';
        }else{
            if($type == 2){//视频
                $up_name = $Model -> table('sixty_video') -> field('biaoti as name') -> where("id = '".$up_id."'") -> find();
            }else if($type == 3){//小贴士
                $up_name = $Model -> table('sixty_tieshi_video') -> field('biaoti as name') -> where("id = '".$up_id."'") -> find();
            }else if($type == 4){//合集
                $up_name = $Model -> table('sixty_jihemsg') -> field('name') -> where("id = '".$up_id."'") -> find();
            }


            if($up_name['name'] == ''){
                echo "<script>alert('素材未找到');history.go(-1);</script>";
                $this -> error('素材未找到!');
            }
        }


        //准备添加数组
        $create_datetime = date('Y-m-d H:i:s', time());
        $arr = array(
            'message' => $message,
            'create_datetime' => $create_datetime,
            'sex' => $sex,
            'vid' => $up_id,
            'type' => $type,
            'videoname' => $up_name['name'],
            'remark' => $remark,
            'timing_date' => $push_time,
            'push_type' => 2,
        );

        //执行添加
        $res = $Model -> table('sixty_tongzhi') -> add($arr);
        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);


        if($res) {
            //成功返回成功
            echo "<script>alert('通知添加成功!');window.location.href='".__APP__.'/Notice/indextiming'.$echourl."';</script>";
            $this -> success('通知添加成功!','__APP__'.$echourl);
        }else {
            echo "<script>alert('通知添加失败！');history.go(-1);</script>";
            $this -> error('通知添加失败！');
        }
    }

    public function edittiming(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_edittiming);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $id = trim($this->_post('notice_id'));

        //判断数据来源
        if($id == '') {
            echo "<script>alert('非法进入此页面！');history.go(-1);</script>";
            $this -> error('非法进入此页面！');
        }


        //实例化方法
        $Model = new Model();

        //执行查询
        $list = $Model -> table('sixty_tongzhi')
            -> field('vid, type, remark, videoname, id, sex, message, 
            create_datetime, push_type, timing_date, schedule_id, schedule_name')
            -> where("id='" . $id . "'") -> find();

        //如果没查出数据返回非法进入
        if($list == '') {
            echo "<script>alert('非法进入此页面！');history.go(-1);</script>";
            $this -> error('非法进入此页面！');
        }

        //加载推送方法
        include(THINK_PATH.'Common/JiPush.php');
        $this->JiPush = new JiPush();

        //根据schedule_id查询推送信息
        $list_schedules = $this->JiPush->schedule_one($list['schedule_id']);

        if($list_schedules['name'] != $list['schedule_name'] || $list_schedules['trigger']['single']['time'] != $list['timing_date']){
            //修改通知状态，改为已发送，并修改最后发送时间

            $update = array(
                'flag' => 1,
                'timging_date' => date('Y-m-d H:i:s', time()),
                'schedule_id' => $list_schedules['schedule_id'],
                'timing_date' => $list_schedules['trigger']['single']['time'],
                'schedule_name' => $list_schedules['name'],
            );

            $res_flag = $Model -> table('sixty_tongzhi') -> where("id='".$id."'") -> save($update);
            //写入日志
            $templogs = $Model->getlastsql();
            hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);
        }

        //准备下拉列表
        $arr_sex = array(
            '0' => '全选',
            '1' => '男',
            '2' => '女',
        );

        $list_sex = $this->downlist($arr_sex,$list['sex']);

        $this->assign('list_sex',$list_sex);

        //分类下拉菜单
        $arr_top = array(
            '1' => '文字',
            '2' => '视频',
            '3' => '小贴士',
            '4' => '合集',
        );
        $list_top = $this->downlist($arr_top, $list['type']);


        //根据推送类型获取对应表的数据
        if($list['type'] == 2){//视频
            $middle_arr = $Model -> table('sixty_video') -> field('id,biaoti') -> select();
        }else if($list['type'] == 3){//小贴士
            $middle_arr = $Model -> table('sixty_tieshi_video') -> field('id, biaoti') -> select();
        }else if($list['type'] == 4){//合集
            $middle_arr = $Model -> table('sixty_jihemsg') -> field('id, name as biaoti') -> select();
        }

        //判断推送类型，生成select选项
        if($list['type'] == 1){//是文字，select显示无
            $list_middle = "<option value=''>无</option>>";
        }else{//非文字，显示对应内容

            $middle = array();
            //把取出的数据放入新数组，准备生成下拉菜单
            foreach($middle_arr as $k_m => $v_m){
                $middle[$v_m['id']] = $v_m['biaoti'];
            }
            //生成下拉菜单
            $list_middle = $this->downlist($middle, $list['vid']);
        }


        //输出到页面
        $this->assign('list_top',$list_top);
        $this->assign('list_middle',$list_middle);


        //输出模板
        $this -> assign('list', $list);
        $this -> display();
    }


    public function edittiming_do(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_edittiming_do);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl', $echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $message = trim($this->_post('message'));
        $sex = trim($this->_post('sex'));
        $type = trim($this->_post('type'));
        $up_id = trim($this->_post('up_id'));
        $remark = trim($this->_post('remark'));
        $push_time = trim($this->_post('push_time'));
        $id = trim($this->_post('editnotice_id'));

        //判断数据是否为空
        if($id == ''){
            echo "<script>alert('非法进入');history.go(-1);</script>";
            $this -> error('非法进入!');
        }
        if($message == '') {
            echo "<script>alert('系统通知不能为空');history.go(-1);</script>";
            $this -> error('系统通知不能为空!');
        }

        if($type == '' || ($type != 1 && $up_id == '')){
            echo "<script>alert('类型选择不正确');history.go(-1);</script>";
            $this -> error('类型选择不正确!');
        }


        //实例化方法
        $Model = new Model();


        //判断通知类型
        if($type == 1){//文字
            $up_name['name'] = '';
        }else{
            if($type == 2){//视频
                $up_name = $Model -> table('sixty_video') -> field('biaoti as name') -> where("id = '".$up_id."'") -> find();
            }else if($type == 3){//小贴士
                $up_name = $Model -> table('sixty_tieshi_video') -> field('biaoti as name') -> where("id = '".$up_id."'") -> find();
            }else if($type == 4){//合集
                $up_name = $Model -> table('sixty_jihemsg') -> field('name') -> where("id = '".$up_id."'") -> find();
            }


            if($up_name['name'] == ''){
                echo "<script>alert('素材未找到');history.go(-1);</script>";
                $this -> error('素材未找到!');
            }
        }

        //根据ID查找数据信息
        $result = $Model -> table('sixty_tongzhi') -> field('*') -> where("id = $id") -> find();

        //判断根据id是否取出信息
        if(count($result) < 0){
            echo "<script>alert('非法进入');history.go(-1);</script>";
            $this -> error('非法进入!');
        }

        //加载极光推送文件
        include(THINK_PATH.'Common/JiPush.php');
        $this->JiPush = new JiPush();

        //判断是否推送全部用户
        if($sex == 0){//推送全部用户

            if($result['type'] == 2){//视频推送
                //把视频id和视频名称放入输出数组
                $txt = array(
                    'vtitle'=>$up_name['name'],
                    'vid' =>$up_id
                );
                //执行推送
                $res_push = $this->JiPush->timing_edit('all',$message,'details',$txt,'',$push_time,$result['schedule_id']);
            }else if($result['type'] == 1){//文字推送
                //执行推送
                $res_push = $this->JiPush->timing_edit('all',$message,'message','','',$push_time,$result['schedule_id']);
            }

        }else if($sex == 1 || $sex == 2){//推送部分用户

            $where_user = 'push_state = 1 ';
            //性别查询
            if($sex == 1){
                $where_user .= 'and sex in (1,3)';
            }else if($sex == 2){
                $where_user .= 'and sex in (2,3)';
            }

            //取出用户极光ID
            $res_user = $Model -> table('sixty_user') -> field('jiguangid') -> where($where_user) -> select();

            if(count($res_user) <= 0){
                echo "<script>alert('通知失败，用户未找到!');history.go(-1);</script>";
                $this -> error('通知失败，用户未找到!');
            }

            //把极光id放入一个数组
            $jg_arr = array();
            foreach($res_user as $k_r => $v_r){
                if($v_r['jiguangid'] != ''){
                    $jg_arr[] = $v_r['jiguangid'];
                }

            }

//$a[] = '88b2255aad7d0a863de7c72f0e26bb11';
            if($result['type'] == 2){//视频推送
                //把视频id和视频名称放入输出数组
                $txt = array(
                    'vtitle'=>$up_name['name'],
                    'vid' =>$up_id
                );
                //执行推送
//                $res_push = $this->JiPush->timing_edit($a,$message,'details',$txt,'',$push_time,$result['schedule_id']);
                $res_push = $this->JiPush->timing_edit($jg_arr,$message,'details',$txt,'',$push_time,$result['schedule_id']);
            }else if($result['type'] == 1){//文字推送
                //执行推送
//                $res_push = $this->JiPush->timing_edit($a,$message,'message','','',$push_time,$result['schedule_id']);
                $res_push = $this->JiPush->timing_edit($jg_arr,$message,'message','','',$push_time,$result['schedule_id']);
            }
        }

        if(!$res_push){
            echo "<script>alert('推送失败！');history.go(-1);</script>";
            $this -> error('推送失败！');
        }

        //准备添加数组
        $create_datetime = date('Y-m-d H:i:s', time());
        $arr = array(
            'message' => $message,
            'create_datetime' => $create_datetime,
            'sex' => $sex,
            'vid' => $up_id,
            'type' => $type,
            'videoname' => $up_name['name'],
            'remark' => $remark,
            'timing_date' => $push_time,
            'push_type' => 2,
        );
        $res = $Model -> table('sixty_tongzhi') -> where("id = $id") -> save($arr);
        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        if($res) {
            //成功返回成功
            echo "<script>alert('通知添加成功!');window.location.href='".__APP__.'/Notice/indextiming'.$echourl."';</script>";
            $this -> success('通知添加成功!','__APP__'.$echourl);
        }else {
            echo "<script>alert('通知添加失败！');history.go(-1);</script>";
            $this -> error('通知添加失败！');
        }










    }




    public function deltiming(){

        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_deltiming);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl', $echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $id = trim($this->_post('delnotice_id'));
        $submitdel = trim($this->_post('submitdel'));

        //判断数据来源
        if ($id == '') {
            echo "<script>alert('非法进入此页面！');history.go(-1);</script>";
            $this->error('非法进入此页面！');
        }

        if ($submitdel == '') {
            echo "<script>alert('非法进入此页面！');history.go(-1);</script>";
            $this->error('非法进入此页面！');
        }




        $Model = new Model();

        //执行查询
        $list = $Model -> table('sixty_tongzhi')
            -> field('vid, type, remark, videoname, id, sex, message, 
            create_datetime, push_type, timing_date, schedule_id, schedule_name, push_datetime')
            -> where("id='" . $id . "'") -> find();

        //如果没查出数据返回非法进入
        if($list == '') {
            echo "<script>alert('非法进入此页面！');history.go(-1);</script>";
            $this -> error('非法进入此页面！');
        }

        //根据发布时间判断该条是否发布过
        if($list['push_datetime'] != '0000-00-00 00:00:00'){
            //加载推送方法
            include(THINK_PATH.'Common/JiPush.php');
            $this->JiPush = new JiPush();

            //根据schedule_id查询推送信息
            $list_schedules = $this->JiPush->schedule_del($list['schedule_id']);


            $res = json_decode($list_schedules,true);
            if(isset($res['error'])) {
                echo "<script>alert('删除失败！');history.go(-1);</script>";
                $this->error('删除失败！');
            }
        }


        //执行删除
        $res = $Model -> table('sixty_tongzhi') -> where("id='" . $id . "'") -> delete();

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //判断结果
        if ($res) {
            //成功返回成功
            echo "<script>alert('删除成功!');window.location.href='" . __APP__ . '/Notice/indextiming' . $echourl . "';</script>";
            $this->success('删除成功!', '__APP__' . $echourl);
        } else {
            echo "<script>alert('删除失败！');history.go(-1);</script>";
            $this->error('删除失败！');

        }
    }

    public function fabu_timing(){

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
    public function func_jgpush($jiguangid,$messagee,$m_type='',$m_txt='',$m_time='86400', $push_type=''){

        //极光推送的设置
        /* $m_type = '';//推送附加字段的类型
        $m_txt = '';//推送附加字段的类型对应的内容(可不填) 可能是url,可能是一段文字。
        $m_time = '86400';//离线保留时间 */
//        $receive = array('alias'=>array($jiguangid));//别名
        $receive = array();//别名
        $receive['alias'] = $jiguangid;//别名
//        $receive = array('alias'=>array('7ae4bf35d36b17d2922bf77c261e354c'));//别名

        $content = $messagee;
        //$message="";//存储推送状态
        if($push_type == 1){
            $result = $this->JiPush->push($receive,$content,$m_type,$m_txt,$m_time);
        }else if($push_type == 2){
            $result = $this->JiPush->timing_push($receive,$content,$m_type,$m_txt,$m_time);
        }


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


    public function downlist($arr, $lock='', $flag=''){

        //        动态下拉列表、
        //start--------------------------------------------------------------
        //动态生成权限下拉选项
//        var_dump($arr);die;
        $res_arr = '';
        if($arr != '') {
            foreach ($arr as $keyr => $valr) {
                $res_arr .= '<option value="' . $keyr . '" ';
                if($flag) {
                    $con = $valr;
                } else {
                    $con = $keyr;
                }
                if($con == $lock) {
                    $res_arr .= ' selected="selected"';
                }
                $res_arr .= '>' . $valr . '</option>';
            }
        }else{
            $res_arr = "<option selected='selected'>无</option>";
        }
        return $res_arr;

        //end--------------------------------------------------------------
    }


    public function linkage(){
        //接收上传数据
        $top = trim($this->_post('top'));
        $Model = new Model();
        //如果等于1，是文字，二级菜单为空
        if($top == 1){
            $list_middle = json_encode('<option selected=\'selected\'>无</option>');
            echo $list_middle;
        }else if($top == 2){//等于2，是视频
            //获取全部视频名及id
            $res_video = $Model -> table('sixty_video') -> field('id, biaoti') -> select();

            $video_arr = array();
            //遍历结果集，以id为键名，标题为键值，把数据存入新数组
            if(count($res_video) > 0){
                foreach($res_video as $k_v => $v_v){
                    $video_arr[$v_v['id']] = $v_v['biaoti'];
                }
            }

            $video_arr = $this->downlist($video_arr);
            print_r(json_encode($video_arr));
        }else if($top == 3){//小贴士
            //获取全部视频名及id
            $res_video = $Model -> table('sixty_tieshi_video') -> field('id, biaoti') -> select();

            $video_arr = array();
            //遍历结果集，以id为键名，标题为键值，把数据存入新数组
            if(count($res_video) > 0){
                foreach($res_video as $k_v => $v_v){
                    $video_arr[$v_v['id']] = $v_v['biaoti'];
                }
            }

            $video_arr = $this->downlist($video_arr);
            print_r(json_encode($video_arr));
        }else if($top == 4){//合集
            //获取全部视频名及id
            $res_video = $Model -> table('sixty_jihemsg') -> field('id, name as biaoti') -> select();

            $video_arr = array();
            //遍历结果集，以id为键名，标题为键值，把数据存入新数组
            if(count($res_video) > 0){
                foreach($res_video as $k_v => $v_v){
                    $video_arr[$v_v['id']] = $v_v['biaoti'];
                }
            }

            $video_arr = $this->downlist($video_arr);
            print_r(json_encode($video_arr));
        }


    }

}
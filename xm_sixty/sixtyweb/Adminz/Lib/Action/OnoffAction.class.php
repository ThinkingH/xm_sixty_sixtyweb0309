<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/30
 * Time: 17:11
 * APP开关控制后台
 */

class OnoffAction extends Action {

    //定义各模块锁定级别
    private $lock_index         = '97';
    private $lock_addOnoff_do   = '9';
    private $lock_addOnoff   = '9';
    private $lock_editOnoff   = '9';
    private $lock_editOnoff_do   = '9';
    private $lock_delOnoff_do   = '9';


    //首页展示列表
    public function index(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //拼接URL地址，并返回到页面
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //实例化方法
        $Model = new Model();

        //获取开关表数据
        $list_on = $Model -> table('sixty_on_off') -> field('id, name, flag, remark, version, create_datetime') -> select();

        foreach($list_on as $k_l => $v_l){
            if($v_l['flag'] == 2){
                $list_on[$k_l]['flag'] = '<span style="background-color:#FF82A5;padding:3px;">2-关闭</span>';
            }else if($v_l['flag'] == 1){
                $list_on[$k_l]['flag'] = '<span style="background-color:#33FF66;padding:3px;">1-开启</span>';
            }
        }

        $this->assign('list',$list_on);
        $this->display();



    }


    //跳转添加页面
    public function addOnoff(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addOnoff);
        //拼接URL地址，并返回到页面
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        $flag_arr = array(
            1 => '开启',
            2 => '关闭',
        );

        $list_flag = $this->downlist($flag_arr,2);

        $this->assign('list_flag',$list_flag);
        $this->display();




    }



    public function addOnoff_do(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addOnoff_do);
        //拼接URL地址，并返回到页面
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //获取上传数据
        $name = trim($this->_post('name'));
        $flag = trim($this->_post('flag'));
        $version = trim($this->_post('version'));
        $remark = trim($this->_post('remark'));

        if($name == ''){
            echo "<script>alert('配置名不能为空！');history.go(-1);</script>";
            $this -> error('配置名不能为空！');
        }

        if($version == ''){
            echo "<script>alert('适用版本号不能为空！');history.go(-1);</script>";
            $this -> error('适用版本号不能为空！');
        }

        if($flag == ''){
            echo "<script>alert('状态不能为空！');history.go(-1);</script>";
            $this -> error('状态不能为空！');
        }


        //实例化方法
        $Model = new Model();

        //查询该名称是否存在
        $res_name = $Model -> table('sixty_onoff') -> field('name') -> where("name = '".$name."'") -> find();

        if($res_name){
            echo "<script>alert('此名称已存在！');history.go(-1);</script>";
            $this -> error('此名称已存在！');
        }


        //准备插入数组
        $ins_arr = array(
            'name' => $name,
            'flag' => $flag,
            'version' => $version,
            'remark' => $remark,
            'create_datetime' => date('Y-m-d H:i:s', time()),
        );

        //添加数据到数据库
        $res = $Model -> table('sixty_on_off') -> add($ins_arr);
//var_dump($Model->getLastSql());die;
        if($res){
            echo "<script>alert('添加成功！');window.location.href=window.location.href='".__APP__."/Onoff/index".$echourl."';</script>";
            $this -> error('添加成功！');
        }else{
            echo "<script>alert('添加失败！');history.go(-1);</script>";
            $this -> error('添加失败！');
        }



    }


    //编辑开关方法
    public function editOnoff(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editOnoff);
        //拼接URL地址，并返回到页面
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=


        //获取上传数据
        $id = trim($this->_post('id'));//id

        //判断上传数据是否为空
        if($id == ''){
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }


        //实例化方法
        $Model = new Model();

        //根据ID查询开关表数据
        $res_on = $Model -> table('sixty_on_off') -> field('id, name, flag, remark, version, create_datetime')
            -> where("id = '".$id."'") -> find();

        if(!$res_on){
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }


        //获取开启关闭下拉列表
        $flag_arr = array(
            1 => '开启',
            2 => '关闭',
        );

        $res_on['flag'] = $this->downlist($flag_arr,$res_on['flag']);


        //输出到模板
        $this->assign('list',$res_on);
        $this->display();


    }


    //执行开关编辑
    public function editOnoff_do(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editOnoff_do);
        //拼接URL地址，并返回到页面
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=


        //接收上传数据
        $id = trim($this->_post('id'));
        $name = trim($this->_post('name'));
        $flag = trim($this->_post('flag'));
        $remark = trim($this->_post('remark'));
        $version = trim($this->_post('version'));


        //判断上传数据
        if($id == ''){
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }

        if($name == ''){
            echo "<script>alert('配置名不能为空！');history.go(-1);</script>";
            $this -> error('配置名不能为空！');
        }

        if($version == ''){
            echo "<script>alert('适用版本号不能为空！');history.go(-1);</script>";
            $this -> error('适用版本号不能为空！');
        }

        if($flag == ''){
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }


        //准备更新数组
        $date_arr = array(
            'version' => $version,
            'flag' => $flag,
            'name' => $name,
            'remark' => $remark,
        );

        //实例化方法
        $Model = new Model();

        //根据id执行更新开关表数据
        $res = $Model -> table('sixty_on_off') -> where("id = '".$id."'") -> save($date_arr);


        //判断更新结果
        if($res){
            echo "<script>alert('更新成功！');window.location.href=window.location.href='".__APP__."/Onoff/index".$echourl."';</script>";
            $this -> error('更新成功！');
        }else{
            echo "<script>alert('更新失败！');history.go(-1);</script>";
            $this -> error('更新失败！');
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



}
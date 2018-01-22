<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/6
 * Time: 17:49
 */

class CookBookAction extends Action {
    //定义各模块锁定级别
    private $lock_index         = '97';
    private $lock_delclass_do   = '97';
    private $lock_addclass     = '97';
    private $lock_addclass_do   = '97';
    private $lock_editclass   = '97';
    private $lock_editclass_do  = '97';


    public function index() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收查询条件
        $find_id = trim($this->_get('find_id'));
        $find_state = trim($this->_get('find_state'));
        $find_biaoti = trim($this->_get('find_biaoti'));
        $find_sta_date = trim($this->_get('find_sta_date'));
        $find_end_date = trim($this->_get('find_end_date'));



        $where = "state <> 0";
        if($find_id != ''){
            $where .= " and id = '".$find_id."'";
        }

        if($find_state != '' && $find_state != 0){
            $where .= " and state = '".$find_state."'";
        }

        if($find_biaoti != ''){
            $where .= " and biaoti like '".$find_biaoti."%'";
        }

        if($find_sta_date != ''){
            $where .= " and a.create_datetime >= '".$find_sta_date." 00:00:00'";
        }

        if($find_end_date != ''){
            $where .= " and a.create_datetime <= '".$find_end_date." 23:59:59'";
        }

        $find_where = array(
            'find_id' => $find_id,
            'find_state' => $find_state,
            'find_biaoti' => $find_biaoti,
            'find_sta_date' => $find_sta_date,
            'find_end_date' => $find_end_date,
        );

        $this->assign('find_where', $find_where);


        $state_arr = array(
            '0' => '0-全选',
            '1' => '1-已通过',
            '2' => '2-待审核',
            '3' => '3-不合格',
        );

        $find_state_arr = $this->downlist($state_arr, $find_state);
        $this->assign('find_state_arr', $find_state_arr);


        $Model = new Model();

        //分页
        import('ORG.Page');// 导入分页类
        $count = $Model->table('sixty_cookbook')
            ->where($condition)
            ->count();// 查询满足要求的总记录数
        $Page = new Page($count, 50);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('page', $show);// 赋值分页输出
        $result = $Model -> table('sixty_cookbook as a') -> field('a.id, a.showimg, a.biaoti, a.state, 
                    a.create_datetime, a.abstract, b.nickname')
            ->where($where) -> join("sixty_user as b ON a.user_id = b.id")
            -> limit($Page->firstRow . ',' . $Page->listRows) -> select();
//var_dump($Model->getLastSql());die;

        if($result == '') {
            $this -> display();
            exit;
        }

        foreach($result as $k_res => $v_res) {
            if($v_res['state'] == 1) {
                $result[$k_res]['state'] = '<span style="background-color:#33FF66;padding:3px;">1-已通过</span>';
            }else if($v_res['state'] == 2) {
                $result[$k_res]['state'] = '<span style="background-color:#FFFF00;padding:3px;">2-待审核</span>';
            }else if($v_res['state'] == 3) {
                $result[$k_res]['state'] = '<span style="background-color:#FF82A5;padding:3px;">3-不合格</span>';
            }

            //获取七牛云图片
            $showimg = $v_res['showimg'];
            $imgwidth = '100';
            $imgheight = '100';
            $addressimg = hy_qiniuimgurl('sixty-imgpinglun',$showimg,$imgwidth,$imgheight);
            $result[$k_res]['showimg'] = "<img src='" . $addressimg . "' />";
        }

        $this -> assign('data', $result);
        $this -> display();

    }


    public function detail() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=


        //接收提交数据
        $c_id = trim($this->_post('c_id'));//食谱ID

        if($c_id == '') {
            echo "<script>alert('非法进入');history.go(-1);</script>";
            $this -> error('非法进入');
        }

        //实例化方法
        $Model = new Model();

        $res_cook = $Model -> table('sixty_cookbook as a') -> field('a.id, a.showimg, a.biaoti, a.state, 
                    a.create_datetime, a.abstract, a.keypoint, a.money, a.maketime, b.nickname')
             -> join("sixty_user as b ON a.user_id = b.id")->where("a.id = '".$c_id."'")
            -> find();

        if(is_array($res_buzhou) && count($res_buzhou) > 0) {
            //获取七牛云图片
            $showimg = $res_cook['showimg'];
            $imgwidth = '100';
            $imgheight = '100';
            $addressimg = hy_qiniuimgurl('sixty-imgpinglun', $showimg, $imgwidth, $imgheight);
            $res_cook['showimg'] = "<img src='" . $addressimg . "' />";
        }
//var_dump($res_cook);die;
        $food_arr=array(
            0 => array('name'=>'牛肉','uselevel'=>'400g'),
            1 => array('name'=>'西红柿','uselevel'=>'100g'),
            2 => array('name'=>'葱','uselevel'=>'50g'),
            3 => array('name'=>'酱油','uselevel'=>'20g'),
            4 => array('name'=>'盐','uselevel'=>'10g'),
        );
//        $food_arr = json_encode($res_cook['food']);

        foreach($food_arr as $k_f => $v_f){

            $res_cook['food'] .= "<div style='width: 200px; height: 30px; float: left;'>".$v_f['name'].'——'.$v_f['uselevel'].'</div>';
        }
//        var_dump($food_str);die;

        $where = "cook_id = '".$c_id."'";

        $res_buzhou = $Model -> table('sixty_cookbook_buzhou') -> field('picture, word, sort, create_datetime')
            -> where($where) -> order("sort asc") -> select();

        if(is_array($res_buzhou) && count($res_buzhou) > 0){
            foreach($res_buzhou as $k_bz => $v_bz){
                //获取七牛云图片
                $showimg = $v_bz['picture'];
                $imgwidth = '100';
                $imgheight = '100';
                $addressimg = hy_qiniuimgurl('sixty-imgpinglun',$showimg,$imgwidth,$imgheight);
                $res_buzhou[$k_bz]['picture'] = "<img src='" . $addressimg . "' />";
            }

        }
//        var_dump($res_buzhou);die;
        $this -> assign('data', $res_cook);
        $this -> assign('data_bz', $res_buzhou);
        $this -> display();
    }


    public function audit(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收提交数据
        $c_id = trim($this->_post('c_id'));//食谱id
        $state = trim($this->_post('state'));//审核结果

        //判断数据来源
        if($c_id == ''){
            //非法进入
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }

        if($state == ''){
            //非法进入
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }


        $update_arr = array('state' => $state);
        $Model = new Model();
        $res = $Model -> table('sixty_cookbook') -> where("id = ".$c_id) -> save($update_arr);
//        var_dump($Model->getLastSql());die;
        if($res){
            //返回成功
            echo "<script>alert('审核修改成功!');window.location.href='".__APP__.'/CookBook/index'. $echourl ."';</script>";
            $this -> success('审核修改成功!','__APP__'.$echourl);
        }else{
            echo "<script>alert('审核修改失败!');history.go(-1);</script>";
            $this -> error('审核修改失败!');
        }
    }



    public function del_cook_do(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收提交数据
        $c_id = trim($this->_post('del_id'));//食谱ID

        //判断数据是否为空
        if($c_id == ''){
            echo "<script>alert('非法进入!');history.go(-1);</script>";
            $this->error('非法进入!');
        }


        //实例化方法
        $Model = new Model();
        //获取图片名称
        $res_cook = $Model -> table('sixty_cookbook') -> field('id, showimg') -> where("id ='".$c_id."'") -> find();

        if(count($res_cook) == 0){
            echo "<script>alert('该食谱不存在!');history.go(-1);</script>";
            $this->error('该食谱不存在!');
        }

        //删除七牛云视频旧图片
        delete_qiniu('sixty-imgpinglun', $res_cook['showimg']);

        $res_buzhou = $Model -> table('sixty_cookbook_buzhou') -> field('id, picture')
            -> where("cook_id = '".$c_id."'") ->select();

        if(count($res_buzhou) > 0){
            foreach($res_buzhou as $k_bz => $v_bz){
                //删除七牛云视频旧图片
                delete_qiniu('sixty-imgpinglun', $v_bz['picture']);
            }
        }

        //删除数据
        $res_del_bz = $Model -> table('sixty_cookbook_buzhou') -> where("cook_id ='".$c_id."'") -> delete();
        $res_del_cook = $Model -> table('sixty_cookbook') -> where("id ='".$c_id."'") -> delete();

        if($res_del_cook && $res_del_bz){
            //返回成功
            echo "<script>alert('删除成功!');window.location.href='".__APP__.'/CookBook/index'. $echourl ."';</script>";
            $this -> success('删除成功!','__APP__'.$echourl);
        }else{
            echo "<script>alert('删除失败!');history.go(-1);</script>";
            $this -> error('删除失败!');
        }
    }



    public function downlist($arr, $lock=''){

        //        动态下拉列表、
        //start--------------------------------------------------------------
        //动态生成权限下拉选项
//        var_dump($arr);die;
        $res_arr = '';
        if($arr != '') {
            foreach ($arr as $keyr => $valr) {
                $res_arr .= '<option value="' . $keyr . '" ';
                if ($keyr == $lock) {
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
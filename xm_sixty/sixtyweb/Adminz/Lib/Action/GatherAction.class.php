<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/2
 * Time: 11:46
 */

class GatherAction extends Action{
    //定义各模块锁定级别
    private $lock_index = '9';
    private $lock_addgat = '9';
    private $lock_addgat_do = '9';
    private $lock_editgat_do = '9';
    private $lock_editgat = '9';
    private $lock_delgat_do = '9';

    public function index(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //获取查询信息
        $gat_id = trim($this->_get('find_id'));
        $gat_name = trim($this->_get('find_name'));
        $gat_find_sta_date = trim($this->_get('find_sta_date'));
        $gat_find_end_date = trim($this->_get('find_end_date'));

        //判断起始日期是否有传值
        $sta_minute = ' 00:00:00';
        if($gat_find_sta_date == ''){
            $gat_find_sta_date = date('Y-m-d',strtotime('-6 month'));
            $where_sta_date = $gat_find_sta_date . $sta_minute;
        }else{
            $where_sta_date = $gat_find_sta_date . $sta_minute;
        }
        //判断结束日期是否有传值
        $end_minute = ' 59:59:59';
        if($gat_find_end_date == ''){
            $gat_find_end_date = date('Y-m-d',time());
            $where_end_date = $gat_find_end_date . $end_minute;
        }else{
            $where_end_date = $gat_find_end_date . $end_minute;
        }

        //准备查询条件
        $where = "create_datetime >= '" . $where_sta_date . "' and " . "create_datetime <= '" . $where_end_date . "'";

        //判断查询条件
        //判断ID是否不为空
        if($gat_id != ''){
            $where .= " and id = '". $gat_id . "'";
        }
        //判断名称是否不为空
        if($gat_name != ''){
            $where .= "and name like '%" . $gat_name . "%'";
        }

        //返回查询条件
        $find_where = array(
            'find_id' => $gat_id,
            'find_name' => $gat_name,
            'find_sta_date' => $gat_find_sta_date,
            'find_end_date' => $gat_find_end_date,
        );
        $this->assign('find_where', $find_where);

        //准备查询数组
        $Model = new Model();
        //查询集合数据表
        $list = $Model -> table('sixty_jihemsg') -> field('id, name, showimg, content, remark, create_datetime')
            -> where($where) -> order('create_datetime desc') -> select();

        $this->assign('list',$list);
        $this->display();
    }

    public function addgat(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addgat);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //输出模板
        $this->display();
    }

    public function addgat_do(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addgat_do);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $name = trim($this->_post('name'));
        $content = trim($this->_post('content'));
        $remark = trim($this->_post('remark'));
        $submit = trim($this->_post('submit'));

        //判断上传数据是否为空
        if($submit == ''){
            echo "<script>alert('非法进入此页面!');history.go(-1);</script>";
            $this -> error('非法进入此页面!');
        }
        if($name == ''){
            echo "<script>alert('合集名称不能为空');history.go(-1);</script>";
            $this -> error('合集名称不能为空!');
        }
        if($content == ''){
            echo "<script>alert('合集描述不能为空!');history.go(-1);</script>";
            $this -> error('合集描述不能为空!');
        }

        //判断图片是否上传
        if($_FILES['showimg'] != '')
        {
            import('ORG.UploadFile');
            $upload = new UploadFile();// 实例化上传类
            $upload->maxSize  = 3145728 ;// 设置附件上传大小
            $upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath =  BASEDIR.'Public/Images/gather_showimg//';// 设置附件上传目录
            if(!$upload->upload()) {// 上传错误提示错误信息
                echo "<script>alert('图片上传失败!');history.go(-1);</script>";
                $this -> error('图片上传失败!');
            }else{// 上传成功 获取上传文件信息
                $info =  $upload->getUploadFileInfo();
                $showimg = $info['0']['savename'];
            }
        }else{
            $showimg = '';
        }

        //准备插入数组
        $create_timedate = date('Y-m-d H:i:s', time());
        $data = array(
            'name' => $name,
            'content' => $content,
            'remark' => $remark,
            'showimg' => $showimg,
            'create_datetime' => $create_timedate,
        );

        //执行插入
        $Model = new Model();
        $res = $Model -> table('sixty_jihemsg') -> add($data);

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);
        //判断插入结果
        if($res){
            //返回成功
            echo "<script>alert('合集添加成功!');window.location.href='".__APP__.'/Gather/index'. $echourl ."';</script>";
            $this -> success('合集添加成功!','__APP__'.$echourl);

        }else{
            unlink(BASEDIR.'tmpimage/'.$showimg);
            echo "<script>alert('合集添加失败!');history.go(-1);</script>";
            $this -> error('合集添加失败!');
        }
    }

    public function editgat(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editgat);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        $id = trim($this->_post('edit_id'));

        if($id == ''){
            echo "<script>alert('非法进入此页面!');history.go(-1);</script>";
            $this -> error('非法进入此页面!');
        }
        $Model = new Model();
        $list = $Model -> table('sixty_jihemsg') -> field('id, name, showimg, content, remark')
            -> where("id='".$id."'") -> find();

        $this->assign('list', $list);
        $this->display();
    }

    public function editgat_do(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editgat_do);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $id = trim($this->_post('edit_id'));
        $name = trim($this->_post('edit_name'));
        $content = trim($this->_post('edit_content'));
        $remark = trim($this->_post('edit_remark'));
        $submit = trim($this->_post('submit'));

        //判断上传数据是否为空
        if($submit == ''){
            echo "<script>alert('非法进入此页面!');history.go(-1);</script>";
            $this -> error('非法进入此页面!');
        }
        if($id == ''){
            echo "<script>alert('非法进入此页面!');history.go(-1);</script>";
            $this -> error('非法进入此页面!');
        }
        if($name == ''){
            echo "<script>alert('合集名称不能为空');history.go(-1);</script>";
            $this -> error('合集名称不能为空!');
        }
        if($content == ''){
            echo "<script>alert('合集描述不能为空!');history.go(-1);</script>";
            $this -> error('合集描述不能为空!');
        }

        //根据ID 查找数据
        $Model = new Model();
        $res_old = $Model -> table('sixty_jihemsg') -> field('id,showimg') -> where("id = '" . $id . "'") -> find();
        if($res_old == '')
        {
            echo "<script>alert('非法进入此页面!');history.go(-1);</script>";
            $this -> error('非法进入此页面!');
        }

        //判断图片是否上传
        if($_FILES['edit_showimg']['name'] != '')
        {
            import('ORG.UploadFile');
            $upload = new UploadFile();// 实例化上传类
            $upload->maxSize  = 3145728 ;// 设置附件上传大小
            $upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath =  BASEDIR.'Public/Images/gather_showimg/';// 设置附件上传目录
            if(!$upload->upload()) {// 上传错误提示错误信息
                echo "<script>alert('图片上传失败!');history.go(-1);</script>";
                $this -> error('图片上传失败!');
            }else{// 上传成功 获取上传文件信息
                $info =  $upload->getUploadFileInfo();
                $showimg_new = $info['0']['savename'];
                //准备插入数组
                $data = array(
                    'name' => $name,
                    'content' => $content,
                    'remark' => $remark,
                    'showimg' => $showimg_new,
                );
            }
        }else{
            //准备插入数组
            $data = array(
                'name' => $name,
                'content' => $content,
                'remark' => $remark,
            );
        }


        //执行插入
        $Model = new Model();
        $res = $Model -> table('sixty_jihemsg') -> where("id='".$id."'") -> save($data);

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //判断结果
        if($res && $_FILES['edit_showimg']['name']){//插入成功，图片上传成功
            //删除旧图片
            $un = unlink(BASEDIR.'Public/Images/gather_showimg/'.$res_old['showimg']);
            //判断旧图片是否删除成功
            if(!$un){
                echo "<script>alert('合集修改成功!');window.location.href='".__APP__.'/Gather/index'. $echourl ."';</script>";
                $this -> success('合集修改成功!','__APP__'.$echourl);
            }else{
                echo "<script>alert('合集修改成功!');window.location.href='".__APP__.'/Gather/index'. $echourl ."';</script>";
                $this -> success('合集修改成功!','__APP__'.$echourl);
            }

        }else if(!$res && $_FILES['edit_showimg']['name']){//插入失败，图片上传成功
            //删除新图
            $un = unlink(BASEDIR.'Public/Images/gather_showimg/'.$showimg_new);
            if(!$un){//判断新图是否删除成功
                echo "<script>alert('合集修改成功!');window.location.href='".__APP__.'/Gather/index'. $echourl ."';</script>";
                $this -> success('合集修改成功!','__APP__'.$echourl);
            }else{
                echo "<script>alert('合集修改成功!');window.location.href='".__APP__.'/Gather/index'. $echourl ."';</script>";
                $this -> success('合集修改成功!','__APP__'.$echourl);
            }

        }else if(!$res && !$_FILES['edit_showimg']['name']){//插入失败，图片上传失败

            echo "<script>alert('合集修改失败!');history.go(-1);</script>";
            $this -> error('合集修改失败!');

        }else if($res && !$_FILES['edit_showimg']['name']){//数据修改成功，图片上传失败

            echo "<script>alert('合集数据修改成功，图片上传失败!');window.location.href='".__APP__.'/Gather/index'. $echourl ."';</script>";
            $this -> success('合集数据修改成功，图片上传失败!','__APP__'.$echourl);
        }
    }

    public function delgat_do(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_delgat_do);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //接收上传数据
        $id = trim($this->_post('del_id'));
        $submit = trim($this->_post('submitdel'));

        //判断上传数据来源
        if(!$id || !$submit)
        {
            echo "<script>alert('非法进入此页面!');history.go(-1);</script>";
            $this -> error('非法进入此页面!');
        }

        $Model = new Model();
        //查询ID是否存在
        $res_showimg = $Model -> table('sixty_jihemsg') -> field('showimg') -> where("id='".$id."'") -> find();
        if($res_showimg == '')
        {
            echo "<script>alert('非法进入此页面!');history.go(-1);</script>";
            $this -> error('非法进入此页面!');
        }

        $res = $Model -> table('sixty_jihemsg') -> where("id='".$id."'") -> delete();

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        if($res != ''){
            //删除图片
            $un = unlink(BASEDIR.'Public/Images/gather_showimg/'.$res_showimg['showimg']);
            if($un == ''){
                echo "<script>alert('合集数据删除成功，图片删除失败!');window.location.href='".__APP__.'/Gather/index'. $echourl ."';</script>";
                $this -> success('合集数据删除成功，图片删除失败!','__APP__'.$echourl);
            }else{
                echo "<script>alert('合集数据删除成功!');window.location.href='".__APP__.'/Gather/index'. $echourl ."';</script>";
                $this -> success('合集数据删除成功!','__APP__'.$echourl);
            }
        }else{
            echo "<script>alert('删除失败!');history.go(-1);</script>";
            $this -> error('删除失败!');
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
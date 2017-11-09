<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/3
 * Time: 9:28
 */
class ClassmsgAction extends Action{
    //定义各模块锁定级别
    private $lock_index = '7';
    private $lock_addclassmsg = '7';
    private $lock_addclassmsg_do = '7';
    private $lock_editclassmsg_do = '7';
    private $lock_editclassmsg = '7';
    private $lock_delclassmsg_do = '7';

    public function index(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $class_id = trim($this->_get('find_id'));
        $class_name = trim($this->_get('find_name'));
        $class_childname = trim($this->_get('find_childname'));

        //准备查询条件
        $where = "";
        //判断查询条件
        //判断ID是否不为空
        if($class_id != '' && $where !=''){
            $where .= " and id = '". $class_id . "'";
        }else if($class_id != '' && $where ==''){
            $where .= "id = '". $class_id . "'";
        }
        //判断名称是否不为空
        if($class_name != '' && $where !=''){
            $where .= "and name like '%" . $class_name . "%'";
        }else if($class_name != '' && $where ==''){
            $where .= "name like '%" . $class_name . "%'";
        }
        //判断子名称是否不为空
        if($class_childname != '' && $where !=''){
            $where .= "and childname like '%" . $class_childname . "%'";
        }else if($class_childname != '' && $where ==''){
            $where .= "childname like '%" . $class_childname . "%'";
        }

        //返回查询条件
        $find_where = array(
            'find_id' => $class_id,
            'find_name' => $class_name,
            'find_childname' => $class_childname,
        );
        $this->assign('find_where', $find_where);

        //准备查询数组
        $Model = new Model();
        //查询集合数据表
        $list = $Model -> table('sixty_classifymsg') -> field('id, level, name, childname, content, remark')
            -> where($where) -> order('id desc') -> select();
        $this->assign('list',$list);
        $this->display();

    }

    public function addclassmsg(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addclassmsg);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        // 动态下拉列表
        //start--------------------------------------------------------------
        //动态生成权限下拉选项
        $levelarr = array(
            '1' => '一级',
            '2' => '二级',
            '3' => '三级',
            '4' => '四级',
        );

        $level_show = '';
        foreach($levelarr as $keyr => $valr) {
            $level_show .= '<option value="'.$keyr.'" ';
            if($keyr==$flag) {
                $level_show .= ' selected="selected"';
            }
            $level_show .= '>'.$valr.'</option>';

        }
        $this -> assign('level_show',$level_show);
        //end--------------------------------------------------------------
        //输出模板
        $this->display();
    }

    public function addclassmsg_do(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addclassmsg_do);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $name = trim($this->_post('name'));
        $childname = trim($this->_post('childname'));
        $content = trim($this->_post('content'));
        $level = trim($this->_post('level'));
        $remark = trim($this->_post('remark'));

        //判断传值是否为空
        if($name == ''){
            echo "<script>alert('分类名称不能为空');history.go(-1);</script>";
            $this -> error('分类名称不能为空!');
        }
        if($childname == ''){
            echo "<script>alert('子分类名称不能为空');history.go(-1);</script>";
            $this -> error('子分类名称不能为空!');
        }
        if($content == ''){
            echo "<script>alert('描述不能为空');history.go(-1);</script>";
            $this -> error('描述不能为空!');
        }

        //准备插入数组
        $data = array(
            'name' => $name,
            'childname' => $childname,
            'content' => $content,
            'remark' => $remark,
            'level' => $level,
        );

        //实例化模型
        $Model = new Model();
        $result = $Model -> table('sixty_classifymsg') -> add($data);

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //判断插入结果
        if($result){
            echo "<script>alert('分类添加成功!');window.location.href='".__APP__.'/Classmsg/index'. $echourl ."';</script>";
            $this -> success('分类添加成功!','__APP__'.$echourl);
        }else{
            echo "<script>alert('分类添加失败');history.go(-1);</script>";
            $this -> error('分类添加失败!');
        }

    }

    public function editclassmsg(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editclassmsg);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $id = trim($this->_post('edit_id'));

        //判断ID是否上传
        if($id == ''){
            echo "<script>alert('非法进入此页面');history.go(-1);</script>";
            $this -> error('非法进入此页面');
        }

        //实例化模型
        $Model = new Model();

        //查询此ID是否存在
        $list = $Model -> table('sixty_classifymsg') -> field('id, name, childname, content, remark')
            -> where("id='".$id."'") -> find();

        if($list == ''){
            echo "<script>alert('非法进入此页面');history.go(-1);</script>";
            $this -> error('非法进入此页面');
        }

        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        // 动态下拉列表
        //start--------------------------------------------------------------
        //动态生成权限下拉选项
        $levelarr = array(
            '1' => '一级',
            '2' => '二级',
            '3' => '三级',
            '4' => '四级',
        );

        $level_show = '';
        foreach($levelarr as $keyr => $valr) {
            $level_show .= '<option value="'.$keyr.'" ';
            if($keyr==$flag) {
                $level_show .= ' selected="selected"';
            }
            $level_show .= '>'.$valr.'</option>';

        }
        $this -> assign('level_show',$level_show);
        //end--------------------------------------------------------------

        //输出模板
        $this->assign('list', $list);
        $this->display();
    }

    public function editclassmsg_do(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editclassmsg_do);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $id = trim($this->_post('edit_id'));
        $name = trim($this->_post('edit_name'));
        $childname = trim($this->_post('edit_childname'));
        $content = trim($this->_post('edit_content'));
        $level = trim($this->_post('edit_remark'));
        $remark = trim($this->_post('edit_level'));

        //判断传值是否为空
        if($name == ''){
            echo "<script>alert('分类名称不能为空');history.go(-1);</script>";
            $this -> error('分类名称不能为空!');
        }
        if($childname == ''){
            echo "<script>alert('子分类名称不能为空');history.go(-1);</script>";
            $this -> error('子分类名称不能为空!');
        }
        if($content == ''){
            echo "<script>alert('描述不能为空');history.go(-1);</script>";
            $this -> error('描述不能为空!');
        }

        //准备插入数组
        $data = array(
            'name' => $name,
            'childname' => $childname,
            'content' => $content,
            'remark' => $remark,
        );
        //实例化模型
        $Model = new Model();
        $result = $Model -> table('sixty_classifymsg') -> where("id='".$id."'") -> save($data);

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //判断结果
        if($result){
            echo "<script>alert('分类修改成功!');window.location.href='".__APP__.'/Classmsg/index'. $echourl ."';</script>";
            $this -> success('分类修改成功!','__APP__'.$echourl);
        }else{
            echo "<script>alert('分类修改失败');history.go(-1);</script>";
            $this -> error('分类修改失败!');
        }
    }

    public function delclassmsg_do(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_delclassmsg_do);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $id = trim($this->_post('del_id'));
        $submit = trim($this->_post('submitdel'));

        //判断上传数据是否为空
        if($id == ''){
            echo "<script>alert('非法进入此页面');history.go(-1);</script>";
            $this -> error('非法进入此页面');
        }

        if($submit == ''){
            echo "<script>alert('非法进入此页面');history.go(-1);</script>";
            $this -> error('非法进入此页面');
        }

        $Model = new Model();
        //执行删除
        $result = $Model -> table('sixty_classifymsg') -> where("id='".$id."'") -> delete();

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //判断结果
        if($result){
            echo "<script>alert('分类删除成功!');window.location.href='".__APP__.'/Classmsg/index'. $echourl ."';</script>";
            $this -> success('分类删除成功!','__APP__'.$echourl);
        }else{
            echo "<script>alert('分类删除失败');history.go(-1);</script>";
            $this -> error('分类删除失败!');
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
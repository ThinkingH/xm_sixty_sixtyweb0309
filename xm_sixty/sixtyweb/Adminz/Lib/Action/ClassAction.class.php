<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/3
 * Time: 9:28
 */
class ClassAction extends Action{
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
        $list = $Model -> table('sixty_classifymsg') -> field('id, name, childname, content, remark')
            -> where($where) -> order('id desc') -> select();
        $this->assign('list',$list);
        $this->display();

    }

    public function addclass(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //输出模板
        $this->display();
    }

    public function addclass_do(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $name = trim($this->_post('name'));
        $childname = trim($this->_post('childname'));
        $content = trim($this->_post('content'));
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
        );

        //实例化模型
        $Model = new Model();
        $result = $Model -> table('sixty_classifymsg') -> add($data);

        //判断插入结果
        if($result){
            echo "<script>alert('合集添加成功!');window.location.href='".__APP__.'/Class/index'. $echourl ."';</script>";
            $this -> success('合集添加成功!','__APP__'.$echourl);
        }else{
            echo "<script>alert('分类添加失败');history.go(-1);</script>";
            $this -> error('分类添加失败!');
        }

    }

    public function editclass(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
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

        //输出模板
        $this->assign('list', $list);
        $this->display();
    }

    public function editclass_do(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $name = trim($this->_post('edit_name'));
        $childname = trim($this->_post('edit_childname'));
        $content = trim($this->_post('edit_content'));
        $remark = trim($this->_post('edit_remark'));

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
        var_dump($result);die;
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
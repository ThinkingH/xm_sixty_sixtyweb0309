<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/5
 * Time: 10:17
 */

class TipsClassAction extends Action {
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
        $find_name = trim($this->_get('find_name'));


        //把条件输出到页面
        $find_where = array(
            'find_id' => $find_id,
            'find_name' => $find_name,
        );

        //输出到模板
        $this -> assign('find_where', $find_where);


        //拼接查询条件
        $where = '';
        if($find_id != '') {
            $where .= "id = '" . $find_id . "' and ";
        }

        if($find_name != '') {
            $where .= "name like '" . $find_name . "%' and ";
        }

        //去掉后四位
        if($where != '') {
            $where = substr($where, 0, -5);
        }


        $Model = new Model();

        //分页
        import('ORG.Page');// 导入分页类
        $count = $Model->table('sixty_tieshi_class')
            ->where($condition)
            ->count();// 查询满足要求的总记录数
        $Page = new Page($count, 50);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('page', $show);// 赋值分页输出


        $data = $Model -> table('sixty_tieshi_class') -> field('id, name, abstract, create_datetime, remark')
            -> where($where) -> order('id desc') -> limit($Page->firstRow . ',' . $Page->listRows) -> select();

        $this -> assign('data', $data);
        $this -> display();
    }


    public function addclass() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addclass);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        $this -> display();
    }


    public function addclass_do() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addclass_do);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=


        //接收上传数据
        $name = trim($this->_post('name'));//名称
        $abstract = trim($this->_post('abstract'));//介绍
        $remark = trim($this->_post('remark'));//备注
        $submit = trim($this->_post('submit'));//来源判断

        $datetime = date('Y-m-d H:i:s', time());//数据创建时间

        //判断数据来源
        if($submit == '') {
            //非法进入
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }

        //判断上传数据是否为空
        if($name == '') {
            //名称不能为空
            echo "<script>alert('名称不能为空！');history.go(-1);</script>";
            $this -> error('名称不能为空！');
        }

        if($abstract == '') {
            //名称不能为空
            echo "<script>alert('介绍不能为空！');history.go(-1);</script>";
            $this -> error('介绍不能为空！');
        }

        //实例化方法
        $Model = new Model();


        //判断分类名是否存在
        $res_name = $Model -> table('sixty_tieshi_class') -> field('id') -> where("name='" . $name . "'") ->find();
        if($res_name != ''){
            echo "<script>alert('此分类名已存在，请使用其他名称');history.go(-1);</script>";
            $this -> error('此分类名已存在，请使用其他名称');
        }

        //组装sql插入数组
        $data = array(
            'name' => $name,
            'abstract' => $abstract,
            'remark' => $remark,
            'create_datetime' => $datetime,
        );


        //执行插入
        $result = $Model -> table('sixty_tieshi_class') -> add($data);


        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);


        //判断插入结果
        if($result){
            //返回成功
            echo "<script>alert('贴士分类添加成功!');window.location.href='".__APP__.'/TipsClass/index'. $echourl ."';</script>";
            $this -> success('贴士分类添加成功!','__APP__'.$echourl);

        }else{
            echo "<script>alert('贴士分类添加失败!');history.go(-1);</script>";
            $this -> error('贴士分类添加失败!');
        }

    }


    public function editclass() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editclass);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $id = trim($this->_post('edit_id'));
        $submit = trim($this->_post('edit_tipsclass'));


        //判断数据来源
        if($submit == '') {
            //非法进入
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }


        //判断数据是否上传
        if($id == '') {
            //非法进入
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }


        //实例化方法
        $Model = new Model();

        //查询条件
        $where = array('id' => $id);

        //根据ID查询数据信息
        $result = $Model -> table('sixty_tieshi_class') -> field('id, name, abstract, remark') -> where($where) -> find();


        //判断查询结果
        if($result == '') {
            //非法进入
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }

        //输出到模板
        $this -> assign('data', $result);
        $this -> display();

    }

    public function editclass_do() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editclass_do);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $id = trim($this->_post('edit_id'));//ID
        $name = trim($this->_post('name'));//名称
        $abstract = trim($this->_post('abstract'));//介绍
        $remark = trim($this->_post('remark'));//备注
        $submit = trim($this->_post('submit'));//来源判断


        //判断数据来源
        if($submit == '') {
            //非法进入
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }

        if($id == '') {
            //非法进入
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }


        //判断上传数据是否为空
        if($name == '') {
            //名称不能为空
            echo "<script>alert('名称不能为空！');history.go(-1);</script>";
            $this -> error('名称不能为空！');
        }

        if($abstract == '') {
            //名称不能为空
            echo "<script>alert('介绍不能为空！');history.go(-1);</script>";
            $this -> error('介绍不能为空！');
        }

        //实例化方法
        $Model = new Model();


        //查询修改的名称是否重复
        //组装查询条件
        $where = array();
        $where['id'] = array('NEQ' => $id);
        $where['name'] = array('EQ' => $name);

        //执行查询
        $result = $Model -> table('sixty_tieshi_class') -> field('id') -> where($where) -> find();

        //判断查询结果
        if($result != '') {
            //非法进入
            echo "<script>alert('分类名重复！');history.go(-1);</script>";
            $this -> error('分类名重复！');
        }


        //组装数据更新数组
        $data = array(
            'name' => $name,
            'abstract' => $abstract,
            'remark' => $remark,
        );

        //执行更新操作
        $result = $Model -> table('sixty_tieshi_class') -> where("id = '" . $id . "'") -> save($data);


        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);


        //判断插入结果
        if($result){
            //返回成功
            echo "<script>alert('贴士分类修改成功!');window.location.href='".__APP__.'/TipsClass/index'. $echourl ."';</script>";
            $this -> success('贴士分类修改成功!','__APP__'.$echourl);

        }else{
            echo "<script>alert('贴士分类修改失败!');history.go(-1);</script>";
            $this -> error('贴士分类修改失败!');
        }
    }


    public function delclass_do() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_delclass_do);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $id = trim($this->_post('del_id'));
        $submit = trim($this->_post('submitdel'));

        //判断数据来源
        if($submit == '') {
            //非法进入
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }

        if($id == '') {
            //非法进入
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }


        //实例化方法
        $Model = new Model();


        //执行删除
        $result = $Model -> table('sixty_tieshi_class') -> where("id = '" .$id. "'") -> delete();


        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);


        //判断删除结果
        if($result != ''){
            echo "<script>alert('数据删除成功!');window.location.href='".__APP__.'/TipsClass/index'. $echourl ."';</script>";
            $this -> success('数据删除成功!','__APP__'.$echourl);
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
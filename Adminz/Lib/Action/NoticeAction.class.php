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

        //分页
        import('ORG.Page');// 导入分页类
        $count = $Model->table('sixty_tongzhi')
            ->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('page', $show);// 赋值分页输出

        //执行查询通知表数据
        $list = $Model -> table('sixty_tongzhi') -> field('id, message, create_datetime')
            -> order('create_datetime desc') -> limit($Page->firstRow . ',' . $Page->listRows) -> select();

        //输出到模板
        $this -> assign('list', $list);
        $this -> display();

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

        //判断数据是否为空
        if($message == '') {
            echo "<script>alert('系统通知不能为空');history.go(-1);</script>";
            $this -> error('系统通知不能为空!');
        }

        //实例化方法
        $Model = new Model();

        //准备添加数组
        $create_datetime = date('Y-m-d H:i:s', time());
        $arr = array(
            'message' => $message,
            'create_datetime' => $create_datetime,
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
        $list = $Model -> table('sixty_tongzhi') -> field('id, message, create_datetime')
            -> where("id='" . $id . "'") -> find();

        //如果没查出数据返回非法进入
        if($list == '') {
            echo "<script>alert('非法进入此页面！');history.go(-1);</script>";
            $this -> error('非法进入此页面！');
        }

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
        $id = trim($this->_post('editnotice_id'));
        $message = trim($this->_post('message'));
        $submitedit = trim($this->_post('submitedit'));

        //判断数据来源
        if ($id == '') {
            echo "<script>alert('非法进入此页面！');history.go(-1);</script>";
            $this->error('非法进入此页面！');
        }

        if ($submitedit == '') {
            echo "<script>alert('非法进入此页面！');history.go(-1);</script>";
            $this->error('非法进入此页面！');
        }


        //实例化方法
        $Model = new Model();

        //准备更新数组
        $arr = array('message' => $message);

        //执行编辑
        $res = $Model->table('sixty_tongzhi')->where("id='" . $id . "'")->save($arr);

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
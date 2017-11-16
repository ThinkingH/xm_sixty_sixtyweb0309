<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/27
 * Time: 9:34
 */

class WebconfigAction extends Action{
    //定义各模块锁定级别
    private $lock_index    = '97';
    private $lock_editconfig  = '97';
    private $lock_editconfig_do = '97';
    private $lock_delconfig_do     = '97';
    private $lock_addconfig       = '97';
    private $lock_addconfig_do     = '97';

    /*
     * 网站配置列表
     * */
    public function index()
    {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //执行查询
        $Model = new Model();
        $list = $Model -> table('sixty_config') -> field('id, name, key1, val1') -> select();

        //输出到模板
        $this -> assign('list', $list);
        $this -> display();
    }

    /*
     * 编辑页面显示
     * */
    public function editconfig()
    {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editconfig);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //接收数据
        $id = trim($this->_post('config_id'));
        $Model = new Model();
        //查询ID是否存在
        if(!empty($id))
        {
            //执行查询
            $res = $Model -> table('sixty_config') -> field('id') -> where("id='".$id."'") -> find();
            //ID没找到
            if(empty($res))
            {
                echo "<script>alert('非法进入此页面');history.go(-1);</script>";
                $this -> error('非法进入此页面');
            }
        }else{
            echo "<script>alert('非法进入此页面');history.go(-1);</script>";
            $this -> error('非法进入此页面');
        }

        //根据ID查询数据
        $list = $Model -> table('sixty_config') -> field('id, name, key1, val1') -> where("id='".$id."'") -> find();

        //输出到模板
        $this->assign('list', $list);
        $this->display();

    }

    /*
     * 执行编辑
     * */
    public function editconfig_do()
    {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editconfig_do);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收提交数据
        $name = trim($this->_post('editconfig_name'));
        $key1 = trim($this->_post('editconfig_key1'));
        $val1 = trim($this->_post('editconfig_val1'));
        $id = trim($this->_post('editconfig_id'));

        //传值不能为空
        if(!$name)
        {
            echo "<script>alert('配置名称不能为空');history.go(-1);</script>";
            $this -> error('配置名称不能为空');
        }

        //传值不能为空
        if(!$key1)
        {
            echo "<script>alert('配置项不能为空');history.go(-1);</script>";
            $this -> error('配置项不能为空');
        }

        //传值不能为空
        if(!$val1)
        {
            echo "<script>alert('配置详情不能为空');history.go(-1);</script>";
            $this -> error('配置详情不能为空');
        }

        if(!$id)
        {
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }


        //正则表达式字符串
        $zhengze = '/^[0-9a-zA-Z\_]+$/';
        //进行匹配
        $res_zheng = preg_match($zhengze,$name);

        //判断匹配结果
        if(!$res_zheng) {
            echo "<script>alert('配置名称不合法!');history.go(-1);</script>";
            $this -> error('配置名称不合法!');
        }


        $Model = new Model();
        //判断ID是否存在
        $res_id= $Model -> table('sixty_config') -> field('id') -> where("id='".$id."'") -> find();

        //查询结果为空
        if(empty($res_id))
        {
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }

        //判断是否重名
        $res_name= $Model -> table('sixty_config') -> field('name') -> where("name='".$name."'") -> find();
        //判断是否查询到配置名
        if($res_name != '')
        {
            echo "<script>alert('配置名已存在！');history.go(-1);</script>";
            $this -> error('配置名已存在！');
        }


        //准备更新数组
        $up_data = array('name' => $name, 'key1' => $key1, 'val1' => $val1);
        $Model = new Model();
        //执行查询
        $result = $Model -> table('sixty_config') -> where("id='".$id."'") -> save($up_data);

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        if($result)
        {
            echo "<script>alert('网站配置修改成功!');window.location.href='".__APP__."/Webconfig/index';</script>";
            $this ->success('网站配置修改成功!','__APP__/Webconfig/index');
        }else{
            echo "<script>alert('网站配置修改失败！');history.go(-1);</script>";
            $this -> error('网站配置修改失败！');
        }
    }

    /*
     * 删除配置信息
     * */
    public function delconfig_do(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_delconfig_do);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-

        //接收提交数据
        $id = trim($this->_post('delconfig_id'));
        $submitdel = trim($this->_post('submitdel'));
        $Model = new Model();

        //判断提交是否为空
        if($submitdel == '')
        {
            echo "<script>alert('非法进入此页面');history.go(-1);</script>";
            $this -> error('非法进入此页面');
        }

        //判断用于id是否为空
        if(empty($id))
        {
            echo "<script>alert('非法进入此页面');history.go(-1);</script>";
            $this -> error('非法进入此页面');
        }

        //判断ID是否存在
        $res_id= $Model -> table('sixty_config') -> field('id') -> where("id='".$id."'") -> find();

        //判断用户id是否存在
        if(!$res_id) {
            echo "<script>alert('非法进入此页面');history.go(-1);</script>";
            $this -> error('非法进入此页面');
        }

        //执行删除操作
        $del_result = $Model -> table('sixty_config') -> where("id='".$id."'") -> delete();

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //判断删除结果
        if($del_result) {
            echo "<script>alert('数据删除成功!');window.location.href='".__APP__."/Webconfig/index';</script>";
            $this -> success('数据删除成功!','__APP__/Webconfig/index');
        }else {
            echo "<script>alert('数据删除失败，系统错误!');history.go(-1);</script>";
            $this -> error('数据删除失败，系统错误!');
        }
    }

    /*
     * 添加页面跳转*/
    public function addconfig()
    {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addconfig);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //输出模板
        $this->display();
    }


    /*
     * 执行添加操作
     * */
    public function addconfig_do()
    {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addconfig_do);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $name = trim($this->_post('addconfig_name'));
        $key1 = trim($this->_post('addconfig_key1'));
        $val1 = trim($this->_post('addconfig_val1'));
        $submit = trim($this->_post('submit_add'));


        //正则表达式字符串
        $zhengze = '/^[0-9a-zA-Z\_]+$/';
        //进行匹配
        $res_zheng = preg_match($zhengze,$name);
        //判断匹配结果
        if(!$res_zheng) {
            echo "<script>alert('配置名称不合法!');history.go(-1);</script>";
            $this -> error('配置名称不合法!');
        }


        //判断提交来源
        if($submit)
        {
            echo "<script>alert('非法进入!');history.go(-1);</script>";
            $this -> error('非法进入!');
        }

        //判断项目名是否提交
        if(!$name)
        {
            echo "<script>alert('配置名称不能为空');history.go(-1);</script>";
            $this -> error('配置名称不能为空');
        }

        //传值不能为空
        if(!$key1)
        {
            echo "<script>alert('配置项不能为空');history.go(-1);</script>";
            $this -> error('配置项不能为空');
        }

        //传值不能为空
        if(!$val1)
        {
            echo "<script>alert('配置详情不能为空');history.go(-1);</script>";
            $this -> error('配置详情不能为空');
        }


        $Model = new Model();
        //判断name是否已存在
        $res_name = $result = $Model -> table('sixty_config') -> where("name='".$name."'") -> find();
        if($res_name)
        {
            echo "<script>alert('此网站配置名已存在!');history.go(-1);</script>";
            $this -> error('此网站配置名已存在!');
        }


        //准备提交数据数组
        $data = array('name' => $name, 'key1' => $key1, 'val1' => $val1);
        //执行添加
        $result = $Model -> table('sixty_config') -> add($data);


        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);


        //判断添加结果
        if($result) {
            echo "<script>alert('网站配置添加成功!');window.location.href='".__APP__."/Webconfig/index';</script>";
            $this -> success('网站配置添加成功!','__APP__/Webconfig/index');
        }else {
            echo "<script>alert('网站配置添加失败，系统错误!');history.go(-1);</script>";
            $this -> error('网站配置添加失败，系统错误!');
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
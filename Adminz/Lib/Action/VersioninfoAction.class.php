<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/8
 * Time: 17:39
 */
class VersioninfoAction extends Action {
    //定义各模块锁定级别
    private $lock_index         = '7';
    private $lock_delversion_do   = '7';
    private $lock_addversion      = '7';
    private $lock_addversion_do   = '7';
    private $lock_editversion    = '7';
    private $lock_editversion_do  = '7';

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
        $count = $Model->table('sixty_versioninfo')
            ->where($condition)
            ->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('page', $show);// 赋值分页输出

        //执行查询
        $list = $Model -> table('sixty_versioninfo')
            -> field('id, flag, system, version, uptype, apk_url, updesc, update_date')
            -> limit($Page->firstRow . ',' . $Page->listRows)
            -> select();

        //修改结果集内容
        foreach ($list as $key_li => $val_li) {
            if($val_li['flag'] == 1) {
                $list[$key_li]['flag'] = '<span style="background-color:#33FF66;padding:3px;">1-开启</span>';
            }else if($val_li['flag'] == 9) {
                $list[$key_li]['flag'] = '<span style="background-color:#FFFF00;padding:3px;">2-关闭</span>';
            }

            if($val_li['uptype'] == 1) {
                $list[$key_li]['uptype'] = '<span style="background-color:#33FF66;padding:3px;">1-非强制</span>';
            }else if($val_li['uptype'] == 2) {
                $list[$key_li]['uptype'] = '<span style="background-color:#FFFF00;padding:3px;">2-强制</span>';
            }

            //转变提示信息键的值，使它可以在输出时可以换行
            $list[$key_li]['updesc'] = nl2br($val_li['updesc']);
        }

        $this -> assign('list', $list);
        //输出到模板
        $this -> display();

    }

    public function addversion() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addversion);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //准备下拉菜单数组
        $flag_arr = array(
            '9' => '关闭',
            '1' => '开启',
        );

        $type_arr = array(
            '1' => '非强制',
            '2' => '强制',
        );

        //生成下拉菜单
        $flag = $this -> downlist($flag_arr);
        $type = $this -> downlist($type_arr);


        //输出到模板
        $this -> assign('flag', $flag);
        $this -> assign('type', $type);

        //输出模板
        $this -> display();


    }

    public function addversion_do() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addversion_do);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $flag = trim($this->_post('flag'));
        $version = trim($this->_post('version'));
        $system = trim($this->_post('system'));
        $url = trim($this->_post('url'));
        $updesc = trim($this->_post('updesc'));
        $type = trim($this->_post('type'));

        //准备更新数组
        $datetime = date('Y-m-d H:i:s', time());
        $data = array(
            'flag' => $flag,
            'version' => $version,
            'system' => $system,
            'uptype' => $type,
            'apk_url' => $url,
            'updesc' => $updesc,
            'update_date' => $datetime,
        );


        //实例化方法
        $Model = new Model();

        //执行添加
        $res = $Model -> table('sixty_versioninfo') -> add($data);

        //判断结果
        if($res) {
            //返回成功
            echo "<script>alert('版本信息添加成功!');window.location.href='".__APP__.'/Versioninfo/index'.$echourl."';</script>";
            $this -> success('版本信息添加成功!','__APP__'.$echourl);
        }else {
            //返回失败
            echo "<script>alert('版本信息添加失败！');history.go(-1);</script>";
            $this -> error('版本信息添加失败！');
        }
    }


    public function editversion() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editversion);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //接收上传数据
        $id = trim($this->_post('id'));
        $submit = trim($this->_post('editversionbutton'));

        //判断数据来源
        if($id == '') {
            echo "<script>alert('非法进入此页面！');history.go(-1);</script>";
            $this -> error('非法进入此页面！');
        }

        if($submit == '') {
            echo "<script>alert('非法进入此页面！');history.go(-1);</script>";
            $this -> error('非法进入此页面！');
        }

        //实例化方法
        $Model = new Model();

        //根据id查询版本信息
        $list = $Model -> table('sixty_versioninfo') -> field('id, flag, version, system, uptype, apk_url, updesc')
            -> where("id='" . $id . "'") -> find();


        //准备下拉下单数组
        $flag_arr = array(
            '9' => '关闭',
            '1' => '开启',
        );

        $type_arr = array(
            '1' => '非强制',
            '2' => '强制',
        );

        //生成下拉菜单
        $list['flag'] = $this -> downlist($flag_arr, $list['flag']);
        $list['uptype'] = $this -> downlist($type_arr, $list['uptype']);

        //输出到模板
        $this->assign('list', $list);
        $this->display();


    }


    public function editversion_do() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editversion_do);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //接收上传数据
        $flag = trim($this->_post('flag'));
        $version = trim($this->_post('version'));
        $system = trim($this->_post('system'));
        $url = trim($this->_post('url'));
        $updesc = trim($this->_post('updesc'));
        $type = trim($this->_post('type'));

        $id = trim($this->_post('id'));
        $submit = trim($this->_post('editversionbutton'));


        //判断数据来源
        if($id == '') {
            echo "<script>alert('非法进入此页面！');history.go(-1);</script>";
            $this -> error('非法进入此页面！');
        }

        if($submit == '') {
            echo "<script>alert('非法进入此页面！');history.go(-1);</script>";
            $this -> error('非法进入此页面！');
        }


        //准备更新数组
        $data = array(
            'flag' => $flag,
            'version' => $version,
            'system' => $system,
            'uptype' => $type,
            'apk_url' => $url,
            'updesc' => $updesc,
        );

        //实例化方法
        $Model = new Model();

        //执行修改操作
        $res = $Model -> table('sixty_versioninfo') -> where("id='" . $id . "'") -> save($data);

        //判断结果
        if($res) {
            //返回成功
            echo "<script>alert('版本信息修改成功!');window.location.href='".__APP__.'/Versioninfo/index'.$echourl."';</script>";
            $this -> success('版本信息修改成功!','__APP__'.$echourl);
        }else {
            //返回失败
            echo "<script>alert('版本信息修改失败！');history.go(-1);</script>";
            $this -> error('版本信息修改失败！');
        }

    }


    public function delversion_do() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_delversion_do);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $id = trim($this->_post('id'));
        $submit = trim($this->_post('del_version'));

        //判断来源
        if($id == '') {
            echo "<script>alert('非法进入此页面!');history.go(-1);</script>";
            $this -> error('非法进入此页面!');
        }

        if($submit == '') {
            echo "<script>alert('非法进入此页面!');history.go(-1);</script>";
            $this -> error('非法进入此页面!');
        }

        //实例化方法
        $Model = new Model();

        //执行删除操作
        $res = $Model -> table('sixty_versioninfo') -> where("id='" . $id . "'") -> delete();

        //判断结果
        //判断结果
        if($res) {
            //返回成功
            echo "<script>alert('版本信息删除成功!');window.location.href='".__APP__.'/Versioninfo/index'.$echourl."';</script>";
            $this -> success('版本信息删除成功!','__APP__'.$echourl);
        }else {
            //返回失败
            echo "<script>alert('版本信息删除失败！');history.go(-1);</script>";
            $this -> error('版本信息删除失败！');
        }

    }

    //        动态下拉列表、
    public function downlist($arr, $lock=''){
        //start--------------------------------------------------------------
        //动态生成权限下拉选项
        //$lock为空时，关联数组array[0]未默认选项
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
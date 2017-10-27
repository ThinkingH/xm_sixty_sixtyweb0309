<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/24
 * Time: 15:01
 */

class UserAction extends Action{
    //定义各模块锁定级别
    private $lock_index    = '9';
    private $lock_edituser  = '9';
    private $lock_edit_do = '9';
    private $lock_deluser_do     = '9';
    private $lock_adduser       = '9';
    private $lock_adduser_x     = '9';

    /*
     * 用户列表*/
    public function index(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //获取信息
        $where_name = trim($this->_request('find_nickname'));
        $where_phone = trim($this->_request('find_phone'));
        $get_sta_day = trim($this->_request('find_sta_date'));
        $get_end_day = trim($this->_request('find_end_date'));

        //准备where条件数组
        if(!($where_name || $where_phone || $get_sta_day || $get_end_day))
        {
            $condition = '';
        }else {
            if ($get_end_day && !$get_sta_day) {
                //准备where条件数组
                $where_end_day = $get_end_day . ' 23:59:59';
                $condition = array('nickname' => array('like', '%' . $where_name . '%'), 'phone' => array('like', '%' . $where_phone . '%'),
                    'create_datetime' => array('ELT', $where_end_day));
            } elseif (!$get_end_day && $get_sta_day) {
                //准备where条件数组
                $where_sta_day = $get_sta_day . ' 00:00:00';
                $condition = array('nickname' => array('like', '%' . $where_name . '%'), 'phone' => array('like', '%' . $where_phone . '%'),
                    'create_datetime' => array('EGT', $where_sta_day)
                );
            } elseif ($get_end_day && $get_sta_day) {
                //准备where条件数组
                $where_end_day = $get_end_day . ' 23:59:59';
                $where_sta_day = $get_sta_day . ' 00:00:00';
                $condition = array('nickname' => array('like', '%' . $where_name . '%'), 'phone' => array('like', '%' . $where_phone . '%'),
                    'create_datetime' => array('between', array($where_sta_day, $where_end_day)));
            } elseif (!$get_end_day && !$get_sta_day) {
                //准备where条件数组
                $condition = array('nickname' => array('like', '%' . $where_name . '%'), 'phone' => array('like', '%' . $where_phone . '%'),
                );
            }
        }

        $Model = new Model();
        //执行查询
        $list = $Model->table('sixty_user')->field('nickname,touxiang,sex,email,phone,userlevel,birthday,create_datetime')
            ->where($condition)->limit($Page->firstRow . ',' . $Page->listRows)->select();

        //改变查询数据中性别字段
        foreach ($list as $key => $value) {
            if ($list[$key]['sex'] == 1) {
                $list[$key]['sex'] = '男';
            }
            if ($list[$key]['sex'] == 2) {
                $list[$key]['sex'] = '女';
            }
            if ($list[$key]['sex'] == 3) {
                $list[$key]['sex'] = '保密';
            }
        }


        import('ORG.Page');// 导入分页类
        $count = $Model->table('sixty_user')
            ->where($condition)
            ->count();// 查询满足要求的总记录数
        $Page = new Page($count, 100);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('page', $show);// 赋值分页输出

        //执行SQL查询语句
        $list = $Model->table('sixty_user')
            ->where($condition)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();


        //准备要传递的数据数组
        $find_where = array('find_nickname' => $where_name, 'find_phone' => $where_phone, 'find_sta_date' => $get_sta_day, 'find_end_date' => $get_end_day);
        $this->assign('list', $list);
        $this->assign('find_where', $find_where);
        // 输出模板
        $this->display();
    }

    /*
     * 添加用户
     * */
    public function adduser()
    {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_adduser);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=


        // 输出模板
        $this->display();
    }

    /*
     * 执行添加操作
     * */
    public function adduser_x()
    {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_adduser_x);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //获取提交的数据
        $nickname = trim($this->_post('nickname'));
        $phone = trim($this->_post('phone'));
        $email = trim($this->_post('email'));
        $qq = trim($this->_post('qq'));
        $sex = trim($this->_post('sex'));
        $birthday = trim($this->_post('birthday'));
        $passwd = trim($this->_post('passwd'));

        //数据库初始化
        $Model = new Model();
        //判断该用户名是否存在
        $sql_nikename = "select nikename from sixty_user where nikename='".$nickname."'";
        $host_nikename = $Model -> query($sql_nikename);
        if($host_nikename) {
            echo "<script>alert('该昵称已经存在，请选择新的昵称');history.go(-1);</script>";
            $this -> error('该昵称已经存在，请选择新的昵称');
        }else{

            //判断手机号是否存在
            $sql_phone = "select phone from sixty_user where phone='".$phone."'";
            $host_phone = $Model->query($sql_phone);

            if($host_phone) {
                echo "<script>alert('此手机号已存在，请使用其他手机号');history.go(-1);</script>";
                $this -> error('此手机号已存在，请使用其他手机号');
            }else{

                //判断邮箱是否存在
                $sql_email = "select email from sixty_user where email='".$email."'";
                $host_email = $Model->query($sql_email);

                if($host_email) {
                    echo "<script>alert('此邮箱已存在，请使用其他邮箱');history.go(-1);</script>";
                    $this -> error('此邮箱已存在，请使用其他邮箱');

                }else{
                    //判断QQ是否存在
                    //判断邮箱是否存在
                    $sql_qq = "select qq from sixty_user where qq='".$qq."'";
                    $host_qq = $Model->query($sql_qq);
                    if($host_qq) {
                        echo "<script>alert('此qq已存在，请使用其他qq');history.go(-1);</script>";
                        $this -> error('此qq已存在，请使用其他qq');
                    }else{

                        //添加用户信息到sql数组
                        $data = array();
                        $data['nickname'] = $nickname;
                        $data['passwd']   = md5($passwd);
                        $data['phone'] = $phone;
                        $data['email'] = $email;
                        $data['qq'] = $qq;
                        $data['sex'] = $sex;
                        $data['birthday'] = $birthday;
                        $data['create_datetime'] = date('Y-m-d H:i:s',time());

                        //执行添加语句
                        $ret = $Model->table('sixty_user')->add($data);

                        //写入日志
                        $templogs = $Model->getlastsql();
                        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

                        if($ret) {
                            echo "<script>alert('数据添加成功!');window.location.href='".__APP__."/User/index';</script>";
                            $this -> success('数据添加成功!','__APP__/User/index');
                        }else {
                            echo "<script>alert('数据添加失败，系统错误!');history.go(-1);</script>";
                            $this -> error('数据添加失败，系统错误!');
                        }
                    }
                }
            }
        }
    }

    /*
     * 编辑用户信息
     * */
    public function edituser()
    {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_edituser);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //获取提交数据
        $nickname = trim($this->_post('edit_nickname'));

        //根据昵称查询用户信息
        $Model = new Model();
        $list = $Model -> table('sixty_user')
            -> field('is_lock, phone, email, nickname, sex, birthday, qq, remark ,openid')
            -> where("nickname='".$nickname."'") -> find();

        //发送给模板
        $this->assign('list',$list);

        //start--------------------------------------------------------------
        //动态生成权限下拉选项
        $is_lock = '';

        $is_lock .= '<option value="1" ';
        if($list['is_lock'] == 1) {
            $is_lock .= ' selected="selected"';
        }
        $is_lock .= '>未禁用</option>';

        $is_lock .= '<option value="-1" ';
        if($list['is_lock'] == -1) {
            $is_lock .= ' selected="selected"';
        }
        $is_lock .= '>已禁用</option>';

        //发送给模板
        $this -> assign('is_lock',$is_lock);
        //end--------------------------------------------------------------

        // 输出模板
        $this->display();
    }

    /*
     * 执行用户编辑操作
     * */
    public function edituser_do()
    {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_edit_do);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //获取提交数据
        $nickname = trim($this->_post('nickname'));
        $phone = trim($this->_post('phone'));
        $qq = trim($this->_post('qq'));
        $openid = trim($this->_post('weixin_openid'));
        $is_lock = trim($this->_post('is_lock'));

        $Model = new Model();

        //检查用户昵称是否存在
        $sql_nickname = "select id from sixty_user where nickname='".$nickname."' ";
        $result_nickname = $Model -> query($sql_nickname);

        $res_nickname = $result_nickname['0']['nickname'];
        if($res_nickname) {
            echo "<script>alert('此昵称用户不存在');history.go(-1);</script>";
            $this -> error('此昵称用户不存在');
        }

        //检查手机号是否存在
        $sql_phone = "select nickname from sixty_user where phone='".$phone."'" ;
        $result_phone = $Model -> query($sql_phone);

        if($result_phone) {
            $res = $result_phone['0']['nickname'];
            if($res != $nickname)
            {
                echo "<script>alert('此手机号已被注册，请使用其他手机号');history.go(-1);</script>";
                $this -> error('此手机号已被注册，请使用其他手机号');
            }
        }

        //检查qq号是否存在
        $sql_qq = "select nickname from sixty_user where qq='".$qq."'";
        $result_qq = $Model -> query($sql_qq);

        if($result_qq)
        {
            $res = $result_qq[0]['nickname'];
            if($res != $nickname) {
                echo "<script>alert('此qq号已被注册，请使用其他qq号');history.go(-1);</script>";
                $this -> error('此qq号已被注册，请使用其他qq号');
            }
        }


        //检查微信号是否存在
        $sql_openid = "select nickname from sixty_user where openid='".$openid."'";
        $result_openid = $Model -> query($sql_openid);

        if($result_openid) {
            $res = $result_openid[0]['nickname'];
            if($res != $nickname)
            {
                echo "<script>alert('此微信号已被注册，请使用其他微信号');history.go(-1);</script>";
                $this -> error('此微信号已被注册，请使用其他微信号');
            }
        }

        //把要存入的数据放入数组
        $datauser = array();
        $datauser['nickname'] = $nickname;
        $datauser['phone'] = $phone;
        $datauser['qq'] = $qq;
        $datauser['openid'] = $openid;
        $datauser['is_lock'] = $is_lock;

        //执行更新语句
        $result = $Model -> table('sixty_user') -> where("nickname='".$nickname."'")->save($datauser);

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        if(!$result) {
            echo "<script>alert('用户信息修改失败！');history.go(-1);</script>";
            $this -> error('用户信息修改失败！');
        }else{
            echo "<script>alert('用户信息修改操作执行完成!');window.location.href='".__APP__."/User/index';</script>";
            $this ->success('用户信息修改操作执行完成!','__APP__/User/index');
        }


    }

    /*
     * 执行用户删除操作
     * */
    public function deluser_do()
    {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_deluser_do);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //获取要删除账户的用户昵称
        $nickname = trim($this->_post('del_nickname'));
        $submitdel = trim($this->_post('submitdel'));

        $Model = new Model();

        //判断用于昵称是否为空
        if($submitdel == '')
        {
            echo "<script>alert('非法进入此页面');history.go(-1);</script>";
            $this -> error('非法进入此页面');
        }

        //判断用于昵称是否为空
        if(empty($nickname))
        {
            echo "<script>alert('非法进入此页面');history.go(-1);</script>";
            $this -> error('非法进入此页面');
        }

        //检查昵称是否存在
        $sql_nickname = "select is_lock, nickname from sixty_user where nickname='".$nickname."'";
        $result_nickname = $Model -> query($sql_nickname);

        //检查用户昵称是否存在
        if(!$result_nickname) {
            echo "<script>alert('此昵称不存在');history.go(-1);</script>";
            $this -> error('此昵称不存在');
        }

        //检查
        $res_is = $result_nickname[0]['is_lock'];
        if($res_is == 1) {
            echo "<script>alert('请先将用户【禁用】，然后再执行删除操作');history.go(-1);</script>";
            $this -> error('请先将用户【禁用】，然后再执行删除操作');
        }

        //执行删除操作
        $del_result = $Model -> table('sixty_user') -> where("nickname='".$nickname."'") -> delete();

        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);


        if($del_result) {
            echo "<script>alert('数据删除成功!');window.location.href='".__APP__."/User/index';</script>";
            $this -> success('数据删除成功!','__APP__/User/index');
        }else {
            echo "<script>alert('数据删除失败，系统错误!');history.go(-1);</script>";
            $this -> error('数据删除失败，系统错误!');
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


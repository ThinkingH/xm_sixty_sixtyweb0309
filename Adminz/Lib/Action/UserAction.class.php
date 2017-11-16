<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/24
 * Time: 15:01
 */

class UserAction extends Action{
    //定义各模块锁定级别
    private $lock_index        = '97';
    private $lock_edituser     = '97';
    private $lock_edit_do      = '97';
    private $lock_deluser_do   = '97';
    private $lock_adduser      = '97';
    private $lock_adduser_x    = '97';

    /*
     * 用户列表*/
    public function index(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //拼接URL地址，并返回到页面
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);

        //获取信息
        $where_id = trim($this->_get('find_id'));
        $where_name = trim($this->_get('find_nickname'));
        $where_phone = trim($this->_get('find_phone'));
        $get_sta_day = trim($this->_get('find_sta_date'));
        $get_end_day = trim($this->_get('find_end_date'));
        $lock = trim($this->_get('lock'));

        //判断用户状态是否选择
        if($lock == '')
        {
            $lock = 4;
        }

        //准备where条件数组
        $condition = "nickname like '" . $where_name . "%' and phone like '". $where_phone ."%'";
        if($where_id != '')
        {
            $condition .= "and id = '". $where_id ."'";
        }
        if($get_sta_day != '')
        {
            $where_sta_day = $get_sta_day . ' 00:00:00';
            $condition .= "and create_datetime >= '" . $where_sta_day . "'";
        }
        if($get_end_day != '')
        {
            $where_end_day = $get_end_day . ' 23:59:59';
            $condition .= "and create_datetime <= '" . $where_end_day . "'";
        }

        if($lock != 4)
        {
            $condition .= "and is_lock = '". $lock ."'";
        }

        $Model = new Model();

        //分页
        import('ORG.Page');// 导入分页类
        $count = $Model->table('sixty_user')
            ->where($condition)
            ->count();// 查询满足要求的总记录数
        $Page = new Page($count, 100);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('page', $show);// 赋值分页输出

        //执行查询
        $list = $Model->table('sixty_user')->field('id, is_lock, openid, nickname, touxiang, sex, email, phone, 
        userlevel, birthday, create_datetime, keyong_money, dongjie_money, keyong_jifen, dongjie_jifen, describes,
        qq,weixin,remark, jiguangid, phonetype, qqid, weiboid')
            ->where($condition) ->order('create_datetime DESC') ->limit($Page->firstRow . ',' . $Page->listRows)->select();
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

            if ($list[$key]['phonetype'] == 1) {
                $list[$key]['phonetype'] = '安卓';
            }
            if ($list[$key]['phonetype'] == 2) {
                $list[$key]['phonetype'] = 'IOS';
            }
            if ($list[$key]['phonetype'] == 0) {
                $list[$key]['phonetype'] = '保密';
            }

            if($list[$key]['is_lock'] == 1)
            {
                $list[$key]['is_lock'] = "<span style='background-color: #33FF66; padding: 3px;'>1-已开启</span>";
            }else{
                $list[$key]['is_lock'] = "<span style='background-color: #FF82A5; padding: 3px;'>2-已关闭</span>";
            }
            //获取七牛云图片
            $showimg = $list[$key]['touxiang'];
            $imgwidth = '50';
            $imgheight = '50';
            $addressimg = hy_qiniuimgurl('sixty-user',$showimg,$imgwidth,$imgheight);
            $list[$key]['touxiang'] = "<img src='" . $addressimg . "' />";
//            var_dump($list['touxiang']);die;
        }

        //start--------------------------------------------------------------
        //动态生成权限下拉选项
        $rootarr = array(
            '4' => '4-全选',
            '1' => '1-已开启',
            '-1' => '2-已关闭',
        );

        $rootflag_show = '';
        foreach($rootarr as $keyr => $valr) {
            $rootflag_show .= '<option value="'.$keyr.'" ';
            if($keyr==$lock) {
                $rootflag_show .= ' selected="selected"';
            }
            $rootflag_show .= '>'.$valr.'</option>';

        }
        $this -> assign('rootflag_show',$rootflag_show);
        //end--------------------------------------------------------------



        //准备要传递的数据数组
        $find_where = array(
            'find_nickname' => $where_name,
            'find_phone' => $where_phone,
            'find_sta_date' => $get_sta_day,
            'find_end_date' => $get_end_day,
            'find_id' => $where_id,
            );
        $this->assign('find_where', $find_where);
        $this->assign('list', $list);

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
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);

        //start--------------------------------------------------------------
        //动态生成权限下拉选项
        $userarr = array(
            '1' => '1-启用',
            '2' => '2-禁用',
        );

        $userlock_show = '';
        foreach($userarr as $keyr => $valr) {
            $userlock_show .= '<option value="'.$keyr.'" ';
            if($keyr==$lock) {
                $userlock_show .= ' selected="selected"';
            }
            $userlock_show .= '>'.$valr.'</option>';

        }
        $this -> assign('userlock_show',$userlock_show);
        //end--------------------------------------------------------------

        //start--------------------------------------------------------------
        //动态生成权限下拉选项
        $sexarr = array(
            '3' => '3-保密',
            '1' => '1-男',
            '2' => '2-女',
        );

        $usersex_show = '';
        foreach($sexarr as $keyr => $valr) {
            $usersex_show .= '<option value="'.$keyr.'" ';
            if($keyr==$lock) {
                $usersex_show .= ' selected="selected"';
            }
            $usersex_show .= '>'.$valr.'</option>';

        }
        $this -> assign('usersex_show',$usersex_show);
        //end--------------------------------------------------------------

        //start--------------------------------------------------------------
        //动态生成权限下拉选项
        $phonearr = array(
            '' => '保密',
            '1' => '1-安卓',
            '2' => '2-IOS',
        );

        $phone_show = '';
        foreach($phonearr as $keyr => $valr) {
            $phone_show .= '<option value="'.$keyr.'" ';
            if($keyr==$lock) {
                $phone_show .= ' selected="selected"';
            }
            $phone_show .= '>'.$valr.'</option>';

        }
        $this -> assign('phone_show',$phone_show);
        //end--------------------------------------------------------------

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

        //拼接URL地址，并返回到页面
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);

        //获取提交的数据
        $nickname = trim($this->_post('nickname'));
        $phone = trim($this->_post('phone'));
        $email = trim($this->_post('email'));
        $qq = trim($this->_post('qq'));
        $sex = trim($this->_post('sex'));
        $birthday = trim($this->_post('birthday'));
        $lock = trim($this->_post('lock'));
        $describes = trim($this->_post('describes'));
        $remark = trim($this->_post('remark'));
        $qqid = trim($this->_post('qqid'));
        $weiboid = trim($this->_post('weiboid'));
        $openid = trim($this->_post('openid'));
        $weixin = trim($this->_post('weixin'));
        $jiguangid = trim($this->_post('jiguangid'));
        $phonetype = trim($this->_post('phonetype'));

        //判断数据是否为空
        if($nickname == '') {
            echo "<script>alert('昵称不能为空');history.go(-1);</script>";
            $this -> error('昵称不能为空');
        }
        if($phone == '') {
            echo "<script>alert('手机号不能为空');history.go(-1);</script>";
            $this -> error('手机号不能为空');
        }
        if($email != '') {
            if(!preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',$email)) {
                echo "<script>alert('邮箱不符合邮箱格式');history.go(-1);</script>";
                $this -> error('邮箱不符合邮箱格式');
            }
        }

        $Model = new Model();
        //检查用户昵称是否存在
        $sql_nickname = "select id from sixty_user where nickname='".$nickname."' and id <> '" . $id ."'";
        $result_nickname = $Model -> query($sql_nickname);

        $res_nickname = $result_nickname['0']['id'];
        if($res_nickname != '') {
            echo "<script>alert('此昵称已存在');history.go(-1);</script>";
            $this -> error('此昵称已存在');
        }


        //检查手机号是否存在
        $sql_phone = "select nickname from sixty_user where phone='".$phone."' and id <> '" . $id ."'" ;
        $result_phone = $Model -> query($sql_phone);

        if($result_phone) {
            $res = $result_phone['0']['nickname'];
            if($res != $nickname)
            {
                echo "<script>alert('此手机号已被注册，请使用其他手机号');history.go(-1);</script>";
                $this -> error('此手机号已被注册，请使用其他手机号');
            }
        }

        if($qq != '') {
            //检查qq号是否存在
            $sql_qq = "select nickname from sixty_user where qq='".$qq."' and id <> '" . $id ."'";
            $result_qq = $Model -> query($sql_qq);

            if($result_qq)
            {
                $res = $result_qq[0]['nickname'];
                if($res != $nickname) {
                    echo "<script>alert('此qq号已被注册，请使用其他qq号');history.go(-1);</script>";
                    $this -> error('此qq号已被注册，请使用其他qq号');
                }
            }
        }


        if($weixin != '') {
            //检查微信号是否存在
            $sql_weixin = "select nickname from sixty_user where weixin='".$weixin."' and id <> '" . $id ."'";
            $result_weixin = $Model -> query($sql_weixin);

            if($result_weixin) {
                $res = $result_weixin[0]['nickname'];
                if($res != $nickname && $result_weixin['openid'] != '')
                {
                    echo "<script>alert('此微信号已被注册，请使用其他微信号');history.go(-1);</script>";
                    $this -> error('此微信号已被注册，请使用其他微信号');
                }
            }
        }


        if($email != '') {
            //检查邮箱是否存在
            $sql_email = "select nickname from sixty_user where email='".$email."' and id <> '" . $id ."'";
            $result_email = $Model -> query($sql_email);
            if($result_email) {
                $res = $result_email[0]['nickname'];
                if($res != $nickname)
                {
                    echo "<script>alert('此邮箱已被注册，请使用其他邮箱');history.go(-1);</script>";
                    $this -> error('此邮箱已被注册，请使用其他邮箱');
                }
            }
        }


        if($qqid != '') {
            //检查qqid是否存在
            $sql_qqid = "select nickname from sixty_user where qqid='".$qqid."' and id <> '" . $id ."'";
            $result_qqid = $Model -> query($sql_qqid);
            if($result_qqid) {
                $res = $result_qqid[0]['nickname'];
                if($res != $nickname)
                {
                    echo "<script>alert('此qqid已被注册，请使用其他qqid');history.go(-1);</script>";
                    $this -> error('此qqid已被注册，请使用其他qqid');
                }
            }
        }


        if($openid != '') {
            //检查openid是否存在
            $sql_openid = "select nickname from sixty_user where openid='".$openid."' and id <> '" . $id ."'";
            $result_openid = $Model -> query($sql_openid);
            if($result_openid) {
                $res = $result_openid[0]['nickname'];
                if($res != $nickname)
                {
                    echo "<script>alert('此openid已被注册，请使用其他openid');history.go(-1);</script>";
                    $this -> error('此openid已被注册，请使用其他openid');
                }
            }
        }


        if($weiboid != '') {
            //检查weiboid是否存在
            $sql_weiboid = "select nickname from sixty_user where weiboid='".$weiboid."' and id <> '" . $id ."'";
            $result_weiboid = $Model -> query($sql_weiboid);
            if($result_weiboid) {
                $res = $result_weiboid[0]['nickname'];
                if($res != $nickname)
                {
                    echo "<script>alert('此微博id已被注册，请使用其他微博id');history.go(-1);</script>";
                    $this -> error('此微博id已被注册，请使用其他微博id');
                }
            }
        }


        if($jiguangid != '') {
            //检查jiguangid是否存在
            $sql_jiguangid = "select nickname from sixty_user where jiguangid='".$jiguangid."' and id <> '" . $id ."'";
            $result_jiguangid = $Model -> query($sql_jiguangid);
            if($result_jiguangid) {
                $res = $result_jiguangid[0]['nickname'];
                if($res != $nickname)
                {
                    echo "<script>alert('此极光id已被注册，请使用其他极光id');history.go(-1);</script>";
                    $this -> error('此极光id已被注册，请使用其他极光id');
                }
            }
        }

        //添加用户信息到sql数组
        $data = array();
        $data['nickname'] = $nickname;
        $data['phone'] = $phone;
        $data['email'] = $email;
        $data['qq'] = $qq;
        $data['sex'] = $sex;
        $data['is_lock'] = $lock;
        $data['birthday'] = $birthday;
        $data['describes'] = $describes;
        $data['remark'] = '后台手工添加' . $remark;
        $data['qqid'] = $qqid;
        $data['openid'] = $openid;
        $data['weixin'] = $weixin;
        $data['weiboid'] = $weiboid;
        $data['jiguangid'] = $jiguangid;
        $data['phonetype'] = $phonetype;
        $data['create_datetime'] = date('Y-m-d H:i:s',time());
        //执行添加语句
        $ret = $Model->table('sixty_user')->add($data);

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        if($ret) {
            echo "<script>alert('数据添加成功!');window.location.href='".__APP__.'/User/index'.$echourl."';</script>";
            $this -> success('数据添加成功!','__APP__'.$echourl);
        }else {
            echo "<script>alert('数据添加失败，系统错误!');history.go(-1);</script>";
            $this -> error('数据添加失败，系统错误!');
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
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);

        //获取提交数据
        $nickname = trim($this->_post('edit_nickname'));

        //根据昵称查询用户信息
        $Model = new Model();
        $list = $Model -> table('sixty_user')
            -> field('id, is_lock, openid, nickname, touxiang, email, phone, create_datetime, 
            describes,qq,weixin,remark, jiguangid, qqid, weiboid')
            -> where("nickname='".$nickname."'") -> find();

        //获取七牛云图片
        $showimg = $list['touxiang'];
        $imgwidth = '50';
        $imgheight = '50';
        $addressimg = hy_qiniuimgurl('sixty-user',$showimg,$imgwidth,$imgheight);
        $list['touxiang'] = "<img src='" . $addressimg . "' />";
        //发送给模板
        $this->assign('list',$list);
        //start--------------------------------------------------------------
        //动态生成权限下拉选项
        $is_lock = '';

        $is_lock .= '<option value="1" ';
        if($list['is_lock'] == 1) {
            $is_lock .= ' selected="selected"';
        }
        $is_lock .= '>已启用</option>';

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
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);

        //获取提交数据
        $id = trim($this->_post('id'));
        $nickname = trim($this->_post('nickname'));
        $phone = trim($this->_post('phone'));
        $email = trim($this->_post('email'));
        $qq = trim($this->_post('qq'));
        $is_lock = trim($this->_post('is_lock'));
        $describes = trim($this->_post('describes'));
        $remark = trim($this->_post('remark'));
        $qqid = trim($this->_post('qqid'));
        $weiboid = trim($this->_post('weiboid'));
        $openid = trim($this->_post('openid'));
        $weixin = trim($this->_post('weixin'));
        $jiguangid = trim($this->_post('jiguangid'));


        $Model = new Model();

        //判断上传数据是否为空
        if($nickname == '') {
            echo "<script>alert('用户昵称不能为空');history.go(-1);</script>";
            $this -> error('用户昵称不能为空');
        }

        if($phone == '') {
            echo "<script>alert('用户手机号不能为空');history.go(-1);</script>";
            $this -> error('用户手机号不能为空');
        }
        if($email != '') {
            if(!preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',$email)) {
                echo "<script>alert('邮箱不符合邮箱格式');history.go(-1);</script>";
                $this -> error('邮箱不符合邮箱格式');
            }
        }

        //检查用户是否存在
        $sql_id = "select id from sixty_user where id='".$id."'";
        $result_id = $Model -> query($sql_id);

        $res_id = $result_id['0']['id'];
        if($res_id == '') {
            echo "<script>alert('此用户不存在');history.go(-1);</script>";
            $this -> error('此用户不存在');
        }

        //检查用户昵称是否存在
        $sql_nickname = "select id from sixty_user where nickname='".$nickname."' and id <> '" . $id ."'";
        $result_nickname = $Model -> query($sql_nickname);

        $res_nickname = $result_nickname['0']['id'];
        if($res_nickname != '') {
            echo "<script>alert('此昵称已被注册，请使用其他昵称');history.go(-1);</script>";
            $this -> error('此昵称已被注册，请使用其他昵称');
        }


        //检查手机号是否存在
        $sql_phone = "select nickname from sixty_user where phone='".$phone."' and id <> '" . $id ."'" ;
        $result_phone = $Model -> query($sql_phone);

        if($result_phone) {
            $res = $result_phone['0']['nickname'];
            if($res != $nickname)
            {
                echo "<script>alert('此手机号已被注册，请使用其他手机号');history.go(-1);</script>";
                $this -> error('此手机号已被注册，请使用其他手机号');
            }
        }

        if($qq != '') {
            //检查qq号是否存在
            $sql_qq = "select nickname from sixty_user where qq='".$qq."' and id <> '" . $id ."'";
            $result_qq = $Model -> query($sql_qq);

            if($result_qq)
            {
                $res = $result_qq[0]['nickname'];
                if($res != $nickname) {
                    echo "<script>alert('此qq号已被注册，请使用其他qq号');history.go(-1);</script>";
                    $this -> error('此qq号已被注册，请使用其他qq号');
                }
            }
        }


        if($weixin != '') {
            //检查微信号是否存在
            $sql_weixin = "select nickname from sixty_user where weixin='".$weixin."' and id <> '" . $id ."'";
            $result_weixin = $Model -> query($sql_weixin);

            if($result_weixin) {
                $res = $result_weixin[0]['nickname'];
                if($res != $nickname && $result_weixin['openid'] != '')
                {
                    echo "<script>alert('此微信号已被注册，请使用其他微信号');history.go(-1);</script>";
                    $this -> error('此微信号已被注册，请使用其他微信号');
                }
            }
        }


        if($email != '') {
            //检查邮箱是否存在
            $sql_email = "select nickname from sixty_user where email='".$email."' and id <> '" . $id ."'";
            $result_email = $Model -> query($sql_email);
            if($result_email) {
                $res = $result_email[0]['nickname'];
                if($res != $nickname)
                {
                    echo "<script>alert('此邮箱已被注册，请使用其他邮箱');history.go(-1);</script>";
                    $this -> error('此邮箱已被注册，请使用其他邮箱');
                }
            }
        }


        if($qqid != '') {
            //检查qqid是否存在
            $sql_qqid = "select nickname from sixty_user where qqid='".$qqid."' and id <> '" . $id ."'";
            $result_qqid = $Model -> query($sql_qqid);
            if($result_qqid) {
                $res = $result_qqid[0]['nickname'];
                if($res != $nickname)
                {
                    echo "<script>alert('此qqid已被注册，请使用其他qqid');history.go(-1);</script>";
                    $this -> error('此qqid已被注册，请使用其他qqid');
                }
            }
        }


        if($openid != '') {
            //检查openid是否存在
            $sql_openid = "select nickname from sixty_user where openid='".$openid."' and id <> '" . $id ."'";
            $result_openid = $Model -> query($sql_openid);
            if($result_openid) {
                $res = $result_openid[0]['nickname'];
                if($res != $nickname)
                {
                    echo "<script>alert('此openid已被注册，请使用其他openid');history.go(-1);</script>";
                    $this -> error('此openid已被注册，请使用其他openid');
                }
            }
        }


        if($weiboid != '') {
            //检查weiboid是否存在
            $sql_weiboid = "select nickname from sixty_user where weiboid='".$weiboid."' and id <> '" . $id ."'";
            $result_weiboid = $Model -> query($sql_weiboid);
            if($result_weiboid) {
                $res = $result_weiboid[0]['nickname'];
                if($res != $nickname)
                {
                    echo "<script>alert('此微博id已被注册，请使用其他微博id');history.go(-1);</script>";
                    $this -> error('此微博id已被注册，请使用其他微博id');
                }
            }
        }


        if($jiguangid != '') {
            //检查jiguangid是否存在
            $sql_jiguangid = "select nickname from sixty_user where jiguangid='".$jiguangid."' and id <> '" . $id ."'";
            $result_jiguangid = $Model -> query($sql_jiguangid);
            if($result_jiguangid) {
                $res = $result_jiguangid[0]['nickname'];
                if($res != $nickname)
                {
                    echo "<script>alert('此极光id已被注册，请使用其他极光id');history.go(-1);</script>";
                    $this -> error('此极光id已被注册，请使用其他极光id');
                }
            }
        }


        //把要存入的数据放入数组
        $datauser = array();
        $datauser['nickname'] = $nickname;
        $datauser['phone'] = $phone;
        $datauser['qq'] = $qq;
        $datauser['openid'] = $openid;
        $datauser['is_lock'] = $is_lock;
        $datauser['email'] = $email;
        $datauser['describes'] = $describes;
        $datauser['remark'] = $remark;
        $datauser['qqid'] = $qqid;
        $datauser['weiboid'] = $weiboid;
        $datauser['weixin'] = $weixin;
        $datauser['jiguangid'] = $jiguangid;


        //执行更新语句
        $result = $Model -> table('sixty_user') -> where("id='".$id."'")->save($datauser);

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        if(!$result) {
            echo "<script>alert('用户信息修改失败！');history.go(-1);</script>";
            $this -> error('用户信息修改失败！');
        }else{
            echo "<script>alert('用户信息修改操作执行完成!');window.location.href='" .__APP__. '/User/index'.$echourl."';</script>";
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
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);


        //获取要删除账户的用户昵称
        $nickname = trim($this->_post('del_nickname'));
        $submitdel = trim($this->_post('submitdel'));

        $Model = new Model();

        //判断提交是否为空
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
        $sql_nickname = "select is_lock, nickname, touxiang from sixty_user where nickname='".$nickname."'";
        $result_nickname = $Model -> query($sql_nickname);

        //判断用户昵称是否存在
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

        //删除七牛云旧图片
        delete_qiniu('sixty-user', $res_is = $result_nickname[0]['touxiang']);

        //执行删除操作
        $del_result = $Model -> table('sixty_user') -> where("nickname='".$nickname."'") -> delete();

        //写入日志
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
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
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




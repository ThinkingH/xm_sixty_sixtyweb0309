<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/9
 * Time: 15:43
 */
class SuggestAction extends Action {
    //定义各模块锁定级别
    private $lock_index         = '97';

    public function index() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $id = trim($this->_get('find_id'));
        $user_id = trim($this->_get('find_user_id'));
        $get_sta_day = trim($this->_get('find_sta_date'));
        $get_end_day = trim($this->_get('find_end_date'));


        //判断查询日期是否提交
        if($get_sta_day == '') {
            $get_sta_day = date('Y-m-d', strtotime('-6 months',time()));
        }
        if($get_end_day == '') {
            $get_end_day = date('Y-m-d', time());
        }

        //准备返回数组
        $find_where = array(
            'find_id' => $id,
            'find_userid' => $user_id,
            'find_sta_date' => $get_sta_day,
            'find_end_date' => $get_end_day,
        );
        //返回查询数据
        $this->assign('find_where', $find_where);



        //准备查询时间
        $where_end_day = $get_end_day . ' 23:59:59';
        $where_sta_day = $get_sta_day . ' 00:00:00';

        //判断是否查询创建时间
        $condition = "create_datetime >= '" . $where_sta_day . "' and create_datetime <= '" . $where_end_day . "'";

        //判断是否查询ID
        if($id != '') {
            $condition .= "and id = '" . $id . "'";
        }

        //判断是否查询用户ID
        if($user_id != '') {
            $condition .= "and userid = '" . $user_id . "'";
        }


        //实例化方法
        $Model = new Model();

        //分页
        import('ORG.Page');// 导入分页类
        $count = $Model -> table('sixty_yijian')
            -> where($condition)
            -> count();// 查询满足要求的总记录数
        $Page = new Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('page',$show);// 赋值分页输出
        //执行查询操作
        $list = $Model -> table('sixty_yijian') -> field('id, type, userid, contact, content, create_datetime')
            -> where($condition) -> order('create_datetime desc')
            -> limit($Page->firstRow . ',' . $Page->listRows) -> select();


        //取出所有用户id
        $user_arr = array();
        array_map(function($value) use (&$user_arr){
            $user_arr[] = $value['userid'];
            }, $list);

        //去除重复值
        $user_arr = array_unique($user_arr);


        //准备查询数组
        $where_user['id'] = array('in',$user_arr);
        $list_user = $Model -> table('sixty_user') -> field('id, nickname') -> where($where_user) -> select();

        foreach ($list as $key_li => $val_li) {
            foreach ($list_user as $key_us => $val_us) {
                if($val_li['userid'] == $val_us['id']) {
                    $list[$key_li]['nickname'] = $val_us['nickname'];
                }
            }

            if($val_li['type'] == '1') {
                $list[$key_li]['type'] = '正常用户';
            }else if($val_li['type'] == '2') {
                $list[$key_li]['type'] = '临时用户';
            }
        }

        //输出到模板
        $this -> assign('list', $list);
        $this -> display();
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
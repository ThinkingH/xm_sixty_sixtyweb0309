<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/30
 * Time: 15:35
 */
class PinglunAction extends Action{

    //定义各模块锁定级别
    private $lock_tougao = '9';
    private $lock_pinglun = '9';
    private $lock_delpinglun_do = '9';

    //视频列表跳转查看单条视频的全部评论
    public function tougao()
    {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_tougao);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);

        //接收查询信息数据
        $v_id = trim($this->_get('video_id'));
        $pl_id = trim($this->_get('find_id'));
        $find_userid = trim($this->_get('find_user_id'));
        $find_sta_date = trim($this->_get('find_sta_date'));
        $find_end_date = trim($this->_get('find_end_date'));
        //设置默认查询日期
        //判断是否查询起始日期
        if ($find_sta_date == '') {
            $find_sta_date = date('Y-m-d', strtotime('-6 month'));
            $where_sta_day = $find_sta_date . ' 00:00:00';
        }else{
            $where_sta_day = $find_sta_date . ' 00:00:00';
        }
        //判断是否查询结束日期
        if ($find_end_date == '') {
            $find_end_date = date('Y-m-d', time());
            $where_end_day = $find_end_date . ' 23:59:59';
        }else{
            $where_end_day = $find_end_date . ' 23:59:59';
        }

        //数据返回页面
        $find_where['find_user_id'] = $find_userid;
        $find_where['find_id'] = $pl_id;
        $find_where['find_end_date'] = $find_end_date;
        $find_where['find_sta_date'] = $find_sta_date;
        $find_where['video_id'] = $v_id;
        $find_where['type'] = '2';
        $this->assign('find_where',$find_where);

        $Model = new Model();
        //准备查询条件
        $condition = "type = '2' and sixty_video_pinglun.create_datetime <= '" . $where_end_day . "' 
        and sixty_video_pinglun.create_datetime >= '" . $where_sta_day . "'";
        //判断是否查询评论ID
        if($pl_id != '')
        {
            $condition .= " and id = '" .  $pl_id . "'";
        }
        //判断是否有查询视频id
        if($v_id != '')
        {
            $condition .= " and vid = '" . $v_id . "'";
        }

//        分页
        import('ORG.Page');// 导入分页类
        $count = $Model->table('sixty_video_pinglun')
            ->where($condition)
            ->count();// 查询满足要求的总记录数

        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('page', $show);// 赋值分页输出

        //查询评论表
        $list = $Model->table('sixty_video_pinglun')
            -> field('id, vid, type, userid, content, showimg, dianzan, create_datetime')
            -> where($condition)-> order('create_datetime desc')
            -> limit($Page->firstRow . ',' . $Page->listRows) -> select();

        //遍历评论表
        foreach($list as $key_li => $val_l)
        {
            //判断评论类型，替换类型内容
            if($list[$key_li]['type'] == 2) {
                $list[$key_li]['type'] = '投稿';
            }
        }

        //输出到模板
        $this->assign('list', $list);
        $this->display();
    }

    public function pinglun()
    {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_pinglun);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);

        //接收查询信息数据
        $v_id = trim($this->_get('video_id'));
        $pl_id = trim($this->_get('find_id'));
        $find_userid = trim($this->_get('find_user_id'));
        $find_sta_date = trim($this->_get('find_sta_date'));
        $find_end_date = trim($this->_get('find_end_date'));

        //设置默认查询日期
        //判断是否查询起始日期
        if ($find_sta_date == '') {
            $find_sta_date = date('Y-m-d', strtotime('-6 month'));
            $where_sta_day = $find_sta_date . ' 00:00:00';
        }else{
            $where_sta_day = $find_sta_date . ' 00:00:00';
        }
        //判断是否查询结束日期
        if ($find_end_date == '') {
            $find_end_date = date('Y-m-d', time());
            $where_end_day = $find_end_date . ' 23:59:59';
        }else{
            $where_end_day = $find_end_date . ' 23:59:59';
        }

        //数据返回页面
        $find_where['find_user_id'] = $find_userid;
        $find_where['find_id'] = $pl_id;
        $find_where['find_end_date'] = $find_end_date;
        $find_where['find_sta_date'] = $find_sta_date;
        $find_where['video_id'] = $v_id;
        $find_where['type'] = '1';
        $this->assign('find_where',$find_where);

        $Model = new Model();
        //准备查询条件
        $condition = "type = '1' and sixty_video_pinglun.create_datetime <= '" . $where_end_day . "' 
        and sixty_video_pinglun.create_datetime >= '" . $where_sta_day . "'";
        //判断是否查询评论ID
        if($pl_id != '')
        {
            $condition .= " and id = '" .  $pl_id . "'";
        }
        //判断是否有查询视频id
        if($v_id != '')
        {
            $condition .= " and vid = '" . $v_id . "'";
        }

//        分页
        import('ORG.Page');// 导入分页类
        $count = $Model->table('sixty_video_pinglun')
            ->where($condition)
            ->count();// 查询满足要求的总记录数

        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('page', $show);// 赋值分页输出
        //查询评论表
        $list = $Model->table('sixty_video_pinglun')
            -> field('id, vid, type, userid, content, dianzan, create_datetime')
            -> where($condition)-> order('create_datetime desc')
            -> limit($Page->firstRow . ',' . $Page->listRows) -> select();

        //遍历评论表
        foreach($list as $key_li => $val_l)
        {
            //判断评论类型，替换类型内容
            if($list[$key_li]['type'] == 1) {
                $list[$key_li]['type'] = '评论';
            }
        }

        //输出到模板
        $this->assign('list', $list);
        $this->display();
    }
    /*
     * 删除评论
     * */
    public function delpinglun_do(){

        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_delpinglun_do);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //拼接URL地址，并返回到页面
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);

        //接收上传数据
        $id = trim($this->_post('pinglun_id'));
        $button = trim($this->_post('delpinglunbutton'));
        $pinglun_type = trim($this->_post('pinglun_type'));

        //判断数据来源是否合法
        if($button == '')
        {
            //返回错误
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }

        if($id == '')
        {
            //返回错误
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }

        //查询ID判断此条评论是否存在
        $Model = new Model();
        $res = $Model -> table('sixty_video_pinglun') -> field('vid') -> where("id = '" . $id . "'") -> find();
        if($res == '')
        {
            //返回错误
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }

        //执行删除操作
        $result = $Model -> table('sixty_video_pinglun') -> where("id = '" . $id . "'") -> delete();

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //判断删除结果
        if($result == '')
        {
            //返回错误
            echo "<script>alert('评论删除失败！');history.go(-1);</script>";
            $this -> error('评论删除失败！');
        }else{
            //判断请求来源，判断成功后请求方法
            if($pinglun_type == '评论'){
                //返回成功
                echo "<script>alert('评论删除成功!');window.location.href='".__APP__.'/Pinglun/pinglun'. $echourl ."';</script>";
                $this -> success('评论删除成功!','__APP__'.$echourl);
            }else if($pinglun_type == '投稿'){
                //返回成功
                echo "<script>alert('评论删除成功!');window.location.href='".__APP__.'/Pinglun/tougao'. $echourl ."';</script>";
                $this -> success('评论删除成功!','__APP__'.$echourl);
            }

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
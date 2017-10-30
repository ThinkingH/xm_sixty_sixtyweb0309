<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/30
 * Time: 15:35
 */
class PinglunAction extends Action{

    //视频列表跳转查看单条视频的全部评论
    public function video_to_pinglun()
    {
        $v_id = trim($this->_request('video_id'));
        $find_nickname = trim($this->_request('find_nickname'));
        $find_content = trim($this->_request('find_content'));
        $find_sta_date = trim($this->_request('find_sta_date'));
        $find_end_date = trim($this->_request('find_end_date'));

        if($v_id == '')
        {
            //返回错误
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }
        //数据返回页面
        $find_where['find_nickname'] = $find_nickname;
        $find_where['find_content'] = $find_content;
        $find_where['find_end_date'] = $find_end_date;
        $find_where['find_sta_date'] = $find_sta_date;
        $find_where['video_id'] = $v_id;

        $this->assign('find_where',$find_where);

        $Model = new Model();

        //判断是否有查询条件
        $condition = "vid = '" . $v_id . "'";
        //判断是否查询用户昵称
        if ($find_nickname != '') {
            $condition .= " and nickname = '" . $find_nickname . "'";
        }
        //判断是否查询起始日期
        if ($find_sta_date != '') {
            $where_sta_day = $find_sta_date . ' 00:00:00';
            $condition .= " and sixty_video_pinglun.create_datetime >= '" . $where_sta_day . "'";
        }
        //判断是否查询结束日期
        if ($find_end_date != '') {
            $where_end_day = $find_end_date . ' 23:59:59';
            $condition .= " and sixty_video_pinglun.create_datetime <= '" . $where_end_day . "'";
        }
        //判断是否查询分类1
        if ($find_content != '') {
            $condition .= " and content like '%" . $find_content . "%'";
        }

//        var_dump($condition);

//        分页
        import('ORG.Page');// 导入分页类
        $count = $Model->table('sixty_video_pinglun')
            ->join('sixty_user on sixty_video_pinglun.userid = sixty_user.id')
            ->where($condition)
//            ->order('sixty_video_pinglun.create_datetime desc')
            ->count();// 查询满足要求的总记录数
//        var_dump($count);
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('page', $show);// 赋值分页输出

        $list = $Model->table('sixty_video_pinglun')
            ->field('sixty_video_pinglun.id, sixty_video_pinglun.vid, sixty_video_pinglun.type, sixty_video_pinglun.userid,
            sixty_video_pinglun.content, sixty_video_pinglun.showimg,
            sixty_video_pinglun.dianzan, sixty_video_pinglun.create_datetime, sixty_user.nickname')
            ->join('sixty_user on sixty_video_pinglun.userid = sixty_user.id')
            ->where($condition)-> order('sixty_video_pinglun.create_datetime desc') -> limit($Page->firstRow . ',' . $Page->listRows) -> select();

        for($i = 0; $i < count($list); $i++)
        {
            if($list[$i]['type'] == 1)
            {
                $list[$i]['type'] = '文字评论';
            }elseif($list[$i]['type'] == 2)
            {
                $list[$i]['type'] = '图片评论';
            }
        }


        $this->assign('list', $list);
        $this->display();
    }

    /*
     * 删除评论
     * */
    public function delpinglun()
    {
        //接收上传数据
        $id = trim($this->_post('pinglun_id'));
        $vid = trim($this->_post('pinglun_vid'));
        $button = trim($this->_post('delpinglunbutton'));
//        $find_where.video_id
        //把VID返回
        $this->assign('find_where',$find_where);
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
            //返回成功
            echo "<script>alert('数据删除成功!');window.location.href='".__APP__."/Pinglun/video_to_pinglun';</script>";
            $this -> success('数据删除成功!',"__APP__/Pinglun/video_to_pinglun);
        }

    }
}
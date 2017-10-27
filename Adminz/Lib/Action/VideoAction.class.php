
<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/26
 * Time: 9:21
 */
class VideoAction extends Action{
    //定义各模块锁定级别
    private $lock_index    = '9';
    private $lock_delvideo_do     = '9';
    private $lock_addvideo       = '9';
    private $lock_addvideo_do     = '9';
    private $lock_editvideo     = '9';
    private $lock_editvideo_do     = '9';


    public function index(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收查询条件数据
        $find_biaoti = trim($this->_request('find_biaoti'));
        $find_biaotichild = trim($this->_request('find_biaotichild'));
        $find_maketime = trim($this->_request('find_maketime'));
        $get_sta_day = trim($this->_request('find_sta_date'));
        $get_end_day = trim($this->_request('find_end_date'));
        $find_classify1 = trim($this->_request('find_classify1'));
        $find_classify2 = trim($this->_request('find_classify2'));
        $find_classify3 = trim($this->_request('find_classify3'));
        $find_classify4 = trim($this->_request('find_classify4'));

        //返回查询数据显示到页面上
        $this->assign('find_biaoti', $find_biaoti);
        $this->assign('find_biaotichild', $find_biaotichild);
        $this->assign('find_maketime', $find_maketime);
        $this->assign('find_sta_date', $get_sta_day);
        $this->assign('find_end_date', $get_end_day);
        $this->assign('find_classify1', $find_classify1);
        $this->assign('find_classify2', $find_classify2);
        $this->assign('find_classify3', $find_classify3);
        $this->assign('find_classify4', $find_classify4);

        $where_end_day = $get_end_day . ' 23:59:59';
        $where_sta_day = $get_sta_day . ' 00:00:00';

        //判断是否有查询条件
        $condition = "biaoti like '%".$find_biaoti."%' and biaotichild like '%".$find_biaotichild."%'" ;

        if($find_maketime)
        {
            $condition .= " and maketime = '".$find_maketime."'";
        }
        if($get_sta_day)
        {
            $condition .= " and create_datetime >= '".$where_sta_day."'";
        }
        if($get_end_day)
        {
            $condition .= " and create_datetime <= '".$where_end_day."'";
        }
        if($find_classify1)
        {
            $condition .= " and classify1 = '".$find_classify1."'";
        }
        if($find_classify2)
        {
            $condition .= " and classify2 = '".$find_classify2."'";
        }
        if($find_classify3)
        {
            $condition .= " and classify3 = '".$find_classify3."'";
        }
        if($find_classify4)
        {
            $condition .= " and classify4 = '".$find_classify4."'";
        }

//        var_dump($condition);die;
        $Model = new Model();

        //分页
        import('ORG.Page');// 导入分页类
        $count = $Model->table('sixty_video')
            ->where($condition)
            ->count();// 查询满足要求的总记录数
        $Page = new Page($count, 2);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('page', $show);// 赋值分页输出

        //执行数据查询
        $list = $Model -> table('sixty_video') -> field('id, classify1, classify2, classify3, classify4, msgjihe, showimg, videosavename,biaoti, biaotichild, jieshao, maketime, huafeimoney, tishishuoming, create_datetime')
            -> where($condition) ->limit($Page->firstRow . ',' . $Page->listRows) ->select();

        //输出到模板
        $this->assign('list', $list);
        $this->display();
    }

    public function addvideo()
    {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addvideo);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        $this->display();
    }

    public function addvideo_do()
    {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addvideo_do);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收数据
        $biaoti = trim($this->_post('biaoti'));
        $biaotichild = trim($this->_post('biaotichild'));
        $classify1 = trim($this->_post('classify1'));
        $classify2 = trim($this->_post('classify2'));
        $classify3 = trim($this->_post('classify3'));
        $classify4 = trim($this->_post('classify4'));
        $jieshao = trim($this->_post('jieshao'));
        $maketime = trim($this->_post('maketime'));
        $tishishuoming = trim($this->_post('tishishuoming'));
        $huafeimoney = trim($this->_post('huafeimoney'));

        //判断提交的视频介绍内容长度
        $len = mb_strlen($jieshao,'UTF-8');
        if($len > 200)
        {
            //超过200返回错误
                echo "<script>alert('视频介绍内容超过200字，不能提交！');history.go(-1);</script>";
                $this -> error('视频介绍内容超过200字，不能提交！');
        }

        //判断提交的视频提示内容长度
        $len = mb_strlen($tishishuoming,'UTF-8');
        if($len > 200)
        {
            //超过200返回错误
            echo "<script>alert('视频提示内容超过200字，不能提交！');history.go(-1);</script>";
            $this -> error('视频提示内容超过200字，不能提交！');
        }

        //准备SQL数据数组
        $create_datetime = date('Y-m-d H:i:s',time());
        $videosavename = mt_rand(1111,11111);
        $showimg = mt_rand(1111,11111);
        $msgjihe = mt_rand(1111,11111);
        $data = array('biaoti' => $biaoti, 'biaotichild' => $biaotichild, 'classify1' => $classify1,
            'classify2' => $classify2, 'classify3' => $classify3, 'classify4' => $classify4,
            'jieshao' => $jieshao, 'maketime' => $maketime, 'tishishuoming' => $tishishuoming,
            'huafeimoney' => $huafeimoney, 'create_datetime' => $create_datetime, 'videosavename' => $videosavename,
            'showimg' => $showimg, 'msgjihe' => $msgjihe);
        $Model = new Model();
        $result = $Model -> table('sixty_video') -> add($data);

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //判断结果
        if($result)
        {
            //成功返回成功
            echo "<script>alert('视频添加成功!');window.location.href='".__APP__."/Video/index';</script>";
            $this -> success('视频添加成功!','__APP__/Video/index');
        }else{
            //失败返回错误
            echo "<script>alert('视频添加失败！');history.go(-1);</script>";
            $this -> error('视频添加失败！');
        }

    }

    public function editvideo()
    {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editvideo);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //获取数据
        $id = $this->_post('video_id');

        //执行查询
        $Model = new Model();
        $list = $Model -> table('sixty_video') -> field('id, biaoti, biaotichild, classify1, classify2, classify3, classify4, jieshao, 
        maketime, huafeimoney, tishishuoming, showimg') -> where("id = '".$id."'") -> find();

        //输出到模板
        $this->assign('list', $list);
        $this->display();
    }

    public function editvideo_do()
    {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editvideo_do);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收数据
        $id = trim($this->_post('id'));
        $biaoti = trim($this->_post('biaoti'));
        $biaotichild = trim($this->_post('biaotichild'));
        $classify1 = trim($this->_post('classify1'));
        $classify2 = trim($this->_post('classify2'));
        $classify3 = trim($this->_post('classify3'));
        $classify4 = trim($this->_post('classify4'));
        $jieshao = trim($this->_post('jieshao'));
        $maketime = trim($this->_post('maketime'));
        $tishishuoming = trim($this->_post('tishishuoming'));
        $huafeimoney = trim($this->_post('huafeimoney'));

        //判断提交的视频介绍内容长度
        $len = mb_strlen($jieshao,'UTF-8');
        if($len > 200)
        {
            //超过200返回错误
            echo "<script>alert('视频介绍内容超过200字，不能提交！');history.go(-1);</script>";
            $this -> error('视频介绍内容超过200字，不能提交！');
        }

        //判断提交的视频提示内容长度
        $len = mb_strlen($tishishuoming,'UTF-8');
        if($len > 200)
        {
            //超过200返回错误
            echo "<script>alert('视频提示内容超过200字，不能提交！');history.go(-1);</script>";
            $this -> error('视频提示内容超过200字，不能提交！');
        }

        //准备SQL数据数组
        $create_datetime = date('Y-m-d H:i:s',time());
        $videosavename = mt_rand(1111,11111);
        $showimg = mt_rand(1111,11111);
        $msgjihe = mt_rand(1111,11111);
        $data = array('biaoti' => $biaoti, 'biaotichild' => $biaotichild, 'classify1' => $classify1,
            'classify2' => $classify2, 'classify3' => $classify3, 'classify4' => $classify4,
            'jieshao' => $jieshao, 'maketime' => $maketime, 'tishishuoming' => $tishishuoming,
            'huafeimoney' => $huafeimoney, 'create_datetime' => $create_datetime, 'videosavename' => $videosavename,
            'showimg' => $showimg, 'msgjihe' => $msgjihe);

        //执行更新
        $Model = new Model();
        $result = $Model -> table('sixty_video') -> where("id='".$id."'") -> save($data);

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //判断结果
        if($result)
        {
            //成功返回成功
            echo "<script>alert('数据修改成功!');window.location.href='".__APP__."/Video/index';</script>";
            $this -> success('数据修改成功!','__APP__/Video/index');
        }else{
            //失败返回错误
            echo "<script>alert('数据修改失败！');history.go(-1);</script>";
            $this -> error('数据修改失败！');
        }
    }

    public function delvideo_do()
    {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_delvideo_do);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $id = trim($this->_post('del_id'));

        $Modle = new Model();
        $res_id = $Modle -> table('sixty_video') -> field('id') -> where("id='".$id."'") -> find();

        //判断ID是否存在
        if(!$res_id)
        {
            //ID不存在
            echo "<script>alert('删除失败，此ID不存在！');history.go(-1);</script>";
            $this -> error('删除失败，此ID不存在！');
        }
        //执行删除
        $result = $Modle -> table('sixty_video') -> where("id = '".$id."'") -> delete();

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        if($result)
        {
            //成功返回成功
            echo "<script>alert('数据删除成功!');window.location.href='".__APP__."/Video/index';</script>";
            $this -> success('数据删除成功!','__APP__/Video/index');
        }else{
            //失败返回错误
            echo "<script>alert('数据删除失败！');history.go(-1);</script>";
            $this -> error('数据删除失败！');
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
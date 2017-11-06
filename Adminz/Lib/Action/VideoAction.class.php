
<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/26
 * Time: 9:21
 */
class VideoAction extends Action{
    //定义各模块锁定级别
    private $lock_index         = '9';
    private $lock_delvideo_do   = '9';
    private $lock_addvideo      = '9';
    private $lock_addvideo_do   = '9';
    private $lock_editvideo     = '9';
    private $lock_editvideo_do  = '9';


    public function index()
    {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=


        //接收查询条件数据
        $find_biaoti = trim($this->_get('find_biaoti'));
        $find_biaotichild = trim($this->_get('find_biaotichild'));
        $find_maketime = trim($this->_get('find_maketime'));
        $get_sta_day = trim($this->_get('find_sta_date'));
        $get_end_day = trim($this->_get('find_end_date'));
        $find_classify1 = trim($this->_get('find_classify1'));
        $find_classify2 = trim($this->_get('find_classify2'));
        $find_classify3 = trim($this->_get('find_classify3'));
        $find_classify4 = trim($this->_get('find_classify4'));
        $flag = trim($this->_get('find_flag'));
        $id = trim($this->_get('find_id'));

        //判断查询日期是否提交
        if($get_sta_day == '') {
            $get_sta_day = date('Y-m-d', strtotime('-6 months',time()));
        }
        if($get_end_day == '') {
            $get_end_day = date('Y-m-d', time());
        }

        //搜索分类表
        $Model = new Model();

        //返回查询数据显示到页面上
        $this->assign('find_biaoti', $find_biaoti);
        $this->assign('find_biaotichild', $find_biaotichild);
        $this->assign('find_id', $id);
        $this->assign('find_sta_date', $get_sta_day);
        $this->assign('find_end_date', $get_end_day);
        $this->assign('find_classify1', $find_classify1);
        $this->assign('find_classify2', $find_classify2);
        $this->assign('find_classify3', $find_classify3);
        $this->assign('find_classify4', $find_classify4);

        $where_end_day = $get_end_day . ' 23:59:59';
        $where_sta_day = $get_sta_day . ' 00:00:00';

        //判断是否传入状态
        if($flag == '') {
            //状态值为4
            $flag = 4;
        }

        //判断是否有查询条件
        $condition = "(biaoti like '%" . $find_biaoti . "%' or biaotichild like '%" . $find_biaoti . "%')";
        //判断是否查询创建时间
        if ($find_maketime != '') {
            $condition .= " and maketime = '" . $find_maketime . "'";
        }
        //判断是否查询起始日期
        if ($get_sta_day != '') {
            $condition .= " and create_datetime >= '" . $where_sta_day . "'";
        }
        //判断是否查询结束日期
        if ($get_end_day != '') {
            $condition .= " and create_datetime <= '" . $where_end_day . "'";
        }
        //判断是否查询分类1
        if ($find_classify1 != '') {
            $condition .= " and classify1 = '" . $find_classify1 . "'";
        }
        //判断是否查询分类2
        if ($find_classify2 != '') {
            $condition .= " and classify2 = '" . $find_classify2 . "'";
        }
        //判断是否查询分类3
        if ($find_classify3 != '') {
            $condition .= " and classify3 = '" . $find_classify3 . "'";
        }
        //判断是否查询分类4
        if ($find_classify4 != '') {
            $condition .= " and classify4 = '" . $find_classify4 . "'";
        }
        //判断是否查询ID
        if($id != '')
        {
            $condition .= " and id= '" . $id . "'";
        }
        //判断是否查询状态
        if($flag != 4 && $flag != '')
        {
            $condition .= " and flag = '" . $flag . "'";
        }

        // 动态下拉列表
        //start--------------------------------------------------------------
        //动态生成权限下拉选项
        $flagarr = array(
            '4' => '4-全选',
            '1' => '1-已开启',
            '2' => '2-已关闭',
        );

        $flag_show = '';
        foreach($flagarr as $keyr => $valr) {
            $flag_show .= '<option value="'.$keyr.'" ';
            if($keyr==$flag) {
                $flag_show .= ' selected="selected"';
            }
            $flag_show .= '>'.$valr.'</option>';

        }
        $this -> assign('flag_show',$flag_show);
        //end--------------------------------------------------------------


        //分页
        import('ORG.Page');// 导入分页类
        $count = $Model->table('sixty_video')
            ->where($condition)
            -> order('create_datetime desc')
            ->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('page', $show);// 赋值分页输出

        //执行数据查询
        $list = $Model->table('sixty_video')
            ->field('id, flag, classify1, classify2, classify3, classify4, msgjihe, showimg, videosavename, biaoti,
             biaotichild, fenshu, jieshao, maketime, huafeimoney, tishishuoming, create_datetime')
            -> where($condition) -> order('create_datetime desc') -> limit($Page->firstRow . ',' . $Page->listRows)->select();
        //判断是否有数据取出
        $num = count($list);
        if ($num <= 0) {
            $this->assign('list', $list);
            $this->display();
            die;
        }

        //取出ID，拼接为数组
        $arr_id = array();
        foreach ($list as $key => $value) {
            $arr_id[] = $value['id'];
        }
        //把数组拼接为字符串
        $str_id = implode(',', $arr_id);

        //根据ID查询材料表中的数据
        $cailiao_where = array();
        $cailiao_where['vid'] = array('in', "$str_id");
        $list_cailiao = $Model->table('sixty_video_cailiao')->field('vid, name, yongliang')
            ->where($cailiao_where)->select();

        //根据ID查询合集表中的数据
        $list_heji = $Model->table('sixty_jihemsg')->field('id, name')->select();

        //空数组保存材料表信息
        $arr_cailiao = array();
        //遍历材料结果集
        foreach ($arr_id as $cl_key => $cl_value) {
            foreach ($list_cailiao as $cl_ke => $cl_va) {
                //把此条材料的VID当做键，yongliao当做键值赋给材料表新数组
                $cl_va['vid'] = $cl_value;
                $arr_cailiao[$cl_value] .= $cl_va['name'];
                $arr_cailiao[$cl_value] .= $cl_va['yongliang'];
                //在一种材料及用量后添加换行
                $arr_cailiao[$cl_value] .= '<br/>';
            }
        }

        //根据ID查询步骤表
        $buzhou_where = array();
        $buzhou_where['vid'] = array('in', "$str_id");
        $list_buzhou = $Model->table('sixty_video_buzhou')->field('vid, buzhouid , buzhoucontent')
            ->where($buzhou_where)-> order('vid asc, buzhouid asc') -> select();


        //定义新数组，用于拼接输出到页面的数组
        $video_list = array();

        //遍历视频列表数组
        foreach ($list as $key_video => $val_video) {
            //视频ID
            $v_id = $val_video['id'];
            //定义步骤键键值为空
            $val_video['buzhou'] = '';
            //转变提示信息键的值，使它可以在输出时可以换行
            $val_video['tishishuoming'] = nl2br($val_video['tishishuoming']);
            $flag = $val_video['flag'];
            if($flag == 1) {
                $val_video['flag'] = '<span style="background-color:#33FF66;padding:3px;">1-开启</span>';

            }else {
                $val_video['flag'] = '<span style="background-color:#FFFF00;padding:3px;">2-关闭</span>';
            }
            $showimg = $val_video['showimg'];
            $imgwidth = '100';
            $imgheight = '100';
            $addressimg = hy_qiniuimgurl('sixty-videoimage',$showimg,$imgwidth,$imgheight);
            $val_video['showimg'] = "<img src='" . $addressimg . "' />";
            //定义评论字段为一个数组
            $val_video['pinglun'] = array();

            //遍历材料结果集
            foreach ($arr_cailiao as $key_cailiao => $val_cailiao) {
                //判断此视频ID在材料数组中是否存在
                if($arr_cailiao[$v_id] != '') {
                    //存在，把内容放入视频列
                    $val_video['cailiao'] = $arr_cailiao[$v_id];
                }else {
                    //不存在，此视频键内容为空
                    $val_cailiao['cailiao'] = '';
                }
            }

            //遍历步骤结果集
            foreach ($list_buzhou as $key_buzhou => $val_buzhou) {
                //判断步骤VID是否等于视频ID
                if($val_buzhou['vid'] == $v_id) {
                    //步骤VID与视频ID相等，把键值付给视频数组步骤键中
                    $val_video['buzhou'] .= $val_buzhou['buzhouid'] . '.' . $val_buzhou['buzhoucontent'] . '<br/>';
                }
            }

            //遍历合集结果集
            foreach ($list_heji as $key_heji => $val_heji) {
                if($val_video['msgjihe'] == $val_heji['id']){
                    $val_video['msgjihe'] = $val_heji['name'];
                    break;
                }
            }
            //把此视频ID的材料，步骤，评论信息存入输出数组
            $video_list[] = $val_video;
        }


        //输出到模板
        $this->assign('list', $video_list);
        $this->display();
    }

    public function addvideo() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addvideo);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //        动态下拉列表、
        //start--------------------------------------------------------------
        //动态生成权限下拉选项
        $videoarr = array(
            '1' => '1-启用',
            '2' => '2-禁用',
        );

        $videoflag_show = '';
        foreach($videoarr as $keyr => $valr) {
            $videoflag_show .= '<option value="'.$keyr.'" ';
            if($keyr==$lock) {
                $videoflag_show .= ' selected="selected"';
            }
            $videoflag_show .= '>'.$valr.'</option>';
        }
        $this -> assign('videoflag_show',$videoflag_show);
        //end--------------------------------------------------------------

        //查询合集
        $Model = new Model();
        $list_heji = $Model -> table('sixty_jihemsg') -> field('id, name') -> select();

        $heji_arr = array();
        foreach($list_heji as $key_heji => $val_heji){
            $heji_arr[$val_heji['id']] = $val_heji['name'];
        }

        //        动态下拉列表、
        //start--------------------------------------------------------------
        //动态生成权限下拉选项
        $videoheji_show = '';
        foreach($heji_arr as $keyr => $valr) {
            $videoheji_show .= '<option value="'.$keyr.'" ';
            if($keyr==$lock) {
                $videoheji_show .= ' selected="selected"';
            }
            $videoheji_show .= '>'.$valr.'</option>';
        }

        $this -> assign('videoheji_show',$videoheji_show);
        //end--------------------------------------------------------------

        //查询分类
        $Model = new Model();
        $list_class = $Model -> table('sixty_classifymsg') -> field('id, name, level') -> select();
        $class_arr_one = array();
        $class_arr_two = array();
        $class_arr_three = array();
        $class_arr_four = array();
        foreach($list_class as $key_class => $val_class){
            $level = $val_class['level'];
            if($level == 1){
                $class_arr_one[$val_class['id']] = $val_class['name'];
            }
            if($level == 2){
                $class_arr_two[$val_class['id']] = $val_class['name'];
            }
            if($level == 3){
                $class_arr_three[$val_class['id']] = $val_class['name'];
            }
            if($level == 4){
                $class_arr_four[$val_class['id']] = $val_class['name'];
            }
        }

        $one_select = $this->downlist($class_arr_one);
        $two_select = $this->downlist($class_arr_two);
        $three_select = $this->downlist($class_arr_three);
        $four_select = $this->downlist($class_arr_four);

        $this -> assign('one_select',$one_select);
        $this -> assign('two_select',$two_select);
        $this -> assign('three_select',$three_select);
        $this -> assign('four_select',$four_select);
        $this->display();
    }

    public function addvideo_do() {
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
        $flag = trim($this->_post('flag'));
        $videosavename = trim($this->_post('videosavename'));
        $msgjihe = trim($this->_post('msgjihe'));


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


        //判断文件是否上传
        $file = $_FILES['showimg']['name'];
        if($file != ''){
            import('ORG.UploadFile');
            $upload = new UploadFile();// 实例化上传类
            $upload->maxSize  = 3145728 ;// 设置附件上传大小
            $upload->saveRule  = date('YmdHis',time()) . mt_rand();// 设置附件上传大小
            $upload->allowExts  = array('jpg');// 设置附件上传类型
            $upload->savePath =  BASEDIR.'Public/Images/video_showimg/';// 设置附件上传目录
            if(!$upload->upload()) {// 上传错误提示错误信息
                echo "<script>alert('图片上传失败!');history.go(-1);</script>";
                $this -> error('图片上传失败!');
            }else{// 上传成功 获取上传文件信息
                $info =  $upload->getUploadFileInfo();
                $showimg = $info['0']['savename'];

                //上传七牛云
                //上传图片存储绝对路径
                $cz_filepathname  = BASEDIR.'Public/Images/video_showimg/'.$showimg;

                if(false===func_isImage($cz_filepathname)) {
                    //解析失败，非正常图片---后台图片上传时本函数可不使用
                    @unlink($cz_filepathname); //删除文件
                    //非正常图片
                }else {
                    //上传到七牛云之前先进行图片格式转换，统一使用jpg格式,图片格式转换
                    $r = hy_resave2jpg($cz_filepathname);
                    if($r!==false) {
                        //图片格式转换成功，赋值新名称
                        $cz_filepathname = $r;
                        $cz_filepathname = str_replace('\\','/',$cz_filepathname);
                    }
                    //上传到七牛云
                    //参数，bucket，文件绝对路径名称，存储名称，是否覆盖同名文件
                    $r = upload_qiniu('sixty-videoimage',$cz_filepathname,$showimg,'yes');

                    if(false===$r) {
                        @unlink($cz_filepathname); //删除文件
                        //上传失败
                    }else {
                        @unlink($cz_filepathname); //删除文件
                        //上传成功
                    }
                }
            }

        }


        //准备SQL数据数组
        $create_datetime = date('Y-m-d H:i:s',time());
        $data = array(
            'biaoti' => $biaoti,
            'biaotichild' => $biaotichild,
            'classify1' => $classify1,
            'classify2' => $classify2,
            'classify3' => $classify3,
            'classify4' => $classify4,
            'jieshao' => $jieshao,
            'maketime' => $maketime,
            'tishishuoming' => $tishishuoming,
            'huafeimoney' => $huafeimoney,
            'create_datetime' => $create_datetime,
            'videosavename' => $videosavename,
            'showimg' => $showimg,
            'msgjihe' => $msgjihe,
            'flag' => $flag,
            );

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
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);

        //获取数据
        $id = $this->_post('video_id');

        //执行查询
        $Model = new Model();
        $list = $Model -> table('sixty_video') -> field('id, fenshu, flag, biaoti, biaotichild, classify1, classify2, classify3, classify4, jieshao, 
        maketime, huafeimoney, tishishuoming, showimg') -> where("id = '".$id."'") -> find();

        //查询合集
        $Model = new Model();
        $list_heji = $Model -> table('sixty_jihemsg') -> field('id, name') -> select();
        //合集数组
        $heji_arr = array();
        foreach($list_heji as $key_heji => $val_heji){
            $heji_arr[$val_heji['id']] = $val_heji['name'];
        }


        //查询分类
        $Model = new Model();
        $list_class = $Model -> table('sixty_classifymsg') -> field('id, name, level') -> select();
        $class_arr_one = array();
        $class_arr_two = array();
        $class_arr_three = array();
        $class_arr_four = array();
        foreach($list_class as $key_class => $val_class){
            $level = $val_class['level'];
            if($level == 1){
                $class_arr_one[$val_class['id']] = $val_class['name'];
            }
            if($level == 2){
                $class_arr_two[$val_class['id']] = $val_class['name'];
            }
            if($level == 3){
                $class_arr_three[$val_class['id']] = $val_class['name'];
            }
            if($level == 4){
                $class_arr_four[$val_class['id']] = $val_class['name'];
            }
        }

        //启用禁用数组
        $videoarr = array(
            '1' => '1-启用',
            '2' => '2-禁用',
        );
        $flag = $list['flag'];

        //执行生成下拉菜单
        $heji_arr = $this->downlist($heji_arr);
        $one_select = $this->downlist($class_arr_one,$list['classify1']);
        $two_select = $this->downlist($class_arr_two,$list['classify2']);
        $three_select = $this->downlist($class_arr_three,$list['classify3']);
        $four_select = $this->downlist($class_arr_four,$list['classify4']);
        $videoflag_show = $this->downlist($videoarr,$flag);


        //输出到模板
        $this -> assign('one_select',$one_select);
        $this -> assign('two_select',$two_select);
        $this -> assign('three_select',$three_select);
        $this -> assign('four_select',$four_select);
        $this -> assign('heji_arr',$heji_arr);
        $this -> assign('videoflag_show',$videoflag_show);


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
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);

        //接收数据
        $id = trim($this->_post('id'));
        $biaoti = trim($this->_post('biaoti'));
        $biaotichild = trim($this->_post('biaotichild'));
        $classify1 = trim($this->_post('classify1'));
        $classify2 = trim($this->_post('classify2'));
        $classify3 = trim($this->_post('classify3'));
        $classify4 = trim($this->_post('classify4'));
        $jieshao = trim($this->_post('jieshao'));
        $fenshu = trim($this->_post('fenshu'));
        $flag = trim($this->_post('flag'));
        $maketime = trim($this->_post('maketime'));
        $tishishuoming = trim($this->_post('tishishuoming'));
        $huafeimoney = trim($this->_post('huafeimoney'));
        $videosavename = trim($this->_post('edit_videosavename'));
        $msgjihe = trim($this->_post('edit_msgjihe'));


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
        //获取旧数据信息
        $Model = new Model();
        $res_old = $Model -> table('sixty_video') -> field('id,showimg') -> where("id='".$id."'") -> find();
        $show_id = $res_old['id'];
        if(!$show_id){
            echo "<script>alert('非法进入该页面！');history.go(-1);</script>";
            $this -> error('非法进入该页面！');
        }
        $show_old = $show_old['showimg'];
        //准备SQL数据数组
        $create_datetime = date('Y-m-d H:i:s',time());

        //判断文件是否上传
        $file = $_FILES['showimg']['name'];
        if($file != ''){
            import('ORG.UploadFile');
            $upload = new UploadFile();// 实例化上传类
            $upload->maxSize  = 3145728 ;// 设置附件上传大小
            $upload->saveRule  = date('YmdHis',time()) . mt_rand();// 设置附件上传大小
            $upload->allowExts  = array('jpg');// 设置附件上传类型
            $upload->savePath =  BASEDIR.'Public/Images/video_showimg/';// 设置附件上传目录
            if(!$upload->upload()) {// 上传错误提示错误信息
                echo "<script>alert('图片上传失败!');history.go(-1);</script>";
                $this -> error('图片上传失败!');
            }else{// 上传成功 获取上传文件信息
                $info =  $upload->getUploadFileInfo();
                $showimg = $info['0']['savename'];

                //上传七牛云
                //上传图片存储绝对路径
                $cz_filepathname  = BASEDIR.'Public/Images/video_showimg/'.$showimg;

                if(false===func_isImage($cz_filepathname)) {
                    //解析失败，非正常图片---后台图片上传时本函数可不使用
                    @unlink($cz_filepathname); //删除文件
                    //非正常图片
                }else {
                    //上传到七牛云之前先进行图片格式转换，统一使用jpg格式,图片格式转换
                    $r = hy_resave2jpg($cz_filepathname);
                    if($r!==false) {
                        //图片格式转换成功，赋值新名称
                        $cz_filepathname = $r;
                        $cz_filepathname = str_replace('\\','/',$cz_filepathname);
                    }
                    //上传到七牛云
                    //参数，bucket，文件绝对路径名称，存储名称，是否覆盖同名文件
                    $r = upload_qiniu('sixty-videoimage',$cz_filepathname,$showimg,'yes');

                    if(false===$r) {
                        @unlink($cz_filepathname); //删除文件
                        //上传失败
                    }else {
                        @unlink($cz_filepathname); //删除文件
                        //上传成功
                    }

                    //删除七牛云旧图片
                    delete_qiniu('sixty-videoimage', $show_old);
                    //准备更新数组
                    $data = array('biaoti' => $biaoti, 'biaotichild' => $biaotichild, 'classify1' => $classify1,
                        'classify2' => $classify2, 'classify3' => $classify3, 'classify4' => $classify4,
                        'jieshao' => $jieshao, 'maketime' => $maketime, 'tishishuoming' => $tishishuoming,
                        'huafeimoney' => $huafeimoney, 'create_datetime' => $create_datetime, 'videosavename' => $videosavename,
                        'showimg' => $showimg, 'msgjihe' => $msgjihe, 'fenshu' => $fenshu, 'flag' => $flag);
                }
            }
        }else{
            //准备更新数组
            $data = array('biaoti' => $biaoti, 'biaotichild' => $biaotichild, 'classify1' => $classify1,
                'classify2' => $classify2, 'classify3' => $classify3, 'classify4' => $classify4,
                'jieshao' => $jieshao, 'maketime' => $maketime, 'tishishuoming' => $tishishuoming,
                'huafeimoney' => $huafeimoney, 'create_datetime' => $create_datetime, 'videosavename' => $videosavename,
                'msgjihe' => $msgjihe, 'fenshu' => $fenshu, 'flag' => $flag);
        }

        //执行更新
        $result = $Model -> table('sixty_video') -> where("id='".$id."'") -> save($data);

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //判断结果
        if($result)
        {
            //成功返回成功
            echo "<script>alert('数据修改成功!');window.location.href='".__APP__.'/Video/index'.$echourl."';</script>";
            $this -> success('数据修改成功!','__APP__'.$echourl);
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
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);

        //接收上传数据
        $id = trim($this->_post('del_id'));

        $Model = new Model();
        $res = $Model -> table('sixty_video') -> field('id, showimg') -> where("id='".$id."'") -> find();

        //判断ID是否存在
        if(!$res['id'])
        {
            //ID不存在
            echo "<script>alert('删除失败，此ID不存在！');history.go(-1);</script>";
            $this -> error('删除失败，此ID不存在！');
        }
        $show_old = $res['showimg'];
        //删除七牛云旧图片
        delete_qiniu('sixty-videoimage', $show_old);
        //执行删除
        $result = $Model -> table('sixty_video') -> where("id = '".$id."'") -> delete();

        //写入日志
        $templogs = $Model -> getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        if($result)
        {
            //成功返回成功
            echo "<script>alert('数据删除成功!');window.location.href='".__APP__.'/Video/index'.$echourl."';</script>";
            $this -> success('数据删除成功!','__APP__'.$echourl);
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

    public function downlist($arr, $lock=''){

        //        动态下拉列表、
        //start--------------------------------------------------------------
        //动态生成权限下拉选项
//        var_dump($arr);die;
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
}
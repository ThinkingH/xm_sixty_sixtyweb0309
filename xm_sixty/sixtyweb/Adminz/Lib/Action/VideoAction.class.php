
<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/26
 * Time: 9:21
 */
class VideoAction extends Action{
    //定义各模块锁定级别
    private $lock_index         = '97';
    private $lock_delvideo_do   = '97';
    private $lock_addvideo      = '97';
    private $lock_addvideo_do   = '97';
    private $lock_editvideo     = '97';
    private $lock_editvideo_do  = '97';


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
        $find_msgjihe = trim($this->_get('find_msgjihe'));
        $flag = trim($this->_get('find_flag'));
        $sflag = trim($this->_get('find_sflag'));
        $id = trim($this->_get('find_id'));

        //判断查询日期是否提交
        if($get_sta_day == '') {
            $get_sta_day = date('Y-m-d', strtotime('-6 months',time()));
        }
        if($get_end_day == '') {
            $get_end_day = date('Y-m-d', time());
        }


        //查询合集
        $Model = new Model();
        $list_heji = $Model -> table('sixty_jihemsg') -> field('id, name') -> select();

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

        //插入全选和不选选项
        $class_arr_one = Array('-1' => '全选', '-2' => '未选') + $class_arr_one;
        $class_arr_two = Array('-1' => '全选', '-2' => '未选') + $class_arr_two;
        $class_arr_three = Array('-1' => '全选', '-2' => '未选') + $class_arr_three;
        $class_arr_four = Array('-1' => '全选', '-2' => '未选') + $class_arr_four;
        $heji_arr = Array('-2' => '未选') + $heji_arr;
        $heji_arr = Array('-1' => '全选') + $heji_arr;

        $one_select = $this->downlist($class_arr_one, $find_classify1);
        $two_select = $this->downlist($class_arr_two, $find_classify2);
        $three_select = $this->downlist($class_arr_three, $find_classify3);
        $four_select = $this->downlist($class_arr_four, $find_classify4);
        $heji_arr = $this->downlist($heji_arr, $find_msgjihe);


        //返回查询数据显示到页面上
        $this -> assign('one_select',$one_select);
        $this -> assign('two_select',$two_select);
        $this -> assign('three_select',$three_select);
        $this -> assign('four_select',$four_select);
        $this -> assign('msgjihe_select',$heji_arr);
        $this->assign('find_biaoti', $find_biaoti);
        $this->assign('find_biaotichild', $find_biaotichild);
        $this->assign('find_id', $id);
        $this->assign('find_sta_date', $get_sta_day);
        $this->assign('find_end_date', $get_end_day);

        $where_end_day = $get_end_day . ' 23:59:59';
        $where_sta_day = $get_sta_day . ' 00:00:00';

        //判断是否传入状态
        if($flag == '') {
            //状态值为4
            $flag = 4;
        }

        //判断是否传入展示状态
        if($sflag == '') {
            //状态值为4
            $sflag = 4;
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
        if ($find_classify1 != '-2' && $find_classify1 != '-1' && $find_msgjihe != '') {
            $condition .= " and classify1 = '" . $find_classify1 . "'";
        } else if($find_classify1 == '-2') {
            $condition .= " and classify1 = ''";
        }

        //判断是否查询分类2
        if ($find_classify2 != '-2' && $find_classify2 != '-1' && $find_msgjihe != '') {
            $condition .= " and classify2 = '" . $find_classify2 . "'";
        }else if($find_classify2 == '-2') {
            $condition .= " and classify2 = ''";
        }

        //判断是否查询分类3
        if ($find_classify3 != '-2' && $find_classify3 != '-1' && $find_msgjihe != '') {
            $condition .= " and classify3 = '" . $find_classify3 . "'";
        }else if($find_classify3 == '-2') {
            $condition .= " and classify3 = ''";
        }

        //判断是否查询分类4
        if ($find_classify4 != '-2' && $find_classify4 != '-1' && $find_msgjihe != '') {
            $condition .= " and classify4 = '" . $find_classify4 . "'";
        }else if($find_classify4 == '-2') {
            $condition .= " and classify4 = ''";
        }

        //判断是否查询合集信息
        if($find_msgjihe != '-1' && $find_msgjihe != '') {
            if($find_msgjihe == '-2') {
                $condition .= " and msgjihe = '0'";
            }else {
                $condition .= " and msgjihe = '" . $find_msgjihe . "'";
            }
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

        //判断是否查询首页展示状态
        if($sflag != 4 && $sflag != '')
        {
            $condition .= " and sflag = '" . $sflag . "'";
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

        //动态生成权限下拉选项
        $sflagarr = array(
            '4' => '4-全选',
            '1' => '1-已展示',
            '2' => '2-已隐藏',
        );

        $sflag_show = '';
        foreach($sflagarr as $keys => $vals) {
            $sflag_show .= '<option value="'.$keys.'" ';
            if($keys==$sflag) {
                $sflag_show .= ' selected="selected"';
            }
            $sflag_show .= '>'.$vals.'</option>';

        }
        $this -> assign('sflag_show',$sflag_show);
        //end--------------------------------------------------------------

        //分页
        import('ORG.Page');// 导入分页类
        $count = $Model->table('sixty_video')
            ->where($condition)
            ->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('page', $show);// 赋值分页输出
        //执行数据查询
        $list = $Model->table('sixty_video')
            ->field('id, flag, sflag, classify1, classify2, classify3, classify4, msgjihe, showimg, videosavename, biaoti,
             biaotichild, fenshu, jieshao, maketime, huafeimoney, tishishuoming, shicaititle, create_datetime')
            -> where($condition) -> order('id desc') -> limit($Page->firstRow . ',' . $Page->listRows) ->select();
        //判断是否有数据取出
        $num = count($list);
        if ($num <= 0) {
            $this->assign('list', $list);
            $this->display();
            exit;
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
            ->where($cailiao_where) -> order('id asc')->select();


        //根据ID查询合集表中的数据
        $list_heji = $Model->table('sixty_jihemsg')->field('id, name')->select();

//        //根据ID查询分类表数据
//        $list_class = $Model->table('sixty_classifymsg')->field('id, name, level')->select();
        //空数组保存材料表信息
        $arr_cailiao = array();


        //遍历材料结果集
        foreach ($arr_id as $cl_key => $cl_value) {
            foreach ($list_cailiao as $cl_ke => $cl_va) {
                //把此条材料的VID当做键，yongliao当做键值赋给材料表新数组
                if($cl_va['vid'] == $cl_value) {
                    $arr_cailiao[$cl_value] .= $cl_va['name'];
                    $arr_cailiao[$cl_value] .= $cl_va['yongliang'];
                    //在一种材料及用量后添加换行
                    $arr_cailiao[$cl_value] .= '<br/>';
                }
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

            //视频是否开启
            $flag = $val_video['flag'];
            if($flag == 1) {
                $val_video['flag'] = '<span style="background-color:#33FF66;padding:3px;">1-开启</span>';
            }else {
                $val_video['flag'] = '<span style="background-color:#FF82A5;padding:3px;">2-关闭</span>';
            }

            //视频是否首页展示
            $sflag = $val_video['sflag'];
            if($sflag == 1) {
                $val_video['sflag'] = '<span style="background-color:#33FF66;padding:3px;">1-展示</span>';

            }else {
                $val_video['sflag'] = '<span style="background-color:#FFFF00;padding:3px;">2-隐藏</span>';
            }

            //获取七牛云图片
            $showimg = $val_video['showimg'];
            $imgwidth = '100';
            $imgheight = '100';
            $addressimg = hy_qiniuimgurl('sixty-videoimage',$showimg,$imgwidth,$imgheight);
            $val_video['showimg'] = "<img src='" . $addressimg . "' />";

            //获取七牛云视频
            $video_url = hy_qiniubucketurl('sixty-video', $val_video['videosavename']);
//            var_dump($video_url);die;
            $val_video['video_url'] = "<a href='".$video_url."' target='_blank' class='yubuttons yuwhite'>预览视频</a>";

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
                    $val_video['cailiao'] = '';
                }
            }
            if($val_video['cailiao'] == '') {
                $val_video['cailiao'] = '<span style="background-color:#FFFF00;padding:3px;">未添加食材</span>';
            }

            //遍历步骤结果集
            foreach ($list_buzhou as $key_buzhou => $val_buzhou) {
                //判断步骤VID是否等于视频ID
                if($val_buzhou['vid'] == $v_id) {
                    //步骤VID与视频ID相等，把键值付给视频数组步骤键中
                    $val_video['buzhou'] .= $val_buzhou['buzhouid'] . '.' . $val_buzhou['buzhoucontent'] . '<br/>';
                }
            }
            if($val_video['buzhou'] == '') {
                $val_video['buzhou'] = '<span style="background-color:#FFFF00;padding:3px;">未添加步骤</span>';
            }

            //遍历合集结果集
            foreach ($list_heji as $key_heji => $val_heji) {
                if($val_video['msgjihe'] == $val_heji['id']){
                    $val_video['msgjihe'] = $val_heji['name'];
                }else if($val_video['msgjihe'] =='' || $val_video['msgjihe'] =='0'){
                    $val_video['msgjihe'] = '<span style="background-color:#FFFF00;padding:3px;">未选择集合</span>';
                }
            }
            if($val_video['classify1'] == '' || $val_video['classify1'] == '无'){
                $val_video['classify1'] = '<span style="background-color:#FFFF00;padding:3px;">未选择分类</span>';
            }
            if($val_video['classify2'] == '' || $val_video['classify2'] == '无'){
                $val_video['classify2'] = '<span style="background-color:#FFFF00;padding:3px;">未选择分类</span>';
            }
            if($val_video['classify3'] == '' || $val_video['classify3'] == '无'){
                $val_video['classify3'] = '<span style="background-color:#FFFF00;padding:3px;">未选择分类</span>';
            }
//
            //把此视频ID的材料，步骤，评论信息存入输出数组
            $video_list[] = $val_video;
        }

        //查询分类表

        //输出到模板
        $this->assign('list', $video_list);
        $this->display();
    }

    public function addvideo() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addvideo);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //        动态下拉列表、
        //start--------------------------------------------------------------
        //动态生成权限下拉选项
        $videoarr = array(
            '2' => '2-关闭',
            '1' => '1-开启',
        );

        $videosflagarr = array(
            '2' => '2-隐藏',
            '1' => '1-展示',
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

        $videosflag_show = '';
        foreach($videosflagarr as $keys => $vals) {
            $videosflag_show .= '<option value="'.$keys.'" ';
            if($keys==$lock) {
                $videosflag_show .= ' selected="selected"';
            }
            $videosflag_show .= '>'.$vals.'</option>';
        }
        $this -> assign('videosflag_show',$videosflag_show);
        //end--------------------------------------------------------------

        //查询合集
        $Model = new Model();
        $list_heji = $Model -> table('sixty_jihemsg') -> field('id, name') -> where('flag = 1') -> order('id DESC') -> limit('0','100') -> select();

        $heji_arr = array();
        foreach($list_heji as $key_heji => $val_heji){
            $heji_arr[$val_heji['id']] = $val_heji['name'];
        }

        //查询分类
        $list_class = $Model -> table('sixty_classifymsg') -> field('id, name, level') -> where('flag = 1') -> select();
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

        //查询小贴士表
        $list_tips = $Model -> table('sixty_tieshi_video') -> field('id, biaoti') -> where('flag = 1') -> select();
        $tips_arr = array();
        foreach($list_tips as $key_tips => $val_tips){

            $tips_arr[$val_tips['id'] ]= $val_tips['biaoti'];

        }


        //插入不选选项
        $class_arr_three = Array('' => '不选') + $class_arr_three;
        $class_arr_four = Array('' => '不选') + $class_arr_four;
        $heji_arr = Array('' => '不选') + $heji_arr;
        $tips_arr = Array('' => '不选') + $tips_arr;

        //生成下拉菜单
        $one_select = $this->downlist2($class_arr_one);
        $two_select = $this->downlist2($class_arr_two);
        $three_select = $this->downlist2($class_arr_three);
        $four_select = $this->downlist2($class_arr_four);
        $heji_arr = $this->downlist($heji_arr);
        $tips_arr = $this->downlist($tips_arr);

        $tips_show = array();
        for($i = 1; $i <=10; $i++){
            $tips_show[$i]['sel'] = $tips_arr;
            $tips_show[$i]['num'] = 'tips'.$i;
//            $this -> assign('tips'.$i,$tips_arr);
        }

        //传递到模板
        $this -> assign('videoheji_show',$heji_arr);
        $this -> assign('tips_show',$tips_show);

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
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
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
        $fenshu = trim($this->_post('fenshu'));
        $videosavename = trim($this->_post('videosavename'));
        $msgjihe = trim($this->_post('msgjihe'));
        $tiaozhaun = trim($this->_post('submitandshicai'));
        $sflag = trim($this->_post('sflag'));


        //判断是否提交视频标题
        if($biaoti == '')
        {
            echo "<script>alert('视频标题不能为空!');history.go(-1);</script>";
            $this -> error('视频标题不能为空!');
        }

        //判断是否提交视频子标题
        if($biaotichild == '')
        {
            echo "<script>alert('视频子标题不能为空!');history.go(-1);</script>";
            $this -> error('视频子标题不能为空!');
        }

//        //判断是否提交视频分类1
//        if($classify1 == '')
//        {
//            echo "<script>alert('视频分类1不能为空!');history.go(-1);</script>";
//            $this -> error('视频分类1不能为空!');
//        }

        //判断是否提交视频分类1
        if($classify2 == '')
        {
            echo "<script>alert('视频分类2不能为空!');history.go(-1);</script>";
            $this -> error('视频分类2不能为空!');
        }

//        //判断是否提交视频分类1
//        if($classify3 == '')
//        {
//            echo "<script>alert('视频分类3不能为空!');history.go(-1);</script>";
//            $this -> error('视频分类3不能为空!');
//        }


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


        $Model = new Model();
        //判断视频标题是否重名
        $old_biaoti = $Model -> table('sixty_video') -> field('id') -> where("biaoti='" . $biaoti . "'") -> find();
        //判断是否找到结果
        if($old_biaoti != '')
        {
            echo "<script>alert('此视频标题名已存在！');history.go(-1);</script>";
            $this -> error('此视频标题名已存在！');
        }


        //判断视频子标题是否重名
        $old_biaotichild = $Model -> table('sixty_video') -> field('id') -> where("biaotichild='" . $biaotichild . "'") -> find();
        //判断是否找到结果
        if($old_biaotichild != '')
        {
            echo "<script>alert('此视频子标题名已存在！');history.go(-1);</script>";
            $this -> error('此视频子标题名已存在！');
        }


        //判断文件是否上传
        $file = $_FILES['showimg']['name'];
        if($file != ''){
            import('ORG.UploadFile');
            $upload = new UploadFile();// 实例化上传类
            $upload->maxSize  = 2097152 ;// 设置附件上传大小
            $upload->saveRule  = date('YmdHis',time()) . mt_rand();// 设置附件上传文件名
            $upload->allowExts  = array('jpg','gif','png');// 设置附件上传类型
            $upload->savePath =  BASEDIR.'Public/Images/sixty-videoimage/';// 设置附件上传目录
            if(!$upload->upload()) {// 上传错误提示错误信息
                echo "<script>alert('图片上传失败!');history.go(-1);</script>";
                $this -> error('图片上传失败!');
            }else{// 上传成功 获取上传文件信息
                $info =  $upload->getUploadFileInfo();
                $showimg = $info['0']['savename'];

                //上传七牛云
                //上传图片存储绝对路径
                $cz_filepathname  = BASEDIR.'Public/Images/sixty-videoimage/'.$showimg;

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

        }else {
            echo "<script>alert('视频展示图片不能为空！');history.go(-1);</script>";
            $this -> error('视频展示图片不能为空！');
        }

        //接收小贴士
        $tips = '';
        for($i=1; $i <= 10; $i++){
            $tips .= trim($this->_post('tips'.$i)) . ',';
        }

        if($tips != ''){
            $tips = rtrim($tips,',');
        }

        //准备SQL数据数组
        $create_datetime = date('Y-m-d H:i:s',time());
        $data = array(
            'biaoti' => $biaoti,
            'biaotichild' => $biaotichild,
            'classify1' => '美食',
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
            'sflag' => $sflag,
            'fenshu' => $fenshu,
            'tips' => $tips,
            );

//        var_dump($data);die;
        //执行添加
        $result = $Model -> table('sixty_video') -> add($data);

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //判断结果
        if($result)
        {
            if($tiaozhaun != '' ) {
            //成功返回成功
                echo "<script>alert('视频添加成功!');window.location.href='".__APP__."/Food/addfood".$echourl.'&to_video_id='.$result."';</script>";
                $this -> success('视频添加成功!','__APP__/Food/addfood'.$echourl);
            }
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
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=


        //获取数据
        $id = $this->_post('video_id');

        //执行视频表查询
        $Model = new Model();
        $list = $Model -> table('sixty_video') -> field('id, fenshu, sflag, flag, biaoti, biaotichild, videosavename,
        classify1, classify2, classify3, classify4, jieshao, msgjihe, maketime, huafeimoney, tishishuoming, showimg, tips')
            -> where("id = '".$id."'") -> find();

        //查询合集
        $Model = new Model();
        $list_heji = $Model -> table('sixty_jihemsg') -> field('id, name') -> where('flag = 1') -> order('id DESC') -> limit('0','100') -> select();
        //合集数组
        $heji_arr = array();
        foreach($list_heji as $key_heji => $val_heji){
            $heji_arr[$val_heji['id']] = $val_heji['name'];
        }


        //查询分类
        $Model = new Model();
        $list_class = $Model -> table('sixty_classifymsg') -> where('flag = 1') -> field('id, name, level') -> select();

        //准备分类数组
        $class_arr_one = array();
        $class_arr_two = array();
        $class_arr_three = array();
        $class_arr_four = array();

        //遍历分类结果集
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
            '1' => '1-开启',
            '2' => '2-关闭',
        );
        $flag = $list['flag'];

        //首页展示数组
        $sflagarr = array(
            '1' => '1-展示',
            '2' => '2-隐藏',
        );
        $sflag = $list['sflag'];


        //查询小贴士表
        $list_tips = $Model -> table('sixty_tieshi_video') -> field('id, biaoti') -> where('flag = 1') -> select();

        //把字符串遍数组
        $tips_v = explode(',',$list['tips']);

        //遍历数组，整理为生成下拉列表格式
        $tips_arr = array();
        foreach($list_tips as $key_tips => $val_tips){

            $tips_arr[$val_tips['id'] ]= $val_tips['biaoti'];

        }


        //插入不选选项
        $class_arr_three = Array('' => '不选') + $class_arr_three;
        $class_arr_four = Array('' => '不选') + $class_arr_four;
        $heji_arr = Array('' => '不选') + $heji_arr;
        $tips_arr = Array('' => '不选') + $tips_arr;

        //生成10个小贴士下拉列表
        $tips_show = array();
        for($i = 1; $i <=10; $i++){
            foreach($tips_arr as $k_ta => $v_ta){
                if($k_ta == $tips_v[$i-1]){
                    $tips_change = $this->downlist($tips_arr,$tips_v[$i-1]);
                    $tips_show[$i]['sel'] = $tips_change;
                    $tips_show[$i]['num'] = 'tips'.$i;
//                    var_dump($tips_change);die;
                    break;
                }
            }


        }


        //执行生成下拉菜单
        $heji_arr = $this->downlist($heji_arr,$list['msgjihe']);
        $one_select = $this->downlist2($class_arr_one,$list['classify1']);
        $two_select = $this->downlist2($class_arr_two,$list['classify2']);
        $three_select = $this->downlist2($class_arr_three,$list['classify3']);
        $four_select = $this->downlist2($class_arr_four,$list['classify4']);
        $videoflag_show = $this->downlist($videoarr,$flag);
        $sflag_show = $this->downlist($sflagarr,$sflag);

        //获取七牛云图片
        $showimg = $list['showimg'];
        $imgwidth = '100';
        $imgheight = '100';
        $addressimg = hy_qiniuimgurl('sixty-videoimage',$showimg,$imgwidth,$imgheight);
        $list['showimg'] = "<img src='" . $addressimg . "' />";


        //输出到模板
        $this -> assign('one_select',$one_select);
        $this -> assign('two_select',$two_select);
        $this -> assign('three_select',$three_select);
        $this -> assign('four_select',$four_select);
        $this -> assign('heji_arr',$heji_arr);
        $this -> assign('videoflag_show',$videoflag_show);
        $this -> assign('sflag_show',$sflag_show);
        $this -> assign('tips_show',$tips_show);


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
        $sflag = trim($this->_post('sflag'));
        $maketime = trim($this->_post('maketime'));
        $tishishuoming = trim($this->_post('tishishuoming'));
        $huafeimoney = trim($this->_post('huafeimoney'));
        $videosavename = trim($this->_post('edit_videosavename'));
        $msgjihe = trim($this->_post('edit_msgjihe'));

        //判断是否提交视频标题
        if($biaoti == '')
        {
            //超过200返回错误
            echo "<script>alert('视频标题不能为空!');history.go(-1);</script>";
            $this -> error('视频标题不能为空!');
        }

        //判断是否提交视频子标题
        if($biaotichild == '')
        {
            //超过200返回错误
            echo "<script>alert('视频子标题不能为空!');history.go(-1);</script>";
            $this -> error('视频子标题不能为空!');
        }

        //判断是否提交视频分类2
        if($classify2 == '')
        {
            echo "<script>alert('视频分类2不能为空!');history.go(-1);</script>";
            $this -> error('视频分类2不能为空!');
        }

//        //判断是否提交视频分类3
//        if($classify3 == '')
//        {
//            echo "<script>alert('视频分类3不能为空!');history.go(-1);</script>";
//            $this -> error('视频分类3不能为空!');
//        }


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


        $Model = new Model();
        //判断视频标题是否重名
        $old_biaoti = $Model -> table('sixty_video') -> field('id')
            -> where("biaoti='" . $biaoti . "' and id <> '" . $id . "'") -> find();
        //判断是否找到结果
        if($old_biaoti != '')
        {
            echo "<script>alert('此视频标题名已存在！');history.go(-1);</script>";
            $this -> error('此视频标题名已存在！');
        }


        //判断视频子标题是否重名
        $old_biaotichild = $Model -> table('sixty_video') -> field('id')
            -> where("biaotichild='" . $biaotichild . "' and id <> '" . $id . "'") -> find();
        //判断是否找到结果
        if($old_biaotichild != '')
        {
            echo "<script>alert('此视频子标题名已存在！');history.go(-1);</script>";
            $this -> error('此视频子标题名已存在！');
        }

        //接收小贴士
        $tips = '';
        for($i=1; $i <= 10; $i++){
            $tips .= trim($this->_post('tips'.$i)) . ',';
        }

        if($tips != ''){
            $tips = rtrim($tips,',');
        }

        //获取旧数据信息
        $Model = new Model();
        $res_old = $Model -> table('sixty_video') -> field('id,showimg') -> where("id='".$id."'") -> find();
        $show_id = $res_old['id'];
        if(!$show_id){
            echo "<script>alert('非法进入该页面！');history.go(-1);</script>";
            $this -> error('非法进入该页面！');
        }
        $show_old = $res_old['showimg'];


        //准备SQL数据数组
        $create_datetime = date('Y-m-d H:i:s',time());


        //判断文件是否上传
        $file = $_FILES['showimg']['name'];
        if($file != ''){
            import('ORG.UploadFile');
            $upload = new UploadFile();// 实例化上传类
            $upload->maxSize  = 2097152 ;// 设置附件上传大小
            $upload->saveRule  = date('YmdHis',time()) . mt_rand();// 设置附件上传大小
            $upload->allowExts  = array('jpg','gif','png');// 设置附件上传类型
            $upload->savePath =  BASEDIR.'Public/Images/sixty-videoimage/';// 设置附件上传目录
            if(!$upload->upload()) {// 上传错误提示错误信息
                echo "<script>alert('图片上传失败!');history.go(-1);</script>";
                $this -> error('图片上传失败!');
            }else{// 上传成功 获取上传文件信息
                $info =  $upload->getUploadFileInfo();
                $showimg = $info['0']['savename'];

                //上传七牛云
                //上传图片存储绝对路径
                $cz_filepathname  = BASEDIR.'Public/Images/sixty-videoimage/'.$showimg;

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
                    $data = array('biaoti' => $biaoti,
                        'biaotichild' => $biaotichild,
                        'classify1' => '美食',
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
                        'fenshu' => $fenshu,
                        'flag' => $flag,
                        'sflag' => $sflag,
                        'tips' => $tips,
                    );
                }
            }
        }else{
            //准备更新数组
            $data = array(
                'biaoti' => $biaoti,
                'biaotichild' => $biaotichild,
                'classify1' => '美食',
                'classify2' => $classify2,
                'classify3' => $classify3,
                'classify4' => $classify4,
                'jieshao' => $jieshao,
                'maketime' => $maketime,
                'tishishuoming' => $tishishuoming,
                'huafeimoney' => $huafeimoney,
                'create_datetime' => $create_datetime,
                'videosavename' => $videosavename,
                'msgjihe' => $msgjihe,
                'fenshu' => $fenshu,
                'flag' => $flag,
                'sflag' => $sflag,
                'tips' => $tips,
            );
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
        $res = $Model -> table('sixty_video') -> field('id, showimg, flag') -> where("id='".$id."'") -> find();

        //判断ID是否存在
        if(!$res['id'])
        {
            //ID不存在
            echo "<script>alert('删除失败，此ID不存在！');history.go(-1);</script>";
            $this -> error('删除失败，此ID不存在！');
        }
        //判断视频是否处于开启状态
        if($res['flag'] == '1')
        {
            //ID不存在
            echo "<script>alert('视频处于开启状态，请关闭后再删除！');history.go(-1);</script>";
            $this -> error('视频处于开启状态，请关闭后再删除！');
        }
        $show_old = $res['showimg'];
        //删除七牛云视频旧图片
        delete_qiniu('sixty-videoimage', $show_old);


        //根据ID查询评论表
        $res_pinglun = $Model -> table('sixty_video_pinglun') -> field('id, showimg') -> where("vid='".$id."'") -> select();
        //遍历评论结果集
        foreach($res_pinglun as $key_pl => $val_pl) {
            //删除七牛云上的图片
            $del_pl = delete_qiniu('sixty-videoimage', $val_pl['showimg']);
        }

        //执行删除评论
        $result_pl = $Model -> table('sixty_video_pinglun') -> where("vid = '".$id."'") -> delete();

        //写入日志
        $templogs = $Model -> getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

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


    //动态下拉列表
    public function downlist($arr, $lock=''){

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

    }

    //动态下拉列表
    public function downlist2($arr, $lock=''){

        //动态生成权限下拉选项
        //$lock为空时，关联数组array[0]未默认选项
        $res_arr = '';

        if(!empty($arr)) {
            foreach ($arr as $keyr => $valr) {
                if($valr == '不选') {
                    $res_arr .= "<option value=''";
                }else {
                    $res_arr .= '<option value="' . $valr . '" ';
                }
                if ($valr == $lock) {
                    $res_arr .= ' selected="selected"';
                }
                $res_arr .= '>' . $valr . '</option>';
            }
        }else{

            $res_arr = "<option value='' selected='selected'>无</option>";
        }
        return $res_arr;

    }
}
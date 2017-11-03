
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


    public function index()
    {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);


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
        if($get_sta_day == '')
        {
            $get_sta_day = date('Y-m-d', strtotime('-6 months',time()));
        }
        if($get_end_day == '')
        {
            $get_end_day = date('Y-m-d', time());
        }

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
        if($flag == '')
        {
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
            '1' => '1-已启用',
            '2' => '2-已禁用',
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

        $Model = new Model();

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

        //空数组保存材料表信息
        $arr_cailiao = array();
        //遍历材料结果集
        foreach ($arr_id as $cl_key => $cl_value)
        {
            foreach ($list_cailiao as $cl_ke => $cl_va)
            {
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
        foreach ($list as $key_video => $val_video)
        {
            //视频ID
            $v_id = $val_video['id'];
            //定义步骤键键值为空
            $val_video['buzhou'] = '';
            //转变提示信息键的值，使它可以在输出时可以换行
            $val_video['tishishuoming'] = nl2br($val_video['tishishuoming']);
            $flag = $val_video['flag'];
            if($flag == 1)
            {
                $val_video['flag'] = '<span style="background-color:green;padding:3px;">1-开启</span>';

            }else{
                $val_video['flag'] = '已关闭';
            }
            //定义评论字段为一个数组
            $val_video['pinglun'] = array();

            //遍历材料结果集
            foreach ($arr_cailiao as $key_cailiao => $val_cailiao)
            {
                //判断此视频ID在材料数组中是否存在
                if($arr_cailiao[$v_id] != '')
                {
                    //存在，把内容放入视频列
                    $val_video['cailiao'] = $arr_cailiao[$v_id];
                }else{
                    //不存在，此视频键内容为空
                    $val_cailiao['cailiao'] = '';
                }
            }

            //遍历步骤结果集
            foreach ($list_buzhou as $key_buzhou => $val_buzhou)
            {
                //判断步骤VID是否等于视频ID
                if($val_buzhou['vid'] == $v_id)
                {
                    //步骤VID与视频ID相等，把键值付给视频数组步骤键中
                    $val_video['buzhou'] .= $val_buzhou['buzhouid'] . '.' . $val_buzhou['buzhoucontent'] . '<br/>';
                }
            }

            //遍历评论结果集
            //定义评论数组字段键名
            $i = 0;
            foreach ($list_pinglun as $key_pinglun => $val_pinglun)
            {
                //判断此条评论VID是否等于此条评论ID
                if($val_pinglun['vid'] == $v_id)
                {
                    //相等，继续遍历评论结果二维数组
                    foreach ($val_pinglun as $k_pinglun => $v_pinglun)
                    {
                        //把此条评论的评论内容以及其他信息存入视频数组
                        $val_video['pinglun'][$i][$k_pinglun] = $v_pinglun;
                    }
                }
                //键名自增1
                $i++;
            }
            //把此视频ID的材料，步骤，评论信息存入输出数组
            $video_list[] = $val_video;
        }



        //输出到模板
        $this->assign('list', $video_list);
        $this->display();
    }

    public function addvideo()
    {
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
        $flag = trim($this->_post('flag'));

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
        $file = $_FILES['showing']['name'];
        if($file){
            //实例化上传类
            $upload = new fileupload();
            //设置属性(上传的位置， 大小， 类型， 名是是否要随机生成)
            $upload -> set("path", "./images/");
            $upload -> set("maxsize", 2000000);
            $upload -> set("allowtype", array("gif", "png", "jpg","jpeg"));
            $upload -> set("israndname", true);
            //使用对象中的upload方法， 就可以上传文件， 方法需要传一个上传表单的名子 pic, 如果成功返回true, 失败返回false
            if(!$upload -> upload("showimg")) {
                //失败返回错误
                echo "<script>alert('视频添加失败！');history.go(-1);</script>";
                $this -> error('视频添加失败！');
                //获取上传失败以后的错误提示
//            var_dump($upload->getErrorMsg());
            }
            $showimg = '../../images/' . $upload->getFileName();
        }



        //准备SQL数据数组
        $create_datetime = date('Y-m-d H:i:s',time());
        $videosavename = mt_rand(1111,11111);

        $msgjihe = mt_rand(1111,11111);
        $data = array('biaoti' => $biaoti, 'biaotichild' => $biaotichild, 'classify1' => $classify1,
            'classify2' => $classify2, 'classify3' => $classify3, 'classify4' => $classify4,
            'jieshao' => $jieshao, 'maketime' => $maketime, 'tishishuoming' => $tishishuoming,
            'huafeimoney' => $huafeimoney, 'create_datetime' => $create_datetime, 'videosavename' => $videosavename,
            'showimg' => $showimg, 'msgjihe' => $msgjihe, 'flag' => $flag);
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

        //        动态下拉列表、
//start--------------------------------------------------------------
        //动态生成权限下拉选项
        $videoarr = array(
            '1' => '1-启用',
            '2' => '2-禁用',
        );
        $flag = $list['flag'];
        $videoflag_show = '';
        foreach($videoarr as $keyr => $valr) {
            $videoflag_show .= '<option value="'.$keyr.'" ';
            if($keyr==$flag) {
                $videoflag_show .= ' selected="selected"';
            }
            $videoflag_show .= '>'.$valr.'</option>';

        }
        $this -> assign('videoflag_show',$videoflag_show);
        //end--------------------------------------------------------------

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
            'showimg' => $showimg, 'msgjihe' => $msgjihe, 'fenshu' => $fenshu, 'flag' => $flag);

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
        $res_id = $Model -> table('sixty_video') -> field('id') -> where("id='".$id."'") -> find();

        //判断ID是否存在
        if(!$res_id)
        {
            //ID不存在
            echo "<script>alert('删除失败，此ID不存在！');history.go(-1);</script>";
            $this -> error('删除失败，此ID不存在！');
        }

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

}



/**
file: fileupload.class.php 文件上传类FileUpload
本类的实例对象用于处理上传文件，可以上传一个文件，也可同时处理多个文件上传
*/
class FileUpload {
    private $path = "./uploads";//上传文件保存的路径
    private $allowtype = array('jpg','gif','png'); //设置限制上传文件的类型
    private $maxsize = 1000000; //限制文件上传大小（字节）
    private $israndname = true; //设置是否随机重命名文件， false不随机
    
    private $originName; //源文件名
    private $tmpFileName; //临时文件名
    private $fileType; //文件类型(文件后缀)
    private $fileSize; //文件大小
    private $newFileName; //新文件名
    private $errorNum = 0; //错误号
    private $errorMess=""; //错误报告消息

    /**
     * 用于设置成员属性（$path, $allowtype,$maxsize, $israndname）
     * 可以通过连贯操作一次设置多个属性值
     *@param  string $key  成员属性名(不区分大小写)
     *@param  mixed  $val  为成员属性设置的值
     *@return  object 返回自己对象$this，可以用于连贯操作
     */
        function set($key, $val){
            $key = strtolower($key);
            if( array_key_exists( $key, get_class_vars(get_class($this) ) ) ){
        $this->setOption($key, $val);
            }
        return $this;
        }

    /**
     * 调用该方法上传文件
     * @param  string $fileFile  上传文件的表单名称
     * @return bool  如果上传成功返回数true
     */

    function upload($fileField) {
    $return = true;
    /* 检查文件路径是滞合法 */
    if( !$this->checkFilePath() ) {
        $this->errorMess = $this->getError();
        return false;
    }
    /* 将文件上传的信息取出赋给变量 */
    $name = $_FILES[$fileField]['name'];
    $tmp_name = $_FILES[$fileField]['tmp_name'];
    $size = $_FILES[$fileField]['size'];
    $error = $_FILES[$fileField]['error'];

    /* 如果是多个文件上传则$file["name"]会是一个数组 */
    if(is_Array($name)){
        $errors=array();
        /*多个文件上传则循环处理 ， 这个循环只有检查上传文件的作用，并没有真正上传 */
        for($i = 0; $i < count($name); $i++){
        /*设置文件信息 */
        if($this->setFiles($name[$i],$tmp_name[$i],$size[$i],$error[$i] )) {
            if(!$this->checkFileSize() || !$this->checkFileType()){
                $errors[] = $this->getError();
                $return=false;
            }
        }else{
            $errors[] = $this->getError();
        $return=false;
        }
        /* 如果有问题，则重新初使化属性 */
        if(!$return)
            $this->setFiles();
        }

        if($return){
            /* 存放所有上传后文件名的变量数组 */
            $fileNames = array();
            /* 如果上传的多个文件都是合法的，则通过销魂循环向服务器上传文件 */
            for($i = 0; $i < count($name); $i++){
                if($this->setFiles($name[$i], $tmp_name[$i], $size[$i], $error[$i] )) {
                    $this->setNewFileName();
                if(!$this->copyFile()){
                    $errors[] = $this->getError();
                    $return = false;
                }
                $fileNames[] = $this->newFileName;
            }
        }
        $this->newFileName = $fileNames;
        }
        $this->errorMess = $errors;
        return $return;
        /*上传单个文件处理方法*/
        } else {
        /* 设置文件信息 */
        if($this->setFiles($name,$tmp_name,$size,$error)) {
            /* 上传之前先检查一下大小和类型 */
            if($this->checkFileSize() && $this->checkFileType()){
                /* 为上传文件设置新文件名 */
                $this->setNewFileName();
                /* 上传文件  返回0为成功， 小于0都为错误 */
                if($this->copyFile()){
                    return true;
                }else{
                    $return=false;
                }
            }else{
                $return=false;
            }
        } else {
        $return=false;
        }
        //如果$return为false, 则出错，将错误信息保存在属性errorMess中
    if(!$return)
    $this->errorMess=$this->getError();

    return $return;
    }
    }

    /**
     * 获取上传后的文件名称
     * @param  void 没有参数
     * @return string 上传后，新文件的名称， 如果是多文件上传返回数组
     */
    public function getFileName(){
    return $this->newFileName;
    }

    /**
     * 上传失败后，调用该方法则返回，上传出错信息
     * @param  void 没有参数
     * @return string  返回上传文件出错的信息报告，如果是多文件上传返回数组
     */
    public function getErrorMsg(){
    return $this->errorMess;
    }

    /* 设置上传出错信息 */
    private function getError() {
    $str = "上传文件<font color='red'>{$this->originName}</font>时出错 : ";
    switch ($this->errorNum) {
    case 4: $str .= "没有文件被上传"; break;
    case 3: $str .= "文件只有部分被上传"; break;
    case 2: $str .= "上传文件的大小超过了HTML表单中MAX_FILE_SIZE选项指定的值"; break;
    case 1: $str .= "上传的文件超过了php.ini中upload_max_filesize选项限制的值"; break;
    case -1: $str .= "未允许类型"; break;
    case -2: $str .= "文件过大,上传的文件不能超过{$this->maxsize}个字节"; break;
    case -3: $str .= "上传失败"; break;
    case -4: $str .= "建立存放上传文件目录失败，请重新指定上传目录"; break;
    case -5: $str .= "必须指定上传文件的路径"; break;
    default: $str .= "未知错误";
    }
    return $str.'<br>';
    }

    /* 设置和$_FILES有关的内容 */
    private function setFiles($name="", $tmp_name="", $size=0, $error=0) {
    $this->setOption('errorNum', $error);
    if($error)
    return false;
    $this->setOption('originName', $name);
    $this->setOption('tmpFileName',$tmp_name);
    $aryStr = explode(".", $name);
    $this->setOption('fileType', strtolower($aryStr[count($aryStr)-1]));
    $this->setOption('fileSize', $size);
    return true;
    }

    /* 为单个成员属性设置值 */
    private function setOption($key, $val) {
    $this->$key = $val;
    }

    /* 设置上传后的文件名称 */
    private function setNewFileName() {
    if ($this->israndname) {
    $this->setOption('newFileName', $this->proRandName());
    } else{
    $this->setOption('newFileName', $this->originName);
    }
    }

    /* 检查上传的文件是否是合法的类型 */
    private function checkFileType() {
    if (in_array(strtolower($this->fileType), $this->allowtype)) {
    return true;
    }else {
    $this->setOption('errorNum', -1);
    return false;
    }
    }

    /* 检查上传的文件是否是允许的大小 */
    private function checkFileSize() {
    if ($this->fileSize > $this->maxsize) {
    $this->setOption('errorNum', -2);
    return false;
    }else{
    return true;
    }
    }

    /* 检查是否有存放上传文件的目录 */
    private function checkFilePath() {
    if(empty($this->path)){
    $this->setOption('errorNum', -5);
    return false;
    }
    if (!file_exists($this->path) || !is_writable($this->path)) {
    if (!@mkdir($this->path, 0755)) {
    $this->setOption('errorNum', -4);
    return false;
    }
    }
    return true;
    }

    /* 设置随机文件名 */
    private function proRandName() {
    $fileName = date('YmdHis')."_".rand(100,999);
    return $fileName.'.'.$this->fileType;
    }

    /* 复制上传文件到指定的位置 */
    private function copyFile() {
    if(!$this->errorNum) {
    $path = rtrim($this->path, '/').'/';
    $path .= $this->newFileName;
    if (@move_uploaded_file($this->tmpFileName, $path)) {
    return true;
    }else{
    $this->setOption('errorNum', -3);
    return false;
    }
    } else {
        return false;
            }
    }
}
<?php
/**
 *小贴士视频控制类
 */

class TipsVideoAction extends Action {
    //定义各模块锁定级别
    private $lock_index         = '97';
    private $lock_delvideo_do   = '97';
    private $lock_addvideo   = '97';
    private $lock_addvideo_do   = '97';
    private $lock_editvideo   = '97';
    private $lock_editvideo_do  = '97';



    public function index() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //实例化方法
        $Model = new Model();

        /*
         * 接收查询数据
         */
        $find_id = trim($this->_get('find_id'));//id
        $find_biaoti = trim($this->_get('find_biaoti'));//标题
        $find_flag = trim($this->_get('find_flag'));//开启关闭
        $find_class = trim($this->_get('find_class'));//分类


        /*
         * 生成并返回查询信息
         */
        //查询分类表，取出所有分类信息
        $res_class = $Model -> table('sixty_tieshi_class') -> field('id, name') -> select();

        //准备接收数组
        $tips_arr = array();

        //遍历分类表查询结果
        foreach($res_class as $key_tips => $val_tips) {
            //把数据存入新数组
            $tips_arr[$val_tips['id']] = $val_tips['name'];
        }

        //补充全选键值对
        $tips_arr = array('0' => '全选') + $tips_arr;

        //生成下拉选项
        $class_arr = $this -> downlist($tips_arr, $find_class);


        //准备状态数组
        $flag_arr = array(
            '3' => '全选',
            '1' => '开启',
            '0' => '关闭',
        );


        //生成下拉菜单
        $flag_arr = $this -> downlist($flag_arr, $find_flag==''?3:$find_flag);


        //返回查询条件
        $find_where = array(
            'find_id' => $find_id,
            'find_biaoti' => $find_biaoti,
            'find_flag' => $flag_arr,
            'find_class' => $class_arr,
        );

        //输出到页面
        $this->assign('find_where', $find_where);


        /*
         * 拼接查询条件
         */
        $where = '';
        if($find_id != '') {
            $where .= "id = '" . $find_id . "' and ";
        }

        if($find_biaoti != '') {
            $where .= "biaoti like '" . $find_biaoti . "%' and ";
        }

        if($find_flag != '' && $find_flag !== '3') {
            $where .= "flag = '" . $find_flag . "' and ";
        }

        if($find_class != '' && $find_class !== '0') {
            if($tips_arr[$find_class] != '') {
                $where .= "class = '" . $tips_arr[$find_class] . "' and ";
            }
        }

        //去掉后四位
        if($where != '') {
            $where = substr($where, 0, -5);
        }

//        var_dump($where);exit;



        /*
         * 查询贴士视频表数据
         * */
        //分页
        import('ORG.Page');// 导入分页类
        $count = $Model->table('sixty_tieshi_video')
            ->where($condition)
            ->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('page', $show);// 赋值分页输出

//var_dump($where);die;
        //执行查询
        $result = $Model -> table('sixty_tieshi_video')
            -> field('id, biaoti, abstract, videosavename, showimg, create_datetime, remark, flag, class')
            -> where($where) -> order('id desc')-> limit($Page->firstRow . ',' . $Page->listRows) ->select();


        //判断是否有数据取出
        $num = count($result);
        if ($num <= 0) {
            $this->assign('list', $result);
            $this->display();
            exit;
        }

        //遍历结果集
        foreach ($result as $key_res => $val_res) {
            //获取七牛云图片
            $showimg = $val_res['showimg'];
            $imgwidth = '100';
            $imgheight = '100';
            $addressimg = hy_qiniuimgurl('sixty-videoimage',$showimg,$imgwidth,$imgheight);
            $result[$key_res]['showimg'] = "<img src='" . $addressimg . "' />";

            //获取七牛云视频
            $video_url = hy_qiniubucketurl('sixty-video', $val_res['videosavename']);

            $result[$key_res]['video_url'] = "<a href='".$video_url."' target='_blank' class='yubuttons yuwhite'>预览视频</a>";

            //视频是否开启
            $flag = $val_res['flag'];
            if($flag == 1) {
                $result[$key_res]['flag'] = '<span style="background-color:#33FF66;padding:3px;">1-开启</span>';
            }else {
                $result[$key_res]['flag'] = '<span style="background-color:#FF82A5;padding:3px;">2-关闭</span>';
            }
        }


        //输出到模板
        $this -> assign('data', $result);
        $this -> display();
    }


    /**
     * @param $lock_key
     */
    public function addvideo() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addvideo);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        $flag_arr = array(
            '0' => '关闭',
            '1' => '开启',
        );

        //实例化方法
        $Model = new Model();

        //查询小贴士分类表
        $res_tips = $Model -> table('sixty_tieshi_class') -> field('id, name') -> select();

        $tips_arr = array();
        foreach($res_tips as $key_tips => $val_tips) {
            $tips_arr[$val_tips['id']] = $val_tips['name'];
        }

        $tips_arr = $this->downlist($tips_arr);
        $flag_arr = $this->downlist($flag_arr,'0');

        $this->assign('flag', $flag_arr);
        $this->assign('tips_class', $tips_arr);
        $this->display();
    }


    /**
     * @param $lock_key
     */
    public function addvideo_do() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addvideo_do);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $biaoti = trim($this->_post('biaoti'));
        $abstract = trim($this->_post('abstract'));
        $videosavename = trim($this->_post('videosavename'));
        $remark = trim($this->_post('remark'));
        $flag = trim($this->_post('flag'));
        $tips_class = trim($this->_post('tips_class'));


        //判断上传数据是否为空
        if($biaoti == '') {
            //标题不能为空
            echo "<script>alert('标题不能为空！');history.go(-1);</script>";
            $this -> error('标题不能为空！');
        }

        if($abstract == '') {
            //介绍不能为空
            echo "<script>alert('介绍不能为空！');history.go(-1);</script>";
            $this -> error('介绍不能为空！');
        }


        //实例化方法
        $Model = new Model();


        //判断标题名是否存在
        $res_biaoti = $Model -> table('sixty_tieshi_video') -> field('id') -> where("biaoti='" . $biaoti . "'") ->find();
        if($res_biaoti != ''){
            echo "<script>alert('此标题名已存在，请使用其他名称');history.go(-1);</script>";
            $this -> error('此标题名已存在，请使用其他名称');
        }


        //根据ID查询贴士分类
        $res_class = $Model -> table('sixty_tieshi_class') -> field('name') -> where("id='" . $tips_class . "'") ->find();

        //判断分类名是否存在
        if($res_class == ''){
            echo "<script>alert('非法进入');history.go(-1);</script>";
            $this -> error('非法进入');
        }

        //判断图片是否上传
        if($_FILES['showimg']['name'] != '')
        {
            import('ORG.UploadFile');
            $upload = new UploadFile();// 实例化上传类
            $upload->maxSize  = 2097152 ;// 设置附件上传大小
            $upload->saveRule  = date('YmdHis',time()) . mt_rand();// 设置附件上传文件名
            $upload->allowExts  = array('jpg', 'png', 'jpeg');// 设置附件上传类型
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
                    @unlink($oldfile); //删除文件
                    //非正常图片
                    echo'不正常';die;
                }else {
                    //上传到七牛云之前先进行图片格式转换，统一使用jpg格式,图片格式转换
                    $r = hy_resave2jpg($oldfile);
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
                        echo'失败';die;
                        //上传失败
                    }else {
                        @unlink($cz_filepathname); //删除文件
                        //上传成功

                    }
                }
            }
        }else{
            echo "<script>alert('视频展示图不能为空！');history.go(-1);</script>";
            $this -> error('视频展示图不能为空！');
        }


        //准备SQL数据数组
        $create_datetime = date('Y-m-d H:i:s',time());
        $data = array(
            'biaoti' => $biaoti,
            'create_datetime' => $create_datetime,
            'videosavename' => $videosavename,
            'showimg' => $showimg,
            'flag' => $flag,
            'remark' => $remark,
            'abstract' => $abstract,
            'class' => $tips_class,
        );


        //执行添加
        $result = $Model -> table('sixty_tieshi_video') -> add($data);


        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);


        //判断结果
        if($result)
        {
            //成功返回成功
            echo "<script>alert('贴士分类添加成功!');window.location.href='".__APP__.'/TipsVideo/index'. $echourl ."';</script>";
            $this -> success('视频添加成功!','__APP__/TipsVideo/index');
        }else{
            //失败返回错误
            echo "<script>alert('视频添加失败！');history.go(-1);</script>";
            $this -> error('视频添加失败！');
        }
    }


    public function editvideo() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editvideo);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //获取上传信息
        $id = trim($this->_post('edit_id'));
        $submit = trim($this->_post('edit_video'));

        //判断来源
        if($id == '') {
            //非法进入
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }

        if($submit == '') {
            //非法进入
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }


        //实例化方法
        $Model = new Model();


        //根据ID查询信息
        $result = $Model -> table('sixty_tieshi_video') -> field('id, biaoti, class, videosavename, remark, showimg, abstract, flag') -> where("id ='".$id."'") -> find();


        //判断查询结果
        if($result == '') {
            //非法进入
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }


        //获取七牛云图片
        $showimg = $result['showimg'];
        $imgwidth = '100';
        $imgheight = '100';
        $addressimg = hy_qiniuimgurl('sixty-videoimage',$showimg,$imgwidth,$imgheight);
        $result['showimg'] = "<img src='" . $addressimg . "' />";

        $flag_arr = array(
            '0' => '关闭',
            '1' => '开启',
        );

        //查询小贴士分类表
        $res_tips = $Model -> table('sixty_tieshi_class') -> field('id, name') -> select();

        $tips_arr = array();
        $lock = '';
        foreach($res_tips as $key_tips => $val_tips) {
            $tips_arr[$val_tips['id']] = $val_tips['name'];
            if($result['class'] == $val_tips['id']){
                $lock = $val_tips['name'];
            }
        }

        $tips_arr = $this->downlist($tips_arr,$lock,1);

        $flag_arr = $this->downlist($flag_arr,$result['flag']);

        $this->assign('flag', $flag_arr);
        $this->assign('class_arr', $tips_arr);
        $this->assign('data', $result);
        $this->display();

    }


    public function editvideo_do() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editvideo_do);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //获取上传信息
        $biaoti = trim($this->_post('biaoti'));
        $abstract = trim($this->_post('abstract'));
        $videosavename = trim($this->_post('videosavename'));
        $remark = trim($this->_post('remark'));
        $flag = trim($this->_post('flag'));
        $tips_class = trim($this->_post('tips_class'));
        $id = trim($this->_post('edit_id'));
        $submit = trim($this->_post('submit'));

        //判断来源
        if($id == '') {
            //非法进入
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }

        if($submit == '') {
            //非法进入
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }


        //实例化方法
        $Model = new Model();


        //根据ID查询信息
        $result = $Model -> table('sixty_tieshi_video') -> field('id, showimg')
            -> where("biaoti = '" . $biaoti . "'") -> find();


        if($result != '' &&$result['id'] != $id) {
            //此标题名已存在
            echo "<script>alert('此标题名已存在，请使用其他名称');history.go(-1);</script>";
            $this -> error('此标题名已存在，请使用其他名称');
        }


        //根据ID查询贴士分类
        $res_class = $Model -> table('sixty_tieshi_class') -> field('name') -> where("id='" . $tips_class . "'") ->find();

        //判断分类名是否存在
        if($res_class == ''){
            echo "<script>alert('非法进入');history.go(-1);</script>";
            $this -> error('非法进入');
        }


        $show_old = $result['showimg'];


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
                    $data = array(
                        'biaoti' => $biaoti,
                        'videosavename' => $videosavename,
                        'showimg' => $showimg,
                        'flag' => $flag,
                        'abstract' => $abstract,
                        'remark' => $remark,
                        'class' => $tips_class,

                    );
                }
            }
        }else{
            //准备更新数组
            $data = array(
                'biaoti' => $biaoti,
                'videosavename' => $videosavename,
                'flag' => $flag,
                'abstract' => $abstract,
                'remark' => $remark,
                'class' => $tips_class,
            );
        }

        //执行更新
        $result = $Model -> table('sixty_tieshi_video') -> where("id='".$id."'") -> save($data);

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //判断结果
        if($result)
        {
            //成功返回成功
            echo "<script>alert('数据修改成功!');window.location.href='".__APP__.'/TipsVideo/index'.$echourl."';</script>";
            $this -> success('数据修改成功!','__APP__'.$echourl);
        }else{
            //失败返回错误
            echo "<script>alert('数据修改失败！');history.go(-1);</script>";
            $this -> error('数据修改失败！');
        }


    }


    public function delvideo_do() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_delvideo_do);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $id = trim($this->_post('del_id'));
        $submitdel = trim($this->_post('submitdel'));

        //判断ID是否存在
        if($id == '')
        {
            //ID不存在
            echo "<script>alert('非法进入！');history.go(-1);</script>";
            $this -> error('非法进入！');
        }


        $Model = new Model();
        $res = $Model -> table('sixty_tieshi_video') -> field('id, showimg, flag') -> where("id='".$id."'") -> find();

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

        //执行删除
        $result = $Model -> table('sixty_tieshi_video') -> where("id = '".$id."'") -> delete();

        //写入日志
        $templogs = $Model -> getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        if($result)
        {
            //成功返回成功
            echo "<script>alert('数据删除成功!');window.location.href='".__APP__.'/Tips/index'.$echourl."';</script>";
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


    public function downlist($arr, $lock='', $flag=''){

        //        动态下拉列表、
        //start--------------------------------------------------------------
        //动态生成权限下拉选项
//        var_dump($arr);die;
        $res_arr = '';
        if($arr != '') {
            foreach ($arr as $keyr => $valr) {
                $res_arr .= '<option value="' . $keyr . '" ';
                if($flag) {
                    $con = $valr;
                } else {
                    $con = $keyr;
                }
                if($con == $lock) {
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
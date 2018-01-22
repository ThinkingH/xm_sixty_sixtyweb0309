<?php
/*
 * 首页图标
 */
class IconAction extends Action{
    //定义各模块锁定级别
    private $lock_index = '97';
    private $lock_addicon = '97';
    private $lock_addicon_do = '97';
    private $lock_editicon_do = '97';
    private $lock_editicon = '97';
    private $lock_delicon_do = '97';

    public function index(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $id = trim($this->_get('find_id'));
        $name = trim($this->_get('find_name'));
        $flag = trim($this->_get('find_flag'));


        //拼接搜索条件
        $where = '';
        if($id != ''){
            $where.= "id = '".$id."' and ";
        }

        if($flag != 4 && $flag != ''){
            $where.= "flag = '".$flag."' and ";
        }

        if($name != ''){
            $where.= "word like '".$name."%' and ";
        }

        //去掉后四位
        if($where != '') {
            $where = substr($where, 0, -5);
        }

        //动态生成权限下拉选项
        $flagarr = array(
            '4' => '4-全选',
            '1' => '1-已开启',
            '2' => '2-已关闭',
        );

        $flagarr = $this->downlist($flagarr, $flag==4?$flag=4:$flag);

        //返回查询数据数组
        $where_find = array(
            'find_id_back' =>  $id,
            'find_name_back' =>  $name,
            'find_flag_back' =>  $flagarr,
        );

        //查询图标表数据
        $Model = new Model();

        $list_icon = $Model -> table('sixty_icon')
            -> field('id, showimg, ordernum, word, keyword, create_datetime, remark, flag')
            -> where($where) -> order('id desc') -> select();

        $imgwidth = '100';
        $imgheight = '100';
        foreach($list_icon as $k_i => $v_i){
            //获取七牛云图片
            $showimg = $v_i['showimg'];
            $addressimg = hy_qiniuimgurl('sixty-jihemsg',$showimg,$imgwidth,$imgheight);
            $list_icon[$k_i]['showimg'] ="<img src='" . $addressimg . "' />";

            //视频是否开启
            $flag = $v_i['flag'];
            if($flag == 1) {
                $list_icon[$k_i]['flag'] = '<span style="background-color:#33FF66;padding:3px;">1-开启</span>';
            }else {
                $list_icon[$k_i]['flag'] = '<span style="background-color:#FF82A5;padding:3px;">2-关闭</span>';
            }
        }

        $this->assign('list', $list_icon);
        $this->assign('where_back', $where_find);
        $this->display();
    }

    public function addicon(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addicon);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        $Model = new Model();
        //获取分类表数据
        $list_class = $Model -> table('sixty_classifymsg') -> field('name, id, level') -> where('flag = 1') -> order('level asc') -> select();


        $arr_class = array();
        foreach($list_class as $k_c => $v_c){
            $arr_class[$v_c['id']] =  $v_c['name'] . ' 等级-' . $v_c['level'];
        }

        $arr_class = $this->downlist($arr_class);

        $arr_flag = array(
            '1' => '开启',
            '2' => '关闭',
        );
        $arr_flag = $this->downlist($arr_flag,'2');
//        header("Content-type:text/html;charset=utf-8");
//        var_dump($arr_class);die;

        $this->assign('class', $arr_class);
        $this->assign('flag', $arr_flag);
        $this->display();
    }

    public function addicon_do(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addicon_do);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        $word = trim($this->_post('word'));
        $flag = trim($this->_post('flag'));
        $class = trim($this->_post('class'));
        $order = trim($this->_post('order'));
        $remark = trim($this->_post('remark'));

        if($word == ''){
            echo "<script>alert('展示文字不能为空!');history.go(-1);</script>";
            $this -> error('展示文字不能为空!');
        }

        if($order == ''){
            echo "<script>alert('顺序值不能为空!');history.go(-1);</script>";
            $this -> error('顺序值不能为空!');
        }

        if($flag == ''){
            echo "<script>alert('非法进入!');history.go(-1);</script>";
            $this -> error('非法进入!');
        }

        if($class == ''){
            echo "<script>alert('非法进入!');history.go(-1);</script>";
            $this -> error('非法进入!');
        }



        //判断文件是否上传
        $file = $_FILES['showimg']['name'];
        if($file != ''){
            import('ORG.UploadFile');
            $upload = new UploadFile();// 实例化上传类
            $upload->maxSize  = 2097152 ;// 设置附件上传大小
            $upload->saveRule  = date('YmdHis',time()) . mt_rand();// 设置附件上传文件名
            $upload->allowExts  = array('jpg','gif','png');// 设置附件上传类型
            $upload->savePath =  BASEDIR.'Public/Images/sixty-jihemsg/';// 设置附件上传目录
            if(!$upload->upload()) {// 上传错误提示错误信息
                echo "<script>alert('图片上传失败!');history.go(-1);</script>";
                $this -> error('图片上传失败!');
            }else{// 上传成功 获取上传文件信息
                $info =  $upload->getUploadFileInfo();
                $showimg = $info['0']['savename'];

                //上传七牛云
                //上传图片存储绝对路径
                $cz_filepathname  = BASEDIR.'Public/Images/sixty-jihemsg/'.$showimg;

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
                        $showimg = $upload->saveRule . '.jpg';
                    }
                    //上传到七牛云
                    //参数，bucket，文件绝对路径名称，存储名称，是否覆盖同名文件
                    $r = upload_qiniu('sixty-jihemsg',$cz_filepathname,$showimg,'yes');

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
            echo "<script>alert('图片不能为空！');history.go(-1);</script>";
            $this -> error('图片不能为空！');
        }

        $datetime = date('Y-m-d H:i:s',time());
        $data = array(
            'word' => $word,
            'ordernum' => $order,
            'flag' => $flag,
            'remark' => $remark,
            'create_datetime' => $datetime,
            'showimg' => $showimg,
            'keyword' => $class,
        );

        $Model = new Model();
        $res = $Model -> table('sixty_icon') -> add($data);

        if($res){
            echo "<script>alert('添加成功!');window.location.href='".__APP__."/Icon/index".$echourl."';</script>";
            $this -> success('添加成功!','__APP__/Icon/index'.$echourl);
        }else{
            echo "<script>alert('添加失败!');history.go(-1);</script>";
            $this -> error('添加失败!');
        }
    }


    public function editicon(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editicon);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //获取数据
        $id = trim($this->_post('edit_id'));


        //判断数据来源
        if($id == ''){
            echo "<script>alert('非法进入!');history.go(-1);</script>";
            $this -> error('非法进入!');
        }


        $Model = new Model();

        //根据ID查询图标表数据
        $res_icon = $Model -> table('sixty_icon')
            -> field('id, word, keyword, showimg, remark, create_datetime, ordernum, flag') -> where("id = $id") -> find();

        if($res_icon < 0){
            echo "<script>alert('非法进入!');history.go(-1);</script>";
            $this -> error('非法进入!');
        }

        $imgwidth = '100';
        $imgheight = '100';
        //获取七牛云图片
        $showimg = $res_icon['showimg'];
        $addressimg = hy_qiniuimgurl('sixty-jihemsg',$showimg,$imgwidth,$imgheight);
        $res_icon['showimg'] ="<img src='" . $addressimg . "' />";


        //获取分类表数据
        $list_class = $Model -> table('sixty_classifymsg') -> field('name, id, level') -> where('flag = 1') -> order('level asc') -> select();


        $arr_class = array();
        foreach($list_class as $k_c => $v_c){
            $arr_class[$v_c['id']] =  $v_c['name'] . ' 等级-' . $v_c['level'];
        }

        $arr_class = $this->downlist($arr_class, $res_icon['keyword']);

        $arr_flag = array(
            '1' => '开启',
            '2' => '关闭',
        );
        $arr_flag = $this->downlist($arr_flag,$res_icon['flag']);
//        header("Content-type:text/html;charset=utf-8");
//        var_dump($arr_class);die;

        $this->assign('class', $arr_class);
        $this->assign('flag', $arr_flag);
        $this->assign('list', $res_icon);
        $this->display();
    }


    public function editicon_do(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editicon_do);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //获取数据
        $id = trim($this->_post('edit_id'));
        $word = trim($this->_post('word'));
        $flag = trim($this->_post('flag'));
        $class = trim($this->_post('class'));
        $order = trim($this->_post('order'));
        $remark = trim($this->_post('remark'));

        //判断数据是否为空
        if($id == ''){
            echo "<script>alert('非法进入！');history.back()</script>";
//            echo "<script>alert('非法进入!');history.go(-1);</script>";
            $this -> error('非法进入!');
        }


        $Model = new Model();
        //根据ID查询图标表
        $res_icon = $Model -> table('sixty_icon') -> field('id, showimg') -> where("id = '".$id."'") -> find();

        //判断此ID是否存在
        if(count($res_icon) <= 0){
            echo "<script>alert('非法进入！');history.back()</script>";
            $this -> error('非法进入!');
        }


//        var_dump($res_icon);die;

        //判断文件是否上传
        $file = $_FILES['showimg']['name'];
        if($file != ''){
            import('ORG.UploadFile');
            $upload = new UploadFile();// 实例化上传类
            $upload->maxSize  = 2097152 ;// 设置附件上传大小
            $upload->saveRule  = date('YmdHis',time()) . mt_rand();// 设置附件上传大小
            $upload->allowExts  = array('jpg','gif','png');// 设置附件上传类型
            $upload->savePath =  BASEDIR.'Public/Images/sixty-jihemsg/';// 设置附件上传目录
            if(!$upload->upload()) {// 上传错误提示错误信息
                echo "<script>alert('图片上传失败!');history.go(-1);</script>";
                $this -> error('图片上传失败!');
            }else{// 上传成功 获取上传文件信息
                $info =  $upload->getUploadFileInfo();
                $showimg = $info['0']['savename'];

                //上传七牛云
                //上传图片存储绝对路径
                $cz_filepathname  = BASEDIR.'Public/Images/sixty-jihemsg/'.$showimg;

                if(false===func_isImage($cz_filepathname)) {
                    //解析失败，非正常图片---后台图片上传时本函数可不使用
                    @unlink($cz_filepathname); //删除文件
                    //非正常图片
                }else {
                    //上传到七牛云之前先进行图片格式转换，统一使用jpg格式,图片格式转换
                    $r = hy_resave2jpg($cz_filepathname);
//                    var_dump($cz_filepathname);die;
                    if($r!==false) {
                        //图片格式转换成功，赋值新名称
                        $cz_filepathname = $r;
                        $cz_filepathname = str_replace('\\','/',$cz_filepathname);
                        $showimg = $upload->saveRule.'.jpg';
                    }
                    //上传到七牛云
                    //参数，bucket，文件绝对路径名称，存储名称，是否覆盖同名文件
                    $r = upload_qiniu('sixty-jihemsg',$cz_filepathname,$showimg,'yes');

                    if(false===$r) {
                        @unlink($cz_filepathname); //删除文件
                        //上传失败
                    }else {
                        @unlink($cz_filepathname); //删除文件
                        //上传成功
                    }

                    //删除七牛云旧图片
                    delete_qiniu('sixty-jihemsg', $res_icon['showimg']);
                    //准备更新数组
                    //准备插入数组
                    $data = array(
                        'word' => $word,
                        'keyword' => $class,
                        'remark' => $remark,
                        'ordernum' => $order,
                        'flag' => $flag,
                        'showimg' => $showimg,
                    );

                }
            }
        }else{
            //准备更新数组
            $data = array(
                'word' => $word,
                'keyword' => $class,
                'remark' => $remark,
                'ordernum' => $order,
                'flag' => $flag,
            );
        }

        $res_edit = $Model -> table('sixty_icon') -> where("id = '".$id."'") -> save($data);

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //判断结果
        if($res_edit){
            echo "<script>alert('修改成功!');window.location.href='".__APP__.'/Icon/index'. $echourl ."';</script>";
            $this -> success('修改成功!','__APP__'.$echourl);
        }else{
            echo "<script>alert('修改失败');history.go(-1);</script>";
            $this -> error('修改失败!');
        }


    }


    public function delicon_do(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_delicon_do);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收数据
        $id = trim($this->_post('del_id'));


        //判断数据是否为空
        if($id == ''){
            echo "<script>alert('非法进入！');history.back();</script>";
            $this->error('非法进入');
        }


        $Model = new Model();

        //根据id查询数据
        $res_icon = $Model -> table('sixty_icon') -> field('id, showimg') -> where("id = '".$id."'") -> find();

        if(count($res_icon) <= 0){
            echo "<script>alert('非法进入！');history.back();</script>";
            $this->error('非法进入');
        }

        //删除七牛云旧图片
        delete_qiniu('sixty-jihemsg', $res_icon['showimg']);

        $res_del = $Model -> table('sixty_icon') -> where("id = '".$id."'") -> delete();

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //判断结果
        if($res_del){
            echo "<script>alert('修改成功!');window.location.href='".__APP__.'/Icon/index'. $echourl ."';</script>";
            $this -> success('修改成功!','__APP__'.$echourl);
        }else{
            echo "<script>alert('修改失败');history.go(-1);</script>";
            $this -> error('修改失败!');
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
}
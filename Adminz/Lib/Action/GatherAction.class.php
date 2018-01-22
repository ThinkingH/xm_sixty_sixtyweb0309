<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/2
 * Time: 11:46
 * 合集表
 */

class GatherAction extends Action{
    //定义各模块锁定级别
    private $lock_index = '97';
    private $lock_addgat = '97';
    private $lock_addgat_do = '97';
    private $lock_editgat_do = '97';
    private $lock_editgat = '97';
    private $lock_delgat_do = '97';

    public function index(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //获取查询信息
        $gat_id = trim($this->_get('find_id'));
        $gat_name = trim($this->_get('find_name'));
        $gat_flag = trim($this->_get('find_flag'));
        $gat_find_sta_date = trim($this->_get('find_sta_date'));
        $gat_find_end_date = trim($this->_get('find_end_date'));


        //判断结束日期是否有传值
        $end_minute = ' 59:59:59';
        if($gat_find_end_date == ''){
            $gat_find_end_date = date('Y-m-d',time());
            $where_end_date = $gat_find_end_date . $end_minute;
        }else{
            $where_end_date = $gat_find_end_date . $end_minute;
        }

        //准备查询条件
        $where = "create_datetime <= '" . $where_end_date . "'";

        //判断起始日期是否有传值
        $sta_minute = ' 00:00:00';
        if($gat_find_sta_date != '') {
            $where_sta_date = $gat_find_sta_date . $sta_minute;
            $where .= " and create_datetime >= '" . $where_sta_date . "'";
        }
        //判断查询条件
        //判断ID是否不为空
        if($gat_id != ''){
            $where .= " and id = '". $gat_id . "'";
        }
        //判断名称是否不为空
        if($gat_name != ''){
            $where .= "and name like '%" . $gat_name . "%'";
        }
        //判断状态是否不为空
        if($gat_flag != ''){
            $where .= "and flag = '" . $gat_flag . "'";
        }

        //下拉菜单
        $flagarr = array(
            ''  => '0-全选',
            '1' => '1-已开启',
            '9' => '9-已关闭',
        );

        $find_flag = $this ->downlist($flagarr, $gat_flag);
        //返回查询条件
        $find_where = array(
            'find_id' => $gat_id,
            'find_name' => $gat_name,
            'find_sta_date' => $gat_find_sta_date,
            'find_end_date' => $gat_find_end_date,
            'find_flag' => $find_flag,
        );
        $this->assign('find_where', $find_where);


        //准备查询数组
        $Model = new Model();

        //分页
        import('ORG.Page');// 导入分页类
        $count = $Model->table('sixty_jihemsg')
            ->where($where)
            ->count();// 查询满足要求的总记录数
        $Page = new Page($count, 30);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('page', $show);// 赋值分页输出


        //查询集合数据表
        $list = $Model -> table('sixty_jihemsg') -> field('id, detailimg, name, showimg, content, flag, orderby, remark, create_datetime')
            -> where($where) -> order('orderby desc,create_datetime desc') -> limit($Page->firstRow . ',' . $Page->listRows)
            -> select();

        foreach($list as $key_li => $val_li) {
            //获取七牛云图片
            $showimg = $list[$key_li]['showimg'];
            $detailimg = $list[$key_li]['detailimg'];

            $imgwidth = '100';
            $imgheight = '100';
            $addressimg = hy_qiniuimgurl('sixty-jihemsg',$showimg,$imgwidth,$imgheight);
            $list[$key_li]['showimg'] = "<img src='" . $addressimg . "' />";

            $addressimg = hy_qiniuimgurl('sixty-jihemsg',$detailimg,$imgwidth,$imgheight);
            $list[$key_li]['detailimg'] = "<img src='" . $addressimg . "' />";

            if($list[$key_li]['flag'] == 1) {
                $list[$key_li]['flag'] = "<span style=\"background-color:#33FF66;padding:3px;\">1-开启</span>";
            }else if($list[$key_li]['flag'] == 9) {
                $list[$key_li]['flag'] = '<span style="background-color:#FF82A5;padding:3px;">2-关闭</span>';
            }
        }
        $this->assign('list',$list);
        $this->display();
    }

    public function addgat(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addgat);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //下拉菜单
        $flagarr = array(
            '1' => '1-开启',
            '9' => '9-关闭',
        );

        $rootflag_show = $this -> downlist($flagarr,'1');
        $this -> assign('rootflag_show',$rootflag_show);


        $orderby = 100;
        $this -> assign('orderby',$orderby);
        //输出模板
        $this->display();
    }

    public function addgat_do(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addgat_do);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $name = trim($this->_post('name'));
        $content = trim($this->_post('content'));
        $remark = trim($this->_post('remark'));
        $submit = trim($this->_post('submit'));
        $orderby = trim($this->_post('orderby'));
        $flag = trim($this->_post('flag'));

        //判断上传数据是否为空
        if($submit == ''){
            echo "<script>alert('非法进入此页面!');history.go(-1);</script>";
            $this -> error('非法进入此页面!');
        }
        if($name == ''){
            echo "<script>alert('合集名称不能为空');history.go(-1);</script>";
            $this -> error('合集名称不能为空!');
        }
        if($content == ''){
            echo "<script>alert('合集描述不能为空!');history.go(-1);</script>";
            $this -> error('合集描述不能为空!');
        }
        if($orderby == ''){
            echo "<script>alert('排序值不能为空!');history.go(-1);</script>";
            $this -> error('排序值不能为空!');
        }
        if($flag == ''){
            echo "<script>alert('非法进入此页面!');history.go(-1);</script>";
            $this -> error('非法进入此页面!');
        }

        //实例化方法
        $Model = new Model();


        //判断分类名是否存在
        $res_name = $Model -> table('sixty_jihemsg') -> field('id') -> where("name='" . $name . "'") ->find();
        if($res_name != ''){
            echo "<script>alert('此合集名已存在，请使用其他名称');history.go(-1);</script>";
            $this -> error('此合集名已存在，请使用其他名称');
        }

//var_dump($_FILES['detailimg']['name']);die;
        //判断图片是否上传
        if($_FILES['showimg']['name'] != '' && $_FILES['showimg2']['name'] != '')
        {
            import('ORG.UploadFile');
            $upload = new UploadFile();// 实例化上传类
            $upload->maxSize  = 2097152 ;// 设置附件上传大小
//            $upload->saveRule  = date('YmdHis',time()) . mt_rand();// 设置附件上传文件名
            $upload->allowExts  = array('jpg', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath =  BASEDIR.'Public/Images/sixty-jihemsg/';// 设置附件上传目录
            if(!$upload->upload()) {// 上传错误提示错误信息

                echo "<script>alert('图片上传失败!');history.go(-1);</script>";
                $this -> error('图片上传失败!');
            }else{// 上传成功 获取上传文件信息
                $info =  $upload->getUploadFileInfo();
//                var_dump($info);exit;
                $showimgs = $info['0']['savename'];
                $showimgd = $info['1']['savename'];

                $showimg = date('YmdHis',time()) . mt_rand();
                $detailimg = date('YmdHis',time()) . mt_rand();
                //上传七牛云
                //上传图片存储绝对路径
                $cz_filepathname_s  = BASEDIR.'Public/Images/sixty-jihemsg/'.$showimgs;
                $cz_filepathname_d  = BASEDIR.'Public/Images/sixty-jihemsg/'.$showimgd;

                if(false===func_isImage($cz_filepathname_s) || false===func_isImage($cz_filepathname_d)) {
                    //解析失败，非正常图片---后台图片上传时本函数可不使用
                    @unlink($cz_filepathname_s); //删除文件
                    @unlink($cz_filepathname_d); //删除文件
                    //非正常图片
                    echo "<script>alert('图片上传失败!');history.go(-1);</script>";
                    $this -> error('图片上传失败!');
                }else {
                    //上传到七牛云之前先进行图片格式转换，统一使用jpg格式,图片格式转换
                    $r = hy_resave2jpg($cz_filepathname_s);
                    if($r!==false) {
                        //图片格式转换成功，赋值新名称
                        $cz_filepathname_s = $r;
                        $cz_filepathname_s = str_replace('\\','/',$cz_filepathname_s);
                    }
                    $r2 = hy_resave2jpg($cz_filepathname_d);
                    if($r2!==false) {
                        //图片格式转换成功，赋值新名称
                        $cz_filepathname_d = $r2;
                        $cz_filepathname_d = str_replace('\\','/',$cz_filepathname_d);
                    }
                    //上传到七牛云
                    //参数，bucket，文件绝对路径名称，存储名称，是否覆盖同名文件
                    $r = upload_qiniu('sixty-jihemsg',$cz_filepathname_s,$showimg,'yes');
                    $r2 = upload_qiniu('sixty-jihemsg',$cz_filepathname_d,$detailimg,'yes');

                    if(false===$r || false===$r2) {
                        @unlink($cz_filepathname_s); //删除文件
                        @unlink($cz_filepathname_d); //删除文件
                        echo'失败';die;
                        //上传失败
                    }else {
                        @unlink($cz_filepathname_s); //删除文件
                        @unlink($cz_filepathname_d); //删除文件
                        //上传成功

                    }
                }
            }
        }else{
            echo "<script>alert('合集展示图不能为空！');history.go(-1);</script>";
            $this -> error('合集展示图不能为空！');
        }

        //准备插入数组
        $create_timedate = date('Y-m-d H:i:s', time());
        $data = array(
            'name' => $name,
            'flag' => $flag,
            'orderby' => $orderby,
            'content' => $content,
            'remark' => $remark,
            'showimg' => $showimg,
            'detailimg' => $detailimg,
            'create_datetime' => $create_timedate,
        );

        //执行插入
        $res = $Model -> table('sixty_jihemsg') -> add($data);

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);
        //判断插入结果
        if($res){
            //返回成功
            @unlink(BASEDIR.'tmpimage/'.$showimg);
            echo "<script>alert('合集添加成功!');window.location.href='".__APP__.'/Gather/index'. $echourl ."';</script>";
            $this -> success('合集添加成功!','__APP__'.$echourl);

        }else{
            @unlink(BASEDIR.'tmpimage/'.$showimg);
            echo "<script>alert('合集添加失败!');history.go(-1);</script>";
            $this -> error('合集添加失败!');
        }
    }

    public function editgat(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editgat);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        $id = trim($this->_post('edit_id'));

        if($id == ''){
            echo "<script>alert('非法进入此页面!');history.go(-1);</script>";
            $this -> error('非法进入此页面!');
        }
        $Model = new Model();
        $list = $Model -> table('sixty_jihemsg') -> field('id, orderby, detailimg, flag, name, showimg, content, remark')
            -> where("id='".$id."'") -> find();

        //获取七牛云图片
        $showimg = $list['showimg'];
        $detailimg = $list['detailimg'];
        $imgwidth = '100';
        $imgheight = '100';
        $addressimg = hy_qiniuimgurl('sixty-jihemsg',$showimg,$imgwidth,$imgheight);
        $addressdetailimg = hy_qiniuimgurl('sixty-jihemsg',$detailimg,$imgwidth,$imgheight);
        $list['showimg'] = "<img src='" . $addressimg . "' />";
        $list['detailimg'] = "<img src='" . $addressdetailimg . "' />";

        //下拉菜单
        $flagarr = array(
            '1' => '1-开启',
            '9' => '9-关闭',
        );

        $rootflag_show = $this -> downlist($flagarr,$list['flag']);
        $this -> assign('rootflag_show',$rootflag_show);

        $this->assign('list', $list);
        $this->display();
    }

    public function editgat_do(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_editgat_do);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $id = trim($this->_post('edit_id'));
        $name = trim($this->_post('edit_name'));
        $content = trim($this->_post('edit_content'));
        $remark = trim($this->_post('edit_remark'));
        $submit = trim($this->_post('submit'));
        $flag = trim($this->_post('flag'));
        $orderby = trim($this->_post('orderby'));

        //判断上传数据是否为空
        if($submit == ''){
            echo "<script>alert('非法进入此页面!');history.go(-1);</script>";
            $this -> error('非法进入此页面!');
        }
        if($id == ''){
            echo "<script>alert('非法进入此页面!');history.go(-1);</script>";
            $this -> error('非法进入此页面!');
        }
        if($name == ''){
            echo "<script>alert('合集名称不能为空');history.go(-1);</script>";
            $this -> error('合集名称不能为空!');
        }
        if($content == ''){
            echo "<script>alert('合集描述不能为空!');history.go(-1);</script>";
            $this -> error('合集描述不能为空!');
        }
        if($flag == ''){
            echo "<script>alert('非法进入此页面!');history.go(-1);</script>";
            $this -> error('非法进入此页面!');
        }
        if($orderby == ''){
            echo "<script>alert('排序值不能为空!');history.go(-1);</script>";
            $this -> error('排序值不能为空!');
        }

        //根据ID 查找数据
        $Model = new Model();
        $res_old = $Model -> table('sixty_jihemsg') -> field('id,showimg') -> where("id = '" . $id . "'") -> find();
        if($res_old == '')
        {
            echo "<script>alert('非法进入此页面!');history.go(-1);</script>";
            $this -> error('非法进入此页面!');
        }


        //判断分类名是否存在
        $res_name = $Model -> table('sixty_jihemsg') -> field('id')
            -> where("name='" . $name . "' and id <> '" . $id . "'") ->find();
        if($res_name != ''){
            echo "<script>alert('此合集名已存在，请使用其他名称');history.go(-1);</script>";
            $this -> error('此合集名已存在，请使用其他名称');
        }

        //准备插入数组
        $data = array(
            'name' => $name,
            'content' => $content,
            'remark' => $remark,
            'flag' => $flag,
            'orderby' => $orderby,
        );


//        //判断图片是否上传
        if($_FILES['edit_showimg']['name'] != '' && $_FILES['edit_showimg2']['name'] != '')
        {
            import('ORG.UploadFile');
            $upload = new UploadFile();// 实例化上传类
            $upload->maxSize  = 2097152 ;// 设置附件上传大小
//            $upload->saveRule  = date('YmdHis',time()) . mt_rand();// 设置附件上传文件名
            $upload->allowExts  = array('jpg', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath =  BASEDIR.'Public/Images/sixty-jihemsg/';// 设置附件上传目录
            if(!$upload->upload()) {// 上传错误提示错误信息

                echo "<script>alert('图片上传失败!');history.go(-1);</script>";
                $this -> error('图片上传失败!');
            }else{// 上传成功 获取上传文件信息
                $info =  $upload->getUploadFileInfo();
//                var_dump($info);exit;
                $showimgs = $info['0']['savename'];
                $showimgs_e = $info['0']['extension'];
                $showimgd = $info['1']['savename'];
                $showimgd_e = $info['1']['extension'];
//                var_dump($info);die;
//                $showimg = date('YmdHis',time()) . mt_rand().$showimgs_e;
//                $detailimg = date('YmdHis',time()) . mt_rand().$showimgd_e;
                //上传七牛云
                //上传图片存储绝对路径
                $cz_filepathname_s  = BASEDIR.'Public/Images/sixty-jihemsg/'.$showimgs;
                $cz_filepathname_d  = BASEDIR.'Public/Images/sixty-jihemsg/'.$showimgd;

                if(false===func_isImage($cz_filepathname_s) || false===func_isImage($cz_filepathname_d)) {
                    //解析失败，非正常图片---后台图片上传时本函数可不使用
                    @unlink($cz_filepathname_s); //删除文件
                    @unlink($cz_filepathname_d); //删除文件
                    //非正常图片
                    echo "<script>alert('图片上传失败!');history.go(-1);</script>";
                    $this -> error('图片上传失败!');
                }else {
                    //上传到七牛云之前先进行图片格式转换，统一使用jpg格式,图片格式转换
                    $r = hy_resave2jpg($cz_filepathname_s);
                    if($r!==false) {
                        //图片格式转换成功，赋值新名称
                        $cz_filepathname_s = $r;
                        $cz_filepathname_s = str_replace('\\','/',$cz_filepathname_s);
                        $showimg = date('YmdHis',time()) . mt_rand().'.jpg';
                    }else{
                        $showimg = date('YmdHis',time()) . mt_rand().'.'.$showimgs_e;
                    }
                    $r2 = hy_resave2jpg($cz_filepathname_d);
                    if($r2!==false) {
                        //图片格式转换成功，赋值新名称
                        $cz_filepathname_d = $r2;
                        $cz_filepathname_d = str_replace('\\','/',$cz_filepathname_d);
                        $detailimg = date('YmdHis',time()) . mt_rand().'.jpg';
                    }else{
                        $detailimg = date('YmdHis',time()) . mt_rand().'.'.$showimgd_e;
                    }
                    //上传到七牛云
                    //参数，bucket，文件绝对路径名称，存储名称，是否覆盖同名文件
                    $r = upload_qiniu('sixty-jihemsg',$cz_filepathname_s,$showimg,'yes');
                    $r2 = upload_qiniu('sixty-jihemsg',$cz_filepathname_d,$detailimg,'yes');

                    if(false===$r || false===$r2) {
                        @unlink($cz_filepathname_s); //删除文件
                        @unlink($cz_filepathname_d); //删除文件
                        echo'失败';die;
                        //上传失败
                    }else {
                        @unlink($cz_filepathname_s); //删除文件
                        @unlink($cz_filepathname_d); //删除文件
                        //上传成功
                        $data['showimg'] = $showimg;
                        $data['detailimg'] = $detailimg;
//                        var_dump($data);
                        //删除七牛云旧图片
                        $a = delete_qiniu('sixty-jihemsg', $res_old['showimg']);
                        $a = delete_qiniu('sixty-jihemsg', $res_old['detailmimg']);
                    }
                }
            }

        }else if($_FILES['edit_showimg']['name'] != '' || $_FILES['edit_showimg2']['name'] != ''){
            import('ORG.UploadFile');
            $upload = new UploadFile();// 实例化上传类
            $upload->maxSize  = 2097152 ;// 设置附件上传大小
            $upload->saveRule  = date('YmdHis',time()) . mt_rand();// 设置附件上传文件名
            $upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath =  BASEDIR.'Public/Images/sixty-jihemsg/';// 设置附件上传目录
            if(!$upload->upload()) {// 上传错误提示错误信息
                echo "<script>alert('图片上传失败!');history.go(-1);</script>";
                $this -> error('图片上传失败!');
            }else{// 上传成功 获取上传文件信息
                $info =  $upload->getUploadFileInfo();
                $showimg_new = $info['0']['savename'];
                $showimg_new_e = $info['0']['extension'];
//                var_dump($showimg_new);die;
//                var_dump($info);die;
                //上传七牛云
                //上传图片存储绝对路径
                $cz_filepathname  = BASEDIR.'Public/Images/sixty-jihemsg/'.$showimg_new;

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
                        $showimg_new = date('YmdHis',time()) . mt_rand().'.jpg';
//                        var_dump($showimg_new);die;
                    }else{
                        $showimg_new = date('YmdHis',time()) . mt_rand().$showimg_new_e;
                    }
                    //上传到七牛云
                    //参数，bucket，文件绝对路径名称，存储名称，是否覆盖同名文件
                    $r = upload_qiniu('sixty-jihemsg',$cz_filepathname,$showimg_new,'yes');

                    if(false===$r) {
                        @unlink($cz_filepathname); //删除文件
                        //上传失败
                    }else {
                        @unlink($cz_filepathname); //删除文件
                        //上传成功
     
                        if($_FILES['edit_showimg']['name'] != ''){
                            $data['showimg'] = $showimg_new;
//                    var_dump($data);die;
                            //删除七牛云旧图片
                            $a = delete_qiniu('sixty-jihemsg', $res_old['showimg']);
                        }else if($_FILES['edit_showimg2']['name'] != ''){
                            $data['detailimg'] = $showimg_new;
//                    var_dump($data);die;
                            //删除七牛云旧图片
                            $a = delete_qiniu('sixty-jihemsg', $res_old['detailimg']);
                        }
                    }
                }

            }
        }

//        var_dump($data);die;
        //执行插入
        $Model = new Model();
        $res = $Model -> table('sixty_jihemsg') -> where("id='".$id."'") -> save($data);

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //判断结果
        if($res) {
            echo "<script>alert('合集修改成功!');window.location.href='".__APP__.'/Gather/index'. $echourl ."';</script>";
            $this -> success('合集修改成功!','__APP__'.$echourl);
        }else {
            echo "<script>alert('合集修改失败!');history.go(-1);</script>";
            $this -> error('合集修改失败!');
        }
    }

    public function delgat_do(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_delgat_do);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //接收上传数据
        $id = trim($this->_post('del_id'));
        $submit = trim($this->_post('submitdel'));

        //判断上传数据来源
        if(!$id || !$submit)
        {
            echo "<script>alert('非法进入此页面!');history.go(-1);</script>";
            $this -> error('非法进入此页面!');
        }

        $Model = new Model();
        //查询ID是否存在
        $res_showimg = $Model -> table('sixty_jihemsg') -> field('showimg,detailimg') -> where("id='".$id."'") -> find();
        if($res_showimg == '')
        {
            echo "<script>alert('非法进入此页面!');history.go(-1);</script>";
            $this -> error('非法进入此页面!');
        }

        //删除七牛云旧图片

        delete_qiniu('sixty-jihemsg', $res_showimg['showimg']);
        delete_qiniu('sixty-jihemsg', $res_showimg['detailimg']);

        //删除数据库数据
        $res = $Model -> table('sixty_jihemsg') -> where("id='".$id."'") -> delete();

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //判断删除结果
        if($res != ''){
            echo "<script>alert('合集数据删除成功!');window.location.href='".__APP__.'/Gather/index'. $echourl ."';</script>";
            $this -> success('合集数据删除成功!','__APP__'.$echourl);
        }else{
            echo "<script>alert('删除失败!');history.go(-1);</script>";
            $this -> error('删除失败!');
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
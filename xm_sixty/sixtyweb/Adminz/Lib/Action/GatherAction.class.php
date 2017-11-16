<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/2
 * Time: 11:46
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

        //返回查询条件
        $find_where = array(
            'find_id' => $gat_id,
            'find_name' => $gat_name,
            'find_sta_date' => $gat_find_sta_date,
            'find_end_date' => $gat_find_end_date,
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
        $list = $Model -> table('sixty_jihemsg') -> field('id, name, showimg, content, remark, create_datetime')
            -> where($where) -> order('create_datetime desc') -> limit($Page->firstRow . ',' . $Page->listRows)
            -> select();

        foreach($list as $key_li => $val_li) {
            //获取七牛云图片
            $showimg = $list[$key_li]['showimg'];
            $imgwidth = '100';
            $imgheight = '100';
            $addressimg = hy_qiniuimgurl('sixty-jihemsg',$showimg,$imgwidth,$imgheight);
//            var_dump($addressimg);die;
            $list[$key_li]['showimg'] = "<img src='" . $addressimg . "' />";
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

        //实例化方法
        $Model = new Model();


        //判断分类名是否存在
        $res_name = $Model -> table('sixty_jihemsg') -> field('id') -> where("name='" . $name . "'") ->find();
        if($res_name != ''){
            echo "<script>alert('此合集名已存在，请使用其他名称');history.go(-1);</script>";
            $this -> error('此合集名已存在，请使用其他名称');
        }


        //判断图片是否上传
        if($_FILES['showimg']['name'] != '')
        {
            import('ORG.UploadFile');
            $upload = new UploadFile();// 实例化上传类
            $upload->maxSize  = 2097152 ;// 设置附件上传大小
            $upload->saveRule  = date('YmdHis',time()) . mt_rand();// 设置附件上传文件名
            $upload->allowExts  = array('jpg', 'png', 'jpeg');// 设置附件上传类型
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
                    $r = upload_qiniu('sixty-jihemsg',$cz_filepathname,$showimg,'yes');

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
            echo "<script>alert('合集展示图不能为空！');history.go(-1);</script>";
            $this -> error('合集展示图不能为空！');
        }


        //准备插入数组
        $create_timedate = date('Y-m-d H:i:s', time());
        $data = array(
            'name' => $name,
            'content' => $content,
            'remark' => $remark,
            'showimg' => $showimg,
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
            echo "<script>alert('合集添加成功!');window.location.href='".__APP__.'/Gather/index'. $echourl ."';</script>";
            $this -> success('合集添加成功!','__APP__'.$echourl);

        }else{
            unlink(BASEDIR.'tmpimage/'.$showimg);
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
        $list = $Model -> table('sixty_jihemsg') -> field('id, name, showimg, content, remark')
            -> where("id='".$id."'") -> find();

        //获取七牛云图片
        $showimg = $list['showimg'];
        $imgwidth = '100';
        $imgheight = '100';
        $addressimg = hy_qiniuimgurl('sixty-jihemsg',$showimg,$imgwidth,$imgheight);
        $list['showimg'] = "<img src='" . $addressimg . "' />";

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


        //判断图片是否上传
        if($_FILES['edit_showimg']['name'] != '')
        {
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
                //上传七牛云
                //上传图片存储绝对路径
                $cz_filepathname  = BASEDIR.'Public/Images/sixty-jihemsg/'.$showimg_new;

                if(false===func_isImage($cz_filepathname)) {
                    //解析失败，非正常图片---后台图片上传时本函数可不使用
                    @unlink($oldfile); //删除文件
                    //非正常图片
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
                    $r = upload_qiniu('sixty-jihemsg',$cz_filepathname,$showimg_new,'yes');

                    if(false===$r) {
                        @unlink($cz_filepathname); //删除文件
                        //上传失败
                    }else {
                        @unlink($cz_filepathname); //删除文件
                        //上传成功
                    }
                }

                //删除七牛云旧图片
                $a = delete_qiniu('sixty-jihemsg', $res_old['showimg']);

                //准备插入数组
                $data = array(
                    'name' => $name,
                    'content' => $content,
                    'remark' => $remark,
                    'showimg' => $showimg_new,
                );
            }
        }else{
            //准备插入数组
            $data = array(
                'name' => $name,
                'content' => $content,
                'remark' => $remark,
            );
        }


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
        $res_showimg = $Model -> table('sixty_jihemsg') -> field('showimg') -> where("id='".$id."'") -> find();
        if($res_showimg == '')
        {
            echo "<script>alert('非法进入此页面!');history.go(-1);</script>";
            $this -> error('非法进入此页面!');
        }

        //删除七牛云旧图片

        delete_qiniu('sixty-jihemsg', $res_showimg['showimg']);

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
}
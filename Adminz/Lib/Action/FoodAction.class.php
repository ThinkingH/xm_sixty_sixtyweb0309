<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/6
 * Time: 15:42
 */
class FoodAction extends Action {
    //定义各模块锁定级别
    private $lock_addfood   = '97';
    private $lock_addfood_do = '97';

    public function addfood() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addfood );
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收视频id
        $v_idp = trim($this->_post('video_id'));
        $v_idg = trim($this->_get('to_video_id'));

        //判断数据来源
        if($v_idp == '' && $v_idg == '') {
            echo "<script>alert('非法进入此页面！');history.go(-1);</script>";
            $this -> error('非法进入此页面！');
        }


        if($v_idp != '') {
            $v_id = $v_idp;
        }else {
            $v_id = $v_idg;
        }
        //实例化方法
        $Model = new Model();
        //查询材料
        $list_cailiao = $Model -> table('sixty_video_cailiao') -> field('id, name, vid, yongliang')
            -> where("vid='". $v_id ."'") -> order('id asc') -> select();

        //计算总数
        $len_c = count($list_cailiao);
        //总数小于24个
        if($len_c < 24) {
            //把材料长度补全到24个
            $i = $len_c + 1;
            for($i; $i<=24; $i++) {
                $list_cailiao[$i]['name'] = '';
                $list_cailiao[$i]['id'] = '';
                $list_cailiao[$i]['yongliang'] = '';
            }
        }

        //遍历材料结果集
        foreach($list_cailiao as $key_li => $val_li) {

            //把24个结果集分为3组，每组8个
            if($key_li <= 8) {//1-8个
                //添加2个字段，用于添加时接收数据
                $list_cailiao[$key_li]['cl_flag'] = 'cl_flag' . $key_li;
                $list_cailiao[$key_li]['cl_name'] = 'cl_name' . $key_li;
                $list_cailiao_8[] = $list_cailiao[$key_li];

            }else if($key_li > 8 && $key_li <= 16) {//9-16个
                //添加2个字段，用于添加时接收数据
                $list_cailiao[$key_li]['cl_flag'] = 'cl_flag' . $key_li;
                $list_cailiao[$key_li]['cl_name'] = 'cl_name' . $key_li;
                $list_cailiao_16[] = $list_cailiao[$key_li];

            }else if($key_li > 16 && $key_li <= 24) {//17-24个
                //添加2个字段，用于添加时接收数据
                $list_cailiao[$key_li]['cl_flag'] = 'cl_flag' . $key_li;
                $list_cailiao[$key_li]['cl_name'] = 'cl_name' . $key_li;
                $list_cailiao_24[] = $list_cailiao[$key_li];
            }

        }

        //输出到模板
        $this->assign('list_cailiao_8', $list_cailiao_8);
        $this->assign('list_cailiao_16', $list_cailiao_16);
        $this->assign('list_cailiao_24', $list_cailiao_24);


        //查询步骤
        $list_buzhou = $Model -> table('sixty_video_buzhou') -> field('id, buzhouid, vid, buzhoucontent')
            -> where("vid='". $v_id ."'") -> order('buzhouid asc') -> select();

        //判断结果集长度
        $len_b = count($list_buzhou);

        //长度不够用空数据补满
        if($len_b < 24) {
            $i = $len_b + 1;
            for($i; $i<=24; $i++) {
                $list_buzhou[$i]['buzhouid'] = '';
                $list_buzhou[$i]['id'] = '';
                $list_buzhou[$i]['buzhoucontent'] = '';
            }
        }

        //遍历结果集
        foreach($list_buzhou as $key_bz => $val_bz) {
            //每8个键值对为一组，分为3组
            if($key_bz <= 8) {
                $list_buzhou[$key_bz]['bz_flag'] = 'bz_flag'.$key_bz;
                $list_buzhou[$key_bz]['buzhouid'] = $key_bz;
                $list_buzhou8[] = $list_buzhou[$key_bz];
            }else if($key_bz > 8 && $key_bz <= 16) {
                $list_buzhou[$key_bz]['bz_flag'] = 'bz_flag'.$key_bz;
                $list_buzhou[$key_bz]['buzhouid'] = $key_bz;
                $list_buzhou16[] = $list_buzhou[$key_bz];
            }else if($key_bz > 16 && $key_bz <= 24) {
                $list_buzhou[$key_bz]['bz_flag'] = 'bz_flag'.$key_bz;
                $list_buzhou[$key_bz]['buzhouid'] = $key_bz;
                $list_buzhou24[] = $list_buzhou[$key_bz];
            }

        }

        //输出到模板
        $this->assign('list_buzhou8', $list_buzhou8);
        $this->assign('list_buzhou16', $list_buzhou16);
        $this->assign('list_buzhou24', $list_buzhou24);
        $this->assign('video_id', $v_id);

        $this->display();
    }

    public function addfood_do() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addfood_do );
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //接收视频id
        $v_idp = trim($this->_post('video_id'));
        $v_idg = trim($this->_get('to_video_id'));


        //判断来源，接收数据为空返回非法
        if($v_idp == '' && $v_idg == '') {
            echo "<script>alert('非法进入此页面！');history.go(-1);</script>";
            $this -> error('非法进入此页面！');
        }

        if($v_idp != '') {
            $v_id = $v_idp;
        }else {
            $v_id = $v_idg;
        }
        //实例化模型
        $Model = new Model();
        //删除旧数据
        $res_bz = $Model -> table('sixty_video_buzhou') -> where("vid='".$v_id."'") -> delete();

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);


        $res_cl = $Model -> table('sixty_video_cailiao') -> where("vid='".$v_id."'") -> delete();

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //步骤添加
        //准备上传数据 input name
        //准备sql语句
        $sql_b = 'insert into sixty_video_buzhou (vid, buzhouid, buzhoucontent) select ';

        $basename = 'bz_flag';
        //循环接收上传数据
        $n = 1;
        for($i=0;$i<=24;$i++) {
            $buzhouarr = trim($this->_post($basename.$i));
            if($buzhouarr != '') {
                $sql_b .= " '".$v_id."','" .$n."','".$buzhouarr."' union all select";
                $n++;
            }
        }

        $sql_b = substr($sql_b,0, -17);

        $res_b =$Model -> execute($sql_b);

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //执行添加
        //材料sql语句
        $sql_c = 'insert into sixty_video_cailiao (vid, name, yongliang) select ';

        //准备上传数据 input name
        $basename_f = 'cl_flag';
        $basename_n = 'cl_name';
        $n = 1;
        //循环接收上传数据
        for($i=0;$i<=24;$i++) {
            $cailiaod_f = trim($this->_post($basename_f.$i));
            $cailiaod_n = trim($this->_post($basename_n.$i));

            if($cailiaod_f != '' || $cailiaod_n !='') {
                $sql_c .= " '".$v_id."','" .$cailiaod_n."','".$cailiaod_f."' union all select";
                $n++;
            }
        }

        //去掉结尾的多余字符
        $sql_c = substr($sql_c,0, -17);

        //实例化模型
        $Model = new Model();
        //执行添加
        $res_c =$Model -> execute($sql_c);

        //写入日志
        $templogs = $Model->getlastsql();
        hy_caozuo_logwrite($templogs,__CLASS__.'---'.__FUNCTION__);

        //成功返回成功
        echo "<script>alert('提交成功!');window.location.href='".__APP__.'/Video/index'.$echourl."';</script>";
        $this -> success('提交成功!','__APP__'.$echourl);

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
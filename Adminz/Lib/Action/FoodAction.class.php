<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/6
 * Time: 15:42
 */
class FoodAction extends Action {
    //定义各模块锁定级别
    private $lock_addstep    = '9';
    private $lock_addstep_do = '9';
    private $editstep_do     = '9';

    public function addfood() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收视频id
        $v_id = trim($this->_post('video_id'));

        //实例化方法
        $Model = new Model();
        $list_cailiao = $Model -> table('sixty_video_cailiao') -> field('id, name, vid, yongliang')
            -> where("vid='". $v_id ."'") -> select();

        $len = count($list_cailiao);
        if($len < 24) {
            $i = $len + 1;
            for($i; $i<=24; $i++) {
                $list_cailiao[$i]['name'] = '';
                $list_cailiao[$i]['id'] = '';
                $list_cailiao[$i]['yongliang'] = '';
            }
        }
        foreach($list_cailiao as $key_li => $val_li) {

            if($key_li <= 8) {
                $list_cailiao[$key_li]['cl_flag'] = 'cl_flag' . $key_li;
                $list_cailiao[$key_li]['cl_name'] = 'cl_name' . $key_li;
                $list_cailiao_8[] = $list_cailiao[$key_li];
            }else if($key_li > 8 && $key_li <= 16) {
                $list_cailiao[$key_li]['cl_flag'] = 'cl_flag' . $key_li;
                $list_cailiao[$key_li]['cl_name'] = 'cl_name' . $key_li;
                $list_cailiao_16[] = $list_cailiao[$key_li];
            }else if($key_li > 16 && $key_li <= 24) {
                $list_cailiao[$key_li]['cl_flag'] = 'cl_flag' . $key_li;
                $list_cailiao[$key_li]['cl_name'] = 'cl_name' . $key_li;
                $list_cailiao_24[] = $list_cailiao[$key_li];
            }

        }
        $this->assign('list_cailiao_8', $list_cailiao_8);
        $this->assign('list_cailiao_16', $list_cailiao_16);
        $this->assign('list_cailiao_24', $list_cailiao_24);

        //实例化方法
        $Model = new Model();
        $list_buzhou = $Model -> table('sixty_video_buzhou') -> field('id, buzhouid, vid, buzhoucontent')
            -> where("vid='". $v_id ."'") -> select();

//        $len = count($list_buzhou);
        if($len < 24) {
            $i = $len + 1;
            for($i; $i<=24; $i++) {
                $list_buzhou[$i]['buzhouid'] = '';
                $list_buzhou[$i]['id'] = '';
                $list_buzhou[$i]['buzhoucontent'] = '';
            }
        }
        foreach($list_buzhou as $key_bz => $val_bz) {
            if($key_bz <= 8) {
                $list_buzhou[$key_bz]['bz_flag'] = 'bz_flag'.$key_bz;
//                $list_buzhou[$key_bz]['buid'] = $key_bz;
                $list_buzhou8[] = $list_buzhou[$key_bz];
            }else if($key_bz > 8 && $key_li <= 16) {
                $list_buzhou[$key_bz]['bz_flag'] = 'bz_flag'.$key_bz;
//                $list_buzhou[$key_bz]['buid'] = $key_bz;
                $list_buzhou16[] = $list_buzhou[$key_bz];
            }else if($key_bz > 16 && $key_li <= 24) {
                $list_buzhou[$key_bz]['bz_flag'] = 'bz_flag'.$key_bz;
//                $list_buzhou[$key_bz]['buid'] = $key_bz;
                $list_buzhou24[] = $list_buzhou[$key_bz];
            }

        }
//        var_dump($list_buzhou24);die;
        $this->assign('list_buzhou8', $list_buzhou8);
        $this->assign('list_buzhou16', $list_buzhou16);
        $this->assign('list_buzhou24', $list_buzhou24);
        $this->assign('video_id', $v_id);

        $this->display();
    }

    public function addfood_do() {


        $v_id = trim($this->_post('video_id'));
        //实例化模型
        $Model = new Model();
        $res = $Model -> table('sixty_video_buzhou') -> where("vid='".$v_id."'") -> delete();

        $res = $Model -> table('sixty_video_cailiao') -> where("vid='".$v_id."'") -> delete();

        //准备上传数据 input name
        //准备sql语句
        $sql_b = 'insert into sixty_video_buzhou (vid, buzhouid, buzhoucontent) select ';

        $buzhouidarr = '';
        $basename = 'bz_flag';
        $n = 1;
        //循环接收上传数据
        for($i=1;$i<=24;$i++) {
            $buzhouidarr = trim($this->_post($basename.$i));

            if($buzhouidarr != '') {
                $sql_b .= " '".$v_id."','" .$n."','".$buzhouidarr."' union all select";
                $n++;
            }
        }
        $sql_b = substr($sql_b,0, -17);

        $res_b =$Model -> query($sql_b);

        //执行添加
        //材料sql语句
        $sql_c = 'insert into sixty_video_cailiao (vid, name, yongliang) select ';

        //准备上传数据 input name
        $cailiaodarr = '';
        $basename_f = 'cl_flag';
        $basename_n = 'cl_name';
        $n = 1;
        //循环接收上传数据
        for($i=1;$i<=24;$i++) {
            $cailiaod_f = trim($this->_post($basename_f.$i));
            $cailiaod_n = trim($this->_post($basename_n.$i));

            if($cailiaod_f != '' || $cailiaod_n !='') {
                $sql_c .= " '".$v_id."','" .$cailiaod_n."','".$cailiaod_f."' union all select";
                $n++;
            }
        }

        //去掉结尾的多余字符
        $sql_c = substr($sql_c,0, -17);

//var_dump($sql_b);die;
        //实例化模型
        $Model = new Model();
//        var_dump($sql_c);die;
        //执行添加
        $res_c =$Model -> query($sql_c);

        //判断结果
        if($res_c === false || $res_b === false) {
            echo "<script>alert('步骤提交失败！');history.go(-1);</script>";
            $this -> error('步骤提交失败！');
        }else {
            //成功返回成功
            echo "<script>alert('步骤提交成功!');window.location.href='".__APP__.'/Video/index'.$echourl."';</script>";
            $this -> success('步骤提交成功!','__APP__'.$echourl);
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
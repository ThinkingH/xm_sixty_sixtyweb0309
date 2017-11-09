<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/3
 * Time: 17:49
 */
class StepAction extends Action{
    //定义各模块锁定级别
    private $lock_addstep    = '9';
    private $lock_addstep_do = '9';
    private $editstep_do = '9';


    public function addstep(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addstep);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收数据
        $video_id = trim($this->_post('video_id'));

        //实例化方法
        $Model = new Model();

        //执行搜索
        $list_se = $Model -> table('sixty_video_buzhou') -> field('id, vid, buzhouid, buzhoucontent')
            -> where("vid='".$video_id."'") -> order('buzhouid asc') -> select();


        //遍历结果，插入修改判断条件字段
        $i=1;
        foreach ($list_se as $key_li => $val_li) {
            $list_se[$key_li]['have'] = 'have'.$i;
            $i++;
        }

        //计算条数
        $count = count($list_se);

        //准备数组kong用于显示空input
        $kong = array();
        $l = $count + 1;
        //准备数组kong
        for($i = $l; $i <= 20; $i++) {
            $kong[$i]['buzhouid'] = $i;
            $kong[$i]['shangchuan'] = 'shangchuan'.$i;
        }

        //输出到模板
        $this->assign('video_id', $video_id);
        $this->assign('list_se', $list_se);
        $this->assign('kong', $kong);
        $this->display();
    }

    public function addstep_do() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_addstep_do);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $v_id = trim($this->_post('video_id'));

        //准备sql语句
        $sql = 'insert into sixty_video_buzhou (vid, buzhouid, buzhoucontent) select ';


        //准备上传数据 input name
        $buzhouidarr = '';
        $basename = 'buzhouid';
        $n = 1;
        //循环接收上传数据
        for($i=1;$i<=24;$i++) {
            $buzhouidarr = trim($this->_post($basename.$i));
            if($buzhouidarr != '') {
                $sql .= " '".$v_id."','" .$n."','".$buzhouidarr."' union all select";
                $n++;
            }
        }

        //去掉结尾的多余字符
        $sql = substr($sql,0, -17);


        //实例化模型
        $Model = new Model();

        //执行添加
        $res =$Model -> query($sql);

        //判断结果
        if($res === false) {
            echo "<script>alert('步骤提交失败！');history.go(-1);</script>";
            $this -> error('步骤提交失败！');
        }else {
            //成功返回成功
            echo "<script>alert('步骤提交成功!');window.location.href='".__APP__.'/Video/index'.$echourl."';</script>";
            $this -> success('步骤提交成功!','__APP__'.$echourl);
        }
    }

    public function editstep_do() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->editstep_do);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

//      获取更新数据
        //准备步骤内容sql语句
        $sql_c = 'UPDATE sixty_video_buzhou SET buzhoucontent = CASE id';
        //准备步骤id sql语句
        $sql_id = " END, buzhouid = CASE id";

        $n = 1;
        //开始循环接收上传数据
        for($i=1; $i<=20; $i++) {
            //已标记名为依据接收id
            $id = trim($this->_post('have'.$i));
            if($id != '') {//id上传不为空
                //已刚接收的id为依据接收内容
                $buzhoucount = trim($this->_post($id));
                if($buzhoucount != '') {//内容上传不为空
                    //拼接sql语句
                    $sql_c .= " WHEN '".$id ."' THEN '" .$buzhoucount ."'";
                    $sql_id .= " WHEN '".$id ."' THEN '" .$n."'";
                    $n++;
                }
            }
        }

        //内容sql和步骤id sql拼接
        $sql_old = $sql_c.$sql_id.' END';

        //实例化模型
        $Model = new Model();
        //执行更新
        $res = $Model -> query($sql_old);
        //判断结果
        if($res === false) {
            echo "<script>alert('步骤修改失败！');history.go(-1);</script>";
            $this->error('步骤修改失败！');
        }




        //获取插入数据
        $v_id = trim($this->_post('video_id'));

        //空字符串接收数据
        $buzhouidarr = '';

        //准备input名称
        $basename = 'shangchuan';

        //准备sql语句
        $sql_new = 'insert into sixty_video_buzhou (vid, buzhouid, buzhoucontent) select ';

        //循环接收上传数据
        $n = 1;
        for($i=1;$i<=20;$i++) {
            //接收上传内容
            $buzhouidarr = trim($this->_post($basename.$i));
            if($buzhouidarr != '') {//上传内容不为空
                //拼接sql语句
                $sql_new .= " '".$v_id."','" .$n."','".$buzhouidarr."' union all select";
            }
            $n++;
        }

        //去掉结尾的多余字符
        $sql_new = substr($sql_new,0, -17);

        //获取sql长度
        $len = strlen($sql_new);
        //判断长度
        if($len > 52) {//sql长度大于52执行添加
            $res =$Model -> query($sql_new);
            if($res === false) {
                echo "<script>alert('步骤修改失败！');history.go(-1);</script>";
                $this->error('步骤修改失败！');
            }
        }

        //成功返回成功
        echo "<script>alert('步骤提交成功!');window.location.href='".__APP__.'/Video/index'.$echourl."';</script>";
        $this -> success('步骤提交成功!','__APP__'.$echourl);


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
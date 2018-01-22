<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16
 * Time: 19:13
 */

class InformAction extends Action
{

    //定义各模块锁定级别
    private $lock_tougao = '97';
    private $lock_pinglun = '97';
    private $lock_delpinglun_do = '97';


    public function index(){
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_delclass_do);
        //拼接URL
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        $this->display();



    }





    //判断用户是否登陆的前台展现封装模块
    private function loginjudgeshow($lock_key)
    {

        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $lockarr = loginjudge($lock_key);
        if($lockarr['grade'] == 'C') {
            //通过
        } else if($lockarr['grade'] == 'B') {
            exit($lockarr['exitmsg']);
        } else if($lockarr['grade'] == 'A') {
            echo $lockarr['alertmsg'];
            $this->error($lockarr['errormsg'], '__APP__/Login/index');
        } else {
            exit('系统错误，为确保系统安全，禁止登入系统');
        }
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
    }
}
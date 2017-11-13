<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/9
 * Time: 11:38
 */

class CollectAction extends Action {
    //定义各模块锁定级别
    private $lock_index         = '7';
    private $lock_statistics   = '7';


    public function index() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_index);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=


        //接收上传数据
        $id = trim($this->_get('user_id'));

        //实例化方法
        $Model = new Model();

        //分页
        import('ORG.Page');// 导入分页类
        $count = $Model -> table('sixty_video_shoucang')
            -> where("userid='" . $id . "'")
            -> count();// 查询满足要求的总记录数
        $Page = new Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('page',$show);// 赋值分页输出

        //根据用户ID查询收藏表
        $list = $Model -> table('sixty_video_shoucang') -> where("userid='" . $id . "'") -> order('create_datetime desc')
            -> limit($Page->firstRow . ',' . $Page->listRows) -> select();

        //遍历收藏表结果集
        $video_arr = array();
        foreach ($list as $key_li => $val_li) {
            if($val_li['type'] == 1) {
                $video_arr[] = $val_li['dataid'];
                $list[$key_li]['type'] = '视频';
            }

        }

        //把收藏视频ID中的重复ID去掉
        $video_arr = array_unique($video_arr);

        //如果ID数组为空,输出模板,数据为空
        if($video_arr == false) {
            $this->display();
            exit;
        }

        //准备查询条件数组
        $where_arr['id'] = array('in', $video_arr);

        //查询视频ID查询视频表数据
        $list_video = $Model -> table('sixty_video') -> field('id, biaoti') -> where($where_arr) -> select();

        //遍历收藏表结果集,并把视频查询结果插入到收藏结果中
        foreach($list as $key_l => $val_l) {

            //遍历视频结果集
            foreach ($list_video as $key_v => $val_v) {

                //判断收藏数据id是否等于视频id
                if($val_l['dataid'] == $val_v['id']) {
                    //相等,进行赋值
                    $list[$key_l]['video_biaoti'] = $val_v['biaoti'];
                }

            }
        }

        //输出模板
        $this -> assign('list', $list);
        $this -> display();

    }

    public function statistics() {
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        //判断用户是否登陆
        $this->loginjudgeshow($this->lock_statistics);
        //返回URL地址
        $echourl = func_baseurlcreate($_GET);
        $this->assign('echourl',$echourl);
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

        //接收上传数据
        $find_id = trim($this->_get('find_id'));
        $get_sta_day = trim($this->_get('find_sta_date'));
        $get_end_day = trim($this->_get('find_end_date'));
        $find_type = trim($this->_get('find_type'));

        //返回查询数据
        $this->assign('find_type', $find_type);
        $this->assign('find_id', $find_id);
        $this->assign('find_sta_date', $get_sta_day);
        $this->assign('find_end_date', $get_end_day);

        //判断查询日期是否提交
        if($get_sta_day != '') {
            $where_sta_day = $get_sta_day . ' 00:00:00';
            $where .= "create_datetime >= '" . $where_sta_day . "' and ";
        }
        if($get_end_day != '') {
            $where_end_day = $get_end_day . ' 23:59:59';
            $where .= "create_datetime <= '" . $where_end_day . "' and ";
        }

        //判断上传数据是否为空并拼接SQL条件
        if($find_id != '') {
            $where .= "dataid = '" . $find_id . "' and ";
        }
        if($find_num_top != '') {
            $where .= "colcont <= '" . $find_num_top . "' and ";
        }
        if($find_num_down != '') {
            $where .= "colcont >= '" . $find_num_top . "' and ";
        }
        if($find_type != '' && $find_type != '0') {
            $where .= "type = '" . $find_type . "' and ";
        }

        if($where != '') {
            $where = ' where ' . $where;
            $where = substr($where,0, -5);
        }
//        var_dump($where);die;
        //实例化方法
        $Model = new Model();

        $sql = "select count('dataid') AS colcont, type, dataid from sixty_video_shoucang " .$where." GROUP BY dataid order by colcont DESC ";
        $list = $Model -> query($sql);
        $count = count($list);

        //分页
        import('ORG.Page');// 导入分页类
        $Page = new Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('page',$show);// 赋值分页输出


        switch ($find_type) {
            case 1:
                $coll = array(
                    'table' => 'sixty_video',
                    'field' => 'biaoti',
                    'type_name' => '视频',
                    'type_id' => '1',
                );
                $sql = "select count('dataid') AS colcont, type, dataid from sixty_video_shoucang ".$where." GROUP BY dataid order by colcont DESC limit "."$Page->firstRow" . ',' . "$Page->listRows";
                $list = $Model -> query($sql, true);
                $res = $this->vice_list($coll,$list);
                break;

            case 2:
                $coll = array(
                    'table' => 'sixty_jihemsg',
                    'field' => 'name',
                    'type_name' => '合集',
                    'type_id' => '2',
                );
                $sql = "select count('dataid') AS colcont, type, dataid from sixty_video_shoucang".$where." GROUP BY dataid order by colcont DESC limit "."$Page->firstRow" . ',' . "$Page->listRows";
                $list = $Model -> query($sql);
                $res = $this->vice_list($coll,$list);
                break;

            default:
                $coll = array(
                    array(
                        'table' => 'sixty_video',
                        'field' => 'biaoti',
                        'type_name' => '视频',
                        'type_id' => '1',
                    ),
                    array(
                        'table' => 'sixty_jihemsg',
                        'field' => 'name',
                        'type_name' => '合集',
                        'type_id' => '2',
                    ),
                );
                $sql = "select count('dataid') AS colcont, type, dataid from sixty_video_shoucang".$where." GROUP BY dataid order by colcont DESC limit "."$Page->firstRow" . ',' . "$Page->listRows";

                $list = $Model -> query($sql);
//                var_dump($list);die;
                $res = $this->vice_list($coll,$list);
                break;
        }


        //生成类型下拉菜单
        $find_type_list = array(
            '0' => '全选',
            '1' => '视频',
            '2' => '合集',
        );
        $find_type_list = $this -> downlist($find_type_list, $find_type);


        //输出模板
        $this -> assign('list', $res);
        $this -> assign('find_type_list', $find_type_list);
        $this -> display();
    }


    //查询子表，并插入到主数据表结果集中
    //$list，主数据表结果集，
    //$coll = array('table' => 字表名称, 'field' => 字表要取出的字段, 'type_id' => 字表数据分类, 'type_name' => 分类命名,);
    private function vice_list($coll, $list) {

        //判断是一维数组还是多维数组
        if (count($coll) == count($coll, 1)) {

            //遍历收藏表结果集
            $video_arr = array();
            foreach ($list as $key_li => $val_li) {
                if($val_li['type'] == $coll['type_id']) {
                    $video_arr[] = $val_li['dataid'];
                    $list[$key_li]['type'] = $coll['type_name'];
                }
            }

            //准备查询条件数组
            $where_arr['id'] = array('in', $video_arr);

            $Model = new Model();
            //查询视频ID查询视频表数据
            $list_video = $Model -> table($coll['table']) -> field($coll['field'] . ', create_datetime, id') -> where($where_arr) -> select();
            //遍历收藏表结果集,并把视频查询结果插入到收藏结果中
            foreach($list as $key_l => $val_l) {

                //遍历视频结果集
                foreach ($list_video as $key_v => $val_v) {
                    //判断收藏数据id是否等于视频id
                    if($val_l['dataid'] == $val_v['id']) {
                        //相等,进行赋值
                        $list[$key_l]['video_biaoti'] = $val_v[$coll['field']];
                        $list[$key_l]['create_datetime'] = $val_v['create_datetime'];
                    }

                }
            }

        } else {

            foreach ($coll as $key_col => $val_coll) {
                //遍历收藏表结果集
                $video_arr = array();
                foreach ($list as $key_li => $val_li) {
                    if($val_li['type'] == $val_coll['type_id']) {
                        $video_arr[] = $val_li['dataid'];
                        $list[$key_li]['type'] = $val_coll['type_name'];
                    }
                }

                //准备查询条件数组
                $where_arr['id'] = array('in', $video_arr);

                $Model = new Model();
                //查询视频ID查询视频表数据
                $list_video = $Model -> table($val_coll['table']) -> field($val_coll['field'] . ', create_datetime, id') -> where($where_arr) -> select();
//                var_dump($list_video);
                //遍历收藏表结果集,并把视频查询结果插入到收藏结果中
                foreach($list as $key_l => $val_l) {

                    //遍历视频结果集
                    foreach ($list_video as $key_v => $val_v) {
                        //判断收藏数据id是否等于视频id
                        if($val_l['dataid'] == $val_v['id']) {
                            //相等,进行赋值
                            $list[$key_l]['video_biaoti'] = $val_v[$val_coll['field']];
                            $list[$key_l]['create_datetime'] = $val_v['create_datetime'];
                        }

                    }
                }
            }
        }
        return $list;
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
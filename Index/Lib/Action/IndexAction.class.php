<?php

// 本类由系统自动生成，仅供测试用途
class IndexAction extends Action
{
    /*
     * 输出模板函数
     */
    public function index()
    {
        $this->display();
    }

    /*
     * 首页视频数据接口
     */
    public function homevideoinfo()
    {
        if (comm_func_origancheck()) {
            return true;
        }
        $re = array(
            'code' => '200',
            'img_url' => STATIC_PATH . 'static/file/test.jpg',
            'video_url' => STATIC_PATH . 'static/file/test.mp4'
        );
        echo json_encode($re);
    }

    /*
     * 返回类别接口
     */
    public function classifyinfo()
    {
        if (comm_func_origancheck()) {
            return true;
        }

        $searchstr = isset($_GET['search_data']) ? $_GET['search_data'] : '';
        $classtype = isset($_GET['classtype']) ? $_GET['classtype'] : '';
        $parr = array(
            'thetype' => '1017',
            'classtype' => $classtype,
            'searchstr' => $searchstr
        );
        $postdata = func_urldatacreate($parr);//拼接请求参数
        $postre = simulate_post(CONFIG_TXURL, $postdata);//请求接口返回的结果集
        if ($postre['httpcode'] === 200) {
            $ndata = json_decode($postre['content'], true);
            $re = array(
                'list' => array(),
                'name' => '',
                'number' => 0,
                'content' => '',
            );
            $re['name'] = $ndata['data']['name'];
            $re['number'] = $ndata['data']['count'];
            $re['content'] = $ndata['data']['content'];
            foreach ($ndata['data']['list'] as $val) {
                array_push($re['list'], array(
                    'type' => $val['type'],
                    'name' => $val['name'],
                    'number' => $val['count'],
                ));
            }
            echo json_encode($re);
//            var_dump($ndata['data']);
        } else {
            echo ERROE_JSON;
            exit();
        }
    }

    /**
     * 视频列表接口
     * @return bool
     */
    public function videolistinfo()
    {
        if (comm_func_origancheck()) {
            return true;
        }
        $re = array(
            'title' => '最新视频',
            'pagemsg' => array(),
            'list' => array()
        );
        $msgjihe = isset($_GET['msgjihe']) ? $_GET['msgjihe'] : '';
        $searchstr = isset($_GET['search_data']) ? $_GET['search_data'] : '';
        $classify1 = isset($_GET['classify1']) ? $_GET['classify1'] : '';
        $classify2 = isset($_GET['classify2']) ? $_GET['classify2'] : '';
        $classify3 = isset($_GET['classify3']) ? $_GET['classify3'] : '';
        $classify4 = isset($_GET['classify4']) ? $_GET['classify4'] : '';
        $pagesize = isset($_GET['pagesize']) ? $_GET['pagesize'] : '12';
        $page = isset($_GET['page']) ? $_GET['page'] : '';
        //准备请求参数
        $parr = array(
            'thetype' => '1015',
            'page' => $page,
            'pagesize' => $pagesize,
        );
        if ($msgjihe != '')
            $parr['msgjihe'] = $msgjihe;
        if ($searchstr != '')
            $parr['searchstr'] = $searchstr;
        if ($classify1 != '')
            $parr['classify1'] = $classify1;
        if ($classify2 != '')
            $parr['classify2'] = $classify2;
        if ($classify3 != '')
            $parr['classify3'] = $classify3;
        if ($classify4 != '')
            $parr['classify4'] = $classify4;
        $postdata = func_urldatacreate($parr);//拼接请求参数
        $postre = simulate_post(CONFIG_TXURL, $postdata);//请求接口返回的结果集
        if ($postre['httpcode'] === 200) {
            $ndata = json_decode($postre['content'], true);
            $re['pagemsg']['nowpage'] = $ndata['data']['pagemsg']['nowpage'];
            $re['pagemsg']['sumpage'] = $ndata['data']['pagemsg']['sumpage'];
            foreach ($ndata['data']['list'] as $val) {
                array_push($re['list'], array(
                    'id' => $val['id'],
                    'img_url' => $val['showimg'],
                    'title' => $val['biaoti'],
                    'description' => $val['biaotichild'],
                    'ingredients' => $val['jieshao'],
                ));
            }
            echo json_encode($re);
        } else {
            echo ERROE_JSON;
            exit();
        }
    }

    /**
     * 特辑列表接口
     * @return bool
     */
    public function specialinfo()
    {
        if (comm_func_origancheck()) {
            return true;
        }
        $pagesize = isset($_GET['pagesize']) ? $_GET['pagesize'] : '4';
        $parr = array(
            'thetype' => '1017',
            'classtype' => 'msgjihe',
            'imgwidth' => '290',
            'imgheight' => '170',
            'pagesize' => $pagesize,
        );
        $postdata = func_urldatacreate($parr);//拼接请求参数
        $postre = simulate_post(CONFIG_TXURL, $postdata);//请求接口返回的结果集
        if ($postre['httpcode'] === 200) {
            $ndata = json_decode($postre['content'], true);
//            var_dump($ndata['data']);
            $l = count($ndata['data']['list']);
            $re = array();
            for ($i = 0; $i < $l; $i++) {
                array_push($re, array(
                    'id' => $ndata['data']['list'][$i]['jiheid'],
                    'img_url' => $ndata['data']['list'][$i]['showimg'],
                    'title' => $ndata['data']['list'][$i]['name'],
                    'description' => $ndata['data']['list'][$i]['content'],
                ));
            }
            echo json_encode($re);
        } else {
            echo ERROE_JSON;
            exit();
        }
    }

    /**
     * 特辑接口
     * @return bool
     */
    public function specialinfoone()
    {
        if (comm_func_origancheck()) {
            return true;
        }
        $searchstr = isset($_GET['searchstr']) ? $_GET['searchstr'] : '';
        $parr = array(
            'thetype' => '1017',
            'classtype' => 'msgjihe',
            'imgwidth' => '290',
            'imgheight' => '170',
        );
        if ($searchstr != '') {
            $parr['searchstr'] = $searchstr;
        }
        $postdata = func_urldatacreate($parr);//拼接请求参数
        $postre = simulate_post(CONFIG_TXURL, $postdata);//请求接口返回的结果集
        if ($postre['httpcode'] === 200) {
            $ndata = json_decode($postre['content'], true);
//              var_dump($ndata['data']);
            $re = $ndata['data'];
            echo json_encode($re);
        } else {
            echo ERROE_JSON;
            exit();
        }
    }

    /**
     * 美食详情接口
     */
    public function videoinfo()
    {
        if (comm_func_origancheck()) {
            return true;
        }
        $nowid = isset($_GET['nowid']) ? $_GET['nowid'] : '';
        if ($nowid == '') {
            echo ERROE_JSON;
            exit();
        }
        $parr = array(
            'thetype' => '1016',
            'nowid' => $nowid,
        );//准备请求参数
        $postdata = func_urldatacreate($parr);//拼接请求参数
        $postre = simulate_post(CONFIG_TXURL, $postdata);//请求接口返回的结果集
        if ($postre['httpcode'] === 200) {
            $ndata = json_decode($postre['content'], true);
//            var_dump($ndata['data']);
            /*-- 面包屑导航栏数据处理开始 --*/
            $breadcrumbinfo = array();//面包屑导航栏数据容器
            array_push($breadcrumbinfo, '首页');
            for ($i = 1; $i < 5; $i++) {
                if ($ndata['data']['classify' . $i] != '') {
                    array_push($breadcrumbinfo, $ndata['data']['classify' . $i]);
                } else {
                    break;
                }
            }
            array_push($breadcrumbinfo, $ndata['data']['biaoti']);
            /*-- 面包屑导航栏数据处理结束 --*/

            /*-- 视频数据处理开始 --*/
            $video = array(
                'img_url' => $ndata['data']['showimg'],
                'video_url' => $ndata['data']['videourl'],
            );
            /*-- 视频数据处理结束 --*/

            /*-- 美食数据处理开始 --*/
            $cate_info = array(
                'title' => $ndata['data']['biaoti'],
                'introduction' => $ndata['data']['biaotichild'],
                'description' => $ndata['data']['jieshao'],
                'maketime' => $ndata['data']['maketime'],
                'expense' => $ndata['data']['huafeimoney'],
                'people' => isset($ndata['data']['fenshu']) ? $ndata['data']['fenshu'] : '无',
                'materials' => $ndata['data']['cailiaoarr'],
                'step' => $ndata['data']['buzhouarr'],
            );
            /*-- 美食数据处理结束 --*/
            $re = array(
                'breadcrumb_data' => $breadcrumbinfo,
                'video_data' => $video,
                'cate_info' => $cate_info,
                'prompt_information' => $ndata['data']['tishishuoming']
            );
            echo json_encode($re);
        } else {
            echo ERROE_JSON;
            exit();
        }
    }

    /**
     * 美食文字评论接口
     */
    public function textcommentinfo()
    {
        if (comm_func_origancheck()) {
            return true;
        }
        $nowid = isset($_GET['nowid']) ? $_GET['nowid'] : '';

        $parr = array(
            'thetype' => '1018',
            'nowid' => $nowid,
            'pagesize' => 10,
        );//准备请求参数
        $postdata = func_urldatacreate($parr);//拼接请求参数
        $postre = simulate_post(CONFIG_TXURL, $postdata);//请求接口返回的结果集
        if ($postre['httpcode'] === 200) {
            $ndata = json_decode($postre['content'], true);
            $re = array(
                'allcount' => $ndata['data']['pagemsg']['allcount'],
                'list' => $ndata['data']['list'],
            );
            echo json_encode($re);
//            var_dump($ndata['data']);
        } else {
            echo ERROE_JSON;
            exit();
        }
    }

    /**
     * 美食图片评论接口
     */
    public function imgcommentinfo()
    {
        if (comm_func_origancheck()) {
            return true;
        }
        $nowid = isset($_GET['nowid']) ? $_GET['nowid'] : '';

        $parr = array(
            'thetype' => '1019',
            'nowid' => $nowid,
            'pagesize' => 9,
            'imgwidth' => 250,
            'imgheight' => 250,
        );//准备请求参数
        $postdata = func_urldatacreate($parr);//拼接请求参数
        $postre = simulate_post(CONFIG_TXURL, $postdata);//请求接口返回的结果集
        if ($postre['httpcode'] === 200) {
            $ndata = json_decode($postre['content'], true);
//            var_dump($ndata['data']);
            $re = array(
                'allcount' => $ndata['data']['pagemsg']['allcount'],
                'list' => $ndata['data']['list'],
            );
            echo json_encode($re);
        } else {
            echo ERROE_JSON;
            exit();
        }
    }
}
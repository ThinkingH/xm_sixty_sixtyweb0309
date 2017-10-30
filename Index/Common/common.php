<?php
/**
 * 设置接口可以跨域
 * @return bool
 */
function comm_func_origancheck()
{
    $allow_origin_arr = json_decode(ALLOW_ORIGIN_STR, true);//可以跨域的域名
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    if (in_array($origin, $allow_origin_arr)) {
        header('Access-Control-Allow-Origin:' . $origin);
        header('Access-Control-Allow-Methods:POST');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
    }
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        return true;
    }
    return false;
}

/**
 * 拼接url参数
 * @param array $urlarr
 * @return bool|string
 */
function func_urldatacreate($urlarr = array())
{
    if (!isset($urlarr['thetype'])) {
        return false;
    } else {
        $ckey = CONFIG_CKEY;
        $newarr = array();
        $newarr['version'] = CONFIG_VERSION;
        $newarr['system'] = CONFIG_SYSTEM;
        $newarr['sysversion'] = CONFIG_SYSVERSION;
        $newarr['thetype'] = $urlarr['thetype'];
        $newarr['nowtime'] = time();
        $newarr['md5key'] = md5($newarr['version'] . $newarr['system'] . $newarr['sysversion'] . $newarr['thetype'] . $newarr['nowtime'] . $ckey); //md5(version+system+sysversion+thetype+nowtime+ckey)
        foreach ($urlarr as $keyu => $valu) {
            if ($keyu != 'thetype') {
                $newarr[$keyu] = $valu;
            }
        }
        return urlcreate($newarr);
    }
}

/**
 * url拼接
 * @param array $urlarr
 * @return bool|string
 */
function urlcreate($urlarr = array())
{
    $baseurl = '';
    if (is_array($urlarr) && count($urlarr) > 0) {
        foreach ($urlarr as $key => $val) {
            $baseurl .= $key . '=' . urlencode($val) . '&';
        }
        $baseurl = substr($baseurl, 0, (strlen($baseurl) - 1));
    }
    return $baseurl;
}

/**
 * 模拟POST
 * @param $url
 * @param $data
 * @param int $timeout
 * @param array $header
 * @param string $useragent
 * @return mixed
 */
function simulate_post($url, $data, $timeout = 5000, $header = array(), $useragent = '')
{
    if (!function_exists('curl_init')) {
        return false;
    }
    if (substr($url, 0, 7) != 'http://' && substr($url, 0, 8) != 'https://') {
        return 'url_error';
    }
    $headerArr = array();
    foreach ($header as $n => $v) {
        $headerArr[] = $n . ':' . $v;
    }
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_NOBODY, 0); // 显示返回的body区域内容
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 2); // 对认证证书来源的检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
    if (trim($useragent) != '') {  //当传递useragent参数时，模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
    }
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    curl_setopt($curl, CURLOPT_NOSIGNAL, 1); //注意，毫秒超时一定要设置这个
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, $timeout); //设置连接等待毫秒数
    curl_setopt($curl, CURLOPT_TIMEOUT_MS, $timeout); //设置超时毫秒数
    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); // 获取的信息以文件流的形式返回
    if (count($headerArr) > 0) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArr);//设置HTTP头
    }
    $content = curl_exec($curl); //返回结果
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE); //页面状态码
    $run_time = (curl_getinfo($curl, CURLINFO_TOTAL_TIME) * 1000); //所用毫秒数
    $errorno = curl_errno($curl);
    curl_close($curl); //关闭curl
    $retarr = array(); //定义return数组变量
    $retarr['content'] = $content;
    $retarr['httpcode'] = $httpcode;
    $retarr['run_time'] = $run_time;
    $retarr['errorno'] = $errorno;
    return $retarr;
}
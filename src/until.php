<?php
/*
* 文件名: until.php
* 作者  : Yoby 微信logove email:logove@qq.com
* 日期时间: 2019/6/3  14:07
* 功能  :函数集合
*/
if (!function_exists('dump')) {
    function dump($arr){
        echo '<pre>'.print_r($arr,TRUE).'</pre>';
    }

}
/*
 * POST或GET的curl请求
 * $url 请求地址
 * $data 请求数组
 * */
if (!function_exists('curl')) {
    function curl($url, $data = '')
    {
        $ch = curl_init();
        if (class_exists('\CURLFile')) {
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
        } else {
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
            }
        }
        preg_match('/https:\/\//', $url) ? $ssl = TRUE : $ssl = FALSE;
        if ($ssl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $d = curl_exec($ch);
        curl_close($ch);
        return $d;
    }
}
/*返回ajax状态*/
if (!function_exists('json')) {
    function json($code = 200, $message = '请求成功', $list = array(), $total = 0)
    {
        $json = array(
            'code' => $code,
            'msg' => $message
        );
        if (!empty($list)) {
            $json['list'] = $list;
        }
        if (!empty($total)) {
            $json['total'] = $total;
        }

        header('Content-type: application/json');
        exit(json_encode($json, JSON_UNESCAPED_UNICODE));
    }
}
function qq(){
    return "你好";
}
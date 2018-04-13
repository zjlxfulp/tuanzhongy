<?php
use Common\Config\App;

if(!function_exists('verify_sign')) {
    function verify_sign($data,$app_config){
        if ( $data ){
            if ( empty($data['appkey']) || empty($data['sign']) ){
                return false;
            }
            //按照参数名排序
            ksort($data);
            //连接待加密的字符串
            $appkey = $data['appkey'];
            if ( empty($app_config[$appkey]) ){
                return false;
            }
            $codes = $appkey;
            while (list($key, $val) = each($data)){
                if (!in_array($key,array('appkey','sign')) ){//排除不签名的参数
                    $codes .=($key.$val);
                }
                $codes .= $app_config[$appkey];
                $sign = strtoupper(sha1($codes));
                if ( $data['sign'] == $sign ){
                    return true;
                }else{
                    return false;
                }
            }
        }else{
            return true;
        }
    }
}



/**
 * 读取配置文件
 * @param 文件名 不含.php /config
 * @param key
 * */
if(!function_exists('config')) {
    function config($config_name = '',$k = '')
    {
        $result = array();
        $const_name = strtoupper($config_name.'_config');
        if (!empty($config_name) && defined($const_name) ) {
            $result = json_decode(constant($const_name),true);

            if(!empty($k)) {
                $result = $result[$k];
            }
        }

        return $result;
    }

}

/**
 * 获取model实例
 * */
if(!function_exists('Model')) {
    function Model($model_name = '')
    {
        static $ms = array();
        $model_flie = __DIR__.'/../'.'/models/'.$model_name.'.php';


        throw new \Exception('not found model');
    }
}

if(!function_exists('config_item')) {
    function config_item($item) {
        return App::$$item;
    }
}

if(!function_exists('log_message')) {
    function log_message($item, $log_message) {

    }
}

if(!function_exists('show_error')) {
    function show_error($item, $log_message) {

    }
}

if (!function_exists('is_cli')) {
    function is_cli() {
        return (PHP_SAPI === 'cli');
    }
}

if ( ! function_exists('remove_invisible_characters'))
{
    function remove_invisible_characters($str, $url_encoded = TRUE)
    {
        $non_displayables = array();

        if ($url_encoded)
        {
            $non_displayables[] = '/%0[0-8bcef]/i';	// url encoded 00-08, 11, 12, 14, 15
            $non_displayables[] = '/%1[0-9a-f]/i';	// url encoded 16-31
        }

        $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';	// 00-08, 11, 12, 14-31, 127

        do
        {
            $str = preg_replace($non_displayables, '', $str, -1, $count);
        }
        while ($count);

        return $str;
    }
}


if ( ! function_exists('is_https'))
{
    function is_https()
    {
        if ( ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        {
            return TRUE;
        }
        elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
        {
            return TRUE;
        }
        elseif ( ! empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off')
        {
            return TRUE;
        }

        return FALSE;
    }
}

if ( ! function_exists('is_php'))
{
    function is_php($version) {
        static $_is_php;
        $version = (string) $version;

        if ( ! isset($_is_php[$version]))
        {
            $_is_php[$version] = version_compare(PHP_VERSION, $version, '>=');
        }

        return $_is_php[$version];
    }
}

if ( ! function_exists('getip'))
{
    function getip(){
        $ip=false;
        if(!empty($_SERVER["HTTP_CLIENT_IP"])){
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }
            for ($i = 0; $i < count($ips); $i++) {
                if (!eregi ("^(10│172.16│192.168).", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }
}

if ( ! function_exists('isjsonp'))
{
    function isjsonp(){
        if(@$_GET['jsoncallback']) {
            return true;
        }
        return false;
    }
}

if ( ! function_exists('url'))
{
    function url($pram){
        return '/index.php?' . http_build_query($pram);;
    }
}

if ( ! function_exists('array_to_php_code'))
{
    function array_to_php_code($arr, $k = '') {
        static $_key;
        static $_str;
        foreach($arr as $key => $val) {
            $s = is_numeric($key) ? "[$key]" : "['$key']";
            if(!preg_match("/". $k . "/i", $_key ,$match)) {
                $_key .= $k . $s;
            } else {
                $_key .= $s;
            }

            if(is_array($val) && !empty($val)) {
                array_to_php_code($val, $_key);
            } elseif(is_array($val) && empty($val)) {
                $_key .= " = array();\n";
                $_str .= '$conf'.$_key;
                $_key = "";
            } else {
                $_key .= is_numeric($val) ? " = $val;\n" : " = '$val';\n";
                $_str .= '$conf'.$_key;
                $_key = "";
            }
        }
        return $_str;
    }
}


if ( ! function_exists('microtime_float'))
{
    function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
}





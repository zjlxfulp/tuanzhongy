<?php

namespace Core;
/**
 * Curl
 */
class Curl {

    private static $ch; //curl初始化对象

    private static $proxy_on = false; //代理开关
    private static $proxy_address; //
    private static $proxy_port; //
    private static $proxy_type; //
    private static $proxy_auth; //
    private static $proxy_user; //
    private static $proxy_pass;
    private static $stime;

    public static function Init($second = 30, $out = false) {
        //初始化curl
        self::$stime = microtime();
        self::$ch = curl_init();
        self::timeOut($second);
        self::outPutHeader($out);
        self::outPutResult();
        self::UA('Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727;https://www.xbike.vip/)');
    }

    public static function timeOut($second = 30) {
        curl_setopt(self::$ch,CURLOPT_TIMEOUT, $second);//超时时间
    }

    public static function outPutHeader($out = false) {
        curl_setopt(self::$ch, CURLOPT_HEADER, $out);
    }

    public static function outPutResult() {
        curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
    }

    public static function Proxy(array $options = array()) {
        if(self::$proxy_on == true) {
            if(!empty ( $options )) {
                self::ProxyConf($options);
            }

            curl_setopt(self::$ch, CURLOPT_PROXYTYPE, self::$proxy_type == 'HTTP' ? CURLPROXY_HTTP : CURLPROXY_SOCKS5 );
            curl_setopt(self::$ch, CURLOPT_PROXY, self::$proxy_address);
            curl_setopt(self::$ch, CURLOPT_PROXYPORT, self::$proxy_port);

            if (! empty ( self::$proxy_user )) {
                curl_setopt ( self::$ch, CURLOPT_PROXYAUTH, self::$proxy_auth == 'BASIC' ? CURLAUTH_BASIC : CURLAUTH_NTLM );
                curl_setopt ( self::$ch, CURLOPT_PROXYUSERPWD, '[' . self::$proxy_user . ']:[' . self::$proxy_pass . ']' );
            }
        }
    }


    public static function ssl() {
        curl_setopt ( self::$ch, CURLOPT_SSL_VERIFYPEER, false );
    }

    public static function UA($str) {
        curl_setopt(self::$ch, CURLOPT_USERAGENT, $str);
    }

    public static function post($url, $query) {
        if (is_array($query)) {
            foreach ($query as $key => $val) {
                if ($val[0] != '@') {
                    $encode_key = urlencode($key);

                    if ($encode_key != $key) {
                        unset($query[$key]);
                    }

                    $query[$encode_key] = urlencode($val);
                }
            }
        }

        curl_setopt ( self::$ch, CURLOPT_POST, true );
        curl_setopt ( self::$ch, CURLOPT_URL, $url );
        curl_setopt ( self::$ch, CURLOPT_POSTFIELDS, $query );
      //  Log::fileLog('ubicycle_request', 'URL: '.$url);
       // Log::fileLog('ubicycle_request', 'POSTFIELDS: '.$query);
        return self::_requrest($url);
    }

    public static function effective_url() {
        return curl_getinfo ( self::$ch, CURLINFO_EFFECTIVE_URL );
    }

    public static function get($url, $query = array()) {
        if (! empty ( $query )) {
            $url .= strpos ( $url, '?' ) === false ? '?' : '&';
            $url .= is_array ( $query ) ? http_build_query ( $query ) : $query;
        }
        curl_setopt ( self::$ch, CURLOPT_URL, $url );
    //    Log::fileLog('ubicycle_request', 'URL: '.$url);
        return self::_requrest($url);
    }

    public static function put($url, $query = array()) {
        curl_setopt ( self::$ch, CURLOPT_CUSTOMREQUEST, 'PUT' );
        return self::post($url, $query);
    }

    public static function http_code() {
        return curl_getinfo( self::$ch, CURLINFO_HTTP_CODE );
    }

    public static function cookie($cookie) {
        curl_setopt ( self::$ch, CURLOPT_COOKIEJAR, $cookie );
        curl_setopt ( self::$ch, CURLOPT_COOKIEFILE, $cookie );
    }


    public static function addHeader($header) {
        curl_setopt(self::$ch, CURLOPT_HTTPHEADER, $header);
   //     Log::fileLog('ubicycle_request', 'HTTPHEADER: '.json_encode($header));
    }

    public static function close() {
        if (is_resource ( self::$ch )) {
            curl_close ( self::$ch );
        }
    }

    private static function _requrest($url = '') {
        $response = curl_exec ( self::$ch );
        Log::fileLog('ubicycle_request_time', array('url'=>$url, 'time'=>microtime() - self::$stime));
//        $errno = curl_errno ( self::$ch );
//
//        if ($errno > 0) {
//            throw new \Exception ( 'curl wrong' );
//        }

        return $response;
    }


    private static function ProxyConf($options) {
        self::$proxy_address = $options['address'] ? $options['address'] : self::$proxy_address;
        self::$proxy_port = $options['port'] ? $options['port'] : self::$proxy_port;
        self::$proxy_type = $options['type'] ? $options['type'] : self::$proxy_type;
        self::$proxy_auth = $options['auth'] ? $options['auth'] : self::$proxy_auth;
        self::$proxy_user = $options['user'] ? $options['user'] : self::$proxy_user;
    }

}

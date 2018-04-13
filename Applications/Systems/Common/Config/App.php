<?php

namespace Common\Config;

class App
{
    /*
    |	cookies
    */
    public static $cookie_prefix	= '';
    public static $cookie_domain	= '';
    public static $cookie_path		= '/';
    public static $cookie_secure	= FALSE;
    public static $cookie_httponly 	= FALSE;

    /*
    |	Cross Site Request Forgery
    */
    public static $global_xss_filtering = FALSE;
    public static $csrf_protection = FALSE;
    public static $csrf_token_name = FALSE;
    public static $csrf_cookie_name = FALSE;
    public static $csrf_expire = FALSE;
    public static $csrf_regenerate = FALSE;
    /*
    |	Charset
    */
    public static $charset = 'utf8';

    /*
    |	Encryption Key
    */
    public static $encryption_key = 'ahLJiga657uEDLiah123gau';

    /*
    |	Encryption Key
    */
    public static $code_switch = false;



    public static function getItem($item) {
        if(self::$$item) {
            return self::$$item;
        } else {
            return false;
        }
    }


    public static function allow_origin() {
        $allow_origin = array(
            'http://127.0.0.1',
            'http://192.168.99.231',
            'http://l.weidan.com:8000',
            'http://localhost',
            'http://t.bigfacetech.com',
            'http://xiaoxiaoceshi.com'
        );
        return $allow_origin;
    }
}

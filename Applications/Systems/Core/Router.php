<?php

namespace Core;

class Router {
    const VERSION = '0.3.0';


    public $autoload = array();
    public $on404 ="";
    public $onAppStart = NULL;
    public $appName = '';
    public $fictExtension = '';
    public $controllerDir = '';
    public $statistic_server = false;
    public $max_request = 10000;

    public static $static_route;
    public static $middleware = '';
    public static $app = 'Api';


    public static function any($url,$call)
    {
        if ( $url != "/" ){
            $url = strtolower(trim($url,"/"));
        }
        self::$static_route[self::$app][] = array(
                                'url'        => $url,
                                'call'       => $call,
                                'middleware' => self::$middleware
                                );
    }


    public static function group($rule,callable $callback)
    {
        if($rule){
            if(!empty($rule['middleware'])) {
                self::$middleware = $rule['middleware'];
                call_user_func($callback);
                self::$middleware = '';
            }
        }else{
            throw new SException('Route Group Error');
        }
    }


//    /*
//     * 完整url 绑定控制器及方法
//     * */
//    public function HandleStr($url, $controller, $func){
//        if ( $url != "/" ){
//            $url = strtolower(trim($url,"/"));
//        }
//        if(is_string($str) && file_exists($this->controllerDir . $str . '.php')){
//            $this->map[] = array($url,$str,3);
//        }else{
//            throw new SException('can not HandleStr');
//        }
//    }
//
//    public function AddFunc($url,callable $callback){
//        if ( $url != "/" ){
//            $url = strtolower(trim($url,"/"));
//        }
//        if ( is_callable($callback) ){
//            if ( $callback instanceof \Closure ){
//                $callback = \Closure::bind($callback, $this, get_class());
//            }
//        }else{
//            throw new SException('can not HandleFunc');
//        }
//        $this->map[] = array($url,$callback,2);
//    }
//
//    public function HandleFunc($url,callable $callback){
//        if ( $url != "/" ){
//            $url = strtolower(trim($url,"/"));
//        }
//        if ( is_callable($callback) ){
//            if ( $callback instanceof \Closure ){
//                $callback = \Closure::bind($callback, $this, get_class());
//            }
//        }else{
//            throw new SException('can not HandleFunc');
//        }
//        $this->map[] = array($url,$callback,1);
//
//    }

}
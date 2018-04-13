<?php

namespace Core;

use Workerman\Worker;
use Workerman\Protocols\Http;

/**
* 版本
*
* @var string
*/
class Rout {

    const VERSION = '0.3.0';

    private $conn = false;
    private $map = array();
    private $access_log = array();

    public $autoload = array();
    public $on404 ="";
    public $onAppStart = NULL;
    public $appName = '';
    public $fictExtension = '';
    public $controllerDir = '';
    public $statistic_server = false;
    public $max_request = 10000;

    public function __construct()
    {

    }

    public function _routes($routes)
    {
        $this->map = $routes;
    }


//    public function HandleFunc($url,callable $callback){
//        if ( $url != "/" ){
//            $url = strtolower(trim($url,"/"));
//        }
//        if ( is_callable($callback) ){
//            if ( $callback instanceof \Closure ){
//                $callback = \Closure::bind($callback, $this, get_class());
//            }
//        }else{
//            throw new SException('can not HandleFunc:' . $url);
//        }
//        $this->map[] = array($url,$callback,1);
//    }
//
//    public function HandleStr($url,$str){
//        if ( $url != "/" ){
//            $url = strtolower(trim($url,"/"));
//        }
//        if(is_string($str) && file_exists($this->controllerDir . $str . '.php')){
//            $this->map[] = array($url,$str,3);
//        }else{
//            throw new SException('can not HandleStr:' . $url);
//        }
//    }
//
//    public function AddFunc($url,callable $callback){
//
//            if ( $url != "/" ){
//                $url = strtolower(trim($url,"/"));
//            }
//            if ( is_callable($callback) ){
//                if ( $callback instanceof \Closure ){
//                    $callback = \Closure::bind($callback, $this, get_class());
//                }
//            }else{
//                throw new SException('can not HandleFunc:' . $url);
//            }
//            $this->map[] = array($url,$callback,2);
//    }

    private function show_404(){
        if ( $this->on404 ){
            $callback = \Closure::bind($this->on404, $this, get_class());
            call_user_func($callback);
        }else{
            Http::header("HTTP/1.1 404 Not Found");
            $html = '404 Not Found';
            $this->conn->send($html);
        }
    }

    private function auto_close(){
        if ( strtolower($_SERVER["SERVER_PROTOCOL"]) == "http/1.1" ){
            if ( isset($_SERVER["HTTP_CONNECTION"]) ){
                if ( strtolower($_SERVER["HTTP_CONNECTION"]) == "close" ){
                    $this->conn->close();
                }
            }
        }else{
            if ( $_SERVER["HTTP_CONNECTION"] == "keep-alive" ){

            }else{
                $this->conn->close();
            }
        }
        $this->access_log[7] = round(microtime_float() - $this->access_log[7],4);
//        echo implode(" - ",$this->access_log)."\n";
    }

    public function onClientMessage(&$connection){

        $this->conn = $connection;
        $this->access_log[0] = $_SERVER["REMOTE_ADDR"];
        $this->access_log[1] = date("Y-m-d H:i:s");
        $this->access_log[2] = $_SERVER['REQUEST_METHOD'];
        $this->access_log[3] = $_SERVER['REQUEST_URI'];
        $this->access_log[4] = $_SERVER['SERVER_PROTOCOL'];
        $this->access_log[5] = "NULL";
        $this->access_log[6] = 200;
        $this->access_log[7] = microtime_float();

        if ( empty($this->map) ){
            $str = <<<'EOD'
<div style="margin: 200px auto;width:600px;height:800px;text-align:left;">基于<a href="http://www.workerman.net/" target="_blank">Workerman</a>没有添加路由，请添加路由!
<pre>$app->HandleFunc("/",function($conn,$data) use($app){
    $conn->send("默认页");
});</pre>
</div>
EOD;
            echo $str;
            return;
        }

        $url= $_SERVER["REQUEST_URI"];
        $pos = stripos($url,"?");
        if ($pos != false) {
            $url = substr($url,0,$pos);
        }
        if ( $url != "/"){
            $url = strtolower(trim($url,"/"));
        }
        $url_arr = explode("/",$url);
        $class = empty($url_arr[0]) ? "_default" : $url_arr[0];
        $method = empty($url_arr[1]) ? "_default" : $url_arr[1];
//        if ( $this->statistic_server ){
//            $statistic_address = $this->statistic_server;
//            StatisticClient::tick($class, $method);
//        }
        $success = false;
//        var_dump($this->map);
        foreach($this->map[$this->appName] as $route){
            if ( $route['url'] == $url ){
                try {
                    if($route['middleware']) {
                        if(is_array($route['middleware'])) {
                            foreach ($route['middleware'] as $key=>$value) {
                                if($value) {
                                    $midd_obj = '\\'.$this->appName.'\middleware\\'.$value;
                                    if(!class_exists($midd_obj)) {
                                        throw new SException('No this middleware');
                                    };
                                    $midd_reuslt = $midd_obj::handle();
                                    if( $midd_reuslt !== true ) {
                                        echo $midd_reuslt;
                                        return;
                                    }
                                }
                            }
                        }else{
                            $midd_obj = '\\'.$this->appName.'\middleware\\'.$route['middleware'];
                            if(!class_exists($midd_obj)) {
                                throw new SException('No this middleware');
                            };
                            $midd_reuslt = $midd_obj::handle();
                            if( $midd_reuslt !== true ) {
                                echo $midd_reuslt;
                                return;
                            }
                        }
                    }
                    $callback = $route['call'];
                    $success = true;
                } catch (SException $e) {
                    echo 'No this middleware';
                    return;
                }
            }
//            if ( $route[2] == 1){//正常路由(闭包)
//                if ( $route[0] == $url ){
//                    $callback[] = $route[1];
//                    $success = true;
//                }
//            }else if ( $route[2] == 2 ){//中间件
//                if ( $route[0] == "/" ){
//                    $callback[] = $route[1];
//                }else if ( stripos($url,$route[0]) === 0 ){
//                    $callback[] = $route[1];
//                }
//            }else if ( $route[2] == 3 ){ // 正常路由(文件名)
//                $callback[] = $route;
//                $success = true;
//            }
        }
        if ( isset($callback) && $success ){
            try {

                if(is_string($callback) && strpos($callback,'@')) {
                    $cm = explode('@',$callback);
                    $obj_name = '\\'.$this->appName.'\Controller\\'.$cm[0];
                    if(!class_exists($obj_name)) {
                        throw new SException('No this class');
                    };
                    $obj = new $obj_name();
                    $method = $cm[1];
                    if ( !is_callable( array( $obj, $cm[1] ) ) )
                    {
                        throw new SException('No this function');
                    }
                    $obj->$method();
                }else {
                    $this->show_404();
                }

//                foreach($callback as $cl){
//                    if(is_array($cl)) {
//                        if(is_string($cl[1]) && $cl[0] == $class) {
//                            $obj_name = '\\' . $this->appName . '\Controller\\'.$cl[1];
//                            if(class_exists($obj_name)) {
//                                $obj = new $obj_name();
//                                if(!is_callable(array($obj,$method) , true , $callable_name)) {
//                                    throw new SException('No this function');
//                                }
//                                $obj->$method();
//                            } else {
//                                continue;
//                            }
//                        } else {
//                            continue;
//                        }
//                    }elseif ( call_user_func($cl) === true ) {
//                        break;
//                    }
//                }
//                if ( $this->statistic_server ){
//                    StatisticClient::report($class, $method, 1, 0, '', $statistic_address);
//                }
            }catch (SException $e) {
                // Jump_exit?
                if ($e->getMessage() != 'jump_exit') {
                    $this->access_log[5] = $e;
                }
                $code = $e->getCode() ? $e->getCode() : 500;
//                if ( $this->statistic_server ){
//                    StatisticClient::report($class, $method, $success, $code, $e, $statistic_address);
//                }
                $this->access_log[6] = 500;

                echo $e->getMessage();
            }
        }else{
            $this->show_404();
//            $code = 404;
//            $msg = "class $class not found";
//            if ( $this->statistic_server ){
//                StatisticClient::report($class, $method, $success, $code, $msg, $statistic_address);
//            }
        }
        $this->auto_close();

        // 已经处理请求数
        static $request_count = 0;
        // 如果请求数达到1000
        if( ++$request_count >= $this->max_request && $this->max_request > 0 ){
            Worker::stopAll();
        }
    }
 
}
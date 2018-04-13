<?php
/**
 * Created by PhpStorm.
 * User: fuliping
 * Date: 2017/12/27
 * Time: 17:10
 */
$start_time = time();
$st_date = date('m-d H:i:s');
//echo "ExecWxSQL_php start date {$st_date} \n";

$db = array(
    'host'      =>  '139.198.5.59',
    'user' 		=>	'root',
    'password' 	=>	'F&tTQgLJkGpo',
    'port' 		=>	'3306',
    'dbname' 	=>	'tuanzy'
);

$redis_ip = '127.0.0.1';
$select = 0;
$file_lock = "ExecWxSQL_@";

try{
    $redis = new Redis();
    $redis->connect($redis_ip,6379);
    $redis->select($select);

    if($redis->get($file_lock)) {
        $msg = "EXEC FILE ERROR : in service";
        throw new Exception($msg);
    }

    $mysql = connect_mysql($db);
    $appid_array = $redis->keys("*_lock");
    $appids = array();
    if($appids) {
        echo "{$st_date} ExecWxSQL.PHP start exec";
    }
    foreach ( $appid_array as $key=>$value ) {
        $redis->set($file_lock,1);
        $temp_app = explode('_',$value);
        $file = __DIR__.'/../Migrate/'.$temp_app[3].'.sql';
        if ( is_file($file) ) {
            exec_file_sql($mysql,$file);
        }
        $file_update = __DIR__.'/../Migrate/'.$temp_app[3].'_update.sql';
        if ( is_file($file_update) ) {
            exec_file_sql($mysql,$file_update);
        }
    }
}catch (Exception $e) {
    push_log($e->getMessage());
}
clearstatcache();
$redis->del($file_lock);
$cha =  time()-$start_time;
//echo "ExecWxSQL_php Total Time {$cha} \n";



function connect_mysql($db1)
{
    $host = $db1['host'];
    $user = $db1['user'];
    $password = $db1['password'];
    $db = $db1['dbname'];
    $port = $db1['port'];
    $mysql = new mysqli($host,$user,$password,$db,$port);
    return $mysql;
}

function exec_file_sql($mysql,$file)
{
    //文件锁
    $read_file_lock = $file.'_lock';
    touch($read_file_lock);

    $content = file_get_contents($file);
    $sql_arr = explode('__@@@__',$content);
    $temp = array();

    if( is_file($read_file_lock) ) {
        unlink($read_file_lock);
    }
    unlink($file);
    foreach ($sql_arr as $key=>$value) {
        if($value) {
            $temp[$key] = $value;
            $mysql->query("SET NAMES utf8mb4");
            $mysql->query($value);
            if(mysqli_affected_rows($mysql) < 1) {
                //如果出错 重写文件
                $diff = array_diff($sql_arr,$temp);
                if($diff) {
                    write_sql_log($file,implode('__@@@__',$diff));
                }
                $sql_msg = "ERROR_SQL : ".mysqli_error($mysql);
                throw new Exception($sql_msg);
            }
        }
    }
}

function push_log($Content)
{
    error_log( '['.date('m-d H:i:s').'] ExecWxSQL_'.$Content . "\n", 3, '/opt/log/wechat.log');
}
function write_sql_log($file,$content)
{
    file_put_contents($file, $content);
}
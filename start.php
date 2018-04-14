<?php
ini_set('display_errors', 'on');
use Workerman\Worker;

require_once 'vendor/autoload.php';

// 标记是全局启动
define('GLOBAL_START', 1);


// 加载所有Applications/*/start.php，以便启动所有服务
//foreach(glob(__DIR__.'/Start/start*.php') as $start_file)
//{
//    require_once $start_file;
//}
foreach(glob(__DIR__.'/Start3/start*.php') as $start_file)
{
    require_once $start_file;
}
//if( in_array('start',$argv) ) {
//    $queue_name = __DIR__.'/Applications/Api/Events/Queue.php';
//    $queue_pid = system("ps -ef | grep -v grep | grep {$queue_name} | awk '{print $2}'");
//    if($queue_pid > 0 ) {
//        system("kill $queue_pid");
//    }
//    system("nohup /usr/local/bin/php {$queue_name} >> /opt/log/wechat.log 2>&1 &");
//}

// 运行所有服务
Worker::runAll();

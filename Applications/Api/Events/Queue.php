<?php
/**
 * Created by PhpStorm.
 * User: fuliping
 * Date: 2017/12/28
 * Time: 17:04
 */
$exec_time = time();
$check_python = trim(exec("which python3"));

if($check_python == '/usr/local/bin/python3') {
    $is_python = true;
}else{
    $is_python = false;
}

while (true) {
    queue_reboot($exec_time);
    if($is_python) {
        thread_py();
    }else{
        WxNewsThread_php();
    }
    ExecWxSQL_php();
    sleep(2);
}

function queue_reboot($exec_time)
{
    $cha = time()-$exec_time;
    if( $cha >= 86400 ) {
        $queue_name = __DIR__.'/Queue.php';
        $queue_pid = system("ps -ef | grep -v grep | grep {$queue_name} | awk '{print $2}'");
        if($queue_pid > 0 ) {
            system("nohup /usr/local/bin/php {$queue_name} >> /opt/log/wechat.log 2>&1 &");
            system("kill $queue_pid");
        }
    }
}

function thread_py()
{
    $file = __DIR__.'/Thread.py';
    system("/usr/local/bin/python3 $file");
}

function ExecWxSQL_php()
{
    $file = __DIR__.'/ExecWxSQL.php';
    system("/usr/local/bin/php $file");
}

function WxNewsThread_php()
{
    $file = __DIR__.'/WxNewsThread.php';
    system("/usr/local/bin/php $file");
}

function push_log($Content)
{
    error_log( '['.date('m-d H:i:s').'] Queue_'.$Content . "\n", 3, '/opt/log/wechat.log');
}
<?php
/*
use Workerman\Worker;
use Workerman\WebServer;
use Core\ApiServer;

require_once __DIR__.'/../vendor/autoload.php';

$web = new ApiServer("http://0.0.0.0" . ADMIN_PORT);
// WebServer数量
$web->count = 2;
// 设置站点根目录
$app = 'Admin';
$apppath =  __DIR__.'/../Applications/'.$app;
$web->addRoot('localhost', $apppath);
$web->fictExtension = ADMINEXTENSION;
$web->name = $app;
require_once $apppath . '/routes.php';
include_once $apppath . '/Third/editer/Bootstrap.php';
// include_once (API_ROOT . 'Third/editer/Bootstrap.php');
if(is_cli() == false) {
    return;
}

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}
*/
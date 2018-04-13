<?php
/*
use Workerman\Worker;
use Workerman\WebServer;

require_once __DIR__.'/../vendor/autoload.php';

$web = new WebServer("http://0.0.0.0:5023");
// WebServer数量
$web->count = 2;
// 设置站点根目录
$app = 'Web';
$apppath =  __DIR__.'/../Applications/'.$app;
$web->addRoot('localhost', $apppath);
$web->name = $app;
// include_once (API_ROOT . 'Third/editer/Bootstrap.php');
if(is_cli() == false) {
    return;
}

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}*/

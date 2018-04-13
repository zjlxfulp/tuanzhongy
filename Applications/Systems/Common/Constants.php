<?php

defined('IS_CLI') or define('IS_CLI', PHP_SAPI == 'cli' ? true : false);
defined('UTF8_ENABLED') or define('UTF8_ENABLED', true);

//域名设置
defined('PROTOCOLS') or define("PROTOCOLS", 'http://');// 'https://'
defined('HOST_ONLINE') or define("HOST_ONLINE", 't.bigfacetech.com');
//端口设置
defined('FRONT_PORT') or define("FRONT_PORT", ':80');//前台端口
defined('ADMIN_PORT') or define("ADMIN_PORT", ':5021');//后台端口
defined('UPLOAD_PORT') or define("UPLOAD_PORT", ':5022');//上传目录端口

//定义前后端解析后缀名
defined('APIEXTENSION') or define("APIEXTENSION", 'front');
defined('ADMINEXTENSION') or define("ADMINEXTENSION", 'admin');

//项目目录设置
defined('APPSPATH') or define("APPSPATH", __DIR__ .'/../../');//Applications目录
defined('COMMON_PATH') or define("COMMON_PATH", APPSPATH .'/Systems/Common/');//Common目录
defined('TPLPATH') or define("TPLPATH", COMMON_PATH .'Template/');//模版目录
defined('APIPATH') or define("APIPATH", APPSPATH .'Api/');//Api目录
defined('APIVIEWS') or define("APIVIEWS", APIPATH .'html/');//生成html静态文件目录
defined('CACHES_PATH') or define("CACHES_PATH", __DIR__ .'/../../Systems/Caches');//缓存目录
defined('UPLOADPATH') or define("UPLOADPATH", __DIR__ .'/../../Uploads');//上传文件目录
defined('UPLOADPHOST') or define("UPLOADPHOST", PROTOCOLS . HOST_ONLINE . UPLOAD_PORT . '/');//上传文件域名地址



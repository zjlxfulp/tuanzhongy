<?php
namespace Core;

class Redis extends \Redis{

    /**
     * 静态成品变量 保存全局实例
     */
    private static  $_instance = array();

    /**
     * 静态工厂方法，返还此类的唯一实例
     */
    public static function getInstance($db = 'db',$select = 0) {
        $config = \Common\Config\Redis::config($db);
        $select = empty($select) ? $config['db'] : $select ;
        self::$_instance = new self();
        self::$_instance->pconnect($config['host'],$config['port']);
        self::$_instance->select($select);
        return self::$_instance;
    }

}

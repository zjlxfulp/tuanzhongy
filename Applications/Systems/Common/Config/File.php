<?php

namespace Api\Config;

class File
{
    public static $driver = 'file';
    public static $ftpServer = false;
    public static $storePath = CACHES_PATH . '/Api';
    public static $chcheTime = 3600;

    public static $read = array(
        'db1', 'db2'
    );

    public static $write = array(
        'db'
    );

    public static $db = array(
        'host'      => 'localhost',
        'port'      => '3306',
        'user'      => 'root',
        'password'  => 'root',
        'dbname'    => 'mytest',
        'charset'   => 'utf8'
    );

    public static $db1 = array(
        'host'      => 'localhost',
        'port'      => '3306',
        'user'      => 'root',
        'password'  => 'root',
        'dbname'    => 'mytest',
        'charset'   => 'utf8'
    );

    public static $db2 = array(
        'host'      => 'localhost',
        'port'      => '3306',
        'user'      => 'root',
        'password'  => 'root',
        'dbname'    => 'mytest',
        'charset'   => 'utf8'
    );

    public static function getConfig($db) {
        if(self::$$db) {
            return self::$$db;
        } else {
            return false;
        }
    }

    public static function getItem($item) {
        if(self::$$item) {
            return self::$$item;
        } else {
            return false;
        }
    }

}


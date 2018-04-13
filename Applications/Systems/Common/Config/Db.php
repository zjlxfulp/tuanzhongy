<?php

namespace Common\Config;

class Db
{
    public static $distributed = true;

    public static $read = array(
        'db1', 'db2'
    );

    public static $write = array(
        'db'
    );

    public static $db = array(
        'host'      =>  '139.198.5.59',
        'user' 		=>	'root',
        'password' 	=>	'F&tTQgLJkGpo',
        'port' 		=>	'3306',
        'dbname' 	=>	'tuanzy',
        'charset' 	=>	'utf8',
    );

    public static $db1 = array(
        'host'      =>  'localhost',
        'user' 		=>	'root',
        'password' 	=>	'F&tTQgLJkGpo',
        'port' 		=>	'3306',
        'dbname' 	=>	'weidan',
        'charset' 	=>	'utf8',
    );

    public static $db2 = array(
        'host'      =>  'localhost',
        'user' 		=>	'root',
        'password' 	=>	'F&tTQgLJkGpo',
        'port' 		=>	'3306',
        'dbname' 	=>	'weidan',
        'charset' 	=>	'utf8',
    );

    public static $dbForEditer = array(
        "type" => "Mysql",              // Database type: "Mysql", "Postgres", "Sqlserver", "Sqlite" or "Oracle"
        "user" => "root",               // Database user name
        "pass" => "F&tTQgLJkGpo",       // Database password
        "host" => "localhost",       // Database host
        "port" => "3306",               // Database connection port (can be left empty for default)
        "db"   => "weidan",             // Database name
        "dsn"  => "charset=utf8"        // PHP DSN extra information. Set as `charset=utf8` if you are using MySQL
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


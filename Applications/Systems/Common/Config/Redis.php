<?php
/**
 * Created by PhpStorm.
 * User: fuliping
 * Date: 2017/12/8
 * Time: 12:50
 */

namespace Common\Config;


class Redis
{
    public static $db1 = array(
        'host'      => '127.0.0.1',
        'port'      => 6379,
        'password'  => '',
        'db'        => 0
    );

    public static function config($db)
    {
        $data['db'] = self::$db1;
//        $data['db'] = self::$db1;
        return $data[$db];
    }
}
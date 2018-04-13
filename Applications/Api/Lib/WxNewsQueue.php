<?php
/**
 * Created by PhpStorm.
 * User: fuliping
 * Date: 2017/12/28
 * Time: 10:32
 */

namespace Api\Lib;


use Core\Redis;

class WxNewsQueue
{
    private static $queue_name = 'WechatNewsQueue';
    private static $lock_postfix = '_lock';
    private static $expire = 86400;

    public static function push_queue($key)
    {
        $redis = Redis::getInstance('db');
        $lock_key = $key.self::$lock_postfix;
        if( $redis->get($lock_key) ) {
            return false;
        }
        $redis->setex($lock_key,self::$expire,1);
        $redis->lPush(self::$queue_name,$key);
    }
}
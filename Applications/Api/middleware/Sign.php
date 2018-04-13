<?php
/**
 * Created by PhpStorm.
 * User: fuliping
 * Date: 2017/12/13
 * Time: 11:14
 */

namespace Api\middleware;


class Sign
{
    private static $a = 1;

    public static function handle()
    {
        if(self::$a == 1) {
            return '错误信息3';
        }

        return true;
    }
}
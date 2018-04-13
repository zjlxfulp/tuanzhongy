<?php
namespace Api\middleware;


class Auth{

    private static $a = 1;

    public static function handle()
    {
        if(self::$a != 1) {
            return '错误信息2';
        }

        return true;
    }

}
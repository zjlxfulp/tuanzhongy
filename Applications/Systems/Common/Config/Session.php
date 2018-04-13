<?php

namespace Common\Config;

class Session
{
    public static function getConf() {
        return array(
            'driver' => 'db',
            'cookie_lifetime' => 7200,
            'cookie_path' => ini_get('session.cookie_path'),
            'cookie_domain' => ini_get('session.cookie_domain'),
            'cookie_secure' => ini_get('session.cookie_secure'),
            'cookie_httponly' => ini_get('session.cookie_httponly')
        );
    }

}


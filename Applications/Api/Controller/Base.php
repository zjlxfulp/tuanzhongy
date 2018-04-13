<?php

namespace Api\Controller;
use Core\Input;
use Core\Db;
use Core\Redis;


class Base
{
    protected $input;
    protected $db;

    public function __construct() {
//        Http::sessionStart();
        $this->input = new Input();
        $this->db = Db::instance('db');
        $this->redis = Redis::getInstance('db');
    }

    protected function chkSession() {
        if(@ isset($_SESSION['authuser']) && !empty($_SESSION['authuser'])) {
            return json_decode($_SESSION['authuser'], true);
        } else {
            return false;
        }
    }

    public function test() {
//        Store
    }
}

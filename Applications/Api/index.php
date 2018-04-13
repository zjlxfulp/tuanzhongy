<?php
use Core\Rout;

$roter = new Rout();
$roter->appName = 'Api';
$roter->controllerDir = __DIR__ . '/Controller/';


$roter->on404 = function() {
    echo ("我的404");
};

try {

    $roter->_routes(\Core\Router::$static_route);

} catch (\Core\SException $e) {
    return ;
}
$roter->onClientMessage($connection);


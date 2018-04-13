<?php
namespace Core;

use Workerman\Worker;
use Workerman\WebServer;
use Workerman\Protocols\Http;


class ApiServer extends WebServer
{
    public $fictExtension = '';

    /**
     * Emit when http message coming.
     *
     * @return void
     */
    public function onMessage($connection)
    {
        // REQUEST_URI.
        $workerman_url_info = parse_url($_SERVER['REQUEST_URI']);
        if (!$workerman_url_info) {
            Http::header('HTTP/1.1 400 Bad Request');
            $connection->close('<h1>400 Bad Request</h1>');
            return;
        }
        $workerman_path = isset($workerman_url_info['path']) ? $workerman_url_info['path'] : '/';
        $workerman_path_info      = pathinfo($workerman_path);
        $workerman_file_extension = isset($workerman_path_info['extension']) ? $workerman_path_info['extension'] : '';
//		if($workerman_file_extension == $this->fictExtension) {
//            $_SERVER["REQUEST_URI"] = str_replace('/index.php' , '', $_SERVER["REQUEST_URI"]);
//			$_SERVER["REQUEST_URI"] = str_replace('.' . $this->fictExtension , '', $_SERVER["REQUEST_URI"]);
//            $workerman_path           = ($len = strlen($workerman_path)) && $workerman_path[$len - 1] === '/' ? 'index.php' : '/index.php';
//            $workerman_file_extension = 'php';
//		}

        $resource = array('html','js','css');
        $workerman_root_dir = isset($this->serverRoot[$_SERVER['SERVER_NAME']]) ? $this->serverRoot[$_SERVER['SERVER_NAME']] : current($this->serverRoot);
        if(  in_array($workerman_file_extension,$resource)  ) {
            $workerman_file  = "$workerman_root_dir/Views/{$workerman_path}";
        }else if ($workerman_file_extension == 'php' || $workerman_file_extension == 'txt') {
            $workerman_file = __DIR__."/../../Api/External/{$workerman_path}";
        }else{
            $workerman_file_extension = 'php';
            $workerman_file = "$workerman_root_dir/index.php";
        }

//        var_dump($workerman_file);

        // File exsits.
        if (is_file($workerman_file)) {
            // Security check.
            if ((!($workerman_request_realpath = realpath($workerman_file)) || !($workerman_root_dir_realpath = realpath($workerman_root_dir))) || 0 !== strpos($workerman_request_realpath,
                    $workerman_root_dir_realpath)
            ) {
                Http::header('HTTP/1.1 400 Bad Request');
                $connection->close('<h1>400 Bad Request</h1>');
                return;
            }

            $workerman_file = realpath($workerman_file);
            // Request php file.
            if ($workerman_file_extension === 'php') {
                $workerman_cwd = getcwd();

                chdir($workerman_root_dir);
                ini_set('display_errors', 'off');
                ob_start();
                // $_SERVER.
                // $_SERVER.
                $_SERVER['REMOTE_ADDR'] = $connection->getRemoteIp();
                $_SERVER['REMOTE_PORT'] = $connection->getRemotePort();
                include $workerman_file;
                $content = ob_get_clean();
//                var_dump($content);
                ini_set('display_errors', 'on');
                if (strtolower($_SERVER['HTTP_CONNECTION']) === "keep-alive") {
                    $connection->send($content);
                } else {
                    $connection->close($content);
                }
                chdir($workerman_cwd);
                return;
            }

            // Send file to client.
            return self::sendFile($connection, $workerman_file);
        } else {
            $workerman_file           = "$workerman_root_dir/404.html";
            if(is_file($workerman_file) && ($workerman_file_extension == 'html' || $workerman_file_extension == 'shtml')){
                return self::sendFile($connection, $workerman_file);
            }

            // 404
            Http::header("HTTP/1.1 404 Not Found");
            $connection->close('<html><head><title>404 File not found</title></head><body><center><h3>404 Not Found</h3></center></body></html>');
            return;
        }
    }

    public function run()
    {
        $this->reusePort = true;
        $this->onMessage = array($this, 'onMessage');
        parent::run();
    }

}
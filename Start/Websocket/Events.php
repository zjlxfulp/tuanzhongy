<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

/**
 * 聊天主逻辑
 * 主要是处理 onMessage onClose 
 */
use \GatewayWorker\Lib\Gateway;
require_once __DIR__.'/../../Applications/Systems/Core/Redis.php';
class Events
{

    public static function onConnect($client_id) {

    }

   /**
    * 有消息时
    * @param int $client_id
    * @param mixed $message
    */
   public static function onMessage($client_id, $message)
   {
       // debug
       $debug = "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id session:".json_encode($_SESSION)." onMessage:".$message."\n";
       echo $debug;

       // 客户端传递的是json数据
       $message_data = json_decode($message, true);
       if(!isset($message_data['temp_id']) || !isset($message_data['type'])) {
           $new_message['message'] = '缺少参数';
           $new_message['type'] = 'error';
           $new_message['time'] = time();
           Gateway::sendToCurrentClient(json_encode($new_message));
           return;
       }
       $redis = \Core\Redis::getInstance('db');
       $client_info = $redis->get($message_data['temp_id']);

       if(empty($client_info)) {
           $new_message['message'] = 'temp_id 不正确!';
           $new_message['type'] = 'error';
           $new_message['time'] = time();
           Gateway::sendToCurrentClient(json_encode($new_message));
           return;
       }

       if($message_data['type'] == 'login') {
           $client_info = json_decode($client_info,true);
           $client_info['client_id'] = $client_id;
           Gateway::sendToCurrentClient(json_encode($client_info));
           $redis->set($message_data['temp_id'],json_encode($client_info));
           return;
       }

       return;
   }
   
   /**
    * 当客户端断开连接时
    * @param integer $client_id 客户端id
    */
   public static function onClose($client_id)
   {

   }

    private static function push_log($Content)
    {
        file_put_contents('/opt/log/wechat.log', '['.date('m-d H:i:s').']'.$Content."\n",FILE_APPEND);
    }
}

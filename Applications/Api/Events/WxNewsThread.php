<?php
/**
 * Created by PhpStorm.
 * User: fuliping
 * Date: 2017/12/15
 * Time: 11:02
 */
$start_time = time();
$st_date = date('m-d H:i:s');
echo "WechatNewsphp start date {$st_date} ";

$db = array(
    'host'      =>  '139.198.5.59',
    'user' 		=>	'root',
    'password' 	=>	'F&tTQgLJkGpo',
    'port' 		=>	'3306',
    'dbname' 	=>	'tuanzy'
);

$redis_ip = '127.0.0.1';
$select = 0;
$queue_key = 'WechatNewsQueue';

$redis = new Redis();
$redis->connect($redis_ip,6379);
$redis->select($select);

//authorizer_access_token_wxe79525d85d0a5ef0
$wechat_info_key = $redis->lPop($queue_key);
$authorizer_access_token = $redis->get($wechat_info_key);

$authorizer_appid = substr($wechat_info_key,strrpos($wechat_info_key,'_')+1);
if( empty($wechat_info_key) || empty($authorizer_access_token) ) {
    push_log("WechatNewsQueue key or  appid : {$authorizer_appid} access_token is not exist");
    die;
}


$msyql = connect_mysql($db);
$wx_info = $msyql->query("select `id` from `wechat_public` where `wx_appid` = '{$authorizer_appid}' AND `is_deleted` = 0 ")->fetch_assoc();
if( !isset($wx_info['id']) ) {
    push_log(" appid {$authorizer_appid} is not exist");
    die;
}
$public_id = $wx_info['id'];
$news_result = get_material($authorizer_access_token,'news');

if($news_result) {
    $info_sql = '';
    $content_sql = '';

    foreach ($news_result as $key => $value) {
        $media = $value['media_id'];
        $create_time = $value['content']['create_time'];
        $update_time = $value['content']['update_time'];
        $ref_date = date('Y-m-d',$update_time);

        $int_page_read_count = getarticletotal($authorizer_access_token,$ref_date);

        foreach ($value['content']['news_item'] as $k => $v) {
            $media_id = $media.'_'.$k;
            $news_row = $msyql->query("select `id`,`update_time` from `wechat_news` where `media_id` = '{$media_id}' AND `is_deleted` = 0 limit 1")->fetch_assoc();
            if ( isset($news_row['id']) && $news_row['update_time'] == $update_time ) {
                push_log("public_id :{$public_id}  media_id :{$media_id} not update ");
                break;
            }
            if ( isset($news_row['id']) && $news_row['update_time'] != $update_time ) {
                $msyql->query("update `wechat_news` set `is_deleted` = 1 WHERE  `media_id` = '{$media_id}' ");
            }
            $v['title'] = addslashes($v['title']);
            $v['author'] = addslashes($v['author']);
            $v['content'] = addslashes($v['content']);
            $v['digest'] = addslashes($v['digest']);
            $info_sql .= "({$public_id},'{$media_id}','{$v['title']}','{$v['author']}','{$v['digest']}','{$v['content_source_url']}','{$v['thumb_media_id']}',{$v['show_cover_pic']},'{$v['url']}','{$v['thumb_url']}',{$v['need_open_comment']},{$v['only_fans_can_comment']},{$create_time},{$update_time},{$start_time}) ,";

            $content_sql .= "('{$media_id}','{$v['content']}') ,";

            if(isset($int_page_read_count[$v['title']])) {
                $read_count_sql = "update `wechat_news` set `int_page_read_count` = {$int_page_read_count[$v['title']]} WHERE `media_id` = '{$media_id}'";
                write_sql_log($authorizer_appid,$read_count_sql,false);
            }
        }
    }
    if($info_sql) {
        $info_sql = trim($info_sql,',');
        $insert_into_sql = "insert into `wechat_news` (`public_id`,`media_id`,`title`,`author`,`digest`,`content_source_url`,`thumb_media_id`,`show_cover_pic`,`url`,`thumb_url`,`need_open_comment`,`only_fans_can_comment`,`create_time`,`update_time`,`create_date`) values {$info_sql};";

        write_sql_log($authorizer_appid,$insert_into_sql);

        $content_sql = trim($content_sql,',');
        $insert_content_sql = "insert into `wechat_news_content` (`media_id`,`content`) VALUES {$content_sql};";

        write_sql_log($authorizer_appid,$insert_content_sql);
    }
}
$cha =  time()-$start_time;
//echo "WechatNewsphp Total Time {$cha} \n";

function write_sql_log($appid,$content,$is_insert = true)
{
    if($is_insert) {
        $file = __DIR__.'/../Migrate/'.$appid.'.sql';
    }else{
        $file = __DIR__.'/../Migrate/'.$appid.'_update.sql';
    }
    $read_file_lock = $file.'_lock';
    if( is_file($read_file_lock) ) {
        //有程序正在读就退出
        return false;
    }
    file_put_contents($file, $content.'__@@@__', FILE_APPEND);
}


function getarticletotal($authorizer_access_token,$ref_date)
{
    $url = 'https://api.weixin.qq.com/datacube/getarticletotal?access_token='.$authorizer_access_token;
    $data['begin_date'] = $ref_date;
    $data['end_date'] = $ref_date;
    $result = http($url,'post',$data);
    $total_info = array();
    if( !empty($result['list']) ) {
        foreach ($result['list'] as $key=>$value) {
            if(isset($value['details'])) {
                $title = $value['title'];
                $info = end($value['details']);
                if(isset($total_info[$title])) {
                    $total_info[$title] += $info['int_page_read_count'];
                }else{
                    $total_info[$title] = $info['int_page_read_count'];
                }
            }
        }
    }
    return $total_info;
}

function get_material_count($authorizer_access_token) {
    $url = 'https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token='.$authorizer_access_token;
    $result = http($url);
    return $result;
}

function get_material($authorizer_access_token,$type)
{
    $material_count = get_material_count($authorizer_access_token);
    $count_key = $type.'_count';
    $rs = array();
    if(isset($material_count[$count_key]) && $material_count[$count_key] > 0) {
        $total = $material_count[$count_key];
        $c_total = 20;
        $cha = $total/$c_total;
        $for_count = ceil($cha);
        for ($i=0; $i < $for_count; $i++) {
            $skip = $c_total*$i;
            $url = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token='.$authorizer_access_token;
            $data['type'] = $type;
            $data['offset'] = $skip;
            $data['count'] = $c_total;
            $result = http($url,'post',$data);
            if(!empty($result['item'])) {
                $rs = array_merge($rs,$result['item']);
            }
        }
    }
    return $rs;
}




function http($url,$method = 'get',$data = array())
{
//    push_log($url);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if($method == 'post' && !empty($data)) {
        $data_josn = json_encode($data,JSON_UNESCAPED_UNICODE);
        curl_setopt($ch, CURLOPT_POST, 1 );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_josn );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_josn))
        );
    }
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
    $response = curl_exec($ch);
//    push_log(json_encode($response));
    return json_decode($response,true);
}


function push_log($Content)
{
    error_log( '['.date('m-d H:i:s').'] WxNewsThread_'.$Content . "\n", 3, '/opt/log/wechat.log');
}

function connect_mysql($db1)
{
    $host = $db1['host'];
    $user = $db1['user'];
    $password = $db1['password'];
    $db = $db1['dbname'];
    $port = $db1['port'];
    $mysql = new mysqli($host,$user,$password,$db,$port);
    return $mysql;
}
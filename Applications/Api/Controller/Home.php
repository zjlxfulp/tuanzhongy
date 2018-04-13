<?php

namespace Api\Controller;
use Api\Config\wechat;
use Api\Lib\QingYunStor;
use Api\Lib\WxNewsQueue;
use chillerlan\QRCode\Output\QRImage;
use chillerlan\QRCode\Output\QRImageOptions;
use chillerlan\QRCode\QRCode;
use Core\Output;
use Core\Db;
use GatewayWorker\Lib\Gateway;

require_once __DIR__."/../Lib/demo/wxBizMsgCrypt.php";
class Home extends Base
{

    private static $AppID;
    private static $AppSecret;
    private static $ComponentAppID;
    private static $ComponentAppSecret;
    private static $encodingAesKey;
    private static $token;
    private static $cache_access_token = 'access_token';
    private static $cache_component_access_token = 'component_access_token';
    private static $cache_component_verify_ticket = 'component_verify_ticket';
    private static $cache_authorizer_access_token = 'authorizer_access_token';

    public function __construct() {
        parent::__construct();
        $this->mysql = Db::instance('db');
        self::$AppID = wechat::$APPID;
        self::$AppSecret = wechat::$AppSecret;
        self::$ComponentAppID = wechat::$ComponentAppID;
        self::$ComponentAppSecret = wechat::$ComponentAppSecret;
        self::$encodingAesKey = wechat::$ComponentEncodingAesKey;
        self::$token = wechat::$ComponentToken;
    }

    public function test()
    {
        $client_id = $this->input->get_post('client_id');
        $message = "123";
        Gateway::sendToClient($client_id,$message);
        $result = true;
        Output::json($result);
    }


    public function get_article_data()
    {
        $authorizer_appid = $this->input->get_post('app_id');
        $data = $this->getarticletotal($authorizer_appid,'2017-12-11');
        Output::json($data);
    }
    

    public function wx_public_list()
    {
        $uid = 3;
        $info = $this->db->query("select `id`,`wx_appid`,`nick_name`,`head_img`,`qrcode_url`,`verify_type` from `wechat_public` WHERE `id` in (select `public_id` from `wechat_relation` WHERE `uid` = '{$uid}' AND `is_deleted` = 0) ");
        foreach ($info as $key=>$value) {
            if($value['verify_type'] == -1) {   //没有认证
                $info[$key]['fans_total'] = 0;
            }else{
                $info[$key]['fans_total'] = $this->get_attention($value['wx_appid']);
            }
        }
        Output::json($info);
    }

    public function invite_wx_public()
    {
        $public_name = $this->input->get_post('public_name');
        if( empty($public_name) ) {

        }
        $create_time = time();
        $info = $this->db->query("insert into `wechat_invite` (`public_name`,`create_date`) VALUES ('{$public_name}',{$create_time})");
        Output::json($info);
    }


    public function wechat_login()
    {
        $auth_code = $this->input->get_post('auth_code');
        $temp_id = $this->input->get_post('temp_id');
        $login_info = $this->redis->get($temp_id);
        $result = array();
        if($login_info) {
            $rs = $this->snsapi_base($auth_code);
            if(isset($rs['openid'])) {
                $result = json_decode($rs,true);
                Gateway::sendToClient($login_info['client_id'],'ok');
            }
        }
        Output::json($result);
    }

    function wechat_login2()
    {
        $temp_id = $this->input->get_post('temp_id');
        $login_info = $this->redis->get($temp_id);
        $result['data'] = array();
        if($login_info) {
            $result['data'] = $login_info;
            Gateway::sendToClient($login_info['client_id'],'ok');
        }
        Output::json($result);
    }



    public function qrcode()
    {
        $url = 'http://192.168.99.231/oauth.html?temp_id=';
        $result = $this->create_qrcode($url);
        Output::json($result);
    }

    private function create_qrcode($url)
    {
        $temp_id = $this->create_temp_token();
        $url = $url.$temp_id;
        $outputOptions = new QRImageOptions();
        $outputOptions->bgBlue = 250;
        $outputOptions->bgGreen = 250;
        $outputOptions->bgRed = 250;
        $result['QRcode'] = (new QRCode($url, new QRImage($outputOptions)))->output();
        $result['temp_id'] = $temp_id;
        return $result;
    }

    private function create_temp_token()
    {
        $data['timestamp'] = microtime();
        $data['nonce'] = mt_rand(1000,9999);
        sort($data,SORT_STRING);
        $token = sha1(implode($data));
        $key = substr($token,0,10);
        $info['token'] = $token;
        $this->redis->set($key,json_encode($info));
        return $key;
    }

    private function snsapi_base($CODE)
    {
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=wx829ddb465b7916e4&secret=34525f5d71f54fbab01dd9de982ac9d1&code='.$CODE.'&grant_type=authorization_code';
        $result = $this->http($url);
        return $result;
//        $rs = false;
//        if(isset($result['openid'])) {
//            $info = $this->db->row("select `id` from `wechat_user` WHERE `openid` = '{$result['openid']}' AND `is_deleted` = 0 ");
//            $date = time();
//            if($info['id']) {
//                $rs = $this->db->query("update `wechat_user` set `update_date` = {$date} WHERE `id` = {$info['id']} ");
//            }else{
//                $rs = $this->db->query("insert into `wechat_user` (`openid`,`update_date`,`create_date`) VALUES ('{$result['openid']}',{$date},{$date})");
//            }
//            if($rs) {
//                $rs = true;
//            }
//        }
//        return $rs;
    }

    //群发-存在微信历史消息中 (认证,48003 不允许群发 48001没有认证)
    public function send_message()
    {
        $authorizer_appid = $this->input->get_post('appid');
        $thumb_media_id = $this->input->get_post('thumb_media_id');
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token='.$this->authorizer_access_token($authorizer_appid);
        $data['filter'] = array(
            'is_to_all'=>true,
            'tag_id'=>''
        );
        $data['mpnews']['media_id'] = $thumb_media_id;
        $data['msgtype'] = 'mpnews';
        $data['send_ignore_reprint'] = 0;
        $data['clientmsgid'] = md5(time());
        $result = $this->http($url,'post',$data);
        Output::json($result);
    }


    //上传永久素材
    public function upload_img()
    {
        $authorizer_appid = $this->input->get_post('appid');
        $url = $this->input->get_post('url');
        $local_img = $this->create_local_img($url);
        $result = $this->wx_upload_img($authorizer_appid,$local_img,'image');
        Output::json($result);
    }

    //添加图文素材
    public function add_material()
    {
        $authorizer_appid = $this->input->get_post('appid');
        $thumb_media_id = $this->input->get_post('thumb_media_id');
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_news?access_token='.$this->authorizer_access_token($authorizer_appid);
        $articles[] = array(
            'title'=>'开发测试2',
            'thumb_media_id'=>$thumb_media_id,
            'author'=>'',
            'digest'=>'',
            'show_cover_pic'=>1,
            'content'=>'图文消息的具体内容，支持HTML标签，必须少于2万字符',
            'content_source_url'=>''
        );
        $data['articles'] = $articles;

        $result = $this->http($url,'post',$data);
        Output::json($result);
    }

    //获取素材
    public function get_material()
    {
        $authorizer_appid = $this->input->get_post('appid');
        $type = $this->input->get_post('type');
        $url = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token='.$this->authorizer_access_token($authorizer_appid);
        $data['type'] = $type;
        $data['offset'] = 0;
        $data['count'] = 20;
        $result = $this->http($url,'post',$data);
        Output::json($result);
    }

    //获取图文群发总数据  (认证,48001没有认证)
    private function getarticletotal($authorizer_appid,$ref_date)
    {
        $url = 'https://api.weixin.qq.com/datacube/getarticletotal?access_token='.$this->authorizer_access_token($authorizer_appid);
        $data['begin_date'] = $ref_date;
        $data['end_date'] = $ref_date;
        $result = $this->http($url,'post',$data);
        return $result;
    }


    //获取粉丝  (认证,48001没有认证)
    private function get_attention($authorizer_appid)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$this->authorizer_access_token($authorizer_appid).'&next_openid=';
        $result = $this->http($url);
        return isset($result['total'])?$result['total']:0;
    }


    //获取粉丝基本信息  (认证,48001没有认证)
    public function get_attention_info()
    {
        $authorizer_appid = $this->input->get_post('appid');
        $OPENID = $this->input->get_post('OPENID');
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->authorizer_access_token($authorizer_appid).'&openid='.$OPENID.'&lang=zh_CN';
        $result = $this->http($url);
        Output::json($result);
    }


    //根据OpenID列表群发 (认证,先不用)
    public function send_message_openid()
    {
        $authorizer_appid = $this->input->get_post('appid');
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token='.$this->authorizer_access_token($authorizer_appid);
        $data['touser'] = array(
            'otlW_1Ul5aV8meZaeF6sxpjxwa54',
            'otlW_1YmWoHEKTnrm5yMyuyPQW3Q',
            'otlW_1cbP1BEB4uW3Nii6r4TTD2g',
            'otlW_1cfhseDDIoay4BHiHSRaLro',
            'otlW_1V8SwE4qCCdyd1zGk6QF9Dk'
        );
        $data['mpnews']['media_id'] = 'dqusVAngDvufWx22rklC40IqD0WhyNCbBG3Adjydun0';
        $data['msgtype'] = 'mpnews';
        $data['send_ignore_reprint'] = 0;
        $data['clientmsgid'] = md5(time());
        $result = $this->http($url,'post',$data);
        Output::json($result);
    }


    private function authorizer_wechat_info($authorizer_appid)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token='.$this->component_access_token();
        $data['component_appid'] = self::$ComponentAppID;
        $data['authorizer_appid'] = $authorizer_appid;
        $result = $this->http($url,'post',$data);
        return $result['authorizer_info'];
    }

    private function authorizer_access_token($authorizer_appid,$access_token = '')
    {
        $auth_access_token = self::$cache_authorizer_access_token.'_'.$authorizer_appid;
        if($access_token != '') {
            $this->redis->setex($auth_access_token,7100,$access_token);
        }else{
            $access_token = $this->redis->get($auth_access_token);
            if(empty($access_token)) {
                $refresh_token = $this->db->row("select `refresh_token` from `wechat_public` WHERE `wx_appid` = '{$authorizer_appid}' AND `is_deleted` = 0 ");
                if($refresh_token['refresh_token']) {
                    $access_token = $this->refresh_wechat_token($authorizer_appid,$refresh_token['refresh_token']);
                    $this->redis->setex($auth_access_token,7100,$access_token);
                }
            }
        }
        WxNewsQueue::push_queue($auth_access_token);
        return $access_token;
    }

    private function refresh_wechat_token($authorizer_appid,$refresh_token)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token='.$this->component_access_token();
        $data['component_appid'] = self::$ComponentAppID;
        $data['authorizer_appid'] = $authorizer_appid;
        $data['authorizer_refresh_token'] = $refresh_token;
        $result = $this->http($url,'post',$data);
        return $result['authorizer_access_token'];
    }

    public function authorization()
    {
        $authorization_code = $this->input->get_post('code');
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token='.$this->component_access_token();
        $data['component_appid'] = self::$ComponentAppID;
        $data['authorization_code'] = $authorization_code;
        $result = $this->http($url,'post',$data);
        $rs = false;
        if(isset($result['authorization_info'])) {
            $authorizer_appid = $result['authorization_info']['authorizer_appid'];
            $authorizer_access_token = $result['authorization_info']['authorizer_access_token'];
            $authorizer_refresh_token = $result['authorization_info']['authorizer_refresh_token'];
            $this->authorizer_access_token($authorizer_appid,$authorizer_access_token);
            $date = time();
            $wechat_info = $this->db->row("select `id` from `wechat_public` WHERE `wx_appid` = '{$authorizer_appid}' AND `is_deleted` = 0 ");
            $info = $this->authorizer_wechat_info($authorizer_appid);
            $info['qrcode_url'] = $this->download_wx_img($info['qrcode_url']);
            $info['head_img'] = $this->download_wx_img($info['head_img']);
            if(isset($wechat_info['id'])) {
                $rs = $this->db->query("update `wechat_public` set `refresh_token`= '{$authorizer_refresh_token}',`gh_id`= '{$info['user_name']}',`nick_name`= '{$info['nick_name']}',`head_img`= '{$info['head_img']}',`qrcode_url`= '{$info['qrcode_url']}',`service_type`= '{$info['service_type_info']['id']}',`verify_type`= '{$info['verify_type_info']['id']}',`principal_name`= '{$info['principal_name']}',`update_date`={$date} WHERE `wx_appid` = '{$authorizer_appid}' ");
            }else{
                $rs = $this->db->query("insert into `wechat_public` (`wx_appid`,`refresh_token`,`gh_id`,`nick_name`,`head_img`,`qrcode_url`,`service_type`,`verify_type`,`principal_name`,`create_date`,`update_date`) VALUES ('{$authorizer_appid}','{$authorizer_refresh_token}','{$info['user_name']}','{$info['nick_name']}','{$info['head_img']}','{$info['qrcode_url']}','{$info['service_type_info']['id']}','{$info['verify_type_info']['id']}','{$info['principal_name']}',{$date},{$date})");
            }
            if($rs) {
                //wechat_relation 新增关联记录


                $rs = true;
            }
        }
        Output::json($rs);
    }

    public function get_auth_code()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token='.$this->component_access_token();
        $data['component_appid'] = self::$ComponentAppID;
        $result = $this->http($url,'post',$data);
        Output::json($result);
    }

    public function event()
    {
        $timeStamp  = empty($this->input->get_post('timestamp'))     ? ""    : trim($this->input->get_post('timestamp')) ;
        $nonce      = empty($this->input->get_post('nonce'))     ? ""    : trim($this->input->get_post('nonce')) ;
        $msg_sign   = empty($this->input->get_post('msg_signature')) ? ""    : trim($this->input->get_post('msg_signature')) ;
        $encryptMsg = $GLOBALS['HTTP_RAW_POST_DATA'];
        $pc = new \WXBizMsgCrypt(self::$token, self::$encodingAesKey, self::$ComponentAppID);
        $xml_tree = new \DOMDocument();
        $xml_tree->loadXML($encryptMsg);
        $array_e = $xml_tree->getElementsByTagName('Encrypt');
        $encrypt = $array_e->item(0)->nodeValue;
        $format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
        $from_xml = sprintf($format, $encrypt);
        // 第三方收到公众号平台发送的消息
        $msg = '';
        $errCode = $pc->decryptMsg($msg_sign, $timeStamp, $nonce, $from_xml, $msg);
        $this->push_log($msg);
        if ($errCode == 0) {
            $xml = new \DOMDocument();
            $xml->loadXML($msg);
            $array_e = $xml->getElementsByTagName('ComponentVerifyTicket');
            $array_i = $xml->getElementsByTagName('InfoType');
            $array_appid = $xml->getElementsByTagName('AuthorizerAppid');
            $component_verify_ticket = $array_e->item(0)->nodeValue;
            $InfoType = $array_i->item(0)->nodeValue;
            $AuthorizerAppid = $array_appid->item(0)->nodeValue;
            //取消授权的通知
            if($InfoType == 'unauthorized' && !empty($AuthorizerAppid)) {

            }

            //ticket的通知
            if(!empty($component_verify_ticket)) {
                $this->redis->set(self::$cache_component_verify_ticket,$component_verify_ticket);
                $this->push_log($component_verify_ticket);
            }
        } else {
            $content = '解密后失败：'.$errCode;
            $this->push_log($content);
        }
        echo 'success';
    }

    private function component_access_token()
    {
        $component_access_token = $this->redis->get(self::$cache_component_access_token);
        if(empty($component_access_token)) {
            $url = "https://api.weixin.qq.com/cgi-bin/component/api_component_token";
            $data['component_appid'] = self::$ComponentAppID;
            $data['component_appsecret'] = self::$ComponentAppSecret;
            $data['component_verify_ticket'] = $this->redis->get(self::$cache_component_verify_ticket);
            $result = $this->http($url,'post',$data);
            if(empty($result['component_access_token'])) {
                return false;
            }
            $component_access_token = $result['component_access_token'];
            $this->redis->setex(self::$cache_component_access_token,7150,$component_access_token);
        }
        return $component_access_token;
    }

    private function push_log($Content)
    {
        file_put_contents('/opt/log/wechat.log', '['.date('m-d H:i:s').']'.$Content."\n",FILE_APPEND);
    }

    private function http($url,$method = 'get',$data = array())
    {
//        $this->push_log($url);
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
//        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//        var_dump($response);
//        $this->push_log(json_encode($response));
        return json_decode($response,true);
    }

    //composer require yunify/qingstor-sdk
    private function download_wx_img($url)
    {
        $ch = curl_init();
        $httpheader = array(
            'Host' => 'mmbiz.qpic.cn',
            'Connection' => 'keep-alive',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'no-cache',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,/;q=0.8',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.89 Safari/537.36',
            'Accept-Encoding' => 'gzip, deflate, sdch',
            'Accept-Language' => 'zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4'
        );
        $options = array(
            CURLOPT_HTTPHEADER => $httpheader,
            CURLOPT_URL => $url,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array( $ch , $options );
        $result = curl_exec( $ch );
        curl_close($ch);
        $QingYunStor = new QingYunStor();
        $url = $QingYunStor->base64_upload('.jpg',$result,$dir = 'file');
        return $url;
    }

    private function wx_upload_img($authorizer_appid,$url2,$type = 'image')
    {
        $access_token = $this->authorizer_access_token($authorizer_appid);
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='.$access_token.'&type='.$type;
        $commd = 'curl -F media=@'.$url2.' "'.$url.'"';
        exec($commd,$rseult,$status);
        if($status == 0) {
            return json_decode($rseult[0],true);
        }
        return false;
    }

    //生成本地图片
    private function create_local_img($img)
    {
        $result = false;
        $ch = curl_init();
        $httpheader = array(
            'Host' => 'mmbiz.qpic.cn',
            'Connection' => 'keep-alive',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'no-cache',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,/;q=0.8',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.89 Safari/537.36',
            'Accept-Encoding' => 'gzip, deflate, sdch',
            'Accept-Language' => 'zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4'
        );
        $options = array(
            CURLOPT_HTTPHEADER => $httpheader,
            CURLOPT_URL => $img,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array( $ch , $options );
        $text = curl_exec( $ch );
        curl_close($ch);
        $img_name = md5(time());
        $file = __DIR__.'/../upload/'.$img_name.'.jpg';
        $myfile = fopen($file, "w");
        if($myfile) {
            $rs = fwrite($myfile, $text);
            fclose($myfile);
            if($rs) {
                $result = $file;
            }
        }
        return $result;
    }

}
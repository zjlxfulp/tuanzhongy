<?php

namespace Core;
use Common\Config\App;
use Workerman\Protocols\Http;
use Exception;

//自定义输出协议
class Output {

	//json格式输出
	public static function json($result = '', $code = 0, $msg = '') {
//        Http::header('Content-type: application/json;charset=utf-8');
        Http::header('Access-Control-Allow-Credentials:true');
//        Http::header("Content-type: application/json");
//        Http::header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '';
        $allow_origin = App::allow_origin();
        if(in_array($origin, $allow_origin)){
            Http::header('Access-Control-Allow-Origin:'.$origin);
        }

        $arr['code'] = $code;
        $arr['data'] = $result;
        $arr['msg'] = $msg;

        throw new SException(json_encode($arr),$code);
	}
	
	//html输出
	public static function html($html, $code = 0) {
        echo $html;
	}
	
	//xml输出
	public static function xml($array, $code = 0) {
        $xml = self::arrayToXml($array);
        throw new SException(($xml));
	}
	
	//其他输出
	public static function others() {
		
	}
	
	
	//json格式输出
	private static function jsonp($result) {
		Http::header("Content-Type:application/javascript; charset=utf-8");
		Http::header('Access-Control-Allow-Origin:*');
		Http::header('Access-Control-Allow-Methods", "POST, GET, OPTIONS,PUT');
		echo $_GET['jsoncallback']."(".$result.")";
	}
	
	//原样输出
	public static function original($res) {
		if(self::isjsonp()) {
			self::jsonp($res);
			return;
		}
		if(is_array($res)) {
			print_r($res);return;
		}
		if(is_string($res)) {
			echo $res;return;
		}
		//...
	}
	//...

    public static function isjsonp() {
	    if(isset($_GET['jsoncallback'])) {
	        return true;
        }
        return false;
    }


    public static function arrayToXml($arr, $dom=0, $item=0) {
        if (!$dom){
            $dom = new \DOMDocument("1.0");
        }
        if(!$item){
            $item = $dom->createElement("root");
            $dom->appendChild($item);
        }
        foreach ($arr as $key=>$val){
            $itemx = $dom->createElement(is_string($key)?$key:"item");
            $item->appendChild($itemx);
            if (!is_array($val)){
                $text = $dom->createTextNode($val);
                $itemx->appendChild($text);

            }else {
                self::arrayToXml($val,$dom,$itemx);
            }
        }
        return $dom->saveXML();
    }

    public static function xmlToArray($xml){
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring),true);
        return $val;
    }
}
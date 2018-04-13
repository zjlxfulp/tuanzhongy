<?php
/**
 */
namespace Core;
/**
 *  康凯斯 Hex 解析器
 */
/**
 * byte数组与字符串转化类
 */
class Byte {
    /**
     * 转换一个String字符串为byte数组
     * @param $str 需要转换的字符串
     * @param $bytes 目标byte数组
     * @author Zikie
     */
    public static function getBytes($string) {
        $bytes = array();
        for($i = 0; $i < strlen($string); $i++){
            $bytes[] = ord($string[$i]);
        }
        return $bytes;
    }

    public static function strToArray($string) {
        $bytes = array();
        while ($string) {
            $ch = substr($string, 0, 2);
            $string = substr($string, 2);
            $bytes[] = $ch;
        }
        return $bytes;
    }

    public static function strToArray1($string) {
        $bytes = array();
        while ($string) {
            $ch = substr($string, 0, 1);
            $string = substr($string, 1);
            $bytes[] = $ch;
        }
        return $bytes;
    }
	
	/**
     * 转换一个String字符串为ASCII十六进制字符串
     */
	public static function getASCII($string) {
        $str = '';

        while ($string) {
            $ch = substr($string, 0, 1);
            $string = substr($string, 1);
            $str .= sprintf("%02x", ord($ch));
        }
        return $str;
    }
	
	/**
     * 转换一个ASCII十六进制字符串为String字符串
     */
    public static function ascii2String($string) {
        $str = "";
        while($s = substr($string, 0, 2)) {
            $str .= chr(hexdec($s));
            $string = substr($string, 2);
        }

        $arr = json_decode($str,true);

        return $arr;
    }
	
    /**
     * 将字节数组转化为String类型的数据
     * @param $bytes 字节数组
     * @param $str 目标字符串
     * @return 一个String类型的数据
     */
    public static function toStr($bytes) {
        $str = '';
        foreach($bytes as $ch) {
            $str .= chr($ch);
        }
        return $str;
    }
    /**
     * 转换一个int为byte数组
     * @param $byt 目标byte数组
     * @param $val 需要转换的字符串
     *
     */
    public static function integerToBytes($val) {
        $byt = array();
        $byt[0] = ($val & 0xff);
        $byt[1] = ($val >> 8 & 0xff);
        $byt[2] = ($val >> 16 & 0xff);
        $byt[3] = ($val >> 24 & 0xff);
        return $byt;
    }
    /**
     * 从字节数组中指定的位置读取一个Integer类型的数据
     * @param $bytes 字节数组
     * @param $position 指定的开始位置
     * @return 一个Integer类型的数据
     */
    public static function bytesToInteger($bytes, $position) {
        $val = 0;
        $val = $bytes[$position + 3] & 0xff;
        $val <<= 8;
        $val |= $bytes[$position + 2] & 0xff;
        $val <<= 8;
        $val |= $bytes[$position + 1] & 0xff;
        $val <<= 8;
        $val |= $bytes[$position] & 0xff;
        return $val;
    }
    /**
     * 转换一个shor字符串为byte数组
     * @param $byt 目标byte数组
     * @param $val 需要转换的字符串
     *
     */
    public static function shortToBytes($val) {
        $byt = array();
        $byt[0] = ($val & 0xff);
        $byt[1] = ($val >> 8 & 0xff);
        return $byt;
    }
    /**
     * 从字节数组中指定的位置读取一个Short类型的数据。
     * @param $bytes 字节数组
     * @param $position 指定的开始位置
     * @return 一个Short类型的数据
     */
    public static function bytesToShort($bytes, $position) {
        $val = 0;
        $val = $bytes[$position + 1] & 0xFF;
        $val = $val << 8;
        $val |= $bytes[$position] & 0xFF;
        return $val;
    }
}
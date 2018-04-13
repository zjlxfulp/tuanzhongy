<?php
/**
 * Created by PhpStorm.
 * User: fuliping
 * Date: 2017/12/8
 * Time: 18:03
 */
namespace Api\Lib;
use QingStor\SDK\Service\QingStor;
use QingStor\SDK\Config;

class QingYunStor
{

    private $QingStor;
    private $StorUrl = 'https://sh1a.qingstor.com/xiaoxiao/img';
    private $StorBucket = 'xiaoxiao/img';

    public function __construct()
    {
        $this->QingStor = new QingStor(
            new Config(
                'EVXBTNLJKJISCZPGNCFM',
                'QrM2kLPkg82uv0uLDPTwFd8NrvzqzTum1dFgIyD3'
            )
        );
    }

    /*
     * 用于base64上传
     * */
    public function base64_upload($extension,$content,$dir = 'file')
    {
        $file_name =  substr(md5(microtime()),0,15).$extension;
        $dir .= '/'.date('Y-m-d');
        $bucket = $this->QingStor->Bucket($this->StorBucket.$dir, 'sh1a');
        $bucket->putObject(
            $file_name,
            ['body' => $content]
        );
        $img_url = $this->StorUrl.$dir.'/'.$file_name;

        return $img_url;
    }


    public function upload($file,$dir = 'file')
    {
        $extension = substr($file['file_name'], strrpos($file['file_name'], '.'));
        $file_name =  substr(md5(microtime()),0,15).$extension;
        $dir .= '/'.date('Y-m-d');
        $bucket = $this->QingStor->Bucket($this->StorBucket.$dir, 'sh1a');
        $bucket->putObject(
            $file_name,
            ['body' => $file['file_data']]
        );
        $img_url = $this->StorUrl.$dir.'/'.$file_name;

        return $img_url;
    }

}
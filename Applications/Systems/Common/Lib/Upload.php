<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/8
 * Time: 16:10
 */
namespace Common\Lib;

use Core\SException;
use Common\Model\Table;

class Upload
{

    const NOT_ALLOWD_TYPE = '格式不符';
    const BIGGER_THAN_MAX_SIZE = '文件不能超过';
    const IS_EXIST = '文件已经存在';

    public $ext;
    public $size;
    public $dir;
    public $fileName;
    public $md5;
    public $type;
    public static $fileType = array(
        'avi', 'mpeg-1', 'mpeg-2', 'mpeg4',
        'dat', 'vob', 'asf', 'mp4', 'mp3',
        'rmvb', 'rm', 'wmv', 'mov', 'png',
        'aac', 'wav', 'wma', 'aac+','jpg',
        'jpeg',
    );

    public static $maxSize = 209715200;
    public static $filePath = UPLOADPATH;
    public static $fileHost = UPLOADPHOST;
    public static $fileDirForm = 'Y/m/d';
//    public static $fileNameForm = 'H-i-s';


    public static function inst() {
        return new self();
    }
    /**
     *
     */
    public function uploadImg($uid, $type) {
        if(!empty($_FILES)) {
            $this->type = $type;
            $this->checkExt();
            $this->checkSize();
            $this->checkDir();
            $this->checkFileName();
            return $this->createFile($uid);
        }
        return false;
    }

    private function checkExt() {
        $this->ext = substr(strrchr($_FILES[0]['file_name'],"."),1);

        if(!in_array($this->ext, self::$fileType)) {
            throw new SException(self::NOT_ALLOWD_TYPE);
        }
    }

    private function checkSize() {
        $this->size = $_FILES[0]['file_size'];

        if($this->size > self::$maxSize) {
            throw new SException(self::BIGGER_THAN_MAX_SIZE . self::$maxSize);
        }
    }

    private function checkDir() {
        $this->dir = $this->type . '/' . date(self::$fileDirForm) . '/';
        if(!is_dir(self::$filePath . '/' .$this->dir)) {
            mkdir(self::$filePath . '/' .$this->dir,0777,true);
        }
    }

    private function checkFileName() {
        $this->fileName = md5(time() . rand(1000,9999)) . '.' . $this->ext;
    }

    private function createFile($uid) {
        $file_path = self::$filePath . '/' . $this->dir . $this->fileName;
        $handle = fopen($file_path, 'w');
        fwrite($handle, $_FILES[0]['file_data']);
        fclose($handle);
        $this->md5 = md5_file($file_path);
        $res = Table::inst('files')->isExist($this->md5);
        if(!empty($res)) {
            unlink($file_path);
            $instData['url'] = $res['url'];
            $instData['sys_path'] = '/' . $res['sys_path'];
        } else {
            $instData['url'] = self::$fileHost . $this->dir . $this->fileName;
            $instData['sys_path'] = '/' . $this->dir . $this->fileName;
        }

        $instData['type'] = $this->type;
        $instData['md5'] = $this->md5;
        $instData['uid'] = $uid;
        $instData['file_name'] = $_FILES[0]['file_name'];
        $instData['ext'] = $this->ext;
        $instData['size'] = $this->size;
        $instData['addtime'] = time();
        $instData['id'] = Table::inst('files')->insertDb($instData);
        $instData['sys_path'] = self::$filePath . $instData['sys_path'];
        return $instData;
    }
}
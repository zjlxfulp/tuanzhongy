<?php

namespace Core;

class Log {
    //
	public static function fileLog($name, $info, $str = ''){

		//file_put_contents('/opt/log/'.$name.".log",date('y-m-d h:i:s')."_" . $str . "_".json_encode($info)."\n\n",FILE_APPEND);

		file_put_contents('/opt/log/'.$name.".log",date('y-m-d H:i:s')."_" . $str . "_".json_encode($info)."\n\n",FILE_APPEND);
	}

	//专门用以输入输出文件日志
    public static function fileLogForInputAndOutput($input, $output){
        file_put_contents('/opt/log/INPUT_OUTPUT.log', date('y-m-d H:i:s')."\n" ."__INPUT :". json_encode($input) ."\n" . "__OUTPUT:". json_encode($output) ."\n\n",FILE_APPEND);
    }

    public static function cacheLog($name, $info){

    }

    public static function dbLog($name, $info){
        Db::instance('db1')
            ->insert('db_log')
            ->cols(
                array(
                    'title' => $name,
                    'json' => json_encode($info),
                    'addtime' => time(),
                )
            )
            ->query();
    }
}
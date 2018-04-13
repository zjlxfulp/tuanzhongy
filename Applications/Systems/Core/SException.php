<?php

namespace Core;

use Exception;

/**
 * 自定义异常处理类
 */
class SException extends Exception {
    public function __construct($message, $code = 500, $char = '') {
        if($code != 0) {
            $message = json_encode(
                array(
                    'code' => $code,
                    'data' => $message,
                    'msg' => $char,
                )
            );
        }

        parent::__construct($message, $code);  
    }  
  
    public function __toString() {
        return strval($this->message);
    }
}  
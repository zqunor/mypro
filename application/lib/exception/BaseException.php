<?php

namespace app\lib\exception;

use think\Exception;

/**
 * 统一定义描述错误
 * @code param 状态码
 * @msg param 错误提示信息
 * @errorCode 自定义错误码
 */
class BaseException extends Exception
{
    // HTTP请求状态码
    public $code = 400;

    // 错误提示信息
    public $msg = '参数错误';

    // 自定义的错误码
    public $errorCode = 10000;

    public function __construct($params = [])
    {
        if(!is_array($params)) {
            return ;
        }

        if(array_key_exists('msg', $params)){
            $this->msg = $params['msg'];
        }
        if(array_key_exists('errorCode', $params)){
            $this->errorCode = $params['errorCode'];
        }
        if(array_key_exists('code', $params)){
            $this->code = $params['code'];
        }
    }
}

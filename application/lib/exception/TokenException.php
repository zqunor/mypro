<?php
/**
 * User: zhouqun
 * Time: 2018/7/20 20:32
 */

namespace app\lib\exception;

class TokenException extends BaseException
{
    public $code = 401;
    public $msg = 'Token已过期或无效Token';
    public $errorCode = 10001;
}
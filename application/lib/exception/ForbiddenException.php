<?php
/**
 * User: zhouqun
 * Time: 2018/7/20 20:32
 */

namespace app\lib\exception;

class ForbiddenException extends BaseException
{
    public $code = 403;
    public $msg = '权限不够';
    public $errorCode = 10002;
}
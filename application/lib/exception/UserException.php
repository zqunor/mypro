<?php
/**
 * User: zhouqun
 * Time: 2018/7/17 20:38
 */

namespace app\lib\exception;

class UserException extends BaseException
{
    public $code = 404;
    public $msg = '用户不存在';
    public $errorCode = 60000;
}
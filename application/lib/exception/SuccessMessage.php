<?php
/**
 * User: zhouqun
 * Time: 2018/8/6 10:54
 */

namespace app\lib\exception;

class SuccessMessage extends BaseException
{
    public $code = 201;
    public $msg = 'ok';
    public $errorCode = 0;
}
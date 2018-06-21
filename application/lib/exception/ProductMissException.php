<?php

namespace app\lib\Exception;

class ProductMissException extends BaseException
{
    public $code = '404';
    public $msg = '请求的product不存在';
    public $errorCode = 20000;

}
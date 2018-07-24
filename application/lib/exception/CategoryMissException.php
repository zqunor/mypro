<?php

namespace app\lib\Exception;

class CategoryMissException extends BaseException
{
    public $code = '404';
    public $msg = '请求的category不存在';
    public $errorCode = 50000;

}
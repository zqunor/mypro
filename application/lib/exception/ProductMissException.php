<?php

namespace app\lib\Exception;

class ProductMissException extends BaseException
{
    public $code = '404';
    public $msg = '当前查询无分类';
    public $errorCode = 20000;

}
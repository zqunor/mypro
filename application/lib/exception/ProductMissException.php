<?php

namespace app\lib\Exception;

class ProductMissException extends BaseException
{
    public $code = '404';
    public $msg = '当前分类无商品';
    public $errorCode = 20000;

}
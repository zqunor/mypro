<?php
namespace app\lib\exception;

class BannerMissException extends BaseException 
{
    /**
     * 覆盖父类的相应属性
     */
    public $code = 404;
    public $msg = '请求的Banner不存在';
    public $errorCode = 40000;
}
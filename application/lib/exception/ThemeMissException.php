<?php
namespace app\lib\exception;

class ThemeMissException extends BaseException
{
    /**
     * 覆盖父类的相应属性
     */
    public $code = 404;
    public $msg = '请求的主题不存在';
    public $errorCode = 30000;
}
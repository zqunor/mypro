<?php
/**
 * User: zhouqun
 * Time: 2018/7/17 20:38
 */

namespace app\lib\exception;

class WechatException extends BaseException
{
    public $code = 404;
    public $msg = '微信服务器接口调用失败';
    public $errorCode = 999;
}
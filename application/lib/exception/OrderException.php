<?php
/*
 * @Author: zhouqun
 * @Date: 2018-08-08 16:20:24
 * @Last Modified by: zhouqun
 * @Last Modified time: 2018-08-08 16:23:10
 */

namespace app\lib\exception;

class OrderException extends BaseException
{
    public $code = 404;
    public $msg = '订单不存在';
    public $errorCode = 80000;
}

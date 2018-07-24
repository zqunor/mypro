<?php
/**
 * User: zhouqun
 * Time: 2018/7/19 21:49
 */

namespace app\api\service;


class Token
{
    public static function generateToken()
    {
        // 用三组字符串，进行md5加密 [加强安全性]
        // 1.32个字符组成一组随机字符串
        $randChars = getRandChar(32);
        // 2.时间戳
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        // 3.盐
        $salt = config('secure.token_salt');

        return md5($randChars.$timestamp.$salt);
    }
}
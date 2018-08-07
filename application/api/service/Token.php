<?php
/**
 * User: zhouqun
 * Time: 2018/7/19 21:49.
 */

namespace app\api\service;

use app\lib\enum\ScopeEnum;
use app\lib\exception\ForbiddenException;
use app\lib\exception\TokenException;
use think\Cache;
use think\Exception;
use think\Request;

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

    public static function getCurrentTokenVar($key)
    {
        $token = Request::instance()->header('token');
        $vars = Cache::get($token);
        if (!$vars) {
            throw new TokenException();
        } else {
            if (!is_array($vars)) {
                $vars = json_decode($vars, true);
            }

            if (isset($vars[$key])) {
                return $vars[$key];
            } else {
                throw new Exception('尝试获取的Token变量不存在');
            }
        }
    }

    public static function getCurrentUid()
    {
        $uid = self::getCurrentTokenVar('uid');

        return $uid;
    }

    // 需要用户和CMS管理员都可以访问的权限
    public static function needPrimaryScope()
    {
        $scope = self::getCurrentTokenVar('scope');
        if (!$scope) {
            throw new TokenException();
        }
        if ($scope < ScopeEnum::User) {
            throw new ForbiddenException();
        }
    }

    // 只有用户可以访问的权限
    public static function needExclusiveScope()
    {
        $scope = self::getCurrentTokenVar('scope');
        if (!$scope) {
            throw new TokenException();
        }
        if ($scope != ScopeEnum::User) {
            throw new ForbiddenException();
        }
    }
}

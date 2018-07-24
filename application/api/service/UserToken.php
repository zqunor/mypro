<?php

namespace app\api\service;

use app\lib\exception\WechatException;
use app\lib\exception\TokenException;

class UserToken extends Token
{
    protected $code;
    protected $appid;
    protected $appSecret;
    protected $loginUrl;

    public function __construct($code)
    {
        $this->code      = $code;
        $this->appid     = config('wx.app_id');
        $this->appSecret = config('wx.app_secret');
        $this->loginUrl  = sprintf(
            config('wx.login_url'),
            $this->appid, $this->appSecret, $this->code
        );
    }

    public function get()
    {
        $result   = curl_get($this->loginUrl);
        $wxResult = json_decode($result, true);

        if (empty($wxResult)) {
            throw new \Exception('获取session_key及openID异常，微信内部错误');
        } else {
            $loginFail = isset($wxResult['errcode']);
            if ($loginFail) {
                $this->processLoginErr($wxResult);
            } else {
                return $this->grantToken($wxResult);
            }
        }
    }

    private function processLoginErr($wxResult)
    {
        throw new WechatException(
            [
                'msg'       => $wxResult['errmsg'],
                'errorCode' => $wxResult['errcode'],
            ]
        );
    }

    private function grantToken($wxResult)
    {
        $now = time();
        // 1.拿到openid
        $openid     = $wxResult['openid'];
        $sessionKey = $wxResult['session_key'];

        // 2.查看数据库中该openid的记录是否已经存在[同一个用户的openid始终保持不变]
        $user = model('user')->getByOpenId($openid);

        // 3.如果存在，则不处理； 如果不存在，那么新增一条user记录
        if ($user) {
            $uid = $user->id;
        } else {
            $uid = $this->newUser($openid);
        }

        // 4.生成令牌，准备缓存数据，写入缓存 [获取用户的相关信息]
        // key: 令牌
        // value: wxResult, uid, scope[决定用户身份，权限级别]
        $cachedValue = $this->prepareCachedValue($wxResult, $uid);
        $token = $this->saveToCache($cachedValue);

        // 5.把令牌返回到客户端去
        return $token;
    }

    private function newUser($openid)
    {
        $user = model('user')->create([
           'openid' => $openid
        ]);

        return $user->id;
    }

    private function prepareCachedValue($wxResult, $uid)
    {
        $cachedValue = $wxResult;
        $cachedValue['uid'] = $uid;
        $cachedValue['scope'] = 16; // 数值越大，权限越多

        return $cachedValue;
    }

    private function saveToCache($cachedValue)
    {
        $key = self::generateToken();
        $value = json_encode($cachedValue);
        // 设置缓存失效时间
        $expire_in = config('setting.token_expire_in');

        $request = cache($key, $value, $expire_in);
        if (!$request) {
            throw new TokenException([
                'msg' => '服务器缓存异常',
                'errorCode' => 10005
            ]);
        }

        return $key;
    }
}

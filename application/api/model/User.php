<?php

namespace app\api\model;

class User extends BaseModel
{
    public static function getByOpenId($openid)
    {
        $user = self::where('openid', '=', $openid)->find();

        return $user;
    }
}

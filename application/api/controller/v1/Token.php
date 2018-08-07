<?php

namespace app\api\controller\v1;

use app\api\service\UserToken;
use app\api\validate\TokenGet;
use app\api\controller\BaseController;

class Token extends BaseController
{
    public function getToken($code = '')
    {
        (new TokenGet())->goCheck();
        $userToken = new UserToken($code);
        $token = $userToken->get();

        // 不以字符串形式返回，以json格式[框架自动将数组转换为json(配置)]
        return [
            'token' => $token,
        ];
    }
}

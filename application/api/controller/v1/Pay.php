<?php

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\service\Pay as PayService;
use app\api\validate\IDMustPositiveInt;

class Pay extends BaseController
{

    // 请求微信api，进行预支付，获取微信返回的支付参数
    public function getPrePay($id)
    {
        (new IDMustPositiveInt)->goCheck();
        $pay = new PayService($id);
        $result = $pay->pay();

        return $result;
    }

}

<?php
/*
 * @Author: zhouqun
 * @Date: 2018-08-08 10:50:32
 * @Last Modified by: zhouqun
 * @Last Modified time: 2018-08-08 17:02:46
 */
namespace app\api\validate;

use app\lib\exception\ParameterException;

class OrderPlace extends BaseValidate
{
    protected $rule = [
        'products' => 'require|isNotEmpty|checkProducts',
    ];

    protected $singleRule = [
        'product_id' => 'require|isPositiveInteger',
        'count' => 'require|isPositiveInteger',
    ];

    protected function checkProducts($dataLists)
    {
        if (!$dataLists) {
            throw new ParameterException([
                'msg' => '商品列表不能为空',
            ]);
        }

        if (!is_array($dataLists)) {
            throw new ParameterException([
                'msg' => '商品参数不正确',
            ]);
        }

        foreach ($dataLists as $key => $data) {
            $this->checkProduct($data);
        }

        return true;
    }

    protected function checkProduct($product)
    {
        $validate = new BaseValidate($this->singleRule);
        $checkResult = $validate->batch()->check($product);
        if (!$checkResult) {
            throw new ParameterException([
                'msg' => '商品信息参数错误',
            ]);
        }
    }
}

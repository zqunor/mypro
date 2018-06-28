<?php

namespace app\api\validate;

use app\lib\exception\ParameterException;
use think\Request;
use think\Validate;

class BaseValidate extends Validate
{
    public function goCheck()
    {
        $request = Request::instance();
        $params = $request->param();

        $res = $this->batch()->check($params);
        if (!$res) {
            $e = new ParameterException([
                'msg' => $this->error,
                'errorCode' => 10000,
            ]);

            throw $e;
        } else {
            return true;
        }
    }

    /**
     * 验证是否是正整数
     *
     * @param int $value
     * @return boolean false/true
     */
    protected function isPositiveInteger($value)
    {
        if (is_numeric($value) && is_int($value + 0) && ($value + 0) > 0) {
            return true;
        } else {
            return false;
        }
    }
}

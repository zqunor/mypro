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
            // 如果使用Exception， 当debug模式关闭时，返回的json错误信息是系统内部错误的错误提示，而实际是用户输入不合法。
            // $error = $this->error;
            // throw new Exception($error);

            // TODO: 全局异常处理层-》自定义异常处理
            $e = new ParameterException([
                'msg' => $this->error,
                'errorCode' => 10000,
            ]);
            // $e->msg = $this->error;
            // $e->errorCode = 10002;
            // 以上两种写法的第一种写法的可读性更好，更面向对象一点，参数应该是实例化对象时就产生的，而不是之后

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

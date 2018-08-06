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

    public function isMobile($value)
    {
        $rule = '^1(3|4|5|6|7|8)[0-9]\d{8}$^';
        $result = preg_match($rule, $value);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    protected function isNotEmpty($value, $rule = '', $data = '', $field = '')
    {
        if (empty($value)) {
            return false;
        } else {
            return true;
        }
    }

    public function getDataByRule($params)
    {
        if (isset($params['uid']) || isset($params['user_id'])) {
            throw new ParameterException([
                'msg' => '参数中包含非法的参数名user_id或者uid',
            ]);
        }
        $newArray = [];
        foreach ($this->rule as $key => $value) {
            $newArray[$key] = $params[$key];
        }

        return $newArray;
    }
}

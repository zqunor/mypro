<?php

namespace app\api\validate;

use think\Validate;
use think\Request;
use think\Exception;

class BaseValidate extends Validate 
{
    public function goCheck() 
    {
        $request = Request::instance();
        $params = $request->param();

        $res = $this->check($params);
        if(!$res) {
            $error = $this->error;
            throw new Exception($error);
            // TODO: 全局异常处理层-》自定义异常处理

        }else{
            return true;
        }
    }
}
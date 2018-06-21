<?php

namespace app\api\validate;

class Count extends BaseValidate
{
     protected $rule = [
         'count' => 'isPositiveInteger|between:1,15'
     ];

    //  protected $message = [
    //     'count' => '数量参数不合法'
    //  ];
}
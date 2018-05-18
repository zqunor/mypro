<?php

namespace app\api\model;

use think\Model;

class Image extends Model
{
    protected $visible = ['url'];

    // 定义读取器（框架自动调用）
    public function getUrlAttr($value, $data)
    {
        // $value 获取到的url值。
        // $data 当前记录的完整信息(包括隐藏未显示的字段)

        $finalUrl = $value;
        if ($data['from'] == 1) {
            $prefix = config('setting.img_prefix');
            $finalUrl = $prefix . $value;
        }

        return $finalUrl;
    }
}

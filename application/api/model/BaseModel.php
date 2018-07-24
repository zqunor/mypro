<?php

namespace app\api\model;

use think\Model;

class BaseModel extends Model
{

    public function prefixImgUrl($value, $data)
    {
        $finalUrl = $value;
        if ($data['from'] == 1) {
            $prefix = config('setting.img_prefix');
            $finalUrl = $prefix . $value;
        }

        return $finalUrl;
    }
}

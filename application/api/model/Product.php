<?php

namespace app\api\model;

use think\Model;

class Product extends BaseModel
{
    protected $hidden = ['delete_time','create_time', 'update_time', 'from', 'category_id', 'pivot'];

    public function getMainImgUrlAttr($value, $data)
    {
        return $this->prefixImgUrl($value, $data);
    }
}

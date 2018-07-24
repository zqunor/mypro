<?php

namespace app\api\model;

class Product extends BaseModel
{
    protected $hidden = ['delete_time','create_time', 'update_time', 'from', 'category_id', 'pivot'];

    public function getMainImgUrlAttr($value, $data)
    {
        return $this->prefixImgUrl($value, $data);
    }

    public function getMostRecent($count)
    {
        $products = self::limit($count)->order('create_time desc')->select();
        return $products;
    }

    public function getProductsByCategoryId($categoryId)
    {
        $products = self::where('category_id', '=', $categoryId)->select();
        return $products;
    }
}

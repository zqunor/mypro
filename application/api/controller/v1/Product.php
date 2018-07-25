<?php

namespace app\api\controller\v1;

use app\api\validate\Count;
use app\api\validate\IDMustPositiveInt;
use app\lib\exception\ProductMissException;
use think\Controller;

class Product extends Controller
{
    public function getRecent($count = 15)
    {
        (new Count())->goCheck();
        $products = model('product')->getMostRecent($count);
        if ($products->isEmpty()) {
            throw new ProductMissException();
        }

        $products = $products->hidden(['summary']);

        return $products;
    }

    /**
     * 通过分类id获取分类下的所有商品
     *
     * @param [int] $id 商品分类id
     *
     * @return void
     */
    public function getAllInCategory($id)
    {
        (new IDMustPositiveInt())->goCheck();

        $products = model('Product')->getProductsByCategoryId($id);

        if ($products->isEmpty()) {
            throw new ProductMissException();
        }

        $products = $products->hidden(['summary']);

        return $products;
    }

    /*
     * 通过商品id获得获得商品详情信息
     *
     * @param int $id 商品id
     * @return void
     */
    public function getOne($id)
    {
        (new IDMustPositiveInt())->goCheck();

        $product = model('Product')->getProductById($id);
        if (!$product) {
            throw new ProductMissException(
                [
                    'msg'       => '当前产品无详情',
                    'errorCode' => 20001
                ]
            );
        }

        return $product;
    }
}

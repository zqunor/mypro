<?php

namespace app\api\controller\v1;

use think\Controller;
use app\api\validate\Count;
use app\lib\exception\ProductMissException;
use app\api\validate\IDMustPositiveInt;

class Product extends Controller
{
    public function getRecent($count=15)
    {
        (new Count())->goCheck();
        $products = model('product')->getMostRecent($count);
        if ($products->isEmpty()) {
            throw new ProductMissException();
        }

        // 在database.php中配置之后，不需要手动转换为collection
        // $productCollection = collection($products);
        // $products = $productCollection->hidden(['summary']);
        $products = $products->hidden(['summary']);
        return $products;
    }

    /**
     * 通过分类id获取分类下的所有商品
     *
     * @param [int] $id 商品分类id
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
}

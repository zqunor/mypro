<?php

namespace app\api\controller\v1;

use think\Controller;
use app\api\validate\Count;
use app\lib\exception\ProductMissException;

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
}

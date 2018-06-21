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
        if ($products) {
            throw new ProductMissException();
        }
        return $products;
    }
}

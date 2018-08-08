<?php
/*
 * @Author: zhouqun
 * @Date: 2018-08-08 10:39:54
 * @Last Modified by: zhouqun
 * @Last Modified time: 2018-08-08 17:47:00
 */

namespace app\api\service;

use app\api\model\Product;
use app\lib\exception\OrderException;
use app\lib\exception\ParameterException;

class Order
{
    // 用户提交的订单商品信息
    protected $oProducts;
    // 根据用户提交的商品信息，查询到数据库中相应商品的信息(库存量)
    protected $products;
    // 用户id
    protected $uid;

    public function place($uid, $oProducts)
    {
        $this->uid = $uid;
        $this->oProducts = $oProducts;
        $this->products = $this->getProductsByOrder($oProducts);
    }

    private function getOrderStatus()
    {
        $status = [
            'pass' => true,
            'orderPrice' => 0,
            'pStatusArray' => [], //订单商品的详细信息
        ];

        foreach ($this->oProducts as $key => $oProduct) {
            $pStatus = $this->getProductStatus($oProduct['product_id'], $oProduct['count'], $this->products);
            $status['pass'] = $pStatus['haveStock'];
            $status['orderPrice'] += $pStatus['totalPrice'];

            array_push($status['pStatusArray'], $pStatus);
        }

        return $status;
    }

    private function getProductStatus($oPId, $count, $products)
    {
        $pIndex = -1;
        $pStatus = [
            'id' => null,
            'haveStock' => false,
            'count' => 0,
            'name' => '',
            'totalPrice' => 0,
        ];

        for ($i = 0; $i < count($products); $i++) {
            if ($products[$i]['id'] == $oPId) {
                $pIndex = $i;
            }
        }

        if ($pIndex == -1) {
            // 客户端传递的product_id有可能根本不存在
            throw new OrderException([
                'msg' => 'id为' . $oPId . '的商品不存在，创建订单失败',
            ]);
        } else {
            $product = $products[$pIndex];
            $pStatus['id'] = $oPId;
            $pStatus['count'] = $count;
            $pStatus['name'] = $products[$pIndex]['name'];
            $pStatus['totalPrice'] = $products[$pIndex]['price'] * $oCount;
            $pStatus['haveStock'] = ($product[$pIndex]['stock'] >= $oCount) ? true : false;
        }

        return $pStatus;
    }

    /**
     * 根据订单信息查找真实的商品信息
     *
     * @param [array] $oProducts 订单的商品信息
     * @return array $products
     */
    private function getProductsByOrder($oProducts)
    {
        if (!is_array($oProducts)) {
            throw new ParameterException([
                'msg' => '商品列表参数错误',
            ]);
        }

        $oPIds = array_column($oProducts, 'product_id');
        $products = Product::all($oPIds)->visible(['id', 'name', 'price', 'stock', 'main_img_url'])->toArray();

        return $products;
    }
}

<?php
/*
 * @Author: zhouqun
 * @Date: 2018-08-08 10:39:54
 * @Last Modified by: zhouqun
 * @Last Modified time: 2018-08-09 15:02:05
 */

namespace app\api\service;

use app\api\model\Product as ProductModel;
use app\api\model\UserAddress;
use app\lib\exception\OrderException;
use app\lib\exception\ParameterException;
use app\lib\exception\UserException;

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

        $status = $this->getOrderStatus();
        if (!$status['pass']) {
            // 进行标记【生成订单号时进行判断】
            $status['order_id'] = -1;
            return $status;
        }

        // 开始创建订单
        $orderSnap = $this->snapOrder($status);
    }

    // 生成订单快照
    private function snapOrder($status)
    {
        $snap = [
            'orderPrice' => 0,
            'totalCount' => 0,
            'pStatus' => [],
            'snapAddress' => null,
            'snapName' => '',
            'snapImg' => '',
        ];

        $snap['orderPrice'] = $status['orderPrice'];
        $snap['totalCount'] = $status['totalCount'];
        $snap['pStatus'] = $status['pStatusArray'];
        $snap['snapAddress'] = json_encode($this->getUserAddress());
        $snap['snapName'] = $this->products[0]['name'];
        $snap['snapImg'] = $this->products[0]['main_img_url'];
        if (count($this->products) > 1) {
            $snap['snapName'] .= '等';
        }
    }

    public function getUserAddress()
    {
        $userAddress = UserAddress::where('user_id', '=', $this->uid)->find();

        if (!$userAddress) {
            throw new UserException([
                'msg' => '用户收货地址不存在，下单失败',
                'errorCode' => 60001,
            ]);
        }

        return $userAddress->toArray();
    }

    private function getOrderStatus()
    {
        $status = [
            'pass' => true,
            'orderPrice' => 0,
            'totalCount' => 0, // 订单商品的总数量，不是商品种类的数量
            'pStatusArray' => [], //订单商品的详细信息
        ];

        foreach ($this->oProducts as $key => $oProduct) {
            $pStatus = $this->getProductStatus($oProduct['product_id'], $oProduct['count'], $this->products);
            $status['pass'] = $pStatus['haveStock'];
            $status['orderPrice'] += $pStatus['totalPrice'];
            $status['totalCount'] += $oProduct['count'];

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
        $products = ProductModel::all($oPIds)->visible(['id', 'name', 'price', 'stock', 'main_img_url'])->toArray();

        return $products;
    }
}

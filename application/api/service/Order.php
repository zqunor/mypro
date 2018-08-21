<?php
/*
 * @Author: zhouqun
 * @Date: 2018-08-08 10:39:54
 * @Last Modified by: zhouqun
 * @Last Modified time: 2018-08-09 15:02:05
 */

namespace app\api\service;

use app\api\model\Order as OrderModel;
use app\api\model\OrderProduct;
use app\api\model\Product as ProductModel;
use app\api\model\UserAddress;
use app\lib\exception\OrderException;
use app\lib\exception\ParameterException;
use app\lib\exception\UserException;
use think\Exception;
use think\Db;

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
        $order = $this->createOrder($orderSnap);
        $order['pass'] = true;

        return $order;
    }

    private function createOrder($snap)
    {
        Db::startTrans();
        try {
            $order = new OrderModel();

            $orderNo = $this->makeOrderNo();
            $order->order_no = $orderNo;
            $order->user_id = $this->uid;
            $order->total_price = $snap['order_price'];
            $order->total_count = $snap['total_count'];
            $order->snap_items = $snap['p_status'];
            $order->snap_address = $snap['snap_address'];
            $order->snap_img = $snap['snap_img'];
            $order->snap_name = $snap['snap_name'];

            $order->save();

            $orderId = $order->id;
            $orderCreateTime = $order->create_time;
            foreach ($this->oProducts as &$op) {
                $op['order_id'] = $orderId;
            }

            $orderProduct = new OrderProduct();
            $orderProduct->saveAll($this->oProducts);

            Db::commit();
            return [
                'order_no' => $orderNo,
                'order_id' => $orderId,
                'create_time' => $orderCreateTime,
            ];
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    public function makeOrderNo()
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn = $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m')))
        . date('d') . substr(time(), -5)
        . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));

        return $orderSn;
    }

    // 生成订单快照
    private function snapOrder($status)
    {
        $snap = [
            'order_price' => 0,
            'total_count' => 0,
            'p_status' => null,
            'snap_address' => null,
            'snap_name' => '',
            'snap_img' => '',
        ];

        $snap['order_price'] = $status['order_price'];
        $snap['total_count'] = $status['total_count'];
        $snap['p_status'] = json_encode($status['p_status_array']);
        $snap['snap_address'] = json_encode($this->getUserAddress());
        $snap['snap_name'] = $this->products[0]['name'];
        $snap['snap_img'] = $this->products[0]['main_img_url'];
        if (count($this->products) > 1) {
            $snap['snap_name'] .= '等';
        }

        return $snap;
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
            'order_price' => 0,
            'total_count' => 0, // 订单商品的总数量，不是商品种类的数量
            'p_status_array' => [], //订单商品的详细信息
        ];

        foreach ($this->oProducts as $key => $oProduct) {
            $pStatus = $this->getProductStatus($oProduct['product_id'], $oProduct['count'], $this->products);
            if (!$pStatus['have_stock']) {
                $status['pass'] = false;
            }
            $status['order_price'] += $pStatus['total_price'];
            $status['total_count'] += $oProduct['count'];

            array_push($status['p_status_array'], $pStatus);
        }

        return $status;
    }

    private function getProductStatus($oPId, $oCount, $products)
    {
        $pIndex = -1;
        $pStatus = [
            'id' => null,
            'have_stock' => false,
            'count' => 0,
            'name' => '',
            'total_price' => 0,
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
            $pStatus['count'] = $oCount;
            $pStatus['name'] = $products[$pIndex]['name'];
            $pStatus['total_price'] = $products[$pIndex]['price'] * $oCount;
            $pStatus['have_stock'] = ($products[$pIndex]['stock'] >= $oCount) ? true : false;
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

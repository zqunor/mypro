<?php

namespace app\api\service;

use app\api\model\Order as OrderModel;
use app\lib\enum\OrderStatusEnum;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use think\Exception;
use think\Loader;
use think\Log;

// extend/WxPay/WxPay.Api.php
Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php');

class Pay
{
    private $orderId;
    private $orderNo;

    public function __construct($orderId)
    {
        if (!$orderId) {
            throw new Exception('订单号不允许为null');
        }
        $this->orderId = $orderId;
    }

    public function pay()
    {
        $this->checkOrderValidate();
        // 4.库存量检测
        //$status[pass order_price total_count p_status_array]
        $order = new Order();
        $status = $order->checkOrderStock($this->orderId);
        if (!$status['pass']) {
            $status['order_id'] = -1;
            return $status;
        }
        return $this->makeWxPreOrder($status['order_price']);
    }

    /**
     * 生成预订单[符合微信需要的订单数据]
     *
     * @return void
     */
    private function makeWxPreOrder($totalPrice)
    {
        $openid = Token::getCurrentTokenVar('openid');
        if (!$openid) {
            throw new TokenException();
        }

        $wxOrderData = new \WxPayUnifiedOrder();
        $wxOrderData->SetOpenid($openid);
        // $wxOrderData->SetAppid();
        // $wxOrderData->SetMch_id();
        // $wxOrderData->SetNonce_str();
        // $wxOrderData->SetSign();
        $wxOrderData->SetBody('商品描述：零食商贩');
        $wxOrderData->SetOut_trade_no($this->orderNo);
        $wxOrderData->SetTotal_fee($totalPrice*100);
        // $wxOrderData->SetSpbill_create_ip();
        $wxOrderData->SetNotify_url('http://qq.com');
        $wxOrderData->SetTrade_type('JSAPI');

        return $this->getPaySignature($wxOrderData);
    }

    private function getPaySignature($wxOrderData)
    {
        $wxOrder = \WxPayApi::unifiedOrder($wxOrderData);
        if ($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS') {
            Log::record($wxOrder, 'error');
            Log::record('获取预支付订单失败', 'error');
        }

        return null;
    }

    private function checkOrderValidate()
    {
        // 1.订单号可能不存在
        $order = OrderModel::where('id', '=', $this->orderId)->find();
        if (!$order) {
            throw new OrderException();
        }
        // 2.订单号存在，但是订单号和当前用户不匹配
        if (!Token::isValidateOperate($order['user_id'])) {
            throw new TokenException([
                'msg' => '订单用户与当前用户身份不匹配',
                'errorCode' => 10003,
            ]);
        }
        // 3.检测订单是否已经被支付过 [status：1-未支付 2-已支付 3-已发货 4-已支付，但库存不足]
        if ($order->status != OrderStatusEnum::UNPAID) {
            throw new OrderException([
                'msg' => '订单已支付，请勿重复支付',
                'errorCode' => 80003,
                'code' => 400,
            ]);
        }

        $this->orderNo = $order->order_no;
        return true;
    }
}

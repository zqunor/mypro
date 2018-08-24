<?php
/*
 * @Author: zhouqun
 * @Date: 2018-08-21 17:11:28
 * @Last Modified by: zhouqun
 * @Last Modified time: 2018-08-21 17:14:13
 */

namespace app\lib\enum;

class OrderStatusEnum
{
    // 待支付
    const UNPAID = 1;

    // 已支付
    const PAID = 2;

    // 已发货
    const DELIVERED = 3;

    // 已支付，但库存不足
    const PAID_BUT_OUT_OF = 4;
}
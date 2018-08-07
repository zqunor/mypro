<?php

namespace app\api\controller\v1;

use app\api\validate\AddressNew;
use app\api\service\Token;
use app\lib\exception\SuccessMessage;
use app\lib\exception\UserException;
use think\Controller;

class Address extends Controller
{

    protected $beforeActionList = [
        'first' => ['only' => 'second']
    ];

    // 前置方法
    protected function first ()
    {
        echo 'first';
    }

    // API接口
    public function second()
    {
        echo 'second';
    }

    /**
     * 创建或更新用户的收货地址
     *
     * @return
     */
    public function createOrUpdate()
    {
        $validate = new AddressNew();
        $validate->goCheck();

        // 1.根据Token获取uid
        $uid = Token::getCurrentUid();

        // 2.根据uid查找用户数据，判断用户是否存在，如果不存在，则抛出异常
        $user = model('user')->get($uid);

        if (!$user) {
            throw new UserException();
        }
        // 3.如果存在，则获取用户从客户端提交来的地址信息
        $dataArray = $validate->getDataByRule(input('post.'));

        // 4.根据用户地址信息是否存在，从而判断是添加地址还是更新地址
        $userAddress = $user->address;
        if (!$userAddress) {
            // 新增
            $user->address()->save($dataArray);
        } else {
            // 更新
            $user->address->save($dataArray);
        }

        return json(new SuccessMessage(), 201);
    }
}

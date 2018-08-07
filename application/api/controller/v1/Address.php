<?php

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\service\Token as TokenService;
use app\api\validate\AddressNew;
use app\lib\exception\SuccessMessage;
use app\lib\exception\UserException;

class Address extends BaseController
{
    protected $beforeActionList = [
        'checkPrimaryScope' => ['only' => 'createorupdate'],
    ];

    public function createOrUpdate()
    {
        $validate = new AddressNew();
        $validate->goCheck();

        // 1.根据Token获取uid
        $uid = TokenService::getCurrentUid();

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

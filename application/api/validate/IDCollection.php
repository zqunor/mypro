<?php
namespace app\api\validate;

class IDCollection extends BaseValidate
{
    protected $rule = [
        'ids' => 'require|checkIDs'
    ];

    protected $message = [
        'ids' => 'ids必须是以逗号隔开的多个正整数'
    ];

    // $values = id1,id2,...
    protected function checkIDs($values)
    {
        $ids = explode(',', $values);

        if (empty($ids)) {
            return false;
        }
        foreach ($ids as $id) {
            $res = $this->isPositiveInteger($id);
            if (!$res) {
                return false;
            }

            return true;
        }
    }

}

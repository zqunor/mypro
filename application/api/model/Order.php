<?php

namespace app\api\model;

use think\Model;

class Order extends Model
{
    protected $hidden = ['user_id', 'delete_time', 'update_time'];
    protected $autoWriteTimestamp = true;
}

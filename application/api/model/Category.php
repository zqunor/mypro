<?php

namespace app\api\model;

use think\Model;

class Category extends Model
{
    protected $hidden = ['topic_img_id', 'delete_time', 'update_time'];

    public function img()
    {
        return $this->belongsTo('Image', 'topic_img_id', 'id');
    }
}

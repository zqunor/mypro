<?php
namespace app\api\model;

use think\Db;
use think\Model;

class BannerItem extends BaseModel
{
    protected $visible = ['key_word', 'type', 'img'];

    public function img()
    {
        return $this->belongsTo('Image', 'img_id', 'id');
    }
}
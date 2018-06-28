<?php
namespace app\api\model;

use think\Db;

class Banner extends BaseModel
{
    protected $hidden = ['delete_time', 'update_time'];

    public function items()
    {
        return $this->hasMany('BannerItem', 'banner_id', 'id');
    }
    public static function getBannerById($id)
    {
        $result = Db::table('banner_item')
            ->where(function ($query) use ($id) {
                $query->where('banner_id', '=', $id);
            })
            ->select();

        return $result;
    }
}

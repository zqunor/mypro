<?php
namespace app\api\controller\v1;

use app\api\validate\IDMustPositiveInt;
use app\lib\exception\BannerMissException;

class Banner
{
    /**
     * 获取指定id 的banner信息
     * @url /banner/:id
     * @http GET
     * @id banner的id
     */
    public function getBanner($id)
    {
        (new IDMustPositiveInt())->goCheck();

        $banner = model('banner')->with(['items', 'items.img'])->find($id);
        if (!$banner) {
            throw new BannerMissException();
        }

        return $banner;
    }
}

<?php

namespace app\api\controller\v1;

use app\api\validate\IDCollection;
use think\Controller;
use app\api\model\Theme as ThemeModel;
use app\api\validate\IDMustPositiveInt;

class Theme extends Controller
{
    public function getSimpleList($ids)
    {
        (new IDCollection())->goCheck();

        $ids = explode(',', $ids);
        $theme = model('theme')->with(['topicImg', 'headImg'])->select($ids);

        if (!$theme) {
            throw new ThemeMissException();
        }

        return json($theme);
    }

    public function getProducts($id)
    {
        (new IDMustPositiveInt())->goCheck();

        $res = ThemeModel::getProductsByThemeId($id);

        return $res;

    }
}

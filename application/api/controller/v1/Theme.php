<?php

namespace app\api\controller\v1;

use app\api\validate\IDCollection;
use app\api\validate\IDMustPositiveInt;
use app\lib\exception\ThemeMissException;
use app\api\controller\BaseController;

class Theme extends BaseController
{
    /**
     * 获取需要的主题theme.
     *
     * @param string $ids
     *
     * @return string $theme 一组theme模型
     */
    public function getSimpleList($ids = '')
    {
        (new IDCollection())->goCheck();

        $ids = explode(',', $ids);
        $theme = model('theme')->with(['topicImg', 'headImg'])->select($ids);

        if ($theme->isEmpty()) {
            throw new ThemeMissException();
        }

        return json($theme);
    }

    public function getProducts($id)
    {
        (new IDMustPositiveInt())->goCheck();

        $res = model('theme')->getThemeWithProducts($id);

        return json($res);
    }
}

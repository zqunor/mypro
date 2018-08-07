<?php

namespace app\api\controller\v1;

use app\lib\exception\CategoryMissException;
use app\api\controller\BaseController;

class Category extends BaseController
{
    /**
     * 显示资源列表.
     *
     * @return
     */
    public function getAllCategories()
    {
        // $categories = model('Category')->with('img')->select();
        $categories = model('Category')->all([], 'img');
        if ($categories->isEmpty()) {
            throw new CategoryMissException();
        }

        return $categories;
    }
}

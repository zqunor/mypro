<?php 
// 注意命名空间表示当前为应用下index模块的控制器
namespace app\index\controller;

use think\Controller;

/**
* 新闻管理控制器
*/
class News extends Controller
{
	public function index()
	{
		echo "hello world";
	}

	public function category($cat_id)
	{
		echo $cat_id;
	}
}
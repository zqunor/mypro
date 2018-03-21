<?php 

namespace app\index\controller\one;

use think\Controller;

/**
* 博客管理控制器
*/
class Blog extends Controller
{
	public function index()
	{
		return $this->fetch();
	}
}
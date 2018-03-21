<?php 

namespace app\index\controller;

use think\Request;

/**
* Error控制器类
* 访问到未定义的控制器的时候调用该错误处理控制器
* 配置文件中的empty_controller配置项配置的错误类名
*/
class Error
{
	
	public function index(Request $request)
	{
		// $request为 think\Request Object
		// 通过调用该属性的方法，获取到相关数据
		print_r($request->dispatch());
	}
}
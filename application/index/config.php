<?php 
return [
	// 这种情况下会将return ['name'=>'thinkphp','status'=>1];的结果以json形式输出
	// 默认情况是html，这样直接返回数组形式则无法正常输出到页面，并会显示页面错误的提示
	// 'default_return_type'    => 'json',


	// 这种情况下ajax请求不会对返回内容进行转换
	// 'default_ajax_return'   => 'html',


	// 默认跳转页面对应的模板文件
    // 'dispatch_success_tmpl'  => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',

    // application/tpl/error.tpl
    'dispatch_error_tmpl'    => APP_PATH . 'tpl' . DS . 'error.tpl',

    // 默认的空控制器名
    'empty_controller'       => 'Error',

    
];


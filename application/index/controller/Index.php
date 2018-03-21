<?php
namespace app\index\controller;
use think\Controller;


class Index extends Controller
{
	// 前置操作
	// 设置 beforeActionList属性，指定某个方法为其他方法的前置操作，数组键名为需要调用的前置方法名，无值的话为当前控制器下所有方法的前置方法。
	// protected $beforeActionList = [
 //        'first',
 //        'second' =>  ['except'=>'hello'],
 //        'three'  =>  ['only'=>'hello,data'],
 //    ];

 //    protected function first()
 //    {
 //        echo 'first<br/>';
 //    }
    
 //    protected function second()
 //    {
 //        echo 'second<br/>';
 //    }
    
 //    protected function three()
 //    {
 //        echo 'three<br/>';
 //    }

 //    public function hello()
 //    {
 //        return 'hello';
 //    }
    
 //    public function data()
 //    {
 //        return 'data';
 //    }

	// public function _initialize()
 //    {
 //        echo '_initialize()方法会在任意方法调用前执行一次， 但是需要继承框架的控制器类；use think\Controller;<br/>';
 //    }

    public function index()
    {
        return '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="ad_bd568ce7058a1091"></think>';
    }

    public function test()
    {
    	// echo "测试成功！";
    	// return ['name'=>'thinkphp','status'=>1];

        // 配置项'dispatch_error_tmpl'可进行设置其显示的页面
        // 'dispatch_error_tmpl'    => APP_PATH . 'tpl' . DS . 'error.tpl', 
        // $this->error('失败');


        // 跳转到index模块的News控制器的category方法,并且传递参数值cat_id=2
        // $this->redirect('News/category', ['cat_id' => 2], 302, ['data'=>'hello']);
        $news = model('News');
        // var_dump($news->fetchAll());
        var_dump($news->fetchOneColumn());

    }

    public function news()
    {

        if (isset($_POST['title'])) {
             // 有id =》修改
            var_dump($_POST);
            
            
        }else{
           // 没有id =》显示
            return view();
        }
        
    }

    public function messageBook()
    {
        //未使用
    	// echo "111";
    	// $view = new View();
        // return $view->fetch('messageBook');
    	
    }

    // 当前控制器的空操作（方法），访问没有定义的方法时，会定位到_empty()方法，这个没有定义的方法名就会作为_empty()的参数
    public function _empty($name)
    {
        return $this->showCity($name);
    }
    protected function showCity($name)
    {
        echo "当前城市：". $cityName;
    }
}

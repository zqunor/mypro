<?php
 namespace app\api\controller\v1;

 use think\Validate;
 use think\Exception;
 use app\api\validate\IDMustPositiveInt;
 use app\lib\exception\BannerMissException;
 use app\api\model\Banner as BannerModel;

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
        // $banner = BannerModel::getBannerById($id);
        // model/Banner.php继承Model就成为了model,于是就可以使用模型类封装的方法。
        $banner = BannerModel::get($id);
        if(!$banner) {
            // $banner === false

            throw new BannerMissException();
            
            // throw new Exception('内部错误！'); //用于测试
            
            // 此处BannerMissException必须是继承Exception的类
        }
        return json($banner);
        //  $data = [
        //      'name' => 'vendor11111',
        //      'email' => 'vendorqq.com'
        //  ];
         // 1、 独立验证
        //  $validate = new Validate([
        //     'name' => 'require|max:10',
        //     'email'=> 'email'
        //  ]);

         // 2、验证器验证
        //  $validate =  new IDMustPotiveInt();

        //  // batch()批量验证
        //  $result = $validate->batch()->check($data);
        //  var_dump($validate->getError());


        // 错误验证
        // try{
        //     $banner = model('banner')::getBannerById($id);
        // }catch(Exception $e){
        //     $err = [
        //         'error_code' => 10001,
        //         'msg' => $e->getMessage()
        //     ];
        //     return json($err, 400);
        // }


         // AOP 面向切面编程 =》 站在更高的角度，用抽象的方式，统一的、总体的来处理某一个问题
         // 中间件（TP5中的行为）即是AOP思想的具体应用
     }
 }
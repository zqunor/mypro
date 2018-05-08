<?php
 namespace app\api\controller\v1;

 use think\Validate;
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

        $banner = model('banner')::getBannerById($id);
        return $banner;
        if(!$banner) {
            // $banner === false
            throw new BannerMissException();
            // 此处BannerMissException必须是继承Exception的类
        }
        return $banner;
    
     }
 }
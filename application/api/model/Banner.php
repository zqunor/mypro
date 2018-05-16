<?php
namespace app\api\model;
use think\Model;
use think\Db;
use think\Exception;

class Banner extends Model
{
    public static function getBannerById($id)
    {
        // 程序编写存在异常，提示服务器内部错误
        // try{
        //     1/0;
        // } catch(Exception $e) {
        //     throw $e;
        // }
        // return 'this is banner info';
        
        // 用户输入存在异常，反馈相应的错误提示
        // return null;

        // 操作数据库，获取数据
        // 1.原生sql语句，进行查询
        $result = Db::query('select * from banner_item where banner_id=?', [$id]);
        return $banners;

    }
}
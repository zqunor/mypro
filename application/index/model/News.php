<?php 
namespace app\index\model;
use think\Model;
use think\Db;

/**
* 新闻管理模型类
*/
class News extends Model
{
	 // 设置当前模型对应的完整数据表名称
    protected $table = 'news';

	public function add($data)
	{
		$res = Db::table($this->table)->insert($data);
		return $res;		
	}
	public function fetchAll()
	{

    	// 测试连接数据库

        // var_dump($news);

        // var_dump(config('database'));  // 查看数据库配置信息

        // 需要引入think\Db类才可使用
        // $res = Db::connect();
        // var_dump($res);

        // 获取数据表中的信息
        // $res = Db::query('select * from '.$this->table);
		// $res = Db::table($this->table)->select();
		$res = Db::table($this->table)->value('createTime');

		// 闭包查询
		// $res = Db::select(function($query){
		// 	$query->table($this->table);
		// });


        return $res;

	}

	public function fetchOneColumn()
	{
		//获取所有记录该列的数据
		// $res = Db::table($this->table)->column($name);

		//获取符合条件的某一列的数据
		$res = Db::table($this->table)->where('id',2)->column('id','createTime');
		return $res;
	}
}
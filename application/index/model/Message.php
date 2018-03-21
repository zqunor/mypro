<?php 
/**
create table message(
`id` int not null PRIMARY KEY auto_increment,
`title` varchar(40) not null,
`content` text ,
`createTime` timestamp,
`userName` varchar(10)
);

insert into message 

*/

namespace app\index\model;
use think\Model;
use think\Db;

/**
* 留言本控制器类
*/
class Message extends Model
{
	
	private $table = 'message';

	public function fetchAll()
	{
		$res = Db::table($this->table)->select();
		return $res;
	}

}
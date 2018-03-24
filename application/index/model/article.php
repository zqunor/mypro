<?php
namespace app\index\model;
use think\Model;

class article extends Model
{
	protected $name='message';

	public function comments()
	{
		return $this->hasMany('comment');
	}
    public function test()
    {

    }
	public function add($data)
    {
        $ret=$this->save($data);
        return $ret;
    }
}

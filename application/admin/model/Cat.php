<?php
namespace app\admin\model;
use think\Model;
class Cat extends Model{
	/*定义一个方法，用于形成树结构
@param $arr
@param $pid int parent_id
@return array 空数组
*/
public function tree($arr, $pid=0, $level=0){
	static $tree = array();
	foreach($arr as $v){
		if($v['pid'] == $pid){
			$v['level'] = $level;
			$tree[] = $v;
			$this->tree($arr, $v['cat_id'], $level+1);
		}

	}
	return $tree;
}
	
}







?>
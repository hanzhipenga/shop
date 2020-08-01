<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\model\User as UserModel;
class Base extends Controller
{
	// protected $is_check_login = [''];
	// public function _initialize(){
	// 	if(!$this->isLogin()&& in_array(Request::instance()->action(), $this->is_check_login)||$this->is_check_login[0]=='*'){
	// 		return $this->error('请先登录！', 'user/login');
	// 	}
	// }
	// public function isLogin(){
	// 	return session('?name');
	// }
	public function _initialize(){
		if(!session('user_id')){
			return $this->error('请先登录系统','user/login');
		}
	}


}






?>
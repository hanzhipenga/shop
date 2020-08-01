<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
class Base extends Controller
{
	 protected $is_check_login = [''];
	 public function _initialize(){
	 	if(!$this->isLogin()&& in_array(Request::instance()->action(), $this->is_check_login)||$this->is_check_login[0]=='*'){
	 		return $this->error('请先登录！', 'index/login');
	 	}
	 }
	 public function isLogin(){
	 	return session('?name');
	 }

	


}


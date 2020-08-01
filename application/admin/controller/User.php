<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\model\User as UserModel;
use think\Log;
class User extends Controller
{
	//登录页面
	public function login()
	{

		return $this->fetch();
	}
	//登录检测页面
	public function check()
	{
		$data = input('post.');
		$user_name = $_POST['user_name'];
        if(preg_match("/^[a-z0-9]+([._]*[a-z0-9]+)*@[a-z0-9]+([_.][a-z0-9]+)+$/u",$user_name)){
            $where['email']=$user_name;
        }else{
            $where['mobile']=$user_name;
        }
		$user = new UserModel();

		$result = $user->where($where)->find();

		if($result){
			if($result['password'] === md5($data['password'])){
				session('user_id', $result['user_id']);
			} else {
				$this->error('密码不正确');
			}
		}else{
			$this->error('用户不存在');
			exit;
		}
		if(captcha_check($data['code'])){
			$this->success('验证码正确，恭喜登录成功','goods/index');
		}else{
			$this->error('验证码不正确');
		}
	}
	//注册页面
	public function register()
	{
		return $this->fetch();
	}
	//添加用户管理
	public function add()
	{
		$data = input('post.');
		$data['add_time'] = date('Y-m-d:H-i-s');
		$user = new UserModel($data);
		if($result = $user->allowField(true)->save()){
			$this->success('用户新增成功','login');
         }else{
         	$this->error( '用户新增失败','register');
         }
		
	}
	//修改密码页面
	public function change()
	{
		return $this->fetch();
	}
	//改密信息管理
	public function newPassword(){
		$data = input('post.');
		$data['update_time'] = date('Y-m-d:H-i-s');
		$pwd = new UserModel($data);
		$result = $pwd->where('user_name', $data['user_name'])->update(['password'=>md5($data['password']), 'repassword'=>md5($data['repassword']),'update_time'=>$data['update_time']]);
		if($result){
			$this->success('用户密码更新成功','login');
         }else{
         	$this->error( '用户密码更新失败','change');
         }
	}
	//检测用户名是否重复
	public function repeat(){
   	$admin_name = $_POST['name'];
   	$user = new UserModel();
   	if($user->where('user_name', $admin_name)->find()){
   	  echo 1;
   	}else{
      echo 0;
    }
   }
}

?>
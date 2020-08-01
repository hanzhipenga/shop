<?php
namespace app\index\validate;
use think\Validate;
class Person extends Validate
{
	protected $rule = [
		'name|用户名'=>'min:3|max:10',
		'password|密码'=>'min:6|max:14|confirm:repassword',
		
    ];
	protected $message = [
		'name.min'=>'用户名长度不能少于三位',
		'name.max'=>'用户名长度不能多于十位',
		'password.min'=>'密码长度不能少于六位',
		'password.max'=>'密码长度不能多于十四位',
		'password.confirm'=>'两次密码不一致',
	];
}






?>
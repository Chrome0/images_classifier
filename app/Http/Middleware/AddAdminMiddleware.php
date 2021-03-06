<?php

namespace App\Http\Middleware;

use Closure;
use App\User;
use Illuminate\Contracts\Auth\Factory as Auth;

class AddAdminMiddleware
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * 管理员注册中间件
     *
     *@global  \Illuminate\Http\Request  $request
     *@param  \Closure  $next
     *@param  string|null  $guard
     *@param  sendUsername 前台发送的 用户名
     *@param  sendPassword 前台发送的 密码
     *@param  sendEmployeeId 前台发送的 工号
     *@param  sendPasswordAgain 前台发送的 第二次输入的密码
     *@
     *@return 通过验证返回$next($request)，
     *		否则返回json数据  ['resultCode'=>'0','remind'=>'xxx']
     *		 (判断是否验证成功 1 成功、0 失败，XXX 返回的提醒内容)
     */
	public function handle($request, Closure $next){
		
		//接收数据
		$username=$request->input('sendUsername');
		$password=$request->input('sendPassword');
		$passwordAgain=$request->input('sendPasswordAgain');
		$email=$request->input('sendEmail');
		$employeeId=$request->input('sendEmployeeId');
		
		//查找用户名，用于判断用户名是否存在
		$name_result = User::select('name')
			->where('name',$username)
			->first();
		$email_result = User::select('email')
			->where('email',$email)
			->first();
		//输入判断
		if($username==''){
			return ['resultCode'=>'0','remind'=>'请输入用户名'];
		}elseif(strlen($username)>5){
			return ['resultCode' => '0','remind' => '用户名长度不能大于5个字符'];
		}elseif($name_result!=null){
			return ['resultCode' => '0', "remind" => '用户名已存在'];
		}elseif($password==''){
			return ['resultCode'=>'0','remind' => '请输入密码'];
		}elseif(strlen($password)<6){
			return ['resultCode' => '0','remind' => '密码长度不能小于6个字符'];
		}elseif($password!=$passwordAgain){
			return ['resultCode' => '0','remind' =>"两次输入密码不一致"];
		}elseif($email==''){
			return ['resultCode' => '0','remind' =>"请输入邮箱"];
		}elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			return ['resultCode' => '0','remind' =>"输入邮箱不合法"];
		}elseif($email_result!=null){
			return ['resultCode' => '0', "remind" => '邮箱已经被注册'];
		}elseif($employeeId==''){
			return ['resultCode'=>'0','remind' => '请输入工号'];
		}elseif(strlen($employeeId)>6){
			return ['resultCode' => '0','remind' => '工号长度过长'];
		}else{
			return $next($request);
		}
		
	}
}

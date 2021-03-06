<?php
	namespace App;


	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Support\Facades\DB;
	
	class Admin extends Model{
		protected $table = 'admins';  //指定表名
		
		protected $primaryKey = 'auto_id';  //指定主键
		
		protected $guarded = ['auto_id'];  //不可批量添加的字段（黑名单）
			
		/**
		*
		*@author 范留山
		*增加管理员，user_id 根据user表自增id进行md5加密
		*
		*@param  $username 前台发送的用户名
		*@param  $password 前台发送的密码
		*@param  $employeeId 前台发送的工号
		*@param  $email 前台发送的邮箱
		*
		*@return  [
		*			'resultCode'=>0 或 1,
		*			'resultMsg' => '添加管理员成功' 或 '添加管理员失败'
		*			];
		*
		*/
		public function addAdministrator($password,$email){
			
			//向user表插入信息
			$user_id=uniqid();
			User::insert([
				'user_id' => $user_id,
				'password' => app('hash')->make($password),
				'email' => $email,
				'status' => 'admin',
			]);
			return true;
		}
		

        /**
        * @auther 田荣鑫
        * 获取管理员列表，获取的管理员名字 为真实姓名
        */
        public function getAdministratorList()
        {
            $getAdminList =User::join('admins',"users.user_id",'admins.user_id')
                ->whereRaw('users.is_del = ? and users.status = ?',[0,"admin"] )
                ->select('users.name',
					'users.user_id',
					'admins.realname',
					'admins.employee_id',
					'admins.idcarnumber',
					'admins.address',
					'admins.telephone',
					'admins.icon_location'
				)
                ->get();
            return $getAdminList;
        }
		
		 /**
		* @author 田荣鑫
		* 删除管理员（deleteAdministrators）
		* @param $user_id   前台传值到后台经过md5加密值，用来判断哪位管理员
		* @return  [
		* 			    'resultMsg' => '删除成功' 或 '删除失败'
		*           ]
		*/
		public function  deleteAdministrators($user_id)
		{
			//删除操作 数据库为更新is_del数值为1
			//$adminDelResult  admin表操作影响行数
			//$userDelResult    users表操作影响行数
		/*	$adminDelResult = DB::table('admins')
				->where('user_id',$user_id)
				->update(['is_del'=>1]);*/
			$userDelResult = DB::table('users')
				->where('user_id',$user_id)
				->update(['is_del'=>1]);
			return $userDelResult;
		}

		/**
		* @author 田荣鑫
		* 修改管理员密码  （允许其他管理员修改别人的密码？）
		* @param $user_id   前台传值到后台经过md5加密值，用来判断哪位管理员。
		* @param $newPassword   前台获取的新的管理员密码
		* @return  [
		* 			    'resultMsg' => '修改成功' 或 '修改失败'
		*           ]
		*
		*/
		public function alterAdminPsd($user_id,$newPassword)
		{
			//修改密码，基于user_id
			$result = DB::table('users')
				->where('user_id',$user_id)
				->update(['password'=>$newPassword]);
			return $result;
		}
	}
?>
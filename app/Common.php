<?php
	
namespace App;

use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Common extends Model
{
      /**
       *
       *
       * @author 聂恒奥
       *
       * 用来更新单个个人信息，或是管理员批量修改一组用户的信息，可更新多个字段。
       *
       * @param $all
       *
       *
       * @return $ResultCode
       *
       *
       */
    public function updatePersonInformation($all,$user_id){

        $ResultCode = 0;
        if (array_key_exists('like_image_class',$all)){
            if($all['like_image_class']!=null){
                $all['like_image_class']=json_encode($all['like_image_class']);
            }
        }
        $all = array_filter($all);
        //if前台传入了user_id数组，即为管理员更改多个用户的统一信息。
        if (isset($all['user_id'])==true){
            $all_input = $all;
            unset($all_input['user_id']);
            unset($all_input['token']);
            //更新数据
            Client::whereIn('user_id',$all['user_id'])->update(
                $all_input
            );
            $ResultCode = 1;
        }
        //else为用户更改个人信息。
        else{
            //提取数据，判断是管理员还是用户
            $user_all = User::where('user_id', $user_id)->first()['attributes'];
            $if = $user_all['status'];
            //获取表中全部字段，为下面判断要更新字段所属表做准备。
            if ($if == 'admin'){
                $table_all = Admin::where('user_id', '=', $user_id)->first()['attributes'];
            }
            else{
                $table_all = Client::where('user_id', '=', $user_id)->first()['attributes'];
            }
            //筛选数据，判断要更新的字段所属的表，并完成更新。
            foreach ($all as $key=>$value){
                if (array_key_exists($key,$user_all)){
                    $ResultCode = User::where('user_id', '=', $user_id)->update(
                        [$key=>$value,'updated_at'=>date("Y-m-d h:i:s")]
                    );
                }
                elseif (array_key_exists($key,$table_all)){
                    if ($if == 'admin'){
                        $ResultCode = Admin::where('user_id', '=', $user_id)->update(
                            [$key=>$value,'updated_at'=>date("Y-m-d h:i:s")]
                        );
                    }
                    else{
                        $ResultCode = Client::where('user_id', '=', $user_id)->update(
                            [$key=>$value,'updated_at'=>date("Y-m-d h:i:s")]
                        );
                    }
                }
            }
        }
        return $ResultCode;
    }
    /**
     *
     * @author 聂恒奥
     *
     * 管理员修改用户信息
     *
     * @param $user_id
     * @param $all
     *
     * @return $ResultCode
     *
     *
     */
    public function adminUpdateInformation($all,$user_id){
        $ResultCode = 0;
        if (array_key_exists('like_image_class',$all)){
            if($all['like_image_class']!=null){
                $all['like_image_class']=json_encode($all['like_image_class']);
            }
        }
        $all = array_filter($all);
        $user_all = User::where('user_id', $user_id)->first()['attributes'];
        $table_all = Client::where('user_id', '=', $user_id)->first()['attributes'];
        //筛选数据，判断要更新的字段所属的表，并完成更新。
        foreach ($all as $key=>$value){
            if (array_key_exists($key,$user_all)){
                $ResultCode = User::where('user_id', '=', $user_id)->update(
                    [$key=>$value,'updated_at'=>date("Y-m-d h:i:s")]
                );
            }
            elseif (array_key_exists($key,$table_all)){
                $ResultCode = Client::where('user_id', '=', $user_id)->update(
                    [$key=>$value,'updated_at'=>date("Y-m-d h:i:s")]
                );
            }
        }
        return $ResultCode;
    }





    /**
     *
     * @author 聂恒奥
     *
     * 用来修改密码，将密码加密后更新进数据库
     *
     * @param $user_id
     * @param $NewPassword
     *
     * @return $ResultCode
     *
     *
     */

    public function changePassword($user_id,$newPassword){

        $ResultCode = User::where('user_id', $user_id)->update(
            ['password' => app('hash')->make($newPassword),'updated_at'=>date("Y-m-d h:i:s")]);
        return $ResultCode;
    }

    /**
     * @author killer 2017年5月5日17:33:56
     * 用来返回相应前台的数据，返回的数据经过该函数进行格式化。
     * 成功相应时$status默认为200
     * 响应成功的例子：$common->returnJsonResponse(1,'token_generated',array('token' => $token));
     * 相应错误信息时，填写相应的状态码
     * 响应失败的例子：$common->returnJsonResponse(0,'could_not_create_token',
    null,Response::HTTP_INTERNAL_SERVER_ERROR);
     * @param $resultCode
     * @param $resultMsg
     * @param $data
     * @param int $status default 200
     * @return JsonResponse
     */
    public static function returnJsonResponse($resultCode,$resultMsg,$data,$status = 200){
        return new JsonResponse([
            'ResultCode' => $resultCode,
            'ResultMsg'  => $resultMsg,
            'Data' => $data
        ],$status);
    }
    /**
     * @author killer 2017年6月4日00:53:19
     * 根据用户的id返回用户任务所在的任务表名称列表
     * @param user_id
     * @return table_name
     */
    public static function generateDatabaseNamesByClientId($user_id){
        $tablesName = array();
        $tableTopName = substr($user_id,-1);//获取用户id的最后一个字符
        //$tableResult = DB::select("SHOW TABLES LIKE '$tableTailName%'");
        $tableResult = DB::select("select table_name from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA = 'images_classifier' and TABLE_NAME LIKE'$tableTopName%'");
        foreach($tableResult as $table){
            $tablesName[] = $table->table_name;
        }
        return $tablesName;
    }
    /**
     * @author killer 2017年6月4日02:07:22
     * 根据用户的id和图片id返回用户任务所在的任务表名称列表
     * @param user_id
     * @return table_name
     */
    public static function generateDatabaseNamesByClientIdAndImageId($user_id,$image_id){
        $tableTopName = substr($user_id,-1);//获取用户id的最后一个字符
        //return $image_id;
        $tableTailName  = substr($image_id,-3,3);//获取图片id的后三个字符
        $tableName = $tableTopName."_".$tableTailName."_task";
        return $tableName;
    }

    /**@author killer 2017年6月4日 03:15:11
     * 检查当前数据库里面是否存在某个任务表，如果不存在则创建
     * @param $table_name
     */
    public static function checkDatabaseByTableName($table_name){
        $tableResult = DB::select("select table_name from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA = 'images_classifier' and TABLE_NAME = '$table_name'");
        if(count($tableResult)==0){
           //不存在该表，创建该表。
            DB::select('create table if not exists images_classifier.'.$table_name.'(
				auto_id  INT(6) not null AUTO_INCREMENT,
				primary key (auto_id),
				task_id varchar(16) not null,
				user_id varchar(16) not null,
				image_id varchar(32) not null, 
				user_assign_label MEDIUMTEXT,
				user_assign_label_id MEDIUMTEXT
				)engine innoDB');
            //sleep(1);//等待表建立成功
        }
    }
}
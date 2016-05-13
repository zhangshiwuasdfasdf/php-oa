<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/


class CommonModel extends Model {
	protected $_auto	 =	 array(
		array('is_del','0',self::MODEL_INSERT),
		array('create_time','time',self::MODEL_INSERT,'function'),
		array('update_time','time',self::MODEL_UPDATE,'function'),
		array('user_id','get_user_id',self::MODEL_INSERT,'callback'),
		array('user_name','get_user_name',self::MODEL_INSERT,'callback'),
		);
	
	function get_user_id(){
		$user_id = session(C('USER_AUTH_KEY'));
		return isset($user_id) ? $user_id : 0;
	}

	function get_user_name(){
		$user_name = session('user_name');
		return isset($user_name) ? $user_name : 0;
	}
	
	protected function _pushReturn($data,$info,$status,$user_id,$time = null){
		$model = M("Push");

		$model -> data = $data;
		$model -> info = $info;
		$model -> status = $status;
		
		if(empty($user_id)){
			$model -> user_id = get_user_id();
		}else{
			$model -> user_id=$user_id;
		}

		if (empty($time)) {
			$model -> time = time();
		} else {
			$model -> time = $time;
		}
		$model -> add();
	}	
}
?>
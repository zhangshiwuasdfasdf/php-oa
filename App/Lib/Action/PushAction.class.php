<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class PushAction extends Action {

	protected $config=array('app_type'=>'asst');

	function _initialize(){	
		$auth_id = session(C('USER_AUTH_KEY'));
		if (!isset($auth_id)) {
			//跳转到认证网关
			die;
		}
	}

	function server(){
		$user_id = $user_id = get_user_id();
		session_write_close();
		while (true){
			$where = array();		
			$where['user_id'] = $user_id;
			$where['time'] = array('elt', time() - 1);
			$model = M("Push");
			$data = $model -> where($where) -> find();

			if ($data){
				$model -> delete($data['id']);
				echo json_encode($data);
				flush();
				sleep(1);
				die;
			} else {
				sleep(1); // sleep 10ms to unload the CPU
				clearstatcache();
			}
		}
	}

	function server2() {
		$user_id = $user_id = get_user_id();
		session_write_close();
		for ($i = 0, $timeout = 10; $i < $timeout; $i++){
			if (connection_status() != 0) {
				exit();
			}
			$where = array();
			$where['user_id'] = $user_id;
			$where['time'] = array('elt', time() - 1);
			$model = M("Push");
			$data = $model -> where($where) -> find();
			$where['id'] = $data['id'];
			//dump($model);
			if ($data){
				sleep(1);
				$model -> where("id=" . $data['id']) -> delete();
				$this -> ajaxReturn($data['data'], $data['info'], $data['status']);
			} else {
				sleep(2);
			}
		}
		$this -> ajaxReturn(null, "no-data", 0);
	}

	//获取当前状态
	function status(){
		$data=get_new_count();
		$this -> ajaxReturn($data);
	}

	function add($status, $info, $data) {
		$user_id = get_user_id();
		$model = M("Push");
		$model -> user_id = $user_id;
		$model -> data = $data;
		$model -> status = $status;
		$model -> info = $info;
		$model -> add();
	}
}
?>
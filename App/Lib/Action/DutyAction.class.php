<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/
 
class DutyAction extends CommonAction {
	protected $config=array('app_type'=>'master');

	public function _search_filter(&$map) {
		if (!empty($_GET['pid'])) {
			$map['pid'] = $_POST['pid'];
		}
	}

	public function del()	{
		$role_id=$_POST['id'];		
		$where['role_id'] = $role_id;
		
		$model = M("DutyUser");
		$model->where($where)->delete();	
		$this->_destory($role_id);
	}

	public function user() {
		$keyword = "";
		if (!empty($_POST['keyword'])) {
			$keyword = $_POST['keyword'];
		}
		$user_list = D("User") -> get_user_list($keyword);
		$this -> assign("user_list", $user_list);

		$role = M("Duty");
		$duty_list = $role-> order('sort asc') -> select();
		$this -> assign("duty_list", $duty_list);
		$this -> display();
	}
}
?>
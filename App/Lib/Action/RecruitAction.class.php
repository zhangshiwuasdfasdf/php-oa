<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class RecruitAction extends CommonAction {
	//app 类型
// 	protected $config = array('app_type' => 'master');

	function _search_filter(&$map) {
		if (!empty($_POST['keyword'])) {
			$map['grade_no|name'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}

	public function index() {
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		
		dump(getDefaultRoleIdsByUserId('3'));
		dump(getRoleIdsByUserId('3'));die;
		$model = M("DeptGrade");
		$list = $model -> order('sort') -> select();
		$this -> assign('list', $list);
		$this -> display();
	}

	function del() {
		$id = $_POST['id'];
		$this -> _destory($id);
	}

}
?>
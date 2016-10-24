<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class DeptGradeAction extends CommonAction {
	//app 类型
	protected $config = array('app_type' => 'master');

	function _search_filter(&$map) {
		if (!empty($_POST['keyword'])) {
			$map['grade_no|name'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}

	public function index() {
// 		dump(unserialize('a:1:{i:2;i:1;}'));die;
		$all = M('FlowHour')->where(array('hour'=>array('lt',0),'status'=>1))->select();
		foreach ($all as $k=>$v){
			$plan = getHourPlan($v['user_id'],$v['hour'],$v['create_time']);
			M('FlowHour')->where(array('id'=>$v['id']))->save(array('use'=>serialize($plan)));
		}
// 		die;
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
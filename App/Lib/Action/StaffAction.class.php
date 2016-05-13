<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class StaffAction extends CommonAction {
	//过滤查询字段
	protected $config=array('app_type'=>'common');
	private $position;
	private $rank;
	private $dept;

	function _search_filter(&$map) {
		$map['name'] = array('like', "%" . $_POST['name'] . "%");
		$map['letter'] = array('like', "%" . $_POST['letter'] . "%");
		$map['is_del'] = array('eq', '0');
		if (!empty($_POST['tag'])) {
			$map['group'] = $_POST['tag'];
		}
		$map['user_id'] = array('eq', get_user_id());
	}

	function index() {
		if(!is_mobile_request()){
			$this->assign("title",'职员查询');
			$node = D("Dept");
			$menu = array();
			$menu = $node -> field('id,pid,name') ->where("is_del=0")-> order('sort asc') -> select();
			$tree = list_to_tree($menu);
			$this -> assign('menu', popup_tree_menu($tree));
			$this -> display();
		}else{//手机端通讯录
			$prefix = C('DB_PREFIX');
			$sql = "select id,emp_no,name,letter,left(letter,1) as l,mobile_tel from {$prefix}user order by letter";
			$user = M('User') -> query($sql);
			$this -> assign('contacts', $user);
			$this -> display();
		}
		
	}
	
	function read() {
		$id = $_REQUEST['id'];
		$model = M("Dept");
		$dept = tree_to_list(list_to_tree(M("Dept") ->where('is_del=0')-> select(), $id));
		$dept = rotate($dept);
		$dept = implode(",", $dept['id']) . ",$id";
		
		$model = D("UserView");
		$where['is_del'] = array('eq', '0');
		$where['dept_id'] = array('in', $dept);
		$data = $model -> where($where) -> select();
		
		$this -> ajaxReturn($data, "", 1);
	}
	
}
?>
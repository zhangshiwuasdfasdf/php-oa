<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class DeptAction extends CommonAction {

	protected $config = array('app_type' => 'asst', 'action_auth' => array('index' => 'admin', 'winpop4' => 'read'));

	public function index(){
		
		$node = M("Dept");
		$menu = array();
		
		if($_REQUEST['tree']){
			$menu = $node -> where($map) -> field('id,pid,name,dept_no,is_del') -> where(array('is_del'=>'0','is_real_dept'=>'1')) -> order('sort asc') -> select();
			$tree = list_to_tree($menu);
			$this -> assign('dept_tree', $tree);
		}else{
			$menu = $node -> where($map) -> field('id,pid,name,is_del') -> where('is_del=0') -> order('sort asc') -> select();
			$tree = list_to_tree($menu);
		}
		
		$a = popup_tree_menu($tree);
		$a = str_replace('tree_menu','submenu',$a);
		$a = str_replace('<a class=""','<a class="dropdown-toggle"',$a);
		$a = preg_replace('/submenu/','nav-list',$a,1);
		$this -> assign('menu', $a);

		$model = M("Dept");
		$list = $model -> order('sort asc') -> getField('id,name');
		$this -> assign('dept_list', $list);

		$model = M("DeptGrade");
		$list = $model -> where('is_del=0') -> order('sort asc') -> getField('id,name');
		$this -> assign('dept_grade_list',$list);

		$this -> display();
	}

	public function del() {
		$id = $_POST['id'];
		$this -> _destory($id);

	}

	public function winpop() {
		$node = M("Dept");
		$menu = array();
		$menu = $node -> where('is_del=0') -> field('id,pid,name') -> order('sort asc') -> select();

		$tree = list_to_tree($menu);
		$this -> assign('menu', popup_tree_menu($tree));

		$this -> assign('pid', $pid);
		$this -> display();
	}

	public function winpop2() {//选择部门
		$this -> winpop();
	}
	
	public function winpop3() {//选择部门，包括..下
		$node = M("Dept");
		$menu = array();
		$menu = $node -> where('is_del=0') -> field('id,pid,name') -> order('sort asc') -> select();
		
		$tree = list_to_tree($menu);
		
// 		$tree = add_leaf($tree);
		
		$this -> assign('menu', popup_tree_menu($tree));

		$this -> assign('pid', $pid);
		$this -> display();
	}
	public function winpop4() {//选择部门，（狭义，只给3级）
		$node = M("Dept");
		$menu = array();
		$menu = $node -> where('is_del=0') -> field('id,pid,name') -> order('sort asc') -> select();
	
		$tree = list_to_tree($menu);
	
// 		$tree = add_leaf($tree);
	
		$this -> assign('menu', popup_tree_menu($tree));
		$this -> assign('type', $_GET['type']);
		$this -> assign('pid', $pid);
		$this -> display('winpop4');
	}
	
}
?>
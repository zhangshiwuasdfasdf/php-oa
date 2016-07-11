<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class GoodsCategoryAction extends CommonAction {

	protected $config = array('app_type' => 'master');

	public function index(){
		
		$node = M("GoodsCategory");
		$menu = array();
		$menu = $node -> where($map) -> field('id,pid,name,is_del') -> order('sort asc') -> select();
		$tree = list_to_tree($menu);
		$this -> assign('menu', popup_tree_menu($tree));

		$model = M("GoodsCategory");
		$list = $model -> order('sort asc') -> getField('id,name');
		$this -> assign('cate_list', $list);

		$model = M("GoodsCategoryGrade");
		$list = $model -> where('is_del=0') -> order('sort asc') -> getField('id,name');
		$this -> assign('cate_grade_list',$list);

		$this -> display();
	}

	public function del() {
		$id = $_POST['id'];
		$this -> _destory($id);

	}

	public function winpop() {
		$node = M("GoodsCategory");
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
		$node = M("GoodsCategory");
		$menu = array();
		$menu = $node -> where('is_del=0') -> field('id,pid,name') -> order('sort asc') -> select();
		
		$tree = list_to_tree($menu);
		
// 		$tree = add_leaf($tree);
		
		$this -> assign('menu', popup_tree_menu($tree));

		$this -> assign('pid', $pid);
		$this -> display();
	}
	public function winpop4() {//选择部门，（狭义，只给3级）
		$node = M("GoodsCategory");
		$menu = array();
		$menu = $node -> where('is_del=0') -> field('id,pid,name') -> order('sort asc') -> select();
	
		$tree = list_to_tree($menu);
	
// 		$tree = add_leaf($tree);
	
		$this -> assign('menu', popup_tree_menu($tree));
		$this -> assign('type', $_GET['type']);
		$this -> assign('pid', $pid);
		$this -> display('winpop4');
	}
	public function read($name = null, $id = null) {
		$model = M('GoodsCategory');
		if (empty($id)) {
			$id = $_REQUEST['id'];
		}
		if (empty($id)) {
			$this -> error(0, "读取失败", 0);
		}
		$vo = $model -> find($id);
		if ($this -> isAjax()) {
			if ($vo !== false) {// 读取成功
				$this -> ajaxReturn($vo, "读取成功", 1);
			} else {
				$this -> ajaxReturn(0, "读取失败", 0);
				die ;
			}
		}
		$this -> assign('vo', $vo);
		$this -> display();
	}
	
}
?>
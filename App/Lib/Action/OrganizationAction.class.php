<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class OrganizationAction extends CommonAction {

	protected $config = array('app_type' => 'asst', 'action_auth' => array('index' => 'read', 'winpop4' => 'read','changeContent'=>'read','getDept'=>'read'));

	public function index(){
		
		$node = M("Dept");
		$menu = array();
		$menu = $node -> where($map) -> field('id,pid,name,is_del') -> order('sort asc') -> select();
		$tree = list_to_tree($menu);
		
		$a = popup_tree_menu($tree);
		$a = str_replace('tree_menu','submenu',$a);
		$a = str_replace('<a class=""','<a class="dropdown-toggle"',$a);
		$a = preg_replace('/submenu/','nav-list',$a,1);
		$this -> assign('menu', $a);

		$model = M("Dept");
		$where['is_del'] = 0;
		if(!empty($_POST['dept_id'])){
			$where['id'] = array('in',get_child_dept_all($_POST['dept_id']));
		}
		$this->_list($model, $where,'',false,'list','page','p','_getRootDept');
		
		$list = $model ->where(array('is_del'=>0)) -> order('sort asc') -> getField('id,pid,name');
		$tree = list_to_tree($list);
		$html = popup_menu_organization($tree);
		$this -> assign('dept_list', $html);

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
	public function changeContent(){
		if($this->isAjax()){
			$p = !$_REQUEST['p']||$_REQUEST['p']<=0 ? 1 : intval($_REQUEST['p']);
			
			$model = M("Dept");
			$where['is_del'] = 0;
			if(!empty($_REQUEST['dept_id'])){
				$where['id'] = array('in',get_child_dept_all($_REQUEST['dept_id']));
			}
			$this->_list($model, $where,'',false,'list','page','p','_getRootDept');
			
			$list = $model->where($where)->page($p.',10')->select();
			$count = $model->where($where)->count();
			$data['list'] = $list;
			
			$data['p'] = $p;
			$data['count'] = $count;
			$data['total'] = $count%10 > 0 ? ceil($count/10) : $count/10;
			$this->ajaxReturn($data, '', 1);
		}
	}
	function getDept(){
		$where['is_del'] = '0';
		if(!empty($_REQUEST['dept_id'])){
			$where['id'] = array('in',get_child_dept_all($_REQUEST['dept_id']));
		}else{
			$where['pid'] = '0';
		}
		$dept = M('Dept')->where($where)->field('id,pid,name')->select();
		if($_REQUEST['type'] == 'origin'){
			$this->ajaxReturn($dept, '', 1);
		}elseif($_REQUEST['type'] == 'option'){
			$tree = list_to_tree($dept);
			$options = popup_menu_option($tree);
			$this->ajaxReturn($options, '', 1);
		}
		
	}
	function _getRootDept($volist){
		foreach ($volist as $k=>$v){
			$pid = $v['pid'];
			$id = $v['id'];
			while($pid){
				$id = $pid;
				$pid = M('Dept')->where(array('id'=>$pid))->getField('pid');
			}
			$volist[$k]['root_name'] = M('Dept')->where(array('id'=>$id))->getField('name');
		}
		return $volist;
	}
}
?>
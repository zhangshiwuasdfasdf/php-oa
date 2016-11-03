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
			$type = $_REQUEST['type'];
			if($type == '0'){//公司
				$model = M("Dept");
				$where['is_del'] = 0;
				if(!empty($_REQUEST['dept_id'])){
					$where['id'] = array('eq',$_REQUEST['dept_id']);
				}
				$list = $model->where($where)->page($p.',10')->select();
				$list = $this->_getRootDept($list);
				$count = $model->where($where)->count();
			}elseif ($type == '1'){//部门
				if(!$_REQUEST['show_leader']){
					$model = M("Dept");
					$where['is_del'] = 0;
					if(!empty($_REQUEST['dept_id'])){
						$where['id'] = array('in',get_child_dept_all($_REQUEST['dept_id']));
					}
					$list = $model->where($where)->page($p.',10')->select();
					$list = $this->_getRootDept($list);
					$count = $model->where($where)->count();
				}
				
			}elseif ($type == '2'){//岗位
				if(!$_REQUEST['show_leader']){
					$model = D("PositionView");
					if(!empty($_REQUEST['dept_id'])){
						$where['dept_id'] = array('in',get_child_dept_all($_REQUEST['dept_id']));
						$position_ids = M('RDeptPosition')->where($where)->getField('position_id',true);
						$list = M('Position')->where(array('id'=>array('in',$position_ids),'is_del'=>'0'))->page($p.',10')->select();
						$count = M('Position')->where(array('id'=>array('in',$position_ids),'is_del'=>'0'))->count();
					}
				}
			}elseif ($type == '3'){//员工
				if(!$_REQUEST['show_leader']){
// 					$where['is_del'] = 0;
					if(!empty($_REQUEST['dept_id'])){
						$where['dept_id'] = array('in',get_child_dept_all($_REQUEST['dept_id']));
					}
					$r_dept_user = M("RDeptUser")->where($where)->getField('user_id,dept_id');
					$user_ids = M("RDeptUser")->where($where)->getField('user_id',true);
					$list = M('User')->field('id,emp_no,name,sex,is_del')->where(array('id'=>array('in',$user_ids)))->page($p.',10')->select();
					foreach ($list as $k=>$v){
						$list[$k]['dept_id'] = $r_dept_user[$v['id']];
						$list[$k]['dept_name'] = M('Dept')->where(array('id'=>$list[$k]['dept_id']))->getField('name');
						$r_user_position = M('RUserPosition')->where(array('user_id'=>$v['id'],'dept_id'=>$list[$k]['dept_id']))->find();
						$list[$k]['position_id'] = $r_user_position['position_id'];
						$position_view = D('PositionView')->field('position_name,sequence_name')->where(array('id'=>$list[$k]['position_id']))->find();
						$list[$k]['position_name'] = $position_view['position_name'];
						$list[$k]['position_sequence'] = $position_view['sequence_name'];
						$list[$k]['major'] = $r_user_position['is_major']==1?'主要':'兼职';
						$list[$k]['is_del'] = $list[$k]['is_del']==1?'离职':'在职';
					}
					$count = M('User')->where(array('id'=>array('in',$user_ids)))->count();
				}else{
					
				}
			}
			$data['type'] = $type;
			$data['list'] = $list;
			
			$data['p'] = $p;
			$data['count'] = $count?$count:0;
			$data['total'] = $count%10 > 0 ? ceil($count/10) : $count/10;
			$data['total'] = $data['total']?$data['total']:1;
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
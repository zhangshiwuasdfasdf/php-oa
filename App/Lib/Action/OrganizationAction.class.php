<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class OrganizationAction extends CommonAction {

	protected $config = array('app_type' => 'asst', 'action_auth' => array('index' => 'read', 'winpop4' => 'read','changeContent'=>'read','getDept'=>'read','get_all_position'=>'read','get_edit_user_html'=>'read','change_dept_html'=>'read','change_position_html'=>'read','user_edit'=>'read','user_dept_position_set'=>'read','search_user'=>'read','r_dept_position_add'=>'read'));

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
// 						$list[$k]['company_name'] = getRootDept($list[$k]['dept_id'])['name'];
// 						$list[$k]['company_id'] = getRootDept($list[$k]['dept_id'])['id'];
// 						$list[$k]['all_company'] = $this->_get_all_company_html($list[$k]['company_id']);
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
	function get_all_position(){
		$where['is_del'] = '0';
		$position = M('Position')->where($where)->field('id,position_name')->select();
		$this->ajaxReturn($position, '', 1);
	
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
	function _get_all_company_html($company_id){
		$companys = M('Dept')->field('id,name')->where(array('pid'=>0))->select();
		$html = '<option>请选择公司</option>';
		foreach ($companys as $k=>$v){
			if($v['id'] == $company_id){
				$html .='<option selected="selected" value="'.$v['id'].'">'.$v['name'].'</option>';
			}else{
				$html .='<option value="'.$v['id'].'">'.$v['name'].'</option>';
			}
		}
		return $html;
	}
	function _get_dept_html($company_id,$dept_id){
		$depts = M('Dept')->field('id,pid,name')->where(array('id'=>array('in',get_child_dept_all($company_id))))->select();
		$tree = list_to_tree($depts);
		$html = popup_menu_option($tree,0,$dept_id);
		return '<option>请选择部门</option>'.$html;
	}
	function _get_position_html($dept_id,$position_id){
		$position_ids = M('RDeptPosition')->where(array('dept_id'=>$dept_id))->getField('position_id',true);
		
		$positions = M('Position')->field('id,position_name')->where(array('id'=>array('in',$position_ids)))->select();
		$html = '<option>请选择岗位</option>';
		foreach ($positions as $k=>$v){
			if($v['id'] == $position_id){
				$html .= '<option selected="selected" value="'.$v['id'].'">'.$v['position_name'].'</option>';
			}else{
				$html .= '<option value="'.$v['id'].'">'.$v['position_name'].'</option>';
			}
		}
		return $html;
	}
	function _get_major_html($user_id,$position_id){
		$is_major = M('RUserPosition')->where(array('user_id'=>$user_id,'position_id'=>$position_id))->getField('is_major');
		$html = '';
		if($is_major == '1'){
			$html .= '<option selected="selected" value="1">主要</option>';
			$html .= '<option value="0">兼职</option>';
		}else{
			$html .= '<option value="1">主要</option>';
			$html .= '<option selected="selected" value="0">兼职</option>';
		}
		return $html;
	}
	function get_edit_user_html(){
		$company_id = getRootDept($_POST['dept_id'])['id'];
		$data['company'] = $this->_get_all_company_html($company_id);
		$data['dept'] = $this->_get_dept_html($company_id,$_POST['dept_id']);
		$data['position'] = $this->_get_position_html($_POST['dept_id'],$_POST['position_id']);
		$data['major'] = $this->_get_major_html($_POST['user_id'],$_POST['position_id']);
		$this->ajaxReturn($data,1,1);
	}
	function change_dept_html(){
		$data['dept'] = $this->_get_dept_html($_POST['company_id']);
		$this->ajaxReturn($data,1,1);
	}
	function change_position_html(){
		$data['position'] = $this->_get_position_html($_POST['dept_id']);
		$this->ajaxReturn($data,1,1);
	}
	function change_sequence_html(){
		$sequence_id = M('Position')->where(array('id'=>$_POST['position_id']))->getField('position_sequence_id');
		$sequence_name = M('PositionSequence')->where(array('id'=>$sequence_id))->getField('sequence_name');
		$data['sequence_id'] = $sequence_id;
		$data['sequence_name'] = $sequence_name;
		$this->ajaxReturn($data,1,1);
	}
	function user_edit(){
		$res_user = M('User')->where(array('id'=>$_POST['user_user_id']))->save(array('name'=>$_POST['user_user_name']));
		$res_r_dept_user = M('RDeptUser')->where(array('dept_id'=>$_POST['user_origin_dept_id'],'user_id'=>$_POST['user_user_id']))->save(array('dept_id'=>$_POST['user_dept']));
		$res_r_user_position = M('RUserPosition')->where(array('position_id'=>$_POST['user_origin_position_id'],'user_id'=>$_POST['user_user_id']))->save(array('position_id'=>$_POST['user_position'],'is_major'=>$_POST['user_major'],'dept_id'=>$_POST['user_dept']));
		if(false !== $res_user && false !== $res_r_dept_user && false !== $res_r_user_position){
			$this->success('修改成功');
		}else{
			$this->error('修改失败');
		}
	}
	function user_dept_position_set(){
		$dept_id = $_POST['user_dept'];
		$user_id = $_POST['user_user_id'];
		$position_id = $_POST['user_position'];
		$is_major = $_POST['user_major'];
		$find = M('RDeptUser')->where(array('dept_id'=>$dept_id,'user_id'=>$user_id))->find();
		if(false != $find){
			$this->error('此部门下已有此人！');
		}else{
			$find = M('RUserPosition')->where(array('position_id'=>$position_id,'user_id'=>$user_id))->find();
			if(false != $find){
				$this->error('此岗位下已有此人！');
			}else{
				$res1 = M('RDeptUser')->add(array('dept_id'=>$dept_id,'user_id'=>$user_id));
				$res2 = M('RUserPosition')->add(array('position_id'=>$position_id,'user_id'=>$user_id,'is_major'=>$is_major,'dept_id'=>$dept_id));
				if(false !== $res1 && false !== $res2){
					$this->success('添加成功');
				}else{
					$this->error('添加失败');
				}
			}
		}
	}
	function search_user(){
		$where['emp_no|name'] = array('like','%'.$_POST['keywords'].'%');
		$res = M('User')->field('id,emp_no,name')->where($where)->select();
		$this->ajaxReturn($res,1,1);
	}
	function r_dept_position_add(){
		$find = M('RDeptPosition')->where(array('dept_id'=>$_POST['position_dept'],'position_id'=>$_POST['position_position']));
		dump($_POST);die;
	}
}
?>
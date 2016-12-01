<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class OrganizationAction extends CommonAction {

	protected $config = array('app_type' => 'asst', 'action_auth' => array('index' => 'read', 'winpop4' => 'read','changeContent'=>'read','getDept'=>'read','get_all_position'=>'read','get_edit_user_html'=>'read','get_edit_dept_html'=>'read','change_dept_html'=>'read','change_position_html'=>'read','user_edit'=>'read','user_dept_position_set'=>'read','search_user'=>'read','r_dept_position_add'=>'read','r_dept_position_edit'=>'read','delete'=>'read','edit_is_use'=>'read','dept_add'=>'read','dept_edit'=>'read','get_role_groupby_company'=>'read','get_user_position_role_html'=>'read','get_admin_jurisdiction_html'=>'read','get_business_jurisdiction_html'=>'read','get_business_base_html'=>'read','get_attendance_dept_html'=>'read','distribution_position_to_role'=>'read','distribution_admin_jurisdiction'=>'read','distribution_business_jurisdiction'=>'read','distribution_attendance_dept'=>'read','validate'=>'read'));

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
		
		$list = $model ->where(array('is_del'=>0,'is_use'=>'1')) -> order('sort asc') -> getField('id,pid,name');
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
// 			$p_dept = !$_REQUEST['p_dept']||$_REQUEST['p_dept']<=0 ? 1 : intval($_REQUEST['p_dept']);
// 			$p_position = !$_REQUEST['p_position']||$_REQUEST['p_position']<=0 ? 1 : intval($_REQUEST['p_position']);
// 			$p_user = !$_REQUEST['p_user']||$_REQUEST['p_user']<=0 ? 1 : intval($_REQUEST['p_user']);
			$type = $_REQUEST['type'];
			
			$p = !$_REQUEST['p']||$_REQUEST['p']<=0 ? 1 : intval($_REQUEST['p']);
			
			if(empty($type) || $type == 'company'){
				$model = M("Dept");
				$where = array();
				$where['is_del'] = 0;
				if(!empty($_REQUEST['dept_id'])){
					$where['id'] = array('eq',$_REQUEST['dept_id']);
				}
				$list_company = $model->where($where)->page($p.',10')->select();
				$list_company = $this->_getRootDept($list_company);
				$count_company = $model->where($where)->count();
			}
			if(empty($type) || $type == 'dept'){
				if(!$_REQUEST['show_leader']){
					$model = M("Dept");
					$where = array();
					$where['is_del'] = 0;
					if(!empty($_REQUEST['dept_id'])){
						/*
						 * 改为部门直接下属部门，而不是子孙部门
						 */
// 						$where['id'] = array('in',get_child_dept_all($_REQUEST['dept_id']));
						$where['pid'] = array('eq',$_REQUEST['dept_id']);
					}
					$list_dept = $model->where($where)->page($p.',10')-> order('is_use desc,sort asc')->select();
					$list_dept = $this->_getRootDept($list_dept);
					$count_dept = $model->where($where)->count();
				}
			}
			if(empty($type) || $type == 'position'){
				if(!$_REQUEST['show_leader']){
					$model = D("PositionView");
					$where = array();
					if(!empty($_REQUEST['dept_id'])){
						/*
						 * 改为部门直接下属岗位，而不是子孙岗位
						 */
// 						$where['dept_id'] = array('in',get_child_dept_all($_REQUEST['dept_id']));
						$where['dept_id'] = array('eq',$_REQUEST['dept_id']);
						$position_ids = M('RDeptPosition')->where($where)->getField('position_id',true);
						$position_dept = M('RDeptPosition')->where($where)->getField('position_id,dept_id');
						$list_position = M('Position')->where(array('id'=>array('in',$position_ids),'is_del'=>'0'))->page($p.',10')->select();
						foreach ($list_position as $k=>$v){
							$list_position[$k]['dept_id'] = $position_dept[$v['id']];
							$list_position[$k]['dept_name'] = M('Dept')->where(array('id'=>$list_position[$k]['dept_id']))->getField('name');
							$role_ids = M('RPositionRole')->where(array('position_id'=>$v['id']))->getField('role_id',true);
							$role_names = M('RoleManager')->where(array('id'=>array('in',$role_ids)))->getField('role_name',true);
							$list_position[$k]['default_role'] = implode(',', $role_names)?implode(',', $role_names):'暂无分配';
						}
						$count_position = M('Position')->where(array('id'=>array('in',$position_ids),'is_del'=>'0'))->count();
					}
				}
			}
			if(empty($type) || $type == 'user'){
				if(!$_REQUEST['show_leader']){
					$where = array();
// 					$where['is_del'] = 0;
					if(!empty($_REQUEST['dept_id'])){
						$where_user['dept_id'] = array('in',get_child_dept_all($_REQUEST['dept_id']));
// 						$where['dept_id'] = array('eq',$_REQUEST['dept_id']);
// 						$where_user['dept_id'] = $where['dept_id'];
					}
					if('' != $_REQUEST['is_part_time_job']){
						if($_REQUEST['is_part_time_job'] == '0'){
							$where_user['is_major'] = '1';
						}elseif($_REQUEST['is_part_time_job'] == '1'){
							$where_user['is_major'] = '0';
						}
					}
// 					$user_ids3 = M('RUserPosition')->where($where_r_user_position)->getField('user_id',true);
// 					$r_dept_user = M("RDeptUser")->where($where)->getField('user_id,dept_id');
// 					$user_ids1 = M("RDeptUser")->where($where)->getField('user_id',true);
					
					if(!empty($_REQUEST['status'])){
						$name_array = array('实习生','试用期','已转正','拟离职','离职');
						$where_status_manage_part = array();
						foreach ((explode(',', $_REQUEST['status'])) as $k=>$v){
							$where_status_manage_part[] = $name_array[$v];
						}
						$where_user['stuff_status'] = array('in',array_filter($where_status_manage_part));
					}else{
						$where_user['stuff_status'] = array('in',array('实习生','试用期','已转正','拟离职'));
					}
// 					$user_ids2 = M("StatusManage")->where($where_status_manage)->getField('user_id',true);
// 					$user_ids_status = M('StatusManage')->where($where_status_manage)->getField('user_id,stuff_status');
// 					$user_ids = array_intersect($user_ids1,$user_ids2,$user_ids3);
// 					$where_user['id'] = array('in',$user_ids);
					if(!empty($_REQUEST['name_no'])){
						$keyword = preg_replace('/^0+/','',trim($_REQUEST['name_no']));
						$where_user['RUserPosition.user_id|User.name|User.emp_no'] = array('like','%'.$keyword.'%');
					}
					
					$list_user = D('UserPositionView')->where($where_user)->select();
					
					foreach ($list_user as $k=>$v){
						$list_user[$k]['no'] = formatto4w($v['user_id']).'_'.$v['name'];
// 						$list_user[$k]['dept_id'] = $r_dept_user[$v['id']];
// 						$list_user[$k]['dept_name'] = M('Dept')->where(array('id'=>$list_user[$k]['dept_id']))->getField('name');
// 						$r_user_position = M('RUserPosition')->where(array('user_id'=>$v['id'],'dept_id'=>$list_user[$k]['dept_id']))->find();
// 						$position_view = D('UserPositionView')->field('id,is_major,position_id,position_name,sequence_name')->where(array('user_id'=>$v['id'],'dept_id'=>$list_user[$k]['dept_id']))->find();
						
// 						$list_user[$k]['position_id'] = $position_view['position_id'];
// 						$list_user[$k]['position_name'] = $position_view['position_name'];
						$list_user[$k]['position_sequence'] = $v['sequence_name'];
						$list_user[$k]['major'] = $v['is_major']==1?'主要':'兼职';
// 						$list_user[$k]['is_del'] = $list_user[$k]['is_del']==1?'离职':'在职';
						$list_user[$k]['status'] = $v['stuff_status'];
						$list_user[$k]['upid'] = $v['id'];
// 						$list_user[$k]['company_name'] = getRootDept($list[$k]['dept_id'])['name'];
// 						$list_user[$k]['company_id'] = getRootDept($list[$k]['dept_id'])['id'];
// 						$list_user[$k]['all_company'] = $this->_get_all_company_html($list[$k]['company_id']);
					}
					$sorted_list_user = array();
					foreach ($list_user as $k=>$v){
						if($v['status'] != '离职'){
							$sorted_list_user[] = $v;
							unset($list_user[$k]);
						}
					}
					foreach ($list_user as $k=>$v){
						$sorted_list_user[] = $v;
					}
					$count_user = M('User')->where(array('id'=>array('in',$where_user)))->count();
				}else{
					
				}
			}	
			$data['type'] = $type;
			$data['list_dept'] = $list_dept;
			$data['list_position'] = $list_position;
			$data['list_user'] = $sorted_list_user;
			
			//以下的当前页只有一个对，但是前端不采用，所以无影响
			$data['p_dept'] = $p;
			$data['p_position'] = $p;
			$data['p_user'] = $p;
			
			$data['count_dept'] = $count_dept?$count_dept:0;
			$data['count_position'] = $count_position?$count_position:0;
			$data['count_user'] = $count_user?$count_user:0;
			
			$data['total_dept'] = $count_dept%10 > 0 ? ceil($count_dept/10) : $count_dept/10;
			$data['total_dept'] = $data['total_dept']?$data['total_dept']:1;
			
			$data['total_position'] = $count_position%10 > 0 ? ceil($count_position/10) : $count_position/10;
			$data['total_position'] = $data['total_position']?$data['total_position']:1;
			
			$data['total_user'] = $count_user%10 > 0 ? ceil($count_user/10) : $count_user/10;
			$data['total_user'] = $data['total_user']?$data['total_user']:1;
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
		$depts = M('Dept')->field('id,pid,name')->where(array('id'=>array('in',get_child_dept_all($company_id)),'is_del'=>'0','is_use'=>'1'))->select();
		$tree = list_to_tree($depts);
		$html = popup_menu_option($tree,0,$dept_id);
		return '<option>请选择部门</option>'.$html;
	}
	function _get_position_html($dept_id,$position_id){
		$position_ids = M('RDeptPosition')->where(array('dept_id'=>$dept_id))->getField('position_id',true);
		
		$positions = M('Position')->field('id,position_name')->where(array('id'=>array('in',$position_ids),'is_del'=>'0','is_use'=>'1'))->select();
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
	function _get_sequence_html($user_id,$position_id){
		$position_sequence_id = M('RUserPosition')->where(array('user_id'=>$user_id,'position_id'=>$position_id))->getField('position_sequence_id');
		$all_sequence = M('PositionSequence')->select();
		$html = '';
		foreach ($all_sequence as $k=>$v){
			if($position_sequence_id == $v['id']){
				$html .= '<option selected="selected" value="'.$v['id'].'">'.$v['sequence_name'].'</option>';
			}else{
				$html .= '<option value="'.$v['id'].'">'.$v['sequence_name'].'</option>';
			}
		}
		return $html;
	}
	function get_edit_user_html(){
		$company_id = getRootDept($_POST['dept_id'])['id'];
		$data['company'] = $this->_get_all_company_html($company_id);
		$data['dept'] = $this->_get_dept_html($company_id,$_POST['dept_id']);
		$data['position'] = $this->_get_position_html($_POST['dept_id'],$_POST['position_id']);
		$data['major'] = $this->_get_major_html($_POST['user_id'],$_POST['position_id']);
		$data['sequence'] = $this->_get_sequence_html($_POST['user_id'],$_POST['position_id']);
		$this->ajaxReturn($data,1,1);
	}
	function get_edit_dept_html(){
		$company_id = getRootDept($_POST['dept_id'])['id'];
		$data['company'] = $this->_get_all_company_html($company_id);
		$dept = M('Dept')->find($_POST['dept_id']);
		$pid = $dept['pid'];
		$data['dept_name'] = $dept['name'];
		$data['sort'] = $dept['sort'];
		$num_to_zh_cn = array('无','一级部门','二级部门','三级部门','四级部门','五级部门','六级部门');
		$dept_grade_html = '';
		foreach ($num_to_zh_cn as $k=>$v){
			if($dept['dept_grade_id'] == $k){
				$dept_grade_html .= '<option value="'.$k.'" selected="selected">'.$v.'</option>';
			}else{
				$dept_grade_html .= '<option value="'.$k.'">'.$v.'</option>';
			}
		}
		if($dept['is_use'] == '1'){
			$is_use_html .= '<option value="1" selected="selected">启用</option><option value="0">禁用</option>';
		}else{
			$is_use_html .= '<option value="1">启用</option><option value="0" selected="selected">禁用</option>';
		}
		$data['dept_grade'] = $dept_grade_html;
		$data['is_use'] = $is_use_html;
		$data['dept_parent'] = $this->_get_dept_html($company_id,$pid);
		
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
		$find = M('RUserPosition')->where(array('is_major'=>'1','user_id'=>$_POST['user_user_id'],'position_id'=>array('neq',$_POST['user_origin_position_id'])))->find();
		if(false != $find && $_POST['user_major'] == '1'){
			$this->error('此人已有主要岗位，只能添加兼职岗位');
		}else{
			$res_user = M('User')->where(array('id'=>$_POST['user_user_id']))->save(array('name'=>$_POST['user_user_name']));
			$res_r_dept_user = M('RDeptUser')->where(array('dept_id'=>$_POST['user_origin_dept_id'],'user_id'=>$_POST['user_user_id']))->save(array('dept_id'=>$_POST['user_dept']));
			$res_r_user_position = M('RUserPosition')->where(array('position_id'=>$_POST['user_origin_position_id'],'user_id'=>$_POST['user_user_id']))->save(array('position_id'=>$_POST['user_position'],'is_major'=>$_POST['user_major'],'dept_id'=>$_POST['user_dept'],'position_sequence_id'=>$_POST['user_position_sequence_id']));
			if(false !== $res_user && false !== $res_r_dept_user && false !== $res_r_user_position){
				$this->success('修改成功');
			}else{
				$this->error('修改失败');
			}
		}
	}
	function user_dept_position_set(){
		$user_id = $_POST['user_user_id'];
		if(!$user_id){
			$this->error('此人不存在！');
		}
		$dept_id = $_POST['user_dept'];
		$position_id = $_POST['user_position'];
		$is_major = $_POST['user_major'];
		$sequence_id = $_POST['user_position_sequence_id'];
		$find = M('RDeptUser')->where(array('dept_id'=>$dept_id,'user_id'=>$user_id))->find();
		if(false != $find){
			$this->error('此部门下已有此人！');
		}else{
			$find = M('RUserPosition')->where(array('position_id'=>$position_id,'user_id'=>$user_id))->find();
			$find2 = M('RUserPosition')->where(array('is_major'=>'1','user_id'=>$user_id))->find();
			if(false != $find){
				$this->error('此岗位下已有此人！');
			}elseif(false != $find2 && $is_major == '1'){
				$this->error('此人已有主要岗位，只能添加兼职岗位！');
			}else{
				$res1 = M('RDeptUser')->add(array('dept_id'=>$dept_id,'user_id'=>$user_id));
				$res2 = M('RUserPosition')->add(array('position_id'=>$position_id,'user_id'=>$user_id,'is_major'=>$is_major,'dept_id'=>$dept_id,'position_sequence_id'=>$sequence_id));
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
		$find = M('RDeptPosition')->where(array('dept_id'=>$_POST['position_dept'],'position_id'=>$_POST['position_position']))->find();
		if(false != $find){
			$this->error('此部门下已有此岗位！');
		}else{
			$res = M('RDeptPosition')->add(array('dept_id'=>$_POST['position_dept'],'position_id'=>$_POST['position_position']));
			if($res){
				$this->success('添加成功');
			}else{
				$this->error('添加失败');
			}
		}
	}
	function r_dept_position_edit(){
		if(empty($_GET['origin_dept_id']) || empty($_GET['origin_position_id'])){
			$this->error('修改失败');
		}
		$res = M('RDeptPosition')->where(array('dept_id'=>$_GET['origin_dept_id'],'position_id'=>$_GET['origin_position_id']))->save(array('dept_id'=>$_POST['position_dept'],'position_id'=>$_POST['position_position']));
		if(false !== $res){
			$this->success('修改成功');
		}else{
			$this->error('修改失败');
		}
	}
	function delete(){
		if($_GET['type'] == '1'){
			$res = M('Dept')->where(array('id'=>array('in',$_POST['box_dept'])))->save(array('is_del'=>'1'));
		}elseif($_GET['type'] == '2'){
			if(!empty($_POST['box_position']) && is_array($_POST['box_position'])){
				foreach ($_POST['box_position'] as $r_position_dept){
					$r_position_dept_arr = explode('_', $r_position_dept);
					$position_id = $r_position_dept_arr[0];
					$dept_id = $r_position_dept_arr[1];
					$res = M('RDeptPosition')->where(array('dept_id'=>$dept_id,'position_id'=>$position_id))->delete();
					if(false === $res){
						break;
					}
				}
			}
		}elseif($_GET['type'] == '3'){
			if(!empty($_POST['box_user']) && is_array($_POST['box_user'])){
				foreach ($_POST['box_user'] as $r_user_dept_position){
					$r_user_dept_position_arr = explode('_', $r_user_dept_position);
					$user_id = $r_user_dept_position_arr[0];
					$dept_id = $r_user_dept_position_arr[1];
					$position_id = $r_user_dept_position_arr[2];
					$upid = M('RUserPosition')->where(array('user_id'=>$user_id,'position_id'=>$position_id))->getField('id');
					$res = M('RUserPosition')->delete($upid);
					$res1 = M('RDeptUser')->where(array('user_id'=>$user_id,'dept_id'=>$dept_id))->delete();
					$res2 = M('RUserPositionRole')->where(array('upid'=>$upid))->delete();
					if(false === $res || false === $res1 || false === $res2){
						break;
					}
				}
			}
		}
		if(false !== $res){
			$this->success('删除成功');;
		}else{
			$this->error('删除失败');
		}
	}
	function dept_add(){
		if(!empty($_POST['dept_dept_parent']) && !empty($_POST['dept_dept'])){
			
			$data['pid'] = $_POST['dept_dept_parent'];
			$data['name'] = $_POST['dept_dept'];
			$find = M('Dept')->where($data)->find();
			if($find){
				$this->error('在此部门的上级部门下面已有此部门');
			}
			
			$data['dept_grade_id'] = $_POST['dept_dept_degree'];
			$last_dept_no = M('Dept')->where(array('dept_no'=>array('like','D%')))->order('dept_no desc')->limit(1)->getField('dept_no');
			$data['dept_no'] = 'D'.formatto4w(intval(substr($last_dept_no, 1))+1);
			
// 			$data['dept_no'] = $_POST['dept_no'];
			$data['is_del'] = '0';
			$data['is_use'] = $_POST['dept_dept_is_use'];
			$data['sort'] = $_POST['dept_sort_add'];
			
			
			$res = M('Dept')->add($data);
			if(false !== $res){
				$this->success('新增成功');
			}else{
				$this->error('新增失败');
			}
		}else{
			$this->error('新增失败');
		}
	}
	function dept_edit(){
		if($_POST['pid'] == $_POST['edit_dept_id']){
			$this->error('上级部门不能是自己');
		}else if(!empty($_POST['pid']) && !empty($_POST['dept_name']) && !empty($_POST['edit_dept_id'])){
			$data['pid'] = $_POST['pid'];
			$data['name'] = $_POST['dept_name'];
			$find = M('Dept')->where($data)->find();
			if($find){
				$this->error('在该上级部门下已有该部门');
			}
// 			$data['dept_no'] = $_POST['dept_no'];
			$data['dept_grade_id'] = $_POST['dept_grade_id'];
			$data['is_del'] = '0';
			$data['is_use'] = $_POST['dept_is_use'];
			$data['sort'] = $_POST['dept_sort'];
			$res = M('Dept')->where(array('id'=>$_POST['edit_dept_id']))->save($data);
			if(false !== $res){
				$this->success('修改成功');
			}else{
				$this->error('修改失败');
			}
		}else{
			$this->error('请填写部门相关信息');
		}
	}
	function get_role_groupby_company(){
		$position_id = $_POST['position_id'];
		$where['is_del'] = '0';
		$role = M('RoleManager')->where($where)->select();
		$new_role = array();
		foreach ($role as $k=>$v){
			$new_role[$v['company']][] = $v;
		}
		$role_html = '';
		foreach ($new_role as $k=>$v){
			$role_html .='<div class="tc_div_jt">'.$k.'：</div><ul class="tc_ul"><li>';
			foreach($new_role[$k] as $kk=>$vv){
				$res = M('RPositionRole')->where(array('position_id'=>$position_id,'role_id'=>$vv['id']))->find();
				$is_check = $res?'checked="checked"':'';
				$role_html .= '<span>';
				$role_html .= '<input type="checkbox" id="role_'.$vv['id'].'" name="role[]" value="'.$vv['id'].'" '.$is_check.'/>';
				$role_html .= '<label for="role_'.$vv['id'].'">'.$vv['role_name'].'</label>';
				$role_html .= '</span>';
			}
			$role_html .='</li></ul>';
		}
		
// 		$role=M("RoleManager")->field("group_concat(id) id,role_no,company,group_concat(role_name) role_name,status,is_del")->where($where)->group('company_id')->select();
		$this->ajaxReturn($role_html);
	}
	function get_user_position_role_html(){
		$upid = $_POST['upid'];
		$where['is_del'] = '0';
		$role = M('RoleManager')->where($where)->select();
		$new_role = array();
		foreach ($role as $k=>$v){
			$new_role[$v['company']][] = $v;
		}
		$set = getRoleIdsByUpid($upid);
// 		$set = M('RUserPositionRole')->where(array('upid'=>$upid))->getField('role_id',true);
		$role_html = '';
		foreach ($new_role as $k=>$v){
			$role_html .='<div class="tc_div_jt">'.$k.'：</div><ul class="tc_ul"><li>';
			foreach($new_role[$k] as $kk=>$vv){
				if(empty($set)){
// 					$position_ids = M('RUserPosition')->where(array('user_id'=>$user_id))->getField('position_id',true);
// 					$res = M('RPositionRole')->where(array('position_id'=>array('in',$position_ids),'role_id'=>$vv['id']))->find();
					$res = false;
				}else{
					if(in_array($vv['id'], $set)){
						$res = true;
					}else{
						$res = false;
					}
				}
				$is_check = $res?'checked="checked"':'';
				$role_html .= '<span>';
				$role_html .= '<input type="checkbox" id="role_'.$vv['id'].'" name="role[]" value="'.$vv['id'].'" '.$is_check.'/>';
				$role_html .= '<label for="role_'.$vv['id'].'">'.$vv['role_name'].'</label>';
				$role_html .= '</span>';
			}
			$role_html .='</li></ul>';
		}
		$this->ajaxReturn($role_html);
	}
	function get_admin_jurisdiction_html(){
		$upid = $_POST['upid'];
		$child_depts = M('RUserPositionDeptPosition')->where(array('upid'=>$upid,'_string'=>'dept_id is not null and position_id is null'))->getField('dept_id',true);
		$child_positions = M('RUserPositionDeptPosition')->field('dept_id,position_id')->where(array('upid'=>$upid,'_string'=>'dept_id is not null and position_id is not null'))->select();
		$list = M('Dept')->field('id,pid,name') ->where(array('is_del'=>0)) -> order('sort asc') -> select();
		$dept_ids = M('Dept') ->where(array('is_del'=>0)) -> order('sort asc') -> getField('id',true);
		$list2 = D('DeptPositionView')->field('dept_id as pid,position_id as id,position_name as name')->where(array('dept_id'=>array('in',$dept_ids),'Position.is_del'=>'0','Position.is_use'=>'1'))->select();
		foreach ($list2 as $k=>$v){
			$list2[$k]['id'] = 'p_'.$list2[$k]['pid'].'_'.$list2[$k]['id'];
		}
		$tree = list_to_tree(array_merge($list,$list2));
		$html = popup_menu_dept_position_checkbox($tree,0,100,$child_depts,$child_positions);
		$this->ajaxReturn($html);
	}
	function get_business_jurisdiction_html(){
		$upid = $_POST['upid'];
		$child_depts = M('RUserPositionDept')->where(array('upid'=>$upid))->getField('dept_id',true);
		$list = M('Dept')->field('id,pid,name') ->where(array('is_del'=>0)) -> order('sort asc') -> select();
		$tree = list_to_tree($list);
		$html = popup_menu_dept_position_checkbox($tree,0,100,$child_depts);
		$this->ajaxReturn($html);
	}
	function get_business_base_html(){
		$upid = $_POST['upid'];
		$child_depts = M('RUserPositionBase')->where(array('upid'=>$upid))->getField('dept_id',true);
		$list = M('Dept')->field('id,pid,name') ->where(array('is_del'=>'0','pid'=>'0')) -> order('sort asc') -> select();
		$tree = list_to_tree($list);
		$html = popup_menu_dept_position_checkbox($tree,0,100,$child_depts);
		$this->ajaxReturn($html);
	}
	function get_attendance_dept_html(){
		$upid = $_POST['upid'];
		$child_depts = M('RUserPositionAttendanceDept')->where(array('upid'=>$upid))->getField('dept_id',true);
		$list = M('Dept')->field('id,pid,name') ->where(array('is_del'=>0)) -> order('sort asc') -> select();
		$tree = list_to_tree($list);
		$html = popup_menu_dept_position_checkbox($tree,0,100,$child_depts);
		$this->ajaxReturn($html);
	}
	function distribution_position_to_role(){
		$position_id = $_POST['position_id'];
		$role_ids = $_POST['role']?$_POST['role']:'';
		M('RPositionRole')->where(array('position_id'=>$position_id,'role_id'=>array('not in',$role_ids)))->delete();
		foreach ($role_ids as $k=>$role_id){
			$res = M('RPositionRole')->where(array('position_id'=>$position_id,'role_id'=>array('eq',$role_id)))->find();
			if(empty($res)){
				$res1 = M('RPositionRole')->add(array('position_id'=>$position_id,'role_id'=>$role_id));
				if(false === $res1){
					$this->error('分配失败');
				}
			}
		}
		$this->success('分配成功');
	}
	function distribution_user_role(){
		$upid = $_POST['user_role_upid'];
		$role_ids = $_POST['role']?$_POST['role']:'';
		$default_role_id = getDefaultRoleIdsByUpid($upid);
		$default_role_id = $default_role_id?$default_role_id:array();
		$role_ids = array_diff($role_ids,$default_role_id);
		$role_ids = empty($role_ids)?'':$role_ids;
		
		M('RUserPositionRole')->where(array('upid'=>$upid,'role_id'=>array('not in',$role_ids)))->delete();
		foreach ($role_ids as $k=>$role_id){
			$res = M('RUserPositionRole')->where(array('upid'=>$upid,'role_id'=>array('eq',$role_id)))->find();
			if(empty($res)){
				$res1 = M('RUserPositionRole')->add(array('upid'=>$upid,'role_id'=>$role_id));
				if(false === $res1){
					$this->error('分配失败');
				}
			}
		}
		$this->success('分配成功');
	}
	function distribution_admin_jurisdiction(){
		$upid = $_POST['admin_jurisdiction_upid'];
		$dept_id = array('');
		$dept_position_id = array('');
		foreach ($_POST['dept'] as $k=>$v){
			if(substr($v, 0,1) == 'p'){
				$arr = explode('_', $v);
				$dept_position_id[] = array('dept_id'=>$arr[1],'position_id'=>$arr[2]);
			}else{
				$dept_id[] = $v;
			}
		}
		M('RUserPositionDeptPosition')->where(array('upid'=>$upid,'dept_id'=>array('not in',$dept_id),'_string'=>'position_id is null'))->delete();
		M('RUserPositionDeptPosition')->where(array('upid'=>$upid,'_string'=>'position_id is not null'))->delete();
		foreach ($dept_id as $k=>$v){
			if($v != ''){
				$find = M('RUserPositionDeptPosition')->where(array('upid'=>$upid,'dept_id'=>$v,'_string'=>'position_id is null'))->find();
				if(!$find){
					$res = M('RUserPositionDeptPosition')->add(array('upid'=>$upid,'dept_id'=>$v));
					if(!$res){
						$this->error('分配失败');
					}
				}
			}
		}
		foreach ($dept_position_id as $k=>$v){
			if($v != ''){
// 				$find = M('RUserPositionDeptPosition')->where(array('upid'=>$upid,'dept_id'=>$v['dept_id'],'position_id'=>$v['position_id']))->find();
// 				if(!$find){
					$res = M('RUserPositionDeptPosition')->add(array('upid'=>$upid,'dept_id'=>$v['dept_id'],'position_id'=>$v['position_id']));
					if(!$res){
						$this->error('分配失败');
					}
// 				}
			}
		}
		$this->success('分配成功');
	}
	function distribution_business_jurisdiction(){
		$upid = $_POST['business_jurisdiction_upid'];
		$dept_id = $_POST['dept']?$_POST['dept']:array('');
		M('RUserPositionDept')->where(array('upid'=>$upid,'dept_id'=>array('not in',$dept_id)))->delete();
		foreach ($dept_id as $k=>$v){
			if($v != ''){
				$find = M('RUserPositionDept')->where(array('upid'=>$upid,'dept_id'=>$v))->find();
				if(!$find){
					$res = M('RUserPositionDept')->add(array('upid'=>$upid,'dept_id'=>$v));
					if(!$res){
						$this->error('分配失败');
					}
				}
			}
		}
		$this->success('分配成功');
	}
	function distribution_business_base(){
		$upid = $_POST['business_base_upid'];
		$dept_id = $_POST['dept']?$_POST['dept']:array('');
		M('RUserPositionBase')->where(array('upid'=>$upid,'dept_id'=>array('not in',$dept_id)))->delete();
		foreach ($dept_id as $k=>$v){
			if($v != ''){
				$find = M('RUserPositionBase')->where(array('upid'=>$upid,'dept_id'=>$v))->find();
				if(!$find){
					$res = M('RUserPositionBase')->add(array('upid'=>$upid,'dept_id'=>$v));
					if(!$res){
						$this->error('分配失败');
					}
				}
			}
		}
		$this->success('分配成功');
	}
	function distribution_attendance_dept(){
		$upid = $_POST['attendance_dept_upid'];
		$dept_id = $_POST['dept']?$_POST['dept']:array('');
		M('RUserPositionAttendanceDept')->where(array('upid'=>$upid,'dept_id'=>array('not in',$dept_id)))->delete();
		foreach ($dept_id as $k=>$v){
			if($v != ''){
				$find = M('RUserPositionAttendanceDept')->where(array('upid'=>$upid,'dept_id'=>$v))->find();
				if(!$find){
					$res = M('RUserPositionAttendanceDept')->add(array('upid'=>$upid,'dept_id'=>$v));
					if(!$res){
						$this->error('分配失败');
					}
				}
			}
		}
		$this->success('分配成功');
	}
	public function validate($model=''){
		if($this->isAjax()){
			if(!$this->_request('clientid','trim') || !$this->_request($this->_request('clientid','trim'),'trim')){
				$this->ajaxReturn("","",3);
			}
	
			$where[$this->_request('clientid','trim')] = array('eq',$this->_request($this->_request('clientid','trim'),'trim'));
			//针对编辑的情况
			if($this->_request('id','intval',0)){
				$where[M('Position')->getpk()] = array('neq',$this->_request('id','intval',0));
			}
	
			if(!$where['name'] && $this->_request('dept_dept','trim')){
				$where['name'] = $this->_request('dept_dept','trim');
				unset($where['dept_dept']);
			}
			if(!$where['pid'] && $this->_request('pid','trim')){
				$where['pid'] = $this->_request('pid','trim');
			}
			if(!$where['name'] && $this->_request('dept_name','trim')){
				$where['name'] = $this->_request('dept_name','trim');
				unset($where['dept_name']);
			}
			if($this->_request('clientid','trim')) {
				if (M('Dept')->where($where)->find()) {
					$this->ajaxReturn("","",1);
				} else {
					$this->ajaxReturn("","",0);
				}
			}else{
				$this->ajaxReturn("","",0);
			}
		}
	}
}
?>
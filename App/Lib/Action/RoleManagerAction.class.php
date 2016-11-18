<?php
class RoleManagerAction extends CommonAction {
	protected $config = array('app_type' => 'common', 'action_auth' => array('ass_menu' => 'read' , 'assi_menu_save' => 'read' ,'authority' => 'read' ,'valirole'=>'read' ,'setauth'=>'read'));
	
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['keyword']) && empty($map['64'])) {
			$map['role_name'] = array('like', "%" . $_POST['keyword'] . "%");
		}
		if($_REQUEST['status'] !== "-1" && !empty($_REQUEST['status'])){
			$map['status'] = array('eq',$_REQUEST['status']);
		}
	}
	//列表页
	function index (){
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$model = M('RoleManager');
		if($model){
			$list = $this -> _list($model, $map);
		}
		$dept = M('dept')->where(array('pid'=>'0','is_del'=>'0','is_use'=>'1'))->select();
		$this -> assign('dept',$dept);
		$this -> display();	
		
	}
	
	//添加角色
	function add(){
		if($this -> isAjax()){
			$model = M('RoleManager');
			$data['company_id'] = I('post.company_id');
			$data['company'] = I('post.company');
			$data['role_name'] = I('post.role_name');
			$data['status'] = I('post.status');
			$data['sort'] = I('post.sort');
			$data['create_time'] =  time();
			/*保存当前数据对象 */
			$list = $model -> add($data);
			if ($list !== false) {//保存成功
				$this -> ajaxReturn($data, "添加成功", 1);
			} else {
				//失败提示
				$this -> ajaxReturn($data, "添加失败", 0);
			}
		}
	}
	//添加数据(一级菜单)
	function save(){
		if($this -> isAjax()){
			$model = M('RoleManager');
			$data['id'] = I('post.id');
			$data['company_id'] = I('post.company_id');
			$data['company'] = I('post.company');
			$data['role_name'] = I('post.role_name');
			$data['status'] = I('post.status');
			$data['sort'] = I('post.sort');
			/*保存当前数据对象 */
			$list = $model -> save($data);
			if ($list !== false) {//保存成功
				$this -> ajaxReturn($data, "修改成功", 1);
			} else {
				//失败提示
				$this -> ajaxReturn($data, "修改失败", 0);
			}
		}
	}
	//分配菜单(页面)
	function ass_menu(){
		$model = M('RoleManager');
		$id = I('post.id');
		/*保存当前数据对象 */
		$sql = "SELECT * FROM `smeoa_menu_new` WHERE ( `is_del` = '0' AND `menu_status` = '1' ) ORDER BY `id` asc ";
		$list = M()->query($sql);
		if (!empty($list)) {//保存成功
			$cheinfo = M("RRoleMenu") -> where(" role_id = $id ") -> select();
			$list = assi_tree_menu(list_to_tree($list),0,100,"",rotate($cheinfo));
			$this -> ajaxReturn($list, "获取菜单成功", 1);
		} else {
			//失败提示
			$this -> ajaxReturn($list, "获取菜单失败", 0);
		}
	}
	//分配菜单(保存)
	function assi_menu_save(){
		$ids = I('post.ids');
		$rid = I('post.rid');
		if(!empty($ids)){
			$id = array_filter(explode(',',$ids));
			$model = M('RRoleMenu');
			foreach ($id as $k => $v){
				$model -> add(array('role_id'=>$rid,'menu_id'=>$v));
			}
			$this -> ajaxReturn($id,"分配成功",1);
		}else{
			$this -> ajaxReturn($ids,' 请选择菜单',0);
		}
	}
	//验证角色名是否存在
	function valiRole(){
		$roleName = I('post.roleName');
		$isEdit = I('post.isEdit');
		$ids = I('post.id');
		if(!empty($roleName)){
			$where['role_name'] = array('eq',$roleName);
			if($isEdit){
				$where['id'] = array('neq',$ids);
			}
			$flag = M('RoleManager') -> where($where) -> find();
			if($flag){
				$this -> ajaxReturn($flag,"角色名已经存在了!",0);
			}else{
				$this -> ajaxReturn($flag,"角色名不存在!",1);
			}
		}
	}
	//分配权限
	function authority(){
		$rid = I('get.id');
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$model = M('Privilege');
		if($model){
			$list = $this -> _list($model, $map);
			$pr = M('PrivilegeRole')->where(array('role_id'=>$rid))->select();
			$arr = array();
			//找到已经分配的权限
			foreach ($list as $k => $v){
				foreach ($pr as $kk => $vv){
					if($vv['privilege_id'] == $v['id']){
						$v['check'] = "checked";
					}
				}
				$arr[$v['menu_new_id']][] = $v;
			}
			$this -> assign('menuList',$arr);
			$this -> assign('rid',$rid);
		}
		$this ->display();
	}
	function setAuth(){
		if($this -> isAjax()){
			$rid = I('post.rid');
			$pids = I('post.pids');
			$drs = I('post.drs');
			$flag = true;
			if(!empty($rid)){
				//功能权限
				$pv = array_filter(explode(',', $pids));
				$pr = M('PrivilegeRole');
				$where['role_id'] = $rid;
				$where['privilege_id'] = array('not in',$pv);
				$pr -> where($where) -> delete();
				$info = $pr ->where(array('role_id'=>$rid)) -> getField('privilege_id',true);
				$ps = array_diff($pv, $info);
				foreach ($ps as $k => $v){
					$data['role_id'] = $rid;
					$data['privilege_id'] = $v;
					$flag=$pr -> add($data);
				}
				//数据权限
				$space = array_filter(explode(',',$drs));
				$rm = M('RRoleMenu');
				$where['role_id'] = $rid;
				
			}
			if($flag){
				$this -> ajaxReturn($pids,'修改成功',1);
			}else{
				$this -> ajaxReturn($pids,'修改失败',0);
			}
		}
	}
	//删除
	function del(){
		$this -> _del();
	}
}
<?php
class MaintainAction extends CommonAction {
	protected $config = array('app_type' => 'common', 'action_auth' => array('changestatus' => 'read' , 'import_client' => 'read' ,'export_info' => 'read','add_role' => 'read'));
	
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['keyword']) && empty($map['64'])) {
			$map['menu_name'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}
	//列表页
	function index (){
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$name = isset($_POST['li_menu_name']) ? " AND `menu_name` LIKE '%".$_POST['li_menu_name']."%' AND `pid` = 0 " : "" ;
		$sql = "SELECT * FROM `smeoa_menu_new` WHERE ( `is_del` = '0' $name ) ORDER BY `sort` asc ";
		$list = M()->query($sql);
		$this -> assign('menu',new_tree_menu(list_to_tree($list),4));
		$this -> assign('menuList',popup_menu_option(list_to_tree($list)));
		//取出所有的角色
		$where["is_del"]=array('eq',0);
		if (!empty($_REQUEST['name'])) {
			$where['role_name'] = array('like','%'.$_REQUEST['name'].'%');
		}
		$role=M("RoleManager")->where($where)->select();
		$company=array();
		foreach($role as $k=>$v){
			$company[$v['company']][$v['id']]=$v['role_name'];
			$role['_company']=$company;
		}
		//dump($role);
		$this->assign('role',$role);
		$this -> display();	
		
	}
	
	//添加数据(一级菜单)
	function add(){
		if($this -> isAjax()){
			$model = M('MenuNew');
			$data['pid'] = empty(I('post.pid')) ? "0" : I('post.pid');
			$data['menu_no'] = empty(I('post.menu_no')) ? "" : I('post.menu_no');
			$data['menu_name'] = I('post.name');
			$data['menu_addr'] = I('post.addr');
			$data['sort'] = I('post.sort');
			$data['menu_status'] = empty($_POST['menu_status']) ? "1" : I('post.menu_status');
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
			$model = M('MenuNew');
			$data['id'] = I('post.id');
			$data['pid'] = empty(I('post.pid')) ? "0" : I('post.pid');
			$data['menu_no'] = empty(I('post.menu_no')) ? "" : I('post.menu_no');
			$data['menu_name'] = I('post.name');
			$data['menu_addr'] = I('post.addr');
			$data['sort'] = I('post.sort');
			$data['create_time'] =  time();
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
	//更改菜单状态
	function changestatus(){
		$model = M('MenuNew');
		$data['id'] = I('post.id');
		$data['menu_status'] = (I('post.status') == "禁用") ? '0' : '1' ;
		/*保存当前数据对象 */
		$list = $model -> save($data);
		if ($list !== false) {//保存成功
			$this -> ajaxReturn($data, "修改成功", 1);
		} else {
			//失败提示
			$this -> ajaxReturn($data, "修改失败", 0);
		}
	}
	//下载模板
	public function down() {
		$this -> _down();
	}
	//查看详情
	function read(){
		$pid = $_REQUEST['id'];
		$model = D('Attract_detail');
		$map['pid'] = $pid;
		if (!empty($model)) {
			$info = $this -> _list($model, $map);
			$this -> assign('info', $info);
		}
		$data = M('Attract') -> find($pid);
		$this -> assign('data',$data);
		$this -> display();
	}
	
	function del(){
		$this -> _del();
	}
	
	
	
	
}
<?php
class RoleManagerAction extends CommonAction {
	protected $config = array('app_type' => 'common', 'action_auth' => array('changestatus' => 'read' , 'import_client' => 'read' ,'export_info' => 'read'));
	
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['keyword']) && empty($map['64'])) {
			$map['role_name'] = array('like', "%" . $_POST['keyword'] . "%");
		}
		if ($_POST['eq_status'] !== "") {
			$map['status'] = array('eq',$_POST['eq_status'] ? "1" : "0") ;
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
		$this -> display();	
		
	}
	
	//添加角色
	function add(){
		if($this -> isAjax()){
			$model = M('RoleManager');
			$data['company'] = I('post.company');
			$data['role_name'] = I('post.role_name');
			$data['status'] = I('post.status');
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
			$data['company'] = I('post.company');
			$data['role_name'] = I('post.role_name');
			$data['status'] = I('post.status');
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
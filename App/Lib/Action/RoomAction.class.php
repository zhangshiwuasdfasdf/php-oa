<?php
class RoomAction extends CommonAction {
	
	//过滤查询字段
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['keyword']) && empty($map['64'])) {
			$map['name'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}
	
	public function index(){
		$where = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($where);
		}
		
		$model = D('Room_meet');
		if (!empty($model)) {
			$res = $this -> _list($model, $where);
			$this -> assign('task_extension', $res);
		}
		$this -> display();
	}
	
	public function config(){
		$node = M("Room_config");
		$menu = array();
		$menu = $node -> where($map) -> field('id,pid,name,is_del') -> order('sort asc') -> select();
		$tree = list_to_tree($menu);
		$this -> assign('menu', popup_tree_menu($tree));
		
		$list = $node -> where(array('is_del'=> 0,'pid'=>0)) -> order('sort asc') -> getField('id,name');
		$this -> assign('dept_grade_list',$list);
		$this -> display();
	}
	
	public function getConfig(){
		$id = $_REQUEST['id'];
		$node = M("Room_config");
		$vo = $node -> find($id);
		if ($this -> isAjax()) {
			if ($vo !== false) {// 读取成功
				$this -> ajaxReturn($vo, "读取成功", 1);
			} else {
				$this -> ajaxReturn(0, "读取失败", 0);
				die ;
			}
		}
	}
	
	public function add(){
		$node = M("Room_config");
		$list = $node -> where(array('is_del'=> 0,'pid'=>1)) -> order('sort asc') -> getField('id,name');
		$list2 = $node -> where(array('is_del'=> 0,'pid'=>2)) -> order('sort asc') -> getField('id,name');
		$list3 = $node -> where(array('is_del'=> 0,'pid'=>3)) -> order('sort asc') -> getField('id,name');
		$this -> assign('room_name_list',$list);
		$this -> assign('room_status_list',$list2);
		$this -> assign('room_shadow_list',$list3);
		$this -> display();
	}
	
	function _insert(){
		$model = D("room_meet");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$model -> create_time = time();
		/*保存当前数据对象 */
		$list = $model -> add();
		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('新增成功!'.$list);
		} else {//失败提示
			$this -> error('新增失败!');
		}
	}
	
	function addConfig(){
		$model = D("room_config");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		/*保存当前数据对象 */
		$list = $model -> add();
		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl', U("room/config"));
			$this -> success('新增成功!'.$list);
		} else {//失败提示
			$this -> error('新增失败!');
		}
	}
	
	function updConfig(){
		$model = D("room_config");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		/*保存当前数据对象 */
		$list = $model -> save();
		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl', U("room/config"));
			$this -> success('编辑成功!');
		} else {//失败提示
			$this -> error('编辑失败!');
		}
	}
	
	public function del() {
		$id = $_POST['id'];
		$this -> _destory($id,"room_config");
	}
	
	public function del_meet(){
		$id = $_REQUEST['id'];
		$this -> _del($id,"room_meet");
	}
	
	public function edit(){
		$id = $_REQUEST['id'];
		$model = M("room_meet")->find($id);
		$this -> assign("list",$model);
		
		$node = M("Room_config");
		$list = $node -> where(array('is_del'=> 0,'pid'=>1)) -> order('sort asc') -> getField('id,name');
		$list2 = $node -> where(array('is_del'=> 0,'pid'=>2)) -> order('sort asc') -> getField('id,name');
		$list3 = $node -> where(array('is_del'=> 0,'pid'=>3)) -> order('sort asc') -> getField('id,name');
		$this -> assign('room_name_list',$list);
		$this -> assign('room_status_list',$list2);
		$this -> assign('room_shadow_list',$list3);
		$this -> display();
	}
	
	function save(){
		$this ->_update("room_meet");
	}
	
	function yeyue(){
		$this ->display();
	}
}
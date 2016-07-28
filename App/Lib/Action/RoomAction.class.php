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
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		
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
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		
		$id = $_REQUEST['id'];
		$model = M("room_meet")->find($id);
		$temp = explode("-",$model['time_frame']);
		$model['start'] = $temp[0];
		$model['end'] = $temp[1];
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
	
	function save_meet(){
		$this ->_update("room_meet");
	}
	
	function yuyue(){
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		
		$id = $_REQUEST['id'];
		$model = M("room_meet");
		$dept = M("Dept");
		$config = M("room_config");
		$user = M("user");
		$name = $model -> find($id);
		$meet_name = $config -> field("name") -> find($name['meet_name']);
		$pos_id = $user->where("id = ".get_user_id()) -> getField("pos_id");
		$where['is_del'] = 0;
		$where['id'] = $pos_id;
		$pos_name = $dept -> where($where) -> getField('name');
		$this -> assign("id",$id);
		$this -> assign("pos_name",$pos_name);
		$this -> assign('meet_name',$meet_name);
		$this -> assign("section",$this ->getTimeSection($name['time_frame']));
		$this ->display();
	}
	//处理时间段
	private function getTimeSection($time){
		$section = explode("-",$time);
		$am = explode(":",$section[0]);
		$pm = explode(":",$section[1]);
		$start = intval($section[0]);
		$end = intval($section[1]);
		$ts = array();
		for ($i=$start;$i<=$end;$i++){
			if($am[1] != "00") {
				$ts[] = $section[0] .'-'.($i+1).":00";$am = "00";
			}else{
				if($i == $end){
					if($pm[1] != "00"){
						$ts[] = $i .":00-".$i .":30";
					}
				}else{
					$ts[] = $i .":00".'-'.$i .":30";
					$ts[] = $i .":30".'-'.($i+1) .":00";
				}
			}
		}
		return $ts;
	}
	
	function add_order(){
		parent::_insert("room_order");
	}
	
	function lists(){
		$map['is_del'] = 0;
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$model = M("room_order");
		if (!empty($model)) {
			$order = $this -> _list($model, $map);
		}
		$this -> display();
	}
}
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
	
	function save_order(){
		$this ->_insert();
	}
	
	function yuyue(){
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		
		$id = $_REQUEST['id'];
		$yid = $_REQUEST['yid'];
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
		$this -> assign("start_time",date("Y-m-d"));
		$this -> assign("pos_name",$pos_name);
		$this -> assign('meet_name',$meet_name);
		$this -> assign("time_frame",$name['time_frame']);
		$this -> assign("new" ,$this->getTimeContain($name['time_frame'],date("Y-m-d"),$id,$yid));
		$this ->display();
	}
	//处理时间段
	private function getTimeSection($time,$date,$meet,$yid,$fl = false){
		$section = explode("-",$time);
		$am = explode(":",$section[0]);
		$pm = explode(":",$section[1]);
		$start = intval($section[0]);
		$end = intval($section[1]);
		dump($yid);
		if(empty($yid)){
			$now_sec = M("room_order")->where(array("date_section"=>$date,"is_del"=>"0","meet_id"=>$meet))->field("time_section")->select();
			$now_sec = rotate($now_sec);
		}else{
			$now_sec = M("room_order")->find($yid);
		}
		$now_sec = $now_sec['time_section'];
		foreach ($now_sec as $k=>$v){
			$tmp = trim($v,"|");
			$arr = explode("|",$tmp);
			foreach($arr as $vv){
				$new_sec[] = $vv;
			}
		}
		dump($new_sec);
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
		$lc = array();
		foreach ($ts as $k=>$v){
			$bh = explode("-",$v);
			foreach ($new_sec as $kk=>$vv){
				$tmp = explode("-",$vv);
				if($tmp[0] == $bh[0]){
					$lc[]["ks"] = $v;
				}
				if($tmp[1] == $bh[1]){
					$lc[]["js"] = $v;
				}
			}
		}
		return $fl?$lc:$ts;
	}
	
	function add_order(){
		$model = D("room_order");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		/*保存当前数据对象 */
		$list = $model -> add();
		if ($list !== false) {//保存成功
				$data['content']= $_POST['proposer'] ."预约了" . $_POST['date_section'] ." " .$_POST['time_section'] . "的会议,邀请您参加!";
				$data['sender_id']=get_user_id();
				$data['sender_name']=get_user_name();
				$data['create_time']=time();
				$model = D('Message');
				$report_look = explode("|",trim($_POST['takes_id'],"|"));
				foreach ($report_look as $tmp) {
					$data['receiver_id']=$tmp;
					$data['receiver_name']= get_user_info($tmp, "name");			
					$data['owner_id']= $tmp;
					$list = $model -> add($data);
					$this -> _pushReturn("", "您有新的消息, 请注意查收", 1,$tmp);	
				}			
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('预约成功!'.$list);
		} else {//失败提示
			$this -> error('预约失败!');
		}
	}
	
	function lists(){
		$map['is_del'] = 0;
		$map['user_id'] = get_user_id();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$model = M("room_order");
		if (!empty($model)) {
			$order = $this -> _list($model, $map);
		}
		$this -> display();
	}
	
	function getTimeContain($time_frame,$date,$meet,$yid){
		$section = $this ->getTimeSection($time_frame,$date,$meet,$yid);
		$contain_old = $this ->getTimeSection($time_frame,$date,$meet,$yid,true);
		$contain = array();
		foreach ($contain_old as $k=>$v){
			if($v['ks']){
				$contain[] = $v['ks'];
			}else{
				$contain[] = $v['js'];
			}
		}
		$new = array();
		foreach ($section as $k=>$v){
			$new[$k]['color'] = 'noyud';
			$new[$k]['time'] = $v;
			foreach ($contain as $kk=>$vv){
				if($v==$vv){
					$new[$k]['color'] = 'booked';
					break;
				}
			}
		}
		return $new;
	}
	
	function getTimeList(){
		$frame = $_POST['frame'];
		$date = $_POST['date'];
		$meet = $_POST['meet'];
		$list = $this -> getTimeContain($frame,$date,$meet);
		$this -> ajaxReturn($list);
	}
	
	function showydr(){
		$frame = $_POST['frame'];
		$date = $_POST['date'];
		$list = M("room_order")->where(array("date_section"=>$date,"time_section"=>array('like','%'.$frame.'%')))->find();
		$this -> ajaxReturn($list);
	}
	function cancel_yuyue(){
		$id = $_REQUEST['id'];
		$this -> _del($id,"room_order");
	}
}
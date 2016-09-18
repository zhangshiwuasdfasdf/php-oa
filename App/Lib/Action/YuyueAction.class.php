<?php
class YuyueAction extends CommonAction {
	
	//预约列表-查询
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['keyword']) && empty($map['64'])) {
			$map['proposer'] = array('like', "%" . $_POST['keyword'] . "%");
		}
		if (!empty($_POST['dept_name_multi'])) {
			$dept_name_mul = $_POST['dept_name_multi'];
			$dept_name_mul = array_filter(explode(';',$dept_name_mul));
			$map['dept'] = array('in', $dept_name_mul);
		}
	}
	function index(){
		$map = $this -> _search("room_order");
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		
		$node = D("Dept");
		$dept_menu = $node -> field('id,pid,name') -> where("is_del=0 and is_real_dept=1") -> order('sort asc') -> select();
		$dept_tree = list_to_tree($dept_menu);
		if(!is_mobile_request()){
			$this -> assign('dept_list_new', select_tree_menu_mul($dept_tree));
		}
		
		$model = M("room_order");
		if (!empty($model)) {
			$order = $this -> _list($model, $map);
		}
		$this->assign("user_id",get_user_id());
		$this -> display();
	}
	
	function lookyy(){
		$id = $_REQUEST['id'];
		$yid = $_REQUEST['yid'];
		$model = M("room_meet");
		$order = M("room_order");
		$name = $model -> find($id);
		//会议室预定信息
		$list = $order -> find($yid);
		$tmp = explode('.',$list['create_time']);
		$list['create_time'] = $tmp[0];
		$li = explode("|",rtrim($list['takes_id'],"|"));
		$per = array();
		foreach ($li as $k=>$v){
			$per[$k]['name'] = get_user_info($v, "name");
			$per[$k]['id'] = $list['id'];
		}
		$this -> assign('meet',$name);
		$this -> assign('vo',$list);
		$this -> assign("per",$per);
		$this -> assign("new" ,$this->getTimeContain($name['time_frame'],date("Y-m-d"),$id,$yid));
		$this ->display();
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
	
	//处理时间段
	private function getTimeSection($time,$date,$meet,$yid,$fl = false){
		$section = explode("-",$time);
		$am = explode(":",$section[0]);
		$pm = explode(":",$section[1]);
		$start = intval($section[0]);
		$end = intval($section[1]);
		if(empty($yid)){
			$now_sec = M("room_order")->where(array("date_section"=>$date,"is_del"=>"0","meet_id"=>$meet))->field("time_section")->select();
			$now_sec = rotate($now_sec);
			$now_sec = $now_sec['time_section'];
			foreach ($now_sec as $k=>$v){
				$tmp = trim($v,"|");
				$arr = explode("|",$tmp);
				foreach($arr as $vv){
					$new_sec[] = $vv;
				}
			}
		}else{
			$now_sec = M("room_order")->find($yid);
			$now_sec = $now_sec['time_section'];
			$tmp = trim($now_sec,"|");
			$arr = explode("|",$tmp);
			foreach($arr as $vv){
				$new_sec[] = $vv;
			}
		}
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
	
	function cancel_yuyue(){
		$id = $_REQUEST['id'];
		$this -> _del($id,"room_order");
	}
}
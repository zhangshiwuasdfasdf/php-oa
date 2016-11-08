<?php
class AttendanceAction extends CommonAction {
	protected $config = array('app_type' => 'master');
	
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_POST['dept_name_multi'])) {
			$dept_name_mul = $_POST['dept_name_multi'];
			$dept_name_mul = array_filter(explode(';',$dept_name_mul));
			$map['dept_name'] = array('in', $dept_name_mul);
		}
		if (!empty($_POST['eq_user_name'])) {
			$map['user_name'] = $_POST['eq_user_name'];
		}
		if (!empty($_POST['eq_user_id'])) {
			$map['user_id'] = $_POST['eq_user_id'];
		}
		if (!empty($_POST['eq_num'])) {
			$map['num'] = $_POST['eq_num'];
		}
		$map['attendance_time'] = array();
		if (!empty($_POST['be_attendance_time'])) {
			$map['attendance_time'][] = array('egt',strtotime($_POST['be_attendance_time']));
			
		}
		if (!empty($_POST['en_attendance_time'])) {
			$map['attendance_time'][] = array('elt',strtotime($_POST['en_attendance_time'].' 24:00:00'));
		}
		if(empty($map['attendance_time'])){
			unset($map['attendance_time']);
		}
		$map['month'] = array();
		if (!empty($_POST['be_date'])) {
			$map['month'][] = array('egt',$_POST['be_date']);
				
		}
		if (!empty($_POST['en_date'])) {
			$map['month'][] = array('elt',$_POST['en_date']);
		}
		if(empty($map['month'])){
			unset($map['month']);
		}
		if (!empty($_POST['number'])) {
			$map['number'] = array('like','%'.$_POST['number'].'%');
		}
// 		dump($map);
// 		dump($_POST['be_attendance_time']);
// 		dump($_POST['en_attendance_time']);
	}
	//列表页
	function index (){
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$model = D('Attendance');
		if (!empty($model)) {
			$info = $this -> _list($model, $map,'import_time');
			$this -> assign('info', $info);
		}

		$dept_name = $model -> where('is_del = 0') -> field('dept_name as id,dept_name as name') ->distinct(true) -> select();
		$user_name = $model -> where('is_del = 0') -> field('user_name as id,user_name as name') ->distinct(true) -> select();
// 		$months = $model -> where('is_del = 0') -> field('months as id,months as name') ->distinct(true) -> select();
		$this -> assign('dept_name', $dept_name);
		$this -> assign('user_name', $user_name);
		
		$node = D("Dept");
		$dept_menu = $node -> field('id,pid,name') -> where("is_del=0 and is_real_dept=1") -> order('sort asc') -> select();
		$dept_tree = list_to_tree($dept_menu);
		if(!is_mobile_request()){
			$this -> assign('dept_list_new', select_tree_menu_mul($dept_tree));
		}
		
// 		$this -> assign('months', $months);
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign("map", serialize($map));
		$this -> display();	
	}
	function table (){
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$model = D('AttendanceTable');
		if (!empty($model)) {
			$info = $this -> _list($model, $map,'month');
			$this -> assign('info', $info);
		}
		$dept_name = $model -> where('is_del = 0') -> field('dept_name as id,dept_name as name') ->distinct(true) -> select();
		$user_name = $model -> where('is_del = 0') -> field('user_name as id,user_name as name') ->distinct(true) -> select();
		// 		$months = $model -> where('is_del = 0') -> field('months as id,months as name') ->distinct(true) -> select();
		$this -> assign('dept_name', $dept_name);
		$this -> assign('user_name', $user_name);
		
		$node = D("Dept");
		$dept_menu = $node -> field('id,pid,name') -> where("is_del=0 and is_real_dept=1") -> order('sort asc') -> select();
		$dept_tree = list_to_tree($dept_menu);
		if(!is_mobile_request()){
			$this -> assign('dept_list_new', select_tree_menu_mul($dept_tree));
		}
		
		// 		$this -> assign('months', $months);
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign("map", serialize($map));
		$this -> display();
	}

	function new_table(){
		// dump(1);die;
		/*$sec = get_leave_seconds_weekend(strtotime('2016-10-2 13:00'),strtotime('2016-10-3 18:00'));

		dump($sec);die;*/
		$map = $this -> _search();
		
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$model = D('AttendanceMonth');
		// dump($map);
		// die;
		if (!empty($model)) {
			$info = $this -> _list($model, $map,'month');
			foreach ($info as $k=> $v) {
				$dept_ids=$info[$k]['attendance_dept'];
				$dept_ids=array_filter(explode('|', $dept_ids));
				$dept_name=M("Dept")->where(array('id'=>array('in',$dept_ids)))->getField('name',true);
				$names=implode(',',$dept_name);
				$info[$k]['names'] = $names;
			}
			$this -> assign('info', $info);
		}
		
		$node = D("Dept");
		$dept_menu = $node -> field('id,pid,name') -> where("is_del=0 and is_real_dept=1") -> order('sort asc') -> select();
		$dept_tree = list_to_tree($dept_menu);
		if(!is_mobile_request()){
			$this -> assign('dept_list_new', select_tree_menu_mul($dept_tree));
		}

		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign("names", $names);
		$this -> assign("map", serialize($map));
		$this -> display();
	}

	function edit_table(){
		$id=I('get.id');
		$model=M("AttendanceMonth");
		if($model->create()){
			if(FALSE !== $model->save()){
				$this->success('编辑成功',U('new_table'));
			}else{
				$this->error('编辑失败');
			}
		}
		$data=$model->find($id);
		$username=$data['create_name'];
		
		$dept_ids=$data['attendance_dept'];
		if (!empty($dept_ids)) {

			$dept_id_mul = array_filter(explode('|',$dept_ids));
			$dept_ids = array();
			foreach ($dept_id_mul as $dept_id){
				$dept_ids = array_merge($dept_ids,get_child_dept_all($dept_id));
			}
			$map['pos_id'] = array('in', $dept_ids);
		}
		$map['is_del'] = array('eq','0');
		$info = D("UserView")->where($map)->order('dept_name desc')->select();
		//dump($info);die;
		//应出勤天数
		foreach ($info as $k => $v) {
			if($v['duty']=='云客服专员'){
				$info[$k]['should_day']=26;
			}else{
				$month = $data['month'];//2016-10
				$m = date('m',strtotime($month));
				$y = date('Y',strtotime($month));
				//$sum = date('d',strtotime($y.'-'.($m+1))-1);
				$t = date('t',strtotime($month));
				
				$start_date = date('Y-m-d H:i:s',strtotime($y.'-'.$m));
				$end_date = $y.'-'.$m.'-'.$t.' 24:00:00';

				$should_day = 0;
				for($i=1;$i<=$t;$i++){
					if(!is_holiday(strtotime($y.'-'.$m.'-'.$i))){
						$should_day += 1;
					};
				}
				$info[$k]['should_day']=$should_day;
			}
		}
		
		$attendance_table = M('AttendanceTable')->where(array('attendance_month_id'=>$id))->select();
		if(empty($attendance_table)){
			//补勤
			foreach ($info as $key => $value) {
				$info[$key]['attendance_day'] = 0;
				$flow_ids = M('Flow')->where(array('user_id'=>$value['id'],'type'=>47))->getField('id',true);
				
				foreach ($flow_ids as $key2 => $flow_id) {
					$FlowAttendance = M('FlowAttendance')->where(array('flow_id'=>$flow_id,'start_time'=>array('between',array($start_date,$end_date))))->count();

					$start_time=date('Y-m-d H:i:s',strtotime(M('FlowAttendance')->where(array('flow_id'=>$flow_id))->getField('start_time')));
					$end_time=date('Y-m-d H:i:s',strtotime(M('FlowAttendance')->where(array('flow_id'=>$flow_id))->getField('end_time')));
					if(M('Flow')->where(array('id'=>$flow_id))->getField('step') == '40'){
						if($start_time < $start_date){
							$FlowAttendance = M('FlowAttendance')->where(array('flow_id'=>$flow_id,'end_time'=>array('between',array($start_date,$end_date))))->count();
						}
						//dump($start_date > $start_time);die;
					
						//foreach ($FlowAttendance as $key3 => $value3) {
							$info[$key]['attendance_day'] += $FlowAttendance;
						//}
					}
					
				}
			}
			//dump($info);die;
			//病假
			foreach ($info as $key => $value) {
				$info[$key]['sick_leave']=0;
				$flow_ids=M('Flow')->where(array('user_id'=>$value['id'],'type'=>39))->getField('id',true);
				foreach ($flow_ids as $key2 => $flow_id) {
					
					$start_time=date('Y-m-d H:i:s',strtotime(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('start_time')));
					$end_time=date('Y-m-d H:i:s',strtotime(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('end_time')));
		
				if(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('style') == '病假' && M('Flow')->where(array('id'=>$flow_id))->getField('step') == '40'){
						if($start_time >= $start_date && $end_time <= $end_date){//1
						$FlowLeave=M('FlowLeave')->field('day_num,hour_num')->where(array('flow_id'=>$flow_id,'style'=>'病假','start_time'=>array('between',array($start_date,$end_date))))->select();
						foreach ($FlowLeave as $key3 => $value3) {
						$info[$key]['sick_leave'] += $value3['day_num']+$value3['hour_num']/8;
						}
					}elseif ($start_time < $start_date && $end_time < $end_date && $end_time > $start_date) {//2
						$FlowLeave=get_leave_seconds(strtotime($start_date),strtotime($end_time));
						$info[$key]['sick_leave'] += $FlowLeave/3600/8;

					}elseif ($start_time > $start_date && $end_time > $end_date) {//3
						$FlowLeave=get_leave_seconds(strtotime($start_time),strtotime($end_date));
						$info[$key]['sick_leave'] += $FlowLeave/3600/8;

					}elseif ($start_time < $start_date && $end_time > $end_date) {//4
						$FlowLeave=get_leave_seconds(strtotime($start_date),strtotime($end_date));
						$info[$key]['sick_leave'] += $FlowLeave/3600/8;
					}
					}
					
				}
			}
			//事假
			foreach ($info as $key => $value) {
				$info[$key]['casual_leave']=0;
				$flow_ids=M('Flow')->where(array('user_id'=>$value['id'],'type'=>39))->getField('id',true);
				foreach ($flow_ids as $key2 => $flow_id) {
					
					$start_time=date('Y-m-d H:i:s',strtotime(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('start_time')));
					$end_time=date('Y-m-d H:i:s',strtotime(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('end_time')));
				if(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('style') == '事假' && M('Flow')->where(array('id'=>$flow_id))->getField('step') == '40'){
					if($start_time >= $start_date && $end_time <= $end_date){
						$FlowLeave=M('FlowLeave')->field('day_num,hour_num')->where(array('flow_id'=>$flow_id,'style'=>'事假','start_time'=>array('between',array($start_date,$end_date))))->select();
						foreach ($FlowLeave as $key3 => $value3) {
							$info[$key]['casual_leave'] += $value3['day_num']+$value3['hour_num']/8;
						}
					}elseif ($start_time < $start_date && $end_time < $end_date && $end_time > $start_date) {//2
						$FlowLeave=get_leave_seconds(strtotime($start_date),strtotime($end_time));
						$info[$key]['casual_leave'] += $FlowLeave/3600/8;

					}elseif ($start_time > $start_date && $end_time > $end_date) {//3
						$FlowLeave=get_leave_seconds(strtotime($start_time),strtotime($end_date));
						$info[$key]['casual_leave'] += $FlowLeave/3600/8;

					}elseif ($start_time < $start_date && $end_time > $end_date) {//4
						$FlowLeave=get_leave_seconds(strtotime($start_date),strtotime($end_date));
						$info[$key]['casual_leave'] += $FlowLeave/3600/8;
					}
				}
					
				}
			}
			//产假
			foreach ($info as $key => $value) {
				$info[$key]['maternity_leave']=0;
				$flow_ids=M('Flow')->where(array('user_id'=>$value['id'],'type'=>39))->getField('id',true);
				foreach ($flow_ids as $key2 => $flow_id) {
					
					$start_time=date('Y-m-d H:i:s',strtotime(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('start_time')));
					$end_time=date('Y-m-d H:i:s',strtotime(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('end_time')));
					if(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('style') == '产假' && M('Flow')->where(array('id'=>$flow_id))->getField('step') == '40'){
						if($start_time >= $start_date && $end_time <= $end_date){
						$FlowLeave=M('FlowLeave')->field('day_num,hour_num')->where(array('flow_id'=>$flow_id,'style'=>'产假','start_time'=>array('between',array($start_date,$end_date))))->select();
				
						foreach ($FlowLeave as $key3 => $value3) {
							$info[$key]['maternity_leave'] += $value3['day_num']+$value3['hour_num']/8;
						}
					}elseif ($start_time < $start_date && $end_time < $end_date && $end_time > $start_date) {//2
						$FlowLeave=get_leave_seconds(strtotime($start_date),strtotime($end_time));
						$info[$key]['maternity_leave'] += $FlowLeave/3600/8;

					}elseif ($start_time > $start_date && $end_time > $end_date) {//3
						$FlowLeave=get_leave_seconds(strtotime($start_time),strtotime($end_date));
						$info[$key]['maternity_leave'] += $FlowLeave/3600/8;

					}elseif ($start_time < $start_date && $end_time > $end_date) {//4
						$FlowLeave=get_leave_seconds(strtotime($start_date),strtotime($end_date));
						$info[$key]['maternity_leave'] += $FlowLeave/3600/8;
					}
					}
					
				}
			}
			//婚假
			foreach ($info as $key => $value) {
				$info[$key]['marriage_leave']=0;
				$flow_ids=M('Flow')->where(array('user_id'=>$value['id'],'type'=>39))->getField('id',true);
				foreach ($flow_ids as $key2 => $flow_id) {
					
					$start_time=date('Y-m-d H:i:s',strtotime(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('start_time')));
					$end_time=date('Y-m-d H:i:s',strtotime(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('end_time')));
					if(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('style') == '婚假' && M('Flow')->where(array('id'=>$flow_id))->getField('step') == '40'){
						if($start_time >= $start_date && $end_time <= $end_date){
						$FlowLeave=M('FlowLeave')->field('day_num,hour_num')->where(array('flow_id'=>$flow_id,'style'=>'婚假','start_time'=>array('between',array($start_date,$end_date))))->select();
				
						foreach ($FlowLeave as $key3 => $value3) {
							$info[$key]['marriage_leave'] += $value3['day_num']+$value3['hour_num']/8;
						}
					}elseif ($start_time < $start_date && $end_time < $end_date && $end_time > $start_date) {//2
						$FlowLeave=get_leave_seconds(strtotime($start_date),strtotime($end_time));
						$info[$key]['marriage_leave'] += $FlowLeave/3600/8;

					}elseif ($start_time > $start_date && $end_time > $end_date) {//3
						$FlowLeave=get_leave_seconds(strtotime($start_time),strtotime($end_date));
						$info[$key]['marriage_leave'] += $FlowLeave/3600/8;

					}elseif ($start_time < $start_date && $end_time > $end_date) {//4
						$FlowLeave=get_leave_seconds(strtotime($start_date),strtotime($end_date));
						$info[$key]['marriage_leave'] += $FlowLeave/3600/8;
					}
					}
					
				}
			}
			//丧假
			foreach ($info as $key => $value) {
				$info[$key]['bereavement_leave']=0;
				$flow_ids=M('Flow')->where(array('user_id'=>$value['id'],'type'=>39))->getField('id',true);
				foreach ($flow_ids as $key2 => $flow_id) {
					
					$start_time=date('Y-m-d H:i:s',strtotime(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('start_time')));
					$end_time=date('Y-m-d H:i:s',strtotime(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('end_time')));
					if(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('style') == '丧假' && M('Flow')->where(array('id'=>$flow_id))->getField('step') == '40'){
						if($start_time >= $start_date && $end_time <= $end_date){
						$FlowLeave=M('FlowLeave')->field('day_num,hour_num')->where(array('flow_id'=>$flow_id,'style'=>'丧假','start_time'=>array('between',array($start_date,$end_date))))->select();
				
						foreach ($FlowLeave as $key3 => $value3) {
							$info[$key]['bereavement_leave'] += $value3['day_num']+$value3['hour_num']/8;
						}
					}elseif ($start_time < $start_date && $end_time < $end_date && $end_time > $start_date) {//2
						$FlowLeave=get_leave_seconds(strtotime($start_date),strtotime($end_time));
						$info[$key]['bereavement_leave'] += $FlowLeave/3600/8;

					}elseif ($start_time > $start_date && $end_time > $end_date) {//3
						$FlowLeave=get_leave_seconds(strtotime($start_time),strtotime($end_date));
						$info[$key]['bereavement_leave'] += $FlowLeave/3600/8;

					}elseif ($start_time < $start_date && $end_time > $end_date) {//4
						$FlowLeave=get_leave_seconds(strtotime($start_date),strtotime($end_date));
						$info[$key]['bereavement_leave'] += $FlowLeave/3600/8;
					}
					}
					
				}
			}
			//工伤
			foreach ($info as $key => $value) {
				$info[$key]['accidents']=0;
				$flow_ids=M('Flow')->where(array('user_id'=>$value['id'],'type'=>39))->getField('id',true);
				foreach ($flow_ids as $key2 => $flow_id) {
					$start_time=date('Y-m-d H:i:s',strtotime(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('start_time')));
					$end_time=date('Y-m-d H:i:s',strtotime(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('end_time')));
					if(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('style') == '工伤假' && M('Flow')->where(array('id'=>$flow_id))->getField('step') == '40'){
						if($start_time >= $start_date && $end_time <= $end_date){
						$FlowLeave=M('FlowLeave')->field('day_num,hour_num')->where(array('flow_id'=>$flow_id,'style'=>'工伤假','start_time'=>array('between',array($start_date,$end_date))))->select();
				
						foreach ($FlowLeave as $key3 => $value3) {
							$info[$key]['accidents'] += $value3['day_num']+$value3['hour_num']/8;
						}
					}elseif ($start_time < $start_date && $end_time < $end_date && $end_time > $start_date) {//2
						$FlowLeave=get_leave_seconds(strtotime($start_date),strtotime($end_time));
						/*dump($start_date);
						dump($end_time);
						dump($FlowLeave/3600/8);die;*/
						$info[$key]['accidents'] += $FlowLeave/3600/8;

					}elseif ($start_time > $start_date && $end_time > $end_date && $start_time < $end_date) {//3
						$FlowLeave=get_leave_seconds(strtotime($start_time),strtotime($end_date));
						$info[$key]['accidents'] += $FlowLeave/3600/8;

					}elseif ($start_time < $start_date && $end_time > $end_date) {//4
						$FlowLeave=get_leave_seconds(strtotime($start_date),strtotime($end_date));
						$info[$key]['accidents'] += $FlowLeave/3600/8;
					}
					}
					
				}
			}
			//年假
			foreach ($info as $key => $value) {
				$info[$key]['annual_leave']=0;
				$flow_ids=M('Flow')->where(array('user_id'=>$value['id'],'type'=>39))->getField('id',true);
				foreach ($flow_ids as $key2 => $flow_id) {
					
					$start_time=date('Y-m-d H:i:s',strtotime(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('start_time')));
					$end_time=date('Y-m-d H:i:s',strtotime(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('end_time')));
					if(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('style') == '年假' && M('Flow')->where(array('id'=>$flow_id))->getField('step') == '40'){
						if($start_time >= $start_date && $end_time <= $end_date){
						$FlowLeave=M('FlowLeave')->field('day_num,hour_num')->where(array('flow_id'=>$flow_id,'style'=>'年假','start_time'=>array('between',array($start_date,$end_date))))->select();
				
						foreach ($FlowLeave as $key3 => $value3) {
							$info[$key]['annual_leave'] += $value3['day_num']+$value3['hour_num']/8;
						}
					}elseif ($start_time < $start_date && $end_time < $end_date && $end_time > $start_date) {//2
						$FlowLeave=get_leave_seconds(strtotime($start_date),strtotime($end_time));
						$info[$key]['annual_leave'] += $FlowLeave/3600/8;

					}elseif ($start_time > $start_date && $end_time > $end_date) {//3
						$FlowLeave=get_leave_seconds(strtotime($start_time),strtotime($end_date));
						$info[$key]['annual_leave'] += $FlowLeave/3600/8;

					}elseif ($start_time < $start_date && $end_time > $end_date) {//4
						$FlowLeave=get_leave_seconds(strtotime($start_date),strtotime($end_date));
						$info[$key]['annual_leave'] += $FlowLeave/3600/8;
					}
					}
					
				}
			}
			//调休
			foreach ($info as $key => $value) {
				$info[$key]['leave_in_lieu']=0;
				$flow_ids=M('Flow')->where(array('user_id'=>$value['id'],'type'=>39))->getField('id',true);
				foreach ($flow_ids as $key2 => $flow_id) {
					
					$start_time=date('Y-m-d H:i:s',strtotime(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('start_time')));
					$end_time=date('Y-m-d H:i:s',strtotime(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('end_time')));
					if(M('FlowLeave')->where(array('flow_id'=>$flow_id))->getField('style') == '调休' && M('Flow')->where(array('id'=>$flow_id))->getField('step') == '40'){
						if($start_time >= $start_date && $end_time <= $end_date){
						$FlowLeave=M('FlowLeave')->field('day_num,hour_num')->where(array('flow_id'=>$flow_id,'style'=>'调休','start_time'=>array('between',array($start_date,$end_date))))->select();
				
						foreach ($FlowLeave as $key3 => $value3) {
							$info[$key]['leave_in_lieu'] += $value3['day_num']+$value3['hour_num']/8;
						}
					}elseif ($start_time < $start_date && $end_time < $end_date && $end_time > $start_date) {//2
						$FlowLeave=get_leave_seconds(strtotime($start_date),strtotime($end_time));
						$info[$key]['leave_in_lieu'] += $FlowLeave/3600/8;

					}elseif ($start_time > $start_date && $end_time > $end_date) {//3
						$FlowLeave=get_leave_seconds(strtotime($start_time),strtotime($end_date));
						$info[$key]['leave_in_lieu'] += $FlowLeave/3600/8;

					}elseif ($start_time < $start_date && $end_time > $end_date) {//4
						$FlowLeave=get_leave_seconds(strtotime($start_date),strtotime($end_date));
						$info[$key]['leave_in_lieu'] += $FlowLeave/3600/8;
					}
					}
					
				}
			}
			//实际出勤
			//平时加班
			foreach ($info as $key => $value) {
				$info[$key]['overtime_weekday']=0;
				$flow_ids=M('Flow')->where(array('user_id'=>$value['id'],'type'=>57))->getField('id',true);
				foreach ($flow_ids as $key2 => $flow_id) {
					
					$start_time=date('Y-m-d H:i:s',strtotime(M('FlowOverTime')->where(array('flow_id'=>$flow_id))->getField('start_time')));
					$end_time=date('Y-m-d H:i:s',strtotime(M('FlowOverTime')->where(array('flow_id'=>$flow_id))->getField('end_time')));
					if(M('FlowOverTime')->where(array('flow_id'=>$flow_id))->getField('use_type') == '申请加班费' && M('Flow')->where(array('id'=>$flow_id))->getField('step') == '40'){
						if($start_time >= $start_date && $end_time <= $end_date){
						$FlowLeave=M('FlowOverTime')->field('day_num,hour_num')->where(array('flow_id'=>$flow_id,'use_type'=>'申请加班费','start_time'=>array('between',array($start_date,$end_date))))->select();
				
						foreach ($FlowLeave as $key3 => $value3) {
							$info[$key]['overtime_weekday'] += $value3['day_num']+$value3['hour_num']/8;
						}
					}elseif ($start_time < $start_date && $end_time < $end_date && $end_time > $start_date) {//2
						$FlowLeave=strtotime($end_time)-strtotime($start_date);
						$info[$key]['overtime_weekday'] += $FlowLeave/3600/8;

					}elseif ($start_time > $start_date && $end_time > $end_date) {//3
						$FlowLeave=strtotime($end_date)-strtotime($start_time);
						$info[$key]['overtime_weekday'] += $FlowLeave/3600/8;

					}elseif ($start_time < $start_date && $end_time > $end_date) {//4
						$FlowLeave=strtotime($end_date)-strtotime($start_date);
						$info[$key]['overtime_weekday'] += $FlowLeave/3600/8;
						}
					}
					
				}
			}
			//周末
			foreach ($info as $key => $value) {
				$info[$key]['overtime_weekends']=0;
				$flow_ids=M('Flow')->where(array('user_id'=>$value['id'],'type'=>57))->getField('id',true);
				foreach ($flow_ids as $key2 => $flow_id) {
					
					$start_time=date('Y-m-d H:i:s',strtotime(M('FlowOverTime')->where(array('flow_id'=>$flow_id))->getField('start_time')));
					$end_time=date('Y-m-d H:i:s',strtotime(M('FlowOverTime')->where(array('flow_id'=>$flow_id))->getField('end_time')));
					if(M('FlowOverTime')->where(array('flow_id'=>$flow_id))->getField('use_type') == '申请加班费' && M('Flow')->where(array('id'=>$flow_id))->getField('step') == '40'){
						if($start_time >= $start_date && $end_time <= $end_date){
						/*$FlowLeave=M('FlowOverTime')->field('day_num,hour_num')->where(array('flow_id'=>$flow_id,'use_type'=>'申请加班费','start_time'=>array('between',array($start_date,$end_date))))->select();
				
						foreach ($FlowLeave as $key3 => $value3) {
							$info[$key]['overtime_weekends'] += $value3['day_num']+$value3['hour_num']/8;
						}*/
						$FlowLeave=get_leave_seconds_weekend(strtotime($start_time),strtotime($end_time));
						$info[$key]['overtime_weekends'] += $FlowLeave/3600/8;
					}elseif ($start_time < $start_date && $end_time < $end_date && $end_time > $start_date) {//2
						$FlowLeave=get_leave_seconds_weekend(strtotime($start_date),strtotime($end_time));
						$info[$key]['overtime_weekends'] += $FlowLeave/3600/8;

					}elseif ($start_time > $start_date && $end_time > $end_date) {//3
						$FlowLeave=get_leave_seconds_weekend(strtotime($start_time),strtotime($end_date));
						$info[$key]['overtime_weekends'] += $FlowLeave/3600/8;

					}elseif ($start_time < $start_date && $end_time > $end_date) {//4
						$FlowLeave=get_leave_seconds_weekend(strtotime($start_date),strtotime($end_date));
						$info[$key]['overtime_weekends'] += $FlowLeave/3600/8;
					}
					}
					
				}
			}
			//法定
			foreach ($info as $key => $value) {
				$info[$key]['overtime_legal']=0;
				$flow_ids=M('Flow')->where(array('user_id'=>$value['id'],'type'=>57))->getField('id',true);
				foreach ($flow_ids as $key2 => $flow_id) {
					
					$start_time=date('Y-m-d H:i:s',strtotime(M('FlowOverTime')->where(array('flow_id'=>$flow_id))->getField('start_time')));
					$end_time=date('Y-m-d H:i:s',strtotime(M('FlowOverTime')->where(array('flow_id'=>$flow_id))->getField('end_time')));
					if(M('FlowOverTime')->where(array('flow_id'=>$flow_id))->getField('use_type') == '申请加班费' && M('Flow')->where(array('id'=>$flow_id))->getField('step') == '40'){
						if($start_time >= $start_date && $end_time <= $end_date){
						// $FlowLeave=M('FlowOverTime')->field('day_num,hour_num')->where(array('flow_id'=>$flow_id,'use_type'=>'申请加班费','start_time'=>array('between',array($start_date,$end_date))))->select();
						
						$FlowLeave=get_leave_seconds_fading_holidy(strtotime($start_time),strtotime($end_time));
						$info[$key]['overtime_legal'] += $FlowLeave/3600/8;
						/*foreach ($FlowLeave as $key3 => $value3) {
							$info[$key]['overtime_legal'] += $value3['day_num']+$value3['hour_num']/8;
						}*/
						}elseif ($start_time < $start_date && $end_time < $end_date && $end_time > $start_date) {//2
							$FlowLeave=get_leave_seconds_fading_holidy(strtotime($start_date),strtotime($end_time));
							$info[$key]['overtime_legal'] += $FlowLeave/3600/8;

						}elseif ($start_time > $start_date && $end_time > $end_date) {//3
							$FlowLeave=get_leave_seconds_fading_holidy(strtotime($start_time),strtotime($end_date));
							$info[$key]['overtime_legal'] += $FlowLeave/3600/8;

						}elseif ($start_time < $start_date && $end_time > $end_date) {//4
							$FlowLeave=get_leave_seconds_fading_holidy(strtotime($start_date),strtotime($end_date));
							$info[$key]['overtime_legal'] += $FlowLeave/3600/8;
						}
					}
					
				}
			}
		}else{
			
			foreach ($attendance_table as $k => $v) {
				$attendance_table[$k]['name'] = $attendance_table[$k]['user_name'];
				$attendance_table[$k]['id'] = $attendance_table[$k]['user_id'];
			}
			$info = $attendance_table;
			// foreach ($attendance_table as $k => $v) {
			// 	foreach ($info as $k2 => $v2) {
			// 		if($v2['id'] == $v['user_id']){
			// 			$info[$k2]['attendance_day'] = $v['attendance_day'];
			// 			$info[$k2]['actually_day'] = $v['actually_day'];
			// 			$info[$k2]['late'] = $v['late'];
			// 			$info[$k2]['sick_leave'] = $v['sick_leave'];
			// 			$info[$k2]['casual_leave'] = $v['casual_leave'];
			// 			$info[$k2]['absent'] = $v['absent'];
			// 			$info[$k2]['maternity_leave'] = $v['maternity_leave'];
			// 			$info[$k2]['marriage_leave'] = $v['marriage_leave'];
			// 			$info[$k2]['bereavement_leave'] = $v['bereavement_leave'];
			// 			$info[$k2]['accidents'] = $v['accidents'];
			// 			$info[$k2]['annual_leave'] = $v['annual_leave'];
			// 			$info[$k2]['leave_in_lieu'] = $v['leave_in_lieu'];
			// 			$info[$k2]['overtime_weekday'] = $v['overtime_weekday'];
			// 			$info[$k2]['overtime_weekends'] = $v['overtime_weekends'];
			// 			$info[$k2]['overtime_legal'] = $v['overtime_legal'];
			// 			$info[$k2]['growth_sponsorship'] = $v['growth_sponsorship'];
			// 			$info[$k2]['remark'] = $v['remark'];
			// 		}
					
			// 	}
			// }
		}
		
		//dump($info);die;
		/*//序号连续
		$rows = get_user_config('list_rows');
		if(isset($_POST['p'])){
			$number = $_POST['p']*$rows-$rows+1;
		}else{
			$number = 1*$rows-$rows+1;
		}
		$this -> assign('rows',$number);*/
		$this->assign('should_day',$should_day);
		$this->assign('info',$info);
		$this->assign('data',$data);
		$this->display();
	}

	//下载模板
	public function down() {
		$this -> _down();
	}
	//导入模板数据
	function import_attendance(){
		$opmode = $_POST['opmode'];
		if($opmode == 'add'){
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
		if (!empty($_FILES)) {
			import("@.ORG.Util.UploadFile");
			$upload = new UploadFile();
			$upload -> subFolder = strtolower(MODULE_NAME);
			$upload -> savePath = get_save_path();
			$upload -> saveRule = "uniqid";
			$upload -> autoSub = true;
			$upload -> subType = "date";
			$upload -> allowExts = array('xlsx','xls');
			if (!$upload -> upload()) {//上传模板失败
				$this -> error($upload -> getErrorMsg());
			} else {
				//取得成功上传的文件信息
				$upload_list = $upload -> getUploadFileInfo();
				$file_info = $upload_list[0];
				//导入thinkphp第三方类库
				Vendor('Excel.PHPExcel');
				$inputFileName = $file_info['savepath'] . $file_info["savename"];
				$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
				$sd = $objPHPExcel -> getActiveSheet() -> toArray(null, true, true, true);//转行为数组格式
				/*header("Content-Type:text/html;charset=utf-8");
				dump($sd);die;*/
				//随机判断模板格式
				$a = $sd[1];
				$b = $sd[2];
				if($a['A'] != '序号' || $a['B'] != '部门' || $a['C'] != '姓名' || $a['D'] != '考勤号码' || $a['E'] != '考勤日期时间' || $a['F'] != '机器号'){
					if (file_exists($inputFileName)) {
						unlink($inputFileName);
					}
					$this -> error('模板格式错误，无法导入!',get_return_url());die;
				}
				//随机判断上传的数据是否为空
				$x = 2;
				while (!empty($sd[$x]['A'])){$x++;}//确定一共有多少条数据
				for ($i=2;$i<$x;$i++){//循环取出每条数据
					if($sd[$i]['A'] == '' || $sd[$i]['B'] == '' || $sd[$i]['C'] == '' || $sd[$i]['D'] == ''  || $sd[$i]['E'] == ''  || $sd[$i]['F'] == ''){
						if (file_exists($inputFileName)) {
							unlink($inputFileName);
						}
					$this -> error('导入信息不全，无法导入!',get_return_url());die;
					}
				}
				$ad = M('attendance');
				$time = time();
				for ($i=2;$i<$x;$i++){//循环取出每条数据
					$info['user_id'] = $sd[$i]['A'];
					$info['dept_name'] = $sd[$i]['B'];
					$info['user_name'] = $sd[$i]['C'];
					$info['num'] = $sd[$i]['D'];
					$info['attendance_time'] = strtotime($sd[$i]['E']);
					$info['machine_no'] = $sd[$i]['F'];
					$info['import_time'] = $time;
					$info['is_del'] = 0;
					
					$res = $ad->where(array('user_id'=>$sd[$i]['A'],'attendance_time'=>strtotime($sd[$i]['E']),'machine_no'=>$sd[$i]['F'],'is_del'=>0))->find();
					if(!$res){
						$today = date('Y-m-d',strtotime($sd[$i]['E']));
						$today_timestamp_start = strtotime($today);
						$today_timestamp_end = strtotime($today.' 24:00:00');
						$res1 = $ad->where(array('user_id'=>$sd[$i]['A'],'attendance_time'=>array('between',array($today_timestamp_start,$today_timestamp_end)),'is_del'=>0))->order('attendance_time asc')->select();
						if(!$res1){
							$info['mark'] = 'in';
						}else{
							$count = count($res1);
							$d_start = $res1[0]['attendance_time'];
							$d_start_id = $res1[0]['id'];
							$d_end = $res1[$count-1]['attendance_time'];
							$d_end_id = $res1[$count-1]['id'];
							if(strtotime($sd[$i]['E'])<$d_start){
								$ad->where(array('id'=>$d_start_id,'mark'=>'in','is_del'=>0))->setField('mark','');
								$info['mark'] = 'in';
							}
							if(strtotime($sd[$i]['E'])>$d_end){
								$ad->where(array('id'=>$d_end_id,'mark'=>'out','is_del'=>0))->setField('mark','');
								$info['mark'] = 'out';
							}
						}
						$ad -> add($info);
						unset($info['mark']);
					}
				}
				//拿到所有数据 删除模板
				if (file_exists($inputFileName)) {
					unlink($inputFileName);
				}
				$this -> success('导入成功！',get_return_url());
			}
		}
		}else{
			$widget['editor'] = true;
			$widget['uploader'] = true;
			$this -> assign("widget", $widget);	
			$this -> display();
		}
	}
	//导入模板数据
	function import_attendance_table(){
		$opmode = $_POST['opmode'];
		if($opmode == 'add'){
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			if (!empty($_FILES)) {
				import("@.ORG.Util.UploadFile");
				$upload = new UploadFile();
				$upload -> subFolder = strtolower(MODULE_NAME);
				$upload -> savePath = get_save_path();
				$upload -> saveRule = "uniqid";
				$upload -> autoSub = true;
				$upload -> subType = "date";
				$upload -> allowExts = array('xlsx','xls');
				if (!$upload -> upload()) {//上传模板失败
					$this -> error($upload -> getErrorMsg());
				} else {
					//取得成功上传的文件信息
					$upload_list = $upload -> getUploadFileInfo();
					$file_info = $upload_list[0];
					//导入thinkphp第三方类库
					Vendor('Excel.PHPExcel');
					$inputFileName = $file_info['savepath'] . $file_info["savename"];
					$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
					$sd = $objPHPExcel -> getActiveSheet() -> toArray(null, true, true, true);//转行为数组格式
					/*header("Content-Type:text/html;charset=utf-8");
					 dump($sd);die;*/
					//随机判断模板格式
					$a = $sd[1];
					$b = $sd[2];
					if($a['A'] != '考勤月份' || $a['B'] != '序号' || $a['C'] != '部门' || $a['D'] != '职务' || $a['E'] != '姓名' || $a['F'] != '应出勤天数' || $a['G'] != '实际出勤' || $a['H'] != '迟到/早退' || $a['I'] != '补勤' || $a['J'] != '病假' || $a['K'] != '事假' || $a['L'] != '旷工'){
						if (file_exists($inputFileName)) {
							unlink($inputFileName);
						}
						$this -> error('模板格式错误，无法导入!',get_return_url());die;
					}
					//随机判断上传的数据是否为空
					$x = 3;
					while (!empty($sd[$x]['A'])){$x++;}//确定一共有多少条数据
// 					for ($i=2;$i<$x;$i++){//循环取出每条数据
// 						if($sd[$i]['A'] == '' || $sd[$i]['B'] == '' || $sd[$i]['C'] == '' || $sd[$i]['D'] == ''  || $sd[$i]['E'] == ''  || $sd[$i]['F'] == ''){
// 							if (file_exists($inputFileName)) {
// 								unlink($inputFileName);
// 							}
// 							$this -> error('导入信息不全，无法导入!',get_return_url());die;
// 						}
// 					}
					$ad = M('AttendanceTable');
					$time = time();
					for ($i=3;$i<$x;$i++){//循环取出每条数据
						$t = str_replace('"年"','-',$sd[$i]['A']);
						$t = str_replace('"月"','',$t);
						$info['month'] = date('Y-m',strtotime($t));
						$info['user_id'] = $sd[$i]['B'];
						$info['dept_name'] = $sd[$i]['C'];
						$info['duty'] = $sd[$i]['D'];
						$info['user_name'] = $sd[$i]['E'];
						$info['should_day'] = $sd[$i]['F'];
						$info['actually_day'] = $sd[$i]['G'];
						$info['late'] = $sd[$i]['H'];
						$info['supply_attendance'] = $sd[$i]['I'];
						$info['sick_leave'] = $sd[$i]['J'];
						$info['casual_leave'] = $sd[$i]['K'];
						$info['absent'] = $sd[$i]['L'];
						$info['maternity_leave'] = $sd[$i]['M'];
						$info['marriage_leave'] = $sd[$i]['N'];
						$info['bereavement_leave'] = $sd[$i]['O'];
						$info['accidents'] = $sd[$i]['P'];
						$info['annual_leave'] = $sd[$i]['Q'];
						$info['leave_in_lieu'] = $sd[$i]['R'];
						$info['overtime_weekday'] = $sd[$i]['S'];
						$info['overtime_weekends'] = $sd[$i]['T'];
						$info['overtime_legal'] = $sd[$i]['U'];
						$info['growth_sponsorship'] = $sd[$i]['V'];
						$info['remark'] = $sd[$i]['W'];
						$info['create_time'] = $time;
						$info['is_del'] = 0;
							
						$res = $ad->where(array('user_id'=>$sd[$i]['B'],'month'=>date('Y-m',strtotime($t)),'is_del'=>0))->find();
						if(!$res){
							$ad -> add($info);
						}
					}
					//拿到所有数据 删除模板
					if (file_exists($inputFileName)) {
						unlink($inputFileName);
					}
					$this -> success('导入成功！',get_return_url());
				}
			}
		}else{
			$widget['editor'] = true;
			$widget['uploader'] = true;
			$this -> assign("widget", $widget);
			$this -> display();
		}
	}
	function export_attendance(){
		$map = unserialize($_REQUEST['map']);
		$map_new = $map;
		unset($map_new['is_del']);
		if(empty($map_new)){
			$this->error('请先设置过滤条件，搜索，再导出！');
		}else{
			$map['mark'] = array('in',array('in','out'));
			$res = M('Attendance')->where($map)->order('import_time desc')->select();
			if(empty($res)){
				$this->error('搜索结果为空，无法导出！');
			}else{
				//导入thinkphp第三方类库
				Vendor('Excel.PHPExcel');
				
				$objPHPExcel = new PHPExcel();
				
				$objPHPExcel -> getProperties() -> setCreator("神洲酷奇ERP") -> setLastModifiedBy("神洲酷奇ERP") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
		
				//编号，类型，标题，登录时间，部门，登录人，状态，审批，协商，抄送，审批情况，自定义字段
				$q = $objPHPExcel -> setActiveSheetIndex(0);
				//第一列为用户
				$q = $q -> setCellValue("A1", '序号');
				$q = $q -> setCellValue("B1", '部门');
				$q = $q -> setCellValue("C1", '姓名');
				$q = $q -> setCellValue("D1", '考勤号码');
				$q = $q -> setCellValue("E1", '考勤日期时间');
				$q = $q -> setCellValue("F1", '机器号');
				$q = $q -> setCellValue("G1", '签入/签出');
				$q = $q -> setCellValue("H1", '备注');
				
				foreach ($res as $k=>$v){
					$i = $k + 2;
					$q = $q -> setCellValue("A".$i, $v['user_id']);
					$q = $q -> setCellValue("B".$i, $v['dept_name']);
					$q = $q -> setCellValue("C".$i, $v['user_name']);
					$q = $q -> setCellValue("D".$i, $v['num']);
					$q = $q -> setCellValue("E".$i, date('Y-m-d H:i:s',$v['attendance_time']));
					$q = $q -> setCellValue("F".$i, $v['machine_no']);
					$q = $q -> setCellValue("G".$i, $v['mark']=='in'?'签入':'签出');
					$q = $q -> setCellValue("H".$i, $v['style']);
				}
				
				$q ->getColumnDimension('A')->setWidth(20);
				$q ->getColumnDimension('B')->setWidth(20);
				$q ->getColumnDimension('C')->setWidth(20);
				$q ->getColumnDimension('D')->setWidth(20);
				$q ->getColumnDimension('E')->setWidth(20);
				$q ->getColumnDimension('F')->setWidth(20);
				$q ->getColumnDimension('G')->setWidth(20);
				$q ->getColumnDimension('H')->setWidth(20);
				// Rename worksheet
				$title = '打卡信息';
				$objPHPExcel -> getActiveSheet() -> setTitle('打卡信息');
				
				// Set active sheet index to the first sheet, so Excel opens this as the first sheet
				$objPHPExcel -> setActiveSheetIndex(0);
				$file_name = $title.".xlsx";
				// Redirect output to a client’s web browser (Excel2007)
				header("Content-Type: application/force-download");
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header("Content-Disposition:attachment;filename =" . str_ireplace('+', '%20', URLEncode($file_name)));
				header('Cache-Control: max-age=0');
				
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
				//readfile($filename);
				$objWriter -> save('php://output');
				exit ;
			}
		}
	}
	function export_attendance_table(){
		$id=$_REQUEST['id'];
		$month=M("AttendanceMonth")->find($id);
		//dump($id);die;
		$map['attendance_month_id']=$id;
		$map_new = $map;
		unset($map_new['is_del']);
		/*if(empty($map_new)){
			$this->error('请先设置过滤条件，搜索，再导出！');
		}else{*/
			$res = M('AttendanceTable')->where($map)->select();
			//dump($res);die;
				//导入thinkphp第三方类库
				Vendor('Excel.PHPExcel');
	
				$objPHPExcel = new PHPExcel();
	
				$objPHPExcel -> getProperties() -> setCreator("小微OA") -> setLastModifiedBy("小微OA") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
	
				//编号，类型，标题，登录时间，部门，登录人，状态，审批，协商，抄送，审批情况，自定义字段
				$q = $objPHPExcel -> setActiveSheetIndex(0);
				//第一列为用户
				$q = $q -> setCellValue("A1", '考勤月份');
				$q = $q -> mergeCells("A1:A2");
				$q = $q -> setCellValue("B1", '序号');
				$q = $q -> mergeCells("B1:B2");
				$q = $q -> setCellValue("C1", '部门');
				$q = $q -> mergeCells("C1:C2");
				$q = $q -> setCellValue("D1", '职务');
				$q = $q -> mergeCells("D1:D2");
				$q = $q -> setCellValue("E1", '姓名');
				$q = $q -> mergeCells("E1:E2");
				$q = $q -> setCellValue("F1", '应出勤天数');
				$q = $q -> mergeCells("F1:F2");
				$q = $q -> setCellValue("G1", '实际出勤');
				$q = $q -> mergeCells("G1:G2");
				$q = $q -> setCellValue("H1", '迟到/早退');
				$q = $q -> mergeCells("H1:H2");
				$q = $q -> setCellValue("I1", '补勤');
				$q = $q -> mergeCells("I1:I2");
				$q = $q -> setCellValue("J1", '病假');
				$q = $q -> mergeCells("J1:J2");
				$q = $q -> setCellValue("K1", '事假');
				$q = $q -> mergeCells("K1:K2");
				$q = $q -> setCellValue("L1", '旷工');
				$q = $q -> mergeCells("L1:L2");
				$q = $q -> setCellValue("M1", '带薪假');
				$q = $q -> mergeCells("M1:R1");
				$q = $q -> setCellValue("S1", '加班');
				$q = $q -> mergeCells("S1:U1");
				$q = $q -> setCellValue("V1", '成长赞助');
				$q = $q -> mergeCells("V1:V2");
				$q = $q -> setCellValue("W1", '备注');
				$q = $q -> mergeCells("W1:W2");
				$q = $q -> setCellValue("X1", '员工签名');
				$q = $q -> mergeCells("X1:X2");
				
				$q = $q -> setCellValue("M2", '产假');
				$q = $q -> setCellValue("N2", '婚假');
				$q = $q -> setCellValue("O2", '丧假');
				$q = $q -> setCellValue("P2", '工伤');
				$q = $q -> setCellValue("Q2", '年假');
				$q = $q -> setCellValue("R2", '调休');
				$q = $q -> setCellValue("S2", '平时');
				$q = $q -> setCellValue("T2", '周末');
				$q = $q -> setCellValue("U2", '法定');
	
				foreach ($res as $k=>$v){
					$i = $k + 3;
					$q = $q -> setCellValue("A".$i, $month['month']);
					$q = $q -> setCellValue("B".$i, $v['user_id']);
					$q = $q -> setCellValue("C".$i, $v['dept_name']);
					$q = $q -> setCellValue("D".$i, $v['duty']);
					$q = $q -> setCellValue("E".$i, $v['user_name']);
					$q = $q -> setCellValue("F".$i, $v['should_day']);
					$q = $q -> setCellValue("G".$i, $v['actually_day']);
					
					$q = $q -> setCellValue("H".$i, $v['late']);
					$q = $q -> setCellValue("I".$i, $v['attendance_day']);
					$q = $q -> setCellValue("J".$i, $v['sick_leave']);
					$q = $q -> setCellValue("K".$i, $v['casual_leave']);
					$q = $q -> setCellValue("L".$i, $v['absent']);
					$q = $q -> setCellValue("M".$i, $v['maternity_leave']);
					
					$q = $q -> setCellValue("N".$i, $v['marriage_leave']);
					$q = $q -> setCellValue("O".$i, $v['bereavement_leave']);
					$q = $q -> setCellValue("P".$i, $v['accidents']);
					$q = $q -> setCellValue("Q".$i, $v['annual_leave']);
					$q = $q -> setCellValue("R".$i, $v['leave_in_lieu']);
					$q = $q -> setCellValue("S".$i, $v['overtime_weekday']);
					
					$q = $q -> setCellValue("T".$i, $v['overtime_weekends']);
					$q = $q -> setCellValue("U".$i, $v['overtime_legal']);
					$q = $q -> setCellValue("V".$i, $v['growth_sponsorship']);
					$q = $q -> setCellValue("W".$i, $v['remark']);
					if($v['sign']==1){
						$q = $q -> setCellValue("X".$i, $v['user_name']);
					}
				}
	
// 				$q ->getColumnDimension('A')->setWidth(20);
// 				$q ->getColumnDimension('B')->setWidth(20);
// 				$q ->getColumnDimension('C')->setWidth(20);
// 				$q ->getColumnDimension('D')->setWidth(20);
// 				$q ->getColumnDimension('E')->setWidth(20);
// 				$q ->getColumnDimension('F')->setWidth(20);
// 				$q ->getColumnDimension('G')->setWidth(20);
				// Rename worksheet
				$title = '考勤表';
				$objPHPExcel -> getActiveSheet() -> setTitle('考勤表');
	
				// Set active sheet index to the first sheet, so Excel opens this as the first sheet
				$objPHPExcel -> setActiveSheetIndex(0);
				$file_name = $title.".xlsx";
				// Redirect output to a client’s web browser (Excel2007)
				header("Content-Type: application/force-download");
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header("Content-Disposition:attachment;filename =" . str_ireplace('+', '%20', URLEncode($file_name)));
				header('Cache-Control: max-age=0');
	
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
				//readfile($filename);
				$objWriter -> save('php://output');
				exit ;
		/*}*/
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
		$id = $_REQUEST['id'];
		if (empty($id)) {
			$this -> error('没有可删除的数据!');
		}
		$res = M('Attendance')->where(array('id'=>$id))->setField('is_del',1);
		if($res){
			$this -> success('删除成功');
		}else{
			$this -> error('删除失败');
		}
	}
	function del_table(){
		$id = $_REQUEST['id'];
		if (empty($id)) {
			$this -> error('没有可删除的数据!');
		}
		$res = M('AttendanceTable')->where(array('id'=>$id))->setField('is_del',1);
		if($res){
			$this -> success('删除成功');
		}else{
			$this -> error('删除失败');
		}
	}
	
	//统计
	function statistics(){
		$pinfo = M('Attract') -> where('is_del = 0') -> field('id')->select();
		foreach ($pinfo as $k=>$v){
			if($v['id']){
				$arr[] = $v['id'];
			}
		}
		$map = $this -> _search("Attract_detail");
		if(!empty($map)){$this -> assign('sfexport','1');}
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$map['pid'] = array('in',$arr);
		$model = D('Attract_detail');
		//详细列表
		if (!empty($model)) {
			$info = $this -> _list($model, $map,'riqi');
			$this -> assign('info', $info);
		}
		//今天是
		$attr = M('attract');
		if(!empty($map['months'])){
			$item = $map['months'][0];
			if (stripos($item, "egt")!==false || stripos($item, "elt")!==false){
				$mid = $map['months'][1];
			}else{
				$mid = $map['months'][1][1];
			}
			$mid_y = substr($mid, 0, 4);
			$mid_m = substr($mid, 4, 2);
			$mid = $mid_y . '/' . $mid_m;
			$prefix = substr($mid, 0, 7);
			$where3['end_time'] = array('like', $prefix .'%');
			$where3['is_del'] = 0;
			$data = $attr -> where($where3) -> find();
			$this -> assign('sfexport','1');
		}else{
			$mid = $attr -> where('is_del = 0') -> max('id');
			$data = $attr -> find($mid);
		}
		$where2['pid'] = array('in',$arr);
		$addr = $model -> where($where2) -> field('base as id,base as name') ->distinct(true) -> select();
		$this -> assign('data',$data);
		$this -> assign('addr_list', $addr);
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign('post',$_POST);
		//完成率
		$finsh = $this -> jsfinsh($data['actuality'],$data['target']);
		$this -> assign('finsh',$finsh);
		
		$d = strtotime($data['today']);
		$tmp = explode('/',$data['today']);
		$to = strtotime($tmp[0].'/'.$tmp[1].'/01');
		$day = ($d - $to) / 86400;
		$lv = $this -> jsfinsh($day+1,$data['days']);
		$this -> assign('lv',$lv);
		//序号连续
		$rows = get_user_config('list_rows');
		if(isset($_POST['p'])){
			$number = $_POST['p']*$rows-$rows+1;
		}else{
			$number = 1*$rows-$rows+1;
		}
		$this -> assign('rows',$number);
		$this -> display();
	}
	
	function jsfinsh($a,$b,$c=2){
		$d = (intval($a)/intval($b))*100;
		return round($d,$c);
	}
	
	//导出
	public function export_info(){
		//导入thinkphp第三方类库
		Vendor('Excel.PHPExcel');
		
		$objPHPExcel = new PHPExcel();
		
		$objPHPExcel -> getProperties() -> setCreator("神洲酷奇OA") -> setLastModifiedBy("神洲酷奇OA") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
		// Add some data
		$q = $objPHPExcel -> setActiveSheetIndex(0);
		//添加招商总计信息
		//今天是
		$map = $this -> _search("Attract_detail");
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$attr = M('attract');
		if(!empty($map['months'])){
			$item = $map['months'][0];
			if (stripos($item, "egt")!==false || stripos($item, "elt")!==false){
				$mid = $map['months'][1];
			}else{
				$mid = $map['months'][1][1];
			}
			$mid_y = substr($mid, 0, 4);
			$mid_m = substr($mid, 4, 2);
			$mid = $mid_y . '/' . $mid_m;
			$prefix = substr($mid, 0, 7);
			$where3['end_time'] = array('like', $prefix .'%');
			$where3['is_del'] = 0;
			$data = $attr -> where($where3) -> find();
			$this -> assign('sfexport','1');
		}else{
			$mid = $attr -> where('is_del = 0') -> max('id');
			$data = $attr -> find($mid);
		}
		//完成率
		$finsh = $this -> jsfinsh($data['actuality'],$data['target']);
		
		$d = strtotime($data['today']);
		$tmp = explode('/',$data['today']);
		$to = strtotime($tmp[0].'/'.$tmp[1].'/01');
		$day = ($d - $to) / 86400;
		$lv = $this -> jsfinsh($day+1,$data['days']);
		$this -> assign('lv',$lv);
		
		$q = $q -> setCellValue('A1', '截止目前时间进度');
		$q = $q -> setCellValue('B1', $lv.'%');
		$q = $q -> setCellValue('C1', '今天是');
		$q = $q -> setCellValue('D1', $data['today']);
		$q = $q -> setCellValue('E1', '考核截止日期');
		$q = $q -> setCellValue('F1', $data['end_time']);
		$q = $q -> setCellValue('G1', '本次考核周期天数');
		$q = $q -> setCellValue('H1', $data['days']);
		$q = $q -> setCellValue('I1', '截止目前任务完成进度');
		$q = $q -> setCellValue('J1', $finsh.'%');
		$q = $q -> setCellValue('K1', '本月累计签约目标/单');
		$q = $q -> setCellValue('L1', $data['target']);
		$q = $q -> setCellValue('M1', '当月实际签约/单');
		$q = $q -> setCellValue('N1', $data['actuality']);
		$q = $q -> setCellValue('O1', '截至目前签约总量');
		$q = $q -> setCellValue('P1', $data['total_sign']);
		//添加列表 头信息
		$tit = array('日期','招商方式','信息来源','招商人员','客户姓名','客户电话','主营行业','预计日发单量','客户意向','客户最关心的问题','接洽内容&备注','是否到访','客户到访日期','是否签约','签约日期','签约日单量');
		$n = 0;
		for ($i=ord('A');$i<=ord('P');$i++){
			for($j = 1 ;$j < 3 ; $j++){
				$q = $q -> setCellValue(chr($i).'2', $tit[$n]);
				$q->getColumnDimension(chr($i))->setWidth(15);
				$q->getStyle(chr($i).'2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID); 
				$q->getColumnDimension('J')->setWidth(30);
				$q->getColumnDimension('K')->setWidth(30);
				$q -> getRowDimension($j)->setRowHeight(35);
				$q -> getStyle(chr($i).$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置水平对齐方式
				$q -> getStyle(chr($i).$j)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//设置垂直居中
				$q -> getStyle(chr($i).$j)->getFill()->getStartColor()->setARGB('FF808080');
				$q -> getStyle(chr($i).$j)->getFont()->setName('微软雅黑');
				$q -> getStyle(chr($i).$j)->getFont()->setSize(11);
			}
			$n++;	
		}
		
		$pinfo = M('Attract') -> where('is_del = 0') -> field('id')->select();
		foreach ($pinfo as $k=>$v){
			if($v['id']){
				$arr[] = $v['id'];
			}
		}
	
		$map['pid'] = array('in',$arr);
		$list = M('Attract_detail') -> where($map) -> order('riqi DESC') -> select();
		foreach ($list as $k => $v){
			$i = $k+3;
			$q = $q -> setCellValue('A'.$i , $v['riqi']);
			$q = $q -> setCellValue('B'.$i , $v['manner']);
			$q = $q -> setCellValue('C'.$i , $v['source']);
			$q = $q -> setCellValue('D'.$i , $v['person']);
			$q = $q -> setCellValue('E'.$i , $v['client']);
			$q = $q -> setCellValue('F'.$i , $v['phone']);
			$q = $q -> setCellValue('G'.$i , $v['trade']);
			$q = $q -> setCellValue('H'.$i , $v['receipt']);
			$q = $q -> setCellValue('I'.$i , $v['intention']);
			$q = $q -> setCellValue('J'.$i , $v['concern']);
			$q = $q -> setCellValue('K'.$i , $v['remarks']);
			$q = $q -> setCellValue('L'.$i , $v['visited']);
			$q = $q -> setCellValue('M'.$i , $v['visitdate']);
			$q = $q -> setCellValue('N'.$i , $v['signed']);
			$q = $q -> setCellValue('O'.$i , $v['signdate']);
			$q = $q -> setCellValue('P'.$i , $v['signreceipt']);
			for ($j=ord('A');$j<=ord('J');$j++){
				$q -> getStyle()->getFont(ord($j).$i)->setName('微软雅黑');
				$q -> getStyle()->getFont(ord($j).$i)->setSize(11);
				$q -> getStyle(chr($j).$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置水平对齐方式
			}
		}
			
		// Rename worksheet
		$title = '招商进度日报导出';
		$objPHPExcel -> getActiveSheet() -> setTitle($title);
		
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel -> setActiveSheetIndex(0);
		$file_name = $title.".xlsx";
		// Redirect output to a client’s web browser (Excel2007)
		header("Content-Type: application/force-download");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition:attachment;filename =" . str_ireplace('+', '%20', URLEncode($file_name)));
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		//readfile($filename);
		$objWriter -> save('php://output');
		exit ;
	}
	public function mark() {
		$action = $_REQUEST['action'];
		$id = $_REQUEST['id'];
		switch ($action) {
			case 'del' :
				$where['id'] = array('in', $id);
				$result = M("Attendance") -> where($where) -> setField('is_del',1);
				if ($result) {
					$this -> ajaxReturn('', "删除成功", 1);
				} else {
					$this -> ajaxReturn('', "删除失败", 0);
				}
				break;
			case 'del_table' :
				$where['id'] = array('in', $id);
				$result = M("AttendanceTable") -> where($where) -> setField('is_del',1);
				if ($result) {
					$this -> ajaxReturn('', "删除成功", 1);
				} else {
					$this -> ajaxReturn('', "删除失败", 0);
				}
				break;
			default :
				break;
		}
	}

	function create_attendance(){
		$model=M("AttendanceMonth");
		$info['month'] = $_POST['month'];
		$info['attendance_dept'] = $_POST['dept'];
		$info['create_time'] = time();
		$info['create_name'] = get_user_name();
		$data=M('User')->where(array('name'=>array('eq',get_user_name())))->find();
		$info['dept_name']=get_dept_name();
		$info['duty_name']=$data['duty'];
		$last = $model->where(array('number'=>array('like',date('ymd',time()).'%')))->order('number desc')->limit(1)->find();
			if($last){
				$num = intval(substr($last['number'],4));
				$num_str = formatto4w($num+1);
			}else{
				$num_str = formatto4w(1);
			}
		$info['number'] = date('ymd',time()).$num_str;
		$res = $model->add($info);
		if($res){
			$this->ajaxReturn('1','success','1');
		}else{
			$this->ajaxReturn('0','failed','0');
		}
		
	}
	public function create_attendance_detail(){
		$user_ids = $_POST['user_id'];//3
		foreach ($user_ids as $k => $user_id) {
			$data['attendance_month_id'] = $_POST['attendance_month_id'];
			$data['user_id'] = $user_id;
			$data['dept_name'] = $_POST['dept_name'][$k];
			$data['duty'] = $_POST['duty'][$k];
			$data['user_name'] = $_POST['name'][$k];
			$data['should_day'] = $_POST['should_day'][$k];
			$data['actually_day'] = $_POST['actually_day'][$k];
			$data['late'] = $_POST['late'][$k];
			$data['attendance_day'] = $_POST['attendance_day'][$k];
			$data['sick_leave'] = $_POST['sick_leave'][$k];
			$data['casual_leave'] = $_POST['casual_leave'][$k];
			$data['absent'] = $_POST['absent'][$k];
			$data['maternity_leave'] = $_POST['maternity_leave'][$k];
			$data['marriage_leave'] = $_POST['marriage_leave'][$k];
			$data['bereavement_leave'] = $_POST['bereavement_leave'][$k];
			$data['accidents'] = $_POST['accidents'][$k];
			$data['annual_leave'] = $_POST['annual_leave'][$k];
			$data['leave_in_lieu'] = $_POST['leave_in_lieu'][$k];
			$data['overtime_weekday'] = $_POST['overtime_weekday'][$k];
			$data['overtime_weekends'] = $_POST['overtime_weekends'][$k];
			$data['overtime_legal'] = $_POST['overtime_legal'][$k];
			$data['growth_sponsorship'] = $_POST['growth_sponsorship'][$k];
			$data['remark'] = $_POST['remark'][$k];
			$data['create_time']=date('Y-m-d H:i:s',time());
			$res = M("AttendanceTable")->where(array('attendance_month_id'=>$data['attendance_month_id'],'user_id'=>$data['user_id']))->find();
			if(empty($res)){
				M("AttendanceTable")->add($data);
				//发送站内信
				$info['sender_id']=1;
				$info['sender_name']='管理员';
				$info['receiver_id'] = $data['user_id'];
				$info['receiver_name'] = $data['user_name'];
				$info['owner_id'] = $data['user_id'];
				$info['content'] = '请进入员工档案确认考勤，<a href="'.U('Profile/user?id='.$data['user_id']).'">点击跳转</a>';
				$info['create_time']=time();
				M('Message') -> add($info);
				$this -> _pushReturn("", "您有新的消息, 请注意查收", 1,$data['user_id']);

			}else{
				M("AttendanceTable")->where(array('attendance_month_id'=>$data['attendance_month_id'],'user_id'=>$data['user_id']))->save($data);
			}
			//删除多余记录
			M("AttendanceTable")->where(array('attendance_month_id'=>$data['attendance_month_id'],'user_id'=>array('not in',$user_ids)))->delete();
		}
		//dump($data);die;
		//dump($_POST);die;
		$this -> success('保存成功',U('new_table'));
	}

	public function read_attendance_detail(){
		$this->edit_table();
		
	}
	function del_attendance(){
		$id = $_REQUEST['id'];
		$where['id'] = array('in', $id);
		$result = M("AttendanceMonth")->where($where)->delete();
			if ($result) {
				$this -> ajaxReturn('', "删除成功", 1);
			} else {
				$this -> ajaxReturn('', "删除失败", 0);
			}
	}
}
<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class HomeAction extends CommonAction {
	protected $config = array('app_type' => 'asst');
	//过滤查询字段

	function _search_filter(&$map) {
		if (!empty($_POST['keyword'])) {
			$map['type|name|code'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}

	public function index() {
		$widget['jquery-ui'] = true;
		$this -> assign("widget", $widget);

		cookie("current_node", null);
		cookie("top_menu", null);

		$config = D("UserConfig") -> get_config();
		$this -> assign("home_sort", $config['home_sort']);
		$this -> assign("ceo_incentive", get_system_config("CEO_INCENTIVE"));
 		$this -> _mail_list();
// 		$this -> _flow_list();
// 		$this -> _schedule_list();
		$this -> _notice_list();
// 		$this -> _doc_list();
// 		$this -> _forum_list();
// 		$this -> _news_list();
// 		$this -> _slide_list();
		$this -> _task_list();
		$this -> _shouxing_list();
		$this -> _jinianri_list();
		$this -> _xinjin_list();
		$this -> _daily_list();
		$user = M('User')->find(get_user_id());
		$this -> assign("bianqian", $user['bianqian']);
		$this -> display();
	}

	public function set_sort() {
		$val = $_REQUEST["val"];
		$data['home_sort'] = $val;
		$model = D("UserConfig") -> set_config($data);
	}
	
	protected function _daily_list(){
		//日报
		$child_ids = array_merge(array(intval(get_user_id())),array_keys(array_to_one_dimension(get_child_ids_all(get_user_id()))));
		$map['user_id'] = array('in',$child_ids);
		$model = D("DailyReport");
		$dailyList = $model -> where($map) -> field("id,user_name,work_date,create_time") -> order("create_time desc") -> limit(6) -> select();
		$this->assign('dailyList',$dailyList);
		//周报
		$child_ids = array_merge(array(intval(get_user_id())),array_keys(array_to_one_dimension(get_child_ids_all(get_user_id()))));
		$map['user_id'] = array('in',$child_ids);
		$model = D("WeeklyReport");
		$weeklyList = $model -> where($map) -> field("id,user_name,work_date,create_time") -> order("create_time desc") -> limit(6) -> select();
		$this -> assign('weeklyList',$weeklyList);
		//月报
		$child_ids = array_merge(array(intval(get_user_id())),array_keys(array_to_one_dimension(get_child_ids_all(get_user_id()))));
		$map['user_id'] = array('in',$child_ids);
		$model = D("MonthlyReport");
		$monthlyList = $model -> where($map) -> field("id,user_name,work_date,create_time") -> order("create_time desc") -> limit(6) -> select();
		$this-> assign('monthlyList',$monthlyList);
	}

	protected function _mail_list() {
		$user_id = get_user_id();
		$model = D('Mail');

		//获取最新邮件
		$where['user_id'] = $user_id;
		$where['is_del'] = array('eq', '0');
		$where['folder'] = array( array('eq', 1), array('gt', 6), 'or');

		$new_mail_list = $model -> where($where) -> field("id,name,create_time") -> order("create_time desc") -> limit(6) -> select();
		$this -> assign('new_mail_list', $new_mail_list);

		//获取未读邮件
		$where['read'] = array('eq', '0');
		$unread_mail_list = $model -> where($where) -> field("id,name,create_time") -> order("create_time desc") -> limit(6) -> select();
		$this -> assign('unread_mail_list', $unread_mail_list);
	}

	protected function _flow_list() {
		$user_id = get_user_id();
		$emp_no = get_emp_no();
		$model = D('Flow');
		//带审批的列表
		$FlowLog = M("FlowLog");
		$where['emp_no'] = $emp_no;
		$where['_string'] = "result is null";
		$log_list = $FlowLog -> where($where) -> field('flow_id') -> select();
		$log_list = rotate($log_list);
		if (!empty($log_list)) {
			$map['id'] = array('in', $log_list['flow_id']);
			$todo_flow_list = $model -> where($map) -> field("id,name,create_time") -> limit(6) -> order("create_time desc") -> select();
			$this -> assign("todo_flow_list", $todo_flow_list);
		}
		//已提交
		$map = array();
		$map['user_id'] = $user_id;
		$map['step'] = array('gt', 10);
		$submit_process_list = $model -> where($map) -> field("id,name,create_time") -> limit(6) -> order("create_time desc") -> select();
		$this -> assign("submit_flow_list", $submit_process_list);
	}

	protected function _doc_list() {
		$user_id = get_user_id();
		$model = D('Doc');
		//获取最新邮件

		$where['is_del'] = array('eq', '0');
		$folder_list = D("SystemFolder") -> get_authed_folder(get_user_id(), "DocFolder");
		$where['folder'] = array("in", $folder_list);
		$doc_list = $model -> where($where) -> field("id,name,create_time") -> order("create_time desc") -> limit(6) -> select();
		$this -> assign("doc_list", $doc_list);
	}

	protected function _news_list() {
		$user_id = get_user_id();
		$model = D('News');

		$where['is_del'] = array('eq', '0');
		$folder_list = D("SystemFolder") -> get_authed_folder(get_user_id(), "NewsFolder");
		$where['folder'] = array("in", $folder_list);
		$news_list = $model -> where($where) -> field("id,name,create_time") -> order("create_time desc") -> limit(6) -> select();
		$this -> assign("news_list", $news_list);
	}

	protected function _slide_list() {
		$slide_list = M("Slide") -> where($where) -> order('sort asc') -> select();
		$this -> assign("slide_list", $slide_list);
	}

	protected function _schedule_list() {
		$user_id = get_user_id();
		$model = M('Schedule');
		//获取最新邮件
		$start_date = date("Y-m");

		$where['user_id'] = $user_id;
		$where['start_time'] = array('egt', $start_date);
		$schedule_list = M("Schedule") -> where($where) -> order('start_time,priority desc') -> limit(6) -> select();
		$this -> assign("schedule_list", $schedule_list);

		$model = M("Todo");
		$where = array();
		$where['user_id'] = $user_id;
		$where['status'] = array("in", "1,2");
		$todo_list = M("Todo") -> where($where) -> order('priority desc,sort asc') -> limit(6) -> select();
		$this -> assign("todo_list", $todo_list);
	}

	protected function _notice_list() {
		$model = D('Notice');
		//获取最新通知
		$where['is_del'] = array('eq', '0');
		$folder_list = D("SystemFolder") -> get_authed_folder(get_user_id(), "NoticeFolder");
		$where['folder'] = array("in", $folder_list);
		$new_notice_list = $model -> where($where) -> field("id,name,content,folder,create_time,add_file") -> order("create_time desc") -> select();
		foreach ($new_notice_list as $k=>$v){
			if(!empty($v['add_file'])){			
				$files = array_filter(explode(';', $v['add_file']));
				$where['sid'] = array('in', $files);
				$model = M("File");
				$file_list = $model -> where($where) -> find();
				$new_notice_list[$k]['file_list']=$file_list['savename'];
			}
			if($v['folder'] == 71){
				$new_notice_list[$k]['name'] = substr($v['name'],18);	
			}
		}
		$this -> assign("new_notice_list", $new_notice_list);
	}

	protected function _forum_list() {
		$model = D('Forum');
		$where['is_del'] = array('eq', '0');
		$folder_list = D("SystemFolder") -> get_authed_folder(get_user_id(), "ForumFolder");
		$where['folder'] = array("in", $folder_list);
		$new_forum_list = $model -> where($where) -> field("id,name,create_time") -> order("create_time desc") -> limit(6) -> select();
		$this -> assign("new_forum_list", $new_forum_list);
	}

	protected function _task_list() {
		//所有任务
		$model = M("Task");
		$where = array();
		$task_all_count = $model -> where($where) -> field('id,name,executor,create_time') -> order('create_time desc') ->limit(6) -> select();
		$this -> assign("task_all_count", $task_all_count);
		
		//等我接受的任务
		
		$where = array();
		$where_log['type'] = 1;
		$where_log['status'] = 0;
		$where_log['executor'] = get_user_id();
		$task_todo_list = M("TaskLog") -> where($where_log) -> getField('task_id id,task_id');
		$where['id'] = array('in', $task_todo_list);

		$task_todo_count = $model -> where($where) -> select();
		$this -> assign("task_todo_count", $task_todo_count);
		
		//未完成的任务
		$where_log = array();
		$where_log['status'] = array('eq', 1);
		$where_log['executor'] = get_user_id();
		$task_no_finish_list = M("TaskLog") -> where($where_log) -> getField('task_id id,task_id');
		$where['id'] = array('in', $task_no_finish_list);
		$task_no_finish_count = $model -> where($where) -> select();
		$this -> assign("task_no_finish_count", $task_no_finish_count);
		
		//已完成的任务
		$where = array();
		$where['status'] = array('eq', 3);
		$task_finished_count = $model -> where($where) -> count();
		$this -> assign("task_finished_count", $task_finished_count);

		//我部门任务
 		$where = array();
 		$auth = D("Role") -> get_auth("Task");
 		if ($auth['admin']) {
 			$where_log['type'] = 2;
 			$where_log['executor'] = get_dept_id();
 			$task_list = M("TaskLog") -> where($where_log) -> getField('task_id id,task_id');
 			$where['id'] = array('in', $task_list);
 		} else {
 			$where['_string'] = '1=2';
 		}

 		$task_dept_list = $model -> where($where) -> order("create_time desc") -> limit(6) -> select();
 		$this -> assign("task_dept_list", $task_dept_list);
	}
	//本月寿星
	protected function _shouxing_list() {
		$model = D('User');
		$shouxing_list = $model ->where('month(birthday)>0 AND is_del = 0') -> order("abs(month(birthday)-month(now())) asc") ->limit(4) -> field('id,name,dept_id,position_id,sex,birthday,pic,email,duty,office_tel,mobile_tel,create_time') ->select();
		$this -> assign("shouxing_list", $shouxing_list);
	}
	//入职纪念日
	protected function _jinianri_list() {
		$model = D('User');
		$jinianri_list = $model ->where('is_del = 0') -> order("mod(unix_timestamp(now())-create_time,365*24*60*60) asc") ->limit(3) -> field('id,name,dept_id,position_id,sex,birthday,pic,email,duty,office_tel,mobile_tel,create_time') ->select();
		$this -> assign("jinianri_list", $jinianri_list);
	}
	//新进员工
	protected function _xinjin_list() {
		$model = D('User');
		$xinjin_list = $model ->where('is_del = 0') -> order("create_time desc") ->limit(7) -> field('id,name,dept_id,position_id,sex,birthday,pic,email,duty,office_tel,mobile_tel,create_time') ->select();
		$this -> assign("xinjin_list", $xinjin_list);
	}
	public function ajax_get_user_info(){
		$user_id = $_GET['user_id'];
		$model = D('User');
		$where = array();
		$where = array('id'=>$user_id);
		$info = $model->where($where)-> field('id,name,dept_id,position_id,sex,birthday,pic,email,duty,office_tel,mobile_tel,create_time')->find();
		$info['create_time'] = date('Y年m月d日',$info['create_time']);
		$model = D('Dept');
		$where = array();
		$where = array('id'=>$info['dept_id']);
		$dept_info = $model->where($where)-> field('name')->find();
		$info['dept'] = $dept_info['name'];
		$model = D('Position');
		$where = array();
		$where = array('id'=>$info['position_id']);
		$position_info = $model->where($where)-> field('name')->find();
		$info['position'] = $position_info['name']?$position_info['name']:'';
		$this->ajaxReturn($info,'JSONP');
	}
	public function ajax_set_bianqian(){
		$user_id = $_GET['user_id'];
		if($user_id){
			$data['id'] = $user_id;
		}
		$val = $_GET['val'];
		$data['bianqian'] = $val;
		$res = M('User')->save($data);
		if($res){
			$this->ajaxReturn(1,1);
		}else{
			$this->ajaxReturn(null,null);
		}
	}
	public function test(){
		$this->ajaxReturn('aa','JSONP');
	}
	
// 	public function arili() {
// 		$this -> display();
// 	}
}
?>
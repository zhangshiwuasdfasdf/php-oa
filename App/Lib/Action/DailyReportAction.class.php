<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class DailyReportAction extends CommonAction {
	protected $config = array('app_type' => 'common', 'action_auth' => array('share' => 'read', 'plan' => 'read', 'save_comment' => 'write', 'edit_comment' => 'write', 'reply_comment' => 'write', 'del_comment' => 'admin'));
	//过滤查询字段
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_POST['content'])) {
			$where['content'] = array('like', '%' . $_POST['content'] . '%');
			$where['plan'] = array('like', '%' . $_POST['content'] . '%');
			$where['_logic'] = 'or';
			$map['_complex'] = $where;
		}
	}
	
	public function index() {
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign('user_id', get_user_id());

		$auth = $this -> config['auth'];
		$this -> assign('auth', $auth);

		if ($auth['admin']) {
			$node = D("Dept");
			$dept_id = get_dept_id();
			$dept_name = get_dept_name();
			$menu = array();
			$dept_menu = $node -> field('id,pid,name') -> where("is_del=0") -> order('sort asc') -> select();
			$dept_tree = list_to_tree($dept_menu, $dept_id);
			$count = count($dept_tree);
			if (empty($count)) {
				/*获取部门列表*/
				$html = '';
				$html = $html . "<option value='{$dept_id}'>{$dept_name}</option>";
				$this -> assign('dept_list', $html);

				//*获取人员列表*/
				$rank_id = get_user_info(get_user_id(), 'rank_id');
				$where['rank_id'] = array('gt', $rank_id);

				$where['dept_id'] = array('eq', $dept_id);
				$emp_list = D("User") -> where($where) -> getField('id,name');
				$this -> assign('emp_list', $emp_list);
			} else {
				/*获取部门列表*/
				$this -> assign('dept_list', select_tree_menu($dept_tree));
				$dept_list = tree_to_list($dept_tree);
				$dept_list = rotate($dept_list);
				$dept_list = $dept_list['id'];

				/*获取人员列表*/
				$rank_id = get_user_info(get_user_id(), 'rank_id');
				$where['rank_id'] = array('gt', $rank_id);

				$where['dept_id'] = array('in', $dept_list);
				$where['is_submit'] = array('eq', 1);
				$where['_logic'] = 'or';

				$map['_complex'] = $where;
				$map['user_id'] = get_user_id();

				$emp_list = D("User") -> where($map) -> getField('id,name');
				$this -> assign('emp_list', $emp_list);
			}
		}

		$map = $this -> _search();
		if ($auth['admin']) {
			if (empty($map['dept_id'])) {
				if (!empty($dept_list)) {
					$map['dept_id'] = array('in', array_merge($dept_list, array($dept_id)));
				} else {
					$map['dept_id'] = array('eq', $dept_id);
				}
			}
		} else {
			if(D("Role") -> check_duty('SHOW_LOG_LOW_ALL')){//允许查看自己及以下所有日志
				$child_ids = array_merge(array(intval(get_user_id())),array_keys(array_to_one_dimension(get_child_ids_all(get_user_id()))));
				$map['user_id'] = array('in',$child_ids);
			}elseif(D("Role") -> check_duty('SHOW_LOG_LOW')){//允许查看自己及下一级日志
				$child_ids = array_merge(array(intval(get_user_id())),get_child_ids(get_user_id()));
				$map['user_id'] = array('in',$child_ids);
			}
			else{//查看自己的日志
				$map['user_id'] = array('eq',intval(get_user_id()));
			}
			
		}

		if ( D("Role") -> check_duty('SHOW_LOG')) {//查看所有日志
			$node = D("Dept");
			$dept_id = get_dept_id();
			$dept_name = get_dept_name();
			$dept_menu = $node -> field('id,pid,name') -> where("is_del=0") -> order('sort asc') -> select();
			$dept_tree = list_to_tree($dept_menu);
			$count = count($dept_tree);
			if (empty($count)) {
				/*获取部门列表*/
				$html = '';
				$html = $html . "<option value='{$dept_id}'>{$dept_name}</option>";
				$this -> assign('dept_list', $html);
				/*获取人员列表*/
				$where['dept_id'] = array('eq', $dept_id);
				$emp_list = D("User") -> where($where) -> getField('id,name');
				$this -> assign('emp_list', $emp_list);
			} else {
				/*获取部门列表*/
				$this -> assign('dept_list', select_tree_menu($dept_tree));
				$dept_list = tree_to_list($dept_tree);
				$dept_list = rotate($dept_list);
				$dept_list = $dept_list['id'];

				/*获取人员列表*/
				$where['dept_id'] = array('in', $dept_list);
				$emp_list = D("User") -> where($where) -> getField('id,name');
				$this -> assign('emp_list', $emp_list);
			}

			$where = array();
			$map=array();
			$where['is_submit'] = array('eq', 1);
			$where['is_del'] = array('eq', '0');
			$where['_logic'] = 'or';

			$map['_complex'] = $where;
// 			$map['user_id'] = get_user_id();
			$map['is_del'] = array('eq', '0');

		}

		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}

		$model = D("DailyReport");
		if (!empty($model)) {
			$daily_report_common = $this -> _list($model, $map);
			$daily_report_extension = array();
			$model_comment = D("DailyReportComment");
			foreach ($daily_report_common as $k=>$v){
				$comment_last = $model_comment->where(array('doc_id'=>array('eq',$v['id']),'is_del'=>array('eq',0)))->order('create_time desc')->find();
				$daily_report_extension[$k]['comment_last'] = $comment_last['content'];
			}
			$this -> assign('daily_report_extension', $daily_report_extension);
			$res = $model->where($map)->order('work_date desc')->limit(28)->select();//手机端app提供数据
			if(is_mobile_request()){
				$daily_report = array();
				$model_daily_detail = M('DailyReportDetail');
				foreach ($res as $k=>$v){
					$daily_detail = $model_daily_detail->where(array('pid'=>array('eq',$v['id'])))->select();
					$daily_report[$k] = array();
					foreach ($daily_detail as $kk=>$vv){
						$daily_detail[$kk]['item'] = str_replace('|||','<br>',$vv['item']);
						$daily_detail[$kk]['work_date'] = $v['work_date'];
						$daily_report[$k][] = $daily_detail[$kk];
					}
					
				}
				$this -> assign('daily_report', $daily_report);
			}
		}		
		$this -> display();
	}

	public function add() {

// 		$data['is_submit'] = 0;
// 		$id = D("DailyReport") -> add($data);
// 		$this -> assign('id', $id);

		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);

		$date_1 = date('Y-m-d', strtotime('0 day'));
		$date_2 = date('Y-m-d', strtotime('-1 day'));
		$date_3 = date('Y-m-d', strtotime('-2 day'));
		$work_date_list = array($date_1 => $date_1, $date_2 => $date_2, $date_3 => $date_3);
		$this -> assign('work_date_list', $work_date_list);

		$where_last['user_id'] = array('eq', get_user_id());
		$where_last['is_submit'] = array('eq', 1);
		$last_report = M("DailyReport") -> where($where_last) -> order('id desc') -> find();
		$this -> assign('last_report', $last_report);

		$where_detail['pid'] = $last_report['id'];
		$where_detail['type'] = array('eq', 1);
		$last_report_detail = M("DailyReportDetail") -> where($where_detail) -> select();
		$this -> assign('last_report_detail', $last_report_detail);

		$time = array('00:00' => '00:00', '00:30' => '00:30', '01:00' => '01:00', '01:30' => '01:30', '02:00' => '02:00', '02:30' => '02:30', '03:00' => '03:00', '03:30' => '03:30', '04:00' => '04:00', '04:30' => '04:30', '05:00' => '05:00', '05:30' => '05:30', '06:00' => '06:00', '06:30' => '06:30', '07:00' => '07:00', '07:30' => '07:30', '08:00' => '08:00', '08:30' => '08:30', '09:00' => '09:00', '09:30' => '09:30', '10:00' => '10:00', '10:30' => '10:30', '11:00' => '11:00', '11:30' => '11:30', '12:00' => '12:00', '13:00' => '13:00', '13:30' => '13:30', '14:00' => '14:00', '14:30' => '14:30', '15:00' => '15:00', '15:30' => '15:30', '16:00' => '16:00', '16:30' => '16:30', '17:00' => '17:00', '17:30' => '17:30', '18:00' => '18:00', '18:30' => '18:30', '19:00' => '19:00', '19:30' => '19:30', '20:00' => '20:00', '20:30' => '20:30', '21:00' => '21:00', '21:30' => '21:30', '22:00' => '22:00', '22:30' => '22:30', '23:00' => '23:00', '23:30' => '23:30', '24:00' => '24:00');
		$this -> assign('time', $time);
		$this -> display();
	}

	public function read($id) {
		if(is_mobile_request()){
			$id = $_REQUEST['pid'];
		}
		$this -> assign('id', $id);
		$this -> assign('auth', $this -> config['auth']);

		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);

		$date_1 = date('Y-m-d', strtotime('0 day'));
		$date_2 = date('Y-m-d', strtotime('-1 day'));
		$date_3 = date('Y-m-d', strtotime('-2 day'));
		$work_date_list = array($date_1 => $date_1, $date_2 => $date_2, $date_3 => $date_3);
		$this -> assign('work_date_list', $work_date_list);

		$where_last['id'] = array('eq', $id);
		$last_report = M("DailyReport") -> where($where_last) -> order('id desc') -> find();
		$this -> assign('last_report', $last_report);

		$where_detail['pid'] = $last_report['id'];
		$where_detail['type'] = array('eq', 1);
		$last_report_detail = M("DailyReportDetail") -> where($where_detail) -> select();

		foreach ($last_report_detail as $key => $val) {
			$last_report_detail[$key]['item'] = explode('|||', $val['item']);
			$last_report_detail[$key]['start_time'] = explode('|||', $val['start_time']);
			$last_report_detail[$key]['end_time'] = explode('|||', $val['end_time']);
			$last_report_detail[$key]['status'] = explode('|||', $val['status']);
		}

		$this -> assign('last_report_detail', $last_report_detail);

		$where_plan['pid'] = $last_report['id'];
		$where_plan['type'] = array('eq', 2);
		$last_report_plan = M("DailyReportDetail") -> where($where_plan) -> select();
		$this -> assign('last_report_plan', $last_report_plan);

		$where_comment['doc_id'] = array('eq', $id);
		$where_comment['is_del'] = array('eq', 0);
		$comment = M("DailyReportComment") -> where($where_comment) -> select();
		$this -> assign('comment', $comment);
		
		$model_report_look = M('ReportLook');
		$report_look = $model_report_look->where(array('type'=>array('eq','daily'),'pid'=>array('eq',$id),'look_id'=>get_user_id()))->find();
		if($last_report['user_id']!=get_user_id()){
			if($report_look){
				$result = $model_report_look->where(array('id' => $report_look['id']))->save(array('create_time'=>time()));
			}else{
				$result = $model_report_look->add(array('type'=>'daily','pid'=>$id,'look_id'=>get_user_id(),'look_name'=>get_user_name(),'create_time'=>time()));
			}
		}
		$report_look = $model_report_look->where(array('type'=>array('eq','daily'),'pid'=>array('eq',$id)))->order('create_time desc')->select();
		$this -> assign('report_look', $report_look);
		
		$this -> display();
	}

	public function edit($id) {

		$this -> assign('id', $id);

		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);

		$date_1 = date('Y-m-d', strtotime('0 day'));
		$date_2 = date('Y-m-d', strtotime('-1 day'));
		$date_3 = date('Y-m-d', strtotime('-2 day'));
		$work_date_list = array($date_1 => $date_1, $date_2 => $date_2, $date_3 => $date_3);
		$this -> assign('work_date_list', $work_date_list);

		$where_last['id'] = array('eq', $id);
		$last_report = M("DailyReport") -> where($where_last) -> order('id desc') -> find();
		$this -> assign('last_report', $last_report);

		$where_detail['pid'] = $last_report['id'];
		$where_detail['type'] = array('eq', 1);
		$last_report_detail = M("DailyReportDetail") -> where($where_detail) -> select();
		$this -> assign('last_report_detail', $last_report_detail);

		$where_plan['pid'] = $last_report['id'];
		$where_plan['type'] = array('eq', 2);
		$last_report_plan = M("DailyReportDetail") -> where($where_plan) -> select();
		$this -> assign('last_report_plan', $last_report_plan);
		
		$time = array('00:00' => '00:00', '00:30' => '00:30', '01:00' => '01:00', '01:30' => '01:30', '02:00' => '02:00', '02:30' => '02:30', '03:00' => '03:00', '03:30' => '03:30', '04:00' => '04:00', '04:30' => '04:30', '05:00' => '05:00', '05:30' => '05:30', '06:00' => '06:00', '06:30' => '06:30', '07:00' => '07:00', '07:30' => '07:30', '08:00' => '08:00', '08:30' => '08:30', '09:00' => '09:00', '09:30' => '09:30', '10:00' => '10:00', '10:30' => '10:30', '11:00' => '11:00', '11:30' => '11:30', '12:00' => '12:00', '13:00' => '13:00', '13:30' => '13:30', '14:00' => '14:00', '14:30' => '14:30', '15:00' => '15:00', '15:30' => '15:30', '16:00' => '16:00', '16:30' => '16:30', '17:00' => '17:00', '17:30' => '17:30', '18:00' => '18:00', '18:30' => '18:30', '19:00' => '19:00', '19:30' => '19:30', '20:00' => '20:00', '20:30' => '20:30', '21:00' => '21:00', '21:30' => '21:30', '22:00' => '22:00', '22:30' => '22:30', '23:00' => '23:00', '23:30' => '23:30', '24:00' => '24:00');
		$this -> assign('time', $time);

		$this -> display();
	}

	function plan() {
		$user_id = get_user_id();
		$leader_id = get_leader_id($user_id);

		$where_last['user_id'] = array('eq', $leader_id);
		$where_last['is_submit'] = array('eq', 1);
		$last_report = M("DailyReport") -> where($where_last) -> order('id desc') -> find();
		$this -> assign('last_report', $last_report);

		$where_detail['pid'] = $last_report['id'];
		$where_detail['type'] = array('eq', 1);
		$last_report_detail = M("DailyReportDetail") -> where($where_detail) -> select();
		$this -> assign('last_report_detail', $last_report_detail);

		$where_plan['pid'] = $last_report['id'];
		$where_plan['type'] = array('eq', 2);
		$last_report_plan = M("DailyReportDetail") -> where($where_plan) -> select();
		$this -> assign('last_report_plan', $last_report_plan);

		$this -> display();
	}

	function upload() {
		$this -> _upload();
	}

	function down() {
		$this -> _down();
	}

	function del($id) {
		$this -> _del($id);
	}

	/** 插入新新数据  **/
	protected function _insert() {
		$model = D("DailyReport");
		if(!is_mobile_request()){
			if (false === $model -> create()) {
				$this -> error($model -> getError());
			}
		}else{
			$user = D('UserView')->find($_GET['id']);
			unset($_GET['id']);
			if (false === $model -> create($_GET)) {
				$this -> error($model -> getError());
			}
		}
		
		if (in_array('user_id', $model -> getDbFields())) {
			$model -> user_id = is_mobile_request()?$user['id']:get_user_id();
		};
		if (in_array('user_name', $model -> getDbFields())) {
			$model -> user_name = is_mobile_request()?$user['name']:get_user_name();
		};
		if (in_array('dept_id', $model -> getDbFields())) {
			$model -> dept_id = is_mobile_request()?$user['dept_id']:get_dept_id();
		};
		if (in_array('dept_name', $model -> getDbFields())) {
			$model -> dept_name = is_mobile_request()?$user['dept_name']:get_dept_name();
		};
		$model -> create_time = time();
		/*保存当前数据对象 */
		$list = $model -> add();
		
		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('新增成功!');
		} else {
			$this -> error('新增失败!');
			//失败提示
		}
	}

	/** 插入新新数据  **/
	protected function _update() {
		$model = D("DailyReport");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		if (in_array('user_id', $model -> getDbFields())) {
			$model -> user_id = get_user_id();
		};
		if (in_array('user_name', $model -> getDbFields())) {
			$model -> user_name = get_user_name();
		};
		if (in_array('dept_id', $model -> getDbFields())) {
			$model -> dept_id = get_dept_id();
		};
		if (in_array('dept_name', $model -> getDbFields())) {
			$model -> dept_name = get_dept_name();
		};
		$model -> create_time = time();
		/*保存当前数据对象 */
		$list = $model -> save();
		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('保存成功!'.$list);
		} else {
			$this -> error('保存失败!');
			//失败提示
		}
	}

	function add_comment() {
		$this -> display();
	}

	function edit_comment() {
		$widget['editor'] = true;
		$widget['uploader'] = true;
		$this -> assign("widget", $widget);

		$comment_id = $_REQUEST['comment_id'];
		$xid = M("DailyReportComment") -> where("id=$comment_id") -> getField("xid");
		$this -> _edit("DailyReportComment", $comment_id);
	}

	function reply_comment() {
		$this -> edit_comment();
	}

	function save_comment() {
		$model = D('DailyReportComment');
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$opmode = $_POST["opmode"];
		switch($opmode) {
			case "add" :
				$list = $model -> add();
				break;
			case "edit" :
				$list = $model -> save();
				break;
			case "del" :
				$this -> _del($name);
				break;
			default :
				$this -> error("非法操作");
		}

		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('操作成功!');
		} else {
			$this -> error('新增失败!');
			//失败提示
		}
	}

	function del_comment() {
		$comment_id = $_REQUEST['comment_id'];
		$this -> _del($comment_id, "DailyReportComment");
	}

}

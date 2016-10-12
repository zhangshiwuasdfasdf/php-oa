<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class WeeklyReportAction extends CommonAction {
	protected $config = array('app_type' => 'common', 'action_auth' => array('share' => 'read', 'plan' => 'read', 'del' => 'write', 'save_comment' => 'write', 'edit_comment' => 'write', 'reply_comment' => 'write', 'del_comment' => 'admin','export_weekly_report' => 'read','import_weekly_report' => 'read','get_dept_child' => 'read','get_real_dept'=>'read','get_username_by_dept'=>'read','json'=>'read','showreport'=>'read'));
	//过滤查询字段
	function _search_filter(&$map) {
	$map['is_del'] = array('eq', '0');
		if (!empty($_POST['eq_dept_id'])) {
			$map['dept_id'] = array('eq', $_POST['eq_dept_id']);
		}
		if (!empty($_REQUEST['li_user_name'])) {
			$map['user_name'] = array('like', '%'.$_REQUEST['li_user_name'].'%');
		}
		if (!empty($_POST['be_create_time']) && !empty($_POST['en_create_time'])) {
			$map['work_date'] = array('between', array($this->_get_week_by_date($_POST['be_create_time']),$this->_get_week_by_date($_POST['en_create_time'])));
		}elseif (!empty($_POST['be_create_time'])) {
			$map['work_date'] = array('egt', $this->_get_week_by_date($_POST['be_create_time']));
		}elseif (!empty($_POST['en_create_time'])) {
			$map['work_date'] = array('elt', $this->_get_week_by_date($_POST['en_create_time']));
		}
		if (!empty($_POST['dept_name_multi_data'])) {
			$dept_id_mul = $_POST['dept_name_multi_data'];
			$dept_id_mul = array_filter(explode('|',$dept_id_mul));
			$dept_ids = array();
			foreach ($dept_id_mul as $dept_id){
				$dept_ids = array_merge($dept_ids,get_child_dept_all($dept_id));
			}
			$map['pos_id'] = array('in', $dept_ids);
		}
		if (!empty($_POST['pos_name_multi_data'])) {
			$pos_id_mul = $_POST['pos_name_multi_data'];
			$pos_id_mul = array_filter(explode('|',$pos_id_mul));
			$pos_ids = array();
			foreach ($pos_id_mul as $pos_id){
				$pos_ids = array_merge($pos_ids,get_child_dept_all($pos_id));
			}
			$map['pos_id'] = array('in', $pos_ids);
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
			$dept_menu = $node -> field('id,pid,name') -> where("is_del=0 and is_real_dept=1") -> order('sort asc') -> select();
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
				$child_ids = array_merge(array(intval(get_user_id())),get_child_ids_all(get_user_id()));
				$map['user_id'] = array('in',$child_ids);
			}elseif(D("Role") -> check_duty('SHOW_LOG_LOW')){//允许查看自己及下一级日志
				$child_ids = array_merge(array(intval(get_user_id())),get_child_ids(get_user_id()));
				$map['user_id'] = array('in',$child_ids);
			}
			else{//查看自己的日志
				$map['user_id'] = array('eq',intval(get_user_id()));
			}
		}

		$node = D("Dept");
		$dept_menu = $node -> field('id,pid,name') -> where("is_del=0 and is_real_dept=1") -> order('sort asc') -> select();
		$dept_tree = list_to_tree($dept_menu);
		if(!is_mobile_request()){
			$this -> assign('dept_list_new', select_tree_menu_mul($dept_tree));
		}
		
		if ( D("Role") -> check_duty('SHOW_LOG')) {//查看所有日志
			$where = array();
			$map=array();
			$where['is_submit'] = array('eq', 1);
			$where['user_id'] = get_user_id();
			$where['_logic'] = 'or';

			$map['_complex'] = $where;
// 			$map['user_id'] = get_user_id();
			$map['is_del'] = array('eq', '0');

		}

		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}

		$model = D("WeeklyReportView");
		if (!empty($model)) {
			$weekly_report_common = $this -> _list($model, $map);
			$weekly_report_extension = array();
			$weekly_ids = array();
			$model_comment = D("WeeklyReportComment");
			$model_report_look = M('ReportLook');
			foreach ($weekly_report_common as $k=>$v){
				$comment_last = $model_comment->where(array('doc_id'=>array('eq',$v['id']),'is_del'=>array('eq',0)))->order('create_time desc')->find();
				$weekly_report_extension[$k]['comment_last'] = $comment_last['content'];
				$report_look[$k] = $model_report_look->where(array('type'=>array('eq','weekly'),'pid'=>array('eq',$v['id']),'look_id'=>array('neq',1)))->order('create_time desc')->limit(2)->select();
				$weekly_ids[$k] = strtotime(date('Y-m-d',strtotime('+1 day',$v['create_time'])));
			}
			$this -> assign('weekly_report_extension', $weekly_report_extension);
			$res = $model->where($map)->order('work_date desc')->limit(28)->select();//手机端app提供数据
			if(is_mobile_request()){
				$weekly_report = array();
				$model_weekly_detail = M('WeeklyReportDetail');
				foreach ($res as $k=>$v){
					$weekly_detail = $model_weekly_detail->where(array('pid'=>array('eq',$v['id'])))->select();
					$weekly_report[$k] = array();
					foreach ($weekly_detail as $kk=>$vv){
						$weekly_detail[$kk]['item'] = str_replace('|||','<br>',$vv['item']);
						$weekly_detail[$kk]['work_date'] = $v['work_date'];
						$weekly_detail[$kk]['author'] = $v['user_name'];
						$weekly_report[$k][] = $weekly_detail[$kk];
					}
						
				}
				$this -> assign('weekly_report', $weekly_report);
			}
		}
		$this -> assign('now_time',time());
		$this -> assign('end_time',$weekly_ids);
		$this -> assign('report',$report_look);
		$this -> display();
	}

	public function add() {

// 		$data['is_submit'] = 0;
// 		$id = D("WeeklyReport") -> add($data);
// 		$this -> assign('id', $id);

		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
		
		$date_now=date('j');
		$weekday_now=date('w',strtotime(date('Y-m-', time()).'01'));
		$cal_result=ceil(($date_now+$weekday_now)/7);
		$date_2 = date('Y-m-', time()).'第'. $cal_result . '周';
		$this -> assign('work_date_list', $date_2);

		$where_last['user_id'] = array('eq', get_user_id());
		$where_last['is_submit'] = array('eq', 1);
		$last_report = M("WeeklyReport") -> where($where_last) -> order('id desc') -> find();
		$this -> assign('last_report', $last_report);

		$where_detail['pid'] = $last_report['id'];
		$where_detail['type'] = array('eq', 1);
		$last_report_detail = M("WeeklyReportDetail") -> where($where_detail) -> select();
		$this -> assign('last_report_detail', $last_report_detail);
		
		
		$time = array();
		$begin=date('Y-m-01', strtotime(date("Y-m-d")));
		$begin=date('Y-m-d', strtotime("$begin -6 day"));
	    $end = date('Y-m-d', strtotime("$begin +1 month +6 day"));
	    $begin = strtotime($begin);$end = strtotime($end);
	    for ($i = $begin ;$i <= $end ; $i+=24*3600){
	    	$time[date("Y-m-d",$i)] = date("Y-m-d", $i);
	    }
		$this -> assign('time', $time);
		$this -> display();
	}

	public function read($id) {
		if(is_mobile_request()){
			$id = $_REQUEST['pid'];
		}
		$this -> assign('uid',get_user_id());
		$this -> assign('id', $id);
		$this -> assign('auth', $this -> config['auth']);

		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
		
		$auth = $this -> config['auth'];
		if (!$auth['admin']) {
			if(D("Role") -> check_duty('SHOW_LOG_LOW_ALL')){//允许查看自己及以下所有日志
				$child_ids = array_merge(array(intval(get_user_id())),get_child_ids_all(get_user_id()));
				$where_last['user_id'] = array('in',$child_ids);
			}elseif(D("Role") -> check_duty('SHOW_LOG_LOW')){//允许查看自己及下一级日志
				$child_ids = array_merge(array(intval(get_user_id())),get_child_ids(get_user_id()));
				$where_last['user_id'] = array('in',$child_ids);
			}else{//查看自己的日志
				$where_last['user_id'] = array('eq',intval(get_user_id()));
			}
		}

		$date_1 = date('Y-m-d', strtotime('0 day'));
		$date_2 = date('Y-m-d', strtotime('-1 day'));
		$date_3 = date('Y-m-d', strtotime('-2 day'));
		$work_date_list = array($date_1 => $date_1, $date_2 => $date_2, $date_3 => $date_3);
		$this -> assign('work_date_list', $work_date_list);

		$where_last['id'] = array('eq', $id);
		$last_report = M("WeeklyReport") -> where($where_last) -> order('id desc') -> find();
		$this -> assign('last_report', $last_report);
		
		if(empty($last_report)){
			$this->error('权限不足！');
		}
		
		$path = get_save_path()."excel_weekly/".$id .".txt";
		$str = file_get_contents($path);
		if($str){
			$this -> assign("excelCon",file_get_contents($path)); 
		}

		$where_detail['pid'] = $last_report['id'];
		$where_detail['type'] = array('eq', 1);
		$last_report_detail = M("WeeklyReportDetail") -> where($where_detail) -> select();

		foreach ($last_report_detail as $key => $val) {
			$last_report_detail[$key]['item'] = explode('|||', $val['item']);
			$last_report_detail[$key]['start_time'] = explode('|||', $val['start_time']);
			$last_report_detail[$key]['end_time'] = explode('|||', $val['end_time']);
			$last_report_detail[$key]['status'] = explode('|||', $val['status']);
		}

		$this -> assign('last_report_detail', $last_report_detail);

		$where_plan['pid'] = $last_report['id'];
		$where_plan['type'] = array('eq', 2);
		$last_report_plan = M("WeeklyReportDetail") -> where($where_plan) -> select();
		$this -> assign('last_report_plan', $last_report_plan);
		//dump($last_report_plan);

		$where_comment['doc_id'] = array('eq', $id);
		$where_comment['is_del'] = array('eq', 0);
		$comment = M("WeeklyReportComment") -> where($where_comment) -> select();
		$this -> assign('comment', $comment);
		
		$model_report_look = M('ReportLook');
		$report_look = $model_report_look->where(array('type'=>array('eq','weekly'),'pid'=>array('eq',$id),'look_id'=>get_user_id()))->find();
		if($last_report['user_id']!=get_user_id()){
			if($report_look){
				$result = $model_report_look->where(array('id' => $report_look['id']))->save(array('create_time'=>time()));
			}else{
				$result = $model_report_look->add(array('type'=>'weekly','pid'=>$id,'look_id'=>get_user_id(),'look_name'=>get_user_name(),'create_time'=>time()));
			}
		}
		$report_look = $model_report_look->where(array('type'=>array('eq','weekly'),'pid'=>array('eq',$id)))->order('create_time desc')->select();
		$this -> assign('report_look', $report_look);
		
		if($last_report['create_time']<strtotime('2016-08-23')){
			$this -> display('read_0');
		}else{
			$this -> display();
		}
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
		$last_report = M("WeeklyReport") -> where($where_last) -> order('id desc') -> find();
		$this -> assign('last_report', $last_report);
		
		$path = get_save_path()."excel_weekly/".$id .".txt";
		$str = file_get_contents($path);
		if($str){
			$this -> assign("excelCon",$str); 
		}
		
		$where_detail['pid'] = $last_report['id'];
		$where_detail['type'] = array('eq', 1);
		$last_report_detail = M("WeeklyReportDetail") -> where($where_detail) -> select();
		$this -> assign('last_report_detail', $last_report_detail);

		$where_plan['pid'] = $last_report['id'];
		$where_plan['type'] = array('eq', 2);
		$last_report_plan = M("WeeklyReportDetail") -> where($where_plan) -> select();
		$this -> assign('last_report_plan', $last_report_plan);
		$time = array();
		$begin=date('Y-m-01', strtotime(date("Y-m-d")));
		$begin=date('Y-m-d', strtotime("$begin -6 day"));
	    $end = date('Y-m-d', strtotime("$begin +1 month +6 day"));
	    $begin = strtotime($begin);$end = strtotime($end);
	    for ($i = $begin ;$i <= $end ; $i+=24*3600){
	    	$time[date("Y-m-d",$i)] = date("Y-m-d", $i);
	    }
	    
		$this -> assign('time', $time);

		if($last_report['create_time']<strtotime('2016-08-23')){
			$this -> display('edit_0');
		}else{
			$this -> display();
		}
	}

	function plan() {
		$user_id = get_user_id();
		$leader_id = get_leader_id($user_id);

		$where_last['user_id'] = array('eq', $leader_id);
		$where_last['is_submit'] = array('eq', 1);
		$last_report = M("WeeklyReport") -> where($where_last) -> order('id desc') -> find();
		$this -> assign('last_report', $last_report);

		$where_detail['pid'] = $last_report['id'];
		$where_detail['type'] = array('eq', 1);
		$last_report_detail = M("WeeklyReportDetail") -> where($where_detail) -> select();
		$this -> assign('last_report_detail', $last_report_detail);

		$where_plan['pid'] = $last_report['id'];
		$where_plan['type'] = array('eq', 2);
		$last_report_plan = M("WeeklyReportDetail") -> where($where_plan) -> select();
		$this -> assign('last_report_plan', $last_report_plan);

		$this -> display();
	}

	function upload() {
		$this -> _upload();
	}

	function down() {
		$this -> _down();
	}

	/** 插入新新数据  **/
	protected function _insert() {
		$model = D("WeeklyReport");
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
		$str = str_replace(array("\r\n", "\r", "\n"), "", $_POST['excel_html']);
		$str = preg_replace("/[\s]{2,}/","",$str);
		$str = str_replace("0.5pt","1pt",$str);
		
		
		/*保存当前数据对象 */
		
		$list = $model -> add();
		if ($list !== false) {//保存成功
			if(!empty($str)){
				$path = get_save_path()."excel_weekly/";
				if (!is_dir($path)){
				    mkdir($path,0777);
				}
				$path .= $list .".txt";
				file_put_contents($path,$str);
			}
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('新增成功!');
		} else {
			$this -> error('新增失败!');
			//失败提示
		}
	}

	/** 插入新新数据  **/
	protected function _update() {
		$model = D("WeeklyReport");
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
		
		$str = str_replace(array("\r\n", "\r", "\n"), "", $_POST['excel_html']);
		$str = preg_replace("/[\s]{2,}/","",$str);
		$str = str_replace("0.5pt","1pt",$str);
		$id = $_POST['id'];
		/*保存当前数据对象 */
		$list = $model -> save();
		if ($list !== false) {//保存成功
			if(!empty($str)){
				$path = get_save_path()."excel_weekly/";
				if (!is_dir($path)){
				    mkdir($path,0777);
				}
				$path .= $id .".txt";
				file_put_contents($path,$str);
			}
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('保存成功!');
		} else {
			$this -> error('保存失败!');
			//失败提示
		}
	}

	function add_comment() {
		$this -> display();
	}
	
	function del($id) {
		$path = get_save_path()."excel_weekly/";
		$path .= $id .".txt";
		@unlink ($path);
		$this -> _del($id);
	}

	function edit_comment() {
		$widget['editor'] = true;
		$widget['uploader'] = true;
		$this -> assign("widget", $widget);

		$comment_id = $_REQUEST['comment_id'];
		$xid = M("WeeklyReportComment") -> where("id=$comment_id") -> getField("xid");
		$this -> _edit("WeeklyReportComment", $comment_id);
	}

	function reply_comment() {
		$this -> edit_comment();
	}

	function save_comment() {
		$model = D('WeeklyReportComment');
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		if(is_mobile_request()){//手机端处理
			$model -> user_id = $model -> id;
			$model -> user_name = get_user_name();
			$model -> create_time = time();
			unset($model -> id);
			unset($model -> token);
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
		$this -> _del($comment_id, "WeeklyReportComment");
	}
	function export_weekly_report(){
		//导入thinkphp第三方类库
		Vendor('Excel.PHPExcel');
	
		$objPHPExcel = new PHPExcel();
	
		$objPHPExcel -> getProperties() -> setCreator("小微OA") -> setLastModifiedBy("小微OA") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
		// Add some data
		// 		$i = 1;
		//dump($list);
	
		//编号，类型，标题，登录时间，部门，登录人，状态，审批，协商，抄送，审批情况，自定义字段
		$q = $objPHPExcel -> setActiveSheetIndex(0);
		//第一列为用户
		$q = $q -> setCellValue("A1", '序号');
	
		$q = $q -> setCellValue("B1", '主要工作事项');
		$q = $q -> mergeCells('B1:C1');
		$q = $q -> setCellValue("D1", '工作内容');
		$q = $q -> mergeCells('D1:E1');
		$q = $q -> setCellValue("F1", '工作时间（起）（年月日）');
		$q = $q -> setCellValue("G1", '工作时间（止）（年月日）');
		$q = $q -> setCellValue("H1", '工作进度（进行中/已完成）');
	
		$q = $q -> setCellValue("A2", '1');
		$q = $q -> mergeCells('A2:A3');
		$q = $q -> mergeCells('B2:C3');
		$q = $q -> mergeCells('D2:E2');
		$q = $q -> mergeCells('D3:E3');
		$q = $q -> setCellValue("A4", '2');
		$q = $q -> mergeCells('A4:A5');
		$q = $q -> mergeCells('B4:C5');
		$q = $q -> mergeCells('D4:E4');
		$q = $q -> mergeCells('D5:E5');
		$q = $q -> setCellValue("A6", '3');
		$q = $q -> mergeCells('A6:A7');
		$q = $q -> mergeCells('B6:C7');
		$q = $q -> mergeCells('D6:E6');
		$q = $q -> mergeCells('D7:E7');
		$q = $q -> setCellValue("A8", '工作小结');
		$q = $q -> mergeCells('A8:A11');
		$q = $q -> mergeCells('B8:H11');
	
		$q = $q -> setCellValue("A14", '下周工作计划（A类最重要 B类重要 C类次重要）');
		$q = $q -> mergeCells('A14:I14');
	
		$q->getStyle('A14')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	
		$q = $q -> setCellValue("A15", '序号');
		$q = $q -> setCellValue("B15", '主要工作事项');
		$q = $q -> mergeCells('B15:C15');
		$q = $q -> setCellValue("D15", '计划推荐目标');
		$q = $q -> mergeCells('D15:E15');
		$q = $q -> setCellValue("F15", '时间安排（起）（年月日）');
		$q = $q -> setCellValue("G15", '时间安排（止）（年月日）');
		$q = $q -> setCellValue("H15", '重要性（A/B/C）');
		$q = $q -> setCellValue("I15", '协助需求（不需要协助/需要协助）');
	
		$q = $q -> setCellValue("A16", '1');
		$q = $q -> mergeCells('B16:C16');
		$q = $q -> mergeCells('D16:E16');
	
		$q = $q -> setCellValue("A17", '2');
		$q = $q -> mergeCells('B17:C17');
		$q = $q -> mergeCells('D17:E17');
	
		$q = $q -> setCellValue("A18", '3');
		$q = $q -> mergeCells('B18:C18');
		$q = $q -> mergeCells('D18:E18');
	
		$q = $q -> setCellValue("A19", '4');
		$q = $q -> mergeCells('B19:C19');
		$q = $q -> mergeCells('D19:E19');
	
		$q = $q -> setCellValue("A20", '5');
		$q = $q -> mergeCells('B20:C20');
		$q = $q -> mergeCells('D20:E20');
	
		$q = $q -> setCellValue("A21", '6');
		$q = $q -> mergeCells('B21:C21');
		$q = $q -> mergeCells('D21:E21');
	
		$q = $q -> setCellValue("A22", '7');
		$q = $q -> mergeCells('B22:C22');
		$q = $q -> mergeCells('D22:E22');
	
		$q = $q -> setCellValue("A23", '下周目标');
		$q = $q -> mergeCells('A23:A26');
		$q = $q -> mergeCells('B23:I26');
	
		$q ->getColumnDimension('A')->setWidth(20);
		$q ->getColumnDimension('B')->setWidth(20);
		$q ->getColumnDimension('C')->setWidth(20);
		$q ->getColumnDimension('D')->setWidth(20);
		$q ->getColumnDimension('E')->setWidth(20);
		$q ->getColumnDimension('F')->setWidth(20);
		$q ->getColumnDimension('G')->setWidth(20);
		$q ->getColumnDimension('H')->setWidth(30);
		$q ->getColumnDimension('I')->setWidth(40);
		$q ->getColumnDimension('J')->setWidth(20);
		// Rename worksheet
		$title = '周报';
		$objPHPExcel -> getActiveSheet() -> setTitle('周报');
	
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
	function import_weekly_report(){
		$save_path = get_save_path();
		$opmode = $_POST["opmode"];
		if ($opmode == "import") {
			import("@.ORG.Util.UploadFile");
			$upload = new UploadFile();
			$upload -> savePath = $save_path;
			$upload -> allowExts = array('xlsx');
			$upload -> saveRule = uniqid;
			$upload -> autoSub = false;
			if (!$upload -> upload()) {
				$this -> error($upload -> getErrorMsg());
			} else {
				//取得成功上传的文件信息
				$uploadList = $upload -> getUploadFileInfo();
				Vendor('Excel.PHPExcel');
				//导入thinkphp第三方类库
	
				$inputFileName = $save_path . $uploadList[0]["savename"];
				$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
				$sheetData = $objPHPExcel -> getActiveSheet() -> toArray(null, true, true, true);
	
				$start = ord('A');
	
				if($sheetData[1]['A']!='序号'){
					$this -> error('导入的excel模板不对:序号');
				}
				if($sheetData[1]['B']!='主要工作事项'){
					$this -> error('导入的excel模板不对:主要工作事项');
				}
				if($sheetData[1]['D']!='工作内容'){
					$this -> error('导入的excel模板不对:工作内容');
				}
				if($sheetData[1]['F']!='工作时间（起）（年月日）'){
					$this -> error('导入的excel模板不对:工作时间（起）（年月日）');
				}
				if($sheetData[1]['G']!='工作时间（止）（年月日）'){
					$this -> error('导入的excel模板不对:工作时间（止）（年月日）');
				}
				if($sheetData[1]['H']!='工作进度（进行中/已完成）'){
					$this -> error('导入的excel模板不对:工作进度（进行中/已完成）');
				}
	
				$model_weekly_report = M("WeeklyReport");
				$weekly_report = array();
	
				$ii=1;
				while ($sheetData[$ii*2]['A']==$ii){
					$ii++;
				}
				//$ii为今日序号+1
				$kk=1;
				while ($sheetData[$kk+$ii*2+7]['A']==$kk){
					$kk++;
				}
				$weekly_report['user_id'] = get_user_id();
				$weekly_report['user_name'] = get_user_name();
				$weekly_report['dept_id'] = get_dept_id();
				$weekly_report['dept_name'] = get_dept_name();
				$weekly_report['create_time'] = time();
				$weekly_report['content'] = $sheetData[$ii*2]['B'];
				$weekly_report['plan'] = $sheetData[$ii*2+$kk+7]['B'];
				$weekly_report['is_del'] = 0;
				$weekly_report['is_submit'] = 0;
				$date_now=date('j');
				$cal_result=ceil($date_now/7);
				$weekly_report['work_date'] = date('Y-m-', time()).'第'. $cal_result . '周';
	
				$pid = $model_weekly_report -> add($weekly_report);
	
				$model_weekly_report_detail = M("WeeklyReportDetail");
	
				$jj=$ii;
				for ($jj=1;$jj<$ii;$jj++){
					$data_detail = array();
					$data_detail['pid'] = $pid;
					$data_detail['type'] = 1;
					$data_detail['subject'] = $sheetData[$jj*2]['B'];
					$data_detail['item'] = $sheetData[$jj*2]['D'].'|||'.$sheetData[$jj*2+1]['D'];
					
					$start_time_1 = explode('-',$sheetData[$jj*2]['F']);
					$start_time_2 = explode('-',$sheetData[$jj*2+1]['F']);
					$data_detail['start_time'] = '20'.$start_time_1[2].'-'.$start_time_1[0].'-'.$start_time_1[1].'|||'.'20'.$start_time_2[2].'-'.$start_time_2[0].'-'.$start_time_2[1];
					$end_time_1 = explode('-',$sheetData[$jj*2]['G']);
					$end_time_2 = explode('-',$sheetData[$jj*2+1]['G']);
					$data_detail['end_time'] = '20'.$end_time_1[2].'-'.$end_time_1[0].'-'.$end_time_1[1].'|||'.'20'.$end_time_2[2].'-'.$end_time_2[0].'-'.$end_time_2[1];
					$status_1 = $sheetData[$jj*2]['H']=='进行中'?1:2;
					$status_2 = $sheetData[$jj*2+1]['H']=='进行中'?1:2;
					$data_detail['status'] = $status_1.'|||'.$status_2;
					$model_weekly_report_detail -> add($data_detail);
					
				}
	
				$mm=$kk;
				for ($ll=1;$ll<$mm;$ll++){
					$data_detail = array();
					$data_detail['pid'] = $pid;
					$data_detail['type'] = 2;
					$data_detail['subject'] = $sheetData[$ll+$ii*2+7]['B'];
					$data_detail['item'] = $sheetData[$ll+$ii*2+7]['D'];
					$start_time = explode('-',$sheetData[$ll+$ii*2+7]['F']);
					$data_detail['start_time'] = '20'.$start_time[2].'-'.$start_time[0].'-'.$start_time[1];
					$end_time = explode('-',$sheetData[$ll+$ii*2+7]['G']);
					$data_detail['end_time'] = '20'.$end_time[2].'-'.$end_time[0].'-'.$end_time[1];
					$data_detail['priority'] = $sheetData[$ll+$ii*2+7]['H'];
						
					$data_detail['is_need_help'] = $sheetData[$ll+$ii*2+7]['I']=='不需要协助'?0:1;
					$model_weekly_report_detail -> add($data_detail);
				}
	
	
				if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/" . $inputFileName)) {
					unlink($_SERVER["DOCUMENT_ROOT"] . "/" . $inputFileName);
				}
				$this -> assign('jumpUrl', U("weekly_report/edit",array('id'=>$pid)));
				$this -> success('导入成功！');
			}
		} else {
			$this -> display();
		}
	}
	function _get_week_by_date($date){
		$date_now=date('j',strtotime($date));
		$weekday_now=date('w',strtotime(date('Y-m-',strtotime($date)).'01'));
		$cal_result=ceil(($date_now+$weekday_now)/7);
		$date_2 = date('Y-m-', strtotime($date)).'第'. $cal_result . '周';
		return $date_2;
	}
	function json() {
		header("Cache-Control: no-cache, must-revalidate");
		header("Content-Type:text/html; charset=utf-8");
		$user_id = $_REQUEST["uid"];
		$start_date = $_REQUEST["start_date"];
		$end_date = $_REQUEST["end_date"];
	
		$where['user_id'] = $user_id;
		$where['is_del']=array('eq',0);
		$where['work_date'] = array( array('egt', $this->_get_week_by_date($start_date)), array('elt', $this->_get_week_by_date($end_date)));
		$list = M("WeeklyReport") -> where($where) -> order('work_date desc') -> select();
		foreach ($list as $k=>$v){
			$num = substr($v['work_date'],-4,-3);
			$month = substr($v['work_date'],0,7);
			$last_day = date('d',strtotime('+1 month -1 day',strtotime($month.'-01')));
			$should = 7-date('w',strtotime($month.'-01'))+($num-1)*7;
			$work_date_last = $should>$last_day?$last_day:$should;
			$work_date_last = $work_date_last<10?'0'.$work_date_last:$work_date_last;
			$list[$k]['work_date_last'] = $month.'-'.$work_date_last;
		}
		exit(json_encode($list));
	}
	function showreport(){
		$id = $_REQUEST["id"];
		$model=M("WeeklyReport");
		$data=$model->field('done,undoo,achievement,problem,content,plan,suggest')->where(array('id'=>$id))->select();
		$this->ajaxReturn($data,'success','1');
	}
}

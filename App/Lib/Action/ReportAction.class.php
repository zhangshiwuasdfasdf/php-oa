<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class ReportAction extends CommonAction {
	protected $config = array('app_type' => 'common', 'action_auth' => array('share' => 'read', 'plan' => 'read', 'save_comment' => 'write', 'edit_comment' => 'write', 'reply_comment' => 'write', 'del_comment' => 'admin','delivery_read'=>'read','delivery_read_all'=>'read','delivery_edit'=>'read','delivery_del'=>'read','delivery'=>'read','export_delivery_report' => 'read','import_delivery_report' => 'read','work_plan'=>'read','add_work_plan'=>'read','export_work_plan_report' => 'read','import_work_plan_report' => 'read','save_work_plan'=>'read','work_plan_del'=>'read','work_plan_read'=>'read','store_problem'=>'read','store_problem_read_all'=>'read','add_store_problem'=>'read','export_store_problem_report'=>'read','import_store_problem_report'=>'read','store_problem_read'=>'read','store_problem_del'=>'read'));
	//过滤查询字段
	function _search_filter(&$map) {
		if (!empty($_REQUEST['eq_addr'])) {
			$where_delivery['addr'] = array('eq',$_REQUEST['eq_addr']);
		}
		if (!empty($_REQUEST['li_user'])) {
			$where_delivery['user_name'] = array('like', '%'.$_REQUEST['li_user'].'%');
		}
		$start_time_0 = $_REQUEST['be_create_time_0'];
		$end_time_0 = $_REQUEST['en_create_time_0'];
		if (!empty($start_time_0)) {
			$where_delivery['create_time'][] = array('egt', strtotime(trim($start_time_0)));
		}
		if (!empty($end_time_0)) {
			$where_delivery['create_time'][] = array('elt', strtotime('+1 month',strtotime(trim($end_time_0).'-01')));
		}
		$start_time = $_REQUEST['be_create_time'];
		$end_time = $_REQUEST['en_create_time'];
		if (!empty($start_time)) {
			$where_delivery_detail['date'][] = array('egt', trim($start_time));
		}
		if (!empty($end_time)) {
			$where_delivery_detail['date'][] = array('elt', trim($end_time));
		}
		$map['_complex'] = array('delivery'=>$where_delivery,'delivery_detail'=>$where_delivery_detail);
	}
	
	public function delivery() {
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign('user_id', get_user_id());

		$auth = $this -> config['auth'];
		$this -> assign('auth', $auth);

		$addr = M("Delivery") -> field('addr as id,addr as name') ->distinct(true) -> select();
		$this -> assign('addr_list', $addr);
		
		$user_list = M("Delivery") -> field('user_name as id,user_name as name') ->distinct(true) -> select();
		$this -> assign('user_list', $user_list);

		$where = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($where);
		}
		$model = D("Delivery");
		if (!empty($model)) {
			$where['_complex']['delivery']['user_id'] = get_user_id();
			$vo = $this -> _list($model, $where['_complex']['delivery']);
			foreach ($vo as $k=>$v){
				$date = M('DeliveryDetail')->where(array('pid'=>$v['id']))->field('date')->distinct(true)->select();
				foreach ($date as $kk=>$vv){
					$vo[$k]['date'].=$vv['date'].',';
				}
			}
			$vo[0]['date'] = substr($vo[0]['date'],0,strlen($vo[0]['date'])-1);
		}	
		if(strlen($vo[0]['date'])>22){
			$vo[0]['date'] = substr($vo[0]['date'],0,22).'...';
		}
		$this -> assign('voo', $vo);
		$this -> display();
	}

	public function add() {
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
	public function add_work_plan() {
		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
		$file = M('File')->where(array('name'=>array('like','%工作计划导入模板%')))->find();
		
		$this -> assign("file_id", $file['id']);
	
		$dept_menu = D("Dept") -> field('id,pid,name') -> where("is_del=0 and is_real_dept=1") -> order('sort asc') -> select();
		$dept_tree = list_to_tree($dept_menu);
		$count = count($dept_tree);
		if(empty($count)){
			/*获取部门列表*/
			$html = '';
			$html = $html . "<option value='{$dept_id}'>{$dept_name}</option>";
			$this -> assign('addr_list', $html);
		}else{
			$this -> assign('addr_list', select_tree_menu($dept_tree));
		}
		$this -> display();
	}

	public function delivery_read($id) {

		$where['id'] = array('eq', $id);
		$delivery = M("Delivery") -> where($where) -> order('id desc') -> find();
		$this -> assign('delivery', $delivery);
		
		$where_detail['pid'] = $delivery['id'];
		$delivery_detail = M("DeliveryDetail") -> where($where_detail) -> select();
		
		$sum_day = array();
		$aa = array();
		$store_name_same = array();
		foreach ($delivery_detail as $k=>$v){
			$aa[$v['date']][$v['express']][$v['store_name']] = $v['num'];
			$store_name_same[$v['date']][$v['store_name']][$v['express']] = $v['num'];
			if(!strstr($v['store_name'], '小计')){//统计每天的总量时把含有小计的商家名过滤
				$sum_day[$v['date']] += $v['num'];
			}
		}
// 		dump($aa);
		$this -> assign('sum_day', $sum_day);
		$this -> assign('delivery_detail', $aa);
		$this -> assign('store_name_same', $store_name_same);
		
		$store_name = M("DeliveryDetail") -> where($where_detail) -> field('store_name') ->distinct(true) -> select();
		$store_name = rotate($store_name);
		$store_name = $store_name['store_name'];
		$this -> assign('store_name', $store_name);
		$this -> assign('store_name_num', count($store_name));
		
		$date = M("DeliveryDetail") -> where($where_detail) -> field('date') ->distinct(true) -> select();
		$date = rotate($date);
		$date = $date['date'];
		$this -> assign('date', $date);
		$this -> assign('date_num', count($date));
		
		$express = M("DeliveryDetail") -> where($where_detail) -> field('express') ->distinct(true) -> select();
		$express = rotate($express);
		$express = $express['express'];
		$this -> assign('express', $express);
		$this -> assign('express_num', count($express));
		
		$this -> display();
	}
	
	public function delivery_read_all() {
// 		$_REQUEST['export']
		ini_set("memory_limit","800M");
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign('auth', $this -> config['auth']);
		$this -> assign('user_id', get_user_id());
		
		$addr_list = M("Delivery") -> field('addr as id,addr as name') ->distinct(true) -> select();
		$this -> assign('addr_list', $addr_list);
		
		$where = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($where);
		}
		
		$role_user = D('Role')->get_role_list(get_user_id());
		foreach ($role_user as $k=>$v){
			$role = M('Role')->field('name')->find($v['role_id']);
			$role_name = $role['name'];
			$res = explode('基地发货日报表-',$role_name);
			if(count($res)>1){
				if($res[1] == '全国'){
					$addr['addr'] = array();
					$addr['addr'] = array('neq','');
					$addr['_string'] = '1=1';
					break;
				}elseif ($res[1] == '杭州'){
					$addr['_string'] .= 'or addr like "%杭州%" ';
				}elseif ($res[1] == '宁波'){
					$addr['_string'] .= 'or addr like "%宁波%" ';
				}elseif ($res[1] == '金华'){
					$addr['_string'] .= 'or addr like "%金华%" ';
				}
			}
		}
		if($addr['_string']){
			$addr['_string'] = substr($addr['_string'],2);
		}
		
		$where['_complex']['delivery']['_complex'] = $addr;
		$this -> assign('post', $_POST);
// 		$where['id'] = array('eq', $id);
		$delivery = M("Delivery") -> where($where['_complex']['delivery']) -> order('id desc') -> select();
		$this -> assign('delivery', $delivery);
// 		dump($where['_complex']['delivery']);
// 		dump($_REQUEST);
// 		dump(($delivery));
		
		$delivery_id = rotate($delivery);
		$delivery_id = $delivery_id['id'];
		$delivery_id = implode(',',$delivery_id);
		
		$where_detail = $where['_complex']['delivery_detail'];
		$where_detail['pid'] = array('in',$delivery_id);
// 		dump($where_detail);
		$delivery_detail = M("DeliveryDetail") -> where($where_detail) -> select();
		
// 		return;
		$sum_day = array();
		$aa = array();
		$store_name_same_day = array();
		$store_name_same = array();
		foreach ($delivery_detail as $k=>$v){
			$aa[$v['date']][$v['express']][$v['store_name']] = $v['num'];
			$store_name_same_day[$v['date']][$v['store_name']][$v['express']] = $v['num'];
			$store_name_same[$v['store_name']] += $v['num'];
			if(!strstr($v['store_name'], '小计')){//统计每天的总量时把含有小计的商家名过滤
				$sum_day[$v['date']] += $v['num'];
			}
		}
// 				dump($aa);
		$this -> assign('sum_day', $sum_day);
		$this -> assign('delivery_detail', $aa);
		$this -> assign('store_name_same_day', $store_name_same_day);
		$this -> assign('store_name_same', $store_name_same);
	
// 		dump($store_name_same);
// 		dump($where_detail);
		$store_name = M("DeliveryDetail") -> where($where_detail) -> field('store_name') ->distinct(true) -> select();
		$store_name = rotate($store_name);
		$store_name = $store_name['store_name'];
		$this -> assign('store_name', $store_name);
		$this -> assign('store_name_num', count($store_name));
	
		$date = M("DeliveryDetail") -> where($where_detail) -> field('date') ->distinct(true) -> select();
		$date = rotate($date);
		$date = $date['date'];
		arsort($date);
		
		$date = array_values($date);
		$dateall = $date;
		$date_num_o = count($date);
		
		if($_REQUEST['p']){
			$date = array($date[$_REQUEST['p']-1]);
		}else{
			$date = array($date[0]);
		}
		
		$this -> assign('date', $date);
		$this -> assign('date_num', count($date));
	
		$express = M("DeliveryDetail") -> where($where_detail) -> field('express') ->distinct(true) -> select();
		$express = rotate($express);
		$express = $express['express'];
		$this -> assign('express', $express);
		$this -> assign('express_num', count($express));
		
		$this -> assign('recent_date_1', date('Y-m').'-01');
		$this -> assign('recent_date', date('Y-m-d'));
		
		if($_REQUEST['export']=='1'){
			$this->_export_delivery($aa,$sum_day,$store_name_same_day,$store_name_same,$store_name,$express,$dateall);
		}
// 		$this->_list(M("DeliveryDetail"), $where_detail);
// 		echo $_REQUEST['p'];
		import("@.ORG.Util.Page2");
		//创建分页对象
		if (!empty($_REQUEST['list_rows'])) {
			$listRows = $_REQUEST['list_rows'];
		} else {
			$listRows = get_user_config('list_rows');
		}
		$p = new Page2($date_num_o*$listRows, $listRows);
		$p -> totalPages = $date_num_o;
		$p -> parameter = $this -> _search();
		//分页显示
		$page = $p -> show();
		$this -> assign("page", $page);
	
		$this -> display();
	}
	
	public function delivery_del($id) {
		$this -> assign('uid',get_user_id());
		$this -> assign('id', $id);
		$this -> assign('auth', $this -> config['auth']);
	
		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
	
		$where['id'] = array('eq', $id);
		$delivery_res = M("Delivery") -> where($where) -> delete();
	
		if($delivery_res){
			$where_detail['pid'] = $id;
			$delivery_detail_res = M("DeliveryDetail") -> where($where_detail) -> delete();
			if($delivery_detail_res){
				$this -> success('删除成功！');
			}else{
				$this -> error('删除失败！');
			}
		}else{
				$this -> error('删除失败！');
		}
	}
	public function work_plan_del($id) {
		$this -> assign('uid',get_user_id());
		$this -> assign('id', $id);
		$this -> assign('auth', $this -> config['auth']);
	
		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
	
		$where['id'] = array('eq', $id);
		$workplan_res = M("WorkPlan") -> where($where) -> delete();
	
		if($workplan_res){
			$this -> success('删除成功！');
		}else{
			$this -> error('删除失败！');
		}
		
	}
	public function store_problem_del($id) {
		$this -> assign('uid',get_user_id());
		$this -> assign('id', $id);
		$this -> assign('auth', $this -> config['auth']);
	
		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
	
		$where['id'] = array('eq', $id);
		$res = M("StoreProblem") -> where($where) -> delete();
	
		if($res){
			$where_detail['store_problem_id'] = $id;
			$detail_res = M("StoreProblemDetail") -> where($where_detail) -> delete();
			if($detail_res){
				$this -> success('删除成功！');
			}else{
				$this -> error('删除失败！');
			}
		}else{
			$this -> error('删除失败！');
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
	protected function _insert($name = null) {
		if (empty($name)) {
			$name = $this -> getActionName();
		}
		$model = D($name);
	
		if(is_mobile_request()){
			unset($_GET['id']);
			unset($_GET['token']);
			if (false === $model -> create($_GET)) {
				$this -> error($model -> getError());
			}
		}else{
			if (false === $model -> create()) {
				$this -> error($model -> getError());
			}
		}
		if($name=='WorkPlan'){
			$model->user_id = get_user_id();
			$model->user_name = get_user_name();
			$model->dept_id = get_dept_id();
			$model->dept_name = get_dept_name();
			$model->create_time = time();
			
			$addr_id = $model->addr_id;
			$dept = M('Dept')->field('name')->find($addr_id);
			$model->addr = $dept['name'];
		}
		
		/*保存当前数据对象 */
		$list = $model -> add();
		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('新增成功!'.$list);
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
	function export_delivery_report(){
		//导入thinkphp第三方类库
		Vendor('Excel.PHPExcel');
		
		$objPHPExcel = new PHPExcel();
		
		$objPHPExcel -> getProperties() -> setCreator("小微OA") -> setLastModifiedBy("小微OA") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
		// Add some data
// 		$i = 1;
		//dump($list);
		
		$q = $objPHPExcel -> setActiveSheetIndex(0);
		//第一列为用户
		$q = $q -> setCellValue("A1", '杭州基地6月仓库发货日报表');
		$q->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$q->getStyle('A1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
		$q = $q -> setCellValue("B1", 'FF');
		$q->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$q->getStyle('B1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
		
		$q = $q -> mergeCells('A1:Z1');
		
		$q = $q -> setCellValue("A3", '日期');
		$q = $q -> setCellValue("B3", '快递单位');
		
		for($i=ord('C');$i<=ord('Z');$i++){
			$q = $q -> setCellValue(chr($i)."2", '某某某商家');
			$q ->getStyle(chr($i)."2")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
			$q ->getStyle(chr($i)."2")->getAlignment()->setWrapText(true);
			$q ->getRowDimension(2)->setRowHeight(80);
			$q = $q -> setCellValue(chr($i)."3", $i-ord('C')+1);
			$q ->getStyle(chr($i)."3")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		}
		
		$q = $q -> setCellValue("A4", date('Y/m/d',time()));
		$q ->getStyle("A4")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		$q = $q -> setCellValue("B4", '韵达');
		$q ->getStyle("B4")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
		$q = $q -> setCellValue("A5", date('Y/m/d',time()));
		$q ->getStyle("A5")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		$q = $q -> setCellValue("B5", '中通');
		$q ->getStyle("B5")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
		$q = $q -> setCellValue("A6", date('Y/m/d',time()));
		$q ->getStyle("A6")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		$q = $q -> setCellValue("B6", '京东');
		$q ->getStyle("B6")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
		$q = $q -> setCellValue("A7", date('Y/m/d',time()));
		$q ->getStyle("A7")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		$q = $q -> setCellValue("B7", '邮政小包');
		$q ->getStyle("B7")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
		$q = $q -> setCellValue("A8", date('Y/m/d',time()));
		$q ->getStyle("A8")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		$q = $q -> setCellValue("B8", '汇通');
		$q ->getStyle("B8")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
		$q = $q -> setCellValue("A9", date('Y/m/d',time()));
		$q ->getStyle("A9")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		$q = $q -> setCellValue("B9", '申通');
		$q ->getStyle("B9")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
		$q = $q -> setCellValue("A10", date('Y/m/d',time()));
		$q ->getStyle("A10")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		$q = $q -> setCellValue("B10", '顺丰');
		$q ->getStyle("B10")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
		// Rename worksheet
		$title = '基地发货日报导入模板';
		$objPHPExcel -> getActiveSheet() -> setTitle('基地发货日报导入模板');
		
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
	function import_delivery_report(){
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
	
				$x=3;
				while($sheetData[2][ToNumberSystem26($x)]!=''){
					$x++;
				}
				$y=4;
				while($sheetData[$y]['A']!=''){
					$y++;
				}
				$title = $sheetData[1]['A'];
				$title1 = explode('基地',$title);
				if($title1[0]!='金华' && $title1[0]!='宁波' && $title1[0]!='杭州' && $title1[0]!='嘉兴'){
					$this -> error('园区必须是金华、宁波、杭州、嘉兴中的一个');
					exit ;
				}
				$isHeadquarters = isHeadquarters(get_user_id());
				if($isHeadquarters>0){//园区
					$res = M('Dept')->where(array('id'=>$isHeadquarters,'name'=>array('like','%'.$title1[0].'%')))->find();
					if(!$res){
						$this -> error('输入的园区与你所在园区不一致');
						exit;
					}
				}
				$title2 = explode('月',$title1[1]);
				if(!is_numeric($title2[0]) || $title2[0]<0 || $title2[0]>12){
					$this -> error('月份必须是1-12');
					exit ;
				}
// 				echo $sheetData[4]['A'];
// 				exit;
				
				$model_delivery = M("Delivery");
				$delivery = array();
				$delivery['user_id'] = get_user_id();
				$delivery['user_name'] = get_user_name();
				$delivery['dept_id'] = get_dept_id();
				$delivery['dept_name'] = get_dept_name();
				$delivery['create_time'] = time();
				$delivery['addr'] = $title1[0];
				$delivery['month'] = $title2[0];
				$pid = $model_delivery->add($delivery);
				if($pid){
					$model_delivery_detail = M("DeliveryDetail");
					for($i=3;$i<$x;$i++){
						for($j=4;$j<$y;$j++){
							if($sheetData[$j][ToNumberSystem26($i)]!=='' && $sheetData[$j]['A']!='小计' && $sheetData[$j]['B']!='小计' && $sheetData[$j]['A']!='总计' && $sheetData[$j]['B']!='总计' && $sheetData[2][ToNumberSystem26($i)]!='合计' && $sheetData[3][ToNumberSystem26($i)]!='合计'){
								$delivery_detail = array();
								$delivery_detail['pid'] = $pid;
								$delivery_detail['store_name'] = $sheetData[2][ToNumberSystem26($i)];
								$delivery_detail['express'] = $sheetData[$j]['B'];
								$date_0 = $sheetData[$j]['A'];

								$date_temp = explode('-',$date_0);
								if(count($date_temp)>1){
									$date_0 = '20'.$date_temp[2].'-'.$date_temp[0].'-'.$date_temp[1];
								}
								$delivery_detail['date'] = date('Y-m-d',strtotime($date_0));
// 								if($delivery_detail['date']=='1970-01-01'){
// 									$date_array = explode('-',$date_0);
// 									$delivery_detail['date'] = '20'.$date_array[2].'-'.$date_array[0].'-'.$date_array[1];
// 								}
								$delivery_detail['num'] = $sheetData[$j][ToNumberSystem26($i)];
								
								$where = array();
								$where['store_name'] = array('eq',$delivery_detail['store_name']);
								$where['express'] = array('eq',$delivery_detail['express']);
								$where['date'] = array('eq',$delivery_detail['date']);
								$is_exist = $model_delivery_detail->where($where)->find();
								if(empty($is_exist)){
									$res = $model_delivery_detail->add($delivery_detail);
									if(!$res){
										$this -> error('导入具体信息失败：'.ToNumberSystem26($i).' '.$j);
										exit ;
									}
								}
							}
						}
					}
				}else{
					$this -> error('导入发货报表失败');
					exit ;
				}
				
// 				if($sheetData[1]['A']!='序号'){
// 					$this -> error('导入的excel模板不对:序号');
// 				}
// 				if($sheetData[1]['B']!='主要工作事项'){
// 					$this -> error('导入的excel模板不对:主要工作事项');
// 				}
// 				if($sheetData[1]['D']!='工作内容'){
// 					$this -> error('导入的excel模板不对:工作内容');
// 				}
// 				if($sheetData[1]['F']!='工作时间（起）hh:mm半小时为单位'){
// 					$this -> error('导入的excel模板不对:工作时间（起）hh:mm半小时为单位');
// 				}
// 				if($sheetData[1]['G']!='工作时间（止）hh:mm半小时为单位'){
// 					$this -> error('导入的excel模板不对:工作时间（止）hh:mm半小时为单位');
// 				}
// 				if($sheetData[1]['H']!='工作进度（进行中/已完成）'){
// 					$this -> error('导入的excel模板不对:工作进度（进行中/已完成）');
// 				}
							
				if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/" . $inputFileName)) {
					unlink($_SERVER["DOCUMENT_ROOT"] . "/" . $inputFileName);
				}
// 				$this -> assign('jumpUrl', U("daily_report/edit",array('id'=>$pid)));
				$this -> success('导入成功！');
			}
		} else {
			$this -> display();
		}
	}
	public function work_plan() {
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign('user_id', get_user_id());
	
		$auth = $this -> config['auth'];
		$this -> assign('auth', $auth);
		
		$file = M('File')->where(array('name'=>array('like','%工作计划导入模板%')))->find();
		
		$this -> assign("file_id", $file['id']);
		
		$dept_list = M("WorkPlan") -> field('addr as id,addr as name') ->distinct(true) -> select();
		$this -> assign('dept_list', $dept_list);
	
		$user_list = M("WorkPlan") -> field('user_name as id,user_name as name') ->distinct(true) -> select();
		$this -> assign('user_list', $user_list);
	
		$where = $this -> _search();
		if (!empty($_POST['eq_dept'])) {
			$where['addr'] = array('eq',$_POST['eq_dept']);
		}
		if (!empty($_POST['eq_user'])) {
			$where['user_name'] = array('eq',$_POST['eq_user']);
		}
		$start_time_0 = $_POST['be_create_time_0'];
		$end_time_0 = $_POST['en_create_time_0'];
		if (!empty($start_time_0)) {
			$where['date'][] = array('egt', date('Y-m',strtotime(trim($start_time_0))));
		}
		if (!empty($end_time_0)) {
			$where['date'][] = array('elt', date('Y-m',strtotime(trim($end_time_0))));
		}
		$role_user = D('Role')->get_role_list(get_user_id());
		foreach ($role_user as $k=>$v){
			$role = M('Role')->field('name')->find($v['role_id']);
			$role_name = $role['name'];
			$res = explode('工作计划查看-',$role_name);
			if(count($res)>1){
				if($res[1] == '全国'){
					$addr['addr'] = array();
					$addr['addr'] = array('neq','');
					$addr['_string'] = '1=1';
					break;
				}elseif ($res[1] == '杭州'){
					$dept = M('Dept')->where(array('name'=>'杭州园区'))->find();
					$arr = get_child_dept_all($dept['id']);
					$arr[] = $dept['id'];
					$addr['addr_id'] = array('in',$arr);
				}elseif ($res[1] == '宁波'){
					$dept = M('Dept')->where(array('name'=>'宁波园区'))->find();
					$arr = get_child_dept_all($dept['id']);
					$arr[] = $dept['id'];
					$addr['addr_id'] = array('in',$arr);
				}elseif ($res[1] == '金华'){
					$dept = M('Dept')->where(array('name'=>'金华园区'))->find();
					$arr = get_child_dept_all($dept['id']);
					$arr[] = $dept['id'];
					$addr['addr_id'] = array('in',$arr);
				}
			}
		}
// 		if($addr['_string']){
// 			$addr['_string'] = substr($addr['_string'],2);
// 		}
		
		$where['_complex'] = $addr;
// 		dump($where);
		$model = D("WorkPlan");
		if (!empty($model)) {
			$this -> _list($model, $where);
		}
		$this -> display();
	}
	public function work_plan_read() {
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign('user_id', get_user_id());
	
		$auth = $this -> config['auth'];
		$this -> assign('auth', $auth);
	
		$model = D("WorkPlan");
		$id = $_GET['id'];
		$vo = $model->find($id);
		$this -> assign('vo', $vo);
		$this -> display();
	}
	function export_work_plan_report(){
		//导入thinkphp第三方类库
		Vendor('Excel.PHPExcel');
	
		$objPHPExcel = new PHPExcel();
	
		$objPHPExcel -> getProperties() -> setCreator("小微OA") -> setLastModifiedBy("小微OA") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
		// Add some data
	
		$q = $objPHPExcel -> setActiveSheetIndex(0);
		//
		$q = $q -> setCellValue("A1", '部门');
		$q->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
// 		$q->getStyle('A1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		$q = $q -> mergeCells('A1:A2');
		
		$q = $q -> setCellValue("B1", '工作性质');
		$q->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$q = $q -> mergeCells('B1:B2');
		
		$q = $q -> setCellValue("C1", '工作主题');
		$q->getStyle('C1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$q = $q -> mergeCells('C1:C2');
		
		$q = $q -> setCellValue("D1", '工作部门 （人数）');
		$q->getStyle('D1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$q = $q -> mergeCells('D1:D2');
		
		$q = $q -> setCellValue("E1", '任务目标');
		$q->getStyle('E1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$q = $q -> mergeCells('E1:E2');
		
		$q = $q -> setCellValue("F1", '协作部门');
		$q->getStyle('F1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$q = $q -> mergeCells('F1:F2');
		
		$q = $q -> setCellValue("G1", '周五');
		$q->getStyle('G1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$q->getStyle('G1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
		$q = $q -> setCellValue("G2", '1');
		$q->getStyle('G2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$q->getStyle('G2')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
		$q = $q -> setCellValue("H1", '周六');
		$q->getStyle('H1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$q->getStyle('H1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
		$q = $q -> setCellValue("H2", '2');
		$q->getStyle('H2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$q->getStyle('H2')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
		
		$q = $q -> setCellValue("A3", '金华基地');
		$q->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$q->getStyle('A3')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		$q = $q -> mergeCells('A3:A19');
		
		$q ->getColumnDimension('A')->setWidth(10);
		$q ->getColumnDimension('B')->setWidth(15);
		$q ->getColumnDimension('C')->setWidth(20);
		$q ->getColumnDimension('D')->setWidth(20);
		$q ->getColumnDimension('E')->setWidth(10);
		$q ->getColumnDimension('F')->setWidth(40);
		
		// Rename worksheet
		$title = '工作计划导入模板';
		$objPHPExcel -> getActiveSheet() -> setTitle('工作计划导入模板');
	
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
	function import_work_plan_report(){
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
	
				$x=7;
				while($sheetData[1][ToNumberSystem26($x)]!=''){
					$x++;
				}
				//$y=3;
					
				echo $x;
				
				$merges = $objPHPExcel -> getActiveSheet()->getMergeCells();
				foreach ($merges as $k=>$v){
					$t = explode('A3',$k);
					if(count($t)>1){
						$y = $t[1];
						$tt = explode('A',$y);
						$y = $tt[1];
					}
				}
				echo $y;
				exit;
				$dept_name = $sheetData[3]['A'];
				if(!$dept_name){
					$this -> error('请写部门');
					exit ;
				}
				
				$model_delivery = M("Delivery");
				$delivery = array();
				$delivery['user_id'] = get_user_id();
				$delivery['user_name'] = get_user_name();
				$delivery['dept_id'] = get_dept_id();
				$delivery['dept_name'] = get_dept_name();
				$delivery['create_time'] = time();
				$delivery['addr'] = $title1[0];
				$delivery['month'] = $title2[0];
				$pid = $model_delivery->add($delivery);
				if($pid){
					$model_delivery_detail = M("DeliveryDetail");
					for($i=3;$i<$x;$i++){
						for($j=4;$j<$y;$j++){
							if($sheetData[$j][ToNumberSystem26($i)]!=='' && $sheetData[$j]['A']!='小计' && $sheetData[$j]['B']!='小计' && $sheetData[$j]['A']!='总计' && $sheetData[$j]['B']!='总计' && $sheetData[2][ToNumberSystem26($i)]!='合计' && $sheetData[3][ToNumberSystem26($i)]!='合计'){
								$delivery_detail = array();
								$delivery_detail['pid'] = $pid;
								$delivery_detail['store_name'] = $sheetData[2][ToNumberSystem26($i)];
								$delivery_detail['express'] = $sheetData[$j]['B'];
								$date_0 = $sheetData[$j]['A'];
	
								$delivery_detail['date'] = date('Y-m-d',strtotime($date_0));
								if($delivery_detail['date']=='1970-01-01'){
									$date_array = explode('-',$date_0);
									$delivery_detail['date'] = '20'.$date_array[2].'-'.$date_array[0].'-'.$date_array[1];
								}
								$delivery_detail['num'] = $sheetData[$j][ToNumberSystem26($i)];
	
								$where = array();
								$where['store_name'] = array('eq',$delivery_detail['store_name']);
								$where['express'] = array('eq',$delivery_detail['express']);
								$where['date'] = array('eq',$delivery_detail['date']);
								$is_exist = $model_delivery_detail->where($where)->find();
								if(empty($is_exist)){
									$res = $model_delivery_detail->add($delivery_detail);
									if(!$res){
										$this -> error('导入具体信息失败：'.ToNumberSystem26($i).' '.$j);
										exit ;
									}
								}
							}
						}
					}
				}else{
					$this -> error('导入发货报表失败');
					exit ;
				}
					
				if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/" . $inputFileName)) {
					unlink($_SERVER["DOCUMENT_ROOT"] . "/" . $inputFileName);
				}
				// 				$this -> assign('jumpUrl', U("daily_report/edit",array('id'=>$pid)));
				$this -> success('导入成功！');
			}
		} else {
			$this -> display();
		}
	}
	function save_work_plan(){
		$add_file = $_POST['add_file'];
		$add_file = explode(';',$add_file);
		foreach ($add_file as $k=>$v){
			if(!empty($v)){
				$file = M('File')->field('extension')->where(array('sid'=>$v))->find();
				if($file['extension']!='xlsx' && $file['extension']!='xls'){
					$this->error('格式不符，必须是excel！');
				}
			}
		}
		$this->_save('WorkPlan');
	}
	
	public function store_problem() {
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign('user_id', get_user_id());
	
		$auth = $this -> config['auth'];
		$this -> assign('auth', $auth);
	
		$file = M('File')->where(array('name'=>array('like','%商家问题受理导入模板%')))->find();
		
		$this -> assign("file_id", $file['id']);
		
		$addr = M("StoreProblem") -> field('addr as id,addr as name') ->distinct(true) -> select();
		$this -> assign('addr_list', $addr);
	
		$user_list = M("StoreProblem") -> field('user_name as id,user_name as name') ->distinct(true) -> select();
		$this -> assign('user_list', $user_list);
	
		if (!empty($_POST['eq_addr'])) {
			$where['addr'] = array('eq',$_POST['eq_addr']);
		}
		if (!empty($_POST['li_user'])) {
			$where['user_name'] = array('like', '%'.$_POST['li_user'].'%');
		}
		$start_time_0 = $_POST['be_create_time_0'];
		$end_time_0 = $_POST['en_create_time_0'];
		if (!empty($start_time_0)) {
			$where['create_time'][] = array('egt', strtotime(trim($start_time_0)));
		}
		if (!empty($end_time_0)) {
			$where['create_time'][] = array('elt', strtotime(trim($end_time_0).' 24:00:00'));
		}
		
		$model = D("StoreProblem");
		if (!empty($model)) {
			$where['user_id'] = get_user_id();
			$this -> _list($model, $where);
		}
		$this -> display();
	}
	public function add_store_problem() {
		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
		$file = M('File')->where(array('name'=>array('like','%商家问题受理导入模板%')))->find();
	
		$this -> assign("file_id", $file['id']);
		$this -> display();
	}
// 	function export_store_problem_report(){
// 		//导入thinkphp第三方类库
// 		Vendor('Excel.PHPExcel');
	
// 		$objPHPExcel = new PHPExcel();
	
// 		$objPHPExcel -> getProperties() -> setCreator("小微OA") -> setLastModifiedBy("小微OA") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
// 		// Add some data
// 		// 		$i = 1;
// 		//dump($list);
	
// 		$q = $objPHPExcel -> setActiveSheetIndex(0);
// 		//第一列为用户
// 		$q = $q -> setCellValue("A1", '基本信息');
// 		$q->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 		$q = $q -> mergeCells('A1:I1');
	
// 		$q = $q -> setCellValue("J1", '事件信息');
// 		$q->getStyle('J1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 		$q = $q -> mergeCells('J1:L1');
		
// 		$q = $q -> setCellValue("M1", '处理信息');
// 		$q->getStyle('M1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 		$q = $q -> mergeCells('M1:O1');
		
// 		$q = $q -> setCellValue("P1", '赔付金额');
// 		$q->getStyle('P1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 		$q = $q -> mergeCells('P1:U1');
		
// 		$q = $q -> setCellValue("A2", '受理日期');
// 		$q->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	
// 		$q = $q -> setCellValue("B2", '受理人');
// 		$q->getStyle('B2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
// 		$q = $q -> setCellValue("C2", '店铺名称');
// 		$q->getStyle('C2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
// 		$q = $q -> setCellValue("D2", '系统订单号');
// 		$q->getStyle('D2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
// 		$q = $q -> setCellValue("E2", '淘宝订单号');
// 		$q->getStyle('E2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
// 		$q = $q -> setCellValue("F2", '收货人');
// 		$q->getStyle('F2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
// 		$q = $q -> setCellValue("G2", '买家id');
// 		$q->getStyle('G2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
// 		$q = $q -> setCellValue("H2", '快递公司');
// 		$q->getStyle('H2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
// 		$q = $q -> setCellValue("I2", '快递单号');
// 		$q->getStyle('I2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
// 		$q = $q -> setCellValue("J2", '问题大类');
// 		$q->getStyle('J2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
// 		$q = $q -> setCellValue("K2", '问题小类');
// 		$q->getStyle('K2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
// 		$q = $q -> setCellValue("L2", '事件详情');
// 		$q->getStyle('L2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
// 		$q = $q -> setCellValue("M2", '处理详情');
// 		$q->getStyle('M2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
// 		$q = $q -> setCellValue("N2", '处理进度');
// 		$q->getStyle('N2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
// 		$q = $q -> setCellValue("O2", '协调结果');
// 		$q->getStyle('O2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
// 		$q = $q -> setCellValue("P2", '货品成本价');
// 		$q->getStyle('P2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
// 		$q = $q -> setCellValue("Q2", '首发快递费');
// 		$q->getStyle('Q2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
// 		$q = $q -> setCellValue("R2", '重发快递费');
// 		$q->getStyle('R2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
// 		$q = $q -> setCellValue("S2", '退件快递费');
// 		$q->getStyle('S2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
// 		$q = $q -> setCellValue("T2", '其他');
// 		$q->getStyle('T2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
// 		$q = $q -> setCellValue("U2", '小计');
// 		$q->getStyle('U2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
// 		$q = $q -> setCellValue("A3", '2016/4/1  10:30:00');
// 		$q->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 		$q ->getStyle("A3")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
// 		$q = $q -> setCellValue("B3", '王晓冬');
// 		$q->getStyle('B3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 		$q ->getStyle("B3")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
// 		$q = $q -> setCellValue("C3", '安琪卫士');
// 		$q->getStyle('C3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 		$q ->getStyle("C3")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
// 		$q = $q -> setCellValue("D3", 'S1603030001381');
// 		$q->getStyle('D3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 		$q ->getStyle("D3")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
// 		$q = $q -> setCellValueExplicit("E3", '1677740519172636',PHPExcel_Cell_DataType::TYPE_STRING);
// 		$q->getStyle('E3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 		$q ->getStyle("E3")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
// 		$q = $q -> setCellValue("F3", '蒋荣艳');
// 		$q->getStyle('F3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 		$q ->getStyle("F3")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
// 		$q = $q -> setCellValue("G3", 'tb1125758_2012');
// 		$q->getStyle('G3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 		$q ->getStyle("G3")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
// 		$q = $q -> setCellValue("H3", '韵达');
// 		$q->getStyle('H3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 		$q ->getStyle("H3")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
// 		$q = $q -> setCellValueExplicit("I3", '1600762220968',PHPExcel_Cell_DataType::TYPE_STRING);
// 		$q->getStyle('I3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 		$q ->getStyle("I3")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
// 		$q = $q -> setCellValue("J3", '投诉类');
// 		$q->getStyle('J3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 		$q ->getStyle("J3")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
// 		$q = $q -> setCellValue("K3", '仓库错发');
// 		$q->getStyle('K3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 		$q ->getStyle("K3")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
// 		$q = $q -> setCellValue("L3", '3.3北京 北京市 丰台区 太平桥街道高楼5号院1号楼2110室(100073).');
// 		$q->getStyle('L3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 		$q ->getStyle("L3")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
// 		$q = $q -> setCellValue("N3", '已处理完毕');
// 		$q->getStyle('N3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 		$q ->getStyle("N3")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
// 		$q = $q -> setCellValue("O3", '公司赔付');
// 		$q->getStyle('O3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 		$q ->getStyle("O3")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
// 		$q = $q -> setCellValue("R3", '5.5');
// 		$q->getStyle('R3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 		$q ->getStyle("R3")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
// 		$q = $q -> setCellValue("T3", '1');
// 		$q->getStyle('T3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 		$q ->getStyle("T3")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
// 		$q = $q -> setCellValue("U3", '6.5');
// 		$q->getStyle('U3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// 		$q ->getStyle("U3")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
// 		for($i=ord('A');$i<=ord('U');$i++){
// 			if(chr($i)=='L' || chr($i)=='M'){
// 				$q ->getColumnDimension(chr($i))->setWidth(60);
// 			}else{
// 				$q ->getColumnDimension(chr($i))->setWidth(20);
// 			}
// 		}
		
		
// 		// Rename worksheet
// 		$title = '商家问题受理导入模板';
// 		$objPHPExcel -> getActiveSheet() -> setTitle('商家问题受理导入模板');
	
// 		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
// 		$objPHPExcel -> setActiveSheetIndex(0);
// 		$file_name = $title.".xlsx";
// 		// Redirect output to a client’s web browser (Excel2007)
// 		header("Content-Type: application/force-download");
// 		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
// 		header("Content-Disposition:attachment;filename =" . str_ireplace('+', '%20', URLEncode($file_name)));
// 		header('Cache-Control: max-age=0');
	
// 		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
// 		//readfile($filename);
// 		$objWriter -> save('php://output');
// 		exit ;
// 	}
	function import_store_problem_report(){
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

			
// 				echo 11;
// 				exit;
					
				$inputFileName = $save_path . $uploadList[0]["savename"];
				

// 				echo $inputFileName;
// 				exit;
					
				$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);

// 				echo 13;
// 				exit;
					
				$sheetData = $objPHPExcel -> getActiveSheet() -> toArray(null, true, true, true);
	
				//合理性校验
				if($sheetData[1]['A']!='基本信息' || $sheetData[1]['K']!='事件信息' || $sheetData[1]['N']!='处理信息' || $sheetData[1]['Q']!='赔付金额'){
					$this -> error('导入具体信息失败！,请按照模板导入！');
				}
				$row_2 = array(
					'A'=>'仓库地点',
					'B'=>'受理日期',
					'C'=>'受理人',
					'D'=>'店铺名称',
					'E'=>'系统订单号',
					'F'=>'淘宝订单号',
					'G'=>'收货人',
					'H'=>'买家id',
					'I'=>'快递公司',
					'J'=>'快递单号',
					'K'=>'问题大类',
					'L'=>'问题小类',
					'M'=>'事件详情',
					'N'=>'处理详情',
					'O'=>'处理进度',
					'P'=>'协调结果',
					'Q'=>'货品成本价',
					'R'=>'首发快递费',
					'S'=>'重发快递费',
					'T'=>'退件快递费',
					'U'=>'其他',
					'V'=>'小计',
				);
				foreach ($row_2 as $k=>$v){
					if($sheetData[2][$k]!=$v){
						$this -> error('导入具体信息失败！,请按照模板导入！');
					}
				}
				if($sheetData[3]['E']==''){
					$this -> error('请导入至少一条数据！');
				}
// 				echo 1;
// 				exit;
				
				$y=3;
				while($sheetData[$y]['E']!=''){
					$y++;
				}
				$model_store_problem = M("StoreProblem");
				$store_problem = array();
				$store_problem['user_id'] = get_user_id();
				$store_problem['user_name'] = get_user_name();
				$store_problem['dept_id'] = get_dept_id();
				$store_problem['dept_name'] = get_dept_name();
				$store_problem['create_time'] = time();
				$store_problem['addr'] = get_first_dept();
				$pid = $model_store_problem->add($store_problem);
// 				exit;
				if($pid){
					$model_store_problem_detail = M("StoreProblemDetail");
					for($j=3;$j<$y;$j++){
						$store_problem_detail = array();
						$store_problem_detail['store_problem_id'] = $pid;
						$store_problem_detail['warehouse_addr'] = $sheetData[$j]['A'];
						$store_problem_detail['accept_date'] = date('Y/m/d H:i:s',strtotime($sheetData[$j]['B']));
						$store_problem_detail['accept_person'] = $sheetData[$j]['C'];
						$store_problem_detail['store_name'] = $sheetData[$j]['D'];
						$store_problem_detail['system_id'] = $sheetData[$j]['E'];
						$store_problem_detail['taobao_id'] = $sheetData[$j]['F'];
						$store_problem_detail['consignee'] = $sheetData[$j]['G'];
						$store_problem_detail['buyer_id'] = $sheetData[$j]['H'];
						$store_problem_detail['delivery'] = $sheetData[$j]['I'];
						$store_problem_detail['delivery_id'] = $sheetData[$j]['J'];
						$store_problem_detail['problem_big'] = $sheetData[$j]['K'];
						$store_problem_detail['problem_small'] = $sheetData[$j]['L'];
						$store_problem_detail['event_detail'] = $sheetData[$j]['M'];
						$store_problem_detail['handle_detail'] = $sheetData[$j]['N'];
						$store_problem_detail['handle_schedule'] = $sheetData[$j]['O'];
						$store_problem_detail['coordination_result'] = $sheetData[$j]['P'];
						$store_problem_detail['goods_cost_price'] = $sheetData[$j]['Q'];
						$store_problem_detail['first_courier_fee'] = $sheetData[$j]['R'];
						$store_problem_detail['repeat_courier_fee'] = $sheetData[$j]['S'];
						$store_problem_detail['return_courier_fee'] = $sheetData[$j]['T'];
						$store_problem_detail['other'] = $sheetData[$j]['U'];
						$store_problem_detail['sum'] = $sheetData[$j]['V'];
// 						$date_0 = $sheetData[$j]['A'];

// 						$date_temp = explode('-',$date_0);
// 						if(count($date_temp)>1){
// 							$date_0 = '20'.$date_temp[2].'-'.$date_temp[0].'-'.$date_temp[1];
// 						}
// 						$delivery_detail['date'] = date('Y-m-d',strtotime($date_0));
					
// 						$delivery_detail['num'] = $sheetData[$j][ToNumberSystem26($i)];

						$where = array();
						$where['system_id'] = array('eq',$store_problem_detail['system_id']);
						$is_exist = $model_store_problem_detail->where($where)->find();
						if(empty($is_exist)){
							$res = $model_store_problem_detail->add($store_problem_detail);
// 							if(!$res){
// 								$this -> error('导入具体信息失败：'.ToNumberSystem26($i).' '.$j);
// 								exit ;
// 							}
						}else{
							$store_problem_detail['id'] = $is_exist['id'];
							$res = $model_store_problem_detail->save($store_problem_detail);
						}
					}
				}else{
					$this -> error('导入发货报表失败');
					exit ;
				}
	
				if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/" . $inputFileName)) {
					unlink($_SERVER["DOCUMENT_ROOT"] . "/" . $inputFileName);
				}
				// 				$this -> assign('jumpUrl', U("daily_report/edit",array('id'=>$pid)));
				$this -> success('导入成功！');
			}
		} else {
			$this -> display();
		}
	}
	public function store_problem_read($id) {
	
		$where['id'] = array('eq', $id);
		$store_problem = M("StoreProblem") -> where($where) -> order('id desc') -> find();
		$this -> assign('store_problem', $store_problem);
	
		$where_detail['store_problem_id'] = $store_problem['id'];
		$store_problem_detail = M("StoreProblemDetail") -> where($where_detail) -> select();
	
// 		$sum_day = array();
// 		$aa = array();
// 		$store_name_same = array();
// 		foreach ($delivery_detail as $k=>$v){
// 			$aa[$v['date']][$v['express']][$v['store_name']] = $v['num'];
// 			$store_name_same[$v['date']][$v['store_name']][$v['express']] = $v['num'];
// 			if(!strstr($v['store_name'], '小计')){//统计每天的总量时把含有小计的商家名过滤
// 				$sum_day[$v['date']] += $v['num'];
// 			}
// 		}
		$this -> assign('sum_item', count($store_problem_detail));
		$this -> assign('store_problem_detail', $store_problem_detail);
// 		$this -> assign('store_name_same', $store_name_same);
	
// 		$store_name = M("DeliveryDetail") -> where($where_detail) -> field('store_name') ->distinct(true) -> select();
// 		$store_name = rotate($store_name);
// 		$store_name = $store_name['store_name'];
// 		$this -> assign('store_name', $store_name);
// 		$this -> assign('store_name_num', count($store_name));
	
// 		$date = M("DeliveryDetail") -> where($where_detail) -> field('date') ->distinct(true) -> select();
// 		$date = rotate($date);
// 		$date = $date['date'];
// 		$this -> assign('date', $date);
// 		$this -> assign('date_num', count($date));
	
// 		$express = M("DeliveryDetail") -> where($where_detail) -> field('express') ->distinct(true) -> select();
// 		$express = rotate($express);
// 		$express = $express['express'];
		$express = array();
		for ($i=0;$i<30;$i++){
			$express[] = $i;
		}
		$this -> assign('express', $express);
// 		$this -> assign('express_num', count($express));
	
		$this -> display();
	}
	
	public function store_problem_read_all() {
		ini_set("memory_limit","800M");
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign('auth', $this -> config['auth']);
		$this -> assign('user_id', get_user_id());
	
		$addr_list = M("StoreProblem") -> field('addr as id,addr as name') ->distinct(true) -> select();
		$this -> assign('addr_list', $addr_list);
		
		$warehouse_addr_list = M("StoreProblemDetail") -> field('warehouse_addr as id,warehouse_addr as name') ->distinct(true) -> select();
		$this -> assign('warehouse_addr_list', $warehouse_addr_list);
		
		$handle_schedule_list = M("StoreProblemDetail") -> field('handle_schedule as id,handle_schedule as name') ->distinct(true) -> select();
		$this -> assign('handle_schedule_list', $handle_schedule_list);
	
		$where = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($where);
		}
	
		$role_user = D('Role')->get_role_list(get_user_id());
		foreach ($role_user as $k=>$v){
			$role = M('Role')->field('name')->find($v['role_id']);
			$role_name = $role['name'];
			$res = explode('商家问题受理表-',$role_name);
			if(count($res)>1){
				if($res[1] == '全国'){
					$addr['addr'] = array();
					$addr['addr'] = array('neq','');
					$addr['_string'] = '1=1';
					break;
				}elseif ($res[1] == '杭州'){
					$addr['_string'] .= 'or addr like "%杭州%" ';
				}elseif ($res[1] == '宁波'){
					$addr['_string'] .= 'or addr like "%宁波%" ';
				}elseif ($res[1] == '金华'){
					$addr['_string'] .= 'or addr like "%金华%" ';
				}
			}
		}
		if($addr['_string']){
			$addr['_string'] = substr($addr['_string'],2);
		}
	
		if (!empty($_REQUEST['eq_addr'])) {
			$where['addr'] = array('eq',$_REQUEST['eq_addr']);
		}
		if (!empty($_REQUEST['eq_warehouse_addr'])) {
			$where_detail['warehouse_addr'] = array('eq',$_REQUEST['eq_warehouse_addr']);
		}
		if (!empty($_REQUEST['eq_handle_schedule'])) {
			$where_detail['handle_schedule'] = array('eq',$_REQUEST['eq_handle_schedule']);
		}
		$start_time = $_REQUEST['be_accept_date'];
		$end_time = $_REQUEST['en_accept_date'];
		if (!empty($start_time)) {
			$where_detail['accept_date'][] = array('egt', date('Y/m/d H:i:s',strtotime(trim($start_time))));
		}
		if (!empty($end_time)) {
			$where_detail['accept_date'][] = array('elt', date('Y/m/d H:i:s',strtotime(trim($end_time).' 24:00:00')));
		}
		
		$where['_complex'] = $addr;
// 		dump($_POST);
		$this -> assign('post', $_POST);
		// 		$where['id'] = array('eq', $id);
		$store_problem = M("StoreProblem") -> where($where) -> order('id desc') -> select();
		$this -> assign('store_problem', $store_problem);
	
		$store_problem = rotate($store_problem);
		$store_problem_id = $store_problem['id'];
		$store_problem_id = implode(',',$store_problem_id);
	
// 		$where_detail = $where['_complex']['delivery_detail'];
		$where_detail['store_problem_id'] = array('in',$store_problem_id);
	
		if($_REQUEST['export']=='1'){
			$store_problem_detail = M("StoreProblemDetail") -> where($where_detail) ->order('accept_date desc')-> select();
			$this->_export_problem($store_problem_detail);
		}
		$res = $this->_list(M("StoreProblemDetail"), $where_detail,'accept_date');
		$this -> assign('sum_item', count($res));
// 		dump($where_detail);
		$aa = M("StoreProblemDetail")->field('id')->where($where_detail)->order('accept_date desc')->select();
// 		dump(rotate($aa)['id']);
		
// 		dump($bb);
		$this -> display();
	}
	function _export_delivery($aa,$sum_day,$store_name_same_day,$store_name_same,$store_name,$express,$dateall){
		//导入thinkphp第三方类库
		Vendor('Excel.PHPExcel');
		
		$objPHPExcel = new PHPExcel();
		
		$objPHPExcel -> getProperties() -> setCreator("小微OA") -> setLastModifiedBy("小微OA") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
		// Add some data
// 		$i = 1;
		//dump($list);
		
		$q = $objPHPExcel -> setActiveSheetIndex(0);
		//第一列为用户
		$q = $q -> setCellValue("A1", '基地仓库发货日报汇总表');
		$q->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
		$q = $q -> mergeCells('A1:'.ToNumberSystem26(count($store_name)+3).'1');
		
		$q = $q -> setCellValue("A3", '日期');
		$q = $q -> setCellValue("B3", '快递单位');
		
		//由于不能使用getCellValue，故暂时把数据存放在此，用于后面比较
		$store_name_array = array();
		for($i=ord('C');$i<ord('C')+count($store_name);$i++){
			$q = $q -> setCellValue(chrr($i)."2", $store_name[$i-ord('C')]);
			$q ->getStyle(chrr($i)."2")->getAlignment()->setWrapText(true);
			$q ->getRowDimension(2)->setRowHeight(80);
			$q = $q -> setCellValue(chrr($i)."3", $i-ord('C')+1);
			$store_name_array[chrr($i)] = $store_name[$i-ord('C')];
		}
		
		$q = $q -> setCellValue(ToNumberSystem26(count($store_name)+3)."2", "合计");
		$q ->getStyle(ToNumberSystem26(count($store_name)+3)."2")->getAlignment()->setWrapText(true);
		$q ->getRowDimension(2)->setRowHeight(80);
		$store_name_array[ToNumberSystem26(count($store_name)+3)] = "合计";
		
		
		$i=4;
		//由于不能使用getCellValue，故暂时把数据存放在此，用于后面比较
		$date_array = array();
		$express_array = array();
		foreach ($dateall as $v){
			foreach ($express as $vv){
				$q = $q -> setCellValue("A".$i, $v);
				$date_array[$i] = $v;
				$q = $q -> setCellValue("B".$i, $vv);
				$express_array[$i] = $vv;
				$i++;
			}
			$q = $q -> setCellValue("A".$i, $v);
			$date_array[$i] = $v;
			$q = $q -> setCellValue("B".$i, "小计");
			$express_array[$i] = "小计";
			$i++;
		}
		$q = $q -> setCellValue("A".$i, "总计");
		$date_array[$i] = "总计";
		$q = $q -> setCellValue("B".$i, "总计");
		$express_array[$i] = "总计";
		
		for ($ii=4;$ii<=$i;$ii++){
			for ($jj=ord('C');$jj<=ord('C')+count($store_name);$jj++){
				$date_t = $date_array[$ii];
				$express_t = $express_array[$ii];
				$store_name_t = $store_name_array[chrr($jj)];
				
				if($store_name_t=="合计"){
					if($express_t=="小计"){
						$q = $q -> setCellValue(chrr($jj).$ii, $sum_day[$date_t]);
					}elseif($express_t=="总计"){
						$q = $q -> setCellValue(chrr($jj).$ii, array_sum($sum_day));
					}else{
						$q = $q -> setCellValue(chrr($jj).$ii, array_sum($aa[$date_t][$express_t])?array_sum($aa[$date_t][$express_t]):0);
					}
				}else{
					if($express_t=="小计"){
						$q = $q -> setCellValue(chrr($jj).$ii, array_sum($store_name_same_day[$date_t][$store_name_t])?array_sum($store_name_same_day[$date_t][$store_name_t]):0);
					}elseif($express_t=="总计"){
						$q = $q -> setCellValue(chrr($jj).$ii, $store_name_same[$store_name_t]);
					}else{
						if($aa[$date_t][$express_t][$store_name_t]!==''){
							$q = $q -> setCellValue(chrr($jj).$ii, $aa[$date_t][$express_t][$store_name_t]);
						}
					}
				}
				
			}
		}
// 		exit;
		// Rename worksheet
		$title = '基地发货日报导出';
		$objPHPExcel -> getActiveSheet() -> setTitle('基地发货日报导出');
		
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
	function _export_problem($store_problem_detail){
		//导入thinkphp第三方类库
		Vendor('Excel.PHPExcel');
		
		$objPHPExcel = new PHPExcel();
		
		$objPHPExcel -> getProperties() -> setCreator("小微OA") -> setLastModifiedBy("小微OA") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
		// Add some data
		// 		$i = 1;
		//dump($list);
		
		$q = $objPHPExcel -> setActiveSheetIndex(0);
		//
		$q = $q -> setCellValue("A1", '基本信息');
		$q->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$q = $q -> mergeCells('A1:J1');
		$q = $q -> setCellValue("K1", '事件信息');
		$q->getStyle('K1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$q = $q -> mergeCells('K1:M1');
		$q = $q -> setCellValue("N1", '处理信息');
		$q->getStyle('N1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$q = $q -> mergeCells('N1:P1');
		$q = $q -> setCellValue("Q1", '赔付金额');
		$q->getStyle('Q1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$q = $q -> mergeCells('Q1:V1');
		
		$row_2 = array(
			'A'=>'仓库地点',
			'B'=>'受理日期',
			'C'=>'受理人',
			'D'=>'店铺名称',
			'E'=>'系统订单号',
			'F'=>'淘宝订单号',
			'G'=>'收货人',
			'H'=>'买家id',
			'I'=>'快递公司',
			'J'=>'快递单号',
			'K'=>'问题大类',
			'L'=>'问题小类',
			'M'=>'事件详情',
			'N'=>'处理详情',
			'O'=>'处理进度',
			'P'=>'协调结果',
			'Q'=>'货品成本价',
			'R'=>'首发快递费',
			'S'=>'重发快递费',
			'T'=>'退件快递费',
			'U'=>'其他',
			'V'=>'小计',
		);
		foreach ($row_2 as $k=>$v){
			$q = $q -> setCellValue($k."2", $v);
			$q->getStyle($k.'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		}
		
		foreach ($store_problem_detail as $k=>$v){
			$row = $k+3;
			unset($v['id']);
			unset($v['store_problem_id']);
			$col = ord('A');
			foreach ($v as $kk=>$vv){
				$q = $q -> setCellValueExplicit(chr($col).$row, $vv,PHPExcel_Cell_DataType::TYPE_STRING);
				$q->getStyle(chr($col).$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$col++;
			}
		}
		
		$col_width = array(
				'A'=>10,
				'B'=>20,
				'C'=>20,
				'D'=>20,
				'E'=>20,
				'F'=>20,
				'G'=>20,
				'H'=>20,
				'I'=>20,
				'J'=>20,
				'K'=>20,
				'L'=>20,
				'M'=>60,
				'N'=>60,
				'O'=>20,
				'P'=>20,
				'Q'=>20,
				'R'=>20,
				'S'=>20,
				'T'=>20,
				'U'=>20,
				'V'=>20,
		);
		foreach ($col_width as $k=>$v){
			$q ->getColumnDimension($k)->setWidth($v);
		}
		// Rename worksheet
		$title = '商家问题受理表导出';
		$objPHPExcel -> getActiveSheet() -> setTitle('商家问题受理表导出');
		
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

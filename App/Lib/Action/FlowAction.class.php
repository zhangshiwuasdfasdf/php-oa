<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class FlowAction extends CommonAction {
	protected $config = array('app_type' => 'flow', 'action_auth' => array('folder' => 'read', 'mark' => 'admin', 'report' => 'admin','ajaxgetflow' =>'admin','ajaxgettime' =>'admin','editflow' =>'admin'));

	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['keyword'])) {
			$keyword = $_POST['keyword'];
			$map['name'] = array('like', "%" . $keyword . "%");
		}
	}

	function index() {
		$model = D("Flow");
		$model = D('FlowTypeView');
		$where['is_del'] = 0;
		$user_id = get_user_id();
		$role_list = D("Role") -> get_role_list($user_id);
		$role_list = rotate($role_list);
		$role_list = $role_list['role_id'];

		$duty_list = D("Role") -> get_duty_list($role_list);
		$duty_list = rotate($duty_list);
		$duty_list = $duty_list['duty_id'];

		$where['request_duty'] = array('in', $duty_list);

		$list = $model -> where($where) -> order('sort') -> select();
		$this -> assign("list", $list);
		$this -> _assign_tag_list();
		$this -> display();
	}

	function _flow_auth_filter($folder, &$map) {
		$emp_no = get_emp_no();
		$user_id = get_user_id();
		switch ($folder) {
			case 'confirm' :
				$this -> assign("folder_name", '待办');
				$FlowLog = M("FlowLog");
				$where['emp_no'] = $emp_no;
				$where['_string'] = "result is null";
				$log_list = $FlowLog -> where($where) -> field('flow_id') -> select();

				$log_list = rotate($log_list);
				if (!empty($log_list)) {
					$map['id'] = array('in', $log_list['flow_id']);
				} else {
					$map['_string'] = '1=2';
				}
				break;

			case 'darft' :
				$this -> assign("folder_name", '草稿');
				$map['user_id'] = $user_id;
				$map['step'] = 10;
				break;

			case 'submit' :
				$this -> assign("folder_name", '提交');
				$map['user_id'] = array('eq', $user_id);
				$map['step'] = array( array('gt', 10), array('eq', 0), 'or');

				break;

			case 'finish' :
				$this -> assign("folder_name", '办理');
				$FlowLog = M("FlowLog");
				$where['emp_no'] = $emp_no;
				$where['_string'] = "result is not null";
				$log_list = $FlowLog -> where($where) -> field('flow_id') -> select();
				$log_list = rotate($log_list);
				if (!empty($log_list)) {
					$map['id'] = array('in', $log_list['flow_id']);
				} else {
					$map['_string'] = '1=2';
				}
				break;

			case 'receive' :
				$this -> assign("folder_name", '收到');
				$FlowLog = M("FlowLog");
				$where['emp_no'] = $emp_no;
				$where['step'] = 100;
				$log_list = $FlowLog -> where($where) -> field('flow_id') -> select();
				$log_list = rotate($log_list);
				if (!empty($log_list)) {
					$map['id'] = array('in', $log_list['flow_id']);
				} else {
					$map['_string'] = '1=2';
				}
				break;
			case 'report' :
				$this -> assign("folder_name", '统计报告');
				$role_list = D("Role") -> get_role_list($user_id);
				$role_list = rotate($role_list);
				$role_list = $role_list['role_id'];

				$duty_list = D("Role") -> get_duty_list($role_list);
				$duty_list = rotate($duty_list);
				$duty_list = $duty_list['duty_id'];

				if (!empty($duty_list)) {
					$map['report_duty'] = array('in', $duty_list);
					$map['step'] = array('gt', 10);
				} else {
					$this -> error("没有权限");
				}
				break;
			case 'hr':
				$type = $_GET['type'];
				$name = $_GET['name'];
				$menu = D("Node") -> access_list();
				if(!empty($_GET['id'])){
					$allow = false;
					foreach ($menu as $v){
						$pu = parse_url($v['url']);
						$arr_query = convertUrlQuery($pu['query']);
						if($arr_query['type']=='common'){
							$res = M('FlowCommon')->where(array('flow_id'=>array('eq',$_GET['id'])))->select();
							if(!empty($res)){
								$allow = true;
								break;
							}
						}
						elseif(!empty($arr_query['name'])){
							$res = M('Flow'.convertUnderline1($arr_query['name']))->where(array('flow_id'=>array('eq',$_GET['id'])))->select();
							if(!empty($res)){
								$allow = true;
								break;
							}
						}
					}
					if(!$allow){
						$this -> error("没有权限");
					}
				}
				elseif(!empty($type)){
					$allow = false;
					if($type=='common'){
						foreach ($menu as $v){
							$pu = parse_url($v['url']);
							$arr_query = convertUrlQuery($pu['query']);
							if($arr_query['type']=='common'){
								$flow_id = M('FlowCommon')->field('flow_id')->select();
								$rt = rotate($flow_id);
								$map['id'] = array('in',$rt['flow_id']);
								$allow = true;
								break;
							}
						}
						if(!$allow){
							$this -> error("没有权限");
						}
					}else{
						foreach ($menu as $v){
							$pu = parse_url($v['url']);
							$arr_query = convertUrlQuery($pu['query']);
							if($arr_query['name']==$name){
								$flow_id = M('Flow'.convertUnderline1($name))->field('flow_id')->select();
								$rt = rotate($flow_id);
								$map['id'] = array('in',$rt['flow_id']);
								$allow = true;
								break;
							}
						}
						if(!$allow){
							$this -> error("没有权限");
						}
					}
				}
				break;
		}
	}

	function folder() {

		$widget['date'] = true;
		$this -> assign("widget", $widget);

		$emp_no = get_emp_no();
		$user_id = get_user_id();

		$flow_type_where['is_del'] = array('eq', 0);

		$flow_type_list = M("FlowType") -> where($flow_type_where) -> getField("id,name");
		$this -> assign("flow_type_list", $flow_type_list);

		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}

		$folder = $_REQUEST['fid'];

		$this -> assign("folder", $folder);

		if (empty($folder)) {
			$this -> error("系统错误");
		}

		$this -> _flow_auth_filter($folder, $map);
		$model = D("FlowView");

		if ($_REQUEST['mode'] == 'export') {
			$this -> _folder_export($model, $map);
		} else {
			$flow_list = $this -> _list($model, $map);
		}
		foreach ($flow_list as $k=>$v){
			$auth = M('FlowLog')->where(array('flow_id'=>array('eq',$v['id']),'_string'=>'result is not null'))->select();
			if($auth){
				$flow_list[$k]['auth'] = 1;
			}else{
				$flow_list[$k]['auth'] = 0;
			}
		}
		$this -> assign("list", $flow_list);
		$this -> display();
	}

	private function _folder_export($model, $map) {
		$list = $model -> where($map) -> select();

		//导入thinkphp第三方类库
		Vendor('Excel.PHPExcel');

		//$inputFileName = "Public/templete/contact.xlsx";
		//$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
		$objPHPExcel = new PHPExcel();

		$objPHPExcel -> getProperties() -> setCreator("小微OA") -> setLastModifiedBy("小微OA") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
		// Add some data
		$i = 1;
		//dump($list);

		//编号，类型，标题，登录时间，部门，登录人，状态，审批，协商，抄送，审批情况，自定义字段
		$objPHPExcel -> setActiveSheetIndex(0) -> setCellValue("A$i", "编号") -> setCellValue("B$i", "类型") -> setCellValue("C$i", "标题") -> setCellValue("D$i", "登录时间") -> setCellValue("E$i", "部门") -> setCellValue("F$i", "登录人") -> setCellValue("G$i", "状态") -> setCellValue("H$i", "审批") -> setCellValue("I$i", "协商") -> setCellValue("J$i", "抄送") -> setCellValue("J$i", "审批情况");
		foreach ($list as $val) {
			$i++;
			//dump($val);
			$id = $val['id'];
			$doc_no = $val["doc_no"];
			//编号
			$name = $val["name"];
			//标题
			$confirm_name = strip_tags($val["confirm_name"]);
			//审批
			$consult_name = strip_tags($val["consult_name"]);
			//协商
			$refer_name = strip_tags($val["refer_name"]);
			//协商
			$type_name = $val["type_name"];
			//流程类型
			$user_name = $val["user_name"];
			//登记人
			$dept_name = $val["dept_name"];
			//不美分
			$create_time = $val["create_time"];
			$create_time = toDate($val["create_time"], 'Y-m-d H:i:s');
			//创建时间
			$step = show_step_type($val["step"]);
			//

			//编号，类型，标题，登录时间，部门，登录人，状态，审批，协商，抄送，审批情况，自定义字段
			$objPHPExcel -> setActiveSheetIndex(0) -> setCellValue("A$i", $doc_no) -> setCellValue("B$i", $type_name) -> setCellValue("C$i", $name) -> setCellValue("D$i", $create_time) -> setCellValue("E$i", $dept_name) -> setCellValue("F$i", $user_name) -> setCellValue("G$i", $step) -> setCellValue("H$i", $confirm_name) -> setCellValue("I$i", $consult_name);

			$model_flow_field = D("FlowField");
			$field_list = $model_flow_field -> get_data_list($id);
			//	dump($field_list);
			$k = 0;
			if (!empty($field_list)) {
				foreach ($field_list as $field) {
					$k++;
					$field_data = $field['name'] . ":" . $field['val'];
					$location = get_cell_location("J", $i, $k);
					$objPHPExcel -> setActiveSheetIndex(0) -> setCellValue($location, $field_data);
				}
			}
		}
		// Rename worksheet
		$objPHPExcel -> getActiveSheet() -> setTitle('流程统计');

		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel -> setActiveSheetIndex(0);
		$file_name = "流程统计.xlsx";
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

	function add() {
		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);

		$type_id = $_REQUEST['type'];
		$model = M("FlowType");
		$flow_type = $model -> find($type_id);
		$this -> assign("flow_type", $flow_type);
		$model_flow_field = D("FlowField");
		$field_list = $model_flow_field -> get_field_list($type_id);
		$this -> assign("field_list", $field_list);
		
		$uid = get_user_id();
		if($uid){
			$info = array();
			$user_info = get_user_info($uid,'name,dept_name,dept_id,office_tel,mobile_tel,duty,email');
			foreach ($user_info as $v){
				$info = $v;
			}
			$info['user_id'] = $uid;
		}
		$this -> assign("user_info", $info);
		$this -> assign("time", time());
		$this -> display();
	}
	public function ajaxgetflow(){
		$type = $_GET['type'];
		switch($type){
			case 'leave' : $this->ajaxgetflow_leave();
			case 'attendance' : $this->ajaxgetflow_attendance();
			case 'over_time' : $this->ajaxgetflow_over_time();
			case 'employment' : $this->ajaxgetflow_employment();
			case 'internal' : $this->ajaxgetflow_internal();
			case 'metting_communicate' : $this->ajaxgetflow_metting_communicate();
			case 'card_application' :$this->ajaxgetflow_card_application();
			case 'notice_file' :$this->ajaxgetflow_notice_file();
			case 'notice_personnel' :$this->ajaxgetflow_notice_personnel();
			case 'contract' :$this->ajaxgetflow_contract();
			case 'resignation_application' : $this->ajaxgetflow_resignation();
			case 'probation_evaluate' : $this->ajaxgetflow_probation();
			case 'regular_work_application' : $this->ajaxgetflow_regular_work_application();
			case 'personnel_changes' :$this->ajaxgetflow_personnel_changes();
			case 'salary_changes' :$this->ajaxgetflow_salary_changes();
			case 'resignation_list' :$this->ajaxgetflow_resignation_list();
			case 'office_supplies_application' :$this->ajaxgetflow_office_supplies_application();
			case 'office_use_application' :$this->ajaxgetflow_office_use_application();
			case 'goods_procurement_allocation' :$this->ajaxgetflow_goods_procurement_allocation();
			case 'bus_card_use' :$this->ajaxgetflow_bus_card_use();
			case 'chops_use' :$this->ajaxgetflow_chops_use();
			case 'car_use' :$this->ajaxgetflow_car_use();
			default :return false;
		}
	}
	public function ajaxgetflow_leave(){//外勤/出差单,请假/调休单
		$uid = $_POST['uid'];
		$day = $_POST['day'];
		if(empty($uid) || ($day<0)){
			return false;
		}
		$flow = getFlow($uid,$day);
		if(!empty($flow)){
			$this->ajaxReturn(getFlowData($flow),null,1);
		}else{
			$this->ajaxReturn(null,null,0);
		}
		
	}
	public function ajaxgetflow_attendance(){//补勤单
		$uid = $_POST['uid'];
		if(empty($uid)){
			return false;
		}
		$flow = getParentid($uid);
		if(!empty($flow)){
			$this->ajaxReturn(getFlowData($flow),null,1);
		}else{
			$this->ajaxReturn(null,null,0);
		}
	}
	public function ajaxgetflow_over_time(){
		$uid = $_POST['uid'];
		$dept_id = $_POST['dept_id'];
		if(empty($uid)){
			return false;
		}
		$dept_uid = getDeptManagerId($uid,$dept_id);
		$flow = array($dept_uid,getHRDeputyGeneralManagerId($uid));
		$this->ajaxReturn(getFlowData(array_unique($flow)),null,1);
	}
	public function ajaxgetflow_employment(){//用工申请表
		$uid = $_POST['uid'];
		$dept_id = $_POST['dept_id'];
		$add = $_GET['add'];
		if(empty($uid)){
			return false;
		}
		$dept_idd = getDeptManagerId($uid,$dept_id);
		if($add=='1'){//辞职补充
			$flow = array($dept_idd,getHRDeputyGeneralManagerId($uid));
		}else{
			$flow = array($dept_idd,getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid));
		}
		
		if(!empty($flow)){
			$this->ajaxReturn(getFlowData($flow),null,1);
		}else{
			$this->ajaxReturn(null,null,0);
		}
	
	}
	function ajaxgetflow_internal(){
		$uid = $_POST['uid'];
		$dept_id_from = $_POST['dept_id_from'];
		$dept_id_to = $_POST['dept_id_to'];
		$model = D('UserView');
		$user_from = $model->where(array('pos_id'=>array('eq',$dept_id_from)))->order('position_sort')->find();
		$user_to = $model->where(array('pos_id'=>array('eq',$dept_id_to)))->order('position_sort')->find();
		if(!empty($user_from) && !empty($user_to)){
			$flow = array($user_from['id'],$user_to['id'],getHRDeputyGeneralManagerId($user_from['id']),getGeneralManagerId($uid));
			$this->ajaxReturn(getFlowData(array_unique($flow)),null,1);
		}else{
			$this->ajaxReturn(null,null,1);
		}
		
	}
	function ajaxgetflow_metting_communicate(){
		$uid = $_POST['uid'];
		$flow = array(getHRDeputyGeneralManagerId($uid));
		$this->ajaxReturn(getFlowData($flow),null,1);
	}
	function ajaxgetflow_card_application(){
		$uid = $_POST['uid'];
		$dept_id = $_POST['dept_id'];
		$dept_uid = getDeptManagerId($uid,$dept_id);
		$flow = array($dept_uid);
		$this->ajaxReturn(getFlowData($flow),null,1);
	}
	function ajaxgetflow_notice_file(){
		$uid = $_POST['uid'];
		$dept_id = $_POST['dept_id'];
		$dept_uid = getDeptManagerId($uid,$dept_id);
		$flow = array($dept_uid,getHRDeputyGeneralManagerId($uid));
		$this->ajaxReturn(getFlowData(array_unique($flow)),null,1);
	}
	function ajaxgetflow_notice_personnel(){
		$uid = $_POST['uid'];
		$dept_id = $_POST['dept_id'];
		$dept_uid = getDeptManagerId($uid,$dept_id);
		$flow = array($dept_uid,getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid));
		$this->ajaxReturn(getFlowData(array_unique($flow)),null,1);
	}
	function ajaxgetflow_contract(){
		$uid = $_POST['uid'];
		$dept_id = $_POST['dept_id'];
		$dept_uid = getDeptManagerId($uid,$dept_id);
		$flow = array($dept_uid,getOfficeManagerId(),getLegalManagerId());
		$this->ajaxReturn(getFlowData(array_unique($flow)),null,1);
	}
	function ajaxgetflow_resignation(){
		$uid = $_POST['uid'];
		$dept_id = $_POST['dept_id'];
		$parentid = getParentid($uid);
		$dept_uid = getDeptManagerId($uid,$dept_id);
		if(getRank($uid)>1){//普通
			$flow = array($parentid,$dept_uid,getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid));
			$this->ajaxReturn(getFlowData(array_unique($flow)),null,1);
		}else{//行政副总，部门总监及以上
			$flow = array(getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid));
			$this->ajaxReturn(getFlowData(array_unique($flow)),null,1);
		}
	}
	function ajaxgetflow_probation(){
		$uid = $_POST['uid'];
		$parentid = getParentid($uid);
		$dept_uid = getDeptManagerId($uid);
		$flow = array($parentid,$dept_uid,getHRDeputyGeneralManagerId($uid));
		$this->ajaxReturn(getFlowData(array_unique($flow)),null,1);
	}
	function ajaxgetflow_regular_work_application(){
		$uid = $_POST['uid'];
		$dept_id = $_POST['dept_id'];
		$parentid = getParentid($uid);
		$dept_uid = getDeptManagerId($uid);
		$flow = array($parentid,$dept_uid,getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid));
		$this->ajaxReturn(getFlowData(array_unique($flow)),null,1);
	}
	function ajaxgetflow_personnel_changes(){
		$uid = $_POST['uid'];
		$dept_id_from = $_POST['dept_id'];
		$dept_id_to = $_POST['dept_id_to'];
		
		$parentid_1 = getParentid($uid);
		$dept_uid_1 = getDeptManagerId($uid,$dept_id_from);
		
// 		$parentid_2 = getParentid(null,$dept_id_to);
		$dept_uid_2 = getDeptManagerId(null,$dept_id_to);
		
		$flow = array($parentid_1,$dept_uid_1,$dept_uid_2,$dept_uid_2,getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid));
		$this->ajaxReturn(getFlowData(array_unique($flow)),null,1);
	}
	function ajaxgetflow_salary_changes(){
		$uid = $_POST['uid'];
		$dept_id_from = $_POST['dept_id'];
		
		$parentid_1 = getParentid($uid,$dept_id_from);
		$dept_uid_1 = getDeptManagerId($uid,$dept_id_from);
		
		$flow = array($parentid_1,$dept_uid_1,getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid));
		$this->ajaxReturn(getFlowData(array_unique($flow)),null,1);
	}
	function ajaxgetflow_resignation_list(){
		$uid = $_POST['uid'];
		$dept_id = $_POST['dept_id'];
		$flow = array(getRSManagerId());
		$this->ajaxReturn(getFlowData(array_unique($flow)),null,1);
	}
	function ajaxgetflow_office_supplies_application(){
		$uid = $_POST['uid'];
		$dept_id = $_POST['dept_id'];
		$dept_uid = getDeptManagerId($uid,$dept_id);
		$flow = array($dept_uid,getHRDeputyGeneralManagerId($uid),getFinancialManagerId(),getGeneralManagerId($uid));
		$this->ajaxReturn(getFlowData(array_unique($flow)),null,1);
	}
	function ajaxgetflow_office_use_application(){
		$uid = $_POST['uid'];
		$dept_id = $_POST['dept_id'];
		$dept_uid = getDeptManagerId($uid,$dept_id);
		$flow = array($dept_uid);
		$this->ajaxReturn(getFlowData(array_unique($flow)),null,1);
	}
	function ajaxgetflow_goods_procurement_allocation(){
		$uid = $_POST['uid'];
		$dept_id = $_POST['dept_id'];
		$dept_uid = getDeptManagerId($uid,$dept_id);
		$flow = array($dept_uid,getHRDeputyGeneralManagerId($uid),getFinancialManagerId(),getGeneralManagerId($uid));
		$this->ajaxReturn(getFlowData(array_unique($flow)),null,1);
	}
	function ajaxgetflow_bus_card_use(){
		$uid = $_POST['uid'];
		$dept_id = $_POST['dept_id'];
		$dept_uid = getDeptManagerId($uid,$dept_id);
		$flow = array($dept_uid,getFrontDesk());
		$this->ajaxReturn(getFlowData(array_unique($flow)),null,1);
	}
	function ajaxgetflow_chops_use(){
		$uid = $_POST['uid'];
		$dept_id = $_POST['dept_id'];
		$dept_uid = getDeptManagerId($uid,$dept_id);
		$flow = array($dept_uid);
		$this->ajaxReturn(getFlowData($flow),null,1);
	}
	function ajaxgetflow_car_use(){
		$uid = $_POST['uid'];
		$dept_id = $_POST['dept_id'];
		$dept_uid = getDeptManagerId($uid,$dept_id);
		$flow = array($dept_uid);
		$this->ajaxReturn(getFlowData($flow),null,1);
	}
	function ajaxgettime(){
		$start_time = $_POST['start_time'];
		$end_time = $_POST['end_time'];
		$type = $_GET['type'];
		if(strtotime($end_time)-strtotime($start_time)<7200){
			$this->ajaxReturn(null,null,1);
		}
		if ($type=='over_time' || $type=='metting' || $type=='outside') {
			$hour_sum = (strtotime($end_time)-strtotime($start_time))/3600;
			$day = floor($hour_sum/24);
			$hour = ceil($hour_sum - $day*24);
			$this->ajaxReturn(array('day'=>$day,'hour'=>$hour),null,1);
		}else{
			$hour_sum = get_leave_seconds(strtotime($start_time),strtotime($end_time))/3600;
			$day = floor($hour_sum/8);
			$hour = ceil($hour_sum - $day*8);
			$this->ajaxReturn(array('day'=>$day,'hour'=>$hour),null,1);
		}
		
	}

	/** 插入新新数据  **/
	protected function _insert() {
		$model = D("Flow");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		if (in_array('user_id', $model -> getDbFields())) {
			$model -> user_id = get_user_id();
		};
		if (in_array('user_name', $model -> getDbFields())) {
			$model -> user_name = get_user_name();
		};
// 		$str_confirm = D("Flow") -> _conv_auditor($model -> confirm);
// 		$str_consult = D("Flow") -> _conv_auditor($model -> consult);
// 		$str_auditor = $str_confirm . $str_consult;
// 		if (empty($str_auditor)) {
// 			$this -> error('没有找到任何审核人');
// 		}
		/*保存当前数据对象 */
		$list = $model -> add();

		$model_flow_filed = D("FlowField") -> set_field($list);

		if ($list !== false) {//保存成功
			$model = M(getModelName($list));
			if (false === $model -> create()) {
				$this -> error($model -> getError());
			}
			//字段中存放数组
			$array_field = array(attitude_me,attitude_leader,ability_me,ability_leader,responsibility_me,responsibility_leader,coordinate_me,coordinate_leader,develop_me,develop_leader,ids,names,types,nums,prices,amounts,marks,goods_name,usage,use_dept,buy_num,add_num,recovery_num,is_allocation,price,amount,add_num_calculation,pay_type,in_place_time);
			foreach ($array_field as $v){
				if(!empty($model -> $v) && is_array($model -> $v)){
					$$v = '';
					foreach ($model -> $v as $vv){
						$$v .=$vv.'|';
					}
					$model -> $v = $$v;
				}
			}
			
			$model -> flow_id = $list;
			$list = $model -> add();
			if ($list !== false) {//保存成功
				$this -> assign('jumpUrl', get_return_url());
				$this -> success('新增成功!');
			}else {
				$this -> error('新增失败!');
				//失败提示
			}
			
		} else {
			$this -> error('新增失败!');
			//失败提示
		}
	}

	function read() {
		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
		$folder = $_REQUEST['fid'];
		$this -> assign("folder", $folder);
		if (empty($folder)) {
			$this -> error("系统错误");
		}
		$this -> _flow_auth_filter($folder, $map);

		$model = D("Flow");
		$id = $_REQUEST['id'];
		$where['id'] = array('eq', $id);
		$where['_logic'] = 'and';
		$map['_complex'] = $where;
		$vo = $model -> where($map) -> find();
		if (empty($vo)) {
			$this -> error("系统错误");
		}
		$flow = M(getModelName($vo['id']))->where(array('flow_id'=>array('eq',$vo['id'])))->find();
		$vo = array_merge($vo,$flow);
		$flow_type_id = $vo['type'];
		
		//字段中存放数组
		$array_field = array(attitude_me,attitude_leader,ability_me,ability_leader,responsibility_me,responsibility_leader,coordinate_me,coordinate_leader,develop_me,develop_leader,ids,names,types,nums,prices,amounts,marks,goods_name,usage,use_dept,buy_num,add_num,recovery_num,is_allocation,price,amount,add_num_calculation,pay_type,in_place_time,basic_info_1,basic_info_2,basic_info_3,basic_info_4,basic_info_5,basic_info_6,basic_info_7,skill_honor_hobby_expect,education,family,training,family_urgency,work_experience);
		foreach ($array_field as $v){
			if(!empty($vo[$v])){
				$vo[$v] = explode('|',$vo[$v]);
			}
		}
		if($vo['pic']){$vo['pic'] = get_save_url() . $vo['pic'];}
		if($vo['education']){$vo['education'] = exp_info($vo['education']);}
		if($vo['training']){$vo['training'] = exp_info($vo['training']);}
		if($vo['family']){$vo['family'] = exp_info($vo['family']);}
		if($vo['work_experience']){$vo['work_experience'] = exp_info($vo['work_experience']);}
		$this -> assign('vo', $vo);
		$this -> assign("emp_no", $vo['emp_no']);
		$this -> assign("user_name", $vo['user_name']);
		
		$model_flow_field = D("FlowField");
		$field_list = $model_flow_field -> get_data_list($id);
		$this -> assign("field_list", $field_list);
		
		$model = M("FlowType");
		$flow_type = $model -> find($flow_type_id);
		$this -> assign("flow_type", $flow_type);
		
		$model = M("FlowLog");
		$where = array();
		$where['flow_id'] = $id;
		$where['step'] = array('lt', 100);
		$where['_string'] = "result is not null";
		$flow_log = $model -> where($where) -> order("id") -> select();
		$this -> assign("flow_log", $flow_log);
		//获取审核流程（加上重复的，加上空的）
		if($vo['name']=='用人申请流程'){
			$uid = $vo['user_id'];
			$dept_id = $vo['dept_id'];
			if(empty($uid)){
				$this -> error("系统错误");
			}
			$parent_list = array();
			$Parentid = getParentid(null,$dept_id);
			while($Parentid){
				$parent_list[] = $Parentid;
				$Parentid = getParentid($Parentid);
			}
			if(count($parent_list) == 1){
				$dept_idd = M('User')->where(array('pos_id'=>array('eq',$dept_id)))->getField('id');
			}else if(count($parent_list) >= 2){
				$dept_idd = $parent_list[count($parent_list)-2];
			}
			$flow = array($dept_idd,getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid));
		}else if($vo['name']=='请假/调休单' || $vo['name']=='外勤/出差单'){
			$flow = getFlow($vo['user_id'],$vo['day_num'],false);
			if(!is_array($flow) && !empty($flow)){
				$flow = array($flow);
			}
		}else if($vo['name']=='出勤证明流程'){
			$flow = array(getParentid($uid));
		}else if($vo['name']=='加班调休申请'){
			$uid = $vo['user_id'];
			$dept_id = $vo['dept_id'];
			$dept_uid = getDeptManagerId($uid,$dept_id);
			$flow = array($dept_uid,getHRDeputyGeneralManagerId($uid));
		}else if($vo['name']=='离职申请流程'){
			$uid = $vo['user_id'];
			$dept_id = $vo['dept_id'];
			$parentid = getParentid($uid);
			$dept_uid = getDeptManagerId($uid,$dept_id);
			if(getRank($uid)>1){//普通
				$flow = array($parentid,$dept_uid,getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid));
			}else{//行政副总，部门总监及以上
				$flow = array(null,null,getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid));
			}
		}else if($vo['name']=='试用期评估表'){
			$uid = $vo['user_id'];
			$dept_id = $vo['dept_id'];
			$parentid = getParentid($uid);
			$dept_uid = getDeptManagerId($uid,$dept_id);
			$flow = array($parentid,$dept_uid,getHRDeputyGeneralManagerId($uid));
		}else if($vo['name']=='转正申请'){
			$uid = $vo['user_id'];
			$dept_id = $vo['dept_id'];
			$parentid = getParentid($uid);
			$dept_uid = getDeptManagerId($uid);
			$flow = array($parentid,$dept_uid,getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid));
		}elseif ($vo['name']=='员工调岗、调职申请'){
			$uid = $vo['user_id'];
			$dept_id_from = $vo['dept_id'];
			$dept_id_to = $vo['dept_id_to'];
			
			$parentid_1 = getParentid($uid);
			$dept_uid_1 = getDeptManagerId($uid,$dept_id_from);
			
// 			$parentid_2 = getParentid(null,$dept_id_to);
			$dept_uid_2 = getDeptManagerId(null,$dept_id_to);
			
			$flow = array($parentid_1,$dept_uid_1,$dept_uid_2,$dept_uid_2,getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid));
			
		}elseif ($vo['name']=='员工调薪申请'){
			$uid = $vo['user_id'];
			$dept_id_from = $vo['dept_id'];
			$parentid_1 = getParentid($uid,$dept_id_from);
			$dept_uid_1 = getDeptManagerId($uid,$dept_id_from);
			$flow = array($parentid_1,$dept_uid_1,getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid));
		}elseif ($vo['name']=='物品采购调拨申请单'){
			$uid = $vo['user_id'];
			$dept_id = $vo['dept_id'];
			$dept_uid = getDeptManagerId($uid,$dept_id);
			$flow = array($dept_uid,getHRDeputyGeneralManagerId($uid),getFinancialManagerId(),getGeneralManagerId($uid));
		
		}elseif ($vo['type']==66){
			$uid = $vo['user_id'];
			$parentId = getParentId(1);
			$flow = array($parentId);
		}
		
		$search = array_keys($flow,get_user_id());
		$flow_index = array();
		if(!empty($search) && is_array($search)){
			foreach ($search as $k=>$v){
				$flow_index[$v+1] = $v+1;
			}
		}
		
		$this -> assign("flow_index", $flow_index);
// 		if($search !== false){//自己在哪个流程上(审核用，总经理的是否需要参加复试)
// 			$this -> assign("flow_index", $search+1);
// 			$this -> assign("flow_index_n", count($confirm_array)-$search-1);
// 		}
		
		$flow_log_all = array();
		foreach ($flow as $k=>$v){//加上null
			if($v == null){
				$flow_log_all[$k] = $v;
			}
		}
		foreach ($flow_log as $v){
			foreach ($flow as $kk=>$vv){
				if($v['user_id'] == $vv){
					$flow_log_all[$kk] = $v;
				}
			}
		}
// 		var_dump($flow_index);
// 		var_dump($flow_log_all);
		$this -> assign("flow_log_all", $flow_log_all);
		$this -> assign("isZhaopinDirector", isZhaopinDirector(get_user_id()));
// 		var_dump(isZhaopinDirector(get_user_id()));
// 		print_r($flow_log);
// 		print_r($vo);
// 		print_r($flow);
// 		print_r($flow_log_all);

		$where = array();
		$where['flow_id'] = $id;
		$where['emp_no'] = get_emp_no();
		$where['_string'] = "result is null";
		$to_confirm = $model -> where($where) -> find();
		$this -> assign("to_confirm", $to_confirm);

		if (!empty($to_confirm)) {
			$is_edit = $flow_type['is_edit'];
			$this -> assign("is_edit", $is_edit);
		} else {
			$is_edit = $flow_type['is_edit'];
			if ($is_edit <> "2") {
				$this -> assign("is_edit", 0);
			}
		}

		$where = array();
		$where['flow_id'] = $id;
		$where['_string'] = "result is not null";
		$where['emp_no'] = array('neq', $vo['emp_no']);
		$confirmed = $model -> Distinct(true) -> where($where) -> field('emp_no,user_name') -> select();
		$this -> assign("confirmed", $confirmed);
		$this -> display();
	}
	function editflow() {
		$id = intval($_POST['id']);//flow_* 的id
		$flow_id = intval($_POST['flow_id']);//flow_* 的flow_id
		$is_retrial = $_POST['is_retrial'];
		$recruit_difficult = $_POST['recruit_difficult'];
		$real_arrive_date = $_POST['real_arrive_date'];
		$hand_over_time = $_POST['hand_over_time'];
		$non_competition_compensation = $_POST['non_competition_compensation'];
		$dept_leader_review = $_POST['dept_leader_review'];
		$dept_leader_type = $_POST['dept_leader_type'];
		$dept_leader_day = $_POST['dept_leader_day'];
		$attitude_leader = $_POST['attitude_leader'];
		$ability_leader = $_POST['ability_leader'];
		
		$responsibility_leader = $_POST['responsibility_leader'];
		$coordinate_leader = $_POST['coordinate_leader'];
		$develop_leader = $_POST['develop_leader'];
		$sum_leader = $_POST['sum_leader'];
		
		$dept_leader_date = $_POST['dept_leader_date'];
		$dept_director_review = $_POST['dept_director_review'];
		$dept_director_date = $_POST['dept_director_date'];
		$hr_type = $_POST['hr_type'];
		$hr_add_date = $_POST['hr_add_date'];
		$hr_execute = $_POST['hr_execute'];
		$hr_dismiss = $_POST['hr_dismiss'];
		
		$from_leader_review = $_POST['from_leader_review'];
		$from_leader_date = $_POST['from_leader_date'];
		$to_leader_review = $_POST['to_leader_review'];
		$to_leader_date = $_POST['to_leader_date'];
		$from_director_review = $_POST['from_director_review'];
		$to_director_review = $_POST['to_director_review'];
		$superior_estimate = $_POST['superior_estimate'];
		
		$model = M(getModelName($flow_id));
		if($id){
			$data['id'] = $id;
		}
		if($is_retrial){
			$data['is_retrial'] = $is_retrial;
		}
		if($recruit_difficult){
			$data['recruit_difficult'] = $recruit_difficult;
		}
		if($real_arrive_date){
			$data['real_arrive_date'] = $real_arrive_date;
		}
		if($hand_over_time){
			$data['hand_over_time'] = $hand_over_time;
		}
		if($non_competition_compensation){
			$data['non_competition_compensation'] = $non_competition_compensation;
		}
		if($dept_leader_review){
			$data['dept_leader_review'] = $dept_leader_review;
		}
		if($dept_leader_type){
			$data['dept_leader_type'] = $dept_leader_type;
		}
		if($dept_leader_day){
			$data['dept_leader_day'] = $dept_leader_day;
		}
		
		if($dept_leader_date){
			$data['dept_leader_date'] = $dept_leader_date;
		}
		if($dept_director_review){
			$data['dept_director_review'] = $dept_director_review;
		}
		if($dept_director_date){
			$data['dept_director_date'] = $dept_director_date;
		}
		if($hr_type){
			$data['hr_type'] = $hr_type;
		}
		if($hr_add_date){
			$data['hr_add_date'] = $hr_add_date;
		}
		if($hr_execute){
			$data['hr_execute'] = $hr_execute;
		}
		if($hr_dismiss){
			$data['hr_dismiss'] = $hr_dismiss;
		}
		
		if($from_leader_review){
			$data['from_leader_review'] = $from_leader_review;
		}
		if($from_leader_date){
			$data['from_leader_date'] = $from_leader_date;
		}
		if($to_leader_review){
			$data['to_leader_review'] = $to_leader_review;
		}
		if($to_leader_date){
			$data['to_leader_date'] = $to_leader_date;
		}
		if($from_director_review){
			$data['from_director_review'] = $from_director_review;
		}
		if($to_director_review){
			$data['to_director_review'] = $to_director_review;
		}
		if($attitude_leader){
			$data['attitude_leader'] = $attitude_leader;
		}
		if($ability_leader){
			$data['ability_leader'] = $ability_leader;
		}
		if($responsibility_leader){
			$data['responsibility_leader'] = $responsibility_leader;
		}
		if($coordinate_leader){
			$data['coordinate_leader'] = $coordinate_leader;
		}
		if($develop_leader){
			$data['develop_leader'] = $develop_leader;
		}
		if($sum_leader){
			$data['sum_leader'] = $sum_leader;
		}
		if($superior_estimate){
			$data['superior_estimate'] = $superior_estimate;
		}
		
		$res = $model->save($data);
		if($res){
			$this->ajaxReturn(1,1,0);
		}else{
			$this->ajaxReturn(null,null,0);
		}
	}
	function edit() {
		//草稿修改
// 		$this -> error("系统错误");
		
		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);

		$folder = $_REQUEST['fid'];
		$this -> assign("folder", $folder);

		if (empty($folder)) {
			$this -> error("系统错误");
		}
		$this -> _flow_auth_filter($folder, $map);

		$model = D("Flow");
		$id = $_REQUEST['id'];
		$where['id'] = array('eq', $id);
		$where['_logic'] = 'and';
		$map['_complex'] = $where;
		$vo = $model -> where($map) -> find();
		if (empty($vo)) {
			$this -> error("系统错误");
		}
		$flow = M(getModelName($vo['id']))->where(array('flow_id'=>array('eq',$vo['id'])))->find();
		$vo = array_merge($vo,$flow);
		
		//字段中存放数组
		$array_field = array(attitude_me,attitude_leader,ability_me,ability_leader,responsibility_me,responsibility_leader,coordinate_me,coordinate_leader,develop_me,develop_leader,ids,names,types,nums,prices,amounts,marks,goods_name,usage,use_dept,buy_num,add_num,recovery_num,is_allocation,price,amount,add_num_calculation,pay_type,in_place_time);
		foreach ($array_field as $v){
			if(!empty($vo[$v])){
				$vo[$v] = explode('|',$vo[$v]);
			}
		}
		
		$this -> assign('vo', $vo);
		$model_flow_field = D("FlowField");
		$field_list = $model_flow_field -> get_data_list($id);
		$this -> assign("field_list", $field_list);

		$model = M("FlowType");
		$type = $vo['type'];
		$flow_type = $model -> find($type);
		$this -> assign("flow_type", $flow_type);
		$model = M("FlowLog");
		$where = array();
		$where['flow_id'] = $id;
		$where['_string'] = "result is not null";
		$flow_log = $model -> where($where) -> select();
		if ($flow_log) {
			$this -> error("系统错误");
		}
		$this -> assign("flow_log", $flow_log);
		$where = array();
		$where['flow_id'] = $id;
		$where['emp_no'] = get_emp_no();
		$where['_string'] = "result is null";
		$confirm = $model -> where($where) -> select();
		$this -> assign("confirm", $confirm[0]);
		$this -> display();
	}

	/* 更新数据  */
	protected function _update() {
		$name = $this -> getActionName();
		$model = D($name);
		
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		
		$idd = $model -> id;
		$flow_id = $_POST['flow_id'];//model中没有flow_id，所以不能用$model -> flow_id
		$model -> id = $flow_id;
		
		$list = $model -> save();
		$model_flow_filed = D("FlowField") -> set_field($flow_id);
		if (false !== $list) {
			$model = M(getModelName($flow_id));
			
			if (false === $model -> create()) {
				$this -> error($model -> getError());
			}
			//字段中存放数组
			$array_field = array(attitude_me,attitude_leader,ability_me,ability_leader,responsibility_me,responsibility_leader,coordinate_me,coordinate_leader,develop_me,develop_leader,ids,names,types,nums,prices,amounts,marks,goods_name,usage,use_dept,buy_num,add_num,recovery_num,is_allocation,price,amount,add_num_calculation,pay_type,in_place_time);
			foreach ($array_field as $v){
				if(!empty($model -> $v) && is_array($model -> $v)){
					$$v = '';
					foreach ($model -> $v as $vv){
						$$v .=$vv.'|';
					}
					$model -> $v = $$v;
				}
			}
			$model -> id = $idd;
			
			$list = $model -> save();
			if (false !== $list) {
				$this -> assign('jumpUrl', get_return_url());
				$this -> success('编辑成功!');
			}
			//成功提示
		} else {
			$this -> error('编辑失败!');
			//错误提示
		}
	}

	public function mark() {
		$action = $_REQUEST['action'];
		switch ($action) {
			case 'approve' :
				$model = D("FlowLog");
				if (false === $model -> create()) {
					$this -> error($model -> getError());
				}

				$model -> result = 1;

				$flow_id = $model -> flow_id;
				$step = $model -> step;
				//保存当前数据对象
				$list = $model -> save();
				$model = D("FlowLog");
				$model -> where("step=$step and flow_id=$flow_id and result is null") -> delete();

				if ($list !== false) {//保存成功
					D("Flow") -> save();
					D("Flow") -> next_step($flow_id, $step);
					$this -> assign('jumpUrl', U('flow/folder?fid=confirm'));
					$this -> success('操作成功!');
				} else {
					//失败提示
					$this -> error('操作失败!');
				}
				break;
			case 'back' :
				$model = D("FlowLog");
				if (false === $model -> create()) {
					$this -> error($model -> getError());
				}

				$model -> result = 2;
				if (in_array('user_id', $model -> getDbFields())) {
					$model -> user_id = get_user_id();
				};
				if (in_array('user_name', $model -> getDbFields())) {
					$model -> user_name = get_user_name();
				};

				$flow_id = $model -> flow_id;
				$step = $model -> step;
				//保存当前数据对象
				$list = $model -> save();
				$emp_no = $_REQUEST['emp_no'];
				if ($list !== false) {//保存成功
					D("Flow") -> next_step($flow_id, $step, $emp_no);
					$this -> assign('jumpUrl', U('flow/folder?fid=confirm'));
					$this -> success('操作成功!');
				} else {
					//失败提示
					$this -> error('操作失败!');
				}
				break;
			case 'reject' :
				$model = D("FlowLog");
				if (false === $model -> create()) {
					$this -> error($model -> getError());
				}
				$model -> result = 0;
				if (in_array('user_id', $model -> getDbFields())) {
					$model -> user_id = get_user_id();
				};
				if (in_array('user_name', $model -> getDbFields())) {
					$model -> user_name = get_user_name();
				};

				$flow_id = $model -> flow_id;
				$step = $model -> step;
				//保存当前数据对象
				$list = $model -> save();
				//可以裁决的人有多个人的时候，一个人评价完以后，禁止其他人重复裁决。
				$model = D("FlowLog");
				$model -> where("step=$step and flow_id=$flow_id and result is null") -> delete();

				if ($list !== false) {//保存成功
					D("Flow") -> where("id=$flow_id") -> setField('step', 0);

					$user_id = M("Flow") -> where("id=$flow_id") -> getField('user_id');
					$this -> _pushReturn($new, "您有一个流程被否决", 1, $user_id);

					$this -> assign('jumpUrl', U('flow/folder?fid=confirm'));
					$this -> success('操作成功!');
				} else {
					//失败提示
					$this -> error('操作失败!');
				}
				break;
			default :
				break;
		}
	}

	public function approve() {

		$model = D("FlowLog");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$model -> result = 1;

		$flow_id = $model -> flow_id;
		$step = $model -> step;
		//保存当前数据对象
		$list = $model -> save();

		$model = D("FlowLog");
		$model -> where("step=$step and flow_id=$flow_id and result is null") -> setField('is_del', 1);

		if ($list !== false) {//保存成功
			D("Flow") -> next_step($flow_id, $step);
			$this -> assign('jumpUrl', U('flow/confirm'));
			$this -> success('操作成功!');
		} else {
			//失败提示
			$this -> error('操作失败!');
		}
	}

	public function reject() {
		$model = D("FlowLog");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$model -> result = 0;
		if (in_array('user_id', $model -> getDbFields())) {
			$model -> user_id = get_user_id();
		};
		if (in_array('user_name', $model -> getDbFields())) {
			$model -> user_name = get_user_name();
		};

		$flow_id = $model -> flow_id;
		$step = $model -> step;
		//保存当前数据对象
		$list = $model -> save();
		//可以裁决的人有多个人的时候，一个人评价完以后，禁止其他人重复裁决。
		$model = D("FlowLog");
		$model -> where("step=$step and flow_id=$flow_id and result is null") -> setField('is_del', 1);

		if ($list !== false) {//保存成功
			D("Flow") -> where("id=$flow_id") -> setField('step', 0);

			$user_id = M("Flow") -> where("id=$flow_id") -> getField('user_id');

			$this -> _pushReturn($new, "您有一个流程被否决", 1, $user_id);

			$this -> assign('jumpUrl', U('flow/confirm'));
			$this -> success('操作成功!');
		} else {
			//失败提示
			$this -> error('操作失败!');
		}
	}

	public function down() {
		$this -> _down();
	}

	public function upload() {
		$this -> _upload();
	}

	protected function _assign_tag_list() {
		$model = D("SystemTag");
		$tag_list = $model -> get_tag_list('id,name', 'FlowType');
		$this -> assign("tag_list", $tag_list);
	}

}
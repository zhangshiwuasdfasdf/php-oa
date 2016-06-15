<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class ReportAction extends CommonAction {
	protected $config = array('app_type' => 'common', 'action_auth' => array('share' => 'read', 'plan' => 'read', 'save_comment' => 'write', 'edit_comment' => 'write', 'reply_comment' => 'write', 'del_comment' => 'admin','delivery_read'=>'read','delivery_edit'=>'read','delivery_del'=>'read','delivery'=>'read','export_delivery_report' => 'read','import_delivery_report' => 'read'));
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
	
	public function delivery() {
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign('user_id', get_user_id());

		$auth = $this -> config['auth'];
		$this -> assign('auth', $auth);

		
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
			

		if ( D("Role") -> check_duty('SHOW_LOG')) {//查看所有日志
			$map=array();
		}

		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}

		$model = D("Delivery");
		if (!empty($model)) {
			$this -> _list($model, $map);
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

	public function delivery_read($id) {
// 		if(is_mobile_request()){
// 			$id = $_REQUEST['pid'];
// 		}
		$this -> assign('uid',get_user_id());
		$this -> assign('id', $id);
		$this -> assign('auth', $this -> config['auth']);

		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);

		$where['id'] = array('eq', $id);
		$delivery = M("Delivery") -> where($where) -> order('id desc') -> find();
		$this -> assign('delivery', $delivery);
		
		$where_detail['pid'] = $delivery['id'];
		$delivery_detail = M("DeliveryDetail") -> where($where_detail) -> select();

		$this -> assign('delivery_detail', $delivery_detail);

		$store_name = M("DeliveryDetail") -> field('store_name') ->distinct(true) -> select();
		$this -> assign('store_name', $store_name);
		
		$date = M("DeliveryDetail") -> field('date') ->distinct(true) -> select();
		$this -> assign('date', $date);
		
		$express = M("DeliveryDetail") -> field('express') ->distinct(true) -> select();
		$this -> assign('express', $express);
		
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
	function export_delivery_report(){
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
		$q = $q -> setCellValue("F1", '工作时间（起）hh:mm半小时为单位');
		$q = $q -> setCellValue("G1", '工作时间（止）hh:mm半小时为单位');
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
		$q = $q -> setCellValue("A8", '今日工作小结');
		$q = $q -> mergeCells('A8:A9');
		$q = $q -> mergeCells('B8:H9');
		
		$q = $q -> setCellValue("A10", '今日自我评价：');
		$q = $q -> setCellValue("B10", '认真');
		$q = $q -> setCellValue("C10", '效率');
		$q = $q -> setCellValue("D10", '坚守承诺');
		$q = $q -> setCellValue("E10", '保证完成任务');
		$q = $q -> setCellValue("F10", '乐观');
		$q = $q -> setCellValue("G10", '自信');
		$q = $q -> setCellValue("H10", '爱与奉献');
		$q = $q -> setCellValue("I10", '绝不找借口');
		$q = $q -> setCellValue("J10", '合计');
		
		$q = $q -> setCellValue("A11", '每项1-10分');
		
		$q = $q -> setCellValue("A14", '明日工作计划（A类最重要 B类重要 C类次重要）');
		$q = $q -> mergeCells('A14:I14');
		
		$q->getStyle('A14')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
		$q = $q -> setCellValue("A15", '序号');
		$q = $q -> setCellValue("B15", '主要工作事项');
		$q = $q -> mergeCells('B15:C15');
		$q = $q -> setCellValue("D15", '计划推荐目标');
		$q = $q -> mergeCells('D15:E15');
		$q = $q -> setCellValue("F15", '时间安排（起）hh:mm半小时为单位');
		$q = $q -> setCellValue("G15", '时间安排（止）hh:mm半小时为单位');
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
		
		$q = $q -> setCellValue("A23", '明日目标');
		$q = $q -> mergeCells('A23:A24');
		$q = $q -> mergeCells('B23:I24');
		
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
		$title = '日报';
		$objPHPExcel -> getActiveSheet() -> setTitle('日报');
		
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
			
				$model_delivery = M("Delivery");
				$delivery = array();
				$delivery['user_id'] = get_user_id();
				$delivery['user_name'] = get_user_name();
				$delivery['dept_id'] = get_dept_id();
				$delivery['dept_name'] = get_dept_name();
				$delivery['create_time'] = time();
				$pid = $model_delivery->add($delivery);
				if($pid){
					$model_delivery_detail = M("DeliveryDetail");
					for($i=3;$i<$x-1;$i++){
						for($j=4;$j<$y;$j++){
							if($sheetData[$j][ToNumberSystem26($i)]!='' && $sheetData[$j]['A']!='小计'){
								$delivery_detail = array();
								$delivery_detail['pid'] = $pid;
								$delivery_detail['store_name'] = $sheetData[2][ToNumberSystem26($i)];
								$delivery_detail['express'] = $sheetData[$j]['B'];
								$date_0 = $sheetData[$j]['A'];
								$date_array = explode('-',$date_0);
								$delivery_detail['date'] = '20'.$date_array[2].'-'.$date_array[0].'-'.$date_array[1];
								$delivery_detail['num'] = $sheetData[$j][ToNumberSystem26($i)];
								$res = $model_delivery_detail->add($delivery_detail);
								if(!$res){
									$this -> error('导入具体信息失败：'.ToNumberSystem26($i).' '.$j);
									exit ;
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
}
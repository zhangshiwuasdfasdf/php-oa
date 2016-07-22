<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class ReportAction extends CommonAction {
	protected $config = array('app_type' => 'common', 'action_auth' => array('share' => 'read', 'plan' => 'read', 'save_comment' => 'write', 'edit_comment' => 'write', 'reply_comment' => 'write', 'del_comment' => 'admin','delivery_read'=>'read','delivery_read_all'=>'read','delivery_edit'=>'read','delivery_del'=>'read','delivery'=>'read','export_delivery_report' => 'read','import_delivery_report' => 'read'));
	//过滤查询字段
	function _search_filter(&$map) {
		if (!empty($_POST['eq_addr'])) {
			$where_delivery['addr'] = array('eq',$_POST['eq_addr']);
		}
// 		if (!empty($_POST['be_create_time'])) {
// 			$where['be_create_time'] = array('like', '%' . $_POST['content'] . '%');
// 			$where['plan'] = array('like', '%' . $_POST['content'] . '%');
// 			$where['_logic'] = 'or';
// 			$map['_complex'] = $where;
// 		}
		$start_time = $_POST['be_create_time'];
		$end_time = $_POST['en_create_time'];
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

		
// 		if(D("Role") -> check_duty('SHOW_LOG_LOW_ALL')){//允许查看自己及以下所有日志
// 			$child_ids = array_merge(array(intval(get_user_id())),array_keys(array_to_one_dimension(get_child_ids_all(get_user_id()))));
// 			$map['user_id'] = array('in',$child_ids);
// 		}elseif(D("Role") -> check_duty('SHOW_LOG_LOW')){//允许查看自己及下一级日志
// 			$child_ids = array_merge(array(intval(get_user_id())),get_child_ids(get_user_id()));
// 			$map['user_id'] = array('in',$child_ids);
// 		}
// 		else{//查看自己的日志
// 			$map['user_id'] = array('eq',intval(get_user_id()));
// 		}
			

// 		if ( D("Role") -> check_duty('SHOW_LOG')) {//查看所有日志
// 			$map=array();
// 		}

// 		if (method_exists($this, '_search_filter')) {
// 			$this -> _search_filter($map);
// 		}

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
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign('auth', $this -> config['auth']);
		$this -> assign('user_id', get_user_id());
		
		$addr = M("Delivery") -> field('addr as id,addr as name') ->distinct(true) -> select();
		$this -> assign('addr_list', $addr);
		
		$where = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($where);
		}
		
// 		$where['id'] = array('eq', $id);
		$delivery = M("Delivery") -> where($where['_complex']['delivery']) -> order('id desc') -> select();
		$this -> assign('delivery', $delivery);
		
		$delivery_id = rotate($delivery);
		$delivery_id = $delivery_id['id'];
		$delivery_id = implode(',',$delivery_id);
		
		$where_detail = $where['_complex']['delivery_detail'];
		$where_detail['pid'] = array('in',$delivery_id);
// 		dump($where_detail);
		$delivery_detail = M("DeliveryDetail") -> where($where_detail) -> select();
// 		dump($delivery_detail);
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
		// 		dump($aa);
		$this -> assign('sum_day', $sum_day);
		$this -> assign('delivery_detail', $aa);
		$this -> assign('store_name_same_day', $store_name_same_day);
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
		
		$q = $objPHPExcel -> setActiveSheetIndex(0);
		//第一列为用户
		$q = $q -> setCellValue("A1", '基地月仓库发货日报表');
		$q->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
		$q = $q -> mergeCells('A1:Z1');
		
		$q = $q -> setCellValue("A3", '日期');
		$q = $q -> setCellValue("B3", '快递单位');
		
		for($i=ord('C');$i<=ord('Z');$i++){
			$q = $q -> setCellValue(chr($i)."2", '某某某商家');
			$q ->getStyle(chr($i)."2")->getAlignment()->setWrapText(true);
			$q ->getRowDimension(2)->setRowHeight(80);
			$q = $q -> setCellValue(chr($i)."3", $i-ord('C')+1);
		}
		
		$q = $q -> setCellValue("A4", date('Y/m/d',time()));
		$q = $q -> setCellValue("B4", '韵达');
		
		$q = $q -> setCellValue("A5", date('Y/m/d',time()));
		$q = $q -> setCellValue("B5", '中通');
		
		$q = $q -> setCellValue("A6", date('Y/m/d',time()));
		$q = $q -> setCellValue("B6", '京东');
		
		$q = $q -> setCellValue("A7", date('Y/m/d',time()));
		$q = $q -> setCellValue("B7", '邮政小包');
		
		$q = $q -> setCellValue("A8", date('Y/m/d',time()));
		$q = $q -> setCellValue("B8", '汇通');
		
		$q = $q -> setCellValue("A9", date('Y/m/d',time()));
		$q = $q -> setCellValue("B9", '申通');
		
		$q = $q -> setCellValue("A10", date('Y/m/d',time()));
		$q = $q -> setCellValue("B10", '顺丰');
		
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
				$title2 = explode('月',$title1[1]);
				if(!is_numeric($title2[0]) || $title2[0]<0 || $title2[0]>12){
					$this -> error('月份必须是1-12');
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
							if($sheetData[$j][ToNumberSystem26($i)]!='' && $sheetData[$j]['A']!='小计' && $sheetData[$j]['B']!='小计' && $sheetData[$j]['A']!='总计' && $sheetData[$j]['B']!='总计' && $sheetData[2][ToNumberSystem26($i)]!='合计' && $sheetData[3][ToNumberSystem26($i)]!='合计'){
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

<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class ProblemFeedbackAction extends CommonAction {
	protected $config = array('app_type' => 'common', 'action_auth' => array('share' => 'read', 'plan' => 'read', 'save_comment' => 'write', 'edit_comment' => 'write', 'reply_comment' => 'write','del' => 'write', 'del_comment' => 'write','export_daily_report' => 'read','import_daily_report' => 'read','get_dept_child' => 'read','get_real_dept'=>'read','get_username_by_dept'=>'read','json'=>'read'));
	//过滤查询字段
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_POST['problem_no'])) {
			$map['problem_no'] = array('eq', $_POST['problem_no']);
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
		if (!empty($_POST['user_name'])) {
			$map['create_user_name'] = array('eq', $_POST['user_name']);
		}
		if ($_POST['me'] == '1') {
			$map['create_user_name'] = array('eq', get_user_name());
		}
		if (!empty($_POST['deal_user_name'])) {
			$map['deal_user_name'] = array('eq', $_POST['deal_user_name']);
		}
		if (!empty($_POST['type'])) {
			$map['type'] = array('eq', $_POST['type']);
		}
		if (!empty($_POST['status'])) {
			$map['status'] = array('eq', $_POST['status']);
		}
		if (!empty($_POST['title'])) {
			$map['title'] = array('like', '%'.$_POST['title'].'%');
		}
	}
	
	public function index() {
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign('user_id', get_user_id());
		
		$node = D("Dept");
		$dept_menu = $node -> field('id,pid,name') -> where("is_del=0 and is_real_dept=1") -> order('sort asc') -> select();
		$dept_tree = list_to_tree($dept_menu);
		if(!is_mobile_request()){
			$this -> assign('dept_list_new', select_tree_menu_mul($dept_tree));
		}
		
		$type_list = M('ProblemFeedback')->field('type as id')->distinct(true)->select();
		foreach ($type_list as $k=>$v){
			$type_list[$k]['name'] = show_mapping($v['id']);
		}
		$this -> assign('type_list', $type_list);
		
		$status_list = M('ProblemFeedback')->field('status as id')->distinct(true)->select();
		foreach ($status_list as $k=>$v){
			$status_list[$k]['name'] = show_mapping($v['id']);
		}
		$this -> assign('status_list', $status_list);
		
		$auth = $this -> config['auth'];
		$this -> assign('auth', $auth);
		
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$this->_list(M('ProblemFeedback'), $map);
		$this -> display();
	}

	public function add() {
		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
		
		$emergency = M('SimpleDataMapping')->field('id,data_type,data_code,data_name')->where(array('data_type'=>'紧急程度','is_del'=>0))->select();
		foreach ($emergency as $k=>$v){
			$e['id'] = $v['data_type'].'_'.$v['data_code'];
			$e['name'] = $v['data_name'];
			$emergency_list[] = $e;
		}
		$this -> assign("emergency_list", $emergency_list);
		$this -> display();
	}

	public function read($id) {
		$this -> assign('auth', $this -> config['auth']);
		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
		
		$problem_feedback = M('ProblemFeedback')->find($id);
		$this -> assign("problem_feedback", $problem_feedback);
		
//		$problem_feedback_comment = M('ProblemFeedbackComment')->where(array('pid'=>$id))->select();
//		$this -> assign("problem_feedback_comment", $problem_feedback_comment);
		$this ->_list(M('ProblemFeedbackComment'), array('pid'=>$id),'id',true);
		
		$user_id = get_user_id();
		$this -> assign("user_id", $user_id);
		
		
		$type = M('SimpleDataMapping')->field('id,data_type,data_code,data_name')->where(array('data_type'=>'oa问题类型','is_del'=>0))->select();
		foreach ($type as $k=>$v){
			$e['id'] = $v['data_type'].'_'.$v['data_code'];
			$e['name'] = $v['data_name'];
			$type_list[] = $e;
		}
		$this -> assign("type_list", $type_list);
		
		$status = M('SimpleDataMapping')->field('id,data_type,data_code,data_name')->where(array('data_type'=>'oa处理状态','is_del'=>0))->select();
		foreach ($status as $k=>$v){
			$e['id'] = $v['data_type'].'_'.$v['data_code'];
			$e['name'] = $v['data_name'];
			$status_list[] = $e;
		}
		$this -> assign("status_list", $status_list);
		
		$auth = $this -> config['auth'];
		$this -> assign("auth", $auth);
		$this -> display();
	}

	public function edit($id) {
		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
		
		$problem_feedback = M('ProblemFeedback')->find($id);
		$this -> assign("problem_feedback", $problem_feedback);
		
		$emergency = M('SimpleDataMapping')->field('id,data_type,data_code,data_name')->where(array('data_type'=>'紧急程度','is_del'=>0))->select();
		foreach ($emergency as $k=>$v){
			$e['id'] = $v['data_type'].'_'.$v['data_code'];
			$e['name'] = $v['data_name'];
			$emergency_list[] = $e;
		}
		$this -> assign("emergency_list", $emergency_list);
		
		$user_id = get_user_id();
		$this -> assign("user_id", $user_id);
		
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
		$model = D("ProblemFeedback");
		$last = $model->where(array('problem_no'=>array('like',date('ym',time()).'%')))->order('problem_no desc')->limit(1)->find();
	
		if($last){
			$num = intval(substr($last['problem_no'],4));
			$num_str = formatto4w($num+1);
		}else{
			$num_str = formatto4w(1);
		}
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		
		$model -> problem_no = date('ym',time()).$num_str;
		$model -> create_time = time();
		$model -> create_user_id = get_user_id();
		$model -> create_user_name = get_user_name();
		$model -> dept_id = get_dept_id();
		$model -> dept_name = get_dept_name();
		$model -> pos_id = get_position_id();
		$pos_name = M('Dept')->field('name')->find($model -> pos_id);
		$model -> pos_name = $pos_name['name'];
		$model -> browser = getBrowser().' '.getBrowserVer();
		$model -> os = determineplatform();
		$model -> status = 'oa处理状态_01';
		$model -> is_del = 0;
		
		/*保存当前数据对象 */
		$list = $model -> add();
		
		if ($list !== false) {//保存成功
			//发代办给某些人
			add_problem_feedback($list);
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('新增成功!');
		} else {
			$this -> error('新增失败!');
			//失败提示
		}
	}

	/** 插入新新数据  **/
	protected function _update() {
	$model = D("ProblemFeedback");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$model -> browser = getBrowser().' '.getBrowserVer();
		$model -> os = determineplatform();
		$model -> status = 'oa处理状态_01';
		$id = $model -> id;
		/*保存当前数据对象 */
		$list = $model -> save();
		
		if ($list !== false) {//保存成功
			//自己的代办取消
			del_problem_feedback($id,array(get_user_id()));
			//发代办给某些人
			add_problem_feedback($id);
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('新增成功!');
		} else {
			$this -> error('新增失败!');
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
		$model = D('ProblemFeedbackComment');
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
		$model -> reply_user_id = get_user_id();
		$model -> reply_user_name = get_user_name();
		$model -> reply_time = time();
		$model -> dept_id = get_dept_id();
		$model -> dept_name = get_dept_name();
		$model -> pos_id = get_position_id();
		$pos_name = M('Dept')->field('name')->find($model -> pos_id);
		$model -> pos_name = $pos_name['name'];
		
		$problem_feedback['id'] = $model -> pid;
		if(!empty($model -> type)){
			$problem_feedback['type'] = $model -> type;
		}
		if(!empty($model -> status)){
			$problem_feedback['status'] = $model -> status;
		}
		if(!empty($model -> type) && !empty($model -> status)){
			$problem_feedback['deal_user_id'] = $model -> reply_user_id;
			$problem_feedback['deal_user_name'] = $model -> reply_user_name;
		}
		$cc_this = $model -> cc;
		$cc_this = array_filter(explode('|',$cc_this));
		$cc_this_backup = $cc_this;
		
		$cc_last = M('ProblemFeedback')->field('cc')->find($problem_feedback['id']);
		$cc_last = $cc_last['cc'];
		$cc_last = array_filter(explode('|',$cc_last));
		$cc_last_backup = $cc_last;
		
		$cc_this = array_unique(array_merge($cc_last,$cc_this));
		$cc_this = implode('|',$cc_this);
		$problem_feedback['cc'] = $cc_this;
		
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
			M('ProblemFeedback')->save($problem_feedback);
			//审核人处理好后取消代办
			if(!empty($problem_feedback['type']) && !empty($problem_feedback['status'])){
				//如果状态为已退回时，系统发待办给提交人
				if($problem_feedback['status'] == 'oa处理状态_00'){
					$tmp = M('ProblemFeedback')->find($problem_feedback['id']);
					add_problem_feedback($problem_feedback['id'],array($tmp['create_user_id']),1);
				}
				del_problem_feedback($problem_feedback['id']);
				if(!empty($cc_this_backup)){
					add_problem_feedback($problem_feedback['id'],$cc_this_backup);
				}else{//解决完成发站内信给发起人
					$url = U('problem_feedback/read?id='.$problem_feedback['id']);
					$data['content']='ERP问题反馈已有回复，问题状态为'.show_mapping($problem_feedback['status']).'。<a href="'.$url.'">点击跳转</a>';
					$data['sender_id']=get_user_id();
					$data['sender_name']=get_user_name();
					$data['create_time']=time();
					
					$model = D('Message');
					
					$tmp = M('ProblemFeedback')->find($problem_feedback['id']);
					$data['receiver_id']=$tmp['create_user_id'];
					$data['receiver_name']=$tmp['create_user_name'];			
					$data['owner_id']=get_user_id();
				
					$list = $model -> add($data);
		
					$data['owner_id']=$tmp['create_user_id'];
					$list = $model -> add($data);
					$this -> _pushReturn("", "您有新的消息, 请注意查收", 1,$tmp['create_user_id']);
				}
			}elseif(in_array(get_user_id(),$cc_last_backup)){//呈送给我
				//取消自己的代办
				del_problem_feedback($problem_feedback['id'],$cc_last_backup);
				//如果这次没有呈送
				if(empty($cc_this_backup)){
					//重新给审核人发代办
					add_problem_feedback($problem_feedback['id']);
					
					//解决完成发站内信给发起人
					$url = U('problem_feedback/read?id='.$problem_feedback['id']);
					$data['content']='ERP问题反馈已有回复，问题状态为'.show_mapping($problem_feedback['status']).'。<a href="'.$url.'">点击跳转</a>';
					$data['sender_id']=get_user_id();
					$data['sender_name']=get_user_name();
					$data['create_time']=time();
					
					$model = D('Message');
					
					$tmp = M('ProblemFeedback')->find($problem_feedback['id']);
					$data['receiver_id']=$tmp['create_user_id'];
					$data['receiver_name']=$tmp['create_user_name'];			
					$data['owner_id']=get_user_id();
				
					$list = $model -> add($data);
		
					$data['owner_id']=$tmp['create_user_id'];
					$list = $model -> add($data);
					$this -> _pushReturn("", "您有新的消息, 请注意查收", 1,$tmp['create_user_id']);
				}else{
					add_problem_feedback($problem_feedback['id'],$cc_this_backup);
				}
				
			}
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
	function export_daily_report(){
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
	function import_daily_report(){
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
				if($sheetData[1]['F']!='工作时间（起）hh:mm半小时为单位'){
					$this -> error('导入的excel模板不对:工作时间（起）hh:mm半小时为单位');
				}
				if($sheetData[1]['G']!='工作时间（止）hh:mm半小时为单位'){
					$this -> error('导入的excel模板不对:工作时间（止）hh:mm半小时为单位');
				}
				if($sheetData[1]['H']!='工作进度（进行中/已完成）'){
					$this -> error('导入的excel模板不对:工作进度（进行中/已完成）');
				}
				
				$model_daliy_report = M("DailyReport");
				$daliy_report = array();
				
				$ii=1;
				while ($sheetData[$ii*2]['A']==$ii){
					$ii++;
				}
				//$ii为今日序号+1
				$kk=1;
				while ($sheetData[$kk+$ii*2+7]['A']==$kk){
					$kk++;
				}
				$daliy_report['user_id'] = get_user_id();
				$daliy_report['user_name'] = get_user_name();
				$daliy_report['dept_id'] = get_dept_id();
				$daliy_report['dept_name'] = get_dept_name();
				$daliy_report['create_time'] = time();
				$daliy_report['content'] = $sheetData[$ii*2]['B'];
				$daliy_report['plan'] = $sheetData[$ii*2+$kk+7]['B'];
				$daliy_report['is_del'] = 0;
				$daliy_report['is_submit'] = 0;
				$daliy_report['score_1'] = $sheetData[$ii*2+3]['B'];
				$daliy_report['score_2'] = $sheetData[$ii*2+3]['C'];
				$daliy_report['score_3'] = $sheetData[$ii*2+3]['D'];
				$daliy_report['score_4'] = $sheetData[$ii*2+3]['E'];
				$daliy_report['score_5'] = $sheetData[$ii*2+3]['F'];
				$daliy_report['score_6'] = $sheetData[$ii*2+3]['G'];
				$daliy_report['score_7'] = $sheetData[$ii*2+3]['H'];
				$daliy_report['score_8'] = $sheetData[$ii*2+3]['I'];
				$daliy_report['score_total'] = $sheetData[$ii*2+3]['J'];
				$daliy_report['work_date'] = date('Y-m-d',time());;
				
				$pid = $model_daliy_report -> add($daliy_report);
				
				$model_daliy_report_detail = M("DailyReportDetail");
	
				$jj=$ii;
				for ($jj=1;$jj<$ii;$jj++){
					$data_detail = array();
					$data_detail['pid'] = $pid;
					$data_detail['type'] = 1;
					$data_detail['subject'] = $sheetData[$jj*2]['B'];
					$data_detail['item'] = $sheetData[$jj*2]['D'].'|||'.$sheetData[$jj*2+1]['D'];
					$start_time_1 = strlen($sheetData[$jj*2]['F'])==4?'0'.$sheetData[$jj*2]['F']:$sheetData[$jj*2]['F'];
					$start_time_2 = strlen($sheetData[$jj*2+1]['F'])==4?'0'.$sheetData[$jj*2+1]['F']:$sheetData[$jj*2+1]['F'];
					$data_detail['start_time'] = $start_time_1.'|||'.$start_time_2;
					$end_time_1 = strlen($sheetData[$jj*2]['G'])==4?'0'.$sheetData[$jj*2]['G']:$sheetData[$jj*2]['G'];
					$end_time_2 = strlen($sheetData[$jj*2+1]['G'])==4?'0'.$sheetData[$jj*2+1]['G']:$sheetData[$jj*2+1]['G'];
					$data_detail['end_time'] = $end_time_1.'|||'.$end_time_2;
					$status_1 = $sheetData[$jj*2]['H']=='进行中'?1:2;
					$status_2 = $sheetData[$jj*2+1]['H']=='进行中'?1:2;
					$data_detail['status'] = $status_1.'|||'.$status_2;
					$model_daliy_report_detail -> add($data_detail);
				}
				
				$mm=$kk;
				for ($ll=1;$ll<$mm;$ll++){
					$data_detail = array();
					$data_detail['pid'] = $pid;
					$data_detail['type'] = 2;
					$data_detail['subject'] = $sheetData[$ll+$ii*2+7]['B'];
					$data_detail['item'] = $sheetData[$ll+$ii*2+7]['D'];
					$data_detail['start_time'] = strlen($sheetData[$ll+$ii*2+7]['F'])==4?'0'.$sheetData[$ll+$ii*2+7]['F']:$sheetData[$ll+$ii*2+7]['F'];
					$data_detail['end_time'] = strlen($sheetData[$ll+$ii*2+7]['G'])==4?'0'.$sheetData[$ll+$ii*2+7]['G']:$sheetData[$ll+$ii*2+7]['G'];
					$data_detail['priority'] = $sheetData[$ll+$ii*2+7]['H'];
					
					$data_detail['is_need_help'] = $sheetData[$ll+$ii*2+7]['I']=='不需要协助'?0:1;
					$model_daliy_report_detail -> add($data_detail);
				}
				
				
				if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/" . $inputFileName)) {
					unlink($_SERVER["DOCUMENT_ROOT"] . "/" . $inputFileName);
				}
				$this -> assign('jumpUrl', U("daily_report/edit",array('id'=>$pid)));
				$this -> success('导入成功！');
			}
		} else {
			$this -> display();
		}
	}
	function json() {
		header("Cache-Control: no-cache, must-revalidate");
		header("Content-Type:text/html; charset=utf-8");
		$user_id = $_REQUEST["uid"];
		$start_date = $_REQUEST["start_date"];
		$end_date = $_REQUEST["end_date"];
	
		$where['user_id'] = $user_id;
		$where['is_del']=array('eq',0);
		$where['work_date'] = array( array('egt', $start_date), array('elt', $end_date));
		$list = M("DailyReport") -> where($where) -> order('work_date desc') -> select();
		exit(json_encode($list));
	}
}

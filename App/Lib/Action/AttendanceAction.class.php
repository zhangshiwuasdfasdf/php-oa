<?php
class AttendanceAction extends CommonAction {
	protected $config = array('app_type' => 'personal');
	
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_POST['eq_dept_name'])) {
			$map['dept_name'] = $_POST['eq_dept_name'];
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
		// 		$this -> assign('months', $months);
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign("map", serialize($map));
		$this -> display();
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
				
				$objPHPExcel -> getProperties() -> setCreator("小微OA") -> setLastModifiedBy("小微OA") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
		
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
				
				foreach ($res as $k=>$v){
					$i = $k + 2;
					$q = $q -> setCellValue("A".$i, $v['user_id']);
					$q = $q -> setCellValue("B".$i, $v['dept_name']);
					$q = $q -> setCellValue("C".$i, $v['user_name']);
					$q = $q -> setCellValue("D".$i, $v['num']);
					$q = $q -> setCellValue("E".$i, date('Y-m-d H:i:s',$v['attendance_time']));
					$q = $q -> setCellValue("F".$i, $v['machine_no']);
					$q = $q -> setCellValue("G".$i, $v['mark']=='in'?'签入':'签出');
				}
				
				$q ->getColumnDimension('A')->setWidth(20);
				$q ->getColumnDimension('B')->setWidth(20);
				$q ->getColumnDimension('C')->setWidth(20);
				$q ->getColumnDimension('D')->setWidth(20);
				$q ->getColumnDimension('E')->setWidth(20);
				$q ->getColumnDimension('F')->setWidth(20);
				$q ->getColumnDimension('G')->setWidth(20);
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
		$map = unserialize($_REQUEST['map']);
		$map_new = $map;
		unset($map_new['is_del']);
		if(empty($map_new)){
			$this->error('请先设置过滤条件，搜索，再导出！');
		}else{
			$res = M('AttendanceTable')->where($map)->order('month desc')->select();
			if(empty($res)){
				$this->error('搜索结果为空，无法导出！');
			}else{
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
					$q = $q -> setCellValue("A".$i, date('Y年m月',strtotime($v['month'])));
					$q = $q -> setCellValue("B".$i, $v['user_id']);
					$q = $q -> setCellValue("C".$i, $v['dept_name']);
					$q = $q -> setCellValue("D".$i, $v['duty']);
					$q = $q -> setCellValue("E".$i, $v['user_name']);
					$q = $q -> setCellValue("F".$i, $v['should_day']);
					$q = $q -> setCellValue("G".$i, $v['actually_day']);
					
					$q = $q -> setCellValue("H".$i, $v['late']);
					$q = $q -> setCellValue("I".$i, $v['supply_attendance']);
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
			}
		}
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
}
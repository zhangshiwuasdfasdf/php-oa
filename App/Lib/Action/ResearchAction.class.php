<?php
class ResearchAction extends CommonAction {
	function index(){
		$model = D('User_suggest');
		if (!empty($model)) {
			$this -> _list($model, $map);
		}
		$res = $model -> select();
		$dayNew = 0;$dayOld = 0;$monNew = 0;$monOld = 0;$wekNew = 0;$wekOld = 0;
		foreach ($res as $k => $v){
			$tmp = explode('|',$v['sustain']);
			if($tmp[0] == '1'){$dayNew ++; }elseif($tmp[0] == '0'){$dayOld++;}
			if($tmp[1] == '1'){$monNew ++; }elseif($tmp[1] == '0'){$monOld++;}
			if($tmp[2] == '1'){$wekNew ++; }elseif($tmp[2] == '0'){$wekOld++;}
		}
		$info = array('dn' => $dayNew,'do' => $dayOld , 'mn' => $monNew ,'mo' => $monOld ,'wn' => $wekNew , 'wo' => $wekOld);
		$this -> assign('res',$info);
		$this -> display();
	}
	
	function add(){
		$this -> display();
	}
	//上传图片
	function save_order(){
		$this -> upload();
	}
	
	Public function upload(){
	import("@.ORG.Util.UploadFile");
	$upload = new UploadFile();// 实例化上传类
	$upload->maxSize  = 3145728 ;// 设置附件上传大小
	$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
	$upload -> savePath = get_save_path() .'research/';// 设置附件上传目录
	$upload -> saveRule = "uniqid";
	if(!$upload->upload()) {// 上传错误提示错误信息
		$this->error($upload->getErrorMsg());
	}else{// 上传成功 获取上传文件信息
		$info =  $upload->getUploadFileInfo();
		$model = M("File");
		$suggest = M("User_suggest_detail");
		$maxNow = $suggest -> max('now');
		$fid['now'] = $maxNow + 1;
		foreach ($info as $k=>$v){
			$model -> create($info[$k]);
			$model -> savename = $v['savepath'] . $v['savename'];
			$model -> create_time = time();
			$model -> user_id = get_user_id();
			$model -> module = MODULE_NAME;
			$fid['fid'] = $model -> add();
			$fid['flag'] = $k + 1;
			$tmp = $suggest -> add($fid);
		}
		$this->success('数据保存成功！');
		}
	}
	
	//导出数据
	public function export_excel(){
		//导入thinkphp第三方类库
		Vendor('Excel.PHPExcel');
		
		$objPHPExcel = new PHPExcel();
		
		$objPHPExcel -> getProperties() -> setCreator("神洲酷奇OA") -> setLastModifiedBy("神洲酷奇OA") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
		// Add some data
		$q = $objPHPExcel -> setActiveSheetIndex(0);
		$objPHPExcel -> getActiveSheet()->mergeCells('A1:B1');
		$objPHPExcel -> getActiveSheet()->mergeCells('C1:D1');
		$objPHPExcel -> getActiveSheet()->mergeCells('E1:F1');
		$q -> getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置水平对齐方式
		$q -> getStyle('C1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置水平对齐方式
		$q -> getStyle('E1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置水平对齐方式
		header("Content-Type:text/html;charset=utf-8");
		$res = D('User_suggest') -> select();
		$dayNew = 0;$dayOld = 0;$monNew = 0;$monOld = 0;$wekNew = 0;$wekOld = 0;
		foreach ($res as $k => $v){
			$tmp = explode('|',$v['sustain']);
			if($tmp[0] == '1'){$dayNew ++; }elseif($tmp[0] == '0'){$dayOld++;}
			if($tmp[1] == '1'){$monNew ++; }elseif($tmp[1] == '0'){$monOld++;}
			if($tmp[2] == '1'){$wekNew ++; }elseif($tmp[2] == '0'){$wekOld++;}
		}
			$q = $q -> setCellValue('A1' , '日报');
			$q = $q -> setCellValue('C1' , '周报');
			$q = $q -> setCellValue('E1' , '月报');
			
			$q = $q -> setCellValue('A2' , '新版');
			$q = $q -> setCellValue('B2' , '旧版');
			$q = $q -> setCellValue('C2' , '新版');
			$q = $q -> setCellValue('D2' , '旧版');
			$q = $q -> setCellValue('E2' , '新版');
			$q = $q -> setCellValue('F2' , '旧版');
			
			$q = $q -> setCellValue('A3' , $dayNew);
			$q = $q -> setCellValue('B3' , $dayOld);
			$q = $q -> setCellValue('C3' , $monNew);
			$q = $q -> setCellValue('D3' , $monOld);
			$q = $q -> setCellValue('E3' , $wekNew);
			$q = $q -> setCellValue('F3' , $wekOld);
			
		// Rename worksheet
		$title = '日周月报新版问卷调查';
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
}
<?php
class SignedAction extends CommonAction {
	protected $config = array('app_type' => 'common', 'action_auth' => array('statistics' => 'read' , 'import_client' => 'read' ,'export_info' => 'read'));
	
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		$be = str_replace('-','',$_REQUEST['be_create']);$en = str_replace('-','',$_REQUEST['en_create']);
		if (!empty($be) && !empty($en)) {
			$map['months'] = array('between', array($be,$en));
		}elseif (!empty($be)) {
			$map['months'] = array('egt', $be);
		}elseif (!empty($en)) {
			$map['months'] = array('elt', $en);
		}
		if (!empty($_REQUEST['keyword']) && empty($map['64'])) {
			$map['user_name'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}
	//列表页
	function index (){
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$model = D('signed');
		if (!empty($model)) {
			$info = $this -> _list($model, $map);
			$this -> assign('info', $info);
		}
		$addr = $model -> where('is_del = 0') -> field('base as id,base as name') ->distinct(true) -> select();
		$user_name = $model -> where('is_del = 0') -> field('user_id as id,user_name as name') ->distinct(true) -> select();
		$this -> assign('addr_list', $addr);
		$this -> assign('user_name', $user_name);
		$widget['date'] = true;
		$this -> assign("widget", $widget);	
		$this -> display();	
	}
	//下载模板
	public function down() {
		$this -> _down();
	}
	//导入模板数据
	function import_client(){
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
				$dept_id = isHeadquarters(get_user_id());
				if( $dept_id > 0){$dept = M('dept')->find($dept_id);$yq_name = $dept['name'];}else{$yq_name = '总部';}
				//取得成功上传的文件信息
				$upload_list = $upload -> getUploadFileInfo();
				$file_info = $upload_list[0];
				//导入thinkphp第三方类库
				Vendor('Excel.PHPExcel');
				$inputFileName = $file_info['savepath'] . $file_info["savename"];
				$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
				$sd = $objPHPExcel -> getActiveSheet() -> toArray(null, true, true, true);//转行为数组格式
//				header("Content-Type:text/html;charset=utf-8");
				$a = $sd[1];
				$b = $sd[2];
				$c = $sd[3];
				$d = $sd[4];
				if($a['A'] != '截止目前时间进度' || $a['G'] != '考核开始日期' || $a['N'] != '考核截止日期' || $b['A'] != '招商任务完成进度' || $b['G'] != '截止目前累计签约数量' || $b['N'] != '年度目标签约数量' || $c['A'] != '签约时间' || $c['J'] != '合同编号'){
					if (file_exists($inputFileName)) {
						unlink($inputFileName);
					}
					$this -> error('模板格式错误，无法导入!',get_return_url());die;
				}
				if($d['A'] == '' || $d['B'] == '' || $d['D'] == '' || $d['J'] == ''){
					if (file_exists($inputFileName)) {
						unlink($inputFileName);
					}
					$this -> error('导入信息不全，无法导入!',get_return_url());die;
				}
				$date['user_id'] = get_user_id();
				$date['user_name'] = get_user_name();
				$date['create_time'] = time();
				$date['base'] = $yq_name;
				$date['current'] = $sd[1]['D'];
				$rqs = explode('-',trim($sd[1]['K']));
				$date['kh_start'] = '20'.$rqs[2].'-'.$rqs[0].'-'.$rqs[1];
				$rqs = explode('-',trim($sd[1]['Q']));
				$date['kh_off'] = '20'.$rqs[2].'-'.$rqs[0].'-'.$rqs[1];
				$date['schedule'] = $sd[2]['D'];
				$date['signed'] = $sd[2]['K'];
				$date['total_signed'] = $sd[2]['Q'];
				$date['months'] = date('Ymd');
				$pid = M('signed')->add($date);//添加主表数组
				if($pid){
					$x = 4;
					while ($sd[$x]['A'] != ''){$x++;}//确定一共有多少条数据
					$ad = M('signed_detail');
					for ($i=4;$i<$x;$i++){//循环取出每条数据
						$info['pid'] = $pid;
						$t = $sd[$i]['A']; //读取到的值
						$n = intval(($t - 25569) * 3600 * 24); //转换成1970年以来的秒数
						$info['riqi'] = gmdate('Y/m/d',$n);
						$info['manner'] = $sd[$i]['B'];
						$info['source'] = $sd[$i]['C'];
						$info['person'] = $sd[$i]['D'];
						$info['client'] = $sd[$i]['E'];
						$info['phone'] = $sd[$i]['F'];
						$info['shop_name'] = $sd[$i]['G'];
						$info['trade'] = $sd[$i]['H'];
						$info['receipt'] = $sd[$i]['I'];
						$info['contract_no'] = $sd[$i]['J'];
						$info['shop_num'] = $sd[$i]['K'];
						$info['checkin_time'] = $sd[$i]['L'];
						$info['checkca_time'] = $sd[$i]['M'];
						$info['storage_area'] = $sd[$i]['N'];
						$info['work_area'] = $sd[$i]['O'];
						$info['work_person'] = $sd[$i]['P'];
						$info['company'] = $sd[$i]['Q'];
						$info['baling_fee'] = $sd[$i]['R'];
						$info['pledge'] = $sd[$i]['S'];
						$info['remark'] = $sd[$i]['T'];
						$info['base'] = $yq_name;
						$info['months'] = gmdate('Ym',$n);
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
	//查看详情
	function read(){
		$pid = $_REQUEST['id'];
		$model = D('signed_detail');
		$map['pid'] = $pid;
		if (!empty($model)) {
			$info = $this -> _list($model, $map);
			$this -> assign('info', $info);
		}
		$data = M('Signed') -> find($pid);
		$this -> assign('data',$data);
		$this -> display();
	}
	
	function del(){
		$this -> _del();
	}
	
	//统计
	function statistics(){
		$pinfo = M('Signed') -> where('is_del = 0') -> field('id')->select();
		foreach ($pinfo as $k=>$v){
			if($v['id']){
				$arr[] = $v['id'];
			}
		}
		$map = $this -> _search('Signed_detail');
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$map['pid'] = array('in',$arr);
		$model = D('Signed_detail');
		//详细列表
		if (!empty($model)) {
			$info = $this -> _list($model, $map, 'riqi');
			$this -> assign('in', $info);
		}
		$sign = M('Signed');
		$id = $sign -> where('is_del = 0') ->max('id');
		$data = $sign -> find($id);
		$this -> assign('data',$data);
		$where2['pid'] = array('in',$arr);
		$addr = $model -> where($where2) -> field('base as id,base as name') ->distinct(true) -> select();
		$this -> assign('addr_list', $addr);
		$widget['date'] = true;
		$this -> assign("widget", $widget);	
		$this -> assign('post',$_POST);
		$this -> display();
	}
	
//导出
	public function export_info(){
		//导入thinkphp第三方类库
		Vendor('Excel.PHPExcel');
		
		$objPHPExcel = new PHPExcel();
		
		$objPHPExcel -> getProperties() -> setCreator("神洲酷奇OA") -> setLastModifiedBy("神洲酷奇OA") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
		// Add some data
		$q = $objPHPExcel -> setActiveSheetIndex(0);
		$tit = array('日期','招商方式','信息来源','招商人员','客户姓名','客户电话','店铺名','主营行业','日发单量','合同编号','经营店铺数量','实际入驻时间','实际入仓时间','仓储面积(㎡)','办公场地(㎡)','办公人数','快递公司','打包费/元','三项押金/元','备注	');
		$n = 0;
		for ($i=ord('A');$i<=ord('T');$i++){
			$q = $q -> setCellValue(chr($i).'1', $tit[$n]);
			$q->getColumnDimension(chr($i))->setWidth(15);
			$q->getColumnDimension('J')->setWidth(25);
			$q->getColumnDimension('T')->setWidth(30);
			$q -> getRowDimension(1)->setRowHeight(35);
			$q -> getStyle(chr($i).'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置水平对齐方式
			$q -> getStyle(chr($i).'1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//设置垂直居中
			$q -> getStyle(chr($i).'1')->getFill()->getStartColor()->setARGB('FF808080');
			$q -> getStyle(chr($i).'1')->getFont()->setName('微软雅黑');
			$q -> getStyle(chr($i).'1')->getFont()->setSize(11);
			$n++;	
		}
		
		$pinfo = M('Signed') -> where('is_del = 0') -> field('id')->select();
		foreach ($pinfo as $k=>$v){
			if($v['id']){
				$arr[] = $v['id'];
			}
		}
		$map = $this -> _search("Signed_detail");
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$map['pid'] = array('in',$arr);
		$list = M('Signed_detail') -> where($map) -> order('riqi DESC') -> select();
		foreach ($list as $k => $v){
			$i = $k+2;
			$q = $q -> setCellValue('A'.$i , $v['riqi']);
			$q = $q -> setCellValue('B'.$i , $v['manner']);
			$q = $q -> setCellValue('C'.$i , $v['source']);
			$q = $q -> setCellValue('D'.$i , $v['person']);
			$q = $q -> setCellValue('E'.$i , $v['client']);
			$q = $q -> setCellValue('F'.$i , $v['phone']);
			$q = $q -> setCellValue('G'.$i , $v['shop_name']);
			$q = $q -> setCellValue('H'.$i , $v['trade']);
			$q = $q -> setCellValue('I'.$i , $v['receipt']);
			$q = $q -> setCellValue('J'.$i , $v['contract_no']);
			$q = $q -> setCellValue('K'.$i , $v['shop_num']);
			$q = $q -> setCellValue('L'.$i , $v['checkin_time']);
			$q = $q -> setCellValue('M'.$i , $v['checkca_time']);
			$q = $q -> setCellValue('N'.$i , $v['storage_area']);
			$q = $q -> setCellValue('O'.$i , $v['work_area']);
			$q = $q -> setCellValue('P'.$i , $v['work_person']);
			$q = $q -> setCellValue('Q'.$i , $v['company']);
			$q = $q -> setCellValue('R'.$i , $v['baling_fee']);
			$q = $q -> setCellValue('S'.$i , $v['pledge']);
			$q = $q -> setCellValue('T'.$i , $v['remark']);
			for ($j=ord('A');$j<=ord('T');$j++){
				$q -> getStyle()->getFont(ord($j).$i)->setName('微软雅黑');
				$q -> getStyle()->getFont(ord($j).$i)->setSize(11);
				$q -> getStyle(chr($j).$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置水平对齐方式
			}
		}
			
		// Rename worksheet
		$title = '已签约客户明细年进度表导出';
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
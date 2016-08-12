<?php
class AttractAction extends CommonAction {
	protected $config = array('app_type' => 'personal');
	
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
		$model = D('Attract');
		if (!empty($model)) {
			$info = $this -> _list($model, $map);
			$this -> assign('info', $info);
		}
		$addr = $model -> where('is_del = 0') -> field('base as id,base as name') ->distinct(true) -> select();
		$user_name = $model -> where('is_del = 0') -> field('user_id as id,user_name as name') ->distinct(true) -> select();
		$months = $model -> where('is_del = 0') -> field('months as id,months as name') ->distinct(true) -> select();
		$this -> assign('addr_list', $addr);
		$this -> assign('user_name', $user_name);
		$this -> assign('months', $months);
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
				/*header("Content-Type:text/html;charset=utf-8");
				dump($sd);die;*/
				//随机判断模板格式
				$a = $sd[1];
				$b = $sd[2];
				$c = $sd[3];
				if($a['A'] != '今天是' || $a['C'] != '考核截止日期' || $a['E'] != '本次考核周期天数' || $a['G'] != '本月签约目标' || $a['I'] != '本月累计签约' || $b['A'] != '日期' || $b['D'] != '招商人员' || $b['I'] != '客户意向'){
					if (file_exists($inputFileName)) {
						unlink($inputFileName);
					}
					$this -> error('模板格式错误，无法导入!',get_return_url());die;
				}
				if($c['A'] == '' || $c['B'] == '' || $c['D'] == '' || $c['I'] == ''){
					if (file_exists($inputFileName)) {
						unlink($inputFileName);
					}
					$this -> error('导入信息不全，无法导入!',get_return_url());die;
				}
				$date['user_id'] = get_user_id();
				$date['user_name'] = get_user_name();
				$date['create_time'] = time();
				$rq = explode('-',trim($sd[1]['B']));
				$date['today'] = '20'.$rq[2].'/'.$rq[0].'/'.$rq[1];
				$rq2 = explode('-',trim($sd[1]['D']));
				$date['end_time'] = '20'.$rq2[2].'/'.$rq2[0].'/'.$rq2[1];
				$date['days'] = $sd[1]['F'];
				$date['target'] = $sd[1]['H'];
				$date['actuality'] = $sd[1]['J'];
				$date['base'] = $yq_name;
				$date['months'] = '20'.$rq[2].$rq[0];
				$pid = M('attract')->add($date);//添加主表数组
				if($pid){
					$x = 3;
					while ($sd[$x]['A'] != ''){$x++;}//确定一共有多少条数据
					$ad = M('attract_detail');
					for ($i=3;$i<$x;$i++){//循环取出每条数据
						$info['pid'] = $pid;
						$rqs = explode('-',trim($sd[$i]['A']));
						$info['riqi'] = '20'.$rqs[2].'/'.$rqs[0].'/'.$rqs[1];
						$info['manner'] = $sd[$i]['B'];
						$info['source'] = $sd[$i]['C'];
						$info['person'] = $sd[$i]['D'];
						$info['client'] = $sd[$i]['E'];
						$info['phone'] = $sd[$i]['F'];
						$info['trade'] = $sd[$i]['G'];
						$info['receipt'] = $sd[$i]['H'];
						$info['intention'] = $sd[$i]['I'];
						$info['base'] = $yq_name;
						$info['months'] = '20'.$rqs[2].$rqs[0];
						$info['remarks'] = $sd[$i]['J'];
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
		$this -> _del();
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
		if(!empty($map['months'][1][0])){
			$mid = $map['months'][1][0];
			$mid = implode('/',explode('-',$mid));
			$prefix = substr($mid, 0, 7);
			$where3['end_time'] = array('like', $prefix .'%');
			$where3['is_del'] = 0;
			$data = $attr -> where($where3) -> find();
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
		$tit = array('日期','招商方式','信息来源','招商人员','客户姓名','客户电话','主营行业','预计日发单量','客户意向','接洽内容&备注');
		$n = 0;
		for ($i=ord('A');$i<=ord('J');$i++){
			$q = $q -> setCellValue(chr($i).'1', $tit[$n]);
			$q->getColumnDimension(chr($i))->setWidth(15);
			$q->getColumnDimension('J')->setWidth(30);
			$q -> getRowDimension(1)->setRowHeight(35);
			$q -> getStyle(chr($i).'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置水平对齐方式
			$q -> getStyle(chr($i).'1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//设置垂直居中
			$q -> getStyle(chr($i).'1')->getFill()->getStartColor()->setARGB('FF808080');
			$q -> getStyle(chr($i).'1')->getFont()->setName('微软雅黑');
			$q -> getStyle(chr($i).'1')->getFont()->setSize(11);
			$n++;	
		}
		
		$pinfo = M('Attract') -> where('is_del = 0') -> field('id')->select();
		foreach ($pinfo as $k=>$v){
			if($v['id']){
				$arr[] = $v['id'];
			}
		}
		$map = $this -> _search("Attract_detail");
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$map['pid'] = array('in',$arr);
		$list = M('Attract_detail') -> where($map) -> order('riqi DESC') -> select();
		foreach ($list as $k => $v){
			$i = $k+2;
			$q = $q -> setCellValue('A'.$i , $v['riqi']);
			$q = $q -> setCellValue('B'.$i , $v['manner']);
			$q = $q -> setCellValue('C'.$i , $v['source']);
			$q = $q -> setCellValue('D'.$i , $v['person']);
			$q = $q -> setCellValue('E'.$i , $v['client']);
			$q = $q -> setCellValue('F'.$i , $v['phone']);
			$q = $q -> setCellValue('G'.$i , $v['trade']);
			$q = $q -> setCellValue('H'.$i , $v['receipt']);
			$q = $q -> setCellValue('I'.$i , $v['intention']);
			$q = $q -> setCellValue('J'.$i , $v['remarks']);
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
}
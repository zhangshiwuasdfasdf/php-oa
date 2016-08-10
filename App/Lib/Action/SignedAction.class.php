<?php
class SignedAction extends CommonAction {
	protected $config = array('app_type' => 'personal');
	
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_POST['be_create']) && !empty($_POST['en_create'])) {
			$map['months'] = array('between', array($_POST['be_create'],$_POST['en_create']));
		}elseif (!empty($_POST['be_create'])) {
			$map['months'] = array('egt', $_POST['be_create']);
		}elseif (!empty($_POST['en_create'])) {
			$map['months'] = array('elt', $_POST['en_create']);
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
				if($a['A'] != '截止目前时间进度' || $a['G'] != '考核开始日期' || $a['N'] != '考核截止日期' || $b['A'] != '招商任务完成进度' || $b['G'] != '考核开始日期' || $b['N'] != '年度目标签约数量' || $c['A'] != '签约时间' || $c['J'] != '合同编号'){
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
				$date['months'] = date('Y-m-d');
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
						$info['months'] = gmdate('Y-m-d',$n);
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
		$this -> display();
	}
	

}
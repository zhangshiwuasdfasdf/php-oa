<?php
class VisitorAction extends CommonAction {
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
	function index(){
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$model = D('Visitor');
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
				if($a['A'] != '到访日期' || $a['B'] != '招商方式' || $a['C'] != '信息来源' || $a['D'] != '招商人员' || $a['E'] != '客户姓名' || $a['G'] != '主营行业' || $a['I'] != '预计日发单量' || $a['K'] != '客户最关心的问题'){
					if (file_exists($inputFileName)) {
						unlink($inputFileName);
					}
					$this -> error('模板格式错误，无法导入!',get_return_url());die;
				}
				if($b['A'] == '' || $b['B'] == '' || $b['D'] == '' || $b['I'] == ''){
					if (file_exists($inputFileName)) {
						unlink($inputFileName);
					}
					$this -> error('导入信息不全，无法导入!',get_return_url());die;
				}
				$date['user_id'] = get_user_id();
				$date['user_name'] = get_user_name();
				$date['create_time'] = date("Y-m-d H:i:s");
				$dept_id = isHeadquarters(get_user_id());
				if( $dept_id > 0){$dept = M('dept')->find($dept_id);$yq_name = $dept['name'];}else{$yq_name = '总部';}
				$date['base'] = $yq_name;
				$date['months'] = date("Y-m-d");
				$pid = M('visitor')->add($date);//添加主表数组
				if($pid){
					$x = 2;
					while ($sd[$x]['A'] != ''){$x++;}//确定一共有多少条数据
					$ad = M('visitor_detail');
					for ($i=2;$i<$x;$i++){//循环取出每条数据
						$info['pid'] = $pid;
						$rq = explode('-',trim($sd[$i]['A']));
						$info['riqi'] = '20'.$rq[2].'/'.$rq[0].'/'.$rq[1];
						$info['manner'] = $sd[$i]['B'];
						$info['source'] = $sd[$i]['C'];
						$info['person'] = $sd[$i]['D'];
						$info['client'] = $sd[$i]['E'];
						$info['phone'] = $sd[$i]['F'];
						$info['trade'] = $sd[$i]['G'];
						$info['cost'] = $sd[$i]['H'];
						$info['receipt'] = $sd[$i]['I'];
						$info['singed_date'] = $sd[$i]['J'];
						$info['concern'] = $sd[$i]['K'];
						$info['remarks'] = $sd[$i]['L'];
						$info['base'] = $yq_name;
						$info['months'] = '20'.$rq[2].'-'.$rq[0].'-'.$rq[1];
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
		$model = D('visitor_detail');
		$map['pid'] = $pid;
		if (!empty($model)) {
			$info = $this -> _list($model, $map);
			$this -> assign('info', $info);
		}
		$this -> display();
	}
	
	function del(){
		$this -> _del();
	}
	
	function _search_filter2(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['keyword']) && empty($map['64'])) {
			$map['person'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}
	//统计
	function statistics(){
		$pinfo = M('Visitor') -> where('is_del = 0') -> field('id')->select();
		foreach ($pinfo as $k=>$v){
			if($v['id']){
				$arr[] = $v['id'];
			}
		}
		$map = $this -> _search('Visitor_detail');
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$map['pid'] = array('in',$arr);
		$model = D('Visitor_detail');
		//详细列表
		if (!empty($model)) {
			$info = $this -> _list($model, $map,'riqi');
			$this -> assign('info', $info);
		}
		//今天是
		$where2['pid'] = array('in',$arr);
		$addr = $model -> where($where2) -> field('base as id,base as name') ->distinct(true) -> select();
		$this -> assign('addr_list', $addr);
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> display();
	}
}
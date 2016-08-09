<?php
class AttractAction extends CommonAction {
	protected $config = array('app_type' => 'personal');
	
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
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
		$addr = $model -> field('base as id,base as name') ->distinct(true) -> select();
		$months = $model -> field('months as id,months as name') ->distinct(true) -> select();
		$user_name = $model -> field('user_id as id,user_name as name') ->distinct(true) -> select();
		$this -> assign('addr_list', $addr);
		$this -> assign('months', $months);
		$this -> assign('user_name', $user_name);
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
				$date['user_id'] = get_user_id();
				$date['user_name'] = get_user_name();
				$date['create_time'] = time();
				$rq = explode('-',trim($sd[1]['B']));
				$date['today'] = '20'.$rq[2].'/'.$rq[0].'/'.$rq[1];
				$rq = explode('-',trim($sd[1]['D']));
				$date['end_time'] = '20'.$rq[2].'/'.$rq[0].'/'.$rq[1];
				$date['days'] = $sd[1]['F'];
				$date['target'] = $sd[1]['H'];
				$date['actuality'] = $sd[1]['J'];
				$date['base'] = $yq_name;
				$date['months'] = '20'.$rq[2].'/'.$rq[0];
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
						$info['months'] = '20'.$rqs[2].'-'.$rqs[0];
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
	
	function _search_filter2(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['keyword']) && empty($map['64'])) {
			$map['person'] = array('like', "%" . $_POST['keyword'] . "%");
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
		if (method_exists($this, '_search_filter2')) {
			$this -> _search_filter2($map);
		}
		$map['pid'] = array('in',$arr);
		$model = D('Attract_detail');
		//详细列表
		if (!empty($model)) {
			$info = $this -> _list($model, $map);
			$this -> assign('info', $info);
		}
		//今天是
		$attr = M('attract');
		$where['is_del'] = '0';
		$where['today'] = date("Y/m/d");
		$data = $attr -> where($where)->find();
		$addr = $model -> field('base as id,base as name') ->distinct(true) -> select();
		$months = $model -> field('months as id,months as name') ->distinct(true) -> select();
		$this -> assign('data',$data);
		$this -> assign('addr_list', $addr);
		$this -> assign('months', $months);
		$this -> display();
	}
	

}
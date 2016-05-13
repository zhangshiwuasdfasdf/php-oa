<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class CrmAction extends CommonAction {
	protected $config = array('app_type' => 'common', 'action_auth' => array('review' => 'admin','submit'=>'read','search'=>'read','approve'=>'admin','reject'=>'admin','opreate'=>'write'));

	//过滤查询字段
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_POST['keyword'])) {
			$keyword = $_POST['keyword'];
			$where['name'] = array('like', "%" . $keyword . "%");
			$where['mobile_tel'] = array('like', "%" . $keyword . "%");
			$where['district'] = array('like', "%" . $keyword . "%");
			$where['need'] = array('like', "%" . $keyword . "%");
			$where['source'] = array('like', "%" . $keyword . "%");
			$where['_logic'] = 'or';
			$map['_complex'] = $where;
		}
	}
	
	function search() {
		$this -> assign('auth',$this -> config['auth']);
		$widget['date'] = true;
		$this -> assign("widget", $widget);
				
		$model = M("Crm");
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$map['status']=1;
		if (!empty($model)) {
			$this -> _list($model,$map);
		}

		$this -> display();
	}
	
	function review() {
		$this -> assign('auth',$this -> config['auth']);
		$model = M("Crm");
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$map['status']=0;
		if(!empty($model)){
			$this -> _list($model,$map);
		}
		
		$this -> display();
	}

	function submit() {
		$model = M("Crm");
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$map['user_id']=get_user_id();
		if (!empty($model)){
			$this -> _list($model,$map);
		}		
		$this -> assign('auth', $this -> config['auth']);
		$this -> display();
	}
	
	function index() {
		$model = M("Crm");
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		if (!empty($model)) {
			$this -> _list($model,$map,'area,active_shop,vip_type,name');
		}
		$this -> display();
	}

	function del(){
		$id = $_POST['id'];
		$count = $this ->_del($id,null,true);

		if ($count !== false) {//保存成功
			$this -> assign('jumpUrl', get_return_url());
			$this -> success("成功删除{$count}条!");
		} else {
			//失败提示
			$this -> error('删除失败!');
		}
	}

	function add(){
		$widget['date'] = true;		
		$this -> assign("widget", $widget);
		$this->display();
	}

	function read(){
		$auth=$this -> config['auth'];
		$this -> assign('auth',$auth);
		
		$id=$_REQUEST['id'];
		
		$model=M("Crm");				
		$where['id']=$id;
		$vo = $model ->where($where)->find();
		$this -> assign('vo', $vo);
		
		if($auth['admin']){
			$this->assign('show_edit',1);
		}		
		if($auth['write']&&($vo['user_id']==get_user_id())){
			$this->assign('show_edit',1);
		}
						
		$this->assign('show_confirm',!$vo['status']&&$auth['admin']);
		$this->assign('show_opreate',$auth['write']);
			
		$model = M("CrmLog");
		$where=array();
		$where['crm_id'] = $id;

		$crm_log = $model -> where($where) ->order("id")-> select();
		$this -> assign("crm_log", $crm_log);

		$this -> display();
	}
	
	function edit(){		
		$auth=$this -> config['auth'];		
		
		$id=$_REQUEST['id'];
		
		$model=M("Crm");				
		$where['id']=$id;
		
		if($auth['admin']){
			
		}elseif($auth['write']){
			$where['user_id']=get_user_id();			
		}else{
			$this->error("没有权限");
		}
		$vo = $model ->where($where)->find();		
		if(empty($vo)){
			$this->error("没有数据");
		}		

		$this -> assign('vo', $vo);
		$this->display();
	}
	
	public function opreate() {

		$model = D("CrmLog");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		
		$model -> emp_no = get_emp_no();
		//保存当前数据对象
		$crm_id=$model->crm_id;
		$list = $model -> add();
		
		if ($list !== false) {//保存成功			
			$this -> assign('jumpUrl',U('crm/review'));
			$this -> success('操作成功!');
		} else {
			//失败提示
			$this -> error('操作失败!');
		}
	}
	
	public function approve() {

		$model = D("CrmLog");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		
		$model -> emp_no = get_emp_no();
		//保存当前数据对象
		$crm_id=$model->crm_id;
		$list = $model -> add();
		
		if ($list !== false) {//保存成功
			M("Crm") ->where("id=$crm_id")-> setField('status',1);
			$this -> assign('jumpUrl',U('crm/review'));
			$this -> success('操作成功!');
		} else {
			//失败提示
			$this -> error('操作失败!');
		}
	}

	public function reject() {
		$model = D("CrmLog");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$crm_id=$model->crm_id;
		$model -> emp_no = get_emp_no();
		//保存当前数据对象
		$list = $model -> add();
		
		if ($list !== false) {//保存成功
			M("Crm") ->where("id=$crm_id")-> setField('status',2);
			$this -> assign('jumpUrl',U('crm/review'));
			$this -> success('操作成功!');
		} else {
			//失败提示
			$this -> error('操作失败!');
		}
	}
	
	protected function _insert(){
		$model = D('Crm');
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}

		$model->need=implode(",",$model->need);
		//保存当前数据对象
		$list = $model -> add();
		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('新增成功!');
		} else {
			//失败提示
			$this -> error('新增失败!');
		}
	}

	protected function _update() {

		$widget['date'] = true;		
		$this -> assign("widget", $widget);

		$id = $_POST['id'];
		$model = D("Crm");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$model->need=implode(",",$model->need);

		// 更新数据
		$list = $model -> save();
		if (false !== $list) {
			//成功提示
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('编辑成功!');
		} else {
			//错误提示
			$this -> error('编辑失败!');
		}
	}

	function search_export() {
		$model = M("Vip");
		$where['is_del']=0;
		$list = $model -> where($where) -> select();

		Vendor('Excel.PHPExcel');
		//导入thinkphp第三方类库

		$inputFileName = "Public/templete/Vip.xlsx";
		$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);

		$objPHPExcel -> getProperties() -> setCreator("smeoa") -> setLastModifiedBy("smeoa") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
		// Add some data
		$i = 1;
		//dump($list);
		foreach ($list as $val) {
			$i++;
			$objPHPExcel -> setActiveSheetIndex(0) -> setCellValue("A$i", $val["name"]) -> setCellValue("B$i", $val["short"]) -> setCellValue("C$i", $val["biz_license"]) -> setCellValue("D$i", $val["payment"]) -> setCellValue("E$i", $val["address"]) -> setCellValue("F$i", $val["salesman"]) -> setCellValue("G$i", $val["contact"]) -> setCellValue("H$i", $val["email"]) -> setCellValue("I$i", $val["office_tel"]) -> setCellValue("J$i", $val["mobile_tel"]) -> setCellValue("J$i", $val["fax"]) -> setCellValue("L$i", $val["im"]) -> setCellValue("M$i", $val["remark"]);
		}
		// Rename worksheet
		$objPHPExcel -> getActiveSheet() -> setTitle('Vip');

		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel -> setActiveSheetIndex(0);
	
		$file_name="Vip.xlsx";
		// Redirect output to a client’s web browser (Excel2007)
		header("Content-Type: application/force-download");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition:attachment;filename =" . str_ireplace('+', '%20', URLEncode($file_name)));
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter -> save('php://output');
		exit ;
	}
}
?>
<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class FlowNodeAction extends CommonAction {
    protected $config=array('app_type'=>'master');

	//过滤查询字段
	function _search_filter(&$map) {
		if (!empty($_POST['keyword'])){
			$map['name'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}
	function add(){
		$model = M('FlowVersion');
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$model->status = '0';
		$model->create_time = time();
		$id = $model -> add();
		if(false != $id){
			$this -> success('新增成功!');
		}else{
			$this -> error('新增失败!');
		}
	}
	
	function index(){
		$model = D("FlowNodeView");
		$map = $this -> _search();
		$map['flow_version_id'] = $_GET['flow_version_id'];
		$map['is_del'] = '0';
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$this -> _list($model, $map);
		$flow_version = M('FlowVersion')->find($_GET['flow_version_id']);
		$this -> assign('version', $flow_version['version']);
		
		$flow_type_setting = M('FlowTypeSetting')->find($flow_version['flow_type_setting_id']);
		$this -> assign('flow_name', $flow_type_setting['flow_name']);
		
		$fields = M($flow_type_setting['table_name'])->getDbFields();
		$this -> assign('fields', $fields);

		cookie('return_url',U('index?flow_version_id='.$_GET['flow_version_id']));
		$this -> display();
	}
	function del(){
		if(!empty($_REQUEST['id']) && is_array($_REQUEST['id'])){
			$res = M('FlowNode')->where(array('id'=>array('in',$_REQUEST['id'])))->save(array('is_del'=>'1'));
			if(false != $res){
				$this->ajaxReturn('1','删除成功','1');
			}else{
				$this->ajaxReturn('1','删除失败','0');
			}
		}
	}
}
?>
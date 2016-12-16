<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class FlowVersionAction extends CommonAction {
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
		$model = D("FlowVersionView");
		$map = $this -> _search();
		$map['flow_type_setting_id'] = $_GET['flow_type_setting_id'];
		$map['is_del'] = '0';
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$this -> _list($model, $map);
// 		$s = $model->where($map)->select();
// 		dump($s);die;
		$flow_name = M('FlowTypeSetting')->where(array('id'=>$_GET['flow_type_setting_id']))->getField('flow_name');
		$this -> assign('flow_name', $flow_name);

		cookie('return_url',U('index?flow_type_setting_id='.$_GET['flow_type_setting_id']));
		$this -> display();
	}
	function del(){
		if(!empty($_REQUEST['id']) && is_array($_REQUEST['id'])){
			$res = M('FlowVersion')->where(array('id'=>array('in',$_REQUEST['id'])))->save(array('is_del'=>'1'));
			if(false != $res){
				$this->ajaxReturn('1','删除成功','1');
			}else{
				$this->ajaxReturn('1','删除失败','0');
			}
		}
	}
	function set_default(){
		if(!empty($_REQUEST['id']) && !empty($_REQUEST['flow_type_setting_id'])){
			$res = M('FlowVersion')->where(array('id'=>array('eq',I('id'))))->save(array('status'=>'1'));
			$res2 = M('FlowVersion')->where(array('id'=>array('neq',I('id')),'flow_type_setting_id'=>array('eq',I('flow_type_setting_id'))))->save(array('status'=>'0'));
			if(false !== $res && false !== $res2){
				$this->ajaxReturn('1','设置成功','1');
			}else{
				$this->ajaxReturn('1','设置失败','0');
			}
		}
	}
}
?>
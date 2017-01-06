<?php
class ApprovalFlowAction extends CommonAction {
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_POST['li_module_name'])) {
			$map['flow_name'] = array('like','%'.$_POST['li_module_name'].'%');
		}
}

	function index(){
		$model = M('FlowTypeSetting');
		$map = $map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		
		if (!empty($model)) {
			$info = $this -> _list($model, $map);
		}
		$this->display();
	}
	
	function del(){
		$id = $_REQUEST['id'];
		$where['id'] = array('in', $id);
		$result = M("FlowTypeSetting")->where($where)->save(array('is_del'=>'1'));
			if ($result) {
				$this -> ajaxReturn('', "删除成功", 1);
			} else {
				$this -> ajaxReturn('', "删除失败", 0);
			}
	}
	
}
?>
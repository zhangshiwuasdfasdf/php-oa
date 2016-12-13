<?php
class ApprovalFlowAction extends CommonAction {
	function _search_filter(&$map) {
		if (!empty($_POST['li_module_name'])) {
			$map['name'] = array('eq',$_POST['li_module_name']);
		}
		
 		//dump($map);die;
}

	function index(){
		$model = M('FlowType');
		$map = $map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		
		if (!empty($model)) {
			$info = $this -> _list($model, $map);
		}
		
		$this->display();
	}
	
	
	
}
?>
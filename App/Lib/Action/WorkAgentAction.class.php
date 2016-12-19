<?php
class WorkAgentAction extends CommonAction {
	/*function _search_filter(&$map) {
		$fid=$_REQUEST['fid'];
		$map['is_del'] = array('eq','0');
		$map['fid'] = array('eq',$fid);
		if (!empty($_POST['li_company_id'])) {
			$map['company_id'] = array('eq',$_POST['li_company_id']);
		}
		if (!empty($_POST['li_version'])) {
			$map['version'] = array('eq',$_POST['li_version']);
		}
 		//dump($map);die;
}*/

	function index(){
		
		$this->display();
	}
	
//工作委托


}
?>
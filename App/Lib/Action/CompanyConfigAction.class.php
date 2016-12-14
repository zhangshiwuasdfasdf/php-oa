<?php
class CompanyConfigAction extends CommonAction {
/*	function _search_filter(&$map) {
		if (!empty($_POST['li_module_name'])) {
			$map['module_name'] = array('eq',$_POST['li_module_name']);
		}
		if (!empty($_POST['li_flow_name'])) {
			$map['flow_name'] = array('like','%'.$_POST['li_flow_name'].'%');
		}
		if (!empty($_POST['li_table_name'])) {
			$map['table_name'] = array('like','%'.$_POST['li_table_name'].'%');
		}
 		//dump($map);die;
}*/

	function index(){
		/*$map = $map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}*/
		$model = M('CompanyConfig');
		if (!empty($model)) {
			$info = $this -> _list($model, $map);
		}
		foreach($info as $k=>$v){
			$id=$v['company_id'];
			$com_name=M("Dept")->where("id=$id")->getField("name");
			$info[$k]["company"]=$com_name;
		}
		$fid=$_REQUEST['fid'];
		$flow_name=M("FlowType")->where("id=$fid")->getField("name");
		$company=M("Dept")->where("pid=0")->field("name,id")->select();
		$this->assign("info",$info);
		$this->assign("company",$company);
		$this->assign("flow_name",$flow_name);
		$this->display();
	}
	
	function add(){
		if(IS_POST)
    	{
    		$model = M('CompanyConfig');
    		if($model->create(I('post.'), 1))
    		{
    			if($id = $model->add())
    			{
    				$this->success('添加成功！', U('index'));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}

	}
	
}
?>
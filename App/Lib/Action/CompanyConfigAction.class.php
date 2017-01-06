<?php
class CompanyConfigAction extends CommonAction {
	function _search_filter(&$map) {
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
}

	function index(){
		$fid=$_REQUEST['fid'];
		$map = $map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$model = M('CompanyConfig');
		if (!empty($model)) {
			//$info = $this -> _list($model, $map);
			$info = $model->where($map)->order("id desc")->select();
		}
		foreach($info as $k=>$v){
			$id=$v['company_id'];
			$com_name=M("Dept")->where("id=$id")->getField("name");
			$info[$k]["company"]=$com_name;
		}
		$flow_name=M("FlowTypeSetting")->where("id=$fid")->getField("flow_name");
		$company=M("Dept")->where("pid=0")->field("name,id")->select();
		$this->assign("fid",$fid);
		$this->assign('type',1);//公司通用
		$this->assign("info",$info);
		$this->assign("company",$company);
		$this->assign("flow_name",$flow_name);
		$this->display();
	}
	
	function add(){
		$fid=$_POST['fid'];
		if(IS_POST)
    	{
    		$model = M('CompanyConfig');
    		if($model->create(I('post.'), 1))
    		{
    			if($id = $model->add())
    			{
    				$this->success('添加成功！', U('index?fid='.$fid));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}

	}
	
	function edit(){
		$id = $_REQUEST['id'];
		$where = array('id'=>$id);
		$model = M('CompanyConfig');
		if(IS_POST)
    	{
    		if($model->create(I('post.'), 2))
    		{
    			$res=$model->where($where)->save();
    		}
    	}
		if($res){
			$this->ajaxReturn(1,1,0);
		}else{
			$this->ajaxReturn(null,null,0);
		}
	}
	
	function update_version(){
		$id = $_POST['id'];
		$company_id = $_POST['company_id'];
		$data=M("CompanyConfig")->where(array('id'=>$id))->setField('version','当前');
		$data=M("CompanyConfig")->where("id != $id and company_id = $company_id")->setField('version','历史');
		exit(json_encode($data));
	}
	function del(){
		$id = $_REQUEST['id'];
		$where['id'] = array('in', $id);
		$result = M("CompanyConfig")->where($where)->save(array('is_del'=>'1'));
			if ($result) {
				$this -> ajaxReturn('', "删除成功", 1);
			} else {
				$this -> ajaxReturn('', "删除失败", 0);
			}
	}
}
?>
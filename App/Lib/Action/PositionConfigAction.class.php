<?php
class PositionConfigAction extends CommonAction {
	function _search_filter(&$map) {
		$fid=$_REQUEST['fid'];
		$map['is_del'] = array('eq','0');
		$map['fid'] = array('eq',$fid);
		if (!empty($_POST['li_company_id'])) {
			$map['company_id'] = array('eq',$_POST['li_company_id']);
		}
		if (!empty($_POST['li_dept_id'])) {
			$map['dept_id'] = array('eq',$_POST['li_dept_id']);
		}
		if (!empty($_POST['li_pos_id'])) {
			$map['pos_id'] = array('eq',$_POST['li_pos_id']);
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
		$model = M('PositionConfig');
		if (!empty($model)) {
			$info = $model->where($map)->order("id desc")->select();
		}
		foreach($info as $k=>$v){
			$id=$v['company_id'];
			$dept_id=$v['dept_id'];
			$pos_id=$v['pos_id'];
			$com_name=M("Dept")->where("id=$id")->getField("name");
			$dept_name=M("Dept")->where("id=$dept_id")->getField("name");
			$position=D("DeptPositionView")->where("position_id=$pos_id")->getField("position_name");
			$info[$k]["company"]=$com_name;
			$info[$k]["dept"]=$dept_name;
			$info[$k]["position"]=$position;
		}
		$flow_name=M("FlowTypeSetting")->where("id=$fid")->getField("flow_name");
		$company=M("Dept")->where("pid=0")->field("name,id")->select();
		$this->assign("fid",$fid);
		$this->assign("info",$info);
		$this->assign("company",$company);
		$this->assign("flow_name",$flow_name);
		$this->display();
	}
	
	function add(){
		$fid=$_POST['fid'];
		if(IS_POST)
    	{
    		$model = M('PositionConfig');
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
		$model = M('PositionConfig');
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
		$pos_id = $_POST['pos_id'];
		$data=M("PositionConfig")->where(array('id'=>$id))->setField('version','当前');
		$data=M("PositionConfig")->where("id != $id and pos_id=$pos_id")->setField('version','历史');
		exit(json_encode($data));
	}
	function del(){
		$id = $_REQUEST['id'];
		$where['id'] = array('in', $id);
		$result = M("PositionConfig")->where($where)->save(array('is_del'=>'1'));
			if ($result) {
				$this -> ajaxReturn('', "删除成功", 1);
			} else {
				$this -> ajaxReturn('', "删除失败", 0);
			}
	}
	function ajax_get_dept(){
		$pid=$_POST['pid'];
		$dept_ids=get_child_dept_all($pid);
		$key=array_search($pid,$dept_ids);
		foreach ($dept_ids as $dept_id){
			if($dept_id == $pid){
				array_splice($dept_ids, $key, 1);
			}
		}
		$html = "";
		$html .= "<option value=\"\">请选择</option>\r\n";
		foreach ($dept_ids as $dept_id){
			$res = M('Dept')->field('id,name')->where(array('id'=>$dept_id,'is_del'=>0,'is_use'=>1))->find();
			if($res){
				$id = $res['id'];
				$name = $res['name'];
				$html .= "<option value=\"$id\">$name</option>\r\n";
			}
		}
		$this -> ajaxReturn($html);
	}
	
	function ajax_get_pos(){
		$dep_id=$_POST['dep_id'];
		//$dept_ids=get_child_dept_all($dep_id);
		$res = M('RDeptPosition')->field('position_id')->where(array('dept_id'=>$dep_id))->Distinct(true)->select();
		$html = "";
		$html .= "<option value=\"\">请选择</option>\r\n";
		foreach ($res as $v){
			$pos_id=$v['position_id'];
			$name=M("Position")->where(array('id'=>$pos_id,'is_del'=>0,'is_use'=>1))->getField("position_name");
			if($name){
				$html .= "<option value=\"$pos_id\">$name</option>\r\n";
			}
		}
		$this -> ajaxReturn($html);
	}
}
?>
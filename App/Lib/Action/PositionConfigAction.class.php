<?php
class PositionConfigAction extends CommonAction {
	function _search_filter(&$map) {
		$fid=$_REQUEST['fid'];
		$map['is_del'] = array('eq','0');
		$map['fid'] = array('eq',$fid);
		if (!empty($_POST['eq_company_id'])) {
			$map['company_id'] = array('eq',$_POST['eq_company_id']);
		}
		if (!empty($_POST['li_dept_id'])) {
			$map['dept_id'] = array('like','%|'.$_POST['li_dept_id'].'|%');
		}
		if (!empty($_POST['eq_pos_id'])) {
			$map['pos_id'] = array('eq',$_POST['eq_pos_id']);
		}
		if (!empty($_POST['eq_version'])) {
			$map['version'] = array('eq',$_POST['eq_version']);
		}
		
//  		dump($map);die;
}

	function index(){
		$fid=$_REQUEST['fid'];
		$map = $map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$model = M('PositionConfig');
		if (!empty($model)) {
			$info = $this->_list($model, $map,'id',false);
// 			$info = $model->where($map)->order("id desc")->select();
		}
		foreach($info as $k=>$v){
			$id=$v['company_id'];
			$dept_id=$v['dept_id'];
			$pos_id=$v['pos_id'];
			$com_name=M("Dept")->where("id=$id")->getField("name");
			$dept_name=M("Dept")->where(array('id'=>array('in',array_filter(explode('|', $dept_id)))))->getField("name",true);
			$position=D("DeptPositionView")->where("position_id=$pos_id")->getField("position_name");
			$info[$k]["company"]=$com_name;
			$info[$k]["dept"]=implode(';', $dept_name);
			$info[$k]["position"]=$position;
		}
		$flow_name=M("FlowTypeSetting")->where("id=$fid")->getField("flow_name");
		$company=M("Dept")->where("pid=0")->field("name,id")->select();
		$this->assign("fid",$fid);
		$this->assign('type',2);//岗位特殊
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
    			$model->dept_id = I('post.dept_name_multi_data');
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
		$company_id = $_POST['company_id'];
		$dept_ids = $_POST['dept_id'];
		$pos_id = $_POST['pos_id'];
		$type = $_POST['type'];
		if($type == '0'){
			$data=M("PositionConfig")->where(array('id'=>$id))->setField('version','历史');
		}else{
			$flag = false;
			$current = M("PositionConfig")->find($id);
			$dept_ids = array_filter(explode('|', $dept_ids));
			foreach ($dept_ids as $dept_id){
				$find=M("PositionConfig")->where(array('id'=>array('neq',$id),'fid'=>$current['fid'],'company_id'=>$company_id,'dept_id'=>array('like','%|'.$dept_id.'|%'),'pos_id'=>$pos_id,'version'=>'当前','is_del'=>'0'))->find();
				if($find){
					$flag = true;
					break;
				}
			}
			if(!$flag){
				$data=M("PositionConfig")->where(array('id'=>$id))->setField('version','当前');
			}else{
				$this->ajaxReturn('0', "无法设置为当前版本", 0);
			}
		}
		if(false !== $data){
			$this->ajaxReturn('1', "设置成功", 1);
		}else{
			$this->ajaxReturn('0', "设置失败", 0);
		}
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
		$depts = M('Dept')->field('id,pid,name')->where(array('id'=>array('in',get_child_dept_all($_POST['pid'])),'is_del'=>'0','is_use'=>'1','name'=>array('neq','公司领导')))->select();
		$tree = list_to_tree($depts);
		$html = select_tree_menu_mul($tree,0,array_filter(explode('|', $_POST['dept_id'])));
		$this -> ajaxReturn($html);
		
// 		$node = D("Dept");
// 		$dept_menu = $node -> field('id,pid,name') -> where("is_del=0") -> order('sort asc') -> select();
// 		$dept_tree = list_to_tree($dept_menu);
// 		if(!is_mobile_request()){
// 			$this -> assign('dept_list_new', select_tree_menu_mul($dept_tree));
// 		}
		
// 		$pid=$_POST['pid'];
// 		$dept_ids=get_child_dept_all($pid);
// 		$key=array_search($pid,$dept_ids);
// 		foreach ($dept_ids as $dept_id){
// 			if($dept_id == $pid){
// 				array_splice($dept_ids, $key, 1);
// 			}
// 		}
// 		$html = "";
// 		$html .= "<option value=\"\">请选择</option>\r\n";
// 		foreach ($dept_ids as $dept_id){
// 			$res = M('Dept')->field('id,name')->where(array('id'=>$dept_id,'is_del'=>0,'is_use'=>1))->find();
// 			if($res){
// 				$id = $res['id'];
// 				$name = $res['name'];
// 				$html .= "<option value=\"$id\">$name</option>\r\n";
// 			}
// 		}
// 		$this -> ajaxReturn($html);
	}
	function ajax_get_dept_simple(){
		$depts = M('Dept')->field('id,pid,name')->where(array('id'=>array('in',get_child_dept_all($_POST['pid'])),'is_del'=>'0','is_use'=>'1','name'=>array('neq','公司领导')))->select();
		$tree = list_to_tree($depts);
		$html = popup_menu_option($tree);
		$this -> ajaxReturn($html);
	}
	
	function ajax_get_pos(){
		$dep_id=$_POST['dep_id'];
		$dep_id = array_filter(explode('|', $dep_id));
		//$dept_ids=get_child_dept_all($dep_id);
		$res = M('RDeptPosition')->field('position_id')->where(array('dept_id'=>array('in',$dep_id)))->Distinct(true)->select();
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
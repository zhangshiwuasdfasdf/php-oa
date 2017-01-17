<?php
class ProcessNodeAction extends CommonAction {
	function _search_filter(&$map) {
		$map['is_del'] = array('eq','0');
}
	//配置页面
	function index(){
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$fid = $_REQUEST['fid'];
		$type = $_REQUEST['type'];
		$cid = $_REQUEST['id'];
		//添加通用流程
		$tid = M('FlowTypeSetting') -> where(array('flow_name'=>'通用','is_del'=>'0')) -> getField('id');
		//找出流程的版本
		$vid = M("FlowVersion") -> where(array('flow_type_setting_id' => array('in',array($fid,$tid)),'is_del'=>'0','status'=>'1')) -> getField('id',true);
		//版本中的节点
		$node = M("FlowNode") -> where(array('flow_version_id'=>array('in',$vid),'is_del'=>'0'))->select();
		$this -> assign('list',$node);
		// 当前配置项的所有信息
		$info = M('FlowConfigDetail') -> where(array('flow_config_id'=>$cid,'type'=>$type,'is_del'=>'0')) -> select();
		if($info){
			$is_new = false;
		}else{
			$is_new = true;
		}
		$this -> assign('is_new',$is_new);
		
		foreach ($info as $k => $v){
			$sheet_info[$v['sheet_id']][] = $v;
		}
		$sheet = array();
		//对配置条进行排序
		foreach ($sheet_info as $k => $v){
			$sheet[$k] = $this -> array_sort($v,'step','asc');
		}
		//虚拟个空的数组
		if(empty($sheet)){$sheet = array(array(''));}
		//查询以及部门
		$company=M("Dept")->where("pid=0")->field("name,id")->select();
		
		if($type == '1'){
			$history = M('CompanyConfig')->where(array('id'=>$cid,'fid'=>$fid,'is_del'=>'0','version'=>'历史'))->find();
			if($history){
				$can_edit = true;
			}else{
				$can_edit = false;
			}
		}elseif ($type == '2'){
			$history = M('PositionConfig')->where(array('id'=>$cid,'fid'=>$fid,'is_del'=>'0','version'=>'历史'))->find();
			if($history){
				$can_edit = true;
			}else{
				$can_edit = false;
			}
		}
		
		$this->assign("company",$company);
		$this -> assign('pageCount',$sheet);
		$this -> assign('cid',$cid);
		$this -> assign('type',$type);
		$this -> assign('fid',$fid);
		$this -> assign('can_edit',$can_edit);
// 		dump($node);die;
		$this->display();
	}
	
	//添加或者修改配置项
	function editProcess(){
		$model = M('FlowConfigDetail');
		$data['node_condition_id'] = $_POST['fnode'];
		$data['flow_config_id'] = $_POST['cid'];
		$data['type'] = $_POST['type'];
		$data['sheet_condition_id'] = $_POST['fenzhi'];
		$data['id'] = $_POST['id'];
		$data['step'] = $_POST['step'];
		$data['is_remind'] = $_POST['remind'];
		$data['is_merge'] = $_POST['merge'];
		$data['sheet_id'] = $_POST['sheet'];
		
		if($data['id']){
			$model ->save($data);
			$this->ajaxReturn(null,'修改成功',1);
		}else{
			$id = $model ->add($data);
			$this->ajaxReturn($id,'添加成功',1);
		}
	}
	//组织架构联动
	function frameInfo(){
		$pid = I('post.id');
		$node = D("Dept");
		$menu = array();
		$where['is_del'] = array('eq',0);
		$where['pid'] = $pid;
		$menu = $node -> field('id,pid,name') ->where($where)-> order('sort asc') -> select();
		$this->ajaxReturn($menu,'添加成功',1);
	}
	
	//	保存选择结果
	function save_result(){
		$id = I('post.id');
		$nr = I('post.result');
		$ni = I('post.result_id');
		$list = M('FlowConfigDetail') -> save(array('id'=>$id,'node_result_id'=>$ni,'node_result_val'=>$nr));
		if (false != $list) {
			$this->ajaxReturn($list,'修改成功',1);
		} else {
			$this->ajaxReturn($list,'修改失败',0);
		}
	}
	
	//验证部门岗位信息
	function valiDetp(){
		$nrv = I('post.nrv');
		if(!empty($nrv)){
			$cdp = explode('|',$nrv);
			$dept = M('Dept') -> where(array('id' => array('in',$cdp),'is_del'=>'0')) -> field('id,pid,name') -> select();
			if (!empty($dept)) {
				$this->ajaxReturn($dept,'修改成功',1);
			} else {
				$this->ajaxReturn($dept,'修改失败',0);
			}
		}
	}
	
	//联想用户名
	function assocName(){
		$name = I('post.name');
		if(!empty($name)){
			$user = M('User') -> where(array('is_del'=>'0','name'=>array('like',"%$name%")))->field('id,name')->select();
			if (!empty($user)) {
				$this->ajaxReturn($user,'修改成功',1);
			} else {
				$this->ajaxReturn($user,'修改失败',0);
			}
		}
	}
	//根据id获取员工姓名
	function assocUser(){
		$id = I('post.id');
		if(!empty($id)){
			$user = M('User') -> where(array('is_del'=>'0','id'=>$id))->field('id,name')->find();
			if (!empty($user)) {
				$this->ajaxReturn($user,'修改成功',1);
			} else {
				$this->ajaxReturn($user,'修改失败',0);
			}
		}
	}
	//获取公司下面所有岗位
	function ajax_get_dept(){
		$depts = M('Dept')->field('id,pid,name')->where(array('id'=>array('in',get_child_dept_all($_POST['pid'])),'is_del'=>'0','is_use'=>'1','name'=>array('neq','公司领导')))->select();
		$tree = list_to_tree($depts);
		$html = select_tree_menu_mul($tree,0,array($_POST['dept_id']),1);
		if($_POST['dept_id']){
			$dept_name = M('Dept')->where(array('id'=>$_POST['dept_id']))->getField('name');
		}
		$this -> ajaxReturn($html,$dept_name,1);
	}
	//根据部门id查询岗位id
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
	function guanjNode(){
		$fid = I('post.fid');
		if(!empty($fid)){
			//找出流程的版本
			$vid = M("FlowVersion") -> where(array('flow_type_setting_id' => array('in',array($fid)),'is_del'=>'0','status'=>'1')) -> getField('id',true);
			//版本中的节点
			$node = M("FlowNode") -> where(array('flow_version_id'=>array('in',$vid),'is_del'=>'0','node_type'=>'关键节点'))->select();
			$this->ajaxReturn($node,'修改成功',1);
		}
	}
	//取消节点
	function quitNode(){
		$id = I('post.id');
		$is_using = I('post.flag');
		if(M('FlowConfigDetail')->save(array('id'=>$id,'is_using'=>$is_using))){
			$this->ajaxReturn('','修改成功',1);
		}
	}
	//删除节点
	function delNode(){
		$id = I('post.id');
		$is_del = I('post.flag');
		if(M('FlowConfigDetail')->save(array('id'=>$id,'is_del'=>$is_del))){
			$this->ajaxReturn('','删除成功',1);
		}else {
			$this->ajaxReturn('','删除失败',0);
		}
	}
	//二维数组某一键名的值不能重复，删除重复项
	private function assoc_unique($arr, $key) {
		$tmp_arr = array();
		foreach ($arr as $k => $v) {
			if (in_array($v[$key], $tmp_arr)) {//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
				unset($arr[$k]);
			} else {
				$tmp_arr[] = $v[$key];
			}
		}
		sort($arr); //sort函数对数组进行排序
		return $arr;
	}
	//二维数组排序
	function array_sort($array,$row,$type){
		$array_temp = array();
		foreach($array as $v){
			$array_temp[$v[$row]] = $v;
		}
		if($type == 'asc'){
			ksort($array_temp);
		}elseif($type='desc'){
			krsort($array_temp);
		}else{
		}
		return $array_temp;
	}
}
?>
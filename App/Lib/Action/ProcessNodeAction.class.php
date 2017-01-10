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
		//添加通用流程
		$tid = M('FlowTypeSetting') -> where(array('flow_name'=>'通用','is_del'=>'0')) -> getField('id');
		//找出流程的版本
		$vid = M("FlowVersion") -> where(array('flow_type_setting_id' => array('in',array($fid,$tid)),'is_del'=>'0','status'=>'1')) -> getField('id',true);
		//版本中的节点
		$node = M("FlowNode") -> where(array('flow_version_id'=>array('in',$vid),'is_del'=>'0'))->select();
		$this -> assign('list',$node);
		/**
		 * 当前配置项的所有信息
		 */
		$info = M('FlowConfigDetail') -> where(array('flow_config_id'=>$fid,'type'=>$type,'is_del'=>'0')) -> select();
		foreach ($info as $k => $v){
			$sheet_info[$v['sheet_id']][] = $v;
		}
		$sheet = array();
		foreach ($sheet_info as $k => $v){
			$sheet[$k] = $this -> array_sort($v,'step','asc');
		}
		$this -> assign('pageCount',$sheet);
		$this -> assign('fid',$fid);
		$this -> assign('type',$type);
		$this->display();
	}
	
	//添加或者修改配置项
	function editProcess(){
		$model = M('FlowConfigDetail');
		$data['node_condition_id'] = $_POST['fnode'];
		$data['flow_config_id'] = $_POST['fid'];
		$data['type'] = $_POST['type'];
		$data['sheet_condition_id'] = $_POST['fenzhi'];
		$data['id'] = $_POST['id'];
		$data['step'] = $_POST['step'];
		$data['is_remind'] = $_POST['remind'];
		$data['is_merge'] = $_POST['merge'];
		$data['sheet_id'] = $_POST['sheet'];
		if($data['id']){
			
		}else{
			$model ->add($data);
			$this->ajaxReturn($data,'添加成功',1);
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
		if (false !== $list) {
			$this->ajaxReturn($list,'修改成功',1);
		} else {
			$this->ajaxReturn($list,'修改失败',0);
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
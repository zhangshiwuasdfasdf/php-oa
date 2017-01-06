<?php
class WorkFlowAction extends CommonAction {
	function _search_filter(&$map) {
		$map['is_del'] = array('eq','0');
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
}

	function index(){
		$map = $map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$model = M('FlowTypeSetting');
		if (!empty($model)) {
			$info = $this -> _list($model, $map);
		}
		
		$this->display();
	}
	
	function add(){
		if(IS_POST)
    	{
    		$model = M('FlowTypeSetting');
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
	function edit(){
		$id = $_REQUEST['id'];
		$where = array('id'=>$id);
		$model = M('FlowTypeSetting');
		if(IS_POST)
    	{
    		if($model->create(I('post.'), 2))
    		{
    			$res=$model->where($where)->save();
    		}
    	}
		if($res){
			$this->ajaxReturn($model->_sql(),1,0);
		}else{
			$this->ajaxReturn(null,null,0);
		}
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
	function get_child_menu($menu_id,&$array){
		$menu = M('MenuNew')->where(array('pid'=>$menu_id,'is_del'=>'0'))->order('sort asc')->getField('id,menu_name,menu_addr');
		if($menu){
			$array = array_merge($array,$menu);
		}
		foreach ($menu as $k=>$v){
			$this->get_child_menu($v['id'],$array);
		}
	}
	function getFlowNameByModuleName(){
		$module_name = I('name');
		if($module_name == '通用'){
			$html = '<option value="通用">通用</option>';
			$this -> ajaxReturn(1, $html, 1);
		}
		$menu_id = M('MenuNew')->where(array('menu_name'=>$module_name,'is_del'=>'0'))->getField('id');
		$array = array();
		$this->get_child_menu($menu_id,$array);
		foreach ($array as $k=>$v){
			if(false === strpos($v['menu_addr'], 'flow/getlist?type=')){
				unset($array[$k]);
			}
		}
		$html = '<option value="">请选择</option>';
		foreach ($array as $k=>$v){
			$query = parse_url($v['menu_addr'])['query'];
			$params = explode('&', $query);
			$query_arr = array();
			foreach ($params as $kk=>$vv){
				$param = explode('=', $vv);
				$query_arr[$param[0]] = $param[1];
			}
			$html .= '<option value="'.$v['menu_name'].'" node="'.$query_arr['type'].'">'.$v['menu_name'].'</option>';
		}
		$this -> ajaxReturn(1, $html, 1);
	}
}
?>
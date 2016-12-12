<?php
class PrivilegeAction extends CommonAction {
	
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_POST['li_pri_no'])) {
			$map['pri_no'] = array('like','%'.$_POST['li_pri_no'].'%');
		}
		if (!empty($_POST['li_pri_name'])) {
			$map['pri_name'] = array('like','%'.$_POST['li_pri_name'].'%');
		}
		if (!empty($_POST['li_menu_name'])) {
			$map['menu_name'] = array('like','%'.$_POST['li_menu_name'].'%');
		}
	}
	
	public function index(){
		//取出所有的菜单名
		$menuModel=D("MenuNew");
		$menuData=$this->getTree();
		//权限列表
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$priModel = D('PrivilegeView');
		if (!empty($priModel)) {
			$info = $this -> _list($priModel, $map,'id',true);
		}
		//取出所有的角色
		$where["is_del"]=array('eq',0);
		if (!empty($_REQUEST['name'])) {
			$where['role_name'] = array('like','%'.$_REQUEST['name'].'%');
		}
		
		$role=M("RoleManager")->where($where)->select();
		$company=array();
		foreach($role as $k=>$v){
			$company[$v['company']][$v['id']]=$v['role_name'];
			$role['_company']=$company;
		}
		//dump($role);die;
		$this->assign('role',$role);
		$this->assign('menuData',$menuData);
		$this->display();
	}
	
	//权限列表添加
	public function add(){
		//dump($_POST);die;
		$model=M("Privilege");
		if(IS_POST){
			$info['pri_name']=I('post.pri_name');
			$info['pri_no']=I('post.pri_no');
			$info['menu_new_id']=I('post.menu_new_id');
			if(I('post.pri_act') == '自定义'){
				$info['pri_act']=I('post.pri_act_zdy');
			}else{
				$info['pri_act']=I('post.pri_act');
			}
			$info['sort']=I('post.sort');
			$info['url']=I('post.url');
		}
		
		if($model->add($info))
    		{
    			$this->success('添加成功！', U('index'));
    			exit;
    		}
	}
	
	public function pri_del(){
		$id = $_REQUEST['id'];
		M("PrivilegeRole")->where(array('privilege_id'=>array('in',$id)))->delete();
		$this -> _del();
	}
	
	//编辑权限
	public function edit_pri(){
		$model=M("Privilege");
		$id = $_REQUEST['pri_id'];
		$where = array('id'=>$id);
		$info['pri_name']=I('post.pri_name');
		$info['pri_no']=I('post.pri_no');
		$info['menu_new_id']=I('post.menu_new_id');
		if(I('post.pri_act') == '自定义'){
			$info['pri_act']=I('post.pri_act_zdy');
		}else{
			$info['pri_act']=I('post.pri_act');
		}
		$info['sort']=I('post.sort');
		$info['url']=I('post.url');
		$res = $model-> where($where)->setField($info);
		
		if($res){
			$this->ajaxReturn(1,1,0);
		}else{
			$this->ajaxReturn(null,null,0);
		}
		
	}
	//权限类型
	function get_pri_act(){
		$menu_new_id=$_REQUEST['menu_new_id'];
		$where['menu_new_id']=array('eq',$menu_new_id);
		$where['is_del']=array('eq',0);
		$res=M("Privilege")->field("pri_act")->where($where)->select();
		$this->ajaxReturn($res,'success','1');
	}
	
	//取出角色
	function show_role(){
		$pri_id=I("post.id");
		$where["is_del"]=array('eq',0);
		if (!empty($_REQUEST['name'])) {
			$where['role_name'] = array('like','%'.$_REQUEST['name'].'%');
		}
		
		$role=M("RoleManager")->where($where)->select();
		$data=M("PrivilegeRole")->where(array('privilege_id' => $pri_id ))->distinct(true)->select();
		if($data){
			foreach ($data as $k => $v) {
			$role["role_id"][]=$v['role_id'];
			}
		}
		
		$this->ajaxReturn($role,'success','1');
	}
	
	//关联角色
	function add_role(){
		//dump($_POST);die;
		$pri_id=I("post.id");
		$role_id=I("post.role_id");
		$model=M("PrivilegeRole");
		$has=$model->where(array('privilege_id'=>$pri_id))->count();
		if($has){
			M("PrivilegeRole")->where(array('privilege_id'=>$pri_id))->delete();
		}
		foreach ($role_id as $k => $v) {
			$res=$model->add(array(
					'privilege_id'=>$pri_id,
					'role_id'=>$v,
				));
			}
		if($res){
			$this->success("关联角色成功！",U('index'));
		}else{
			$this->error("关联角色失败！");
		}
	}
	
	//关联角色复制
	public function copy_role(){
		$pri_id=I('post.pri_id');
		$where['menu_new_id']=array('eq',I("post.menu_new_id"));
		$where['pri_act']=array('eq',I("post.pri_act"));
		$copy_pri_id=M("Privilege")->where($where)->getField('id');
		$has=M("PrivilegeRole")->where(array('privilege_id'=>$pri_id))->count();
		$copy_role_id=M("PrivilegeRole")->field('role_id')->where(array('privilege_id'=>$copy_pri_id))->select();
		if($has){
			M("PrivilegeRole")->where(array('privilege_id'=>$pri_id))->delete();
		}
		foreach ($copy_role_id as $k => $v) {
			$res=M("PrivilegeRole")->add(array(
					'privilege_id'=>$pri_id,
					'role_id'=>$v['role_id'],
				));
		}
		if($res){
			$this->success("关联角色复制成功！",U('index'));
		}else{
			$this->error("关联角色复制失败！");
			}

	}
	
	public function getTree()
	{
		$menuModel=D("MenuNew");
		$data = $menuModel-> where(array('is_del'=>'0')) ->select();
		return $this->_reSort($data);
	}
	private function _reSort($data, $parent_id=0, $level=0, $isClear=TRUE)
	{
		static $ret = array();
		if($isClear)
			$ret = array();
		foreach ($data as $k => $v)
		{
			if($v['pid'] == $parent_id)
			{
				$v['level'] = $level;
				$ret[] = $v;
				$this->_reSort($data, $v['id'], $level+1, FALSE);
			}
		}
		return $ret;
	}
	
}
?>
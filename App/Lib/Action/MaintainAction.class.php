<?php
class MaintainAction extends CommonAction {
	protected $config = array('app_type' => 'common', 'action_auth' => array('changestatus' => 'read' , 'import_client' => 'read' ,'export_info' => 'read','add_role' => 'read','show_role' => 'read','copy_role' => 'read','add_data' => 'read','ajax_get_data' => 'read', 'avliname'=>'read'));
	
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['keyword']) && empty($map['64'])) {
			$map['menu_name'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}
	//列表页
	function index (){
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$name = isset($_POST['li_menu_name']) ? " AND `menu_name` LIKE '%".$_POST['li_menu_name']."%' " : "" ;
		$sql = "SELECT * FROM `smeoa_menu_new` WHERE ( `is_del` = '0' $name ) ORDER BY `sort` asc ";
		$list = M()->query($sql);
		if(empty($_POST['li_menu_name'])){
			$this -> assign('menu',new_tree_menu(list_to_tree($list),4));
		}else{
			$this -> assign('menu',new_tree_menu($list,4));
		}
		$this -> assign('menuList',popup_menu_option(list_to_tree($list)));
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
		//取出所有的菜单
		$menuModel=D("MenuNew");
		$menuData=$this->getTree();
		$this->assign('menuData',$menuData);
		$this->assign('role',$role);
		$this -> display();	
		
	}
	
	//添加数据(一级菜单)
	function add(){
		if($this -> isAjax()){
			$model = M('MenuNew');
			$data['pid'] = empty(I('post.pid')) ? "0" : I('post.pid');
			$data['menu_no'] = empty(I('post.menu_no')) ? "" : I('post.menu_no');
			$data['menu_name'] = I('post.name');
			$data['menu_addr'] = I('post.addr');
			$data['sort'] = I('post.sort');
			$data['menu_status'] = empty($_POST['menu_status']) ? "1" : I('post.menu_status');
			$data['create_time'] =  time();
			/*保存当前数据对象 */
			$list = $model -> add($data);
			if ($list !== false) {//保存成功
				$this -> ajaxReturn($data, "添加成功", 1);
			} else {
				//失败提示
				$this -> ajaxReturn($data, "添加失败", 0);
			}
		}
	}
	//添加数据(一级菜单)
	function save(){
		if($this -> isAjax()){
			$model = M('MenuNew');
			$data['id'] = I('post.id');
			$data['pid'] = empty(I('post.pid')) ? "0" : I('post.pid');
			$data['menu_no'] = empty(I('post.menu_no')) ? "" : I('post.menu_no');
			$data['menu_name'] = I('post.name');
			$data['menu_addr'] = I('post.addr');
			$data['sort'] = I('post.sort');
			$data['create_time'] =  time();
			/*保存当前数据对象 */
			$list = $model -> save($data);
			if ($list !== false) {//保存成功
				$this -> ajaxReturn($data, "修改成功", 1);
			} else {
				//失败提示
				$this -> ajaxReturn($data, "修改失败", 0);
			}
		}
	}
	//更改菜单状态
	function changestatus(){
		$model = M('MenuNew');
		$data['id'] = I('post.id');
		$data['menu_status'] = (I('post.status') == "禁用") ? '0' : '1' ;
		/*保存当前数据对象 */
		$list = $model -> save($data);
		if ($list !== false) {//保存成功
			$this -> ajaxReturn($data, "修改成功", 1);
		} else {
			//失败提示
			$this -> ajaxReturn($data, "修改失败", 0);
		}
	}
	//下载模板
	public function down() {
		$this -> _down();
	}
	//查看详情
	function read(){
		$pid = $_REQUEST['id'];
		$model = D('Attract_detail');
		$map['pid'] = $pid;
		if (!empty($model)) {
			$info = $this -> _list($model, $map);
			$this -> assign('info', $info);
		}
		$data = M('Attract') -> find($pid);
		$this -> assign('data',$data);
		$this -> display();
	}
	
	function del(){
		$this -> _del(null,'MenuNew');
	}
	//验证同级菜单名称是否存在
	public function avliName(){
		$id = I('post.id');
		$pid = I('post.pid');
		$name = I('post.name');
		if(!empty($name)){
			$where['pid'] = $pid;
			$where['menu_name'] = $name;
			$where['is_del'] = '0';
			if($id){//如果是修改
				$where['id'] = array('neq',$id);
			}
			$flag = M('MenuNew') -> where($where) -> find();
			if(!empty($flag)){
				$this -> ajaxReturn($flag,'菜单名称已经存在!',0);
			}else{
				$this -> ajaxReturn($where,'菜单名称不存在!',1);
			}
				
		}
	}
	//关联角色
    function add_role(){
        $menu_id=I("post.id");
        $role_id=I("post.role_id");
        $model=M("RRoleMenu");
        $has=$model->where(array('menu_id'=>$menu_id))->count();
        if($has){
            M("RRoleMenu")->where(array('menu_id'=>$menu_id))->delete();
        }
        foreach ($role_id as $k => $v) {
            $res=$model->add(array(
                    'menu_id'=>$menu_id,
                    'role_id'=>$v,
                ));
        }
        if($res){
            $this->success("关联角色成功！",U('index'));
        }else{
            $this->error("关联角色失败！");
        }
    }
    
    function show_role(){
        $menu_id=I("post.id");
        $where["is_del"]=array('eq',0);
        if (!empty($_REQUEST['name'])) {
            $where['role_name'] = array('like','%'.$_REQUEST['name'].'%');
        }
        
        $role=M("RoleManager")->where($where)->select();
        $data=M("RRoleMenu")->where(array('menu_id' => $menu_id ))->distinct(true)->select();
        if($data){
            foreach ($data as $k => $v) {
            $role["role_id"][]=$v['role_id'];
            }
        }
        $this->ajaxReturn($role,'success','1');
    }
	
	public function getTree()
	{
		$menuModel=D("MenuNew");
		$data = $menuModel->order('menu_name asc')->select();
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
	
	public function copy_role(){
		$now_menu_id=I('post.now_menu_id');
		$where['menu_new_id']=array('eq',I("post.menu_new_id"));
		$has=M("RRoleMenu")->where(array('menu_id'=>$now_menu_id))->count();
		$copy_role_id=M("RRoleMenu")->field('role_id')->where(array('menu_id'=>I("post.menu_new_id")))->select();
		if($has){
			M("RRoleMenu")->where(array('menu_id'=>$now_menu_id))->delete();
		}
		foreach ($copy_role_id as $k => $v) {
		$res=M("RRoleMenu")->add(array(
				'menu_id'=>$now_menu_id,
				'role_id'=>$v['role_id'],
			));
		}
		if($res){
			$this->success("关联角色复制成功！",U('index'));
		}else{
			$this->error("关联角色复制失败！");
			}
	}
	
	public function ajax_get_data(){
		$menu_id=I("post.id");
        $data=M("RRoleMenu")
        ->field('a.menu_id,a.role_id,a.scope,b.id,b.company,b.role_name')
        ->alias('a')
        ->join('LEFT JOIN __ROLE_MANAGER__  b ON a.role_id=b.id')
        ->where(array('menu_id' => $menu_id ))->distinct(true)->select();
        $company=array();
        $scope=array();
		/*foreach($data as $k=>$v){
			$company[$v['company']][$v['id']]=$v['role_name'];
			$data['_company']=$company;
			$scope[$v['id']]=$v['scope'];
			$data['_scope']=$scope;
		}*/
		foreach($data as $k=>$v){
			$scope=$v['id'].','.$v['scope'];
			$company[$v['company']][$scope]=$v['role_name'];
			$data['_company']=$company;
		}
		$this->assign('info',$info);
		$this->assign('data',$data);
		$this->display('data');
	}
	
	public function add_data(){
		$menu_id=I('post.id');
		$scope=I('post.scope');
		foreach($scope as $k=>$v){
			$data['scope']=$v;
			$where['menu_id']=array('eq',$menu_id);
			$where['role_id']=array('eq',$k);
			$res=M("RRoleMenu")->where($where)->save($data);
		}
			$this->success("关联数据成功！",U('index'));
	}
	
}
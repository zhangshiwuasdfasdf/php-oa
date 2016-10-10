<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class StaffAction extends CommonAction {
	//过滤查询字段
	protected $config=array('app_type'=>'common', 'action_auth' => array('get_dept_and_user' => 'read','get_user_by_id' => 'read'));
	private $position;
	private $rank;
	private $dept;

	function _search_filter(&$map) {
		$map['is_del'] = 0;
		if (!empty($_POST['keyword'])) {
			$map['name|emp_no'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}

	function index() {
		if(!is_mobile_request() || $_REQUEST['mobile_login_type'] == 'pc'){
			$map = $this -> _search();
			if (method_exists($this, '_search_filter')) {
				$this -> _search_filter($map);
			}
			if(!empty($map)){
				$res = $this -> _list(D('User'), $map,"emp_no",true);
				foreach ($res as $k=>$v){
					$position = M('Position')->find($v['position_id']);
					$res[$k]['position_name'] = $position['name'];
				}
			}
			$this->assign("title",'职员查询');
			$node = D("Dept");
			$menu = array();
			$where['is_del'] = array('eq',0);
			$menu = $node -> field('id,pid,name') ->where($where)-> order('sort asc') -> select();
		
			$tree = list_to_tree($menu);
			$a = popup_tree_menu($tree,0,100,true);
			
			$a = str_replace('tree_menu','submenu',$a);
			$a = str_replace('<a class=""','<a class="dropdown-toggle"',$a);
			$a = preg_replace('/submenu/','nav-list',$a,1);
			if(!is_mobile_request()){
				$this -> assign('menu', $a);
			}
			$this -> display();
		}else{//手机端通讯录
			$prefix = C('DB_PREFIX');
			$sql = "select id,emp_no,name,letter,left(letter,1) as l,mobile_tel from {$prefix}user order by letter";
			$user = M('User') -> query($sql);
			$this -> assign('contacts', $user);
			$this -> display();
		}
		
	}
	
	function read() {
		$id = $_REQUEST['id'];
		$model = M("Dept");
		$dept = tree_to_list(list_to_tree(M("Dept") ->where('is_del=0')-> select(), $id));
		$dept = rotate($dept);
		$dept = implode(",", $dept['id']) . ",$id";
		$model = D("UserView");
		$where['is_del'] = array('eq', '0');
		$where['pos_id'] = array('in', $dept);
		$data = $model -> where($where)->order('duty asc') -> select();
		
		$this -> ajaxReturn($data, "", 1);
	}
	public function get_dept_and_user(){
		$node = D("Dept");
		$menu = array();
		$where['is_del'] = array('eq',0);
		$menu = $node -> field('id,pid,name,is_real_dept') ->where($where)-> order('sort asc') -> select();
		
		foreach ($menu as $k=>$v){
			$users = M('User')->field('id,name,pos_id as pid')->where(array('pos_id'=>$v['id']))->select();
			foreach ($users as $kk=>$vv){
				$users[$kk]['id'] = $users[$kk]['name'].'|'.$users[$kk]['id'].';';
				if($v['is_real_dept']){
					$menu[] = $users[$kk];
				}else{
					$pid = $v['pid'];
					$is = M("Dept")->field('id,pid,is_real_dept')->find($pid);
					while ($is['is_real_dept']=='0'){
						$pid = $is['pid'];
						$is = M("Dept")->field('id,pid,is_real_dept')->find($pid);
					}
					$users[$kk]['pid'] = $is['id'];
					$menu[] = $users[$kk];
				}
				
			}
		}
		//把不是部门的删掉
		foreach ($menu as $k=>$v){
			if($v['is_real_dept']=='0'){
				unset($menu[$k]);
			}
		}
		$tree = list_to_tree($menu);
		$a = popup_tree_menu($tree,0,100,true);
			
		$a = str_replace('tree_menu','submenu',$a);
		$a = str_replace('<a class=""','<a class="dropdown-toggle"',$a);
		$a = preg_replace('/submenu/','nav-list',$a,1);
// 		echo $a;
		$this -> ajaxReturn($a, "", 1);
	}
	public function get_user_by_id(){
		if(is_mobile_request()){
			$user_id = intval($_REQUEST['target_user_id']);
			$info = D('UserView')->field('id,emp_no,sex,birthday,dept_name,duty,email,mobile_tel,name,pic,bk_pic')->find($user_id);
// 			$info = get_user_info($user_id, 'id,emp_no,sex,birthday,dept_name,duty,email,mobile_tel,name,pic,bk_pic');
			$this -> ajaxReturn($info, "", 1);
		}
	}
}
?>
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
	protected $config=array('app_type'=>'common');
	private $position;
	private $rank;
	private $dept;

	function _search_filter(&$map) {
		if (!empty($_POST['keyword'])) {
			$map['name|emp_no'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}

	function index() {
		if(!is_mobile_request()){
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
// 			$open=fopen("C:\log.txt","a" );
// 			fwrite($open,$a."\r\n");
// 			fclose($open);
			$this -> assign('menu', $a);
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
		$data = $model -> where($where) -> select();
		
		$this -> ajaxReturn($data, "", 1);
	}
	
}
?>
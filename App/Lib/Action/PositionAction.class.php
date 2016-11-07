<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/


class PositionAction extends CommonAction {
	protected $config=array(
		'app_type'=>'master'
		);

	function _search_filter(&$map) {
		if (!empty($_POST['code'])) {
			$map['code'] = array('eq',$_POST['code']);
		}
		if (!empty($_POST['li_position_name'])) {
			$map['position_name'] = array('like','%'.$_POST['li_position_name'].'%');
		}
		if (false !== $_POST['eq_is_del'] && '' != $_POST['eq_is_del']) {
			$map['is_del'] = array('eq',$_POST['eq_is_del']);
		}
		
	}
	
	function del(){
		$id=$_POST['id'];
		$this->_destory($id);		
	}
	function index(){
		$map = array();
		$this->_search_filter($map);
		$this->_list(M('Position'), $map,'code',true);
		$this->display();
	}
	function insert(){
		$model = M("Position");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$last_code = M('Position')->where(array('code'=>array('like',date('ym').'%')))->order('code desc')->limit(1)->getField('code');
		if($last_code){
			$num = intval(substr($last_code,-4));
			$new_num = formatto4w($num+1);
			$model -> code = date('ym').$new_num;
		}else{
			$model -> code = date('ym').formatto4w(1);
		}
		if(false !== $model -> id){
			unset($model -> id);
		}
		$model -> create_user_name = get_user_name();
		$model -> create_time = time();
		$list = $model -> add();
		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('新增成功!');
		}else {
			$this -> error('新增失败!');
		}
	}
	function update(){
		$model = M("Position");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
	
		$model -> update_user_name = get_user_name();
		$model -> update_time = time();
		$list = $model -> save();
		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('保存成功!');
		}else {
			$this -> error('保存失败!');
		}
	}
	function distribute(){
		if(!empty($_POST['position_id']) && !empty($_POST['dept']) && is_array($_POST['dept'])){
			//删掉多余的
			$del = M('RDeptPosition')->where(array('dept_id'=>array('not in',$_POST['dept']),'position_id'=>$_POST['position_id']))->delete();
			foreach ($_POST['dept'] as $k=>$v){
				$find = M('RDeptPosition')->where(array('dept_id'=>$v,'position_id'=>$_POST['position_id']))->find();
				if(!$find){
					$res = M('RDeptPosition')->add(array('dept_id'=>$v,'position_id'=>$_POST['position_id']));
					if(false === $res){
						$this -> error('分配失败!');
					}
				}
			}
			$this -> success('分配成功!');
		}elseif(!empty($_POST['position_id'])){
			$del = M('RDeptPosition')->where(array('position_id'=>$_POST['position_id']))->delete();
			if(false !== $del){
				$this -> success('分配成功!');
			}else{
				$this -> error('分配失败!');
			}
		}else{
			$this -> error('不存在该岗位!');
		}
	}
	function list_dept_checkbox(){
		$list = M('Dept') ->where(array('is_del'=>0)) -> order('sort asc') -> getField('id,pid,name');
		$tree = list_to_tree($list);
		$html = popup_menu_organization_checkbox($tree);
		$this->ajaxReturn($html,1,0);
	}
}
?>
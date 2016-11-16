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
			$map['code'] = array('like','%'.$_POST['code'].'%');
		}
		if (!empty($_POST['li_position_name'])) {
			$map['position_name'] = array('like','%'.$_POST['li_position_name'].'%');
		}
		if (false !== $_POST['eq_is_use'] && '' != $_POST['eq_is_use']) {
			$map['is_use'] = array('eq',$_POST['eq_is_use']);
		}
		
	}
	
	function del(){
		$id=$_REQUEST['id'];
		$res = M('Position')->where(array('id'=>array('in',$id)))->save(array('is_del'=>'1'));
		if(false !== $res){
			$this -> ajaxReturn('', "删除成功", 1);
		}else{
			$this -> ajaxReturn('', "删除失败", 0);
		}
	}
	function index(){
		$map['is_del'] = '0';
		$this->_search_filter($map);
		$this->_list(M('Position'), $map,'code',false);
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
		$html = popup_menu_organization_checkbox($tree,0,100,$_POST['position_id']);
		$this->ajaxReturn($html,1,0);
	}
// 	function test(){
// 		$ids = M('User')->field('id')->select();
// 		foreach ($ids as $k=>$v){
// 			$data['user_id'] = $v['id'];
// 			$data['no_status'] = '正常';
// 			$data['stuff_status'] = '已转正';
// 			$data['entry_time'] = '2016-03-14';
// 			$data['regular_time'] = '2016-06-13';
// 			$data['create_time'] = date('Y/m/d H:i:s');
// 			$data['create_name'] = get_user_name();
// 			M('StatusManage')->add($data);
// 		}
// 	}
}
?>
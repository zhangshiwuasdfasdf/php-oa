<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/


class SystemConfigAction extends CommonAction {
	//过滤查询字段
	protected $config=array('app_type'=>'master');
	
	function _search_filter(&$map) {
		if (!empty($_POST['keyword'])) {
			$map['val|name|code'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}
	
	function del(){
		$id=$_POST['id'];
		$this->_destory($id);		
	}
	function holiday(){
		$model = D("Holiday");
		$this -> _list($model, $map,'date',true);
		$this->display();
	}
	function holiday_save(){
		$res = M('Holiday')->add($_POST);
		if($res){
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('新增成功!');
		}
	}
	function holiday_edit(){
		$model = D("Holiday");
		$where = array('id'=>$_POST['id']);
		
		if($_POST['is_holiday']){
			$data['is_holiday'] = $_POST['is_holiday'];
		}
		if($_POST['remark']){
			$data['remark'] = $_POST['remark'];
		}
		$res = $model-> where($where)->setField($data);
		if($res){
			$this->ajaxReturn(1,1,0);
		}else{
			$this->ajaxReturn(null,null,0);
		}
	}
	function holiday_del(){
		$id=$_POST['id'];
		$where = array('id'=>intval($id));
		$model = D("Holiday");
		$res = $model->where($where)->delete();
		if($res){
			$this->ajaxReturn(1,1,0);
		}else{
			$this->ajaxReturn(null,null,0);
		}
	}
	
	function noneDR(){
		$dail = M('DailyReport');
		$where['work_date'] = date('Y-m-d', strtotime('-1 day'));
		$where['is_del'] = '0';
		$where['is_submit'] = '1';
		$list = $dail -> where($where)->field('id,user_id,work_date')->select();
		$list = rotate($list);
		
		
		$id = $_REQUEST['id'];
		$model = M("Dept");
		$dept = tree_to_list(list_to_tree(M("Dept") ->where('is_del=0')-> select(), $id));
		$dept = rotate($dept);
		$dept = implode(",", $dept['id']);
		$model = D("UserView");
		$map['is_del'] = array('eq', '0');
		$map['pos_id'] = array('in', $dept);
		$map['id'] = array('not in',$list['user_id']);
		$data = $model -> where($map)->order('duty asc') -> select();
		
		
		if($data){
			$this->ajaxReturn($data,1,1);
		}else{
			$this->ajaxReturn(null,1,0);
		}
	}
	function simple_data_mapping(){
// 		$res = M('SimpleData')->select();
// 		foreach ($res as $k=>$v){
// 			if($v['type']=='common'){//普通配置
// 				$D[$v['name']] = M($v['table_name'])->field($v['code'].','.$v['field'])->select();
// 			}elseif($v['type']=='complex'){//循环配置
// 				$complex = M($v['table_name'])->field($v['code'].','.$v['field'].','.$v['pid_field'])->select();
// 				foreach ($complex as $kk=>$vv){
// 					if($vv['pid']){
// 						$name = M($v['table_name'])->field($v['field'])->find($vv['pid']);
// 						$D[$name[$v['field']]][] = array($v['code']=>$vv[$v['code']],$v['field']=>$vv[$v['field']]);
// 					}
// 				}
// 			}
			
// 		}
// 		dump($D);
		$map = array();
		if($_POST['li_data_type']){
			$map['data_type'] = array('like','%'.$_POST['li_data_type'].'%');
		}
		if($_POST['li_data_code']){
			$map['data_code'] = array('like','%'.$_POST['li_data_code'].'%');
		}
		if($_POST['li_data_name']){
			$map['data_name'] = array('like','%'.$_POST['li_data_name'].'%');
		}
		$model = M('SimpleDataMapping');
		$this->_list($model, $map);
		$this->display();
	}
}
?>
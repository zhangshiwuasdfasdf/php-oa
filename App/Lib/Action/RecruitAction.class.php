<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class RecruitAction extends CommonAction {
	//app 类型
// 	protected $config = array('app_type' => 'master');

	function _search_filter(&$map) {
		if (!empty($_POST['keyword'])) {
			$map['grade_no|name'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}

	public function index() {
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		
		$companys = M('Dept')->field('id,name')->where(array('pid'=>0))->select();
		$company_html = '<option>请选择公司</option>';
		foreach ($companys as $k=>$v){
			if($v['id'] == $company_id){
				$company_html .='<option selected="selected" value="'.$v['id'].'">'.$v['name'].'</option>';
			}else{
				$company_html .='<option value="'.$v['id'].'">'.$v['name'].'</option>';
			}
		}
		$this -> assign("company", $company_html);
		$this -> _list(M("Recruit"), array('is_del'=>'0'),'',false,'list','','','_add_names');
		$this -> display();
	}
	function _add_names($array){
		$recruit_status = array('待报到','已报到','已建档','未报到');
		foreach ($array as $k=>$v){
			$array[$k]['company_name'] = M('Dept')->where(array('id'=>$v['company_id']))->getField('name');
			$array[$k]['dept_name'] = M('Dept')->where(array('id'=>$v['dept_id']))->getField('name');
			$array[$k]['position_name'] = M('Position')->where(array('id'=>$v['position_id']))->getField('position_name');
			$array[$k]['stuff_status'] = M('SimpleDataMapping')->where(array('data_type'=>'员工状态','data_code'=>$v['stuff_status']))->getField('data_name');
			$array[$k]['hr_specialist_name'] = M('User')->where(array('id'=>$v['hr_specialist_id']))->getField('name');
			$array[$k]['recruit_status_name'] = $recruit_status[$v['recruit_status']];
		}
		return $array;
	}
	function set_recruit_status(){
		$id = $_POST['id'];
		$status = $_POST['status'];
		if($status == '2'){
			$recruit = M('Recruit')->find($id);
		}
		
	}
	public function add(){
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		
		$companys = M('Dept')->field('id,name')->where(array('pid'=>0))->select();
		$company_html = '<option>请选择公司</option>';
		foreach ($companys as $k=>$v){
			$company_html .='<option value="'.$v['id'].'">'.$v['name'].'</option>';
		}
		$this -> assign("company", $company_html);
		
		$qualifications = M('SimpleDataMapping')->field('data_code,data_name')->where(array('data_type'=>'学历'))->select();
		$qualifications_html = '<option>请选择学历</option>';
		foreach ($qualifications as $k=>$v){
			$qualifications_html .='<option value="'.$v['data_code'].'">'.$v['data_name'].'</option>';
		}
		$this -> assign("qualifications", $qualifications_html);
		
		$stuff_status = M('SimpleDataMapping')->field('data_code,data_name')->where(array('data_type'=>'员工状态','data_code'=>array('not in',array('98','99'))))->select();
		$stuff_status_html = '<option>请选择学历</option>';
		foreach ($stuff_status as $k=>$v){
			$stuff_status_html .='<option value="'.$v['data_code'].'">'.$v['data_name'].'</option>';
		}
		$this -> assign("stuff_status", $stuff_status_html);
		$this -> assign("hr_specialist_id", get_user_id());
		$this -> assign("hr_specialist_name", get_user_name());
		$this -> display();
	}
// 	public function save(){
// 		dump($_POST);die;
// 	}
	function get_dept_html(){
		$company_id = $_POST['company_id'];
		$depts = M('Dept')->field('id,pid,name')->where(array('id'=>array('in',get_child_dept_all($company_id)),'is_del'=>'0','is_use'=>'1'))->select();
		$tree = list_to_tree($depts);
		$data['dept'] = popup_menu_option($tree,0);
		$this->ajaxReturn($data,1,1);
	}
	function get_position_html(){
		$dept_id = $_POST['dept_id'];
		$position_ids = M('RDeptPosition')->where(array('dept_id'=>$dept_id))->getField('position_id',true);
	
		$positions = M('Position')->field('id,position_name')->where(array('id'=>array('in',$position_ids),'is_del'=>'0','is_use'=>'1'))->select();
		$html = '<option>请选择岗位</option>';
		foreach ($positions as $k=>$v){
			$html .= '<option value="'.$v['id'].'">'.$v['position_name'].'</option>';
		}
		$data['position'] = $html;
		$this->ajaxReturn($data,1,1);
	}
	
	function del() {
		$id = $_POST['id'];
		$this -> _destory($id);
	}

}
?>
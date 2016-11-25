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
		if (!empty($_POST['li_name'])) {
			$map['name'] = array('like', "%" . $_POST['li_name'] . "%");
		}
		if (!empty($_POST['be_entry_date'])) {
			$map['entry_date'] = array('egt', $_POST['be_entry_date']);
		}
		if (!empty($_POST['en_entry_date'])) {
			$map['_complex']['entry_date'] = array('elt', $_POST['en_entry_date']);
		}
		if ($_POST['eq_recruit_status']>-1) {
			$map['recruit_status'] = array('eq', $_POST['eq_recruit_status']);
		}
		if (!empty($_POST['li_recruitment_channel'])) {
			$map['recruitment_channel'] = array('like', "%" . $_POST['li_recruitment_channel'] . "%");
		}
		if (!empty($_POST['li_hr_specialist_name'])) {
			$user_ids = M('User')->where(array('name'=>array('like',"%".$_POST['li_hr_specialist_name']."%")))->getField('id',true);
			$map['hr_specialist_id'] = array('in',$user_ids);
		}
		if (!empty($_POST['eq_company_id'])) {
			$map['company_id'] = array('in', array_filter(explode(',',$_POST['eq_company_id'])));
		}
		if (!empty($_POST['eq_dept_id'])) {
			$map['dept_id'] = array('in', array_filter(explode(',',$_POST['eq_dept_id'])));
		}
		if (!empty($_POST['eq_position_id'])) {
			$map['position_id'] = array('in', array_filter(explode(',',$_POST['eq_position_id'])));
		}
	}

	public function index() {
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		
		$companys = M('Dept')->field('id,name')->where(array('pid'=>0))->select();
		$company_html = '<option>请选择公司</option>';
		$company_search_html = '<ul>';
		foreach ($companys as $k=>$v){
			if($v['id'] == $company_id){
				$company_html .='<option selected="selected" value="'.$v['id'].'">'.$v['name'].'</option>';
			}else{
				$company_html .='<option value="'.$v['id'].'">'.$v['name'].'</option>';
			}
			$company_search_html .= '<li>';
			$company_search_html .= '<img src="__PUBLIC__/img/hl.png"/>';
			$company_search_html .= '<input type="checkbox" name="company_id[]" id="company_id_'.$v['id'].'" value="'.$v['id'].'">';
			$company_search_html .= '<label for="company_id_'.$v['id'].'">'.$v['name'].'</label>';
			$company_search_html .= '</li>';
		}
		$company_search_html .= '</ul>';
		$this -> assign("company", $company_html);
		$this -> assign("company_search_html", $company_search_html);
		$map['is_del'] = '0';
		$this->_search_filter($map);
		$this -> _list(M("Recruit"), $map,'',false,'list','page','p','_add_names');
		$this -> display();
	}
	function _add_names($array){
		$recruit_status = array('待报到','已报到','已建档','未报到');
		foreach ($array as $k=>$v){
			$array[$k]['company_name'] = M('Dept')->where(array('id'=>$v['company_id']))->getField('name');
			$array[$k]['dept_name'] = M('Dept')->where(array('id'=>$v['dept_id']))->getField('name');
			$array[$k]['position_name'] = M('Position')->where(array('id'=>$v['position_id']))->getField('position_name');
			$array[$k]['stuff_status'] = M('SimpleDataMapping')->where(array('data_type'=>'员工状态','data_code'=>$v['stuff_status'],'is_del'=>'0'))->getField('data_name');
			$array[$k]['hr_specialist_name'] = M('User')->where(array('id'=>$v['hr_specialist_id']))->getField('name');
			$array[$k]['recruit_status_name'] = $recruit_status[$v['recruit_status']];
		}
		return $array;
	}
	function read(){
		$recruit = M("Recruit")->find($_GET['id']);
		$recruit_status = array('待报到','已报到','已建档','未报到');
		$interview_evaluation = array('','及格','良好','优秀');
		$recruit['company_name'] = M('Dept')->where(array('id'=>$recruit['company_id']))->getField('name');
		$recruit['dept_name'] = M('Dept')->where(array('id'=>$recruit['dept_id']))->getField('name');
		$recruit['position_name'] = M('Position')->where(array('id'=>$recruit['position_id']))->getField('position_name');
		$recruit['stuff_status'] = M('SimpleDataMapping')->where(array('data_type'=>'员工状态','data_code'=>$recruit['stuff_status'],'is_del'=>'0'))->getField('data_name');
		$recruit['hr_specialist_name'] = M('User')->where(array('id'=>$recruit['hr_specialist_id']))->getField('name');
		$recruit['recruit_status_name'] = $recruit_status[$recruit['recruit_status']];
		$recruit['position_sequence_name'] = M('PositionSequence')->where(array('id'=>$recruit['position_sequence_id']))->getField('sequence_name');
		$recruit['interview_evaluation'] = $interview_evaluation[$recruit['interview_evaluation']];
		$recruit['user_id'] = formatto4w($recruit['user_id']);
		$recruit['create_record_user_name'] = M('User')->where(array('id'=>$recruit['create_record_user_id']))->getField('name');
		$this -> assign("recruit", $recruit);
		$this -> display();
	}
	function edit(){
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		
		$recruit = M("Recruit")->find($_GET['id']);
		if($recruit['recruit_status']>1){
			$this->error('已建档或未报到的无法编辑！');
		}
		
		$companys = M('Dept')->field('id,name')->where(array('pid'=>0))->select();
		$company_html = '<option>请选择公司</option>';
		foreach ($companys as $k=>$v){
			if($v['id'] == $recruit['company_id']){
				$company_html .='<option value="'.$v['id'].'" selected="selected">'.$v['name'].'</option>';
			}else{
				$company_html .='<option value="'.$v['id'].'">'.$v['name'].'</option>';
			}
		}
		$this -> assign("company", $company_html);
		
		$position_sequence = M('PositionSequence')->field('id,sequence_name')->select();
		$position_sequence_html = '<option>请选择岗位序列</option>';
		foreach ($position_sequence as $k=>$v){
			if($v['id'] == $recruit['position_sequence_id']){
				$position_sequence_html .='<option value="'.$v['id'].'" selected="selected">'.$v['sequence_name'].'</option>';
			}else{
				$position_sequence_html .='<option value="'.$v['id'].'">'.$v['sequence_name'].'</option>';
			}
		}
		$this -> assign("position_sequence", $position_sequence_html);
		
		$qualifications = M('SimpleDataMapping')->field('data_code,data_name')->where(array('data_type'=>'学历','is_del'=>'0'))->select();
		$qualifications_html = '<option>请选择学历</option>';
		foreach ($qualifications as $k=>$v){
			if($v['data_code'] == $recruit['qualifications']){
				$qualifications_html .='<option value="'.$v['data_code'].'" selected="selected">'.$v['data_name'].'</option>';
			}else{
				$qualifications_html .='<option value="'.$v['data_code'].'">'.$v['data_name'].'</option>';
			}
		}
		$this -> assign("qualifications", $qualifications_html);
		
		$stuff_status = M('SimpleDataMapping')->field('data_code,data_name')->where(array('data_type'=>'员工状态','data_code'=>array('not in',array('98','99')),'is_del'=>'0'))->select();
		$stuff_status_html = '<option>请选择员工状态</option>';
		foreach ($stuff_status as $k=>$v){
			if($v['data_code'] == $recruit['stuff_status']){
				$stuff_status_html .='<option value="'.$v['data_code'].'" selected="selected">'.$v['data_name'].'</option>';
			}else{
				$stuff_status_html .='<option value="'.$v['data_code'].'">'.$v['data_name'].'</option>';
			}
		}
		$this -> assign("stuff_status", $stuff_status_html);
		
		$companys2 = M('Dept')->field('id,name')->where(array('pid'=>0))->select();
		$company_html2 = '<option>请选择公司</option>';
		foreach ($companys2 as $k=>$v){
			if($v['id'] == $recruit['social_security_company_id']){
				$company_html2 .='<option value="'.$v['id'].'" selected="selected">'.$v['name'].'</option>';
			}else{
				$company_html2 .='<option value="'.$v['id'].'">'.$v['name'].'</option>';
			}
		}
		$this -> assign("company2", $company_html2);
		$this -> assign("hr_specialist_id", get_user_id());
		$this -> assign("hr_specialist_name", get_user_name());
		
		$recruit_status = array('待报到','已报到','已建档','未报到');
		$interview_evaluation = array('','及格','良好','优秀');
		$recruit['company_name'] = M('Dept')->where(array('id'=>$recruit['company_id']))->getField('name');
		$recruit['dept_name'] = M('Dept')->where(array('id'=>$recruit['dept_id']))->getField('name');
		$recruit['position_name'] = M('Position')->where(array('id'=>$recruit['position_id']))->getField('position_name');
		$recruit['stuff_status'] = M('SimpleDataMapping')->where(array('data_type'=>'员工状态','data_code'=>$recruit['stuff_status'],'is_del'=>'0'))->getField('data_name');
		$recruit['hr_specialist_name'] = M('User')->where(array('id'=>$recruit['hr_specialist_id']))->getField('name');
		$recruit['recruit_status_name'] = $recruit_status[$recruit['recruit_status']];
// 		$recruit['position_sequence_name'] = M('PositionSequence')->where(array('id'=>$recruit['position_sequence_id']))->getField('sequence_name');
// 		$recruit['interview_evaluation'] = $interview_evaluation[$recruit['interview_evaluation']];
		$recruit['user_id'] = formatto4w($recruit['user_id']);
		$recruit['create_record_user_name'] = M('User')->where(array('id'=>$recruit['create_record_user_id']))->getField('name');
		$this -> assign("recruit", $recruit);
		$this -> display();
	}
	function set_recruit_status(){
		$id = $_POST['id'];
		$status = $_POST['status'];
		if($status == '2'){
			$recruit = M('Recruit')->find($id);
			$user['emp_no'] = create_emp_no($recruit['name']);
			$user['name'] = $recruit['name'];
			$user['letter'] = get_letter($recruit['name']);
			$user['password'] = md5('123456');
			$user['sex'] = $recruit['sex'];
			$user['pic'] = 'emp_pic/no_avatar.jpg';
			$user['mobile_tel'] = $recruit['mobile'];
			$user['create_time'] = time();
			$user['is_del'] = '0';
			$user_id = M('User')->add($user);
			if(false != $user_id){
				$r_user_position['user_id'] = $user_id;
				$r_user_position['position_id'] = $recruit['position_id'];
				$r_user_position['is_major'] = '1';
				$r_user_position['dept_id'] = $recruit['dept_id'];
				$r_user_position['position_sequence_id'] = $recruit['position_sequence_id'];
				$r_user_position_id = M('RUserPosition')->add($r_user_position);
				
				$r_dept_user['dept_id'] = $recruit['dept_id'];
				$r_dept_user['user_id'] = $user_id;
				$r_dept_user_id = M('RDeptUser')->add($r_dept_user);
				
				$status_manager['user_id'] = $user_id;
				$status_manager['no_status'] = '正常';
				$status_manager['stuff_status'] = M('SimpleDataMapping')->where(array('data_type'=>'员工状态','data_code'=>$recruit['stuff_status'],'is_del'=>'0'))->getField('data_name');
				$status_manager['entry_time'] = $recruit['entry_date'];
				$status_manager['create_time'] = date('Y/m/d H:i:s',time());
				$status_manager['create_name'] = get_user_name();
				$status_manager_id = M('StatusManage')->add($status_manager);
				
				if(false != $r_user_position_id && false != $r_dept_user_id && false != $status_manager_id){
					$res = M('Recruit')->where(array('id'=>$id))->save(array('emp_no'=>$user['emp_no'],'user_id'=>$user_id,'recruit_status'=>'2','create_record_user_id'=>get_user_id(),'create_record_time'=>date('Y-m-d')));
					if(false !== $res){
						$root_dept = getRootDept($recruit['dept_id']);
						if($root_dept['name'] == '金华基地'){
							$email_host = '@jhszkq.com';
						}else{
							$email_host = '@xyb2c.com';
						}
						$message['content']='新员工'.$recruit['name'].'，邮箱用户名：'.$user['emp_no'].$email_host.'；邮箱密码：*Pass123456 ；ERP登入名：'.$user['emp_no'].'，登入密码：123456，登入地址：http://oa.xyb2c.com';
						$message['sender_id']='1';
						$message['sender_name']='管理员';
						$message['create_time']=time();
						$position_ids = M('Position')->where(array('position_name'=>array('in',array('人事专员','网络运维工程师','网络管理员'))))->getField('id',true);
						$user_ids = M('RUserPosition')->where(array('position_id'=>array('in',$position_ids),'dept_id'=>array('in',get_child_dept_all($root_dept['id']))))->getField('user_id',true);
						foreach ($user_ids as $user_id){
							$message['receiver_id']=$user_id;
							$message['receiver_name']=M('User')->where(array('id'=>$user_id))->getField('name');
							$message['owner_id']='1';
							$list = D('Message') -> add($message);
							$message['owner_id']=$user_id;
							$list = D('Message') -> add($message);
							$this -> _pushReturn("", "您有新的消息, 请注意查收", 1,$user_id);
						}
						$data = array();
						$data['id'] = $id;
						$data['stuff_status'] = '2';
						$data['status'] = '1';
						$this->ajaxReturn($data);
					}
				}
			}
			$this->ajaxReturn(array('status'=>'0'));
		}else{
			$res = M('Recruit')->where(array('id'=>$id))->save(array('recruit_status'=>$status));
			if(false !== $res){
				$data['id'] = $id;
				$data['stuff_status'] = $status;
				$data['status'] = '1';
				$this->ajaxReturn($data);
			}
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
		
		$position_sequence = M('PositionSequence')->field('id,sequence_name')->select();
		$position_sequence_id_html = '<option>请选择岗位序列</option>';
		foreach ($position_sequence as $k=>$v){
			$position_sequence_id_html .='<option value="'.$v['id'].'">'.$v['sequence_name'].'</option>';
		}
		$this -> assign("position_sequence_id", $position_sequence_id_html);
		
		$qualifications = M('SimpleDataMapping')->field('data_code,data_name')->where(array('data_type'=>'学历','is_del'=>'0'))->select();
		$qualifications_html = '<option>请选择学历</option>';
		foreach ($qualifications as $k=>$v){
			$qualifications_html .='<option value="'.$v['data_code'].'">'.$v['data_name'].'</option>';
		}
		$this -> assign("qualifications", $qualifications_html);
		
		$stuff_status = M('SimpleDataMapping')->field('data_code,data_name')->where(array('data_type'=>'员工状态','data_code'=>array('not in',array('98','99')),'is_del'=>'0'))->select();
		$stuff_status_html = '<option>请选择员工状态</option>';
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
		$dept_id = $_POST['dept_id'];
		$depts = M('Dept')->field('id,pid,name')->where(array('id'=>array('in',get_child_dept_all($company_id)),'is_del'=>'0','is_use'=>'1'))->select();
		$tree = list_to_tree($depts);
		$data['dept'] = popup_menu_option($tree,0,$dept_id);
		$this->ajaxReturn($data,1,1);
	}
	function get_position_html(){
		$dept_id = $_POST['dept_id'];
		$position_id = $_POST['position_id'];
		$position_ids = M('RDeptPosition')->where(array('dept_id'=>$dept_id))->getField('position_id',true);
	
		$positions = M('Position')->field('id,position_name')->where(array('id'=>array('in',$position_ids),'is_del'=>'0','is_use'=>'1'))->select();
		$html = '<option>请选择岗位</option>';
		foreach ($positions as $k=>$v){
			if($v['id'] == $position_id){
				$html .= '<option value="'.$v['id'].'" selected="selected">'.$v['position_name'].'</option>';
			}else{
				$html .= '<option value="'.$v['id'].'">'.$v['position_name'].'</option>';
			}
		}
		$data['position'] = $html;
		$this->ajaxReturn($data,1,1);
	}
	function get_dept_search_html(){
		$company_ids = array_filter(explode(',',$_POST['company_id']));
		$depts_all = array();
		foreach ($company_ids as $company_id){
			$depts = M('Dept')->field('id,pid,name')->where(array('id'=>array('in',get_child_dept_all($company_id)),'is_del'=>'0','is_use'=>'1'))->select();
			$depts_all = array_merge($depts_all,$depts);
		}
		$tree = list_to_tree($depts_all);
		$data = popup_dept_search_checkbox($tree,0);
		$this->ajaxReturn($data,1,1);
	}
	function get_position_search_html(){
		$dept_ids = array_filter(explode(',',$_POST['dept_id']));
		$positions = D('DeptPositionView')->field('position_id,position_name')->where(array('dept_id'=>array('in',$dept_ids),'Position.is_del'=>'0'))->select();
		
		$position_search_html = '<ul>';
		foreach ($positions as $k=>$v){
			$position_search_html .= '<li>';
			$position_search_html .= '<img src="'.__ROOT__.'/Public/img/hl.png"/>';
			$position_search_html .= '<input type="checkbox" name="position_id[]" id="position_id_'.$v['position_id'].'" value="'.$v['position_id'].'">';
			$position_search_html .= '<label for="position_id_'.$v['position_id'].'">'.$v['position_name'].'</label>';
			$position_search_html .= '</li>';
		}
		$position_search_html .= '</ul>';
		$this->ajaxReturn($position_search_html,1,1);
	}
	function del() {
		$id = $_POST['id'];
		$this -> _del($id);
	}
	protected function _insert() {
		$model = D('Recruit');
		if(is_mobile_request()){
			unset($_GET['id']);
			unset($_GET['token']);
			if (false === $model -> create($_GET)) {
				$this -> error($model -> getError());
			}
		}else{
			if (false === $model -> create()) {
				$this -> error($model -> getError());
			}
		}
		$model->create_time = date('Y-m-d');
		/*保存当前数据对象 */
		$list = $model -> add();
		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('新增成功!'.$list);
		} else {
			$this -> error('新增失败!');
			//失败提示
		}
	}
}
?>
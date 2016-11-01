<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class ProfileAction extends CommonAction {
	protected $config=array('app_type'=>'common', 'action_auth' => array('upload' => 'read','reset_pwd'=>'read','password'=>'read','save'=>'read','resume'=>'read','resume_must'=>'read','addResume'=>'read','save_resume'=>'read','add_record'=>'read','save_record'=>'read','userlist'=>'read','user'=>'read','get_dept_child'=>'read','export_sign'=>'read','update_sign'=>'read'));
	
	function index(){	
		$auth = $this -> config['auth'];
		
		$user=D("UserView")->find(get_user_id());
		$this->assign("vo",$user);
		$this->display();
	}
	
	public function upload() {
		$this -> _upload();	
	}

	//重置密码
	public function reset_pwd(){
		$id = get_user_id();
		$password = $_REQUEST['password'];
		if ('' == trim($password)) {
			$this -> error('密码不能为空！');
		}
		$User = M('User');
		$User -> password = md5($password);
		$User -> id = $id;
		$result = $User -> save();
		
		if (false !== $result) {
			$ids = array();
			$info = $User->field('more_role')->find($id);
			//找上级用户
			if($info['more_role']){
				$p_user = $User->field('id')->find($info['more_role']);
				if($p_user){
					$ids[] = $p_user['id'];
				}
				//找兄弟用户
				$b_user = $User->field('id')->where(array('more_role'=>$p_user['id'],'id'=>array('neq',$id)))->select();
				if(!empty($b_user) && is_array($b_user)){
					foreach ($b_user as $k=>$v){
						$ids[] = $v['id'];
					}
				}
			}else{//找下级用户
				$c_user = $User->field('id')->where(array('more_role'=>$id))->select();
				if(!empty($c_user) && is_array($c_user)){
					foreach ($c_user as $k=>$v){
						$ids[] = $v['id'];
					}
				}
			}
			if(!empty($ids)){
				$result1 = $User->where(array('id'=>array('in',$ids)))->setField('password',md5($password));
			}
		}
		
		if (false !== $result && false !== $result1) {
			$this -> assign('jumpUrl', get_return_url());
			$this -> success("密码修改成功",U('login/logout'));
		} else {
			$this -> error('重置密码失败！');
		}
	}

	public function password(){	
		$this -> display();
	}

	function save(){
		$model = D("User");
		if(!empty($_POST)){//电脑端
			if (false === $model -> create()) {
				$this -> error($model -> getError());
			}
		}else{//手机端
			if (false === $model -> create($_GET)) {
				$this -> error($model -> getError());
			}
		}
		session('user_pic', $model->pic);
		// 更新数据
		$list = $model -> save();
		if (false !== $list) {
			//成功提示
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('编辑成功!');
		} else {
			//错误提示
			$this -> error('编辑失败!');
		}
		
	}
	/**
	 * 读取个人简历和履历
	 */
	function resume(){
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$id = $_REQUEST['id'];
		if(empty($id)){
			$id = get_user_id();
		}
		//简历
		$resume = M('user_resume');
		$list = $resume -> where(array('user_id' => $id)) -> find();//获取文件(简历)
		if(is_null($list)){
			$this -> addResume($id);
		}else{
			if($list['pic']){$list['pic'] = get_save_url() . $list['pic'];}
			foreach ($list as $k => $v){
				$info[$k] = explode('|',$v);
			}
			//教育经历
			foreach ($info['education'] as $v){
				$education[] = explode(',',$v);
			}
			foreach ($info['training'] as $v){
				$train[] = explode(',',$v);
			}
			foreach ($info['family'] as $v){
				$family[] = explode(',',$v);
			}
			foreach ($info['work_experience'] as $v){
				$work[] = explode(',',$v);
			}	
			$this->assign('list',$info);
			$this->assign('educa',$education);
			$this->assign('train',$train);
			$this->assign('family',$family);
			$this->assign('work',$work);
			$this->assign('id', 'jl_'.$id);
			//履历
			$record = M('user_record');
			$data_file = $record -> where(array('user_id' => $id))->find();
			foreach ($data_file as $k => $v){
				$data[$k] = explode('|',$v);
			}
			foreach ($data['discipline'] as $v){
				$discipline[] = explode(',',$v);
			}
			foreach ($data['promotion'] as $v){
				$promotion[] = explode(',',$v);
			}
			foreach ($data['performance'] as $v){
				$performance[] = explode(',',$v);
			}
			foreach ($data['award_punish'] as $v){
				$award_punish[] = explode(',',$v);
			}
			foreach ($data['study'] as $v){
				$study[] = explode(',',$v);
			}
			foreach ($data['part_time'] as $v){
				$part_time[] = explode(',',$v);
			}
			$this->assign('data',$data);
			$this->assign('disc',$discipline);
			$this->assign('prom',$promotion);
			$this->assign('perf',$performance);
			$this->assign('award',$award_punish);
			$this->assign('study',$study);
			$this->assign('part',$part_time);
			$this->display();
		}	
	}
	
	/**
	 * 强制员工填写个人简历
	 */
	public function resume_must(){
		$id = get_user_id();
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign('id', 'jl_'.$id);
		$this -> display();
	}
	/**
	 * 添加简历页面
	 */
	function addResume($id){
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign('id', 'jl_'.$id);
		$this -> display('add_resume');
	}
	/**
	 *添加个人简历 
	 */
	function save_resume(){
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$id = str_replace('jl_','',$_POST['id']); 
		$model = M("user_resume");
		if(!empty($_POST)){
			foreach ($_POST as $k => $v){
				if(is_array($v)){
					$_POST[$k] = array_filter($_POST[$k]);
				}
			}
			$data['flow_id'] = $flow_id;
			//1.基本情况
			$data['basic_info_1'] = $_POST['name'] . '|' . $_POST['sex'] . '|' . $_POST['age'] . '|' . $_POST['nation'];
			$data['basic_info_2'] = $_POST['hunyin'] . '|' . $_POST['zhengzhi'] . '|' . $_POST['jiguan'] . '|' . $_POST['hujkou'];
			$data['basic_info_3'] = $_POST['id_number'] . '|' . $_POST['hukou_add'];
			$data['basic_info_4'] = $_POST['xueli'] . '|' . $_POST['zhuanye'] . '|' . $_POST['xuewei'] . '|' . $_POST['zige'];
			$data['basic_info_5'] = $_POST['waiyu'] . '|' . $_POST['yuzhong'] . '|' . $_POST['jibie'] . '|' . $_POST['kouyu'];
			$data['basic_info_6'] = $_POST['jishuji'] . '|' . $_POST['zhengshu'] . '|' . $_POST['cj_gongzuo'];
			$data['basic_info_7'] = $_POST['phone'] . '|' . $_POST['email'] . '|' . $_POST['address'];
			//2.自我评价
			$data['appraisal_main'] = $_POST['ziwopingjia'];
			$data['skill_honor_hobby_expect'] = $_POST['jineng'] . '|' . $_POST['rongyu'] . '|' . $_POST['techang'] . '|' . $_POST['qiwang'];
			//3.主要教育经历
			for ($i = 0,$count=count($_POST['jiaoyu']); $i < $count; $i++) {
				$data['education'] .= $_POST['jiaoyu'][$i] . ',' . $_POST['yuanxiao'][$i] . ',' . $_POST['xueli_jl'][$i] . ',' . $_POST['zhuanye_jl'][$i] . ',' . $_POST['zhengshu_jl'][$i] . '|';
			}
			$data['education'] = rtrim($data['education'],'|');
			//4.主要培训经历
			for ($i = 0,$count=count($_POST['px_shijian']); $i < $count; $i++) {
				$data['training'] .= $_POST['px_shijian'][$i] . ',' . $_POST['px_neirong'][$i] . ',' . $_POST['px_jigou'][$i] . '|';
			}
			$data['training'] = rtrim($data['training'],'|');
			//5.家庭成员
			for ($i = 0,$count=count($_POST['benren_gx']); $i < $count; $i++) {
				$data['family'] .= $_POST['benren_gx'][$i] . ',' . $_POST['xingming'][$i] . ',' . $_POST['danwei'][$i] . '|';
			}
			$data['family'] = rtrim($data['family'],'|');
			$data['family_urgency'] = $_POST['jj_xingm'] . '|' . $_POST['jj_guanx'] . '|' . $_POST['jj_dizhi'] . '|' . $_POST['jj_phone'];
			//6.工作经历
			for ($i = 0,$count=count($_POST['gzjl_sj']); $i < $count; $i++) {
				$data['work_experience'] .= $_POST['gzjl_sj'][$i] . ',' . $_POST['gzjl_yy'][$i] . ',' . $_POST['gzjl_jj'][$i] . ',' . $_POST['gzjl_zw'][$i] . ',' . $_POST['gzjl_dy'][$i] . ',' . $_POST['gzjl_dx'][$i] . ',' . $_POST['gzjl_rs'][$i] . ',' . $_POST['gzjl_ms'][$i] . ',' . $_POST['gzjl_yj'][$i] . '|';
			}
			$data['work_experience'] = rtrim($data['work_experience'],'|');
			//直接上机评价
			$data['superior_estimate'] .= '';
			//签字
			$data['signature_time'] .= $_POST['qianming'];
			//头像
			$data['pic'] = $_POST['pic'];
			$data['add_file'] = $_POST['add_file'];
			$data['user_id'] = $id;
			//如果是第一次添加 就走一边流程
			if($_POST['opmode'] === 'add'){
				$model_flow = D('Flow');
				if (false === $model_flow -> create()) {
					$this -> error($model_flow -> getError());
				}
				$user_info = M('user')->find($id);
				$data_flow = array();
				$data_flow['name'] = $_POST['name'].'的简历';
				$FlowData = getFlowData(getParentid($id));
				$data_flow['confirm'] = $FlowData['confirm'];
				$data_flow['confirm_name'] = $FlowData['confirm_name'];
				$data_flow['user_id'] = $id;
				$data_flow['user_name'] = $_POST['name'];
				$FlowType = M('FlowType')->where(array('name'=>array('eq','简历')))->find();
				
				$data_flow['type'] = $FlowType['id'];
				$data_flow['opmode'] = 'add';
				$data_flow['step'] = 20;
				$data_flow['emp_no'] = $user_info['emp_no'];
				$data_flow['dept_id'] = $user_info['dept_id'];
				$dept =  M("Dept") -> find($user_info['dept_id']);
				$data_flow['dept_name'] = $dept['name'];
				$data_flow['type'] = 66;
				$data_flow['create_time'] = time();
				$flow_id = $model_flow->add($data_flow);
				$data['flow_id'] = $flow_id;
				if ($model -> add($data)) {
					//成功提示
					$this -> assign('jumpUrl', get_return_url());
					$this -> success('编辑成功!');
				} else {
					//错误提示
					$this -> error('编辑失败!');
				}
			}else{
				$rid = $model -> getByUser_id($id,'id');
				$data['id'] = $rid['id'];
				if ($model -> save($data)) {
					//成功提示
					$this -> assign('jumpUrl', get_return_url());
					$this -> success('编辑成功!');
				} else {
					//错误提示
					$this -> error('编辑失败!');
				}
			}
			
		}
	}
	//添加履历页面
	function add_record(){
		$id = $_REQUEST['id'];
		$this -> assign('id',$id);
		//履历
		$record = M('user_record');
		$data_file = $record -> where(array('user_id'=>$id))->find();
		foreach ($data_file as $k => $v){
			$data[$k] = explode('|',$v);
		}
		foreach ($data['discipline'] as $v){
			$discipline[] = explode(',',$v);
		}
		foreach ($data['promotion'] as $v){
			$promotion[] = explode(',',$v);
		}
		foreach ($data['performance'] as $v){
			$performance[] = explode(',',$v);
		}
		foreach ($data['award_punish'] as $v){
		$award_punish[] = explode(',',$v);
	}
	foreach ($data['study'] as $v){
		$study[] = explode(',',$v);
	}
	foreach ($data['part_time'] as $v){
		$part_time[] = explode(',',$v);
	}
	$this->assign('data',$data);
	$this->assign('disc',$discipline);
	$this->assign('prom',$promotion);
	$this->assign('perf',$performance);
	$this->assign('award',$award_punish);
	$this->assign('study',$study);
	$this->assign('part',$part_time);
	$this -> display();
	}
	/**
	 * 添加履历
	 */
	function save_record(){
		if(!empty($_POST)){
			foreach ($_POST as $k => $v){
				if(is_array($v)){
					$_POST[$k] = array_filter($_POST[$k]);
				}
			}
		}
		$data['user_id'] = $_POST['id'];
		$data['information'] = $_POST['entry'] . '|' . $_POST['become'] . '|' . $_POST['position'] . '|' . $_POST['tallest'];
		for ($i = 0,$count=count($_POST['year']); $i < $count; $i++) {
				$data['discipline'] .= $_POST['year'][$i] . ',' . $_POST['late'][$i] . ',' . $_POST['early'][$i] . ',' . $_POST['overtime'][$i] . ',' . $_POST['tiaoxiu'][$i] . ',' . $_POST['sabbatical'][$i] . ',' . $_POST['regime'][$i] . '|';
		}
		for ($i = 0,$count=count($_POST['category']); $i < $count; $i++) {
				$data['promotion'] .= $_POST['category'][$i] . ',' . $_POST['adjust'][$i] . ',' . $_POST['original'][$i] . ',' . $_POST['former_duty'][$i] . ',' . $_POST['salary'][$i] . ',' . $_POST['new_dept'][$i] . ',' . $_POST['new_duty'][$i] . ',' . $_POST['new_salary'][$i] . '|';
		}
		for ($i = 0,$count=count($_POST['data_san']); $i < $count; $i++) {
				$data['performance'] .= $_POST['data_san'][$i] . ',' . $_POST['dependent'][$i] . ',' . $_POST['achievements'][$i] . ',' . $_POST['on_duty'][$i] . ',' . $_POST['remark'][$i] . '|';
		}
		for ($i = 0,$count=count($_POST['data_si']); $i < $count; $i++) {
				$data['award_punish'] .= $_POST['data_si'][$i] . ',' . $_POST['cause'][$i] . ',' . $_POST['sum'][$i] . ',' . $_POST['award'][$i] . ',' . $_POST['execute'][$i] . '|';
		}
		for ($i = 0,$count=count($_POST['data_wu']); $i < $count; $i++) {
				$data['study'] .= $_POST['data_wu'][$i] . ',' . $_POST['details'][$i] . ',' . $_POST['organization'][$i] . ',' . $_POST['certificate'][$i] . ',' . $_POST['section'][$i] . ',' . $_POST['cost'][$i] . ',' . $_POST['agreement'][$i] . '|';
		}
		for ($i = 0,$count=count($_POST['data_liu1']); $i < $count; $i++) {
				$data['part_time'] .= $_POST['data_liu1'][$i] . ',' . $_POST['job1'][$i] . ',' . $_POST['data_liu2'][$i] . ',' . $_POST['job2'][$i] . '|';
		}
		$data['comment'] = $_POST['comment'];
		$model = M('user_record');
		$record = $model -> where(array('user_id'=>$data['user_id']))->find();
		if(empty($record)){
			if (M('user_record') -> add($data)) {
				//成功提示
				$this -> assign('jumpUrl', get_return_url());
				$this -> success('编辑成功!');
			} else {
				//错误提示
				$this -> error('编辑失败!');
			}
		}else{
			$data['id'] = $record['id'];
			if (M('user_record') -> save($data)) {
				//成功提示
				$this -> assign('jumpUrl', get_return_url());
				$this -> success('编辑成功!');
			} else {
				//错误提示
				$this -> error('编辑失败!');
			}
		}
	}
	public function userlist(){
		//管理员可以查看所有人，普通人只能查看自己及以下
		$auth = $this -> config['auth'];
// 		dump($auth);
		if ($auth['admin']) {
			$where = array();
		}else{
			$child_ids = array_merge(array(get_user_id()),get_child_ids_all(get_user_id()));
			$where['id'] = array('in',$child_ids);
		}
		//搜索条件
		if (!empty($_POST['li_name'])) {
			$where['name'] = array('like', '%'.$_POST['li_name'].'%');
		}
		if (!empty($_POST['is_del'])) {
			$where['is_del'] = $_POST['is_del'];
		}
		if (!empty($_POST['dept_name_multi_data'])) {
			$dept_id_mul = $_POST['dept_name_multi_data'];
			$dept_id_mul = array_filter(explode('|',$dept_id_mul));
			$dept_ids = array();
			foreach ($dept_id_mul as $dept_id){
				$dept_ids = array_merge($dept_ids,get_child_dept_all($dept_id));
			}
			$where['pos_id'] = array('in', $dept_ids);
		}
		if (!empty($_POST['pos_name_multi_data'])) {
			$pos_id_mul = $_POST['pos_name_multi_data'];
			$pos_id_mul = array_filter(explode('|',$pos_id_mul));
			$pos_ids = array();
			foreach ($pos_id_mul as $pos_id){
				$pos_ids = array_merge($pos_ids,get_child_dept_all($pos_id));
			}
			$where['pos_id'] = array('in', $pos_ids);
		}
		//取出数据
		$users = $this->_list(D("UserView"), $where,'id',true);
		foreach ($users as $k=>$v){
			$pos_id = M('Dept')->field('name')->find($v['pos_id']);
			$users_extension[$k]['pos_name'] = $pos_id['name'];
			if(!empty($v['more_role'])){
				$users_extension[$k]['more_role'] = '是';
			}else{
				$users_extension[$k]['more_role'] = '否';
			}
		}
		$this->assign("users_extension",$users_extension);
		//选择部门的内容
		$node = D("Dept");
		$dept_menu = $node -> field('id,pid,name') -> where("is_del=0 and is_real_dept=1") -> order('sort asc') -> select();
		$dept_tree = list_to_tree($dept_menu);
		if(!is_mobile_request()){
			$this -> assign('dept_list_new', select_tree_menu_mul($dept_tree));
		}
		$this->display();
	}
	public function user(){
		//管理员可以查看所有人，普通人只能查看自己及以下
		$id = $_REQUEST['id'];
		$auth = $this -> config['auth'];
		if (!$auth['admin']) {
			$child_ids = array_merge(array(get_user_id()),get_child_ids_all(get_user_id()));
			if(!in_array($id,$child_ids)){
				$this->error('无权查看');
			}
		}
		$user=D("UserView")->find($id);
		$pos_name = M('Dept')->field('name')->find($user['pos_id']);
		$user['pos_name'] = $pos_name['name'];
		$user['more_role'] = $user['more_role']?'是':'否';
// 		dump($user);
		$this->assign("vo",$user);
		
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		
		if(empty($id)){
			$id = get_user_id();
		}
		//简历
		$resume = M('user_resume');
		$list = $resume -> where(array('user_id' => $id)) -> find();//获取文件(简历)
		if(is_null($list)){
			$this->assign('id', 'jl_'.$id);
			if($id==get_user_id()){
				$this->assign('can_modify_user_resume',1);
			}else{
				$this->assign('can_modify_user_resume',0);
			}
			
			if($auth['admin']){
				$this->assign('can_modify_user_record',1);
			}else{
				$this->assign('can_modify_user_record',0);
			}
		}else{
			if($list['pic']){$list['pic'] = get_save_url() . $list['pic'];}
			foreach ($list as $k => $v){
				$info[$k] = explode('|',$v);
			}
			//教育经历
			foreach ($info['education'] as $v){
				$education[] = explode(',',$v);
			}
			foreach ($info['training'] as $v){
				$train[] = explode(',',$v);
			}
			foreach ($info['family'] as $v){
				$family[] = explode(',',$v);
			}
			foreach ($info['work_experience'] as $v){
				$work[] = explode(',',$v);
			}
			$this->assign('list',$info);
// 			echo $info['pic'][0];
			$this->assign('educa',$education);
			$this->assign('train',$train);
			$this->assign('family',$family);
			$this->assign('work',$work);
			$this->assign('id', 'jl_'.$id);
			if($id==get_user_id()){
				$this->assign('can_modify_user_resume',1);
			}else{
				$this->assign('can_modify_user_resume',0);
			}
			
			//履历
			$record = M('user_record');
			$data_file = $record -> where(array('user_id' => $id))->find();
			foreach ($data_file as $k => $v){
				$data[$k] = explode('|',$v);
			}
			foreach ($data['discipline'] as $v){
				$discipline[] = explode(',',$v);
			}
			foreach ($data['promotion'] as $v){
				$promotion[] = explode(',',$v);
			}
			foreach ($data['performance'] as $v){
				$performance[] = explode(',',$v);
			}
			foreach ($data['award_punish'] as $v){
				$award_punish[] = explode(',',$v);
			}
			foreach ($data['study'] as $v){
				$study[] = explode(',',$v);
			}
			foreach ($data['part_time'] as $v){
				$part_time[] = explode(',',$v);
			}
			$this->assign('data',$data);
			$this->assign('disc',$discipline);
			$this->assign('prom',$promotion);
			$this->assign('perf',$performance);
			$this->assign('award',$award_punish);
			$this->assign('study',$study);
			$this->assign('part',$part_time);
			if($auth['admin']){
				$this->assign('can_modify_user_record',1);
			}else{
				$this->assign('can_modify_user_record',0);
			}
			
		}
		//考勤动态
		//打卡
		C('VAR_PAGE','p_attendance');//p的名字变一下
		$attendance = M('Attendance')->where(array('user_id'=>$id,'is_del'=>0,'mark'=>array('in',array('in','out'))))->select();

		$this->_list(M('Attendance'), array('user_id'=>$id,'is_del'=>0,'mark'=>array('in',array('in','out'))),'attendance_time',false,'attendance','page_attendance');

// 		$this->assign('attendance',$attendance);
		
		C('VAR_PAGE','p_attendance_table');//p的名字变一下
		//考勤统计
		$attendance_table = M('AttendanceTable')->where(array('user_id'=>$id))->select();
		//dump(D('AttendanceTableView')->where(array('user_id'=>$id))->select());

		$this->_list(D('AttendanceTableView'), array('user_id'=>$id),'',false,'attendance_table','page_attendance_table');
// 		$this->assign('attendance_table',$attendance_table);
		

		//dump(D('AttendanceTableView')->select());die;
		C('VAR_PAGE','p');//p的名字变回来
		$this->assign('profile_user_index',cookie('profile_user_index'));
		
		//签入数据
		$signdata = M('SignInOut')->where(array('user_id'=>$id))->order('time asc')->select();
		foreach ($signdata as $k=>$v){
			$date = date('Y-m-d',$v['time']);
			$signdata_new[$date][$v['type']] = date('Y-m-d H:i:s',$v['time']);
		}
		$this->assign('signdata_new',$signdata_new);
		$this->assign('month',$month);
		$this->assign('signdata_count',count($signdata_new));
		$this->display();
	}
	public function export_sign(){
		//导入thinkphp第三方类库
		Vendor('Excel.PHPExcel');
		
		$objPHPExcel = new PHPExcel();
		
		$objPHPExcel -> getProperties() -> setCreator("小微OA") -> setLastModifiedBy("小微OA") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
		// Add some data
		
		$id = $_GET['id']?$_GET['id']:get_user_id();
		$user = D('UserView')->field('dept_name,name')->find($id);
		$dept_name = $user['dept_name'];
		$user_name = $user['name'];
		//签入数据
		$signdata = M('SignInOut')->where(array('user_id'=>$id))->order('time asc')->select();
		foreach ($signdata as $k=>$v){
			$date = date('Y-m-d',$v['time']);
			$signdata_new[$date][$v['type']] = date('Y-m-d H:i:s',$v['time']);
		}
		
		$q = $objPHPExcel -> setActiveSheetIndex(0);
		//第一列为用户
		$q = $q -> setCellValue("A1", '签入时间');
		$q = $q -> setCellValue("B1", '签出时间');
		$q = $q -> setCellValue("C1", '序号：'.$id);
		$q = $q -> setCellValue("D1", '部门：'.$dept_name);
		$q = $q -> setCellValue("E1", '姓名：'.$user_name);
		
		$i = 2;
		foreach ($signdata_new as $k=>$v){
			$q = $q -> setCellValue("A".$i, $v['in']);
			$q = $q -> setCellValue("B".$i, $v['out']);
			$i++;
		}
		$q ->getColumnDimension('A')->setWidth(20);
		$q ->getColumnDimension('B')->setWidth(20);
		$q ->getColumnDimension('C')->setWidth(20);
		$q ->getColumnDimension('D')->setWidth(20);
		// Rename worksheet
		$title = 'oa签入数据导出';
		$objPHPExcel -> getActiveSheet() -> setTitle('oa签入数据导出');
		
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel -> setActiveSheetIndex(0);
		$file_name = $title.".xlsx";
		// Redirect output to a client’s web browser (Excel2007)
		header("Content-Type: application/force-download");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition:attachment;filename =" . str_ireplace('+', '%20', URLEncode($file_name)));
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		//readfile($filename);
		$objWriter -> save('php://output');
		exit ;
	}

	function update_sign(){
		$id = $_POST['id'];
		$data=M("AttendanceTable")->where(array('id'=>$id))->setField('sign','1');
		exit(json_encode($data));
	}
}
?>
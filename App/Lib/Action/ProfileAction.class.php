<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class ProfileAction extends CommonAction {
	protected $config=array('app_type'=>'personal');
	
	function index(){	
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
			$this -> assign('jumpUrl', get_return_url());
			$this -> success("密码修改成功");
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
			//履历
			$record = M('user_record');
			$user = M('user');
			$file = M('file');
			$list = $user -> where(array('id'=>$id)) -> getField('add_file');
			$files = explode(';',rtrim($list,';'));
			$token = end($files);
			$sname = $file -> where(array('sid'=>$token)) -> getField('savename');
			$data_file = $record -> where(array('path'=>'Data/Files/'.$sname))->find();
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
			if ($model -> add($data)) {
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
?>
<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa        
  
 -------------------------------------------------------------------------*/
header("Access-Control-Allow-Origin:*");
/*星号表示所有的域都可以接受，*/
header("Access-Control-Allow-Methods:GET,POST");
class LoginAction extends Action {
	protected $config=array('app_type'=>'public');

	public function index(){
		//如果通过认证跳转到首页
		$this->assign("js_file","js/index");
		$this->assign("title",get_system_config("SYSTEM_NAME"));
		$this->assign("is_verify_code",get_system_config("IS_VERIFY_CODE"));
		
		if(is_mobile_request()){//手机端
			$id = $_REQUEST['id'];
			$token = $_REQUEST['token'];
			if(!empty($id) && !empty($token)){
				$map = array();
				$map["id"] = array('eq', intval($id));
				$model = M("User");
				$auth_info = $model -> where($map) -> find();
				
				if(md5($auth_info['password'].md5($auth_info['last_mobile_login_time'])) == $token && time()-$auth_info['last_mobile_login_time']<C('MOBILE_TOKEN_LIFETIME')){
					$auth_id = $id;
				}else{
					$this->assign('auto_auth_fail','自动登录失败，请前往登录页面');
				}
			}
		}else{
			$auth_id = session(C('USER_AUTH_KEY'));
		}
		
		if (!isset($auth_id)) {
			$this -> display();
		} else {//手机端不带参数的话会跳到login/index,也就是跳回来
			header('Location: ' .__APP__);
		}
	}

	// 用户登出
	public function logout() {
		if(is_mobile_request()){
			$id = $_REQUEST['id'];
			$map["id"] = array('eq', intval($id));
			$model = M("User");
			$res = $model -> where($map) -> setField('last_mobile_login_time','');
			if ($res) {
				$this -> assign("jumpUrl", __URL__ );
				$this -> success('退出成功！');
			} else {
				$this -> assign("jumpUrl", __URL__);
				$this -> error('退出失败！');
			}
		}else{
			$auth_id = session(C('USER_AUTH_KEY'));
			if (isset($auth_id)) {
				session(C('USER_AUTH_KEY'), null);
				session('user_pic', null);
				$this -> assign("jumpUrl", __URL__ );
				$this -> success('退出成功！');
			} else {
				$this -> assign("jumpUrl", __URL__);
				$this -> error('已经退出！');
			}
		}
		
	}

	// 登录检测
	public function check_login(){
		$is_verify_code=get_system_config("IS_VERIFY_CODE");
		if(!empty($is_verify_code)){
			if(session('verify') != md5($_REQUEST['verify'])) {
				 $this->error('验证码错误！');
			}
		}
		if (empty($_REQUEST['emp_no'])) {
			$this -> error('帐号必须！');
		} elseif (empty($_REQUEST['password'])) {
			$this -> error('密码必须！');
		}
		if($_REQUEST['remember'] == '1'){//记住密码
			session('remember_emp_no',$_REQUEST['emp_no']);
			session('remember',$_REQUEST['password']);
			session('is_remember_password',true);
		}else{
			session('remember_emp_no',null);
			session('remember',$_REQUEST['password']);
			session('is_remember_password',false);
		}
		if ($_REQUEST['emp_no'] == 'admin'){
			$is_admin=true;
			session(C('ADMIN_AUTH_KEY'), true);
		}

		if(C("LDAP_LOGIN")&&!$is_admin){
			$ldap_host = C("LDAP_SERVER");//LDAP 服务器地址
			$ldap_port = C("LDAP_PORT");//LDAP 服务器端口号
			$ldap_user = "uid=".$_REQUEST['emp_no'].",cn=users,dc=laxdn,dc=com,dc=cn";
			$ldap_pwd = $_REQUEST['password']; //设定服务器密码

			$ldap_conn = ldap_connect($ldap_host, $ldap_port) //建立与 LDAP 服务器的连接
			or die("Can't connect to LDAP server");
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION,3);
			$r=ldap_bind($ldap_conn, $ldap_user, $ldap_pwd);//与服务器绑定			
			if($r){
				$map['emp_no'] = $_REQUEST['emp_no'];
				$map["is_del"] = array('eq', 0);
				$model = M("User");
				$auth_info = $model -> where($map) -> find();
			}else{
				$this->error(ldap_error($ldap_conn));
			}
		}else{
			$map = array();
			// 支持使用绑定帐号登录
			$map['emp_no'] = $_REQUEST['emp_no'];
			$map["is_del"] = array('eq', 0);
			$map['password']=array('eq',md5($_REQUEST['password']));
			$model = M("User");
			$auth_info = $model -> where($map) -> find();
		}

		//使用用户名、密码和状态的方式进行认证
		if (false == $auth_info){
			$this -> error('帐号或密码错误！');
		} else {
			session(C('USER_AUTH_KEY'),$auth_info['id']);
			session('user_id',$auth_info['id']);
			session('emp_no', $auth_info['emp_no']);
			session('user_name', $auth_info['name']);
			session('dept_id', $auth_info['dept_id']);
			
			//保存登录信息
			$User = M('User');
			$ip = get_client_ip();
			$time = time();
			$data = array();
			$data['id'] = $auth_info['id'];
			$data['last_login_time'] = $time;
			$data['login_count'] = array('exp', 'login_count+1');
			$data['last_login_ip'] = $ip;
			
			if(is_mobile_request()){//如果是手机端登录，则返回id和token
				$data['last_mobile_login_time'] = $time;
				$User -> save($data);
				$this -> assign('jumpUrl', U("index/index"));
				$this -> assign('id', $auth_info['id']);
				$this -> assign('token', md5($auth_info['password'].md5($time)));
				$this -> display();
			}
			$User -> save($data);
			$this -> assign('jumpUrl', U("index/index"));
			header('Location: ' .U("index/index"));
		}
	}

	public function verify() {
		$type = isset($_GET['type']) ? $_GET['type'] : 'gif';
		import("@.ORG.Util.Image");
		Image::buildImageVerify(4,1, $type);
	}
	public function client_login(){
		$user_name = $_POST['user_name'];
		$password = $_POST['password'];
		if(empty($user_name) && empty($password)){
			$id = session(C('USER_AUTH_KEY'));
			if(!empty($id)){//session中有
				$data = array('id'=>$id,'status'=>1);
				$this->ajaxReturn($data,'JSON');
			}
			else{
				$data = array('status'=>0,'msg'=>'session中获取不到，请输入用户名和密码登录！');
				$this->ajaxReturn($data,'JSON');
			}
		}elseif(empty($user_name) || empty($password)){
			$data = array('status'=>0,'msg'=>'用户名或密码为空！');
			$this->ajaxReturn($data,'JSON');
		}else{
			$map = array();
			$map['emp_no'] = $user_name;
			$map["is_del"] = array('eq', 0);
			$map['password']=array('eq',md5($password));
			$model = M("User");
			$auth_info = $model -> where($map) -> find();
			if (false == $auth_info){
				$data = array('status'=>0,'msg'=>'用户名或密码错误！');
				$this->ajaxReturn($data,'JSON');
			} else {
				session(C('USER_AUTH_KEY'),$auth_info['id']);
				session('emp_no', $auth_info['emp_no']);
				session('user_name', $auth_info['name']);
				session('user_pic', $auth_info['pic']);
				session('dept_id', $auth_info['dept_id']);
			
				//保存登录信息
				$User = M('User');
				$ip = get_client_ip();
				$time = time();
				$data_user = array();
				$data_user['id'] = $auth_info['id'];
				$data_user['last_login_time'] = $time;
				$data_user['login_count'] = array('exp', 'login_count+1');
				$data_user['last_login_ip'] = $ip;
				$User -> save($data_user);
// 				$token = md5($auth_info['id'].$time);
				$data = array('id'=>$auth_info['id'],'status'=>1);
				$this->ajaxReturn($data,'JSON');
			}
		}
	}
	public function client_get_msgtask(){
		$id = $_POST['id'];
		$id_in = session(C('USER_AUTH_KEY'));
		if(empty($id_in)){
			$data = array('status'=>0,'msg'=>'请先登录');
			$this->ajaxReturn($data,'JSON');
		}
		elseif(!empty($id)){
			if($id!=$id_in){
				$data = array('status'=>0,'msg'=>'登录的id与请求的id不同！');
				$this->ajaxReturn($data,'JSON');
			}
		}else{
			$id = $id_in;
		}
		$model_message = D('message');
		$where = array('receiver_id'=>$id,'owner_id'=>$id,'is_del'=>0,'is_read'=>0);
		$message = $model_message->where($where)->select();
		
		$model_task = D('task');
		
		$where_log['type'] = 1;
		$where_log['status'] = 0;
		$where_log['executor'] = $id;
		$task_list = M("TaskLog") -> where($where_log) -> getField('task_id id,task_id');
		$where = array();
		$where['id'] = array('in', $task_list);
		
// 		$where = array('executor'=>array('like','%|'.$id.';%'));
		$task = $model_task->where($where)->field('id,name,content,executor,add_file')->select();
		if(empty($task)){
			$task = null;
		}
		//获取待裁决
		$where = array();
		$FlowLog = M("FlowLog");
		
		$where['emp_no'] = get_emp_no();
		$where['_string'] = "result is null";
		$log_list = $FlowLog -> where($where) -> field('flow_id') -> select();
		
		$log_list = rotate($log_list);
		$new_confirm_count = 0;
		if (!empty($log_list)) {
			$map['id'] = array('in', $log_list['flow_id']);
			$new_confirm_count = M("Flow") -> where($map) -> count();
		}
		//流程通过提示
		$flow_id = M('Flow')->field('id')->where(array('user_id'=>$id))->select();
		$flow_id = rotate($flow_id);
		$flow_id = $flow_id['id'];
		$time = time();
		$where = array();
		$where['flow_id'] = array('in',$flow_id);
		$where['_string'] = "result is not null";
		$where['update_time'] = array('between',array($time-30,$time));
		$log_message_list = $FlowLog -> field('user_name,result') -> where($where) -> select();
		$flow_pass_message = '';
		if(!empty($log_message_list)){
			foreach ($log_message_list as $v){
				$result = $v['result']=='1'?'同意':'否决';
				$flow_pass_message .=$v['user_name'].'已'.$result.'了您的流程 ';
			}
		}
		
		$data = array('data'=>array('message'=>array('data'=>$message,'count'=>count($message)),'task'=>array('data'=>$task,'count'=>count($task)),'flow'=>array('count'=>$new_confirm_count,'flow_pass_message'=>$flow_pass_message)),'status'=>1);
		$this->ajaxReturn($data,'JSON');
	}
	public function change_user(){
// 		$_POST['id'];
		if(!empty($_POST['id']) && !empty($_POST['emp_no']) && !empty($_POST['name']) && !empty($_POST['dept_id'])){
			session(C('USER_AUTH_KEY'),$_POST['id']);
			session('user_id',$_POST['id']);
			session('emp_no', $_POST['emp_no']);
			session('user_name', $_POST['name']);
			session('dept_id', $_POST['dept_id']);
				
			//保存登录信息
			$User = M('User');
			$ip = get_client_ip();
			$time = time();
			$data = array();
			$data['id'] = $_POST['id'];
			$data['last_login_time'] = $time;
			$data['login_count'] = array('exp', 'login_count+1');
			$data['last_login_ip'] = $ip;
				
// 			if(is_mobile_request()){//如果是手机端登录，则返回id和token
// 				$data['last_mobile_login_time'] = $time;
// 				$User -> save($data);
// 				$this -> assign('jumpUrl', U("index/index"));
// 				$this -> assign('id', $auth_info['id']);
// 				$this -> assign('token', md5($auth_info['password'].md5($time)));
// 				$this -> display();
// 			}
			$User -> save($data);
			$this ->ajaxReturn(1,1,1);
		}
		$this ->ajaxReturn(0,0,0);
	}
	public function check_user_name(){
		if($_REQUEST['user_name']){
			$user = M('User')->field('id')->where(array('emp_no'=>$_REQUEST['user_name'],'is_del'=>'0'))->find();
			if($user){
				$this ->ajaxReturn(1,$user['id'],1);
			}
		}
		$this ->ajaxReturn(0,null,0);
	}
}
?>
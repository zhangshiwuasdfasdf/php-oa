<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

// 后台用户模块
class UserAction extends CommonAction {
	protected $config=array('app_type'=>'master','action_auth'=>array('password'=>'admin','reset_pwd'=>'admin'));
	
	function _search_filter(&$map) {
		if (!empty($_POST['keyword'])) {
			$map['name|emp_no'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}
	

	public function index(){
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$widget['date'] = true;					
		$this -> assign("widget", $widget);
		$model = M("Position");
		$list = $model -> where('is_del=0') -> order('sort asc') -> getField('id,name');
		$this -> assign('position_list', $list);

		$model = M("Rank");
		$list = $model -> where('is_del=0') -> order('sort asc') -> getField('id,name');
		$this -> assign('rank_list', $list);

		$model = M("Dept");
		$list = $model -> where('is_del=0') -> order('sort asc') -> getField('id,name');
		$this -> assign('dept_list', $list);

		$model = M("Rank");
		$list = $model -> where('is_del=0') -> order('sort asc') -> getField('id,name');
		$this -> assign('rank_list', $list);
				
		if (isset($_POST['eq_is_del'])){					
			$eq_is_del = $_POST['eq_is_del'];			
		} else{
			$eq_is_del="0";
		}
		//die;
		$this->assign('eq_is_del',$eq_is_del);

		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$map['is_del']=array('eq',$eq_is_del);	
		$name = $this -> getActionName();
		$model = D($name);
		if (!empty($model)) {
			$this -> _list($model, $map,"emp_no",true);
		}
		$this -> display();							
	}
	
	public function add() {
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$widget['date'] = true;	
		$this -> assign("widget", $widget);
		
		$model = M("Position");
		$list = $model -> where('is_del=0') -> order('sort asc') -> getField('id,name');
		$this -> assign('position_list', $list);
		
		$model = M("Rank");
		$list = $model -> where('is_del=0') -> order('sort asc') -> getField('id,name');
		$this -> assign('rank_list', $list);

		$model = M("Dept");
		$list = $model -> where('is_del=0') -> order('sort asc') -> getField('id,name');
		$this -> assign('dept_list', $list);

		$this -> display();
	}

	// 检查帐号
	public function check_account() {
		if (!preg_match('/^[a-z]\w{4,}$/i', $_POST['emp_no'])) {
			$this -> error('用户名必须是字母，且5位以上！');
		}
		$User = M("User");
		// 检测用户名是否冲突
		$name = $_REQUEST['emp_no'];
		$result = $User -> getByAccount($name);
		if ($result) {
			$this -> error('该编码已经存在！');
		} else {
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('该编码可以使用！');
		}
	}

	// 插入数据
	public function _insert() {
		// 创建数据对象
		$model = D("User");
		if (!$model -> create()) {
			$this -> error($model -> getError());
		} else {
			// 写入帐号数据
			$model ->letter=get_letter($model ->name);
			$model ->password=md5('123456');
			$model -> pic = 'emp_pic/no_avatar.jpg';
			if ($result = $model -> add()){
				$data['id']=$result;
				M("UserConfig")->add($data);
				
				$this -> assign('jumpUrl', get_return_url());
				$this -> success('用户添加成功！');
			} else {
				$this -> error('用户添加失败！');
			}
		}
	}
	
	public  function demo(){
		echo get_return_url();
	}
	//添加新用户
	public function  addUser(){
		if(!empty($_FILES['resume']['name'])){
			import("@.ORG.Util.UploadFile");
			$upload = new UploadFile();
			$upload -> subFolder = strtolower(MODULE_NAME);
			$upload -> savePath = get_save_path();
			$upload -> saveRule = "uniqid";
			$upload -> autoSub = true;
			$upload -> subType = "date";
			$upload->allowExts  = array('xlsx','xls');// 设置附件上传类型
			if(!$upload->upload()) {// 上传错误提示错误信息
				$this->error($upload->getErrorMsg());
			 }else{// 上传成功 获取上传文件信息
				$info =  $upload->getUploadFileInfo();
				$file_info = $info[0];
				$file_path = $file_info['savepath'] . $file_info['savename'];
				 // 创建数据对象
				$model = D("User");
				if (!$model -> create()) {
					$this -> error($model -> getError());
				} else {
					// 写入帐号数据
					$model ->letter=get_letter($model ->name);
					$model -> pic = 'emp_pic/no_avatar.jpg';
					$model ->password=md5('123456');
					$model ->add_resume = $file_path;
					if ($result = $model -> add()){
						$data['id']=$result;
						M("UserConfig")->add($data);
						resume_upload($file_path,$result);//调用xlsx文件上传方法
	
						$model = D("Role");
						$model -> del_role($result);
						$role_list = $model->where(array('name'=>array('eq','基本权限')))->getField('id');
						$result = $model -> set_role($result, $role_list);
						
						$this -> assign('jumpUrl', get_return_url());
						$this -> success('用户添加成功！','index.php?m=user&a=index');
					} else {
						$this -> error('用户添加失败！');
					}
				}
			 }
		}else{
			$model = D("User");
			if (!$model -> create()) {
				$this -> error($model -> getError());
			} else {
				// 写入帐号数据
				$model ->letter=get_letter($model ->name);
				$model ->password=md5('123456');
				if ($result = $model -> add()){
					$data['id']=$result;
					M("UserConfig")->add($data);

					$model = D("Role");
					$model -> del_role($result);
					$role_list = $model->where(array('name'=>array('eq','基本权限')))->getField('id');
					$result = $model -> set_role($result, $role_list);
					
					$this -> assign('jumpUrl', get_return_url());
					$this -> success('用户添加成功！','index.php?m=user&a=index');
				} else {
					$this -> error('用户添加失败！');
				}
			}
		}
		 
		
	}
	function upload() {
		$this -> _upload(true);
	}
	
	function _update() {
		$name = $this -> getActionName();
		$model = D($name);
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		// 更新数据
		$model -> __set('letter', get_letter($model -> __get('name')));
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

	protected function add_role($user_id) {
		//新增用户自动加入相应权限组
		$RoleUser = M("RoleUser");
		$RoleUser -> user_id = $user_id;
		// 默认加入网站编辑组
		$RoleUser -> role_id = 3;
		$RoleUser -> add();
	}

	//重置密码
	public function reset_pwd() {
		$id = $_POST['user_id'];
		$password = $_POST['password'];
		if ('' == trim($password)) {
			$this -> error('密码不能为空!');
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
	
	public function password() {
		$this -> assign("id", $_REQUEST['id']);
		$this -> display();
	}
	
	function json() {
		header("Content-Type:text/html; charset=utf-8");
		$key = $_REQUEST['key'];

		$model = M("User");
		$where['name'] = array('like', "%" . $key . "%");
		$where['emp_no'] = array('like', "%" . $key . "%");
		$where['_logic'] = 'or';
		$map['_complex'] = $where;
		$list = $model -> where($map) -> field('id,name') -> select();
		exit(json_encode($list));
	}

	function del(){
		$id=$_POST['id'];
		$this->_destory($id);		
	}
}
?>
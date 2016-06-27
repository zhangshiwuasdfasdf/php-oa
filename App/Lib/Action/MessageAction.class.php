<?php
class MessageAction extends CommonAction {
	protected $config = array('app_type' => 'personal');
	//过滤查询字段
	function _filter(&$map){
		$map['is_del'] = array('eq', '0');
		$map['owner_id'] = get_user_id();
		if (!empty($_REQUEST['keyword'])) {
			$map['content'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}
	
	function add() {
		$widget['editor'] = true;
		$widget['uploader'] = true;
		//查询最近联系人
		$con = M('Message_contacts');
		$where['sender_id'] = get_user_id();
		$list = $con -> where($where) -> field('receiver_id,receiver_name')->find();
		$sid = empty($list['receiver_id']) ? null : explode('|',rtrim($list['receiver_id'],'|'));
		$rec_name = empty($list['receiver_name']) ? null : explode('|',rtrim($list['receiver_name'],'|'));
		$list = array_reverse(array_combine($sid,$rec_name),true);
		//查询部门联系人
		$node = D("Dept");
		$menu = array();
		$menu = $node -> field('id,pid,name') ->where("is_del=0")-> order('sort asc') -> select();
		$tree = list_to_tree($menu);
		if(is_mobile_request()){
			$this -> assign('menu', popup_tree_menu($tree,0,4));
		}else{
			$this -> assign('menu', popup_tree_menu($tree,0,5));
		}
		$this -> assign("contact", $list);		
		$this -> assign("widget", $widget);	
		$this -> display();
	}
	public function getUserByDept_id(){
		$dept_id = $_REQUEST['dept_id'];
		
		$dept = tree_to_list(list_to_tree(M("Dept") ->where('is_del=0')-> select(), $dept_id));
		$dept = rotate($dept);
		$dept = implode(",", $dept['id']) . ",$dept_id";
		$model = D("UserView");
		$where['is_del'] = array('eq', '0');
		$where['pos_id'] = array('in', $dept);
		$user = $model -> where($where)->order('duty asc') -> select();
		
		$this -> assign("user", $user);
		$this -> display();
	}
	//根据部门id查询员工
	public function staffList(){
		$id = $_REQUEST['id'];
		$user = M('user');
		$sql = "SELECT `id`,`name` FROM `smeoa_user` WHERE (`pos_id` = '$id' OR `pos_id` = '_$id' )";
		$list = $user -> query($sql);
		$this -> ajaxReturn($list, "", 1);
		
	}

	public function index(){
		//列表过滤器，生成查询Map对象
		$model = D("Message");
		if (empty($_POST['keyword'])){
			$list = $model -> get_list();
// 			手机端提供照片
			foreach ($list as $k=>$v){
				if($v['owner_id'] == $v['sender_id']){
					$user = M('User')->find($v['receiver_id']);
					$list[$k]['pic'] = $user['pic'];
				}elseif($v['owner_id'] == $v['receiver_id']){
					$user = M('User')->find($v['sender_id']);
					$list[$k]['pic'] = $user['pic'];
				}
			}
			$this -> assign('list', $list);
		} else {
			if (method_exists($this, '_filter')) {
				$this -> _filter($map);
			}
			if (!empty($model)) {
				$res = $this -> _list($model, $map);
			}
		}
		$this->assign('user_id',get_user_id());
		$this->assign('owner_id',get_user_id());
		$this->assign('auth',$this->config['auth']);
		$this -> display();
	}

	function _insert(){
		$user = new Model();
		$user -> startTrans();
		$data['content']=$_POST['content'];
		$data['add_file']=$_POST['add_file'];
		$data['sender_id']=get_user_id();
		$data['sender_name']=get_user_name();
		$data['create_time']=time();
		
		$model = D('Message');
		
		$arr_recever = array_filter(explode(";",$_POST['to']));
		foreach ($arr_recever as $val) {
			$tmp=explode("|",$val);
			$data['receiver_id']=$tmp[1];
			$data['receiver_name']=$tmp[0];			
			$data['owner_id']=get_user_id();
		
			$list = $model -> add($data);

			$data['owner_id']=$tmp[1];
			$list = $model -> add($data);
			$this -> _pushReturn("", "您有新的消息, 请注意查收", 1,$tmp[1]);
		}
		
		//保存联系人信息 方便查询最近联系人
		$info['sender_id'] = get_user_id();
		$info['create_time'] = time();
		foreach ($arr_recever as $v){
			$emp = explode("|",$v);
			$info['receiver_id'] .= $emp[1] . "|";
			$info['receiver_name'] .= $emp[0] . "|";
		}
		$contact = M('Message_contacts');
		//获取现有的最近联系人
		$con_list = $contact -> where('sender_id = '.get_user_id()) -> field('id,receiver_id,receiver_name') -> find();
		//之前已经有最近联系人了
		if($con_list){
			$sid = empty($con_list['receiver_id']) ? null : explode('|',rtrim($con_list['receiver_id'],'|'));//senderid
			$rec_name = empty($con_list['receiver_name']) ? null : explode('|',rtrim($con_list['receiver_name'],'|'));//sendername
			
			$nid = empty($info['receiver_id']) ? null : explode('|',rtrim($info['receiver_id'],'|'));//receiverid
			$temp = empty($info['receiver_name']) ? null : explode('|',rtrim($info['receiver_name'],'|'));//receivername
			
			$id_new = array_merge($sid,$nid);
			$id_new = array_unique($id_new);
			$id_new = implode('|',$id_new);
			
			$name_new = array_merge($rec_name,$temp);
			$name_new = array_unique($name_new);
			$name_new = implode('|',$name_new);
			
			$infos['receiver_id'] = $id_new;
			$infos['receiver_name'] = $name_new;
			$infos['id'] = $con_list['id'];
			$flag = $contact -> save($infos);
			
		}else{//没有
			$flag = $contact -> add($info);
		}
		//保存当前数据对象
		if ($list !== false && $flag !== false) {//保存成功
			$user->commit();
			$this -> assign('jumpUrl',get_return_url());
			$this -> success('发送成功!');
		} else {
			$user->rollback();
			//失败提示
			$this -> error('发送失败!');
		}
	}

	public function read(){
		$widget['editor'] = true;
		$widget['uploader'] = true;
		$this -> assign("widget", $widget);	

		$receiver_id = $_REQUEST['reply_id'];
		$sender_id = get_user_id();
		$model = M("Message");
		$where['owner_id'] = get_user_id();
		$where['_string'] = "(sender_id='$sender_id' and receiver_id='$receiver_id') or (receiver_id='$sender_id' and sender_id='$receiver_id')";
		$list = $model -> where($where) -> order('create_time desc') -> select();
		$model -> where($where) -> setField('is_read', '1');
// 		手机端提供照片
		if(is_mobile_request()){
			$only_no_read = $_GET['only_no_read']?$_GET['only_no_read']:0;
			foreach ($list as $k=>$v){
				if($k<40){//手机端取出前40条
					if($only_no_read && $v['is_read']=='1'){
						unset($list[$k]);
					}else{
						$user = M('User')->find($v['sender_id']);
						$list[$k]['pic'] = $user['pic'];
						$list[$k]['mobile_file'] = mobile_show_file($v['add_file']);
					}
				}else{
					unset($list[$k]);
				}
			}
		}else{
			foreach ($list as $k=>$v){
				if($v['owner_id'] == $v['sender_id']){
					$user = M('User')->find($v['receiver_id']);
					$list[$k]['pic'] = $user['pic'];
				}elseif($v['owner_id'] == $v['receiver_id']){
					$user = M('User')->find($v['sender_id']);
					$list[$k]['pic'] = $user['pic'];
				}
			}
		}
		
// 		dump($list);
		$this -> assign('list', $list);
		if(is_array($list)){
			$vo=$list[0];
			if($vo['sender_id']==get_user_id()){
				$reply_id=$vo['receiver_id'];
				$reply_name=$vo['receiver_name'];
			}
			if($vo['receiver_id']==get_user_id()){
				$reply_id=$vo['sender_id'];
				$reply_name=$vo['sender_name'];
			}
			$this-> assign('reply_id',$reply_id);
			$this-> assign('reply_name',$reply_name);
		}
		$this -> display();
	}

	function reply(){

		$data['content']=$_POST['content'];
		$data['add_file']=$_POST['add_file'];
		$data['sender_id']=get_user_id();
		$data['sender_name']=get_user_name();
		$data['create_time']=time();
		$data['receiver_id']=$_POST['receiver_id'];
		$data['receiver_name']=$_POST['receiver_name'];
		$data['owner_id']=get_user_id();

		$model = D('Message');		
		$list = $model -> add($data);

		$data['owner_id']=$_POST['receiver_id'];
		$list = $model -> add($data);
		$this -> _pushReturn("", "您有新的消息, 请注意查收", 1,$_POST['receiver_id']);	

		//保存当前数据对象
		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl',get_return_url());
			$this -> success('发送成功!');
		} else {
			//失败提示
			$this -> error('发送失败!');
		}
	}

	function forward(){
		$id = $_REQUEST['id'];
		$model = M("Message");
		$where['owner_id']=array('eq',get_user_id());
		$where['id']=array('eq',$id);
		
		$list=$model ->where($where)->find();
		if ($list !== false) {//保存成功
			$this -> assign('vo',$list);
			$this -> display();
		} else {
			//失败提示
			$this -> error('读取失败!');
		}
	}

	function upload(){
		$this->_upload();
	}

	public function del() {
		$type=$_REQUEST['type'];
		$where['owner_id'] = array("eq",get_user_id());
		switch($type){
			case 'all' :
				break;
			case 'dialogue' :
				$receiver_id = $_REQUEST['reply_id'];
				$sender_id = get_user_id();
				$where['_string'] = "(sender_id='$sender_id' and receiver_id='$receiver_id') or (receiver_id='$sender_id' and sender_id='$receiver_id')";
				break;
			case 'message' :
				$message_id = $_REQUEST['message_id'];
				$where['id'] = array("eq",$message_id);
				break;
			default :
				$this -> ajaxReturn('', "删除失败",0);
				break;
		}
		$model=D("Message");
		$list=$model->where($where)->delete();
	
		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('删除成功!');
		} else {
			$this -> error('删除失败!');
			//失败提示
		}
	}
	function down() {
		$this -> _down();
	}
}

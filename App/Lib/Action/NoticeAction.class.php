<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class NoticeAction extends CommonAction {

	protected $config = array('app_type' => 'folder', 'action_auth' => array('folder' => 'read','sign'=>'read','mark' => 'read', 'upload' => 'read' ,'changeplan' => 'read' ,'confirm' => 'read'));

	//过滤查询字段
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['keyword']) && empty($map['name'])) {
			$map['name'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}

	public function index() {
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		
		$arr_read = array_filter(explode(",", get_user_config("readed_notice").",".$id));
		$map['id']=array('in',$arr_read);
			
		$this -> assign("readed_id", $arr_read);

		$user_id = get_user_id();
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		
		$folder_list=D("SystemFolder")->get_authed_folder(get_user_id());
		$map['folder']=array("in",$folder_list);
		//已提交或自己的草稿
		$where['is_submit'] = 1;
		$self['is_submit'] = 0;
		$self['user_id'] = $user_id;
		$where['_complex'] = $self;
		$where['_logic'] = 'OR';
		$map['_complex'] = $where;
		
		$model = D("NoticeView");
		if (!empty($model)) {
			$this -> _list($model, $map);
		}
		$this -> display();
		return;
	}

	public function mark() {
		$action = $_REQUEST['action'];
		$id = $_REQUEST['id'];
		switch ($action) {
			case 'del' :
				$where['id'] = array('in', $id);
				$folder = M("Notice") -> distinct(true) -> where($where) -> field("folder") -> select();
				if (count($folder) == 1) {
					$auth = D("SystemFolder") -> get_folder_auth($folder[0]["folder"]);
					if ($auth['admin'] == true) {
						$field = 'is_del';
						$result = $this -> _set_field($id, $field, 1);
						if ($result) {
							$this -> ajaxReturn('', "删除成功", 1);
						} else {
							$this -> ajaxReturn('', "删除失败", 0);
						}
					}
				} else {
					$this -> ajaxReturn('', "删除失败", 0);
				}
				break;
			case 'move_folder' :
				$target_folder = $_REQUEST['val'];
				$where['id'] = array('in', $id);
				$folder = M("Notice") -> distinct(true) -> where($where) -> field("folder") -> select();
				if (count($folder) == 1) {
					$auth = D("SystemFolder") -> get_folder_auth($folder[0]["folder"]);
					if ($auth['admin'] == true) {
						$field = 'folder';
						$this -> _set_field($id, $field, $target_folder);
					}
					$this -> ajaxReturn('', "操作成功", 1);
				} else {
					$this -> ajaxReturn('', "操作成功", 1);
				}
				break;
			case 'readed' :
				$s = '';
				foreach ($id as $idd){
					$s.=$idd.',';
				}
				$res = $this -> _readed($s);
				if(!empty($res)){
					$this -> ajaxReturn('', "操作成功", 1);
				}else{
					$this -> ajaxReturn('', "操作失败", 1);
				}
				break;
			//增加签收
			default :
				break;
		}
	}
	//异步修改工作计划状态
	function changeplan(){
		$sid = $_REQUEST['sid'];
		$id = $_REQUEST['id'];
		if($id){
			$model = M('notice');
			$plan = $model -> getFieldById($id,'plan');
			$data['plan'] = $plan . '|' .$sid;
			$data['id'] = $id;
			$result = $model -> save($data);
			if($result){
				$this ->ajaxReturn('', "工作计划状态修改成功",1);
			}else{
				$this ->ajaxReturn('', "工作计划状态修改失败",0);
			}
		}
	}
	
	function confirm(){
		$id = $_REQUEST['id'];
		$fi = $_REQUEST['fi'];
		if($id){
			$model = M('notice');
			$data['is_submit'] = $fi;
			$data['id'] = $id;
			$result = $model -> save($data);
			if($result){
				$this ->ajaxReturn('', "请求成功",1);
			}else{
				$this ->ajaxReturn('', "请求失败",0);
			}
		}
	}
	
	function sign(){
		$user_id = get_user_id();
		$id = $_REQUEST['id'];
		
		$model = M("Notice");
		$folder_id = $model -> where("id=$id") -> getField('folder');

		$Form = D('Notice_sign');
		$data['notice_id']  =   $id;
		$data['user_id']    =   $user_id;
		$data['folder']     =   $folder_id;
		$data['user_name']  =   get_user_name();
		$data['is_sign']    =   '1';
		$data['sign_time']  =   time();
		$result=$Form->add($data);
		if($result){
			$this ->ajaxReturn('', "签收成功",1);
		}else{
			$this ->ajaxReturn('', "签收失败",0);
		}
	}

	function add() {
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);

		$fid = $_REQUEST['fid'];
		$this -> assign('folder', $fid);
		//部门查看权限
		if(in_array($fid , array('72','74','94','96'))){
			$this ->assign('ckdept','1');
		}
		//企业概况
		if(in_array($fid , array('68','96'))){
			$this -> assign('ckfile','1');
		}
		//计划类型
		if($fid == '94'){
			$this ->assign('ckplan','1');
		}
		//公司新闻与今日头条
		if($fid == '95'){
			$this -> assign('cknews','1');
		}
		$this -> display();
	}
	
	public function edit() {
		$id = $_REQUEST['id'];
		$vo = D("Notice") -> find($id);
		if($vo['is_submit']==1 || ($vo['is_submit']==0 && $vo['user_id']!=get_user_id())){
			$this -> error('该公告已提交或不是自己的草稿!');
		}
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
		$this -> _edit();
	}

	public function read() {
		$id = is_mobile_request()==true?$_REQUEST['notice_id']:$_REQUEST['id'];
		if($_REQUEST['fid'] == 'confirm'){
			$this -> assign('confirm',1);
		}
		$this -> _readed($id);
		$user_id = get_user_id();
		$model = M("Notice");
		$folder_id = $model -> where("id=$id") -> getField('folder');		
		$this -> assign("auth", $auth = D("SystemFolder") -> get_folder_auth($folder_id));
			//获得已经签收人员名字
		$User = M('Notice_sign');
		$signlist = $User->where("notice_id=$id")->select();
		$this->assign('signlist',$signlist);
			
		$signok = $User->where("notice_id=$id and user_id=$user_id and is_sign=1")->select();
		$this->assign('is_sign',count($signok));

		//今日头条或公司新闻
		if($folder_id == '95'){
			$model -> where("id = $id") -> setInc("views");
		}
		$this -> _edit(null,$id);
	}


	public function folder() {
		$folder_id = $_REQUEST['fid'];
		
		if(in_array($folder_id , array('72','74','94','96'))){
			$this -> inform($folder_id);die;
		}
		$widget['date'] = true;
		$this -> assign("widget", $widget);

		$arr_read = array_filter(explode(",", get_user_config("readed_notice")));

		$this -> assign("readed_id",$arr_read);
						
		$model = D("Notice");
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$this -> assign('user_id', get_user_id());
		$this -> assign("folder_id", $folder_id);

		$map['folder'] = $folder_id;
		//已提交或自己的草稿
		$res = $model -> where($map) -> order('id desc') -> select();
		if(!empty($model)){
			$res = $this -> _list($model, $map);
		}
		$this -> assign('res',$res);
		$this -> assign("folder_name", D("SystemFolder") -> get_folder_name($folder_id));
		$this -> assign('auth', $this -> config['auth']);
		$this -> _assign_folder_list();
		$this -> display();
	}
	// 通知与公告
	public function inform($folder_id){
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		
		if($folder_id == '94'){
			$this -> assign('ckplan', '1');
		}
		$arr_read = array_filter(explode(",", get_user_config("readed_notice")));
		$this -> assign("readed_id",$arr_read);
						
		$model = D("Notice");
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}

		$this -> assign("folder_id", $folder_id);

		$map['folder'] = $folder_id;
		//已提交或自己的草稿
		$where['is_submit'] = 1;
		$self['is_submit'] = 0;
		$self['user_id'] = get_user_id();
		$where['_complex'] = $self;
		$where['_logic'] = 'OR';
		$map['_complex'] = $where;
		$res = $model -> where($map) -> order('id desc') -> select();
		$pos_id = M('User')->field('pos_id')->find(get_user_id());
		$Parentid = $pos_id['pos_id'];
		$parent_list = array();
		while($Parentid){//获取上级数组
			$parent_list[] = $Parentid;
			$Parentid = getParentDept(null,$Parentid);
		}
		
		foreach ($res as $k=>$v){
			$tmp = explode(';',$v['read']);
			$res[$k]['can'] = false;
			foreach ($tmp as $kk => $vv){
				if(in_array($vv,$parent_list)){
					$res[$k]['can'] = true;
					break;
				}
			}	
		}
		foreach ($res as $k => $v){
			if(!$v['can']){
				unset($res[$k]);
			}
		}
		if (!empty($_REQUEST['list_rows'])) {
			$listRows = $_REQUEST['list_rows'];
		} else {
			$listRows = get_user_config('list_rows');
		}
		$now = intval($_REQUEST['p']); $rows =  intval($listRows);
		$offset = $rows * ($now - 1);
		$ress = array_slice($res , $offset , $rows);
		$this -> assign('res',$ress);
		$this -> assign("folder_name", D("SystemFolder") -> get_folder_name($folder_id));
		$this -> assign('auth', $this -> config['auth']);
		$this -> _assign_folder_list();
		//分页
		import("@.ORG.Util.Page");
		//创建分页对象
		$p = new Page(count($res), $listRows);
		$p -> parameter = $this -> _search();
		//分页显示
		$page = $p -> show();
		$this -> assign("page", $page);
		$this -> display('inform');
	}
	//今日头条和公司新闻显示页面
	
	public function upload() {
		$this -> _upload();
	}

	public function down() {
		$this -> _down();
	}

	private function _readed2($id) {
		$arr_read = array_filter(explode(",", get_user_config("readed_notice")));
		$arr_readed_notice = array();
		foreach ($arr_read as $key => $val) {
			$tmp = explode("|", $val);
			$create_time = $tmp[1];
			if ($create_time > time() - 3600 * 24 * 30) {
				$arr_readed_notice[] = $val;
			}
		}

		$readed_notice = implode("_", $arr_readed_notice);
		$read_notice = M("Notice") -> field("id,create_time") -> find($id);
		if ($read_notice['create_time'] > time() - 3600 * 24 * 30) {
			$read_notice_str = $read_notice['id'] . "|" . $read_notice['create_time'] . "_";
			$readed_notice = str_replace($read_notice_str, "", $readed_notice);
			trace($readed_notice);
			$readed_notice .= $read_notice_str;
			trace($readed_notice);
			M("UserConfig") -> where(array('eq', get_user_id())) -> setField('readed_notice', $readed_notice);
		}
	}
	
	private function _readed($id) {		
		$folder_list=D("SystemFolder")->get_authed_folder(get_user_id());
		$map['folder']=array("in",$folder_list);
		$map['create_time']=array("egt",time() - 3600 * 24 * 30);
						
		$arr_read = array_filter(explode(",", get_user_config("readed_notice").",".$id));
		
		$map['id']=array('in',$arr_read);
				
		$readed_notice=M("Notice")->where($map)->getField("id,name");
		$readed_notice=implode(",",array_keys($readed_notice));
		$where['id']=array('eq',get_user_id());
		return M("UserConfig") -> where($where) -> setField('readed_notice', $readed_notice);
	}
}

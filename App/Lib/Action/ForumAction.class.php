<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
-------------------------------------------------------------------------*/

class ForumAction extends CommonAction {
	protected $config = array(
		'app_type' => 'folder',
		'pid'=>'forum_id',
		'sub_model'=>'ForumPost',
		'action_auth' => array('folder' => 'read','mark' => 'write', 'upload' => 'write', 'update_forum' => 'write'),
		'sub_action_auth'=>array('save_post' => 'write', 'edit_post' => 'write', 'del_post' => 'admin','update_post' => 'write')
	);
	//过滤查询字段

	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['fid'])){
			$map['folder'] = $_REQUEST['fid'];
		}
		if (!empty($_REQUEST['keyword']) && empty($map['name'])) {
			$keyword = $_POST['keyword'];
			$where['name'] = array('like', "%" . $keyword . "%");
			$where['content'] = array('like', "%" . $keyword . "%");
			$where['user_name'] = array('like', "%" . $keyword . "%");
			$where['_logic'] = 'or';
			$map['_complex'] = $where;
		}
	}

	public function index(){
		$model=D("SystemFolder");
		$forum_list=$model->get_folder_list("","id,pid,name,admin");
		$this->assign("forum_list",$forum_list);

		$model=D("Forum");
		$forum_info=$model->get_info();

		$temp=array();
		foreach($forum_info as $item){
			$temp[$item['folder']]=$item;
		}
		$forum_info=$temp;
		$this->assign("forum_info",$forum_info);

		$today_count=$model->get_today_count();
		$temp=array();
		foreach($today_count as $item){
			$temp[$item['folder']]=$item;
		}
		$today_count=$temp;
		$this->assign("today_count",$today_count);		

		$this -> display();
	}

	public function newly() {
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$model = D("Forum");
		if (!empty($model)) {
			$this -> _list($model,$map);
		}
		$this -> display();
	}

	public function add() {
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);

		$this -> assign('folder', $_REQUEST['fid']);
		$this -> display();
	}

	public function edit() {
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
		$this -> _edit();
	}

	public function read() {
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget",$widget);
		$this -> assign('auth', $this -> config['auth']);
		
		$model = M("Forum");

		$id = $_REQUEST['id'];
		$where['id'] = array('eq', $id);

		$folder_id = $_REQUEST['fid'];
		if(!empty($folder_id)){
			$where['folder'] = array('eq', $folder_id);
		}
		$forum = $model -> where($where) -> find();
		$this -> assign('forum',$forum);

		$id = $_REQUEST['id'];
		$user_id = get_user_id();
		$user['user_id'] = $user_id;

		$this -> assign('user', $user);
		$this -> assign('user_id',$user_id);
		//添加查看次数和关注人数
		$model = M("Forum");
		$model -> where("id=$id") -> setInc('views', 1);
		$atten = M("ForumAtten");
		$atten_list = $atten -> find();
		if(empty($atten_list)){
			$data['atten_user'] = $user_id . ',';
			$data['atten_num'] = 1;
			$atten -> add($data);
		}else{
			$tmp = array_filter(explode(',',rtrim($atten_list['atten_user'],',')));
			$flag = true;
			foreach ($tmp as $k => $v){
				if($v == $user_id){
					$flag = false;
				}
			}
			if($flag){
				$atten_list['atten_user'] .=  $user_id .',';
				$atten_list['atten_num'] += 1;
				$atten -> save($atten_list);
			}
		}
		$model = M("Forum");

		$where = array();
		$where['forum_id'] = $id;
		$where['pid'] = 0;
		$where['is_del'] = 0;

		$model = M("ForumPost");

		if (!empty($model)) {
			$res = $this -> _list($model, $where, "id", true);
		}
		$where['pid'] = array('neq',0);
		$info = $model -> where($where) -> select();
		foreach ($res as $k => $v){
			foreach ($info as $kk => $vv){
				if($vv['pid'] == $v['id']){
					$res[$k]['comm_ids'][] = $vv['id'];
				}
			}
		}
		$this -> assign('info',$res);
		$this -> assign("forum_id", $id);
		$this -> assign("folder_id", $folder_id);
		// 序号
		$rows = get_user_config('list_rows');
		if(isset($_POST['p'])){
			$number = $_POST['p']*$rows-$rows+1;
		}else{
			$number = 1*$rows-$rows+1;
		}
		$this -> assign('rows',$number);
		$this -> display();
	}

	public function folder() {
		$widget['date'] = true;
		$this -> assign("widget", $widget);

		$this -> assign('auth', $this -> config['auth']);
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$model = M("Forum");
		$sortby="is_top desc,id desc";
		if (!empty($model)) {
			$this -> _list($model, $map,$sortby);
		}
		$model = M("SystemFolder");
		$bbs = $model -> where($where) -> order("id desc") -> getField('id,name');
		$this -> assign('folder_bbs', $bbs);//bbs模块
		$this -> assign('fid',$_GET['fid']);
		$folder_id = $map['folder'];
		$where['id'] = array('eq', $folder_id);
		$folder_name = M("SystemFolder") -> where($where) -> getField("name");
		$this -> assign("folder_name", $folder_name);

		$this -> _assign_folder_list('/forum/folder/');
		$this -> assign("folder_id", $folder_id);
		$this -> display();
		return;
	}

	public function mark() {
		$action = $_REQUEST['action'];
		$id = $_REQUEST['id'];
		if (!empty($id)) {
			switch ($action) {
				case 'del' :
					$where['id'] = array('in', $id);
					$folder = M("Forum") -> distinct(true) -> where($where) -> field("folder") -> select();
					if (count($folder) == 1) {
						$auth = D("SystemFolder") -> get_folder_auth($folder[0]["folder"]);
						if ($auth['write'] == true) {
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
					$folder = M("Forum") -> distinct(true) -> where($where) -> field("folder") -> select();
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
				case 'is_top':
					$where['id'] = array('in', $id);
					$folder = M("Forum") -> distinct(true) -> where($where) -> field("folder,is_top") -> select();
					if (count($folder) == 1) {
						$auth = D("SystemFolder") -> get_folder_auth($folder[0]["folder"]);
						if ($auth['admin'] == true) {
							$field = 'is_top';
							if($folder[0]['is_top']==0){
							$result = $this -> _set_field($id, $field, 1);
							}else{
							$result = $this -> _set_field($id, $field, 0);
							}
							
							if ($result) {
								$this -> ajaxReturn('', "操作成功", 1);
							} else {
								$this -> ajaxReturn('', "操作失败", 0);
							}
						}
					} else {
						$this -> ajaxReturn('', "操作失败", 0);
					}
					break;
				default :
					break;
			}
		}
	}

	public function save_post() {
		$id = $_POST['forum_id'];
		$fid = $_POST['folder_id'];
		$model = M("Forum");
		$model -> where("id=$id") -> setInc('reply', 1);
		
		$model = D("ForumPost");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		/*保存当前数据对象 */
		$list = $model -> add();
		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl', U('read',array('id'=>$id,'fid'=>$fid)));
			$this -> success('新增成功!'.$list);
		} else {
			$this -> error('新增失败!');
			//失败提示
		}
	}

	public function edit_post(){
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);

		$this->_edit("ForumPost");
	}
	
	public function update_post(){
		$id = $_REQUEST['forum_id'];
		$fid = $_REQUEST['folder_id'];
		$model = D("ForumPost");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$list = $model -> save();
		if (false !== $list) {
			$this -> assign('jumpUrl', U('read',array('id'=>$id,'fid'=>$fid)));
			$this -> success('编辑成功!');
			//成功提示
		} else {
			$this -> error('编辑失败!');
			//错误提示
		}
	}
	
	public function update_forum(){
		$id = $_REQUEST['forum_id'];
		$fid = $_REQUEST['folder_id'];
		$model = M("Forum");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$list = $model -> save();
		if (false !== $list) {
			$this -> assign('jumpUrl', U('read',array('id'=>$id,'fid'=>$fid)));
			$this -> success('编辑成功!');
			//成功提示
		} else {
			$this -> error('编辑失败!');
			//错误提示
		}
	}

	public function del_post(){
		$forum_id = $_REQUEST['forum_id'];
		$model = M("Forum");
		$model -> where("id=$forum_id") -> setDec('reply', 1);
		$id=$_REQUEST['id'];
		$this->_del($id,"ForumPost");
	}

	public function upload() {
		$this -> _upload();
	}

	public function down() {
		$this -> _down();
	}
}

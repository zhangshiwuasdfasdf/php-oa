<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class GoodsAction extends CommonAction {

	protected $config = array('app_type' => 'master');

	//过滤查询字段
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['keyword']) && empty($map['name'])) {
			$map['name'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}

	public function index() {
		$widget['date'] = true;
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		
		$map['is_del'] = 0;
		
		$model = D("Goods");
		if (!empty($model)) {
			$goods = $this -> _list($model, $map);
			$goods_ext = array();
			foreach ($goods as $k=>$v){
				$goods_sum = M('GoodsChange')->field('sum')->where(array('goods_id'=>$v['id']))->order('create_time desc,id desc')->find();
				$goods_ext[$k]['sum'] = $goods_sum['sum']?$goods_sum['sum']:0;
			}
			$this -> assign('goods_ext', $goods_ext);
		}
		$this -> display();
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
		$this -> display();
	}

	public function edit() {
		$id = $_REQUEST['id'];
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
		$vo = M('Goods') -> find($id);
		$vo['cate_name'] = get_goods_category_name($vo['cate_id']);
		$this -> assign("vo", $vo);
		$this -> display();
	}
	public function del() {
		$id = $_REQUEST['id'];
		$sum = M('GoodsChange')->where(array('goods_id'=>$id))->sum('num');
		$sum = $sum?$sum:0;
		if($sum == 0){
			$res = M('GoodsChange')->where(array('goods_id'=>$id)) -> delete();
			if(false !== $res){
				$res2 = M('Goods') -> delete($id);
				$this -> assign('jumpUrl', get_return_url());
				$this -> success("成功删除{$res2}条!");
			}else{
				$this -> error("删除失败");
			}
			
		}else{
			$this -> assign('jumpUrl', get_return_url());
			$this -> error("有库存，不能删除");
		}
	}

	public function read() {
		$id = is_mobile_request()==true?$_REQUEST['notice_id']:$_REQUEST['id'];
		$this -> _edit(null,$id);
	}

	function change() {
		$widget['date'] = true;
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		unset($map['is_del']);
		$model = D("GoodsChangeView");
		if (!empty($model)) {
			$r = $this -> _list($model, $map);
		}
		$this -> display();
	}
	function addchange() {
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
	
		$this -> display();
	}
	function savechange() {
		$model = D('GoodsChange');
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
		$model ->user_id = get_user_id();
		$model ->create_time = time();
		$goods_id = $model ->goods_id;
		if($goods_id){
			$last = M('GoodsChange')->field('id,sum')->where(array('goods_id'=>$goods_id))->order('create_time desc,id desc')->find();
			$last_sum = $last?$last['sum']:0;
			if($model ->sum<0 && $last_sum+$model ->num<0){
				$this -> error('数量不够，新增失败!');
			}
			$model ->sum = $last_sum+$model ->num;
			
			/*保存当前数据对象 */
			$list = $model -> add();
			if ($list !== false) {//保存成功
				$this -> assign('jumpUrl', get_return_url());
				$this -> success('新增成功!');
			} else {
				$this -> error('新增失败!');
				//失败提示
			}
		}else{
			$this -> error('新增失败!');
		}
		
		
	}

	public function folder() {
		$widget['date'] = true;
		$this -> assign("widget", $widget);

		$arr_read = array_filter(explode(",", get_user_config("readed_notice")));

		$this -> assign("readed_id",$arr_read);
						
		$model = D("Notice");
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}

		$folder_id = $_REQUEST['fid'];
		$this -> assign("folder_id", $folder_id);

		$map['folder'] = $folder_id;
		//已提交或自己的草稿
		$where['is_submit'] = 1;
		$self['is_submit'] = 0;
		$self['user_id'] = get_user_id();
		$where['_complex'] = $self;
		$where['_logic'] = 'OR';
		$map['_complex'] = $where;
		
		if (!empty($model)) {
			$this -> _list($model, $map);
		}

		$this -> assign("folder_name", D("SystemFolder") -> get_folder_name($folder_id));
		$this -> assign('auth', $this -> config['auth']);
		$this -> _assign_folder_list();

		$this -> display();
	}
	
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
	public function winpop() {
		$node = M("GoodsCategory");
		$menu = array();
		$menu = $node -> where('is_del=0') -> field('id,pid,name') -> order('sort asc') -> select();
	
		$tree = list_to_tree($menu);
		$this -> assign('menu', popup_tree_menu($tree));
	
		$this -> assign('pid', $pid);
		$this -> display();
	}
	public function winpop_goods() {
		$node = M("GoodsCategory");
		$menu = array();
		$menu = $node -> where('is_del=0') -> field('id,pid,name') -> order('sort asc') -> select();
	
		$menu2 = array();
		$menu2 = M("Goods") -> where('is_del=0') -> field('id as goods_id,cate_id as pid,goods_name as name') -> order('sort asc') -> select();
		
		$tree = list_to_tree(array_merge($menu,$menu2));
		$this -> assign('menu', popup_tree_menu($tree,0,100,array('goods_id')));
	
		$this -> assign('pid', $pid);
		$this -> display();
	}
}

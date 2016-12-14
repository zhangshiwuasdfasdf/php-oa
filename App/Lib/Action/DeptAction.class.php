<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class DeptAction extends CommonAction {

	protected $config = array('app_type' => 'master', 'action_auth' => array('index' => 'admin', 'winpop4' => 'read','get_company_candidate'=>'read','company_add'=>'read','dept_add'=>'read','edit_company'=>'read','edit_dept'=>'read','update_dept'=>'read','get_dept_by_company_id'=>'read','set_dept'=>'read','ajax_get_dept_info'=>'read','view'=>'read','validate'=>'read'));

	public function index(){
		$node = M("Dept");
		$menu = array();
		$map = array();
		$ids = array();
		$where['is_del'] = '0';
		if(!empty($_POST['dept_no'])){
			$map['dept_no'] = array('like','%'.$_POST['dept_no'].'%');
		}
		if(!empty($_POST['name'])){
			$map['name'] = array('like','%'.$_POST['name'].'%');
		}
		if(!empty($_POST['is_use']) && $_POST['is_use']>-1){
			$map['is_use'] = array('eq',$_POST['is_use']);
		}
		if(!empty($map)){
			$map['is_del'] = '0';
			$ids = $node -> where($map) -> getField('id',true);
		}
// 		$open=fopen("C:\log.txt","a" );
// 		fwrite($open,json_encode($ids)."\r\n");
// 		fclose($open);
		$menu = $node -> where($where) -> field('id,pid,name,dept_no,is_use') -> order('sort asc') -> select();
		$tree = list_to_tree($menu);
		
		$a = popup_tree_menu_dept($tree,'0','100',$ids);
// 		dump($a);die;
		$this -> assign('menu', $a);
		$this -> display();
	}

	public function del() {
		$id = $_POST['id'];
		$this -> _destory($id);

	}

	public function winpop() {
		$node = M("Dept");
		$menu = array();
		$menu = $node -> where('is_del=0') -> field('id,pid,name') -> order('sort asc') -> select();

		$tree = list_to_tree($menu);
		$this -> assign('menu', popup_tree_menu($tree));

		$this -> assign('pid', $pid);
		$this -> display();
	}

	public function winpop2() {//选择部门
		$this -> winpop();
	}
	
	public function winpop3() {//选择部门，包括..下
		$node = M("Dept");
		$menu = array();
		$menu = $node -> where('is_del=0') -> field('id,pid,name') -> order('sort asc') -> select();
		
		$tree = list_to_tree($menu);
		
// 		$tree = add_leaf($tree);
		
		$this -> assign('menu', popup_tree_menu($tree));

		$this -> assign('pid', $pid);
		$this -> display();
	}
	public function winpop4() {//选择部门，（狭义，只给3级）
		$node = M("Dept");
		$menu = array();
		$menu = $node -> where('is_del=0') -> field('id,pid,name') -> order('sort asc') -> select();
	
		$tree = list_to_tree($menu);
	
// 		$tree = add_leaf($tree);
	
		$this -> assign('menu', popup_tree_menu($tree));
		$this -> assign('type', $_GET['type']);
		$this -> assign('pid', $pid);
		$this -> display('winpop4');
	}
	public function get_company_candidate(){
		$company_name = M('SimpleDataMapping')->field('data_name')->where(array('data_type'=>array('like','%人事公司%')))->select();
		$html = '';
		foreach ($company_name as $k=>$v){
			$html .= '<option>'.$v['data_name'].'</option>';
		}
		$this->ajaxReturn($html);
	}
	public function company_add(){
		$data['pid'] = 0;
		$data['name'] = $_POST['company'];
		$data['sort'] = $_POST['sort'];
		$data['is_del'] = 0;
		$data['is_use'] = 1;
		$res = M('Dept')->add($data);
		if(false !== $res){
			$this->success('新增成功');
		}else{
			$this->error('新增失败');
		}
	}
	public function dept_add(){
		if(!empty($_POST['belong_dept_id']) && !empty($_POST['dept_name'])){
			$data['pid'] = $_POST['belong_dept_id'];
			$data['name'] = $_POST['dept_name'];
			
			$last_dept_no = M('Dept')->where(array('dept_no'=>array('like','D%')))->order('dept_no desc')->limit(1)->getField('dept_no');
			$data['dept_no'] = 'D'.formatto4w(intval(substr($last_dept_no, 1))+1);
			$data['sort'] = $_POST['sort'];
			$data['is_del'] = '0';
			$data['is_use'] = '1';
			$res = M('Dept')->add($data);
			if(false !== $res){
				$this->success('新增成功');
			}else{
				$this->error('新增失败');
			}
		}else{
			$this->error('请填写部门相关信息');
		}
	}
	public function edit_dept(){
		$dept_id = $_GET['id'];
		
		$dept_info = M('Dept')->find($dept_id);
		$this->assign('dept_info',$dept_info);
		
		$pid = $dept_id;
		while($pid){
			$id = $pid;
			$pid = M('Dept')->where(array('id'=>$id))->getField('pid');
		}
		$company_id = $id;
		$company = M('Dept')->where(array('pid'=>'0','is_del'=>'0'))->select();
		$company_html = '';
		foreach ($company as $k=>$v){
			if($v['id'] == $company_id){
				$company_html .= '<option value="'.$v['id'].'" selected="selected">'.$v['name'].'</option>';
			}else{
				$company_html .= '<option value="'.$v['id'].'">'.$v['name'].'</option>';
			}
		}
		$this->assign('company',$company_html);
		
		$dept = M('Dept')->field('id,pid,name')->where(array('id'=>array('in',get_child_dept_all($company_id)),'is_del'=>'0'))->select();
		$tree = list_to_tree($dept);
		$dept_html = popup_menu_option($tree,0,$dept_info['pid']);
		$this->assign('dept',$dept_html);
		
		if($dept_info['is_use'] == '1'){
			$status_html = '<option value="1" selected="selected">启用</option><option value="0" >禁用</option>';
		}else{
			$status_html = '<option value="1">启用</option><option value="0" selected="selected">禁用</option>';
		}
		$this->assign('status',$status_html);
		$this->display();
	}
	public function edit_company(){
		$dept_id = $_GET['id'];
	
		$dept_info = M('Dept')->find($dept_id);
		$this->assign('dept_info',$dept_info);
	
		
		if($dept_info['is_use'] == '1'){
			$status_html = '<option value="1" selected="selected">启用</option><option value="0" >禁用</option>';
		}else{
			$status_html = '<option value="1">启用</option><option value="0" selected="selected">禁用</option>';
		}
		$this->assign('status',$status_html);
		$this->display();
	}
	public function get_dept_by_company_id(){
		$dept = M('Dept')->field('id,pid,name')->where(array('id'=>array('in',get_child_dept_all($_POST['company_id'])),'is_del'=>'0'))->select();
		$tree = list_to_tree($dept);
		$dept_html = '<option>请选择部门</option>'.popup_menu_option($tree);
		$this->ajaxReturn($dept_html);
	}
	public function update_dept(){
		if(!empty($_POST['pid'])){
			$data['pid'] = $_POST['pid'];
		}
		$data['sort'] = $_POST['sort'];
		$data['name'] = $_POST['name'];
		$data['is_use'] = $_POST['is_use'];
		$res = M('Dept')->where(array('id'=>$_POST['id']))->save($data);
		if(false !== $res){
			$this->success('修改成功',U('index'));
		}else{
			$this->success('修改失败');
		}
	}
	public function set_dept(){
		$res = M('Dept')->where(array('id'=>$_POST['id']))->save(array('is_use'=>$_POST['is_use']));
		if(false !== $res){
			$data['dept_id'] = $_POST['id'];
			$data['res'] = 1;
			$data['is_use'] = $_POST['is_use'];
			$this->ajaxReturn($data);
		}else{
			$data['dept_id'] = $_POST['id'];
			$data['res'] = 0;
			$data['is_use'] = $_POST['is_use'];
			$this->ajaxReturn($data);
		}
	}
	public function ajax_get_dept_info(){
		$data['info'] = M('Dept')->find($_POST['dept_id']);
		
		$pid = $_POST['dept_id'];
		while($pid){
			$id = $pid;
			$pid = M('Dept')->where(array('id'=>$id))->getField('pid');
		}
		$data['info']['company_name'] = M('Dept')->where(array('id'=>$id))->getField('name');
		$this->ajaxReturn($data);
	}
	public function view(){
		$dept_id = $_GET['id'];
		
		$dept_info = M('Dept')->find($dept_id);
		$dept_info['pid_name'] = M('Dept')->where(array('id'=>$dept_info['pid']))->getField('name');
		$dept_info['is_use'] = $dept_info['is_use']=='1'?'启用':'禁用';
		$pid = $dept_id;
		while($pid){
			$id = $pid;
			$pid = M('Dept')->where(array('id'=>$id))->getField('pid');
		}
		$company_id = $id;
		$dept_info['company_name'] = M('Dept')->where(array('id'=>$company_id))->getField('name');
		
		$this->assign('dept_info',$dept_info);
		$this->display();
	}
	public function validate($model=''){
		
		if($this->isAjax()){
			if(!$this->_request('clientid','trim') || !$this->_request($this->_request('clientid','trim'),'trim')){
				$this->ajaxReturn("","",3);
			}
			$where[$this->_request('clientid','trim')] = array('eq',$this->_request($this->_request('clientid','trim'),'trim'));
			if($where['dept_name']){
				$where['name'] = $where['dept_name'];
				unset($where['dept_name']);
				if($_REQUEST['belong_dept_id']){
					$where['pid'] = $_REQUEST['belong_dept_id'];
				}
			}
			if($where['belong_dept_id']){
				$where['pid'] = $where['belong_dept_id'];
				unset($where['belong_dept_id']);
			}
			if(!$where['name'] && $this->_request('name','trim')){
				$where['name'] = $this->_request('name','trim');
			}
			if(!$where['pid'] && $this->_request('pid','trim')){
				$where['pid'] = $this->_request('pid','trim');
			}
			//针对编辑的情况
			if($this->_request('id','intval',0)){
				$where[M('Position')->getpk()] = array('neq',$this->_request('id','intval',0));
			}
			
			if($this->_request('clientid','trim')) {
				$model = $model?$model:MODULE_NAME;
				$open=fopen("C:\log.txt","a" );
				fwrite($open,json_encode($_REQUEST)."\r\n");
				fwrite($open,json_encode($where)."\r\n");
				fclose($open);
				
				if (M($model)->where($where)->find()) {
					$this->ajaxReturn("","",1);
				} else {
					$this->ajaxReturn("","",0);
				}
			}else{
				$this->ajaxReturn("","",0);
			}
		}
	}
}
?>
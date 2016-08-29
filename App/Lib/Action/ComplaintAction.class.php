<?php
/**
 * 意见箱action
 */
class ComplaintAction extends CommonAction {
	
	public function index(){
		$model = M("complaint");
		$id = get_user_id();
		$where['visible'] = array(array('eq',$id),array('eq','0'),'or');
		$res = $this -> _list($model, $where);
		foreach ($res as $k=>$v){
			if($v['anony']){
				$res[$k]['anony'] = get_user_info($v['user_id'],'name');
			}else{
				$res[$k]['anony'] = "匿名";
			}
			if(empty($v['is_read'])){
				$res[$k]['is_read'] = '0';
			}
			if(!empty($v['is_read'])){
				$tmp = explode(',',$v['is_read']);
				if(!in_array($id,$tmp)){
					$res[$k]['is_read'] = '0';
				}
			}
		}
		$this -> assign('res',$res);
		$this -> display();
	}
	public function add(){
		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
		$this -> display();
	}
	
	function _insert(){
		$model = M("complaint");
		$data['title']=$_POST['title'];
		$data['anony']=$_POST['anony'];
		$data['add_file']=$_POST['add_file'];
		$data['opinion']=$_POST['opinion'];
		$data['user_id'] = get_user_id();
		$data['create_time'] = time();
		$data['visible'] = $_POST['visible'];
		if($data['visible'] != '0'){
			$visi = explode('|',rtrim($data['visible'],'|'));
			foreach ($visi as $k=>$v){
				$data['visible'] = $v;
				$list = $model -> add($data);
			}
		}else{
			$list = $model -> add($data);
		}
		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl',get_return_url());
			$this -> success('发送成功!');
		} else {
			//失败提示
			$this -> error('发送失败!');
		}
	}
	
	public function read(){
		$id = is_mobile_request()?$_REQUEST['idd']:$_REQUEST['id'];
		$user_is = get_user_id();
		$where['id'] = array('eq', $id);
		$model = M('complaint');
		$read = $model -> where($where) -> getField('is_read');
		$is_read = empty($read) ? $user_is : $read. ','.$user_is ;
		$model -> where($where) -> setField('is_read', $is_read);
		$res = $model->find($id);
		if($res['anony']){
			$res['anony'] = get_user_info($res['user_id'],'name');
		}else{
			$res['anony'] = "匿名";
		}
		$this -> assign('vo',$res);
		$this -> display();
	}
	
	function upload(){
		$this->_upload();
	}
	function down(){
		$this->_down();
	}
}
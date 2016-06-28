<?php
/**
 * 意见箱action
 */
class ComplaintAction extends CommonAction {
	public function index(){
		$model = M("complaint");
		$compla = $model -> field("id,visible") -> select();
		$id = get_user_id();
		$arr = array();
		foreach ($compla as $k=>$v){
			$arr = explode('|',rtrim($v['visible'],'|'));
			foreach ($arr as $vo){
				if($id == $vo){
					$compla[$k]['flag'] = $vo;
				}
			}
			if($v['visible'] == '0' || isset($compla[$k]['flag'])){
				$ids[] = $v['id'];
			}
		}
		$where['id'] = array('in',$ids);
		$res = $this -> _list($model, $where);
		foreach ($res as $k=>$v){
			if($v['anony']){
				$res[$k]['anony'] = get_user_info($v['user_id'],'name');
			}else{
				$res[$k]['anony'] = "匿名";
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
		$this -> assign('user_id',get_user_id());
		$this -> assign("create_time",time());
		$this -> display();
	}
	
	public function read(){
		$id = $_REQUEST['id'];
		$vo = M("complaint")->find($id);
		$this -> assign('vo',$vo);
		$this -> display();
	}
	
	function upload(){
		$this->_upload();
	}
}
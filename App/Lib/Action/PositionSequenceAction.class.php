<?php
class PositionSequenceAction extends CommonAction {
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_POST['li_sequence_number'])) {
			$map['sequence_number'] = array('like','%'.$_POST['li_sequence_number'].'%');
		}
		if (!empty($_POST['li_sequence_name'])) {
			$map['sequence_name'] = array('like','%'.$_POST['li_sequence_name'].'%');
		}
// 		dump($map);
	}
	
	function index(){
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$model = M('PositionSequence');
		if (!empty($model)) {
			$info = $this -> _list($model, $map,'sequence_degree');
		}
		$this->display();
	}

	function add(){
		if(IS_POST)
    	{
    		$model = M('PositionSequence');
    		if($model->create(I('post.'), 1))
    		{
    			if($id = $model->add())
    			{
    				$this->success('添加成功！', U('index'));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}

	}
	
	function edit_sequence(){
		$id = $_REQUEST['id'];
		$model = M('PositionSequence');
		$where = array('id'=>$id);
		
		if(FALSE !== $_POST['sequence_number']){
			$data['sequence_number'] = $_POST['sequence_number'];
		}
		if(FALSE !== $_POST['sequence_name']){
			$data['sequence_name'] = $_POST['sequence_name'];
		}
		if(FALSE !== $_POST['sequence_degree']){
			$data['sequence_degree'] = $_POST['sequence_degree'];
		}
		$res = $model-> where($where)->setField($data);
		
		if($res){
			$this->ajaxReturn(1,1,0);
		}else{
			$this->ajaxReturn(null,null,0);
		}
    	
	}
	
	function del_sequence(){
		$id = $_REQUEST['id'];
		$where['id'] = array('in', $id);
		$result = M("PositionSequence")->where($where)->delete();
			if ($result) {
				$this -> ajaxReturn('', "删除成功", 1);
			} else {
				$this -> ajaxReturn('', "删除失败", 0);
			}
	}
	
	Public function checkNo () {
		$sequence_number = $this->_post('sequence_number');
		$where = array('sequence_number' => $sequence_number);
		if (M('PositionSequence')->where($where)->getField('id')) {
			echo 'false';
		} else {
			echo 'true';
		}
	}
	
	Public function checkName () {
		$sequence_name = $this->_post('sequence_name');
		$where = array('sequence_name' => $sequence_name);
		if (M('PositionSequence')->where($where)->getField('id')) {
			echo 'false';
		} else {
			echo 'true';
		}
	}
	
	Public function checkDegree () {
		$sequence_degree = $this->_post('sequence_degree');
		$where = array('sequence_degree' => $sequence_degree);
		if (M('PositionSequence')->where($where)->getField('id')) {
			echo 'false';
		} else {
			echo 'true';
		}
	}
	
}
?>
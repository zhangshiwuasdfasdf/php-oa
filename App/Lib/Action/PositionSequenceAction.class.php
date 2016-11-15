<?php
class PositionSequenceAction extends CommonAction {
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_POST['sequence_number'])) {
			$map['sequence_number'] = array('like','%'.$_POST['sequence_number'].'%');
		}
		if (!empty($_POST['sequence_name'])) {
			$map['sequence_name'] = array('like','%'.$_POST['sequence_name'].'%');
		}
// 		dump($map);
	}
	
	function index(){
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$model = D('PositionSequence');
		if (!empty($model)) {
			$info = $this -> _list($model, $map);
		}
		$this->display();
	}

	function add(){
		if(IS_POST)
    	{
    		$model = D('PositionSequence');
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
	
}
?>
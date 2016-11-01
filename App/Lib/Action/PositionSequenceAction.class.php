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
		$model = M('PositionSequence');
		if (!empty($model)) {
			$info = $this -> _list($model, $map);
		}
		$this->display();
	}

	function add(){
		//dump($_POST);die;
		if(IS_POST){
			$model=M("PositionSequence");
			if($model->create(I('post.'),1)){
				if($model->add())
    			{
    				$this->success('添加成功！', U('index'));
    			}
    		}
    		$this->error($model->getError());
			}	
		}
	

}
?>
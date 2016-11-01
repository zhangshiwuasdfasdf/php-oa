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
		$data['sequence_number']=$_POST['sequence_number'];
		$data['sequence_name']=$_POST['sequence_name'];
		$data['sequence_degree']=$_POST['sequence_degree'];
		$result=M('PositionSequence')->add($data);
		dump($result);die;
		if ($result) {
				$this -> ajaxReturn('', "删除成功", 1);
			} else {
				$this -> ajaxReturn('', "删除失败", 0);
			}
	}
	
}
?>
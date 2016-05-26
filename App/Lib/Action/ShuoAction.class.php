<?php
class ShuoAction extends CommonAction {
	public function  index(){
		$model = M('user');
		$map['is_del'] = 0;
		$map['bianqian'] = array('neq','');
		if (!empty($model)) {
			$this -> _list($model, $map);
		}
		$this -> display();
	}
}
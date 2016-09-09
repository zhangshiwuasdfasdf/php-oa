<?php
class ParameterAction extends CommonAction {
	protected $config = array('app_type' => 'common');
	public function index(){
		$node = M("Room_config");
		$menu = array();
		$menu = $node -> where($map) -> field('id,pid,name,is_del') -> order('sort asc') -> select();
		$tree = list_to_tree($menu);
		$this -> assign('menu', popup_tree_menu($tree));
		$list = $node -> where(array('is_del'=> 0,'pid'=>0)) -> order('sort asc') -> getField('id,name');
		$this -> assign('dept_grade_list',$list);
		$this -> display();
	}
	
	public function getConfig(){
		$id = $_REQUEST['id'];
		$node = M("Room_config");
		$vo = $node -> find($id);
		if ($this -> isAjax()) {
			if ($vo !== false) {// 读取成功
				$this -> ajaxReturn($vo, "读取成功", 1);
			} else {
				$this -> ajaxReturn(0, "读取失败", 0);
				die ;
			}
		}
	}
	
	function updConfig(){
		$model = D("room_config");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		/*保存当前数据对象 */
		$list = $model -> save();
		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl', U("room/config"));
			$this -> success('编辑成功!');
		} else {//失败提示
			$this -> error('编辑失败!');
		}
	}
}
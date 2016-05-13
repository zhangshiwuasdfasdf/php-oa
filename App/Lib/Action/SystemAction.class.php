<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class SystemAction extends CommonAction {
	//过滤查询字段
	protected $config=array('app_type'=>'asst');
	function _search_filter(&$map) {
		if (!empty($_POST['keyword'])) {
			$map['type|name|code'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}

	function check_reg() {

	}

	function save() {

	}

	function index(){
		$this->get_auth();
		$this->assign("SERVER_NAME",$this->_SERVER('SERVER_NAME'));
		$this -> display();
	}

	function RandAbc($length = "") {//返回随机字符串
		$str = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
		return str_shuffle($str);
	}

	function get_auth(){
		 $server_info = $this->_SERVER('SERVER_NAME').'|'.$this->_SERVER('REMOTE_ADDR');
		 $server_info .= '|'.$this->_SERVER('DOCUMENT_ROOT');

		$result = @file_get_contents('http://www.smeoa.com/get_auth.php?'.base64_encode($server_info));
		return $result;
	}

	function _GET($n) {return isset($_GET[$n]) ? $_GET[$n] : NULL; }
	function _SERVER($n) { return isset($_SERVER[$n]) ? $_SERVER[$n] : '[undefine]'; }

}
?>
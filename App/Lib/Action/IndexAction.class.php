<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class IndexAction extends CommonAction {
	protected $config=array('app_type'=>'asst');
	// 框架首页

	public function index() {
		//$this -> redirect("Home/index");die;
		
		$resume = M("User_suggest_detail") -> find();
		$sug = M('User_suggest') -> where("user_id = ".get_user_id()) -> find();
		if(!is_null($resume) && !is_null($sug)){
			$this -> redirect("Home/suggest");die;
		}else{
			$this -> redirect("Home/index");die;
		}
		
		
		//$list = M('user_idea')->where(array('user_id'=>get_user_id()))->find();
		/*if($list){
			$this -> redirect("Home/index");
		}else{
			$this->assign("js_file","js/index");
			$this -> redirect("Home/survey");
		}*/
	}
}
?>
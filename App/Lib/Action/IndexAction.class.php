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
		//$this -> redirect("Home/index");
		
		//判断是否完成了简历的填写
		$resume = M("user_resume")-> where("user_id = ".get_user_id())->find();
		$info = M("user") -> find(get_user_id());
		if(is_null($resume)){
			$this -> success('请先去完善个人简历,在进行其他操作。谢谢配合!!!',U("Profile/resume"));die;
		}elseif(empty($info['duty']) || empty($info['mobile_tel']) || empty($info['email'])){
			$this -> success('请先去完善个人信息,在进行其他操作。谢谢配合!!!',U("Profile/index"));die;
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
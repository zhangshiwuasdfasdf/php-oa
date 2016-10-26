<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class FlowLeaveViewModel extends ViewModel {
	public $viewFields=array(
		'FlowLeave'=>array('*'),
		'Flow'=>array('type','user_id','user_name','step','is_del','_on'=>'FlowLeave.flow_id=Flow.id')
		);
}
?>
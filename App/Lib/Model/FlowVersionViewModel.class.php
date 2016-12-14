<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class FlowVersionViewModel extends ViewModel {
	public $viewFields=array(
		'FlowVersion'=>array('id','flow_type_setting_id','status','version','create_time','version_remark','is_del'),
		'FlowTypeSetting'=>array('flow_name','_on'=>'FlowVersion.flow_type_setting_id=FlowTypeSetting.id')
		);
}
?>
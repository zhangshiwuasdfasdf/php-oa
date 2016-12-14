<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class FlowNodeViewModel extends ViewModel {
	public $viewFields=array(
		'FlowNode'=>array('id','flow_version_id','node_type','node_name','rule_expression','rule_explain','is_del','sort','_type'=>'LEFT'),
		'FlowVersion'=>array('version','flow_type_setting_id','_on'=>'FlowNode.flow_version_id=FlowVersion.id','_type'=>'LEFT'),
		'FlowTypeSetting'=>array('module_name','flow_name','_on'=>'FlowVersion.flow_type_setting_id=FlowTypeSetting.id'),
		);
}
?>
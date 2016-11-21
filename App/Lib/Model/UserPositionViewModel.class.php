<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class UserPositionViewModel extends ViewModel {
	public $viewFields = array(
			'RUserPosition'=>array('*', '_type'=>'LEFT'),
			'PositionSequence'=>array('sequence_number','sequence_name','sequence_degree','_on'=>'RUserPosition.position_sequence_id=PositionSequence.id', '_type'=>'LEFT'),
			'Position'=>array('position_name','_on'=>'RUserPosition.position_id=Position.id', '_type'=>'LEFT'),
			'User'=>array('emp_no','name','_on'=>'RUserPosition.user_id=User.id', '_type'=>'LEFT'),
			'Dept'=>array('name'=>'dept_name','_on'=>'RUserPosition.dept_id=Dept.id', '_type'=>'LEFT'),
			'StatusManage'=>array('stuff_status','_on'=>'RUserPosition.user_id=StatusManage.user_id', '_type'=>'LEFT'),
	);
}
?>
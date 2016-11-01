<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class UserView3Model extends ViewModel {
	public $viewFields=array(
		'User'=>array('id','emp_no','name','letter','position_id','email','duty','office_tel','mobile_tel','pic','bk_pic','birthday','sex','password','is_del','available_hour','more_role','_type'=>'LEFT'),
		'Position'=>array('dept_id','position_name','_on'=>'Position.id=User.position_id','_type'=>'LEFT'),
		'Dept'=>array('name'=>'dept_name','_on'=>'Dept.id=Position.dept_id')
		);
}
?>
<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class DeptViewModel extends ViewModel {
	public $viewFields=array(
		'Dept'=>array('id','pid','dept_no','dept_grade_id','name','short','sort','remark','is_del','_type'=>'LEFT'),
		'User'=>array('id'=>'user_id','name'=>'user_name','_on'=>'User.pos_id=Dept.id')
		);
}
?>
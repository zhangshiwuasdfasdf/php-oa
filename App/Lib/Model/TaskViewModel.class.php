<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class TaskViewModel extends ViewModel {
	public $viewFields=array(
		'Task'=>array('id','task_no','pid','name','content','executor','add_file','expected_time','user_id','user_name','dept_id','dept_name','create_time','update_time','update_user_id','update_user_name','_type'=>'LEFT'),
		'TaskLog'=>array('status'=>'status','_on'=>'TaskLog.task_id=Task.id'),
		);
}
?>
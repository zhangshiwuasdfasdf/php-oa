<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class WeeklyReportViewModel extends ViewModel {
	public $viewFields=array(
		'WeeklyReport'=>array('*','_type'=>'LEFT'),
		'User'=>array('pos_id'=>'pos_id','_on'=>'User.id=WeeklyReport.user_id')
		);
}
?>
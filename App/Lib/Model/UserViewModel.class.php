<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class UserViewModel extends ViewModel {
	public $viewFields=array(
// 		'User'=>array('id','emp_no','name','letter','dept_id','position_id','pos_id','rank_id','email','duty','office_tel','mobile_tel','pic','bk_pic','birthday','sex','password','is_del','available_hour','more_role','_type'=>'LEFT'),
// 		'Position'=>array('name'=>'position_name','sort'=>'position_sort','_on'=>'Position.id=User.position_id','_type'=>'LEFT'),
// 		'Rank'=>array('name'=>'rank_name','_on'=>'Rank.id=User.rank_id','_type'=>'LEFT'),
// 		'Dept'=>array('name'=>'dept_name','_on'=>'Dept.id=User.dept_id'),
			
		'User'=>array('id','emp_no','name','letter','email','duty','office_tel','mobile_tel','pic','bk_pic','birthday','sex','password','is_del','available_hour','_type'=>'LEFT'),
		'RUserPosition'=>array('position_id','dept_id','position_id'=>'pos_id','_on'=>'RUserPosition.user_id=User.id and RUserPosition.is_major=1','_type'=>'LEFT'),
		'Position'=>array('position_name','_on'=>'RUserPosition.position_id=Position.id','_type'=>'LEFT'),
		'Dept'=>array('name'=>'dept_name','_on'=>'RUserPosition.dept_id=Dept.id')
		);
}
?>
<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class PrivilegeViewModel extends ViewModel {
	public $viewFields=array(
		'Privilege'=>array('*'),
		'menu_new'=>array('menu_name','_on'=>'menu_new.id=Privilege.menu_new_id'),
		);
}
?>
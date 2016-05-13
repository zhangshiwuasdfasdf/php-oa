<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class ProductViewModel extends ViewModel {
	public $viewFields=array(
		'Product'=>array('*'),
		'ProductType'=>array('name'=>'type_name','_on'=>'ProductType.id=Product.type')
		);
}
?>
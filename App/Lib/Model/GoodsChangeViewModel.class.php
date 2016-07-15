<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class GoodsChangeViewModel extends ViewModel {
	public $viewFields=array(
		'GoodsChange'=>array('*'),
		'Goods'=>array('goods_name','cate_id','_on'=>'GoodsChange.goods_id=Goods.id'),
		'GoodsCategory'=>array('name'=>'cate_name','_on'=>'Goods.cate_id=GoodsCategory.id'),
		'User'=>array('name'=>'user_name','_on'=>'GoodsChange.user_id=User.id'),
		);
}
?>
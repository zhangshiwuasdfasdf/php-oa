<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/
/*
 * 测试关系表
 */
class UserRecordModel extends RelationModel {
	protected $_link=array(
		'User'=>array(
				'mapping_type'=>HAS_MANY,
				'class_name'=>'User',
				'foreign_key'=>'rank_id',
				'mapping_fields'=>'name',
				'as_fields'=>'name:user_name'
		),
	);
}
?>
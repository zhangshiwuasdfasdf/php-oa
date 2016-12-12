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
class DeptModel extends RelationModel {
	protected $_link=array(
		'R'=>array(
				'mapping_type'=>MANY_TO_MANY,
				'class_name'=>'Position',
				'foreign_key'=>'dept_id',
				'relation_foreign_key'=>'position_id',
                'relation_table'=>'smeoa_r_dept_position'
		),
	);
}
?>
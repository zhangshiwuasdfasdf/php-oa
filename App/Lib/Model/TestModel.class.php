<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/
/*
 * 测试Mongo模型
 */
class TestModel extends MongoModel  {
	protected $trueTableName = 'col';
	protected $dbName = 'test';
	
	Protected $_idType = self::TYPE_INT;
    protected $_autoinc =  true;
     
	protected $connection = array(
			'db_type' => 'mongo',
			'db_host' => 'localhost',
			'db_port' => '27017',
	);
		
}
?>
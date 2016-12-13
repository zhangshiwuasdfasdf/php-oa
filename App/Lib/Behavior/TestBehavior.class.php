<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class TestBehavior extends Behavior {
	protected $options = array(
			'TEST_PARAM'=>false,
	);
	public function run(&$params) {
		if(C('TEST_PARAM')){
			echo 'RUNTEST BEHAVIOR'.$params;
			$open=fopen("C:\log.txt","a" );
			fwrite($open,'RUNTEST BEHAVIOR'."\r\n");
			fwrite($open,json_encode($params)."\r\n");
			fclose($open);
		}
	}
}
?>
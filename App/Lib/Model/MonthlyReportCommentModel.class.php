<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class MonthlyReportCommentModel extends CommonModel {
	// 自动验证设置 
	function _after_insert($data,$options){
		$doc_id=$data["doc_id"];
		M("MonthlyReport")->where("id=$doc_id")->setField("update_time",time());
	}
}	
?>
<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class CrmModel extends CommonModel {
	// 自动验证设置
	function _before_update(&$data,$options){
		$old_data=M("Crm")->find($data['id']);
		$diff=array_diff_assoc($data,$old_data);
		$diff=array_keys($diff);
		
		$desc=array('name'=>'姓名','mobile_tel'=>'手机','district'=>'小区','need'=>'客户需求','source'=>'客户来源','age'=>'年龄','work'=>'职业');
		
		if(!empty($diff)){
			foreach ($diff as $val) {
			 $model->need=implode(",",$model->need);
			 	if(is_array($data[$val])){
			 		$new=implode(",",$data[$val]);
			 	}else{
			 		$new=$data[$val];
			 	}
				$comment.=$desc[$val]."：".$old_data[$val]."->".$new." ;  ";
			}					
			$model = D("CrmLog");
			$log['user_id']=get_user_id();
			$log['user_name']=get_user_name();
			$log['create_time']=time();
			$log['crm_id']=$data['id'];
			$log['emp_no']=get_emp_no();
			$log['comment']=$comment;
			$model->add($log);			
		}		
	}	
}
?>
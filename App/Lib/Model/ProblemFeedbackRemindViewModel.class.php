<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class ProblemFeedbackRemindViewModel extends ViewModel {
	public $viewFields=array(
		'ProblemFeedbackRemind'=>array('*'),
		'ProblemFeedback'=>array('problem_no','create_time','create_user_name','dept_name','pos_name','emergency','title','type','status','deal_user_name','_on'=>'ProblemFeedbackRemind.problem_feedback_id=ProblemFeedback.id')
		);
}
?>
<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class PositionViewModel extends ViewModel {
	public $viewFields = array(
			'position'=>array('*'),
			'position_sequence'=>array('sequence_number','sequence_name','sequence_degree','_on'=>'position.position_sequence_id=position_sequence.id', '_type'=>'LEFT'),
	);
}
?>
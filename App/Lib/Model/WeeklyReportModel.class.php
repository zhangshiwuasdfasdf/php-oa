<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class WeeklyReportModel extends CommonModel {
	// 自动验证设置
	function _after_insert($data, $options) {
		$pid = $data['id'];
		$data_detail['pid'] = $pid;

		$subject = array_filter(I('subject'));
		$model_detail = M("WeeklyReportDetail");

		foreach ($subject as $key => $val) {
			$data_detail['type'] = 1;
			$data_detail['subject'] = $val;
			$data_detail['item'] = implode("|||", I("item_$key"));
			$data_detail['start_time'] = implode("|||", I("start_time_$key"));
			$data_detail['end_time'] = implode("|||", I("end_time_$key"));
			$data_detail['status'] = implode("|||", I("status_$key"));
			$model_detail -> add($data_detail);
		}

		$plan_subject = array_filter(I('plan_subject'));
		$plan_item = I('plan_item');

		$plan_start_time = I('plan_start_time');
		$plan_end_time = I('plan_end_time');

		$plan_priority = I('plan_priority');
		$is_need_help = I('is_need_help');

		foreach ($plan_subject as $key => $val) {
			$data_detail['type'] = 2;
			$data_detail['subject'] = $val;
			$data_detail['item'] = $plan_item[$key];
			$data_detail['start_time'] = $plan_start_time[$key];
			$data_detail['end_time'] = $plan_end_time[$key];
			$data_detail['priority'] = $plan_priority[$key];
			$data_detail['is_need_help'] = $is_need_help[$key];
			$model_detail -> add($data_detail);
		}
		$plan_subject = $data['plan_subject'];
	}

	function _after_update($data, $options) {
		$pid = $data['id'];
		$data_detail['pid'] = $pid;
		
		$subject = array_filter(I('subject'));
		$model_detail = M("WeeklyReportDetail");
		$model_detail->where($data_detail)->delete();
		
		foreach ($subject as $key => $val) {
			$data_detail['type'] = 1;
			$data_detail['subject'] = $val;
			$data_detail['item'] = implode("|||", I("item_$key"));
			$data_detail['start_time'] = implode("|||", I("start_time_$key"));
			$data_detail['end_time'] = implode("|||", I("end_time_$key"));
			$data_detail['status'] = implode("|||", I("status_$key"));
			$model_detail -> add($data_detail);
		}

		$plan_subject = array_filter(I('plan_subject'));
		$plan_item = I('plan_item');

		$plan_start_time = I('plan_start_time');
		$plan_end_time = I('plan_end_time');

		$plan_priority = I('plan_priority');
		$is_need_help = I('is_need_help');

		foreach ($plan_subject as $key => $val) {
			$data_detail['type'] = 2;
			$data_detail['subject'] = $val;
			$data_detail['item'] = $plan_item[$key];
			$data_detail['start_time'] = $plan_start_time[$key];
			$data_detail['end_time'] = $plan_end_time[$key];
			$data_detail['priority'] = $plan_priority[$key];
			$data_detail['is_need_help'] = $is_need_help[$key];
			$model_detail -> add($data_detail);
		}
		$plan_subject = $data['plan_subject'];
	}
	
	

}
?>
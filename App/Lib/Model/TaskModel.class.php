<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class TaskModel extends CommonModel {
	// 自动验证设置
	protected $_validate = array( array('name', 'require', '文件名必须', 1), array('content', 'require', '内容必须'), );

	function _before_insert(&$data, $options) {
		$sql = "SELECT CONCAT(year(now()),'-',LPAD(count(*)+1,4,0)) task_no FROM `" . $this -> tablePrefix . "task` WHERE 1 and year(FROM_UNIXTIME(create_time))>=year(now())";
		$rs = $this -> db -> query($sql);
		if ($rs) {
			$data['task_no'] = $rs[0]['task_no'];
		} else {
			$data['task_no'] = date('Y') . "-0001";
		}
	}

	function _after_insert($data, $options) {
		$executor_list = $data['executor'];
		//$executor_list="管理部|dept_6;副总1003/副总|1003;经理3001/经理|3001;";
		$executor_list = array_filter(explode(';', $executor_list));

		if (!empty($executor_list)) {
			foreach ($executor_list as $key => $val) {
				$tmp = explode('|', $val);
				$executor_name = $tmp[0];
				$executor = $tmp[1];

				if (strpos($executor, "dept_") !== false) {
					$type = 2;
					$executor = str_replace('dept_', '', $executor);
				} else {
					$type = 1;
					$this -> _send_mail($data['id'],$executor);
				}

				$log_data['executor'] = $executor;
				$log_data['executor_name'] = $executor_name;
				$log_data['type'] = $type;
				$log_data['assigner'] = $data['user_id'];
				$log_data['task_id'] = $data['id'];
				M("TaskLog") -> add($log_data);
			}
		}
	}

	function forword($task_id, $executor_list) {
		$executor_list = array_filter(explode(';', $executor_list));

		if (!empty($executor_list)) {
			foreach ($executor_list as $key => $val) {
				$tmp = explode('|', $val);
				$executor_name = $tmp[0];
				$executor = $tmp[1];

				if (strpos($executor, "dept_") !== false) {
					$type = 2;
					$executor = str_replace('dept_', '', $executor);
				} else {
					$type = 1;
				}

				$log_data['executor'] = $executor;
				$log_data['executor_name'] = $executor_name;
				$log_data['type'] = $type;
				$log_data['assigner'] = get_user_id();
				$log_data['task_id'] = $task_id;
				M("TaskLog") -> add($log_data);
			}
		}
	}

	function _send_mail($task_id,$executor) {
		$executor_info=M("User")->where("id=$executor")->find();
		
		$email=$executor_info['email'];
		$user_name=$executor_info['name'];
				
		$info = M("Task") -> where("id=$task_id") -> find();

		$title="您有新的任务：".$info['name'];
		
		$body="您好，{$user_name}，{$info['user_name']} 有一个任务需要您的协助！</br>";
		$body.="任务主题：{$info['name']}</br>";
		$body.="任务时间：{$info['expected_time']}</br>";
		$body.="任务发起人：{$info['user_name']}</br>";
		$body.="请与{$info['user_name']}做好沟通，尽快完成任务。</br>";
		$body.="点击查看任务详情：http://". $_SERVER['SERVER_NAME'].U('Task/read','id='.$info['id'])."</br>";
		$body.="霞湖世家，感谢有您！</br>";

		send_mail($email, $user_name, $title, $body);
	}

}
?>
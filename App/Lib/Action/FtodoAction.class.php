<?php
class FtodoAction extends CommonAction {
	function index(){
		//任务
		$this -> assign("folder", 'confirm');
		$where_log['executor'] = get_user_id();
		$where_log['transactor'] = get_user_id();
		$where_log['_logic'] = 'or';
		$task_list1 = M("TaskLog") -> where($where_log) -> getField('task_id id,task_id');

		$where_task['executor'] = array('like',array('%'.get_user_name().'|'.get_user_id().';'.'%','%'.get_dept_name().'|'.'dept_'.get_dept_id().';'.'%'),'or');
		$task_list = M("Task")->field('id') -> where($where_task) -> select();
		$task_list2 = array();
		foreach ($task_list as $v){
			$task_list2[] = $v['id'];
		}
		$task_list = array_unique(array_merge($task_list1,$task_list2));
		$where['id'] = array('in', $task_list);
		
		$model = D('Task');
		if (!empty($model)) {
			$res = $this -> _list($model, $where);
			$model_task_log = D('TaskLog');
			$task_extension = array();
			foreach ($res as $k=>$v){
				$r = $model_task_log->where(array('task_id'=>array('eq',$v['id'])))->find();
				if(!empty($r)){
					$task_extension[$k]['status'] = $r['status'];
				}
			}
			$this -> assign('task_extension', $task_extension);
		}
		//审批
		$emp_no = get_emp_no();
		$FlowLog = M("FlowLog");
		$where1['emp_no'] = $emp_no;
		$where1['_string'] = "result is null";
		$log_list = $FlowLog -> where($where1) -> field('flow_id') -> select();

		$log_list = rotate($log_list);
		if (!empty($log_list)) {
			$map['id'] = array('in', $log_list['flow_id']);
		} else {
			$map['_string'] = '1=2';
		}
		//$map加上自己园区的
		if(isHeadquarters(get_user_id())==0){//总部
			$map['dept_id'] = array('in',get_child_dept_all(1));
		}elseif (isHeadquarters(get_user_id())>0){//园区
			$map['dept_id'] = array('in',get_child_dept_all(isHeadquarters(get_user_id())));
		}elseif (isHeadquarters(get_user_id())==-1){//副总
			$map['dept_id'] = array('in',get_child_dept_all(86));
		}elseif (isHeadquarters(get_user_id())==-2){//总经理
			$map['dept_id'] = array('in',get_child_dept_all(27));
		}
		$model = D("FlowView");
		if(!empty($model)){
			$flow_list = $this -> _list($model, $map ,'',false,'list2');
			foreach ($flow_list as $k=>$v){
				$auth = M('FlowLog')->where(array('flow_id'=>array('eq',$v['id']),'_string'=>'result is not null'))->select();
				if($auth){
					$flow_list[$k]['auth'] = 1;
				}else{
					$flow_list[$k]['auth'] = 0;
				}
				if(!empty($v['confirm'])){
					$confirm = explode('|',$v['confirm']);
					$flowLog = M('FlowLog')->where(array('flow_id'=>array('eq',$v['id']),'_string'=>'result is null'))->find();
					if(!empty($flowLog)){
						$i = array_search($flowLog['emp_no'],$confirm);
					}
					$confirm_name = explode('<>',$v['confirm_name']);
				
					$s = '';
					foreach ($confirm_name as $kk=>$vv){
						if($i===$kk){
							$s.=$vv.'（审批中）'.'->';
						}else{
							$s.=$vv.'->';
						}
					}
					$s = substr($s,0,strlen($s)-4);
					$flow_list[$k]['flow_name'] = $s;
				}
			}
		$this -> assign("list2", $flow_list);
		}
		$this -> display();
	}
}
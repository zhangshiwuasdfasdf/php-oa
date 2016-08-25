<?php
class FtodoAction extends CommonAction {
	function index(){
		//任务
		$this -> assign("folder", 'confirm');
		$where_log['type'] = 1;
		$where_log['status'] = 0;
		$where_log['executor'] = get_user_id();
		$task_list = M("TaskLog") -> where($where_log) -> getField('task_id id,task_id');
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
			$flow_list = $model -> where($map) -> select();
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
			$uid = get_user_id();
			if($uid == '111' || $uid == '13' || $uid == '260' || $uid == '1'){
				$notice = M('notice') -> where(array('folder'=>'95','is_submit'=>2)) -> select();
				if(!empty($notice)){
					$j = count($flow_list);
					foreach ($notice as $k => $v){
						$flow_list[$j]['doc_no'] = '95';
						$flow_list[$j]['type_name'] = '今日头条与公司新闻';
						$flow_list[$j]['create_time'] = $v['create_time'];
						$flow_list[$j]['user_name'] = $v['user_name'];
						$flow_list[$j]['step'] = 20;
						$flow_list[$j]['flow_name'] = "姚一飞 或 张婷 或 彭梦洁 (审批中)";
						$flow_list[$j]['name'] = $v['name'];
						$flow_list[$j]['flag'] = '1';
						$flow_list[$j]['id'] = $v['id'];
						$j++;
					}
				}
			}
			$listRows = get_user_config('list_rows');
			if(is_null($listRows)){$listRows = 12;}
			$now = $_REQUEST['p'];
			if(is_null($now)){$now = '1';}
			$rows =  intval($listRows);
			$offset = $rows * (intval($now) - 1);
			$ress = array_slice($flow_list , $offset , $rows);
			//分页
			import("@.ORG.Util.Page");
			//创建分页对象
			$p = new Page(count($flow_list), $listRows);
			$p -> parameter = $this -> _search();
			//分页显示
			$page = $p -> show();
			$this -> assign("pages", $page);
			$this -> assign("lists", $ress);
		}
		$this -> display();
	}
}
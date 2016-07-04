<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class TaskAction extends CommonAction {
	protected $config = array('app_type' => 'common', 'action_auth' => array('test' => 'admin', 'let_me_do' => 'read', 'accept' => 'read', 'reject' => 'read', 'save_log' => 'read'));

	//过滤查询字段
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['keyword']) && empty($map['64'])) {
			$map['name'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}

	public function index() {
		U('folder', array('fid' => 'about_me'), true, true);
	}

	public function folder() {
		D("Role")->get_auth('Task');
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign('auth', $this -> config['auth']);
		$this -> assign('user_id', get_user_id());

		$where = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($where);
		}

		$fid = $_GET['fid'];
		$this -> assign("fid", $fid);

		switch ($fid) {
			case 'all' :
				$this -> assign("folder_name", '所有任务');
				break;
			case 'about_me' :
				$this -> assign("folder_name", '与我有关的任务');
			
// 				$where_log['assigner'] = get_user_id();
				$where_log['executor'] = get_user_id();
				$where_log['transactor'] = get_user_id();
				$where_log['_logic'] = 'or';
				$task_list1 = M("TaskLog") -> where($where_log) -> getField('task_id id,task_id');
			
// 				$where_task['user_id'] = get_user_id();
				$where_task['executor'] = array('like',array('%'.get_user_name().'|'.get_user_id().';'.'%','%'.get_dept_name().'|'.'dept_'.get_dept_id().';'.'%'),'or');
// 				$where_task['_logic'] = 'or';
				$task_list = M("Task")->field('id') -> where($where_task) -> select();
				$task_list2 = array();
				foreach ($task_list as $v){
					$task_list2[] = $v['id'];
				}
				$task_list = array_unique(array_merge($task_list1,$task_list2));
				$where['id'] = array('in', $task_list);
				break;
			case 'todo' :
				$this -> assign("folder_name", '等待我接受的任务');

				$where_log['type'] = 1;
				$where_log['status'] = 0;
				$where_log['executor'] = get_user_id();
				$task_list = M("TaskLog") -> where($where_log) -> getField('task_id id,task_id');
				$where['id'] = array('in', $task_list);
				break;

			case 'dept' :
				$this -> assign("folder_name", '我们部门的任务');
				$auth = $this -> config['auth'];

				if ($auth['admin']) {
					$where_log['type'] = 2;
					$where_log['executor'] = get_dept_id();
					$task_list = M("TaskLog") -> where($where_log) -> getField('task_id id,task_id');
					$where['id'] = array('in', $task_list);
				} else {
					$where['_string'] = '1=2';
				}
				break;
			case 'no_assign' :
				$this -> assign("folder_name", '不知让谁处理的任务');

				$prefix = C('DB_PREFIX');
				$sql = "select id from {$prefix}task task where not exists (select * from {$prefix}task_log task_log where task.id=task_log.task_id)";
				$task_list = M() -> query($sql);
				$where['id'] = array('in', $task_list[0]);

				break;
			case 'no_finish' :
				$this -> assign("folder_name", '未完成的任务');

				$where_log['status'] = array('lt', 2);
				$task_list = M("TaskLog") -> where($where_log) -> getField('task_id id,task_id');
				$where['id'] = array('in', $task_list);

				break;
			case 'finished' :
				$this -> assign("folder_name", '已完成的任务');
				$where_log['status'] = array('eq', 3);
				$task_list = M("TaskLog") -> where($where_log) -> getField('task_id id,task_id');
				$where['id'] = array('in', $task_list);
				
				break;
			case 'my_task' :
				$this -> assign("folder_name", '我发布的任务');
				$where['user_id'] = get_user_id();
				break;
			case 'my_assign' :
				$this -> assign("folder_name", '我指派的任务');

				$where_log['assigner'] = get_user_id();
				$task_list = M("TaskLog") -> where($where_log) -> getField('task_id id,task_id');
				$where['id'] = array('in', $task_list);
				break;
			case 'ajax_get_by_month' :
				$year = $_POST['year'];
				$month = $_POST['month']<10?'0'.$_POST['month']:$_POST['month'];
				$model = M("Task");
				$where = 'select * from (SELECT id,name,content,expected_time from __TABLE__ where expected_time like "%'.$year.'-'.$month.'%" ORDER BY expected_time desc) aa GROUP BY left(expected_time,10)';
				$task = $model->query($where);
				$this->ajaxReturn($task,'JSON');
				break;
			default :
				break;
		}
		
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
		$this -> display();
	}

	public function edit() {
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$widget['date'] = true;
		$this -> assign("widget", $widget);

		$id = $_REQUEST['id'];
		$model = M("Task");
		$folder_id = $model -> where("id=$id") -> getField('folder');
		$this -> assign("auth", D("SystemFolder") -> get_folder_auth($folder_id));
		$this -> _edit();
	}

	public function del($id) {
		$this -> _destory($id);
	}

	public function add() {
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$widget['date'] = true;
		$this -> assign("widget", $widget);

		$fid = $_REQUEST['fid'];
		$type = D("SystemFolder") -> where("id=$fid") -> getField("folder");
		$this -> assign('folder', $fid);
		$this -> display();
	}

	public function read() {

		$widget['jquery-ui'] = true;
		$widget['editor'] = true;
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign('auth', $this -> config['auth']);
		if(is_mobile_request()){
			$id = $_REQUEST['idd'];
		}else{
			$id = $_REQUEST['id'];
		}
		$this -> assign('task_id', $id);
		$model = M("Task");
		$vo = $model -> find($id);
		
		if(is_mobile_request()){
			$vo['mobile_file'] = mobile_show_file($vo['add_file'],'task');
		}
		$this -> assign('vo', $vo);

		$where_log['task_id'] = array('eq', $id);
		$task_log = M("TaskLog") -> where($where_log) -> select();
		$this -> assign('task_log', $task_log);

		if (empty($vo['executor'])) {
			$this -> assign('no_assign', 1);
		} else {

		}

		$where_accept['status'] = 0;
		$where_accept['task_id'] = $id;
		$where_accept['type'] = 1;
		$where_accept['executor'] = array('eq', get_user_id());
		$task_accept = M("TaskLog") -> where($where_accept) -> find();

		if ($task_accept) {
			$this -> assign('is_accept', 1);
			$this -> assign('task_log_id', $task_accept['id']);
		}

		if ($this -> config['auth']['admin']) {
			$where_dept_accept['status'] = 0;
			$where_dept_accept['task_id'] = $id;
			$where_dept_accept['type'] = 2;
			$where_dept_accept['executor'] = array('eq', get_dept_id());
			$task_dept_accept = M("TaskLog") -> where($where_dept_accept) -> find();
// 			if(empty($task_dept_accept)){
// 				$where_dept_accept['status'] = 0;
// 				$where_dept_accept['task_id'] = $id;
// 				$where_dept_accept['type'] = 1;
// 				$where_dept_accept['executor'] = array('eq', get_user_id());
// 				$task_dept_accept = M("TaskLog") -> where($where_dept_accept) -> find();
// 			}
			if ($task_dept_accept) {
				$this -> assign('is_accept', 1);
				$this -> assign('task_log_id', $task_dept_accept['id']);
			}
		}

		$where_working['status'] = array('in', '1,2');
		$where_working['task_id'] = $id;
		$where_working['transactor'] = array('eq', get_user_id());
		$task_working = M("TaskLog") -> where($where_working) -> find();

		if ($task_working) {
			$this -> assign('is_working', 1);
			$this -> assign('task_working', $task_working);

		}
		$this -> display();
	}

	function let_me_do($task_id) {
		if (IS_POST) {
			M("Task") -> where("id=$task_id") -> setField('executor', get_user_name() . "|" . get_user_id());
			M("Task") -> where("id=$task_id") -> setField('status', 1);

			$data['task_id'] = I(task_id);
			$data['executor'] = get_user_id();
			$data['executor_name'] = get_user_name();
			$data['transactor'] = get_user_id();
			$data['transactor_name'] = get_user_name();
			$data['status'] = 1;
			
			$task_id=I(task_id);
			$list = M("TaskLog") -> add($data);
			if ($list != false) {
				$this->_add_to_schedule($task_id);
				$return['info'] = '接受成功';
				$return['status'] = 1;
				$this -> ajaxReturn($return);
			} else {
				$this -> error('提交成功');
			}
		}
	}

	function accept() {
		if (IS_POST) {
			$task_log_id = I('task_log_id');
			$data['id'] = $task_log_id;
			$data['transactor'] = get_user_id();
			$data['transactor_name'] = get_user_name();
			$data['status'] = 1;
			$list = M("TaskLog") -> save($data);

			$task_id = M("TaskLog") -> where("id=$task_log_id") -> getField('task_id');
			M("Task") -> where("id=$task_id") -> setField('status', 1);

			if ($list != false) {
				$this->_add_to_schedule($task_id);
				$return['info'] = '接受成功';
				$return['status'] = 1;
				$this -> ajaxReturn($return);
			} else {
				$this -> error('提交成功');
			}
		}
	}

	function reject() {
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
		if (IS_POST) {
			$model = D("TaskLog");
			if (false === $model -> create()) {
				$this -> error($model -> getError());
			}
			$model -> transactor = get_user_id();
			$model -> transactor_name = get_user_name();
			$model -> finish_time = toDate(time());
			
			if(is_mobile_request()){
				$model -> id = $_POST['idd'];
			}
			$list = $model -> save();
			
			if ($list !== false) {
				$this -> success('提交成功');
			} else {
				$this -> success('提交失败');
			}
		}
		
		$task_id = I('task_id');
		$where_log1['type'] = 2;
		$where_log1['executor'] = get_dept_id();
		$where_log1['task_id'] = $task_id;
		$task_log1 = M("TaskLog") -> where($where_log1) -> find();
		if ($task_list1) {
			$this -> assign('task_log', $task_log1);
		} else {
			$where_log2['type'] = 1;
			$where_log2['executor'] = get_user_id();
			$where_log2['task_id'] = $task_id;
			$task_log2 = M("TaskLog") -> where($where_log2) -> find();

			if ($task_log2) {
				$this -> assign('task_log', $task_log2);
			}
		}
		$this -> display();
	}

	public function save_log($id) {
		$model = D("TaskLog");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$model -> transactor = get_user_id();
		$model -> transactor_name = get_user_name();
		if ($status == 4) {
			$model -> finish_time = time();
		}

		if(is_mobile_request()){
			$model -> id = $_POST['idd'];
		}
		$list = $model -> save();
		
		$task_log_id = $id;
		$status = I('status');
		$task_id = M("TaskLog") -> where("id=$task_log_id") -> getField('task_id');

		if ($status == 2) {
			M("Task") -> where("id=$task_id") -> setField('status', 2);
		}

		if ($status == 4) {
			$task_id = I('task_id');
			$forword_executor = I('forword_executor');
			D('Task') -> forword($task_id, $forword_executor);
		}

		if ($status > 2) {
			$where_count['task_id'] = array('eq', $task_id);
			$total_count = M("TaskLog") -> where($where_count) -> count();

			$where_count['status'] = array('gt', 2);
			$finish_count = M("TaskLog") -> where($where_count) -> count();
			if ($total_count == $finish_count) {
				M("Task") -> where("id=$task_id") -> setField('status', 3);
				$user_id=M('Task')->where("id=$task_id")->getField('user_id');
				$this->_send_mail_finish($task_id,$user_id);				
			}
		}

		if ($list !== false) {
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('提交成功!');
			//成功提示
		} else {
			$this -> error('提交失败!');
			//错误提示
		}
	}

	function upload() {
		$this -> _upload();
	}

	function down() {
		$this -> _down();
	}

	private function _add_to_schedule($task_id){
		$info=M("Task") -> where("id=$task_id")->find();
		$data['name']=$info['name'];
		$data['content']=$info['content'];
		$data['start_time']=toDate(time());
		$data['end_time']= $info['expected_time'];
		$data['user_id']=get_user_id();
		$data['user_name']=get_user_name();
		$data['priority']=3;
 
		$list=M('Schedule')->add($data);
	}
	
	function _send_mail_finish($task_id,$executor) {
		$executor_info=M("User")->where("id=$executor")->find();
		
		$email=$executor_info['email'];
		$user_name=$executor_info['name'];
				
		$info = M("Task") -> where("id=$task_id") -> find();
		
		$transactor_name=M("TaskLog")->where("task_id=$task_id")->getField('id,transactor_name');
		
		$transactor_name=implode(",",$transactor_name);

		$title="您发布任务已完成：".$info['name'];
				
		$body="您好，{$user_name}，{$transactor_name} 完成了您发起的[{$info['name']}]任务</br>";
		$body.="任务主题：{$info['name']}</br>";
		$body.="任务时间：{$info['expected_time']}</br>";
		$body.="任务执行人：{$transactor_name}</br>";
		$body.="请及时检查任务执行情况，如有问题，请与[{$transactor_name}]进行沟通。</br>";
		$body.="任务完成后请对[任务执行人]表达我们的感谢。</br>";
	
		$body.="点击查看任务详情：http://". $_SERVER['SERVER_NAME'].U('Task/read','id='.$info['id'])."</br>";
		$body.="霞湖世家，感谢有您！</br>";

		send_mail($email, $user_name, $title, $body);
	}
	public function ajax_get_task(){
		D("Role")->get_auth('Task');
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign('auth', $this -> config['auth']);
		$this -> assign('user_id', get_user_id());
		
		$this->ajaxReturn('ok','JSON');
	}
}

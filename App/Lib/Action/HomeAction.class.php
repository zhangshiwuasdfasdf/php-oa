<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class HomeAction extends CommonAction {
	protected $config = array('app_type' => 'asst');
	//过滤查询字段

	function _search_filter(&$map) {
		if (!empty($_POST['keyword'])) {
			$map['type|name|code'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}

	public function index_old() {
		$widget['jquery-ui'] = true;
		$this -> assign("widget", $widget);

		cookie("current_node", null);
		cookie("top_menu", null);

		$config = D("UserConfig") -> get_config();
		$this -> assign("home_sort", $config['home_sort']);
		$this -> assign("ceo_incentive", get_system_config("CEO_INCENTIVE"));
	
		$this -> get_user_info();
 		$this -> _mail_list();
// 		$this -> _flow_list();
// 		$this -> _schedule_list();
		$this -> _notice_list();
// 		$this -> _doc_list();
// 		$this -> _forum_list();
// 		$this -> _news_list();
// 		$this -> _slide_list();
		$this -> _task_list();
		$this -> _shouxing_list();
		$this -> _jinianri_list();
		$this -> _xinjin_list();
		$this -> _daily_list();
		$this -> shuoshuo();
		$this -> display();
	}
	public function index() {
		$widget['jquery-ui'] = true;
		$this -> assign("widget", $widget);
	
		cookie("current_node", null);
		cookie("top_menu", null);
	
		$config = D("UserConfig") -> get_config();
		$this -> assign("home_sort", $config['home_sort']);
		$this -> assign("ceo_incentive", get_system_config("CEO_INCENTIVE"));
	
		$this -> get_user_info();
		$this -> _mail_list();
		$this -> _notice_list_new();
		$this -> _plan_work();
		$this -> _memo_task();
		$this -> _bbs_info();
		$this -> _task_list();
//		$this -> _shouxing_list();
//		$this -> _jinianri_list();
//		$this -> _xinjin_list();
//		$this -> _daily_list();
//		$this -> shuoshuo();
		
//		$menu = array();
//		$menu = M('NodeNew') -> field('id,pid,name,sort,url,remark') -> order('sort asc') -> select();
//		$tree = list_to_tree($menu, 0);
//		$this -> assign("left_menu", $tree);
// 		dump($tree);
// 		die;
		
// 		$left_menu = array();
// 		$left_menu['个人中心']['用户设置']['用户资料'] = U('profile/index');
// 		$left_menu['个人中心']['用户设置']['修改密码'] = U('profile/password');
// 		$left_menu['个人中心']['用户设置']['个人简历'] = U('profile/resume');
// 		$left_menu['个人中心']['用户设置']['用户设置'] = U('user_config/index');
// 		$left_menu['个人中心']['每日说说'] = U('shuo/index');
// 		$left_menu['个人中心']['代办事项'] = U('ftodo/index');
// 		$left_menu['个人中心']['联系人'] = U('contact/index');
// 		$left_menu['个人中心']['意见箱'] = U('complaint/index');
// 		$left_menu['个人中心']['日程'] = U('schedule/index');
// 		$left_menu['个人中心']['消息'] = U('message/index');
// 		$left_menu['个人中心']['日报'] = U('daily_report/index');
// 		$left_menu['个人中心']['周报'] = U('weekly_report/index');
// 		$left_menu['个人中心']['月报'] = U('monthly_report/index');
		
// 		$left_menu['人力资源']['请假/调休单'] = U('flow/getlist?type=39');
// 		$left_menu['人力资源']['外勤/出差单'] = U('flow/getlist?type=46');
// 		$left_menu['人力资源']['用人申请流程'] = U('flow/getlist?type=48');
// 		$left_menu['人力资源']['员工调岗/调职申请'] = U('flow/getlist?type=52');
// 		$left_menu['人力资源']['试用期评估表'] = U('flow/getlist?type=50');
// 		$left_menu['人力资源']['员工调薪申请'] = U('flow/getlist?type=56');
// 		$left_menu['人力资源']['转正申请'] = U('flow/getlist?type=51');
// 		$left_menu['人力资源']['离职申请流程'] = U('flow/getlist?type=49');
// 		$left_menu['人力资源']['离职交接流程'] = U('flow/getlist?type=40');
// 		$left_menu['人力资源']['出勤证明流程'] = U('flow/getlist?type=47');
// 		$left_menu['人力资源']['加班调休申请'] = U('flow/getlist?type=57');
// 		$left_menu['人力资源']['每月考勤表'] = U('attendance/table');
// 		$left_menu['人力资源']['每月打卡信息'] = U('attendance/index');
		
// 		$left_menu['行政管理']['文件审批流程'] = U('flow/getlist?type=39');
// 		$left_menu['个人中心']['内部联络单'] = U('flow/getlist?type=46');
// 		$left_menu['个人中心']['名片使用'] = U('flow/getlist?type=48');
// 		$left_menu['个人中心']['合同审阅流程'] = U('flow/getlist?type=52');
// 		$left_menu['个人中心']['会务证明流程'] = U('flow/getlist?type=50');
// 		$left_menu['个人中心']['用印申请'] = U('flow/getlist?type=56');
// 		$left_menu['个人中心']['物品采购调拨申请单'] = U('flow/getlist?type=51');
// 		$left_menu['个人中心']['人事任免、业务提成通知'] = U('flow/getlist?type=49');
// 		$left_menu['个人中心']['办公用品采购'] = U('flow/getlist?type=40');
// 		$left_menu['个人中心']['办公用品领用'] = U('flow/getlist?type=47');
// 		$left_menu['个人中心']['公交卡使用申请'] = U('flow/getlist?type=57');
// 		$left_menu['个人中心']['私车公用申请流程'] = U('attendance/table');
// 		$left_menu['个人中心']['会议室预定']['会议室管理'] = U('attendance/index');
// 		$left_menu['个人中心']['会议室预定']['预约列表'] = U('attendance/index');
// 		$left_menu['个人中心']['会议室预定']['参数配置'] = U('attendance/index');
// 		$left_menu['个人中心']['资产管理']['资产目录'] = U('attendance/index');
// 		$left_menu['个人中心']['会议室预定']['资产目录级别'] = U('attendance/index');
// 		$left_menu['个人中心']['会议室预定']['资产'] = U('attendance/index');
// 		dump($left_menu);
// 		die;
		$this -> display();
	}
	
	public function shuoshuo(){
		$map['bianqian'] = array('neq','');
		$user = M('User')->where($map)->getField('id,name,bianqian');
		foreach($user as $k=>$v){
			$temp = array_filter(explode('|',$v['bianqian']));
			if($temp[0]){
				$user[$k]['bianqian'] = $temp[0];
				$user[$k]['time'] = $temp[1];
				$user[$k]['len'] = mb_strlen($temp[0]);	
			}
		}
		$users = $this -> my_sort($user, 'time');
		if(!is_mobile_request()){
			$this -> assign("bianq", $users);
		}
		elseif(ACTION_NAME == 'shuoshuo') {
			$this -> assign("bianq", array_slice($users,0,20));
			$this->display();
		}
	}
	
	private function my_sort($arrays,$sort_key,$sort_order=SORT_DESC,$sort_type=SORT_REGULAR ){ 
		if(is_array($arrays)){ 
			foreach ($arrays as $array){ 
				if(is_array($array)){ 
					$key_arrays[] = $array[$sort_key]; 
				}else{ 
					return false; 
				} 
			} 
		}else{ 
			return false; 
		}
		array_multisort($key_arrays,$sort_order,$sort_type,$arrays); 
		return $arrays; 
	}
			
	public function set_sort() {
		$val = $_REQUEST["val"];
		$data['home_sort'] = $val;
		$model = D("UserConfig") -> set_config($data);
	}
	
	protected function _daily_list(){
		//日报
		$child_ids = array_merge(array(intval(get_user_id())),get_child_ids_all(get_user_id()));
		$map['user_id'] = array('in',$child_ids);
		$model = D("DailyReport");
		$dailyList = $model -> where($map) -> field("id,user_name,work_date,create_time") -> order("create_time desc") -> limit(6) -> select();
		if(!is_mobile_request()){
			$this->assign('dailyList',$dailyList);
		}
		
		//周报
		$child_ids = array_merge(array(intval(get_user_id())),get_child_ids_all(get_user_id()));
		$map['user_id'] = array('in',$child_ids);
		$model = D("WeeklyReport");
		$weeklyList = $model -> where($map) -> field("id,user_name,work_date,create_time") -> order("create_time desc") -> limit(6) -> select();
		if(!is_mobile_request()){
			$this -> assign('weeklyList',$weeklyList);
		}
		
		//月报
		$child_ids = array_merge(array(intval(get_user_id())),get_child_ids_all(get_user_id()));
		$map['user_id'] = array('in',$child_ids);
		$model = D("MonthlyReport");
		$monthlyList = $model -> where($map) -> field("id,user_name,work_date,create_time") -> order("create_time desc") -> limit(6) -> select();
		if(!is_mobile_request()){
			$this-> assign('monthlyList',$monthlyList);
		}
		
	}

	protected function _mail_list() {
		$user_id = get_user_id();
		$model = D('Mail');

		//获取最新邮件
		$where['user_id'] = $user_id;
		$where['is_del'] = array('eq', '0');
		$where['folder'] = array( array('eq', 1), array('gt', 6), 'or');

		$new_mail_list = $model -> where($where) -> field("id,name,create_time") -> order("create_time desc") -> limit(6) -> select();
		if(!is_mobile_request()){
			$this -> assign('new_mail_list', $new_mail_list);
		}
		//获取未读邮件
		$where['read'] = array('eq', '0');
		$unread_mail_list = $model -> where($where) -> field("id,name,create_time") -> order("create_time desc") -> limit(6) -> select();
		if(!is_mobile_request()){
			$this -> assign('unread_mail_list', $unread_mail_list);
		}
	}

	protected function _flow_list() {
		$user_id = get_user_id();
		$emp_no = get_emp_no();
		$model = D('Flow');
		//带审批的列表
		$FlowLog = M("FlowLog");
		$where['emp_no'] = $emp_no;
		$where['_string'] = "result is null";
		$log_list = $FlowLog -> where($where) -> field('flow_id') -> select();
		$log_list = rotate($log_list);
		if (!empty($log_list)) {
			$map['id'] = array('in', $log_list['flow_id']);
			$todo_flow_list = $model -> where($map) -> field("id,name,create_time") -> limit(6) -> order("create_time desc") -> select();
			$this -> assign("todo_flow_list", $todo_flow_list);
		}
		//已提交
		$map = array();
		$map['user_id'] = $user_id;
		$map['step'] = array('gt', 10);
		$submit_process_list = $model -> where($map) -> field("id,name,create_time") -> limit(6) -> order("create_time desc") -> select();
		$this -> assign("submit_flow_list", $submit_process_list);
	}

	protected function _doc_list() {
		$user_id = get_user_id();
		$model = D('Doc');
		//获取最新邮件

		$where['is_del'] = array('eq', '0');
		$folder_list = D("SystemFolder") -> get_authed_folder(get_user_id(), "DocFolder");
		$where['folder'] = array("in", $folder_list);
		$doc_list = $model -> where($where) -> field("id,name,create_time") -> order("create_time desc") -> limit(6) -> select();
		$this -> assign("doc_list", $doc_list);
	}

	protected function _news_list() {
		$user_id = get_user_id();
		$model = D('News');

		$where['is_del'] = array('eq', '0');
		$folder_list = D("SystemFolder") -> get_authed_folder(get_user_id(), "NewsFolder");
		$where['folder'] = array("in", $folder_list);
		$news_list = $model -> where($where) -> field("id,name,create_time") -> order("create_time desc") -> limit(6) -> select();
		$this -> assign("news_list", $news_list);
	}

	protected function _slide_list() {
		$slide_list = M("Slide") -> where($where) -> order('sort asc') -> select();
		$this -> assign("slide_list", $slide_list);
	}

	protected function _schedule_list() {
		$user_id = get_user_id();
		$model = M('Schedule');
		//获取最新邮件
		$start_date = date("Y-m");

		$where['user_id'] = $user_id;
		$where['start_time'] = array('egt', $start_date);
		$schedule_list = M("Schedule") -> where($where) -> order('start_time,priority desc') -> limit(6) -> select();
		$this -> assign("schedule_list", $schedule_list);

		$model = M("Todo");
		$where = array();
		$where['user_id'] = $user_id;
		$where['status'] = array("in", "1,2");
		$todo_list = M("Todo") -> where($where) -> order('priority desc,sort asc') -> limit(6) -> select();
		$this -> assign("todo_list", $todo_list);
	}

	protected function _notice_list() {
		$model = D('Notice');
		//获取最新通知
		$where['is_del'] = array('eq', '0');
		$folder_list = D("SystemFolder") -> get_authed_folder(get_user_id(), "NoticeFolder");
		$where['folder'] = array("in", $folder_list);
		$where1['is_del'] = array('eq', '0');
		$where1['folder'] = '68';
		$new_notice_list = $model -> where($where) -> field("id,name,content,folder,create_time,add_file,user_name") -> order("create_time desc") -> select();
		$new_notice_list1 = $model -> where($where1) -> field("id,name,content,folder,create_time,add_file,user_name") -> order("create_time desc") -> select();
		$mobile_new_notice_list = array();
		$j = 0;
		foreach ($new_notice_list as $k=>$v){
			if(!empty($v['add_file'])){			
				$files = array_filter(explode(';', $v['add_file']));
				$where['sid'] = array('in', $files);
				$model = M("File");
				$file_list = $model -> where($where) -> find();
				$new_notice_list[$k]['file_list']=$file_list['savename'];
			}
			if($v['folder'] == 71){
				$new_notice_list[$k]['name'] = substr($v['name'],18);	
			}
			if(is_mobile_request() && $v['folder'] == '72' && $j<15){
				$j++;
				$mobile_new_notice_list[] = array('folder'=>$v['folder'],'name'=>$v['name']);
			}
		}
		if(is_mobile_request()){
			$this -> assign("new_notice_list", $mobile_new_notice_list);
		}else{
			$this -> assign("new_notice_list", $new_notice_list);
		}
		
		if(!is_mobile_request()){
			$this -> assign("new_notice_list1", $new_notice_list1);
		}
	}
	
	protected function _notice_list_new() {
		$model = D("Notice");

		//已提交或自己的草稿
		$map['is_del'] = 0;
		$where['is_submit'] = 1;
		$self['is_submit'] = 0;
		$self['user_id'] = get_user_id();
		$where['_complex'] = $self;
		$where['_logic'] = 'OR';
		$map['_complex'] = $where;
		$res = $model -> where($map) -> field("id,name,content,folder,create_time,add_file,user_name,plan,read,views,plan_time") -> order("create_time desc") -> select();
		$pos_id = M('User')->field('dept_id')->find(get_user_id());
		$Parentid = $pos_id['dept_id'];
		$parent_list = array();
		while($Parentid){//获取上级数组
			$parent_list[] = $Parentid;
			$Parentid = getParentDept(null,$Parentid);
		}
		$user_id = get_user_id();
		foreach ($res as $k=>$v){
			$tmp = array_filter(explode(';',$v['read']));
			$res[$k]['can'] = false;
			foreach ($tmp as $kk => $vv){
				if($vv === "-1" && $user_id == 2){//谢总可以看 
					$res[$k]['can'] = true;
					break;
				}
				if(in_array($vv,$parent_list)){
					$res[$k]['can'] = true;
					break;
				}
			}	
		}
		$tmp_news = array();//今日头条/公司新闻
		$stipulate = array();//制度与通知
		$zhidu = array();//制度
		$tongzhi = array();//通知
		$weidu = array();//公告未读
		$survey = array();//企业概况
		$staff_activity = array();//员工活动
		$ckframe = array();//组织框架
		//企业公告->未读
		$arr_read = array_filter(explode(",", get_user_config("readed_notice")));
		foreach ($res as $k => $v){
			if(!$v['can']){
				unset($res[$k]);
			}
		}
		foreach ( $res as $k => $v){
			if($v['folder'] == 95){$tmp_news[] = $v;}//今日头条与公司新闻
			if($v['folder'] == 71 || $v['folder'] == 72){$stipulate[] = $v;}//企业制度与通知
			if($v['folder'] == 71){$zhidu[] = $v;}
			if($v['folder'] == 72){$tongzhi[] = $v;}
			if($v['folder'] == 71 || $v['folder'] == 72){//企业制度与通知里的未读
				if(!in_array($v['id'],$arr_read) &&($v['create_time']>=time()-3600*30*24)){
					$weidu[] = $v;
				}
			}
			if($v['folder'] == 68){$survey[] = $v;}
			if($v['folder'] == 96){$staff_activity[] = $v;}
			if($v['folder'] == 97){$ckframe[] = $v;};
		}
		$this -> assign('ckfram' , $ckframe[0]);
		//今日头条与公司新闻
		$ni = 0;
		$nt = 1;
		$news_notice = array();
		foreach ($tmp_news as $k => $v){
			if($tmp_news[0]['plan'] == '1' && $tmp_news[1]['plan'] == '1'){//前两个都是今日头条
				$news_notice[] = $v;
				$ni = $ni + 4;
			}elseif($v['plan'] == '1' && $nt == 1){
				$news_notice[] = $v;
				$ni = $ni + 4;
				$nt = 0;
			}elseif($v['plan'] == '2') {
				$news_notice[] = $v;
				$ni++;
			}
			if($ni >= 8){break;}
		}
		header("Content-Type:text/html;charset=utf-8");
		//工作计划
		$pn = 0;
		$plan_notice = array();
		foreach ($res as $v){
			if($v['folder'] == 94){
				$plan_notice[$pn] = $v;
				$kd = array_filter(explode(';',$v['read']));
				if(in_array('27',$kd)){
					$plan_notice[$pn]['comp'] = 1;
				}
				$pn++;
			}
			if($pn >= 6){break;}
		}
		
		/*echo '<pre>';
		dump($ckframe[0]);
		echo '</pre>';die;*/
		
		$this -> assign('news_notice',$news_notice);//今日头条与公司新闻
		$this -> assign('plan_notice',$plan_notice);//工作计划
		$this -> assign('stipulate',$stipulate);//公司制度与通知
		$this -> assign('zhidu',$zhidu);
		$this -> assign('tongzhi',$tongzhi);
		$this -> assign('weidu',$weidu);//公司制度与通知未读
		$this -> assign('survey',$survey);//企业概况
		$this -> assign('staff_activity',$staff_activity);
		$this -> assign('notice_list',$res);//全部
	}
	//代办事项
	protected function _plan_work(){
		//任务
		$this -> assign("folder", 'confirm');
		$where_log['type'] = 1;
		$where_log['status'] = 0;
		$where_log['executor'] = get_user_id();
		$task_list = M("TaskLog") -> where($where_log) -> getField('task_id id,task_id');
		 $where['id'] = array('in', $task_list) ;
			
		$model = D('Task');
		if (!empty($model)) {
			$task_extension = $model -> where($where) -> order("create_time desc") -> select();
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
			$uid = get_user_id();
			if($uid == '111' || $uid == '13' || $uid == '260' || $uid == '1'){
				$notice = M('notice') -> where(array('folder'=>'95','is_submit'=>2)) ->order("create_time desc") -> select();
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
			$this -> assign("lists", $flow_list);
			$fn = $flow_list ? count($flow_list) : 0 ;
			$tn = $task_extension ? count($task_extension) : 0 ;
			
			//erp问题反馈的代办
			$problem_feedback_remind = D('ProblemFeedbackRemindView')->where(array('user_id'=>get_user_id()))->order('create_time desc')->select();
			$this -> assign("problem_feedback_remind", $problem_feedback_remind);
			$pn = $problem_feedback_remind ? count($problem_feedback_remind) : 0 ;
			
			$this -> assign('daiban_count',$fn + $tn +$pn);
		}
	}
	//今日便签和未完成的任务
	protected function _memo_task(){
		$model = M('DailyReport');
		if(!empty($model)){
			$where['user_id'] = get_user_id();
			$info = $model -> where($where) -> field('id,undoo,plan') -> order("id desc") -> find();
			if($info){
				$this -> assign('meta',$info);
			}
		}
	   $uplan = M('UndooPlan');
	   $time = strtotime(date('Y-m-d') . '00:00:00');
	   $map['user_id'] = get_user_id();
	   $map['create_time'] = array('egt',$time);
	   $list = $uplan -> where($map) -> select();
	   if($list){
	   		$this -> assign('tmpmeta',$list);
	   }
	}
	//异步添加今日便签和未完成任务
	public function set_today_task(){
		$data['content'] = $_REQUEST['content'];
		$data['flag'] = $_REQUEST['flag'];
		$data['user_id'] = get_user_id();
		$data['create_time'] = time();
		if(M('undooPlan') -> add($data)){
			$this ->ajaxReturn('', "新建成功",1);
		}else{
			$this ->ajaxReturn('', "新建失败",0);
		}
	}
	//首页论坛bbs的信息
	protected function _bbs_info(){
		$where['folder'] = 'ForumFolder';
		$model = M("SystemFolder");
		$list = $model -> where($where) -> order("id desc") -> getField('id,name');
		$this -> assign('folder_list', $list);//bbs模块
		$wm = array();
		foreach ($list as $k => $v){
			$wm[] = $k;
		}
		if(!empty($wm)){
			$map['folder'] = array('IN' , $wm);
		}
		$map['is_del'] = 0;
		$bbs_list = M('forum') -> where($map) -> order("views desc") -> getField('id,folder,name');
		$bbs_count = M('forum') -> where($map) -> count();
		$atten_num = M('ForumAtten') -> getField('atten_num');
		$this -> assign('bbs_list',$bbs_list);
		$this -> assign('bbs_count',$bbs_count);
		$this -> assign('atten_num',$atten_num);
		
		/*echo '<pre>';
		dump($list);
		echo '</pre>';die;*/
	}
	protected function _forum_list() {
		$model = D('Forum');
		$where['is_del'] = array('eq', '0');
		$folder_list = D("SystemFolder") -> get_authed_folder(get_user_id(), "ForumFolder");
		$where['folder'] = array("in", $folder_list);
		$new_forum_list = $model -> where($where) -> field("id,name,create_time") -> order("create_time desc") -> limit(6) -> select();
		$this -> assign("new_forum_list", $new_forum_list);
	}

	protected function _task_list() {
		//所有任务
		$model = M("Task");
		$where = array();
		$task_all_count = $model -> where($where) -> field('id,name,executor,create_time') -> order('create_time desc') ->limit(6) -> select();
		$this -> assign("task_all_count", $task_all_count);

		//等我接受的任务
		
		$where = array();
		$where_log['type'] = 1;
		$where_log['status'] = 0;
		$where_log['executor'] = get_user_id();
		$task_todo_list = M("TaskLog") -> where($where_log) -> getField('task_id id,task_id');
		$where['id'] = array('in', $task_todo_list);

		$task_todo_count = $model -> where($where) -> select();
		if(!is_mobile_request()){
			$this -> assign("task_todo_count", $task_todo_count);
		}
		
		//未完成的任务
		$where_log = array();
		$where_log['status'] = array('eq', 1);
		$where_log['executor'] = get_user_id();
		$task_no_finish_list = M("TaskLog") -> where($where_log) -> getField('task_id id,task_id');
		$where['id'] = array('in', $task_no_finish_list);
		$task_no_finish_count = $model -> where($where) -> select();
		if(!is_mobile_request()){
			$this -> assign("task_no_finish_count", $task_no_finish_count);
		}
		
		//已完成的任务
		$where = array();
		$where['status'] = array('eq', 3);
		$task_finished_count = $model -> where($where) -> count();
		if(!is_mobile_request()){
			$this -> assign("task_finished_count", $task_finished_count);
		}
	
		//我部门任务
 		$where = array();
 		$auth = D("Role") -> get_auth("Task");
 		if ($auth['admin']) {
 			$where_log['type'] = 2;
 			$where_log['executor'] = get_dept_id();
 			$task_list = M("TaskLog") -> where($where_log) -> getField('task_id id,task_id');
 			$where['id'] = array('in', $task_list);
 		} else {
 			$where['_string'] = '1=2';
 		}

 		$task_dept_list = $model -> where($where) -> order("create_time desc") -> limit(6) -> select();
 		if(!is_mobile_request()){
 			$this -> assign("task_dept_list", $task_dept_list);
 		}
	}
	//本月寿星
	protected function _shouxing_list() {
		$model = D('User');
		$shouxing_list = $model ->where('month(birthday)>0 AND is_del = 0') -> order("abs(month(birthday)-month(now())) asc") ->limit(4) -> field('id,name,dept_id,position_id,sex,birthday,pic,email,duty,office_tel,mobile_tel,create_time') ->select();
		if(!is_mobile_request()){
			$this -> assign("shouxing_list", $shouxing_list);
		}
		
	}
	//入职纪念日
	protected function _jinianri_list() {
		$model = D('User');
		$jinianri_list = $model ->where('is_del = 0') -> order("mod(unix_timestamp(now())-create_time,365*24*60*60) asc") ->limit(3) -> field('id,name,dept_id,position_id,sex,birthday,pic,email,duty,office_tel,mobile_tel,create_time') ->select();
		if(!is_mobile_request()){
			$this -> assign("jinianri_list", $jinianri_list);
		}
	}
	//新进员工
	protected function _xinjin_list() {
		$model = D('User');
		$xinjin_list = $model ->where('is_del = 0') -> order("create_time desc") ->limit(7) -> field('id,name,dept_id,position_id,sex,birthday,pic,email,duty,office_tel,mobile_tel,create_time') ->select();
		if(!is_mobile_request()){
			$this -> assign("xinjin_list", $xinjin_list);
		}
		
	}
	public function get_user_info(){
		$user_id = get_user_id();
		$model = D('User');
		$where = array();
		$where = array('id'=>$user_id);
		$info = $model->where($where)-> field('id,name,more_role,dept_id,pos_id,position_id,sex,birthday,pic,email,duty,office_tel,mobile_tel,create_time')->find();
		$info['create_time'] = date('Y年m月d日',$info['create_time']);
		$model = D('Dept');
		$where = array();
		$where = array('id'=>$info['dept_id']);
		$dept_info = $model->where($where)-> field('name')->find();
		$info['dept'] = $dept_info['name'];
		$model = D('Position');
		$where = array();
		$where = array('id'=>$info['position_id']);
		$position_info = $model->where($where)-> field('name')->find();
		$info['position'] = $position_info['name']?$position_info['name']:'';
		$pos_name = M('Dept')-> field('name')->find($info['pos_id']);
		$info['pos_name'] = $pos_name['name']?$pos_name['name']:'';
		
		//找上级用户
		if($info['more_role']){
			$p_user = D('UserView')->field('id,emp_no,more_role,name,dept_id,dept_name,pos_id,duty')->find($info['more_role']);
			$pos_name = D('Dept')->field('name')->find($p_user['pos_id']);
			$p_user['pos_name'] = $pos_name['name'];
			$info['p_user'] = $p_user;
			//找兄弟用户
			$b_user = D('UserView')->field('id,emp_no,more_role,name,dept_id,dept_name,pos_id,duty')->where(array('more_role'=>$p_user['id'],'id'=>array('neq',$user_id)))->select();
			if(!empty($b_user) && is_array($b_user)){
				$info['b_user'] = $b_user;
			}
		}else{//找下级用户
			$c_user = D('UserView')->field('id,emp_no,more_role,name,dept_id,dept_name,pos_id,duty')->where(array('more_role'=>$user_id))->select();
			if(!empty($c_user) && is_array($c_user)){
				$info['c_user'] = $c_user;
			}
		}
		$info['users'] = array();
		if(!empty($p_user)){
			$p_user['role'] = 'p';
			$info['users'][] = $p_user;
		}
		if(!empty($c_user)){
			foreach ($c_user as $k=>$v){
				$c_user[$k]['role'] = 'c';
				$info['users'][] = $c_user[$k];
			}
		}
		if(!empty($b_user)){
			foreach ($b_user as $k=>$v){
				$b_user[$k]['role'] = 'b';
				$info['users'][] = $b_user[$k];
			}
		}
		
		if(!is_mobile_request()){
			$this->assign('info',$info);
		}
	}
	public function ajax_set_bianqian(){
		$user_id = is_mobile_request()?$_GET['id']:$_GET['user_id'];
		if($user_id){
			$data['id'] = $user_id;
		}
		$val = $_GET['val'];
		$data['bianqian'] = $val .'|' .time();
		$res = M('User')->save($data);
		if($res){
			$this->ajaxReturn(get_user_name());
		}else{
			$this->ajaxReturn(null,null);
		}
	}
	//统计问题
	public function question(){ 
		$model = M("user_idea");
		$model -> idea = $_POST['idea'];
		$model -> question = implode('|',$_POST['question']);
		$model -> user_id = get_user_id();
		$model -> finish_time = time();
		$list = $model -> add();
		if ($list !== false) {
			$this -> redirect("Home/index");
		} else {
			$this -> redirect("Home/index");
		}
		
	}
	//日、周、月报样式调查 ->页面
	public function suggest(){
		$sug = M("User_suggest_detail");
		$max = $sug -> max('now');
		$info = $sug -> where('now = '.$max) -> select();
		if(!empty($info)){
			$file = M('file');
			$list = array();
			foreach($info as $k=>$v){
				$list[] = $file -> find($v['fid']);
			}
			$this -> assign('list',$list);
		}
		$this -> display();		
	}
	//保存
	public function save_order(){
		$sug = M('User_suggest'); 
		$ids = $sug -> field('user_id') -> select();
		$sign = false;
		$id = get_user_id();
		foreach ($ids as $k => $v){
			if($v['user_id'] == $id){$sign =  true;break;}
		}
		if(!$sign){
			$data['sustain'] = $_POST['gt1'] .'|' .$_POST['gt2'] . '|' .$_POST['gt3'];
			$data['suggest'] = $_POST['jy1'] .'|' .$_POST['jy2'] . '|' .$_POST['jy3'];
			$data['user_id'] = get_user_id();
			$data['user_name'] = get_user_name();
			$data['create_time'] = time();
			$flag = $sug -> add($data);
			if(false !== $flag){
				$this -> redirect("Home/index");die;
			}
		}
		$this -> redirect("Home/index");die;
			
	}
	//杭州园区
 	public function hangzhou() {
 		$this -> _grader_notice('79','83');
 		$this -> display();
 	}
 	//嘉兴园区
 	public function jiaxing() {
 		$this -> _grader_notice('80','84');
 		$this -> display();
 	}
 	//金华园区
 	public function jinhua() {
 		$this -> _grader_notice('81','85');
 		$this -> display();
 	}
 	//宁波园区
 	public function ningbo() {
 		$this -> _grader_notice('82','86');
 		$this -> display();
 	}
 	protected function _grader_notice($gg,$gk){
		$model = D('Notice');
		//获取最新通知
		$where['is_del'] = array('eq', '0');
		$where['is_submit'] = array('eq', '1');
		$where['folder'] = array("eq", $gg);
		$new_notice_list = $model -> where($where) -> field("id,name,content,folder,create_time,add_file") -> order("create_time desc") -> select();
		$where['folder'] = array('eq',$gk);
		$new_notice_list1 = $model -> where($where) -> field("id,name,content,folder,create_time,add_file") -> order("create_time desc") -> select();
		$this -> assign("new_notice_list", $new_notice_list);
		$this -> assign("new_notice_list1", $new_notice_list1);
	}
	public function getHour(){
		$users = M('User')->field('id,emp_no,name')->select();
		$ext = array();
		foreach ($users as $k=>$user){
			$hour = getAvailableHour(null,$user['id']);
			$users[$k]['hour'] = $hour;
			if($hour<0){
				$ext[] = $users[$k];
			}
		}
		$this->ajaxReturn('1',array('users'=>$users,'ext'=>$ext),1);
	}
	//设置用户自定义信息
	public function sethomebottom(){
		$list = $_REQUEST['list'];
		$data['id'] = get_user_id();
		$data['home_bottom'] = $list;
		if(M('UserConfig') -> save($data)){
			$this -> ajaxReturn('', "设置成功", 1);
		}else{
			$this -> ajaxReturn('', "设置失败", 0);
		}
	}
	
	//默认加载的数据
	public function gethomebottom(){
		$bottom = M('UserConfig') -> where('id='.get_user_id()) -> getField('home_bottom');
		$bottom = str_replace('*','&',$bottom);
		if($bottom == ''){
			$bottom = "<li><a class='bottom_a' href='/index.php?m=daily_report&a=index'><img src='/Public/img/new_home/bottom_rb.png'/></a></li><li><a class='bottom_a' href='/index.php?m=weekly_report&a=index'><img src='/Public/img/new_home/bottom_zb.png'/></a></li><li><a class='bottom_a' href='/index.php?m=monthly_report&a=index'><img src='/Public/img/new_home/bottom_yb.png'/></a></li><li><a class='bottom_a' href='/index.php?m=notice&a=folder&fid=95'><img src='/Public/img/new_home/bottom_tt.png'/></a></li><li><a class='bottom_a' href='/index.php?m=notice&a=read&id=104'><img src='/Public/img/new_home/bottom_jg.png'/></a></li>";
		}
		if(!empty($bottom)){
			$this -> ajaxReturn($bottom, "加载成功", 1);
		}else{
			$this -> ajaxReturn('', "加载失败", 0);
		}
	}
	
	//默认加载的数据
	public function gethomebottomcheck(){
		$bottom = M('UserConfig') -> where('id='.get_user_id()) -> getField('home_bottom');
		$bottom = str_replace('*','&',$bottom);
		if($bottom == ''){
			$bottom = "<li><a class='bottom_a' href='/index.php?m=daily_report&a=index'><img src='/Public/img/new_home/bottom_rb.png'/></a></li><li><a class='bottom_a' href='/index.php?m=weekly_report&a=index'><img src='/Public/img/new_home/bottom_zb.png'/></a></li><li><a class='bottom_a' href='/index.php?m=monthly_report&a=index'><img src='/Public/img/new_home/bottom_yb.png'/></a></li><li><a class='bottom_a' href='/index.php?m=notice&a=folder&fid=95'><img src='/Public/img/new_home/bottom_tt.png'/></a></li><li><a class='bottom_a' href='/index.php?m=notice&a=read&id=104'><img src='/Public/img/new_home/bottom_jg.png'/></a></li>";
		}
		preg_match_all("/href=[\'|\"](\S+)[\'|\"]/i", $bottom, $match);
		$match1 = str_replace('href=','',$match[0]);
		$match1 = str_replace("'",'',$match1);
		if(!empty($match1)){
			$this -> ajaxReturn($match1, "加载成功", 1);
		}else{
			$this -> ajaxReturn('', "加载失败", 0);
		}
	}
	
}
?>
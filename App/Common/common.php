<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

function get_file_path($sid) {
	if (is_array($sid)) {
		$where['sid'] = array("in", array_filter($sid));
	} else {
		$where['sid'] = array('in', array_filter(explode(';', $sid)));
	}
	$list = M("File") -> where($where) -> getField('savename');
	return $list;
}

function get_task_log($task_id) {
	$list = M("TaskLog") -> where("task_id=$task_id") -> select();
	return $list;
}
function userExp($i,$bq){
	if(empty($bq)){return nulll;}
	$temp =  explode('|',$bq);
	return $temp[$i];
}
function is_weixin() {
	if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
		return true;
	}
	return false;
}
function dump2($var){
	echo "<pre>";
	print_r($var);
	echo "</pre>";die;
}
function get_new_count() {

	$emp_no = get_emp_no();

	//获取未读邮件
	$data = array();

	$user_id = get_user_id();
	$where['user_id'] = $user_id;
	$where['is_del'] = array('eq', '0');
	$where['folder'] = array('eq', 1);
	$where['read'] = array('eq', '0');
	$new_mail_inbox = M("Mail") -> where($where) -> count();
	$data['bc-mail']['bc-mail-inbox'] = $new_mail_inbox;

	//获取未读邮件
	$where['user_id'] = $user_id;
	$where['is_del'] = array('eq', '0');
	$where['folder'] = array('gt', 6);
	$where['read'] = array('eq', '0');
	$new_mail_myfolder = M("Mail") -> where($where) -> count();
	$data['bc-mail']['bc-mail-myfolder'] = $new_mail_myfolder;

	//获取待裁决
	$where = array();
	$FlowLog = M("FlowLog");

	$where['emp_no'] = $emp_no;
	$where['_string'] = "result is null";
	$log_list = $FlowLog -> where($where) -> field('flow_id') -> select();

	$log_list = rotate($log_list);
	$new_confirm_count = 0;
	if (!empty($log_list)) {
		$map['id'] = array('in', $log_list['flow_id']);
		$new_confirm_count = M("Flow") -> where($map) -> count();
	}
	$data['bc-flow']['bc-flow-confirm'] = $new_confirm_count;

	//获取收到的流程
	$where = array();
	$where['emp_no'] = $emp_no;
	$where['step'] = 100;
	$where['is_read'] = 1;

	$log_list = M("FlowLog") -> where($where) -> field('flow_id') -> select();
	$log_list = rotate($log_list);
	$new_receive_count = 0;
	if (!empty($log_list)) {
		$map['id'] = array('in', $log_list['flow_id']);
		$new_receive_count = M("Flow") -> where($map) -> count();
	}
	$data['bc-flow']['bc-flow-receive'] = $new_receive_count;

	//获取最新通知
	$where = array();
	$where['is_del'] = array('eq', '0');
	$folder_list = D("SystemFolder") -> get_authed_folder(get_user_id(), "NoticeFolder");
	$where['folder'] = array('in', $folder_list);
	$where['create_time'] = array("egt", time() - 3600 * 24 * 30);
	$readed = array_filter(explode(",", get_user_config("readed_notice")));
	if(!empty($readed)){
		$where['id'] = array("not in", $readed);
	}
	$where['is_submit'] = 1;//只获取提交的
	//新增 是否有查看权限
	$pos_id = M('User')->field('pos_id')->find(get_user_id());
	$Parentid = $pos_id['pos_id'];
	$parent_list = array();
	while($Parentid){//获取上级数组
		$parent_list[] = $Parentid;
		$Parentid = getParentDept(null,$Parentid);
	}
	$nList = M('Notice') -> where($where) -> select();
	foreach ($nList as $k=>$v){
		$tmp = explode(';',$v['read']);
		foreach ($tmp as $kk => $vv){
			if(in_array($vv,$parent_list)){
				$arr[]= $v['id'];
				break;
			}
		}	
	}
	$where['id'] = array("in", $arr);
	$new_notice_count = M('Notice') -> where($where) -> count();
	$data['bc-notice']['bc-notice-new'] = $new_notice_count;

	//获取待办事项
	$where = array();
	$where['user_id'] = $user_id;
	$where['status'] = array("in", "1,2");
	$new_todo_count = M("Todo") -> where($where) -> count();
	$data['bc-personal']['bc-personal-todo'] = $new_todo_count;

	//获取日程事项
	$where = array();
	$where['user_id'] = $user_id;
	$where['is_del'] = 0;
// 	$where['start_time'] = array("elt", date("Y-m-d H:i:s"));
	$where['end_time'] = array("egt", date("Y-m-d H:i:s"));
	$new_schedule_count = M("Schedule") -> where($where) -> count();
	$data['bc-personal']['bc-personal-schedule'] = $new_schedule_count;

	//获取最新消息
	$model = M("Message");
	$where = array();
	$where['owner_id'] = $user_id;
	$where['receiver_id'] = $user_id;
	$where['is_read'] = array('eq', '0');
	$new_message_count = M("Message") -> where($where) -> count();
	$data['bc-message']['bc-message-new'] = $new_message_count;
	
	//获取最意见箱
	$model = M("complaint");
	$id = get_user_id();
	$where1['visible'] = array(array('eq',$id),array('eq','0'),'or');
	$compla = $model -> where($where1) -> select();
	$num = 0;
	foreach ($compla as $k => $v){
		if(!empty($v['is_read'])){
			$tmp = explode(',',$v['is_read']);
			if(in_array($id,$tmp)){
				$num++;
			}
		}
	}
	$new_message_count = count($compla) - $num;
	$data['bc-complaint']['bc-complaint-new'] = $new_message_count;

	//等我接受的任务
	$where = array();
	$where_log['type'] = 1;
	$where_log['status'] = 0;
	$where_log['executor'] = get_user_id();
	$task_list = M("TaskLog") -> where($where_log) -> getField('task_id id,task_id');

	$where['id'] = array('in', $task_list);

	$task_todo_count = M("Task") -> where($where) -> count();
	$data['bc-task']['task_todo_count'] = $task_todo_count;

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

	$task_dept_count = M("Task") -> where($where) -> count();
	$data['bc-task']['task_dept_count'] = $task_dept_count;

	return $data;
}

function is_mobile($mobile) {
	return preg_match("/^(?:13\d|14\d|15\d|18[0123456789])-?\d{5}(\d{3}|\*{3})$/", $mobile);
}

function is_email($email) {
	return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
}

/**
 * 发送HTTP请求方法，目前只支持CURL发送请求
 * @param  string $url    请求URL
 * @param  array  $params 请求参数
 * @param  string $method 请求方法GET/POST
 * @return array  $data   响应数据
 */
function http($url, $params, $method = 'GET', $header = array(), $multi = false) {
	$opts = array(CURLOPT_TIMEOUT => 30, CURLOPT_RETURNTRANSFER => 1, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false, CURLOPT_HTTPHEADER => $header);

	/* 根据请求类型设置特定参数 */
	switch(strtoupper($method)) {
		case 'GET' :
			$opts[CURLOPT_URL] = $url . '?' . str_replace("&amp;", "&", http_build_query($params));
			break;
		case 'POST' :
			//判断是否传输文件
			//$params = $multi ? $params : http_build_query($params);
			$opts[CURLOPT_URL] = $url;
			$opts[CURLOPT_POST] = 1;
			$opts[CURLOPT_POSTFIELDS] = $params;
			break;
		default :
			throw new Exception('不支持的请求方式！');
	}

	/* 初始化并执行curl请求 */
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$error = curl_error($ch);
	curl_close($ch);
	if ($error)
		throw new Exception('请求发生错误：' . $error);
	return $data;
}

/**
 * 不转义中文字符和\/的 json 编码方法
 * @param array $arr 待编码数组
 * @return string
 */
function jsencode($arr) {
	$str = str_replace("\\/", "/", json_encode($arr));
	$search = "#\\\u([0-9a-f]+)#ie";

	if (strpos(strtoupper(PHP_OS), 'WIN') === false) {
		$replace = "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))";
		//LINUX
	} else {
		$replace = "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))";
		//WINDOWS
	}

	return preg_replace($search, $replace, $str);
}

// 数据保存到文件
function data2file($filename, $arr = '') {
	if (is_array($arr)) {
		$con = var_export($arr, true);
		$con = "<?php\nreturn $con;\n?>";
	} else {
		$con = $arr;
		$con = "<?php\n $con;\n?>";
	}
	write_file($filename, $con);
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key  加密密钥
 * @param int $expire  过期时间 单位 秒
 * @return string
 * @author winky
 */

function encrypt($data, $key = '', $expire = 0) {
	$key = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
	$data = base64_encode($data);
	$x = 0;
	$len = strlen($data);
	$l = strlen($key);
	$char = '';

	for ($i = 0; $i < $len; $i++) {
		if ($x == $l)
			$x = 0;
		$char .= substr($key, $x, 1);
		$x++;
	}

	$str = sprintf('%010d', $expire ? $expire + time() : 0);

	for ($i = 0; $i < $len; $i++) {
		$str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
	}
	return str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($str));
}

/**
 * 系统解密方法
 * @param  string $data 要解密的字符串 （必须是encrypt方法加密的字符串）
 * @param  string $key  加密密钥
 * @return string
 * @author winky
 */
function decrypt($data, $key = '') {
	$key = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
	$data = str_replace(array('-', '_'), array('+', '/'), $data);
	$mod4 = strlen($data) % 4;
	if ($mod4) {
		$data .= substr('====', $mod4);
	}
	$data = base64_decode($data);
	$expire = substr($data, 0, 10);
	$data = substr($data, 10);

	if ($expire > 0 && $expire < time()) {
		return '';
	}
	$x = 0;
	$len = strlen($data);
	$l = strlen($key);
	$char = $str = '';

	for ($i = 0; $i < $len; $i++) {
		if ($x == $l)
			$x = 0;
		$char .= substr($key, $x, 1);
		$x++;
	}

	for ($i = 0; $i < $len; $i++) {
		if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
			$str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
		} else {
			$str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
		}
	}
	return base64_decode($str);
}

function upload_filter($val) {
	$allow_type = array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'dwg', 'rar', 'zip', '7z', 'pdf', 'txt', 'rtf', 'jpg', 'jpeg', 'png', 'tip', 'psd');
	if (in_array($val, $allow_type)) {
		return true;
	} else {
		return false;
	}
}

function get_save_path() {
	$app_path = __APP__;
	$save_path = C('SAVE_PATH');
	$app_path = str_replace("/index.php?s=", "", $app_path);
	$app_path = str_replace("/index.php", "", $app_path);
	return C('SAVE_PATH');
}

function get_save_url() {
	$app_path = __APP__;
	$save_path = C('SAVE_PATH');
	$app_path = str_replace("/index.php?s=", "", $app_path);
	$app_path = str_replace("/index.php", "", $app_path);
	return $app_path . "/" . $save_path;
}

function _encode($arr) {
	$na = array();
	foreach ($arr as $k => $value) {
		$na[_urlencode($k)] = _urlencode($value);
	}
	return addcslashes(urldecode(json_encode($na)), "\r\n");
}

function _urlencode($elem) {
	if (is_array($elem)) {
		foreach ($elem as $k => $v) {
			$na[_urlencode($k)] = _urlencode($v);
		}
		return $na;
	}
	return urlencode($elem);
}

function get_img_info($img) {
	$img_info = getimagesize($img);
	if ($img_info !== false) {
		$img_type = strtolower(substr(image_type_to_extension($img_info[2]), 1));
		$info = array("width" => $img_info[0], "height" => $img_info[1], "type" => $img_type, "mime" => $img_info['mime'], );
		return $info;
	} else {
		return false;
	}
}

function get_return_url($level = null) {
	if (empty($level)) {
		$return_url = cookie('return_url');
	} else {
		$return_url = cookie('return_url_' . $level);
	}
	return $return_url;
}

function get_system_config($code) {
	$model = M("SystemConfig");
	$where['code'] = array('eq', $code);
	$count = $model -> where($where) -> count();
	if ($count > 1) {
		return $model -> where($where) -> getfield("val,name");
	} else {
		return $model -> where($where) -> getfield("val");
	}
}

function get_user_config($field) {
	$model = M("UserConfig");
	$user_id = get_user_id();
	$where['id'] = array('eq', $user_id);
	$result = $model -> where($where) -> getfield($field);
	if (empty($result)) {
		return get_system_config(strtoupper($field));
	} else {
		return $result;
	}
}

function get_user_info($id, $field) {
	$model = D("UserView");
	$where['id'] = array('eq', $id);
	$result = $model -> where($where) -> getfield($field);
	return $result;
}

function get_user_id() {
	if(is_mobile_request()){
		$id = $_REQUEST['id'];
		$token = $_REQUEST['token'];
		if(!empty($id) && !empty($token)){
			$map = array();
			$map["id"] = array('eq', intval($id));
			$model = M("User");
			$auth_info = $model -> where($map) -> find();
			if(md5($auth_info['password'].md5($auth_info['last_mobile_login_time'])) == $token && time()-$auth_info['last_mobile_login_time']<C('MOBILE_TOKEN_LIFETIME')){
				$user_id = $id;
			}
		}
	}else{
		$user_id = session(C('USER_AUTH_KEY'));
	}
	return isset($user_id) ? $user_id : 0;
}

function get_child_ids($parent_id){//获取直接下级的id
	$model_user = D("User");
	$pos_id = $model_user->where(array('id'=>$parent_id))->field('pos_id')->find();
	if(!is_numeric($pos_id['pos_id'])){//已经是最下级了
		return array();
	}
	if(!empty($pos_id)){
		$model_dept = D("Dept");
		$dept = $model_dept->where(array('pid'=>$pos_id['pos_id']))->field('id')->select();
		
		if(!empty($dept) && is_array($dept)){
			foreach ($dept as $dep){
				$dept_new[] = $dep['id'];
			}
			$child_user_idd = $model_user->where(array('pos_id'=>array('in',$dept_new)))->field('id')->select();
			foreach ($child_user_idd as $id){
				$child_user_id[$id['id']] = intval($id['id']);
			}
		}
		else{
			$child_user_idd = $model_user->where(array('pos_id'=>'_'.$pos_id['pos_id']))->field('id')->select();
			foreach ($child_user_idd as $id){
				$child_user_id[$id['id']] = intval($id['id']);
			}
		}
		return $child_user_id;
	}
	return array();
}
function get_child_ids_2($parent_id){//获取下级的下级的id
	if($parent_id){
		$child_user_id = get_child_ids($parent_id);
		if(!empty($child_user_id)&&is_array($child_user_id)){
			foreach ($child_user_id as $k=>$child_user_idd){
				$child_user_id[$k] = get_child_ids($child_user_idd);
			}
		}
		foreach ($child_user_id as $id){
			foreach ($id as $idd){
				$new[$idd] = $idd;
			}
		}
		return $new;
	}else{
		return array();
	}
}
function get_child_ids_all($parent_id){//获取所有（包括间接）下级的id
	$pos_id = M('User')->field('pos_id')->find($parent_id);
	$child_depts =  get_child_dept_all($pos_id['pos_id'],false);
	$child_users = M('User')->field('id')->where(array('pos_id'=>array('in',$child_depts)))->select();
	$child_users = rotate($child_users);
	$child_users = $child_users['id'];
	return $child_users?$child_users:array();
// 	if($parent_id){
// 		$child_user_id = get_child_ids($parent_id);
// 		if(!empty($child_user_id)&&is_array($child_user_id)){
// 			foreach ($child_user_id as $k=>$child_user_idd){
// 				$child_user_id[$k] = get_child_ids_all($child_user_idd);
// 			}
// 		}else{
// 			return array();
// 		}
// 		return $child_user_id;
// 	}
// 	else{
// 		return array();
// 	}
	
}

function array_to_one_dimension($array){//将多维数组降为一维数组
	static $new_array = array();
	foreach ($array as $k => $v){
		$new_array[$k] = $k;
		if(is_array($v)){
			array_to_one_dimension($v);
		}
		else {
			$new_array[$k] = $v;
		}
	}
	return $new_array;
	
}

function get_root_dept(){
	return M('Dept')->where(array('pid'=>0))->find();
}
function get_child_dept($pid){
	$child = D('DeptView')->where(array('pid'=>$pid))->select();
	if(empty($child)){
		$child = D('User')->where(array('pos_id'=>'_'.$pid))->field('id,emp_no,name,pos_id')->select();
	}
	return $child;
}
function get_child_depts($pid){
	$a = array();
	$child = D('DeptView')->where(array('pid'=>$pid))->select();
	foreach ($child as $v){
		$a[] = $v['id'];
	}
	return $a;
}
/*
 * $flag=true表示包括自己
 * $flag=false表示不包括自己
 */
function get_child_dept_all($pid,$flag=true){
	$child = get_child_depts($pid);
	$a = $child;
	if($child){
		foreach ($child as $k=>$v){
			$b = get_child_dept_all($v,$flag);
			if(!empty($b)){
				$a = array_unique(array_merge($a,$b));
			}
		}
	}
	if($flag){
		return array_merge($a,array($pid));
	}else{
		return $a;
	}
	
}

function get_emp_no() {
	$emp_no = session("emp_no");
	return isset($emp_no) ? $emp_no : 0;
}

function del_folder($dir) {
	//打开文件目录
	$dh = opendir($dir);
	//循环读取文件
	while ($file = readdir($dh)) {
		if ($file != '.' && $file != '..') {
			$fullpath = $dir . '/' . $file;

			//判断是否为目录
			if (!is_dir($fullpath)) {
				//echo $fullpath."已被删除<br>";
				//如果不是,删除该文件
				if (!unlink($fullpath)) {
				}
			} else {
				//如果是目录,递归本身删除下级目录
				del_folder($fullpath);
			}
		}
	}
	//关闭目录
	closedir($dh);
	//删除目录

	if (rmdir($dir)) {
		return true;
	} else {
		return false;
	}
}

function get_user_name() {
	if(is_mobile_request()){
		$id = $_REQUEST['id'];
		$token = $_REQUEST['token'];
		if(!empty($id) && !empty($token)){
			$map = array();
			$map["id"] = array('eq', intval($id));
			$model = M("User");
			$auth_info = $model -> where($map) -> find();
			if(md5($auth_info['password'].md5($auth_info['last_mobile_login_time'])) == $token && time()-$auth_info['last_mobile_login_time']<C('MOBILE_TOKEN_LIFETIME')){
				$user_name = $auth_info['name'];
			}
		}
	}else{
		$user_name = session('user_name');
	}
	return isset($user_name) ? $user_name : 0;
}

function get_dept_id() {
	return session('dept_id');
}

function get_dept_name() {
	$result = M("Dept") -> find(session("dept_id"));
	return $result['name'];
}
function get_dept_name_by_id($val) {
	$result = M("Dept") -> find($val);
	return $result['name'];
}
function get_duty_by_userid($id){
	$result = M("User") -> find($val);
	return $result['duty'];
}
function get_module($str) {
	$arr_str = explode("/", $str);
	return $arr_str[0];
}

function get_bc_class($str) {
	$arr_str = explode(" ", $str);
	foreach ($arr_str as $val) {
		if (strpos($val, "bc-") !== false) {
			return $val;
		}
	}
}

function toDate($time, $format = 'Y-m-d H:i:s') {
	if (empty($time)) {
		return '';
	}
	$format = str_replace('#', ':', $format);
	return date($format, $time);
}

function date_to_int($date) {
	$date = explode("-", $date);
	$time = explode(":", "00:00");
	$time = mktime($time[0], $time[1], 0, $date[1], $date[2], $date[0]);
	return $time;
}

function fix_time($time) {
	return substr($time, 0, 5);
}

function filter_search_field($v1) {
	if ($v1 == "keyword")
		return true;
	$prefix = substr($v1, 0, 3);
	$arr_key = array("be_", "en_", "eq_", "li_", "lt_", "gt_", "bt_","dep","pos");
	if (in_array($prefix, $arr_key)) {
		return true;
	} else {
		return false;
	}
}

function filter_flow_field($val) {
	if (strpos($val, "flow_field_") !== false) {
		return true;
	} else {
		return false;
	}
}

function get_cell_location($col, $row, $col_offset = 0, $row_offset = 0) {
	if (!is_numeric($col)) {
		$col = ord($col) - 65;
	}
	$location = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
	$col = $col + $col_offset;
	$row = $row + $row_offset;
	return $location[$col] . $row;
}

function get_model_fields($model) {
	$arr_field = array();
	if (isset($model -> viewFields)) {
		foreach ($model->viewFields as $key => $val) {
			unset($val['_on']);
			unset($val['_type']);
			if (!empty($val[0]) && ($val[0] == "*")) {
				$model = M($key);
				$fields = $model -> getDbFields();
				$arr_field = array_merge($arr_field, array_values($fields));
			} else {
				$arr_field = array_merge($arr_field, array_values($val));
			}
		}
	} else {
		$arr_field = $model -> getDbFields();
	}
	return $arr_field;
}

function show_step_type($step) {
	if ($step >= 20 && $step < 30) {
		return "审批";
	}
	if ($step >= 30) {
		return "协商";
	}
}

function show_result($result) {
	if ($result == 1) {
		return "同意";
	}
	if ($result == 0) {
		return "否决";
	}
	if ($result == 2) {
		return "退回";
	}
}

function show_step($step) {
	if ($step == 40) {
		return "通过";
	}
	if ($step > 30) {
		return "协商中";
	}
	if ($step == 30) {
		return "待协商";
	}
	if ($step > 20) {
		return "审批中";
	}
	if ($step == 20) {
		return "待审批";
	}
	if ($step == 10) {
		return "临时保管";
	}
	if ($step == 0) {
		return "否决";
	}
}

function IP($ip = '', $file = 'UTFWry.dat') {
	$_ip = array();
	if (isset($_ip[$ip])) {
		return $_ip[$ip];
	} else {
		import("ORG.Net.IpLocation");
		$iplocation = new IpLocation($file);
		$location = $iplocation -> getlocation($ip);
		$_ip[$ip] = $location['country'] . $location['area'];
	}
	return $_ip[$ip];
}

function sort_by($array, $keyname = null, $sortby = 'asc') {
	$myarray = $inarray = array();
	# First store the keyvalues in a seperate array
	foreach ($array as $i => $befree) {
		$myarray[$i] = $array[$i][$keyname];
	}
	# Sort the new array by
	switch ($sortby) {
		case 'asc' :
			# Sort an array and maintain index association...
			asort($myarray);
			break;
		case 'desc' :
		case 'arsort' :
			# Sort an array in reverse order and maintain index association
			arsort($myarray);
			break;
		case 'natcasesor' :
			# Sort an array using a case insensitive "natural order" algorithm
			natcasesort($myarray);
			break;
	}
	# Rebuild the old array
	foreach ($myarray as $key => $befree) {
		$inarray[] = $array[$key];
	}
	return $inarray;
}

function fix_array_key($list, $key) {
	$arr = null;
	foreach ($list as $val) {
		$arr[$val[$key]] = $val;
	}
	return $arr;
}

function fill_option($list, $data) {
	$html = "";
	foreach ($list as $key => $val) {
		if (is_array($val)) {
			$id = $val['id'];
			$name = $val['name'];
			if ($id == $data) {
				$selected = "selected";
			} else {
				$selected = "";
			}
			$html = $html . "<option value='{$id}' $selected>{$name}</option>";
		} else {
			if ($key == $data) {
				$selected = "selected";
			} else {
				$selected = "";
			}
			$html = $html . "<option value='{$key}' $selected>{$val}</option>";
		}
	}
	echo $html;
}

/**
 +----------------------------------------------------------
 * 产生随机字串，可用来自动生成密码
 * 默认长度6位 字母和数字混合 支持中文
 +----------------------------------------------------------
 * @param string $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 其它 混合
 * @param string $addChars 额外字符
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function rand_string($len = 6, $type = '', $addChars = '') {
	$str = '';
	switch ($type) {
		case 0 :
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
			break;
		case 1 :
			$chars = str_repeat('0123456789', 3);
			break;
		case 2 :
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
			break;
		case 3 :
			$chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
			break;
		default :
			// 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
			$chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
			break;
	}
	if ($len > 10) {//位数过长重复字符串一定次数
		$chars = $type == 1 ? str_repeat($chars, $len) : str_repeat($chars, 5);
	}
	if ($type != 4) {
		$chars = str_shuffle($chars);
		$str = substr($chars, 0, $len);
	} else {
		// 中文随机字
		for ($i = 0; $i < $len; $i++) {
			$str .= msubstr($chars, floor(mt_rand(0, mb_strlen($chars, 'utf-8') - 1)), 1);
		}
	}
	return $str;
}

function list_to_tree($list, $root = 0, $pk = 'id', $pid = 'pid', $child = '_child') {
	// 创建Tree
	$tree = array();
	if (is_array($list)) {
		// 创建基于主键的数组引用
		$refer = array();
		//id 作为键 => 存储每条部门信息为数据
		foreach ($list as $key => $data) {
			$refer[$data[$pk]] = &$list[$key];
		}
		foreach ($list as $key => $data) {
			// 判断是否存在parent
			$parentId = 0;
			if (isset($data[$pid])) {
				$parentId = $data[$pid];
			}
			// 上一级是不是我想要的
			if ((string)$root == $parentId) {
				$tree[] = &$list[$key];
			} else {
				if (isset($refer[$parentId])) {
					$parent = &$refer[$parentId];
					$parent[$child][] = &$list[$key];
				}
			}
		}
	}
	return $tree;
}

function add_leaf($tree,$leaf_name_postfix = '下',$leaf_id_prefix = '_'){
	foreach ($tree as $k=>$v){
		if(isset($v['_child'])){
			$tree[$k]['_child'] = add_leaf($tree[$k]['_child']);
		}
		else{
			$tree[$k]['_child'][] = array('pid' => $tree[$k]['id'],'name'=>$tree[$k]['name'].$leaf_name_postfix,'id'=>$leaf_id_prefix.$tree[$k]['id']);
		}
	}
	return $tree;
}

function tree_to_list($tree, $level = 0, $pk = 'id', $pid = 'pid', $child = '_child') {
	$list = array();
	if (is_array($tree)) {
		foreach ($tree as $val) {
			$val['level'] = $level;
			if (isset($val['_child'])) {
				$child = $val['_child'];
				if (is_array($child)) {
					unset($val['_child']);
					$list[] = $val;
					$list = array_merge($list, tree_to_list($child, $level + 1));
				}
			} else {
				$list[] = $val;
			}
		}
		return $list;
	}
}

function left_menu($tree, $level = 0) {
	$level++;
	$html = "";
	if (is_array($tree)) {
		$html = "<ul class=\"tree_menu\">\r\n";
		foreach ($tree as $val) {
			if (isset($val["name"])) {
				$title = $val["name"];
				if (!empty($val["url"])) {
					$url = U($val['url']);
				} else {
					$url = "#";
				}
				$id = $val["id"];
				if (empty($val["id"])) {
					$id = $val["name"];
				}
				if (isset($val['_child'])) {
					$html = $html . "<li>\r\n<a node=\"$id\" href=\"" . "$url\"><i class=\"fa fa-angle-right level$level\"></i><span>$title</span></a>\r\n";
					$html = $html . left_menu($val['_child'], $level);
					$html = $html . "</li>\r\n";
				} else {
					$html = $html . "<li>\r\n<a  node=\"$id\" href=\"" . "$url\"><i class=\"fa fa-angle-right level$level\"></i><span>$title</span></a>\r\n</li>\r\n";
				}
			}
		}
		$html = $html . "</ul>\r\n";
	}
	return $html;
}

function select_tree_menu($tree) {
	$html = "";
	if (is_array($tree)) {
		$list = tree_to_list($tree);
		foreach ($list as $val) {
			$html = $html . "<option value='{$val['id']}'>" . str_pad("", $val['level'] * 3, "│") . "├─" . "{$val['name']}</option>";
		}
	}
	return $html;
}
function select_tree_menu_mul($tree,$level=0) {
	$level++;
	$html = "";
	if (is_array($tree)) {
		if($level == 3){
			$html = "<ul class=\"ul1\">\r\n";
		}else{
			$html = "<ul>\r\n";
		}
		foreach ($tree as $val) {
			if (isset($val["name"])) {
				$title = $val["name"];
				$id = $val["id"];
				if (empty($val["id"])) {
					$id = $val["name"];
				}
				if (isset($val['_child'])) {
					$html = $html . "<li>\r\n";
					$html = $html . "<img src=\"./Public/img/zk.png\"/>\r\n";
					$html = $html . "<input type=\"checkbox\" name=\"dept[]\" name2=\"$title\" id=\"dept_$id\" value=\"$id\">\r\n";
					$html = $html . "<label for=\"dept_$id\">$title</label>\r\n";
					$html = $html . select_tree_menu_mul($val['_child'],$level);
					$html = $html . "</li>\r\n";
				} else {
					$html = $html . "<li>\r\n";
					$html = $html . "<img src=\"./Public/img/hl.png\"/>\r\n";
					$html = $html . "<input type=\"checkbox\" name=\"dept[]\" name2=\"".$title."\" id=\"dept_".$id."\" value=\"".$id."\">\r\n";
					$html = $html . "<label for=\"dept_$id\">$title</label>\r\n";
					$html = $html . "</li>\r\n";
				}
			}
		}
		$html = $html . "</ul>\r\n";
	}
	return $html;
}
/*
 * 物品选择
 */

function popup_menu($tree, $level = 0,$deep=100,$other_nodes=array()) {
	$level++;
	$deep--;
	$html = "";
	if (is_array($tree) && $deep>0) {
		$html = "<ul class=\"ul$level\">\r\n";
		foreach ($tree as $val) {
			if (isset($val["name"])) {
				$title = $val["name"];
				$id = $val["id"];
				if (empty($val["id"])) {
					$id = $val["name"];
				}
				if (!empty($val["is_del"])) {
					$del_class = "is_del";
				} else {
					$del_class = "";
				}
				$ext = '';
				if(!empty($other_nodes) && is_array($other_nodes)){
					foreach ($other_nodes as $k=>$other_node){
						if(!empty($val[$other_node])){
							$ext .= ' '.$other_node.'='.$val[$other_node];
						}else{
							$ext .= ' '.$other_node.'=""';
						}
					}
				}
				
				if (isset($val['_child'])) {
					$html = $html . "<li class=\"li$level\">\r\n<a node=\"$id\" $ext ><span class=\"span$level\">$title</span></a>\r\n";
					$html = $html . popup_menu($val['_child'], $level,$deep,$other_nodes);
					$html = $html . "</li>\r\n";
				} else {
					$html = $html . "<li class=\"li$level\">\r\n<a node=\"$id\" $ext ><span class=\"span$level\">$title</span></a>\r\n</li>\r\n";
				}
			}
		}
		$html = $html . "</ul>\r\n";
	}
	return $html;
}

function popup_tree_menu($tree, $level = 0,$deep=100,$other_nodes=array()) {
	$level++;
	$deep--;
	$html = "";
	if (is_array($tree) && $deep>0) {
		$html = "<ul class=\"tree_menu\">\r\n";
		foreach ($tree as $val) {
			if (isset($val["name"])) {
				$title = $val["name"];
				$id = $val["id"];
				if (empty($val["id"])) {
					$id = $val["name"];
				}
				if (!empty($val["is_del"])) {
					$del_class = "is_del";
				} else {
					$del_class = "";
				}
				$ext = '';
				if(!empty($other_nodes) && is_array($other_nodes)){
					foreach ($other_nodes as $k=>$other_node){
						if(!empty($val[$other_node])){
							$ext .= ' '.$other_node.'='.$val[$other_node];
						}else{
							$ext .= ' '.$other_node.'=""';
						}
					}
				}
				
				if (isset($val['_child'])) {
					$html = $html . "<li>\r\n<a class=\"$del_class\" node=\"$id\" $ext ><i class=\"fa fa-angle-right level$level\"></i><span>$title</span></a>\r\n";
					$html = $html . popup_tree_menu($val['_child'], $level,$deep,$other_nodes);
					$html = $html . "</li>\r\n";
				} else {
					$html = $html . "<li>\r\n<a class=\"$del_class\" node=\"$id\" $ext ><i class=\"fa fa-angle-right level$level\"></i><span>$title</span></a>\r\n</li>\r\n";
				}
			}
		}
		$html = $html . "</ul>\r\n";
	}
	return $html;
}
//菜单维护列表
function new_tree_menu($tree, $level = 0,$deep=100) {
	$level++;
	$deep--;
	$html = "";
	if (is_array($tree) && $deep>0) {
		$html = "<ul class=\"content_ul\">\r\n";
		foreach ($tree as $val) {
			if (isset($val["menu_name"])) {
				$title = $val["menu_name"];
				$id = $val["id"];
				$pid = $val["pid"];
				$menu_no = $val["menu_no"];
				$sort = $val['sort'];
				if (empty($val["id"])) {
					$id = $val["menu_name"];
				}
				$status = $val['menu_status'] ? "启用" : "禁用" ;
				$activate = $val['menu_status'] ? "禁用" : "启用" ;
				if (isset($val['_child'])) {
					$html = $html . "<li>\r\n<span class=\"li_sp3\"><input name=\"box\" type=\"checkbox\"/><input name=\"sid\" type=\"hidden\" value=\"{$id}\"/><input name=\"pid\" type=\"hidden\" value=\"{$pid}\"/><input name=\"menu_no\" type=\"hidden\" value=\"{$menu_no}\"/></span>\r\n";
					$html = $html . "<span class=\"li_sp0_2\"><img src=\"__PUBLIC__/img/new_versions/add.png\"/><span>{$title}</span></span>\r\n";
					$html = $html . "<span class=\"li_sp1\">{$val['menu_addr']}</span>\r\n";
					$html = $html . "<span class=\"li_sp2\">$status</span>\r\n";
					$html = $html . "<span class=\"li_sp2\"><a class=\"content_a a1_0\">添加</a></span>\r\n";
					$html = $html . "<span class=\"li_sp2\"><a class=\"content_a a1_1\" msg=\"$sort\">修改</a></span>\r\n";
					$html = $html . "<span class=\"li_sp2\"><a class=\"content_a a1_2\">{$activate}</a></span>\r\n";
					$html = $html . "<span class=\"li_sp2\"><a class=\"content_a a1_3\" id=\"{$id}\">关联角色</a></span>\r\n";
					$html = $html . "<span class=\"li_sp2\"><a class=\"content_a a1_5\" id=\"{$id}\" menu_name=\"{$title}\">关联角色复制</a></span>\r\n";
					$html = $html . "<span class=\"li_sp$level isChild\"><a class=\"content_a a1_4\" id=\"{$id}\">关联数据控制策略</a></span>\r\n";
					$html = $html . new_tree_menu($val['_child'], $level,$deep);
					$html = $html . "</li>\r\n";
				} else {
					$html = $html . "<li>\r\n<span class=\"li_sp3\"><input name=\"box\" type=\"checkbox\"/><input name=\"sid\" type=\"hidden\" value=\"{$id}\"/><input name=\"pid\" type=\"hidden\" value=\"{$pid}\"/><input name=\"menu_no\" type=\"hidden\" value=\"{$menu_no}\"/></span>\r\n";
					$html = $html . "<span class=\"li_sp0_2\"><img src=\"__PUBLIC__/img/new_versions/add.png\"/><span>{$title}</span></span>\r\n";
					$html = $html . "<span class=\"li_sp1\">{$val['menu_addr']}</span>\r\n";
					$html = $html . "<span class=\"li_sp2\">$status</span>\r\n";
					$html = $html . "<span class=\"li_sp2\"><a class=\"content_a a1_0\">添加</a></span>\r\n";
					$html = $html . "<span class=\"li_sp2\"><a class=\"content_a a1_1\" msg=\"$sort\">修改</a></span>\r\n";
					$html = $html . "<span class=\"li_sp2\"><a class=\"content_a a1_2\">{$activate}</a></span>\r\n";
					$html = $html . "<span class=\"li_sp2\"><a class=\"content_a a1_3\" id=\"{$id}\">关联角色</a></span>\r\n";
					$html = $html . "<span class=\"li_sp2\"><a class=\"content_a a1_5\" id=\"{$id}\" menu_name=\"{$title}\">关联角色复制</a></span>\r\n";
					$html = $html . "<span class=\"li_sp$level isParent\"><a class=\"content_a a1_4\" id=\"{$id}\">关联数据控制策略</a></span>\r\n";
					$html = $html . "</li>\r\n";
				}
			}
		}
		$html = $html . "</ul>\r\n";
	}
	return $html;
}
//分配菜单页面
function assi_tree_menu($tree, $level = 0,$deep=100,$other_nodes="",$info) {
	$level++;
	$deep--;
	$html = "";
	if (is_array($tree) && $deep>0) {
		$html = "<ul class=\"zz_ul$level\" $other_nodes>\r\n";
		foreach ($tree as $val) {
			if (isset($val["menu_name"])) {
				$title = $val["menu_name"];
				$id = $val["id"];
				$chd = in_array($id, $info['menu_id']) ? "checked" : "";   
				if (isset($val['_child'])) {
					$other_nodes = "style=\"display:none;\"";
					$html = $html . "<li class=\"zz_li\">\r\n<img src=\"Public/img/new_versions/add.png\"/><input $chd name=\"box\" type=\"checkbox\"/><input type=\"hidden\" name=\"sid\" value=\"$id\"/><label>$title</label>\r\n";
					$html = $html . assi_tree_menu($val['_child'], $level,$deep,$other_nodes,$info);
					$html = $html . "</li>\r\n";
				} else {
					$other_nodes="";
					$html = $html . "<li class=\"zz_li\">\r\n<img src=\"Public/img/new_versions/add.png\"/><input $chd name=\"box\" type=\"checkbox\"/><input type=\"hidden\" name=\"sid\" value=\"$id\"/><label>$title</label>\r\n</li>\r\n";
				}
			}
		}
		$html = $html . "</ul>\r\n";
	}
	return $html;
}
//部门档案管理列表
function popup_tree_menu_dept($tree, $level = 0,$deep=100,$default_ids=array()) {
	$level++;
	$deep--;
	if($level == 1){
		$width = 283;
		$class = "content_ul";
	}else{
		$width = 283-($level-1)*18;
		$class = "content_ul1";
	}
	
	$html = "";
	if (is_array($tree) && $deep>0) {
		$html = "<ul class=\"$class\">\r\n";
		foreach ($tree as $val) {
			if (isset($val["name"])) {
				$title =  $level == '1'?'':$val["name"];
				$id = $val["id"];
				$pid = $val["pid"];
				$code = $level == '1'?$val["name"]:$val["dept_no"];
				if (empty($val["id"])) {
					$id = $val["name"];
				}
				$status = ($val['is_use'] == '1' ? "启用" : "禁用");
				$activate = ($val['is_use'] == '1' ? "禁用" : "启用");
				$set_use = $val['is_use'] == '1' ? "0" : "1";
				$style_bold = $val['is_use'] == '1' ? '' : "style=\"font-weight:bold;\"";
				$edit = '编辑';
				if($level == 1){
					$edit_url = U('edit_company?id='.$id);
				}else{
					$edit_url = U('edit_dept?id='.$id);
				}
				$style_color = '';
				if(!empty($default_ids) && is_array($default_ids)){
					if(in_array($id, $default_ids)){
						$style_color = "style=\"color:magenta;\"";
					}
				}
				$html = $html . "<li>\r\n<span class=\"li_sp0_1\" style=\"width:".$width."px;\"><img src=\"__PUBLIC__/img/ajj.png\"/>";
				if($level == '1'){
					$html = $html . "<span>$code</span>";
				}else{
					$html = $html . "<a href=".U('view?id='.$id)." $style_color>$code</a>";
				}
				
				$html = $html . "\r\n</span>\r\n";
				$html = $html . "<span class=\"li_sp1\">$title</span>\r\n";
				$html = $html . "<span class=\"li_sp2\" id=\"a0_$id\" $style_bold>$status</span>\r\n";
				$html = $html . "<span class=\"li_sp3\">";
				$html = $html . "<a class=\"content_a\" id=\"a1_$id\" onclick=\"set_use('$id','$set_use')\">$activate</a>";
// 				$html = $html . "<a class=\"content_a\" id=\"a1_0\">$activate</a>";
				$html = $html . "<a class=\"content_a\" id=\"a2_$id\" href=\"$edit_url\">$edit</a>";
				$html = $html . "<a class=\"content_a\" id=\"a3_$id\" onclick=\"add_child_dept('$id')\">新增子部门</a>";
				$html = $html . "</span>\r\n";
				if (isset($val['_child'])) {
					$html = $html . popup_tree_menu_dept($val['_child'], $level,$deep,$default_ids);
				}
				$html = $html . "</li>\r\n";
			}
		}
		$html = $html . "</ul>\r\n";
	}
	return $html;
}
function popup_menu_organization($tree, $level = 0,$deep=100) {
	$level++;
	$deep--;
	$html = "";
	if (is_array($tree) && $deep>0) {
		$i = $level>2?'2':'';
		$html = "<ul class=\"zz_ul$i\">\r\n";
// 		if($level == '2'){
// 			$html = $html . "<li class=\"zz_li1\" onclick=show_list('".$tree[0]['id']."','1')>"."公司领导"."</li>\r\n";
// 		}
		foreach ($tree as $val) {
			if (isset($val["name"])) {
				$title = $val["name"];
				$id = $val["id"];
				if (empty($val["id"])) {
					$id = $val["name"];
				}

				if (isset($val['_child'])) {
					$html = $html . "<li class=\"zz_li\" >\r\n<img src=\"__PUBLIC__/img/xl.png\"/><span onclick=show_list('$id')>$title</span>\r\n";
					$html = $html . popup_menu_organization($val['_child'], $level,$deep);
					$html = $html . "</li>\r\n";
				} else {
					$html = $html . "<li class=\"zz_li1\" onclick=show_list('$id')>$title</li>\r\n";
				}
			}
		}
		$html = $html . "</ul>\r\n";
	}
	return $html;
}
function popup_menu_organization_checkbox($tree, $level = 0,$deep=100,$position_id) {
	$level++;
	$deep--;
	$html = "";
	if (is_array($tree) && $deep>0) {
		$i = $level>1?'1':'';
		$html = "<ul class=\"zz_ul$i\">\r\n";
		foreach ($tree as $val) {
			if (isset($val["name"])) {
				$title = $val["name"];
				$id = $val["id"];
				if (empty($val["id"])) {
					$id = $val["name"];
				}
				if(!empty($position_id)){
					$res = M('RDeptPosition')->where(array('position_id'=>$position_id,'dept_id'=>$id))->find();
					$is_checked = $res?' checked="checked"':'';
				}else{
					$is_checked = '';
				}
				
				if (isset($val['_child'])) {
					$html = $html . "<li class=\"zz_li\" >\r\n<img src=\"".__ROOT__."/Public/img/xl.png\"/><input type=\"checkbox\" id=\"dept_$id\" name=\"dept[]\" value=\"$id\"'.$is_checked.'/><label for=\"dept_$id\">$title</label>\r\n";
					$html = $html . popup_menu_organization_checkbox($val['_child'], $level,$deep,$position_id);
					$html = $html . "</li>\r\n";
				} else {
					$html = $html . "<li class=\"zz_li1\"><input type=\"checkbox\" id=\"dept_$id\" name=\"dept[]\" value=\"$id\"'.$is_checked.'/><label for=\"dept_$id\">$title</label></li>\r\n";
				}
			}
		}
		$html = $html . "</ul>\r\n";
	}
	return $html;
}
function popup_menu_dept_position_checkbox($tree, $level = 0,$deep=100,$child_depts,$child_positions) {
	$level++;
	$deep--;
	$html = "";
	if (is_array($tree) && $deep>0) {
		$i = $level>1?'1':'';
		$style = $level>1?'style="display:none;"':'';
		$html = "<ul class=\"zz_ul$i\" $style>\r\n";
		foreach ($tree as $val) {
			if (isset($val["name"])) {
				$title = $val["name"];
				$id = $val["id"];
				if (empty($val["id"])) {
					$id = $val["name"];
				}
				if(substr($id, 0,1) == 'p'){
					//岗位
					if(in_array(substr($id, 2), $child_positions)){
						$is_checked = ' checked="checked"';
					}else{
						$is_checked = ' ';
					}
				}else{
					//部门
					if(in_array($id, $child_depts)){
						$is_checked = ' checked="checked"';
					}else{
						$is_checked = ' ';
					}
				}

				if (isset($val['_child'])) {
					$html = $html . "<li class=\"zz_li\" >\r\n<img src=\"".__ROOT__."/Public/img/xl.png\"/><input type=\"checkbox\" id=\"dept_$id\" name=\"dept[]\" value=\"$id\"'.$is_checked.'/><label for=\"dept_$id\">$title</label>\r\n";
					$html = $html . popup_menu_dept_position_checkbox($val['_child'], $level,$deep,$child_depts,$child_positions);
					$html = $html . "</li>\r\n";
				} else {
					$html = $html . "<li class=\"zz_li1\"><input type=\"checkbox\" id=\"dept_$id\" name=\"dept[]\" value=\"$id\"'.$is_checked.'/><label for=\"dept_$id\">$title</label></li>\r\n";
				}
			}
		}
		$html = $html . "</ul>\r\n";
	}
	return $html;
}
function popup_dept_search_checkbox($tree, $level = 0,$deep=100) {
	$level++;
	$deep--;
	$html = "";
	if (is_array($tree) && $deep>0) {
		$html = "<ul>\r\n";
		foreach ($tree as $val) {
			if (isset($val["name"])) {
				$title = $val["name"];
				$id = $val["id"];
				if (empty($val["id"])) {
					$id = $val["name"];
				}
				
				if (isset($val['_child'])) {
					$html = $html . "<li>\r\n<img src=\"".__ROOT__."/Public/img/hl.png\"/><input type=\"checkbox\" id=\"dept_id_$id\" name=\"dept_id[]\" value=\"$id\"/><label for=\"dept_id_$id\">$title</label>\r\n";
					$html = $html . popup_dept_search_checkbox($val['_child'], $level,$deep);
					$html = $html . "</li>\r\n";
				} else {
					$html = $html . "<li>\r\n<img src=\"".__ROOT__."/Public/img/hl.png\"/><input type=\"checkbox\" id=\"dept_id_$id\" name=\"dept_id[]\" value=\"$id\"/><label for=\"dept_id_$id\">$title</label>\r\n</li>\r\n";
				}
			}
		}
		$html = $html . "</ul>\r\n";
	}
	return $html;
}
function popup_menu_option($tree, $level = 0,$default='') {
	$level++;
	$html = "";
	if (is_array($tree)) {
		foreach ($tree as $val) {
			if (isset($val["name"]) || isset($val['menu_name'])) {
				$title = isset($val["name"]) ? $val['name'] : $val['menu_name'];
				$id = $val["id"];
				if (empty($val["id"])) {
					$id = $val["name"];
				}
				if($id == $default){
					$ext = "selected=\"selected\"";
				}else{
					$ext = "";
				}
				if (isset($val['_child'])) {
					$html = $html . "<option ".$ext." value=\"$id\">".substr('----------',0,$level-1).$title."</option>\r\n";
					$html = $html . popup_menu_option($val['_child'], $level,$default);
				} else {
					$html = $html . "<option ".$ext." value=\"$id\">".substr('----------',0,$level-1).$title."</option>\r\n";
				}
			}
		}
		$html = $html . "\r\n";
	}
	return $html;
}

function sub_tree_menu($tree, $level = 0) {
	$level++;
	$html = "";
	if (is_array($tree)) {
		$html = "<ul class=\"tree_menu\">\r\n";
		foreach ($tree as $val) {
			if (isset($val["name"])) {
				$title = $val["name"];
				$id = $val["id"];
				if (empty($val["id"])) {
					$id = $val["name"];
				}
				if (isset($val['_child'])) {
					$html = $html . "<li>\r\n<a node=\"$id\"><i class=\"fa fa-angle-right level$level\"></i><span>$title</span></a>\r\n";
					$html = $html . sub_tree_menu($val['_child'], $level);
					$html = $html . "</li>\r\n";
				} else {
					$html = $html . "<li>\r\n<a  node=\"$id\" ><i class=\"fa fa-angle-right level$level\"></i><span>$title</span></a>\r\n</li>\r\n";
				}
			}
		}
		$html = $html . "</ul>\r\n";
	}
	return $html;
}

function dropdown_menu($tree, $level = 0) {
	$level++;
	$html = "";
	if (is_array($tree)) {
		foreach ($tree as $val) {
			if (isset($val["name"])) {
				$title = $val["name"];
				$id = $val["id"];
				if (empty($val["id"])) {
					$id = $val["name"];
				}
				if (isset($val['_child'])) {
					$html = $html . "<li id=\"$id\" class=\"level$level\"><a>$title</a>\r\n";
					$html = $html . dropdown_menu($val['_child'], $level);
					$html = $html . "</li>\r\n";
				} else {
					$html = $html . "<li  id=\"$id\"  class=\"level$level\">\r\n<a>$title</a>\r\n</li>\r\n";
				}
			}
		}
	}
	return $html;
}
//页面菜单栏的显示
function left_new_tree_menu($tree, $level = 0,$deep=100) {
	$level++;
	$deep--;
	$html = "";
	if (is_array($tree) && $deep>0) {
		$html = "<ul class=\"menu_ul2\">\r\n";
		foreach ($tree as $val) {
			if (isset($val["menu_name"])) {
				$title = $val["menu_name"];
				$id = $val["id"];
				$pid = $val["pid"];
				$url = U($val["menu_addr"]);
				$sort = $val['sort'];
				if (isset($val['_child'])) {
					$html = $html . "<li>\r\n<a class=\"menu_li2_a\"><span class=\"cd_span2\"></span><div class=\"cd_div\" id=\"cd_div2\">{$title}</div><img class=\"cd_ts\" src=\"__PUBLIC__/img/new_home/jian2.png\"/></a>\r\n";
					$html = $html . left_new_tree_menu($val['_child'], $level,$deep);
					$html = $html . "</li>\r\n";
				} else {
					$html = $html . "<li class='menu_li2'><a href=\"$url\"><span class='cd_span2'></span><div class='cd_div'>{$title}</div></a></li>\r\n";
				}
			}
		}
		$html = $html . "</ul>\r\n";
	}
	return $html;
}
function f_encode($str) {
	$str = base64_encode($str);
	$str = rand_string(10) . $str . rand_string(10);
	$str = str_replace("+", "-", $str);
	$str = str_replace("/", "_", $str);
	$str = str_replace("==", "*", $str);
	return $str;
}

function f_decode($str) {
	$str = str_replace("-", "+", $str);
	$str = str_replace("_", "/", $str);
	$str = str_replace("*", "==", $str);
	$str = substr($str, 10, strlen($str) - 20);
	$str = base64_decode($str);
	return $str;
}

function u_str_pad($cnt, $str) {
	$tmp = '';
	for ($i = 1; $i <= $cnt; $i++) {
		$tmp = $tmp . $str;
	}
	return $tmp;
}

function show_contact($str, $mode = "show") {
	$tmp = '';

	if (!empty($str)) {
		$contacts = array_filter(explode(';', $str));
		if (count($contacts) > 1) {
			foreach ($contacts as $contact) {
				$arr = explode('|', $contact);
				$name = htmlspecialchars(rtrim($arr[0]));
				$email = htmlspecialchars(rtrim($arr[1]));
				if ($mode == "edit") {
					$tmp = $tmp . "<span data=\"$email\"><nobr><b  title=\"$email\">$name</b><a class=\"del\" title=\"删除\"><i class=\"fa fa-times\"></i></a></nobr></span>";
				} else {
					$tmp = $tmp . "<a email=\"$email\" title=\"$email\" >$name;</a>&nbsp;";
				}
			}
		} else {
			$arr = explode('|', $contacts[0]);
			$name = htmlspecialchars(rtrim($arr[0]));
			$email = htmlspecialchars(rtrim($arr[1]));
			$tmp = "";
			if ($mode == "edit") {
				$tmp = $tmp . "<span data=\"$email\"><nobr><b  title=\"$email\">$name</b><a class=\"del\" title=\"删除\"><i class=\"fa fa-times\"></i></a></nobr></span>";
			} else {
				$tmp = $tmp . "<a email=\"$email\" title=\"$email\" >$name</a>";
			}
		}
	}
	return $tmp;
}

function show_recent($str) {
	$contacts = explode(';', $str);
	if (count($contacts) > 2) {
		foreach ($contacts as $contact) {
			if (strlen($contact) > 6) {
				$arr = explode('|', $contact);
				$name = rtrim($arr[0]);
				$email = rtrim($arr[1]);
				$tmp = $tmp . "<li><span title=\"$email\">$name</span></li>";
			}
		}
	} else {
		$arr = explode('|', $contacts[0]);
		$name = rtrim($arr[0]);
		$email = rtrim($arr[1]);
		$tmp = "";
		$tmp = $tmp . "<li><span title=\"$email\">$name</span></li>";
	}
	return $tmp;
}

function not_dept($val) {
	if (strrchr($val, "dept@group")) {
		return false;
	} else {
		return true;
	}
}

// 自动转换字符集 支持数组转换
function auto_charset($fContents, $from, $to) {
	$from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
	$to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
	if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {
		//如果编码相同或者非字符串标量则不转换
		return $fContents;
	}
	if (is_string($fContents)) {
		if (function_exists('mb_convert_encoding')) {
			return mb_convert_encoding($fContents, $to, $from);
		} elseif (function_exists('iconv')) {
			return iconv($from, $to, $fContents);
		} else {
			return $fContents;
		}
	} elseif (is_array($fContents)) {
		foreach ($fContents as $key => $val) {
			$_key = auto_charset($key, $from, $to);
			$fContents[$_key] = auto_charset($val, $from, $to);
			if ($key != $_key)
				unset($fContents[$key]);
		}
		return $fContents;
	} else {
		return $fContents;
	}
}

function getExt($filename) {
	$pathinfo = pathinfo($filename);
	return $pathinfo['extension'];
}

function del_html($str) {
	$str = trim($str);
	$str = preg_replace("/<[^>]*>/i", "", $str);
	$str = ereg_replace("\t", "", $str);
	$str = ereg_replace("\r\n", "", $str);
	$str = ereg_replace("\r", "", $str);
	$str = ereg_replace("\n", "", $str);
	$str = ereg_replace("&nbsp;", "", $str);
	$str = ereg_replace(" ", "", $str);
	$str = ereg_replace("{br}", "<br/>", $str);
	$str = ereg_replace("{}", "&nbsp;", $str);
	return $str;
}

function getfilecounts($ff) {
	$dir = './' . $ff;
	$handle = opendir($dir);
	$i = 0;
	while (false !== $file = (readdir($handle))) {
		if ($file !== '.' && $file != '..') {
			$i++;
		}
	}
	closedir($handle);
	return $i;
}

function show_refer($emp_list) {
	$arr_emp_no = array_filter(explode('|', $emp_list));
	if (count($arr_emp_no) > 1) {
		$model = D("UserView");
		foreach ($arr_emp_no as $emp_no) {
			$where['emp_no'] = array('eq', substr($emp_no, 4));
			$emp = $model -> where($where) -> find();
			$emp_no = $emp['emp_no'];
			$user_name = $emp['name'];
			$position_name = $emp['position_name'];
			$str .= "<span data=\"$emp_no\" id=\"$emp_no\"><nobr><b title=\"$user_name/$position_name\">$user_name/$position_name</b></nobr><b>;&nbsp;</b></span>";
		}
		return $str;
	} else {
		return "";
	}
}

function show_file($add_file) {
	$files = array_filter(explode(';', $add_file));
	foreach ($files as $file) {
		if (strlen($file) > 1) {
			$model = M("File");
			$where['sid'] = array('eq', $file);
			$File = $model -> where($where) -> field("id,name,size,extension") -> find();
			echo '<div class="attach_file" style="background-image:url(__PUBLIC__/ico/ico_' . strtolower($File['extension']) . '.jpg); background-repeat:no-repeat;"><a target="_blank" href="__URL__/down/attach_id/' . f_encode($File['id']) . '">' . $File['name'] . ' (' . reunit($File['size']) . ')' . '</a>';
			echo '</div>';
		}
	}
}
function show_file2($add_file) {
	$files = array_filter(explode(';', $add_file));
	foreach ($files as $file) {
		if (strlen($file) > 1) {
			$model = M("File");
			$where['sid'] = array('eq', $file);
			$File = $model -> where($where) -> field("id,name,size,extension") -> find();
			echo '<div class="attach_file" style="background-image:url(__PUBLIC__/ico/ico_' . strtolower($File['extension']) . '.jpg); background-repeat:no-repeat;"><a target="_blank" href="'.U('down?attach_id=') . f_encode($File['id']) . '">' . $File['name'] . ' (' . reunit($File['size']) . ')' . '</a>';
			echo '</div>';
		}
	}
}
function show_comment($comm){
	if(!empty($comm) && is_array($comm)){
		$post = M('Forum_post');
		$id = get_user_id();
		foreach ($comm as $k => $v){
			$info = $post -> where('is_del = 0') -> find($v);
			echo '<hr><div class="post_content">' . $info['content'] . '<span class="pull-right">评论人：'. $info['user_name'] ;
			 if($id == $info['user_id']){
			 	echo '　<a onclick="del_post();return false;" class="btn btn-link comm_del" mss='.$info['id'].'>删除</a>';
			 }
			echo '</span></div>';
		}		
	}
}
function mobile_show_file($add_file,$action='message'){
	$files = array_filter(explode(';', $add_file));
	$a = '';
	foreach ($files as $file) {
		if (strlen($file) > 1) {
			$model = M("File");
			$where['sid'] = array('eq', $file);
			$File = $model -> where($where) -> field("id,name,size,extension,savename,user_id") -> find();
			$__PUBLIC__ = __PUBLIC__;
			$__URL__ = __URL__;
			if($action=='message'){
				if($File['user_id']==get_user_id()){
					$class='file_me';
				}else{
					$class='file_others';
				}
			}elseif($action=='task'){
				$class = 'task';
			}elseif($action=='flow'){
				$class = 'fjflow';
			}
			
			$a.= '<a target="_blank" class="'.$class.'" href="'.'http://oa.xyb2c.com/Data/Files/'.$File['savename'].'">'.$File['name'].'</a>';
// 			return '<div class="attach_file" style="background-image:url('.$__PUBLIC__.'/ico/ico_' . strtolower($File['extension']) . '.jpg); background-repeat:no-repeat;"><a target="_blank" href="'.$__URL__.'/down/attach_id/' . f_encode($File['id']) . '">' . $File['name'] . ' (' . reunit($File['size']) . ')' . '</a>'.'</div>';
		}
	}
	return $a;
}

function reunit($size) {
	$unit = " B";
	if ($size > 1024) {
		$size = $size / 1024;
		$unit = " KB";
	}
	if ($size > 1024) {
		$size = $size / 1024;
		$unit = " MB";
	}
	if ($size > 1024) {
		$size = $size / 1024;
		$unit = " GB";
	}
	return round($size, 2) . $unit;
}

function rotate($a) {
	$b = array();
	if (is_array($a)) {
		foreach ($a as $val) {
			foreach ($val as $k => $v) {
				$b[$k][] = $v;
			}
		}
	}
	return $b;
}
function getNavPid($id,$pid){
    $nav = M('dept')->find($id);
    if(($nav['pid'] != $pid[0]) || ($nav['pid'] != $pid[1]) || ($nav['pid'] != $pid[2]) || ($nav['pid'] != $pid[3]) || ($nav['pid'] != $pid[4])){ return getNavPid($nav['pid'],$pid); }
    return $nav['id'];
}

function utf_strlen($string) {
	return count(mb_str_split($string));
}

function utf_str_sub($string, $cnt) {
	$charlist = mb_str_split($string);
	$new = array_chunk($charlist, $cnt);
	return implode($new[0]);
}

function get_letter($string) {
	$charlist = mb_str_split($string);
	return implode(array_map("getfirstchar", $charlist));
}

function mb_str_split($string) {
	// Split at all position not after the start: ^
	// and not before the end: $
	return preg_split('/(?<!^)(?!$)/u', $string);
}

function getfirstchar($s0) {
	$fchar = ord(substr($s0, 0, 1));
	if (($fchar >= ord("a") and $fchar <= ord("z")) or ($fchar >= ord("A") and $fchar <= ord("Z")))
		return strtoupper(chr($fchar));
	$s = iconv("UTF-8", "GBK", $s0);
	$asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
	if ($asc >= -20319 and $asc <= -20284)
		return "A";
	if ($asc >= -20283 and $asc <= -19776)
		return "B";
	if ($asc >= -19775 and $asc <= -19219)
		return "C";
	if ($asc >= -19218 and $asc <= -18711)
		return "D";
	if ($asc >= -18710 and $asc <= -18527)
		return "E";
	if ($asc >= -18526 and $asc <= -18240)
		return "F";
	if ($asc >= -18239 and $asc <= -17923)
		return "G";
	if ($asc >= -17922 and $asc <= -17418)
		return "H";
	if ($asc >= -17417 and $asc <= -16475)
		return "J";
	if ($asc >= -16474 and $asc <= -16213)
		return "K";
	if ($asc >= -16212 and $asc <= -15641)
		return "L";
	if ($asc >= -15640 and $asc <= -15166)
		return "M";
	if ($asc >= -15165 and $asc <= -14923)
		return "N";
	if ($asc >= -14922 and $asc <= -14915)
		return "O";
	if ($asc >= -14914 and $asc <= -14631)
		return "P";
	if ($asc >= -14630 and $asc <= -14150)
		return "Q";
	if ($asc >= -14149 and $asc <= -14091)
		return "R";
	if ($asc >= -14090 and $asc <= -13319)
		return "S";
	if ($asc >= -13318 and $asc <= -12839)
		return "T";
	if ($asc >= -12838 and $asc <= -12557)
		return "W";
	if ($asc >= -12556 and $asc <= -11848)
		return "X";
	if ($asc >= -11847 and $asc <= -11056)
		return "Y";
	if ($asc >= -11055 and $asc <= -10247)
		return "Z";
	return null;
}
function create_emp_no($name){
	import("@.ORG.Util.Pinyin");
	$py = new Pinyin();
	$charlist = mb_str_split($name);
	$s = '';
	if(!empty($charlist) && is_array($charlist)){
		foreach ($charlist as $k=>$v){
			if($k == '0'){
				$s .= $py->getAllPY($v);
			}else{
				$s .= $py->getFirstPY($v);
			}
		}
		$regexp = '"^'.$s.'[0-9]*$"';
		$last = M('User')->where(array('_string'=>'emp_no REGEXP '.$regexp))->order('emp_no desc')->getField('emp_no');
		if(false != $last){
			$a = explode($s,$last);
			$new = $s.(intval($a[1])+1);
		}else{
			$new = $s;
		}
		return $new;
	}
	return null;
}
function get_folder_name($id) {

	if ($id == 1) {
		return "收件箱";
	}
	if ($id == 2) {
		return "已发送";
	}
	if ($id == 3) {
		return "草稿箱";
	}
	if ($id == 4) {
		return "已删除";
	}
	if ($id == 5) {
		return "垃圾邮件";
	}

	$model = D("UserFolder");
	$result = $model -> where("id=$id") -> getField("name");
	if ($result) {
		return $result;
	} else {
		return null;
	}
}

function mail_org_string($vo) {
	$count = 0;
	if (!empty($vo['sender_check']) && $count < 1) {
		$count++;
		if ($vo["sender_option"] == 1) {
			$str1 = "包含";
		} else {
			$str1 = "不包含";
		}
		$str2 = $vo['sender_key'];

		$str3 = get_folder_name($vo["to"]);

		$html = "发件人" . $str1 . " " . $str2 . " 则 : 移动到 " . $str3;
	};

	if (!empty($vo['domain_check']) && $count < 1) {
		$count++;
		if ($vo["domain_option"] == 1) {
			$str1 = "包含";
		} else {
			$str1 = "不包含";
		}
		$str2 = $vo['domain_key'];

		$str3 = get_folder_name($vo["to"]);

		$html = "发件域" . $str1 . " " . $str2 . " 则 : 移动到 " . $str3;
	};

	if (!empty($vo['recever_check']) && $count < 1) {
		$count++;
		if ($vo["recever_option"] == 1) {
			$str1 = "包含";
		} else {
			$str1 = "不包含";
		}
		$str2 = $vo['recever_key'];

		$str3 = get_folder_name($vo["to"]);

		$html = "收件人" . $str1 . " " . $str2 . " 则 : 移动到 " . $str3;
	};

	if (!empty($vo['title_check']) && $count < 1) {
		$count++;
		if ($vo["title_option"] == 1) {
			$str1 = "包含";
		} else {
			$str1 = "不包含";
		}
		$str2 = $vo['title_key'];

		$str3 = get_folder_name($vo["to"]);

		$html = "标题中" . $str1 . " " . $str2 . " 则 : 移动到 " . $str3;
	};
	if ($count > 1) {
		$html .= " 等";
	}
	return $html;
}

function status($status) {
	if ($status == 0) {
		return "启用";
	}
	if ($status == 1) {
		return "禁用";
	}
}

function status_wek($status) {
	if ($status == 1) {
		return "进行中";
	}
	if ($status == 2) {
		return "已完成";
	}
}

function task_preson($status) {
	$arr = explode(';',$status);
	foreach ($arr as $v){
		$t = explode('|',$v);
		$list .= $t[0];
	}
	return $list;
}

function getDeptName($status) {
	return M('dept')->getFieldById($status,'name');
}

function need_help($status) {
	if ($status == 0) {
		return "不需要协助";
	}
	if ($status == 1) {
		return "需要协助";
	}
}

function crm_status($status) {
	if ($status == 0) {
		return "未审核";
	}
	if ($status == 1) {
		return "通过";
	}
	if ($status == 2) {
		return "拒绝";
	}
}

function todo_status($status) {
	if ($status == 1) {
		return "尚未进行";
	}
	if ($status == 2) {
		return "正在进行";
	}
	if ($status == 3) {
		return "完成";
	}
}

function mb_unserialize($serial_str) {
	$out = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $serial_str);
	return unserialize($out);
}

function get_sid() {
	return md5(bin2hex(time()) . rand_string());
}

function get_position_name($id) {
	$data = D('UserView') -> find($id);
	if(empty($data['position_name'])){//可能是建立视图时表链接时某些字段不写导致无法生成视图
		$user = D('User')->find($id);
		$position = D('position')->find($user['position_id']);
		if(empty($position['name'])){
			return '不明';
		}else{
			return $position['name'];
		}
		
	}else{
		return $data['position_name'];
	}
}

function get_emp_pic($id) {
	$data = M("User") -> where("id=$id") -> getField("pic");
	if (empty($data)) {
		$data = get_save_path() . "emp_pic/no_avatar.jpg";
	}
	return $data;
}

function task_status($status) {
	if ($status == 0) {
		return "等待接受";
	}
	if ($status == 1) {
		return "已接受";
	}
	if ($status == 2) {
		return "进行中";
	}
	if ($status == 3) {
		return "已完成";
	}
	if ($status == 4) {
		return "已转交";
	}
	if ($status == 5) {
		return "不接受";
	}
}

function task_log_status($status) {
	if ($status == 0) {
		return "等待接受";
	}
	if ($status == 1) {
		return "已接受";
	}
	if ($status == 2) {
		return "进行中";
	}
	if ($status == 3) {
		return "已完成";
	}
	if ($status == 4) {
		return "已转交";
	}
	if ($status == 5) {
		return "不接受";
	}
}

function finish_rate($rate) {
	if ($rate == 0) {
		return "任务未开始执行";
	}
	if ($rate > 0 and $rate < 100) {
		return "任务已完成$rate%";
	}
	if ($rate == 100) {
		return "任务已完成";
	}
}

function is_submit($val) {
	if ($val == 0) {
		return "临时保管";
	}
	if ($val == 1) {
		return "已提交";
	}
}
function get_goods_category_name($cate_id){
	$cate = M('GoodsCategory')->field('name')->find($cate_id);
	if($cate['name']){
		return $cate['name'];
	}
}

//--------------------------------------------------------------------
//  发送邮件
//--------------------------------------------------------------------
function send_mail($email, $name, $title, $body) {

	$mail_account = C('ADMIN_MAIL_ACCOUNT');

	//header('Content-type:text/html;charset=utf-8');
	//vendor("Mail.class#send");
	import("@.ORG.Util.send");
	//从PHPMailer目录导入class.send.php类文件
	$mail = new PHPMailer(true);
	// the true param means it will throw exceptions on errors, which we need to catch
	$mail -> IsSMTP();
	// telling the class to use SMTP
	try {
		$mail -> Host = $mail_account['smtpsvr'];
		//"smtp.qq.com"; // SMTP server 部分邮箱不支持SMTP，QQ邮箱里要设置开启的
		$mail -> SMTPDebug = false;
		// 改为2可以开启调试
		$mail -> SMTPAuth = true;
		// enable SMTP authentication
		$mail -> Port = 25;
		// set the SMTP port for the GMAIL server
		$mail -> CharSet = "UTF-8";
		// 这里指定字符集！解决中文乱码问题
		$mail -> Encoding = "base64";
		$mail -> Username = $mail_account['mail_id'];
		// SMTP account username
		$mail -> Password = $mail_account['mail_pwd'];
		// SMTP account password
		$mail -> SetFrom($mail_account['email'], $mail_account['mail_name']);

		//发送者邮箱

		$mail -> AddReplyTo($mail_account['email'], $mail_account['mail_name']);
		//回复到这个邮箱
		$mail -> AddAddress($email, $name);
		$mail -> Subject = "=?UTF-8?B?" . base64_encode($title) . "?=";
		//嵌入式图片处理
		$mail -> MsgHTML($body);

		if ($mail -> Send()) {

		} else {
			$this -> error($name . '该用户未设置邮箱,暂时不能收到邮件,请通过其他方式告知,以免耽误任务进度');
		};
	} catch (phpmailerException $e) {
		//echo $e -> errorMessage();
		//Pretty error messages from PHPMailer
	} catch (Exception $e) {
		//echo $e -> getMessage();
		//Boring error messages from anything else!
	}
}

function get_leader_id() {
	return 1;
}
function is_mobile_request()//是否为手机端app登录
{	
	if(empty($_REQUEST['token'])){
		if(empty($_REQUEST['mobile_login_type'])){
			return false;
		}
	}
// 	return true;
	$_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';
	$mobile_browser = '0';
	if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
		$mobile_browser++;
	if((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false))
		$mobile_browser++;
	if(isset($_SERVER['HTTP_X_WAP_PROFILE']))
		$mobile_browser++;
	if(isset($_SERVER['HTTP_PROFILE']))
		$mobile_browser++;
	$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
	$mobile_agents = array(
			'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
			'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
			'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
			'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
			'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
			'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
			'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
			'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
			'wapr','webc','winw','winw','xda','xda-'
	);
	if(in_array($mobile_ua, $mobile_agents))
		$mobile_browser++;
	if(strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)
		$mobile_browser++;
	// Pre-final check to reset everything if the user is on Windows
	if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)
		$mobile_browser=0;
	// But WP7 is also Windows, with a slightly different characteristic
	if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)
		$mobile_browser++;
	if($mobile_browser>0)
		return true;
	else
		return false;
}
//获取上级的id，若有pos_id则优先根据pos_id，若没有则根据uid
//若上级没有，则获取上级的上级（递归）
function getParentid($uid,$pos_id='',$ignore_del=false){
	$model_user = M('User');
	if(empty($pos_id)){
		$user = $model_user->where(array('id'=>array('eq',$uid)))->find();
		$dept_id = $user['dept_id'];
		$pos_id = $user['pos_id'];
		if(empty($pos_id)){
			return false;
		}
	}
	if(is_numeric($pos_id)){
		$model_dept = M('Dept');
		$dept = $model_dept->where(array('id'=>array('eq',$pos_id)))->find();
		if($dept['pid'] == 0){
			return 0;
		}else{
			$view_user = D('UserView');
			if($ignore_del){
				$user_parent = $view_user->where(array('pos_id'=>array('eq',$dept['pid'])))->order('position_sort')->find();
			}else{
				$user_parent = $view_user->where(array('pos_id'=>array('eq',$dept['pid']),'is_del'=>array('eq',0)))->order('position_sort')->find();
			}
			if(empty($user_parent)){
				return getParentid(null,$dept['pid'],$ignore_del);
			}else{
				return $user_parent['id'];
			}
		}
	}else{
		if(substr($pos_id,0,1) == '_' && is_numeric(substr($pos_id,1))){
			$pos_id_p = substr($pos_id,1);
			if($ignore_del){
				$user_parent = $model_user->where(array('pos_id'=>array('eq',$pos_id_p)))->find();
			}else{
				$user_parent = $model_user->where(array('pos_id'=>array('eq',$pos_id_p),'is_del'=>array('eq',0)))->find();
			}
			
			if(empty($user_parent)){
				return getParentid(null,$pos_id_p,$ignore_del);
			}else{
				return $user_parent['id'];
			}
		}else{
			return false;
		}
	}
}
function getParentDept($uid,$dept_id){
	if(empty($dept_id)){
		$dept_id = get_user_info($uid, 'pos_id');
	}
	if($dept_id!=0){
		$res = M('Dept')->find($dept_id);
		if($res){
			return $res['pid'];
		}else{
			return false;
		}
	}else{
		return false;
	}
	
	
}
/*
 * 获取部门总监的id
 * 入参：员工id，员工部门id
 */
function getDeptManagerId($uid,$dept_id){
	if(empty($uid) && empty($dept_id)){
		return false;
	}
	if(!empty($uid) && empty($dept_id)){
		$user = M('User')->find($uid);
		$dept_id = $user['pos_id'];
	}
	
	$parent_list = array();
	$Parentid = getParentid($uid,$dept_id);
	// dump($dept_id);
	// dump($Parentid);die;
	while($Parentid){//获取上级数组
		$parent_list[] = $Parentid;
		$Parentid = getParentid($Parentid,null);
	}
	// dump($parent_list);die;
	//获取部门总监
	if(count($parent_list) == 1){
		$dept = M('Dept')->find($dept_id);
		if($dept['name']=='园区'){
			return null;
		}
		$userview = D('UserView')->where(array('pos_id'=>array('eq',$dept_id),'is_del'=>array('eq',0)))->order('position_sort')->find();
		$uid = $userview['id'];
	}else if(count($parent_list) >= 2){
		$uid = $parent_list[count($parent_list)-2];
		$user = M('User')->where(array('id'=>array('eq',$uid),'is_del'=>array('eq',0)))->find();
		if($user){
			$dept = M('Dept')->find($user['pos_id']);
		}else{
			return null;
		}
		if($dept['name']=='园区'){
			if(count($parent_list) >= 3){
				$uid = $parent_list[count($parent_list)-3];
				$user = M('User')->where(array('id'=>array('eq',$uid),'is_del'=>array('eq',0)))->find();
				if(empty($user)){
					return null;
// 					$dept = M('Dept')->find($user['pos_id']);
				}
			}else{
				return null;
			}
// 			if(count($parent_list) == 2){
// 				return $dept_id;
// 			}else{
// 				$uid = $parent_list[count($parent_list)-3];
// 				$user = M('User')->find($uid);
// 				return $user['pos_id'];
// 			}
		}
	}else{
		$uid = null;
	}
	return $uid;
}
/*
 * -3：查无此人
 * -2：总经理
 * -1：副总
 * 0:总部
 * >0:园区，园区dept_id；
 */
function isHeadquarters($uid){
	$user = M('User')->find($uid);
	if(empty($user)){
		return -3;
	}
	$dept_id = $user['pos_id'];
	
	$dept_list = array();
	$Parentdept = getParentDept(null,$dept_id);
	while($Parentdept){//获取上级数组
		$dept_list[] = $Parentdept;
		$Parentdept = getParentDept(null,$Parentdept);
	}
	if(count($dept_list) == 1){
		$dept = M('Dept')->find($dept_id);
		if($dept['name']=='园区'){//总部副总
			return -1;
		}else{
			return 0;
		}
	}else if(count($dept_list) >= 2){
		$dept_idd = $dept_list[count($dept_list)-2];
		$dept = M('Dept')->find($dept_idd);
		if($dept['name']=='园区'){
			if(count($dept_list) == 2){
				return $dept_id;
			}else{
				return $dept_list[count($dept_list)-3];
			}
		}else{
			return 0;
		}
	}else{//园区总经理
		return -2;
	}
}
//已经不用
function isHeadquarters_by_uid($uid){
	$user = M('User')->find($uid);
	if(empty($user)){
		return -3;
	}
	$dept_id = $user['pos_id'];
	$parent_list = array();
	$Parentid = getParentid($uid);
	while($Parentid){//获取上级数组
		$parent_list[] = $Parentid;
		$Parentid = getParentid($Parentid);
	}

	if(count($parent_list) == 1){
		$dept = M('Dept')->find($dept_id);
		if($dept['name']=='园区'){//总部副总
			return -1;
		}else{
			return 0;
		}
	}else if(count($parent_list) >= 2){
		$uid = $parent_list[count($parent_list)-2];
		$user = M('User')->find($uid);
		$dept = M('Dept')->find($user['pos_id']);
		if($dept['name']=='园区'){
			if(count($parent_list) == 2){
				return $dept_id;
			}else{
				$uid = $parent_list[count($parent_list)-3];
				$user = M('User')->find($uid);
				return $user['pos_id'];
			}
		}else{
			return 0;
		}
	}else{//园区总经理
		return -2;
	}
}
/*
 * 获取园区的人事行政部负责人id
 */
function getHRManagerIdByDept_id($dept_id){
	$dept = M('Dept')->where(array('pid'=>array('eq',$dept_id),'dept_no'=>array('eq','XZDDB')))->find();
	if(!empty($dept)){
		$dept_id = $dept['id'];
		$model_user = D('UserView');
		$user = $model_user->where(array('pos_id'=>array('eq',$dept_id),'is_del'=>array('eq',0)))->order('position_sort')->find();
	
		if(empty($user['id']) && $dept_id){
			$user['id'] = getParentid(null,$dept_id);
		}
		return $user['id'];
	}else{
		return null;
	}
}
/*
 * 获取园区的老总id
*/
function getGeneralManagerIdByDept_id($dept_id){
	$model_user = D('UserView');
	$user = $model_user->where(array('pos_id'=>array('eq',$dept_id),'is_del'=>array('eq',0)))->order('position_sort')->find();
	if(!empty($user)){
		return $user['id'];
	}
}
function getHRDeputyGeneralManagerId($uid){
	$flag = isHeadquarters($uid);
	if($flag>0){
		return getHRManagerIdByDept_id($flag);
	}elseif ($flag!=-3){
		return getHeadquartersHRDeputyGeneralManagerId();
	}else{
		return null;
	}
}
/*
 * 获取总部的人事行政部负责人id
 */
function getHeadquartersHRDeputyGeneralManagerId(){
	$model_dept = M('Dept');
	$dept = $model_dept->where(array('dept_no'=>array('eq','HR')))->find();
	$dept_id = $dept['id'];
	
	$model_user = D('UserView');
	$user = $model_user->where(array('pos_id'=>array('eq',$dept_id),'is_del'=>array('eq',0)))->order('position_sort')->find();
	return $user['id'];
}
function getFinancialManagerId(){
	$model_dept = M('Dept');
	$dept = $model_dept->where(array('dept_no'=>array('eq','CWZX')))->find();
	$dept_id = $dept['id'];
	
	$model_user = D('UserView');
	$user = $model_user->where(array('pos_id'=>array('eq',$dept_id),'is_del'=>array('eq',0)))->order('position_sort')->find();
	return $user['id'];
}
function getOfficeManagerId(){//总经办主任id
	$model_dept = M('Dept');
	$dept = $model_dept->where(array('dept_no'=>array('eq','ZJB')))->find();
	$dept_id = $dept['id'];
	
	$model_user = D('UserView');
	$user = $model_user->where(array('pos_id'=>array('eq',$dept_id),'is_del'=>array('eq',0)))->order('position_sort')->find();
	return $user['id'];
}
function getLegalManagerId(){//法务部id
	$model_dept = M('Dept');
	$dept = $model_dept->where(array('dept_no'=>array('eq','FWB')))->find();
	$dept_id = $dept['id'];

	$model_user = D('UserView');
	$user = $model_user->where(array('pos_id'=>array('eq',$dept_id),'is_del'=>array('eq',0)))->order('position_sort')->find();
	return $user['id'];
}
function getRSManagerId(){//人事负责人id
	$model_dept = M('Dept');
	$dept = $model_dept->where(array('dept_no'=>array('eq','RSZG')))->find();
	$dept_id = $dept['id'];

	$model_user = D('UserView');
	$user = $model_user->where(array('pos_id'=>array('eq',$dept_id),'is_del'=>array('eq',0)))->order('position_sort')->find();
	return $user['id'];
}
function getGeneralManagerId($uid){//获取总部的老总id，或园区的老总id
	if(empty($uid)){
		return getHeadquartersGeneralManagerId();
	}
	$flag = isHeadquarters($uid);
	if($flag>0){
		return getGeneralManagerIdByDept_id($flag);
	}elseif ($flag!=-3){
		return getHeadquartersGeneralManagerId();
	}else{
		return null;
	}
}
function getHeadquartersGeneralManagerId(){//获取总部总经理id
	$model_position = M('Position');
	$position = $model_position->where(array('name'=>array('eq','总经理')))->find();
	if(empty($position)){
		$position = $model_position->order('sort')->find();
	}
	$position_id = $position['id'];
	$model_user = M('User');
	$user = $model_user->where(array('position_id'=>array('eq',$position_id),'is_del'=>array('eq',0)))->find();
	return $user['id'];
}
function getFrontDesk(){
	$model_dept = M('Dept');
	$dept = $model_dept->where(array('dept_no'=>array('eq','XZQT')))->find();
	$dept_id = $dept['id'];
	
	$model_user = D('UserView');
	$user = $model_user->where(array('pos_id'=>array('eq',$dept_id),'is_del'=>array('eq',0)))->order('position_sort')->find();
	return $user['id'];
}
function getRank($uid){
	$model_user = D('UserView');
	$user = $model_user->where(array('id'=>array('eq',$uid)))->find();
	$position_name = $user['position_name'];
	switch($position_name){
		case '常务副总' : return 1;
		case '副总' : return 1;
		case '总监' : return 1;
		case '经理' : return 2;
		case '主管' : return 3;
		case '助理' : return 3;
		case '员工' : return 3;
		default :return 3;
	}
}
function getlength($uid){//获取从根节点到uid的长度
	$user = M('User')->find($uid);
	$dept_id = $user['pos_id'];
	
	$parent_list = array();
	while(!empty($dept_id)){//获取上级数组
		$parent_list[] = $dept_id;
		$dept_p = M('Dept')->find($dept_id);
		$dept_id = $dept_p['pid'];
	}
	return count($parent_list);
}
/*
 * 流程只能越走越高
 */
function checkFlowUp($array){
	if(!empty($array) && is_array($array)){
		$max = getlength($array[0]);
		foreach ($array as $k=>$v){
			if($k>0){
				if(getlength($v)>$max){
					unset($array[$k]);
				}else{
					$max = getlength($v);
				}
			}
			
		}
		return $array;
	}
}
function checkFlowNotMe($array,$uid=null){
	$uid=$uid?$uid:get_user_id();
	if(!empty($array) && is_array($array)){
		foreach ($array as $k=>$v){
			if($v==$uid){
				unset($array[$k]);
			}	
		}
		return $array;
	}
}
/*
 * 相邻元素相同则过滤（只留一个）；
 * 不相邻元素可以相同
 */
function array_unique2($array){
	if(!empty($array) && is_array($array)){
		$t = '';
		foreach ($array as $k=>$v){
			if($v == $t){
				unset($array[$k]);
			}else{
				$t = $v;
			}
		}
	}
	return $array;
}
function getFlow($uid,$day,$unique=true){
	if($day<=3 && $day>=0){
		return getParentid($uid);
	}elseif ($day>3 && $day<7){
		if(getRank($uid) == 3){//主管，助理，员工
			if($unique){
				if(isYuanQuCaiWuBu($uid)){
					return checkFlowUp(array_unique(array(getParentid($uid),getFinancialManagerId($uid),getHRDeputyGeneralManagerId($uid))));
				}else{
					return checkFlowUp(array_unique(array(getParentid($uid),getHRDeputyGeneralManagerId($uid))));
				}
			}else{
				if(isYuanQuCaiWuBu($uid)){
					return checkFlowUp(array(getParentid($uid),getFinancialManagerId($uid),getHRDeputyGeneralManagerId($uid)));
				}else{
					return checkFlowUp(array(getParentid($uid),getHRDeputyGeneralManagerId($uid)));
				}
				
			}
		}elseif (getRank($uid) == 2){//经理
		    if(isYuanQuCaiWuBu($uid)){
		    	return array(getFinancialManagerId($uid),getHRDeputyGeneralManagerId($uid));
		    }else{
		    	return getHRDeputyGeneralManagerId($uid);
		    }
			
		}elseif (getRank($uid) == 1){//副总，总监
			return getGeneralManagerId($uid);
		}else{
			return false;
		}
	}elseif($day>=7){
		if(getRank($uid) == 3){//主管，助理，员工
			if($unique){
				if(isYuanQuCaiWuBu($uid)){
					return checkFlowUp(array_unique(array(getParentid($uid),getFinancialManagerId($uid),getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid))));
				}else{
					return checkFlowUp(array_unique(array(getParentid($uid),getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid))));
				}
			}else{
				if(isYuanQuCaiWuBu($uid)){
					return checkFlowUp(array(getParentid($uid),getFinancialManagerId($uid),getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid)));
				}else{
					return checkFlowUp(array(getParentid($uid),getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid)));
				}
				
			}
		}elseif (getRank($uid) == 2){//经理，总监
			if($unique){
				if(isYuanQuCaiWuBu($uid)){
					return checkFlowUp(array_unique(array(getFinancialManagerId($uid),getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid))));
				}else{
					return checkFlowUp(array_unique(array(getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid))));
				}
				
			}else{
				if(isYuanQuCaiWuBu($uid)){
					return checkFlowUp(array(getFinancialManagerId($uid),getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid)));	
				}else{
					return checkFlowUp(array(getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid)));
				}
				
			}
		}elseif (getRank($uid) == 1){//副总
			return getGeneralManagerId($uid);
		}else{
			return false;
		}
	}else{
		return false;
	}
}
function getNameById($uid){
	if($uid){
		$model_user = M('User');
		$user = $model_user->where(array('id'=>array('eq',intval($uid))))->find();
		if($user){
			return $user['name'];
		}else{
			return false;
		}
		
	}else {
		return false;
	}
	
}
function getEmpNoById($uid){
	if($uid){
		$model_user = M('User');
		$user = $model_user->where(array('id'=>array('eq',intval($uid))))->find();
		if($user){
			return $user['emp_no'];
		}else{
			return false;
		}

	}else {
		return false;
	}

}
function getFlowName($uid,$day){
	$flow = getFlow($uid,$day);
	if(!$flow){
		return false;
	}elseif (!is_array($flow)){
		return getNameById($flow);
	}else{
		foreach ($flow as $v){
			$name[] = getNameById($v);
		}
		return $name;
	}
}
function getFlowEmpNo($uid,$day){
	$flow = getFlow($uid,$day);
	if(!$flow){
		return false;
	}elseif (!is_array($flow)){
		return getEmpNoById($flow);
	}else{
		foreach ($flow as $v){
			$name[] = getEmpNoById($v);
		}
		return $name;
	}
}
function isZhaopinDirector($uid){
	return $uid==getZhaopinDirector($uid);
}
function isYuanQuCaiWuBu($uid){
	$dept_ids = M('Dept')->where(array('dept_no'=>'CWB','name'=>'财务部','_logic'=>'or'))->getField('id',true);
	if(!empty($dept_ids) && is_array($dept_ids)){
		$child_depts = array();
		foreach ($dept_ids as $dept_id){
			$child_depts = array_merge($child_depts,get_child_dept_all($dept_id));
		}
		$user = M('User')->where(array('id'=>$uid,'pos_id'=>array('in',$child_depts)))->find();
		if(!empty($user)){
			return true;
		}else{
			return false;
		}
	}else{
		return false;
	}
}
function getZhaopinDirector($uid){
	$flag = isHeadquarters($uid);
	if($flag>0){
		$model_dept = M('Dept');
		$dept_id = $model_dept->where(array('pid'=>array('eq',$flag),'dept_no'=>array('eq','XZDDB')))->getField('id');
		$model_user = M('User');
		$id = $model_user->where(array('pos_id'=>array('eq',$dept_id),'is_del'=>array('eq',0)))->getField('id');
		return $id;
	}elseif ($flag!=-3){
		$model_dept = M('Dept');
		$dept_id = $model_dept->where(array('dept_no'=>array('eq','ZP')))->getField('id');
		$model_user = M('User');
		$id = $model_user->where(array('pos_id'=>array('eq',$dept_id),'is_del'=>array('eq',0)))->getField('id');
		return $id;
	}else{
		return null;
	}
	
	
}
function is_holiday($unix_timestamp){
	$date = date('Ymd',$unix_timestamp);
	$model = M('Holiday');
	$where['date'] = $date;
	$res = $model->where($where)->find();
	if($res){
		return $res['is_holiday'];
	}else{
		$w = date('w',$unix_timestamp);
		if($w=='0' || $w=='6'){
			return 1;
		}else{
			return 0;
		}
	}
}
function get_leave_seconds_weekend($start,$end){
	$start_date = date('Y-m-d',$start);
	$start_date_w = date('w',$start);
	$start_date2 = date('Ymd',$start);
	$start_date_1 = strtotime($start_date.' 00:00')+86400;
	//判断是否是法定节假日
	$times = M('Holiday')->where(array('is_holiday'=>'1','date'=>$start_date2))->getField('times');
	if(is_holiday($start) && ($start_date_w=='0' || $start_date_w == '6' )&& ($times=='1' || empty($times))){
		
		if($end<$start_date_1){
			return get_leave_day_seconds($start,$end,true);
		}else{
			return get_leave_day_seconds($start,$start_date_1,true)+get_leave_seconds_weekend($start_date_1,$end);
		}
	}else{
		return 0;
	}
}

function get_leave_seconds_fading_holidy($start,$end){
	$start_date = date('Y-m-d',$start);
	$start_date2 = date('Ymd',$start);
	$start_date_1 = strtotime($start_date.' 00:00')+86400;
	$times = M('Holiday')->where(array('is_holiday'=>'1','date'=>$start_date2))->getField('times');
	if($end<$start_date_1){
		if($times){
			return $times*get_leave_day_seconds($start,$end,true);
		}else{
			return 0;
		}
		
	}else{
		if($times){
			return $times*get_leave_day_seconds($start,$start_date_1,true)+get_leave_seconds_fading_holidy($start_date_1,$end);
		}else{
			return get_leave_seconds_fading_holidy($start_date_1,$end);
		}
	}
}
function get_leave_seconds($start,$end){//获取start和end之间经过的秒数（工作时间）
	$start_date = date('Y-m-d',$start);
	$start_date_1 = strtotime($start_date.' 00:00')+86400;
	if($end<$start_date_1){
		return get_leave_day_seconds($start,$end);
	}else{
		return get_leave_day_seconds($start,$start_date_1)+get_leave_seconds($start_date_1,$end);
	}
}
function get_overtime_seconds($start,$end){//获取start和end之间经过的秒数（除去12点到1点的午餐时间）
	$start_date = date('Y-m-d',$start);
	$start_date_1 = strtotime($start_date.' 00:00')+86400;
	if($end<$start_date_1){
		return get_overtime_day_seconds($start,$end);
	}else{
		return get_overtime_day_seconds($start,$start_date_1)+get_overtime_seconds($start_date_1,$end);
	}
}
// function get_leave_seconds1($start,$end){
// 	$seconds = 0;
// 	for($i=$start;$i<$end;$i=$i+86400){
// 		if(is_holiday($i)=='1'){
// 			continue;
// 		}else{
// 			if($end<$i+86400){
// 				$seconds += get_leave_day_seconds($i,$end);
// 			}else{
// 				$seconds += get_leave_day_seconds($i,$i+86400);
// 			}
			
// 		}
// 	}
// 	return $seconds;
// }
function get_leave_day_seconds($start,$end,$is_holidy=false){//获取一天之中start和end之间经过的秒数（工作时间）
	if(!$is_holidy){
		if(is_holiday($start)=='1'){
				return 0;
		}
	}
	
	$start_date = date('Y-m-d',$start);
	$start_morning = strtotime($start_date.' '.get_system_config("MORNING_START"));
	$end_morning = strtotime($start_date.' '.get_system_config("MORNING_END"));
	$start_afternoon = strtotime($start_date.' '.get_system_config("AFTERNOON_START"));
	$end_afternoon = strtotime($start_date.' '.get_system_config("AFTERNOON_END"));
	if($end-$start<86400){
		if($start<=$start_morning){
			if($end>=$end_afternoon){
				return $end_morning-$start_morning+$end_afternoon-$start_afternoon;
			}elseif ($end>$start_afternoon && $end<$end_afternoon){
				return $end_morning-$start_morning+$end-$start_afternoon;
			}elseif ($end>=$end_morning && $end<=$start_afternoon){
				return $end_morning-$start_morning;
			}elseif ($end>$start_morning && $end<$end_morning){
				return $end-$start_morning;
			}elseif ($end<=$start_morning){
				return 0;
			}
		}elseif($start>$start_morning && $start<$end_morning){
			if($end>=$end_afternoon){
				return $end_morning-$start+$end_afternoon-$start_afternoon;
			}elseif ($end>$start_afternoon && $end<$end_afternoon){
				return $end_morning-$start+$end-$start_afternoon;
			}elseif ($end>=$end_morning && $end<=$start_afternoon){
				return $end_morning-$start;
			}elseif ($end>$start_morning && $end<$end_morning){
				return $end-$start;
			}
		}elseif($start>=$end_morning && $start<=$start_afternoon){
			if($end>=$end_afternoon){
				return $end_afternoon-$start_afternoon;
			}elseif ($end>$start_afternoon && $end<$end_afternoon){
				return $end-$start_afternoon;
			}elseif ($end>=$end_morning && $end<=$start_afternoon){
				return 0;
			}
		}elseif($start>$start_afternoon && $start<$end_afternoon){
			if($end>=$end_afternoon){
				return $end_afternoon-$start;
			}elseif ($end>$start_afternoon && $end<$end_afternoon){
				return $end-$start;
			}
		}elseif($start>=$end_afternoon){
			return 0;
		}
	}else{
		return $end_morning-$start_morning+$end_afternoon-$start_afternoon;
	}
}
/*
 * 获取一天之中start和end之间经过的秒数（中间减去午休时间）
 */
function get_overtime_day_seconds($start,$end){
	$start_date = date('Y-m-d',$start);
	$end_morning = strtotime($start_date.' '.get_system_config("MORNING_END"));
	$start_afternoon = strtotime($start_date.' '.get_system_config("AFTERNOON_START"));
	if($end-$start<86400){
		if($start<$end_morning){
			if ($end>$start_afternoon){
				return $end_morning-$start+$end-$start_afternoon;
			}elseif ($end>=$end_morning && $end<=$start_afternoon){
				return $end_morning-$start;
			}elseif ($end<$end_morning){
				return $end-$start;
			}
		}elseif($start>=$end_morning && $start<=$start_afternoon){
			if ($end>$start_afternoon){
				return $end-$start_afternoon;
			}elseif ($end>=$end_morning && $end<=$start_afternoon){
				return 0;
			}
		}elseif($start>$start_afternoon){
			if ($end>$start_afternoon){
				return $end-$start;
			}
		}
	}else{
		return $end_morning-$start+$end-$start_afternoon;
	}
}
function slice_time($start,$end){
	$start_date = date('Y-m-d',$start);
	$start_date_1 = strtotime($start_date.' 00:00')+86400;
	if($end<$start_date_1){
		return slice_time_day($start,$end);
	}else{
		return array_merge(slice_time_day($start,$start_date_1),slice_time($start_date_1,$end));
	}
}
function slice_time_over_time($start,$end){
	$start_date = date('Y-m-d',$start);
	$start_date_1 = strtotime($start_date.' 00:00')+86400;
	if($end<$start_date_1){
		return slice_time_day_over_time($start,$end);
	}else{
		return array_merge(slice_time_day_over_time($start,$start_date_1),slice_time_over_time($start_date_1,$end));
	}
}
function slice_time_day($start,$end){
	if(is_holiday($start)=='1'){
		return ;
	}
	$start_date = date('Y-m-d',$start);
	$start_morning = strtotime($start_date.' '.get_system_config("MORNING_START"));
	$end_morning = strtotime($start_date.' '.get_system_config("MORNING_END"));
	$start_afternoon = strtotime($start_date.' '.get_system_config("AFTERNOON_START"));
	$end_afternoon = strtotime($start_date.' '.get_system_config("AFTERNOON_END"));
	if($end-$start<86400){
		if($start<=$start_morning){
			if($end>=$end_afternoon){
				return array(date('H:i',$start_morning).'-'.date('H:i',$end_morning).'|1|'.date('d',$start),date('H:i',$start_afternoon).'-'.date('H:i',$end_afternoon).'|2|'.date('d',$start));
			}elseif ($end>$start_afternoon && $end<$end_afternoon){
				return array(date('H:i',$start_morning).'-'.date('H:i',$end_morning).'|1|'.date('d',$start),date('H:i',$start_afternoon).'-'.date('H:i',$end).'|2|'.date('d',$start));
			}elseif ($end>=$end_morning && $end<=$start_afternoon){
				return array(date('H:i',$start_morning).'-'.date('H:i',$end_morning).'|1|'.date('d',$start));
			}elseif ($end>$start_morning && $end<$end_morning){
				return array(date('H:i',$start_morning).'-'.date('H:i',$end).'|1|'.date('d',$start));
			}elseif ($end<=$start_morning){
				return array();
			}
		}elseif($start>$start_morning && $start<$end_morning){
			if($end>=$end_afternoon){
				return array(date('H:i',$start).'-'.date('H:i',$end_morning).'|1|'.date('d',$start),date('H:i',$start_afternoon).'-'.date('H:i',$end_afternoon).'|2|'.date('d',$start));
			}elseif ($end>$start_afternoon && $end<$end_afternoon){
				return array(date('H:i',$start).'-'.date('H:i',$end_morning).'|1|'.date('d',$start),date('H:i',$start_afternoon).'-'.date('H:i',$end).'|2|'.date('d',$start));
			}elseif ($end>=$end_morning && $end<=$start_afternoon){
				return array(date('H:i',$start).'-'.date('H:i',$end_morning).'|1|'.date('d',$start));
			}elseif ($end>$start_morning && $end<$end_morning){
				return array(date('H:i',$start).'-'.date('H:i',$end).'|1|'.date('d',$start));
			}
		}elseif($start>=$end_morning && $start<=$start_afternoon){
			if($end>=$end_afternoon){
				return array(date('H:i',$start_afternoon).'-'.date('H:i',$end_afternoon).'|2|'.date('d',$start));
			}elseif ($end>$start_afternoon && $end<$end_afternoon){
				return array(date('H:i',$start_afternoon).'-'.date('H:i',$end).'|2|'.date('d',$start));
			}elseif ($end>=$end_morning && $end<=$start_afternoon){
				return array();
			}
		}elseif($start>$start_afternoon && $start<$end_afternoon){
			if($end>=$end_afternoon){
				return array(date('H:i',$start).'-'.date('H:i',$end_afternoon).'|2|'.date('d',$start));
			}elseif ($end>$start_afternoon && $end<$end_afternoon){
				return array(date('H:i',$start).'-'.date('H:i',$end).'|2|'.date('d',$start));
			}
		}elseif($start>=$end_afternoon){
			return array();
		}
	}else{
		return array(date('H:i',$start_morning).'-'.date('H:i',$end_morning).'|1|'.date('d',$start),date('H:i',$start_afternoon).'-'.date('H:i',$end_afternoon).'|2|'.date('d',$start));
	}
}
function slice_time_day_over_time($start,$end){
	$start_date = date('Y-m-d',$start);
	$noon = strtotime($start_date.' 12:00');
	if($end-$start<86400){
		if($start>=$noon){
			return array(date('H:i',$start).'-'.date('H:i',$end).'|2|'.date('d',$start));
		}elseif($end<=$noon){
			return array(date('H:i',$start).'-'.date('H:i',$end).'|1|'.date('d',$start));
		}else{
			return array(date('H:i',$start).'-'.date('H:i',$noon).'|1|'.date('d',$start),date('H:i',$noon).'-'.date('H:i',$end).'|2|'.date('d',$start));
		}
	}else{
		return array(date('H:i',$start).'-'.date('H:i',$noon).'|1|'.date('d',$start),date('H:i',$noon).'-'.date('H:i',$end).'|2|'.date('d',$start));
	}
}
/*
 * 输入id(或者数组)，输出emp_no，emp_name，emp_name_display
 */
function getFlowData($ids,$mark='->'){
	if(empty($ids)){
		return false;
	}elseif(!is_array($ids)){
		$ids = array($ids);
	}
	$emp_no = '';
	$emp_name = '';
	$emp_name_display = '';
	foreach ($ids as $id){
		if(!empty($id)){
			$flow_emp_no = get_user_info($id,'emp_no');
			$flow_name = get_user_info($id,'name');
			
			$emp_no .= 'emp_'.$flow_emp_no.'|';
			$emp_name .= $flow_name.'<>';
			$emp_name_display .= $mark.$flow_name;
		}
	}
	$emp_name_display = substr($emp_name_display,strlen($mark));
	return array('confirm'=>$emp_no,'confirm_name'=>$emp_name,'confirm_name_display'=>$emp_name_display);
}
/*
 * 根据flow表的type字段判断使用哪张表
 */
function getModelName($flow_id){
	$model = M('Flow');
	$flow = $model->find($flow_id);
	$model = M('FlowType');
	$flow_type = $model->find($flow['type']);
	switch($flow_type['name']){
		case '员工请假申请' : return 'FlowLeave';
		case '外勤/出差申请' : return 'FlowOutside';
		case '出勤异常申请' : return 'FlowAttendance';
		case '加班申请' : return 'FlowOverTime';
		case '部门招聘需求申请' : return 'FlowEmploymentApplication';
		case '内部联络单' : return 'FlowInternal';
		case '会务申请' :return 'FlowMeetingCommunicate';
		case '名片申请' :return 'FlowCardApplication';
		case '员工离职申请' : return 'FlowResignationApplication';
		case '员工离职交接申请' : return 'FlowResignationList';
		case '试用期评估表' : return 'FlowProbationEvaluate';
		case '转正申请' : return 'FlowRegularWorkerApplication';
		case '员工调动申请' : return 'FlowPersonnelChanges';
		case '员工调薪申请' : return 'FlowSalaryChanges';
		case '办公用品采购申请' : return 'FlowOfficeSuppliesApplication';
		case '办公用品领用申请' : return 'FlowOfficeUseApplication';
		case '物品采购调拨申请' : return 'FlowGoodsProcurementAllocation';
		case '简历' : return 'UserResume';
		default :return 'FlowCommon';
	}
}
function convertUnderline1 ( $str , $ucfirst = true)
{
	while(($pos = strpos($str , '_'))!==false)
		$str = substr($str , 0 , $pos).ucfirst(substr($str , $pos+1));

	return $ucfirst ? ucfirst($str) : $str;
}
function convertUrlQuery($query)
{
	$queryParts = explode('&', $query);
	 
	$params = array();
	foreach ($queryParts as $param)
	{
		$item = explode('=', $param);
		$params[$item[0]] = $item[1];
	}
	 
	return $params;
}
function exp_info($info){
	if(!empty($info)){
		foreach ($info as $k => $v){
			$temp[] =  explode(',',$v);
		}
		return $temp;
	}
	return '';
}
function news_home_view($add){
	if(!empty($add)){
		$files = array_filter(explode(';',$add));
		$file = M("file");
		foreach ($files as $k => $v){
			$j = $k +1;
			$finfo = $file -> getBySid($v);
			$save = $finfo['savename'];
			$path = get_save_url();
			echo "<span class=\"num$j\"><img src=\"$path$save\"/></span>";
			if($j >= 3){break;}
		}
	}
	echo '';
}
function news_home_time($time){
	if(!empty($time)){
		$t=time()-$time;
	    $f=array(
	        '31536000'=>'年',
	        '2592000'=>'个月',
	        '604800'=>'星期',
	        '86400'=>'天',
	        '3600'=>'小时',
	        '60'=>'分钟',
	        '1'=>'秒'
	    );
	    foreach ($f as $k=>$v)    {
	        if (0 !=$c=floor($t/(int)$k)) {
	            return $c.$v.'前';
	        }
	    }
	}
	return '';
}
function home_survey_show($file){
	if(!empty($file)){
		$list = array_filter(explode(';',$file));
		if(!empty($list[0])){
			$file_info = M('file') -> getBySid($list[0]);
			$save = $file_info['savename'];
			$path = get_save_url();
			echo "<img src=\"$path$save\"/>";
		}
	}
	echo '';
}
function plan_home_show($plan){
	if(!empty($plan)){
		$ps = explode('|',$plan);
		$con = '';
		if(count($ps) == 1){
			$con .= '<span class="zt2">进行中</span>';
		}else{
			$con .= '<span class="zt2">已完成</span>';
		}
		if($ps[0] == '1'){
			$con .='<span class="lx2">月计划</span>';
		}else{
			$con .='<span class="lx2">周计划</span>';
		}
	}
	echo $con;
}
function seniority($date2){
	$date1 = date("Y/m/d");
	if(!empty($date2)){
		if(strtotime($date1)>strtotime($date2)){  
        $tmp=$date2;  
        $date2=$date1;  
        $date1=$tmp;  
	    }  
	    list($Y1,$m1,$d1)=explode('/',$date1);  
	    list($Y2,$m2,$d2)=explode('/',$date2);  
	    $y=$Y2-$Y1;  
	    $m=$m2-$m1;  
	    $d=$d2-$d1;  
	    if($d<0){  
	        $d+=(int)date('t',strtotime("-1 month $date2"));  
	        $m--;  
	    }  
	    if($m<0){  
	        $m+=12;  
	        $y--;  
	    }
	    if($y == 0){
	    	return $m.'月';
	    }elseif($m == 0){
			return $d.'日';
	    }else{
		    return $y.'年'.$m.'月';  
	    }
	    
	}
	return '';
}
function ToNumberSystem26($n){
	$s = '';
	while ($n > 0){
		$m = $n % 26;
		if ($m == 0) $m = 26;
		$s = chr($m + 64) . $s;
		$n = ($n - $m) / 26;
	}
	return $s;
}
function getHourPlan($user_id,$hour,$timestamp,$create=''){
	if($hour>=0){
		return false;
	}
	//如果调休过期计算方式为按月，则把now改为月初
	if(get_system_config("LEAVE_CALCULATE_TYPE")=='按月'){
		$d = date('Y-m',$timestamp);
		$now = strtotime($d.'-1');
	}else{
		$now = $timestamp;
	}
	$part = array();
	$flag = false;
	$three_month_ago = strtotime("-3 months",$now);
	$over_time_hours = M('FlowHour'.$create)->where(array('user_id'=>array('eq',$user_id),'create_time'=>array('between',array($three_month_ago,$timestamp-1)),'status'=>array('eq','1'),'hour'=>array('gt',0)))->order('create_time asc')->select();
	$leave_hours = M('FlowHour'.$create)->where(array('user_id'=>array('eq',$user_id),'create_time'=>array('between',array($three_month_ago,strtotime("+4 months",$timestamp))),'status'=>array('eq','1'),'hour'=>array('lt',0)))->order('create_time asc')->select();
	foreach ($over_time_hours as $k=>$v){
		if($v['is_use'] !== '1'){//没用完
			$cur_hour = intval($v['hour']);
			foreach ($leave_hours as $kk=>$vv){
				$next_over_time = false;
				$use = unserialize($vv['is_use']);
				foreach ($use as $kkk=>$vvv){
					if($v['id'] == $kkk){
						$cur_hour -= $vvv;
						if($cur_hour == '0'){
							$next_over_time = true;
							break;
						}
					}
				}
				if($next_over_time){
					break;
				}
			}

// 			dump(array('id'=>$v['id'],'hour'=>$cur_hour,'o'=>$over_time_hours));
			if($hour*(-1)<=$cur_hour){
				$flag = true;
				$part[] = array('id'=>$v['id'],'hour'=>$hour*(-1));
				
				if($hour*(-1) == $cur_hour){//加班单设为1，表示用完
					M('FlowHour'.$create)->where(array('id'=>$v['id']))->save(array('is_use'=>'1'));
				}
				break;
			}elseif ($cur_hour>0){
				$part[] = array('id'=>$v['id'],'hour'=>$cur_hour);
				$hour = $hour + $cur_hour;
				M('FlowHour'.$create)->where(array('id'=>$v['id']))->save(array('is_use'=>'1'));
			}
		}
	}
	if($flag){//完成了
		$res = array();
		foreach ($part as $k=>$v){
			$res[$v['id']] = $v['hour'];
		}
		return $res;
	}else{
		return false;
	}
// 	return array('70'=>1,'60'=>2);
}
function getAvailableHour3($timestamp,$uid,$create=''){
	if(empty($timestamp)){
		$timestamp = time();
	}
	if(empty($uid)){
		$uid = get_user_id();
	}
	//如果调休过期计算方式为按月，则把now改为月初
	if(get_system_config("LEAVE_CALCULATE_TYPE")=='按月'){
		$d = date('Y-m',$timestamp);
		$now = strtotime($d.'-1');
	}else{
		$now = $timestamp;
	}

	$use_sum = 0;
	$three_month_ago = strtotime("-3 months",$now);
	$over_time_hours_ids = M('FlowHour'.$create)->where(array('user_id'=>array('eq',$uid),'create_time'=>array('between',array($three_month_ago,$timestamp)),'status'=>array('eq','1'),'hour'=>array('gt',0),'is_use'=>array('neq','1')))->getField('id',true);
	$over_time_hours_sum = M('FlowHour'.$create)->where(array('user_id'=>array('eq',$uid),'create_time'=>array('between',array($three_month_ago,$timestamp)),'status'=>array('eq','1'),'hour'=>array('gt',0),'is_use'=>array('neq','1')))->sum('hour');
	$leave_hours = M('FlowHour'.$create)->where(array('user_id'=>array('eq',$uid),'create_time'=>array('between',array($three_month_ago,strtotime("+4 months",$timestamp))),'status'=>array('eq','1'),'hour'=>array('lt',0)))->select();
	$leave_hours_wait = M('FlowHour'.$create)->where(array('user_id'=>array('eq',$uid),'create_time'=>array('between',array($three_month_ago,strtotime("+4 months",$timestamp))),'status'=>array('eq','0'),'hour'=>array('lt',0)))->sum('hour');
	foreach ($leave_hours as $k=>$v){
		$use = unserialize($v['is_use']);
		foreach ($use as $kk=>$vv){
			if(in_array($kk, $over_time_hours_ids)){
				$use_sum += $vv;
			}
		}
	}
	if($over_time_hours_sum>=$use_sum-$leave_hours_wait){
		return $over_time_hours_sum-$use_sum+$leave_hours_wait;
	}else{
		return false;
	}
}
function getAvailableHour2($timestamp,$uid,$create=''){
	if(empty($timestamp)){
		$timestamp = time();
	}
	if(empty($uid)){
		$uid = get_user_id();
	}
	//如果调休过期计算方式为按月，则把now改为月初
	if(get_system_config("LEAVE_CALCULATE_TYPE")=='按月'){
		$d = date('Y-m',$timestamp);
		$now = strtotime($d.'-1');
	}else{
		$now = $timestamp;
	}

	$use_sum = 0;
	$three_month_ago = strtotime("-3 months",$now);
	$over_time_hours_ids = M('FlowHour'.$create)->where(array('user_id'=>array('eq',$uid),'create_time'=>array('between',array($three_month_ago,$timestamp)),'status'=>array('eq','1'),'hour'=>array('gt',0),'`use`'=>array('neq','1')))->getField('id',true);
	$over_time_hours_sum = M('FlowHour'.$create)->where(array('user_id'=>array('eq',$uid),'create_time'=>array('between',array($three_month_ago,$timestamp)),'status'=>array('eq','1'),'hour'=>array('gt',0),'`use`'=>array('neq','1')))->sum('hour');
	$leave_hours = M('FlowHour'.$create)->where(array('user_id'=>array('eq',$uid),'create_time'=>array('between',array($three_month_ago,strtotime("+4 months",$timestamp))),'status'=>array('eq','1'),'hour'=>array('lt',0)))->select();
	$leave_hours_wait = M('FlowHour'.$create)->where(array('user_id'=>array('eq',$uid),'create_time'=>array('between',array($three_month_ago,strtotime("+4 months",$timestamp))),'status'=>array('eq','0'),'hour'=>array('lt',0)))->sum('hour');
	foreach ($leave_hours as $k=>$v){
		$use = unserialize($v['use']);
		foreach ($use as $kk=>$vv){
			if(in_array($kk, $over_time_hours_ids)){
				$use_sum += $vv;
			}
		}
	}
	if($over_time_hours_sum>=$use_sum-$leave_hours_wait){
		return $over_time_hours_sum-$use_sum+$leave_hours_wait;
	}else{
		return false;
	}
}

function getAvailableHour($now,$uid){
	if(empty($now)){
		$now = time();
	}
	if(empty($uid)){
		$uid = get_user_id();
	}
	//如果调休过期计算方式为按月，则把now改为月初
	if(get_system_config("LEAVE_CALCULATE_TYPE")=='按月'){
		$d = date('Y-m',$now);
		$now = strtotime($d.'-1');
	}
	
	$three_month_ago = strtotime("-3 months",$now);
	$res1 = M('FlowHour')->where(array('user_id'=>array('eq',$uid),'create_time'=>array('egt',$three_month_ago),'status'=>array('eq','0'),'hour'=>array('lt',0)))->sum('hour');
	$res2 = M('FlowHour')->where(array('user_id'=>array('eq',$uid),'create_time'=>array('egt',$three_month_ago),'status'=>array('eq','1')))->sum('hour');
	
	if(empty($res1) && empty($res2)){
		$res1 = 0;
	}
	return $res1+$res2;
}

function getAvailableYearHour($now,$uid){
	if(empty($now)){
		$now = time();
	}
	$this_year = date('Y',$now);
	$this_month = date('m',$now);
	$this_day = date('d',$now);
	
	if(empty($uid)){
		$uid = get_user_id();
	}
	$UserRecord = M('UserRecord')->where(array('user_id'=>array('eq',$uid)))->find();
	if(!empty($UserRecord) && !empty($UserRecord['information'])){
		$information = explode('|',$UserRecord['information']);
		if(!empty($information[0])){
			$in_date = $information[0];
			$in_date1 = explode('.',$in_date);
			if(!empty($in_date1[1])){
				$in_date2 = $in_date1[0].'-'.$in_date1[1].'-'.$in_date1[2];
			}else{
				$in_date1 = explode('/',$in_date);
				if(!empty($in_date1[1])){
					$in_date2 = $in_date1[0].'-'.$in_date1[1].'-'.$in_date1[2];
				}else{
					$in_date1 = explode('-',$in_date);
					if(!empty($in_date1[1])){
						$in_date2 = $in_date1[0].'-'.$in_date1[1].'-'.$in_date1[2];
					}
				}
			}
			//这样的year只是模糊数，但是避免了闰月影响
			$year = $this_year-$in_date1[0]+($this_month-$in_date1[1])/30+($this_day-$in_date1[2])/360;
// 			$year = ($now-strtotime($in_date2))/(365*24*60*60);
		}
	}
	if($year>=1){
		$user = D('UserView')->where(array('id'=>array('eq',$uid)))->find();
		$position_name = $user['position_name'];
		
		$start = strtotime($this_year.'-'.$in_date1[1].'-'.$in_date1[2]);
		$end = strtotime(($this_year+1).'-'.$in_date1[1].'-'.$in_date1[2]);
		
		if($now<$start){
			$end = $start;
			$start = strtotime(($this_year-1).'-'.$in_date1[1].'-'.$in_date1[2]);
		}
		$where['user_id'] = array('eq',$uid);
		$where['create_time'] = array('between',array($start,$end));
		$where['status'] = array('in','0,1');
		$half_day = M('FlowYear')->where($where)->sum('half_day');
		
		if($position_name=='助理' || $position_name=='员工' || $position_name=='主管'){
			if($year<3){
				return 5*2+$half_day;
			}elseif ($year<10){
				return 7*2+$half_day;
			}else{
				return 10*2+$half_day;
			}
			
		}elseif($position_name=='经理'){
			if($year<3){
				return 7*2+$half_day;
			}elseif ($year<10){
				return 7*2+$half_day;
			}else{
				return 10*2+$half_day;
			}
			
		}elseif($position_name=='总监' || $position_name=='副总' || $position_name=='总经理'){
			return 10*2+$half_day;
		}
	}else{
		return 0;
	}
}
function change_goods($goods_id,$num,$mark,$user_id,$create_time,$sum_egt_0 = true){
	if($goods_id){
		$data = array();
		$last = M('GoodsChange')->field('id,sum')->where(array('goods_id'=>$goods_id))->order('create_time desc,id desc')->find();
		$last_sum = $last?$last['sum']:0;
		$data['sum'] = $last_sum+$num;
		if($data['sum']<0 && $num<0){
// 			$goods_name = M('Goods')->field('goods_name')->find($goods_id);
// 			$str = '商品id:'.$goods_id.' 商品名称:'.$goods_name['goods_name'].' 缺货:'.$data['sum'].' 请及时补货';
// 			$open=fopen("C:\log.txt","a" );
// 			fwrite($open,$str."\r\n");
// 			fclose($open);
			if($sum_egt_0){
				return false;
			}
		}
		$data['goods_id'] = $goods_id;
		$data['num'] = $num;
		$data['mark'] = $mark;
		$data['user_id'] = $user_id;
		$data['create_time'] = $create_time;
		
		$list = M('GoodsChange') -> add($data);
		return $list;
	}
}
function getConfirmByCode($code){
	switch ($code){
		case 'getParentid':
			return '上一级';
		case 'getDeptManagerId':
			return '部门总监';
		case 'getHRDeputyGeneralManagerId':
			return '人事行政';
		case 'getZhaopinDirector':
			return '招聘主管';
		case 'getGeneralManagerId':
			return '总经理';
	}
}
function getPositionNameByRank($rank){
	switch ($rank){
		case '1':
			return '副总，总监';
		case '2':
			return '经理';
		case '3':
			return '助理，员工，主管';
	}
}
function getConfirmText($confirm_position_arr=array(),$flow_type_id=null){
	$arr = getConfirmTextArr($confirm_position_arr,$flow_type_id);
	$text = ' ';
	foreach ($arr as $k=>$v){
		$text.=$v;
	}
	return $text;
}
function getConfirmTextArr($confirm_position_arr=array(),$flow_type_id=null){
	$text = array();
	foreach ($confirm_position_arr as $k=>$v){
		$text_part = M('FlowConfirmSetting')->field('text')->where(array('flow_type'=>$flow_type_id,'confirm_position_name'=>array('eq',$v)))->find();
		if($text_part){
			$l = $k+1;
			for($i=0;$i<8;$i++){
				$j=$i+1;
				$text_part = str_replace('$flow_index.'.$j.' neq '.$j,'$flow_index.'.$l.' neq '.$l,$text_part);
				$text_part = str_replace('$flow_log_all['.$i.']','$flow_log_all['.$k.']',$text_part);
			}
			$text[] = $text_part['text'];
		}
	}
	return $text;
}
function getConfirmTextArrNotMe($confirm_position_arr=array(),$flow_type_id=null,$uid=null){
	$uid = $uid?$uid:get_user_id();
	$text = array();
	foreach ($confirm_position_arr as $k=>$v){
		if($uid==$v($uid)){
			continue;
		}
		$text_part = M('FlowConfirmSetting')->field('text')->where(array('flow_type'=>$flow_type_id,'confirm_position_name'=>array('eq',$v)))->find();
		$l = $k+1;
		for($i=0;$i<8;$i++){
			$j=$i+1;
			$text_part = str_replace('$flow_index.'.$j.' neq '.$j,'$flow_index.'.$l.' neq '.$l,$text_part);
			$text_part = str_replace('$flow_log_all['.$i.']','$flow_log_all['.$k.']',$text_part);
		}
		$text[] = $text_part['text'];
	}
	return $text;
}
function getConfirmTextNotMe($confirm_position_arr=array(),$flow_type_id=null,$uid=null){
	$arr = getConfirmTextArrNotMe($confirm_position_arr,$flow_type_id,$uid);
	$text = ' ';
	foreach ($arr as $k=>$v){
		$text.=$v;
	}
	return $text;
}
function array_sum_except($exc,$array){
	$sum = 0;
	foreach ($array as $k=>$v){
		if(!strstr($k, $exc)){
			$sum += $v;
		}
	}
	return $sum;
}
function get_room_list($name){
	return  M("room_config")->where("id=$name")->getField("name");
}
function conv_inputbox($name, $data) {
	$html = "<span data=$data id=$data>";
	$html .= "<nobr><b  title=$name>$name</b>";
	$html .= "<a class=\"del\" title=\"删除\"><i class=\"fa fa-times\"></i></a></nobr></span>";
	return $html;
}
function toPlan($plan){
	if(!empty($plan)){
		$p = explode('|',$plan);
		if($p[1]){return '已完成';}
		return '<select name="plan_sec"><option value="0">进行中</option><option value="1">已完成</option></select>';
	}
}
function shifzc($list){
	if(!empty($list)){
		$tmp = explode('|',$list);
		foreach ($tmp as $k=>$v){
			$arr[] = ($v == '0') ? "旧版" : "新版";
		}
		return implode('，',$arr);
	}
	return '';
}
function HzYuanQuFlowOrigin($uid){
	$user = D('UserView')->find($uid);
	$p1 = getParentid($user['id']);
	
// 	获取本园区负责人和园区负责人（刘清）
	$dept_id = isHeadquarters($uid);
	$YuanQuBoss = M('User')->where(array('pos_id'=>$dept_id))->find();
	$YuanQuBossBoss_id = getParentid($YuanQuBoss['id']);
	
// 	如果一级审批人为本园区负责人，且发起人不是副总级别的，就不让往上走
//  如果一级审批人为园区负责人（刘清），就不让往上走
	if($p1==$YuanQuBoss['id'] && $user['position_name']!='副总' || $p1==$YuanQuBossBoss_id){
		return array($p1);
	}else{
		$p2 = getParentid($p1);
		return array_unique(array($p1,$p2));
	}
}
function HzYuanQuFlow($uid,$day){
	$flow_origin = HzYuanQuFlowOrigin($uid);
	if($day<=0){
		return array();
	}
	if($day<3){
		return array($flow_origin[0]);
	}else{
		return $flow_origin;
	}
}
function isHzYuanQu($uid){
	$dept = M('Dept')->find(isHeadquarters($uid));
	return strpos($dept['name'],'杭州')!==false;
}
function get_add_type_in_over_time($flow_id,$opt){
	$over_time = M('FlowOverTime')->where(array('flow_id'=>$flow_id))->field('use_type')->find();
	
	if($over_time['use_type']=='调休'){
		$data = array('name'=>$over_time['use_type'],'color'=>'green');
	}elseif($over_time['use_type']=='申请加班费'){
		$data = array('name'=>$over_time['use_type'],'color'=>'red');
	}
	return $data[$opt];
}
/*
 * 获取一级部门
 */
function get_first_dept(){
	$res = isHeadquarters(get_user_id());
	if($res>0){
		$yuanqu = M('Dept')->find($res);
		return $yuanqu['name'];
	}elseif ($res==0){
		return get_dept_name();
	}
}
/*
 * chr()的强化版
 * 例如：chrr(91)='AA'
 */
function chrr($num){
	return ToNumberSystem26($num-64);
}
function tree_to_html($tree){
	$html = '';
	
	if(!empty($tree) && is_array($tree)){
		foreach ($tree as $k0=>$v0){
			foreach ($v0['_child'] as $k1=>$v1){
				foreach ($v1['_child'] as $k2=>$v2){
					//$html.='<ul id=\''.$v2['id'].'\' name=\''.$v2['name'].'\'>';
					$html.='<li class="bottom_x_li" style="width:108px">';
					$html.='<img class="bottom_yj_img" src="./Public/img/img/zx_x.png"/>';
					$html.='<a class="bottom_yj_a" id="'.$v2['id'].'">'.$v2['name'].'</a>';
					$html.='<ul class="bottom_x_ej" style="margin-left:0px;">';
					$le = count($v2['_child']);
					foreach ($v2['_child'] as $k3=>$v3){
						$html.='<li id="'.$v3['id'].'">';
						if($k3<$le-1){
							$html.='<img class="bottom_ej_img" src="./Public/img/img/wx.png"/>';
							$html.='<a class="bottom_ej_a">'.$v3['name'].'</a>';
						}else{
							$html.='<img class="bottom_ej_img" src="./Public/img/img/wx_s.png"/>';
							$html.='<a class="bottom_ej_a2">'.$v3['name'].'</a>';
						}
						$html.='</li>';
// 						$html.='<li id=\''.$v3['id'].'\' name=\''.$v3['name'].'\'>';
					}	
					$html.='</ul>';
					$html.='</li>';
				}	
			}
		}
	}
	return $html;
}
function user_status($is_del){
	return $is_del=='1'?'禁用':'启用';
}
function is_use($is_use){
	return $is_use=='1'?'启用':'禁用';
}
function formatto4w($num){
	if($num>=1000){
		return substr($num,-4);
	}
	if($num>=100){
		return '0'.substr($num,-4);
	}
	if($num>=10){
		return '00'.substr($num,-4);
	}
	if($num>=1){
		return '000'.substr($num,-4);
	}
}
function showsex($sex){
	if($sex=='male'){
		return '男';
	}else{
		return '女';
	}
}
function getBrowser(){
	$agent=$_SERVER["HTTP_USER_AGENT"];
	if(strpos($agent,'MSIE')!==false || strpos($agent,'rv:11.0')) //ie11判断
		return "ie";
	else if(strpos($agent,'Firefox')!==false)
		return "firefox";
	else if(strpos($agent,'Chrome')!==false)
		return "chrome";
	else if(strpos($agent,'Opera')!==false)
		return 'opera';
	else if((strpos($agent,'Chrome')==false)&&strpos($agent,'Safari')!==false)
		return 'safari';
	else
		return 'unknown';
}

function getBrowserVer(){
	if (empty($_SERVER['HTTP_USER_AGENT'])){    //当浏览器没有发送访问者的信息的时候
		return 'unknow';
	}
	$agent= $_SERVER['HTTP_USER_AGENT'];
	if (preg_match('/MSIE\s(\d+)\..*/i', $agent, $regs))
		return $regs[1];
	elseif (preg_match('/FireFox\/(\d+)\..*/i', $agent, $regs))
	return $regs[1];
	elseif (preg_match('/Opera[\s|\/](\d+)\..*/i', $agent, $regs))
	return $regs[1];
	elseif (preg_match('/Chrome\/(\d+)\..*/i', $agent, $regs))
	return $regs[1];
	elseif ((strpos($agent,'Chrome')==false)&&preg_match('/Safari\/(\d+)\..*$/i', $agent, $regs))
	return $regs[1];
	else
		return 'unknow';
}
function determineplatform () {
	$Agent = $_SERVER['HTTP_USER_AGENT'];
	$browserplatform=='';
	if (eregi('win',$Agent) && strpos($Agent, '95')) {
		$browserplatform="Windows 95";
	}
	elseif (eregi('win 9x',$Agent) && strpos($Agent, '4.90')) {
		$browserplatform="Windows ME";
	}
	elseif (eregi('win',$Agent) && ereg('98',$Agent)) {
		$browserplatform="Windows 98";
	}
	elseif (eregi('win',$Agent) && eregi('nt 5.0',$Agent)) {
		$browserplatform="Windows 2000";
	}
	elseif (eregi('win',$Agent) && eregi('nt 5.1',$Agent)) {
		$browserplatform="Windows XP";
	}
	elseif (eregi('win',$Agent) && eregi('nt 6.0',$Agent)) {
		$browserplatform="Windows Vista";
	}
	elseif (eregi('win',$Agent) && eregi('nt 6.1',$Agent)) {
		$browserplatform="Windows 7";
	}
	elseif (eregi('win',$Agent) && ereg('32',$Agent)) {
		$browserplatform="Windows 32";
	}
	elseif (eregi('win',$Agent) && eregi('nt',$Agent)) {
		$browserplatform="Windows NT";
	}elseif (eregi('Mac OS',$Agent)) {
		$browserplatform="Mac OS";
	}
	elseif (eregi('linux',$Agent)) {
		$browserplatform="Linux";
	}
	elseif (eregi('unix',$Agent)) {
		$browserplatform="Unix";
	}
	elseif (eregi('sun',$Agent) && eregi('os',$Agent)) {
		$browserplatform="SunOS";
	}
	elseif (eregi('ibm',$Agent) && eregi('os',$Agent)) {
		$browserplatform="IBM OS/2";
	}
	elseif (eregi('Mac',$Agent) && eregi('PC',$Agent)) {
		$browserplatform="Macintosh";
	}
	elseif (eregi('PowerPC',$Agent)) {
		$browserplatform="PowerPC";
	}
	elseif (eregi('AIX',$Agent)) {
		$browserplatform="AIX";
	}
	elseif (eregi('HPUX',$Agent)) {
		$browserplatform="HPUX";
	}
	elseif (eregi('NetBSD',$Agent)) {
		$browserplatform="NetBSD";
	}
	elseif (eregi('BSD',$Agent)) {
		$browserplatform="BSD";
	}
	elseif (ereg('OSF1',$Agent)) {
		$browserplatform="OSF1";
	}
	elseif (ereg('IRIX',$Agent)) {
		$browserplatform="IRIX";
	}
	elseif (eregi('FreeBSD',$Agent)) {
		$browserplatform="FreeBSD";
	}
	if ($browserplatform=='') {$browserplatform = "Unknown"; }
	return $browserplatform;
}
function show_mapping($code,$field='data_name'){
	$code = explode('_',$code);
	$data_type = $code[0];
	$data_code = $code[1];
	$res = M('SimpleDataMapping')->field($field)->where(array('data_type'=>$data_type,'data_code'=>$data_code,'is_del'=>0))->find();
	return $res[$field];
}
function show_cc($code,$find='|',$replace="、"){
	$code = array_filter(explode($find,$code));
	foreach ($code as $k=>$v){
		$code[$k] = get_user_info($v, 'name');
	}
	$code = implode($replace,$code);
	return $code;
}
//增加erp问题反馈代办
//type=null表示查看
//type=1表示编辑
function add_problem_feedback($problem_feedback_id,$user_id_array=array(),$type=null){
	if(!empty($problem_feedback_id)){
		if(empty($user_id_array)){
			//找到erp问题反馈管理员
			$role_erp = M('Role')->where(array('name'=>'erp问题反馈管理员'))->find();
			$role_user = M('RoleUser')->field('user_id')->where(array('role_id'=>$role_erp['id']))->select();
			$role_user = rotate($role_user);
			$erp_user_id = $role_user['user_id'];
		}else{
			$erp_user_id = $user_id_array;
		}
		foreach ($erp_user_id as $k=>$v){
			$data['user_id'] = $v;
			$data['problem_feedback_id'] = $problem_feedback_id;
			$data['type'] = $type;
			M('ProblemFeedbackRemind')->add($data);
		}
	}
}
//删除erp问题反馈代办
function del_problem_feedback($problem_feedback_id,$user_id_array=array()){
	if(!empty($problem_feedback_id)){
		if(empty($user_id_array)){
			//找到erp问题反馈管理员
			$role_erp = M('Role')->where(array('name'=>'erp问题反馈管理员'))->find();
			$role_user = M('RoleUser')->field('user_id')->where(array('role_id'=>$role_erp['id']))->select();
			$role_user = rotate($role_user);
			$erp_user_id = $role_user['user_id'];	
		}else{
			$erp_user_id = $user_id_array;
		}
		$data['user_id'] = array('in',$erp_user_id);
		$data['problem_feedback_id'] = $problem_feedback_id;
		M('ProblemFeedbackRemind')->where($data)->delete();
	}
}
function show_status_color($name){
	switch ($name){
		case '已退回':return 'orange';
		case '待处理':return 'red';
		case '处理中':return 'blue';
		case '已处理':return 'green';
		case '无需处理':return 'gray';
		default:return 'black';
	}
}
function test($a='a',$b='b',$c='c'){
	return $a.$b.$c;
}
function getRootDept($dept_id){
	while($dept_id){
		$id = $dept_id;
		$dept_id = M('Dept')->where(array('id'=>$dept_id))->getField('pid');
	}
	$dept = M('Dept')->where(array('id'=>$id))->find();
	return $dept;
}
function getDefaultRoleIdsByUserId($user_id){
	$position_ids = M('RUserPosition')->where(array('user_id'=>$user_id))->getField('position_id',true);
	$default_role_ids = M('RPositionRole')->where(array('position_id'=>array('in',$position_ids)))->getField('role_id',true);
	return $default_role_ids;
}
function getRoleIdsByUserId($user_id){
	$default_role_ids = getDefaultRoleIdsByUserId($user_id);
	$upids = M('RUserPosition')->where(array('user_id'=>$user_id))->getField('id',true);
	$role_ids = M('RUserPositionRole')->where(array('upid'=>array('in',$upids)))->getField('role_id',true);
	foreach ($role_ids as $k=>$v){
		if(!in_array($v, $default_role_ids)){
			$default_role_ids[] = $v;
		}
	}
	return $default_role_ids;
}
function getDefaultRoleIdsByUpid($upid){
	$position_id = M('RUserPosition')->where(array('id'=>$upid))->getField('position_id');
	$default_role_ids = M('RPositionRole')->where(array('position_id'=>array('eq',$position_id)))->getField('role_id',true);
	return $default_role_ids;
}
function getRoleIdsByUpid($upid){
	$default_role_ids = getDefaultRoleIdsByUpid($upid);
// 	$upids = M('RUserPosition')->where(array('user_id'=>$user_id))->getField('id',true);
	$role_ids = M('RUserPositionRole')->where(array('upid'=>array('eq',$upid)))->getField('role_id',true);
	foreach ($role_ids as $k=>$v){
		if(!in_array($v, $default_role_ids)){
			$default_role_ids[] = $v;
		}
	}
	return $default_role_ids;
}
function showPriName($id){
	return M('MenuNew')->where(array('id'=>$id))->getField('menu_name');
}
function showBusiness($rid,$mid){
	$info = M('RRoleMenu') -> where(array('menu_id'=>$mid,'role_id'=>$rid))->getField('scope');
	return $info ? $info : "1";
}
function showBusinessSave($rid,$mid){
	return $info = M('RRoleMenu') -> where(array('menu_id'=>$mid,'role_id'=>$rid))->getField('id');
}
?>
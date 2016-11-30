<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class FlowAction extends CommonAction {
	protected $config = array('app_type' => 'flow', 'action_auth' => array('folder' => 'read','cancel'=>'read', 'mark' => 'read', 'report' => 'read','ajaxgetflow' =>'read','ajaxgettime' =>'read','editflow' =>'read','export_office_supplies_application'=>'read','import_office_supplies_application'=>'read','export_goods_procurement_allocation'=>'read','import_goods_procurement_allocation'=>'read','del'=>'write','winpop_goods'=>'read','getlist'=>'read','get_dept_child'=>'read','export_excel'=>'read'));

	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['keyword'])) {
			$keyword = $_POST['keyword'];
			$map['name'] = array('like', "%" . $keyword . "%");
		}
	}

	function index() {
		$model = D("Flow");
		$model = D('FlowTypeView');
		$where['is_del'] = 0;
		$user_id = get_user_id();
		$role_list = D("Role") -> get_role_list($user_id);
		$role_list = rotate($role_list);
		$role_list = $role_list['role_id'];

		$duty_list = D("Role") -> get_duty_list($role_list);
		$duty_list = rotate($duty_list);
		$duty_list = $duty_list['duty_id'];

		$where['request_duty'] = array('in', $duty_list);

		if(is_mobile_request()){
			$where['id'] = array('in',array(39,63,57,60,46,47));//手机端提供有限几个流程
		}
		$list = $model -> where($where) -> order('sort') -> select();
		$this -> assign("list", $list);
		$this -> _assign_tag_list();
		$this -> display();
	}

	function _flow_auth_filter($folder, &$map) {
		$emp_no = get_emp_no();
		$user_id = get_user_id();
		switch ($folder) {
			case 'confirm' :
				$this -> assign("folder_name", '待办');
				$FlowLog = M("FlowLog");
				$where['emp_no'] = $emp_no;
				$where['_string'] = "result is null";
				$log_list = $FlowLog -> where($where) -> field('flow_id') -> select();
				$log_list = rotate($log_list);
				if (!empty($log_list)) {
					$map['id'] = array('in', $log_list['flow_id']);
				} else {
					$map['_string'] = '1=2';
				}
				break;

			case 'darft' :
				$this -> assign("folder_name", '草稿');
				$map['user_id'] = $user_id;
				$map['step'] = 10;
				break;

			case 'submit' :
				$this -> assign("folder_name", '提交');
				$auth = $this -> config['auth'];
				if (!$auth['admin']) {//不是管理员的话就要自己才可以看
					$map['user_id'] = array('eq', $user_id);
				}
				$map['step'] = array( array('gt', 10), array('eq', 0), 'or');
				break;
			case 'group' :
				$this -> assign("folder_name", '详情');
				$map['user_id'] = array('eq', $_REQUEST['user_id']);
				$map['step'] = array( array('eq', 40));
				$map['type'] = array( array('eq', $_REQUEST['type']));
				break;
			
			case 'finish' :
				$this -> assign("folder_name", '办理');
				$FlowLog = M("FlowLog");
				$where['emp_no'] = $emp_no;
				$where['_string'] = "result is not null";
				$log_list = $FlowLog -> where($where) -> field('flow_id') -> select();
				$log_list = rotate($log_list);
				if (!empty($log_list)) {
					$map['id'] = array('in', $log_list['flow_id']);
				} else {
					$map['_string'] = '1=2';
				}
				break;

			case 'receive' :
				$this -> assign("folder_name", '收到');
				$FlowLog = M("FlowLog");
				$where['emp_no'] = $emp_no;
				$where['step'] = 100;
				$log_list = $FlowLog -> where($where) -> field('flow_id') -> select();
				$log_list = rotate($log_list);
				if (!empty($log_list)) {
					$map['id'] = array('in', $log_list['flow_id']);
				} else {
					$map['_string'] = '1=2';
				}
				break;
			case 'report' :
				$this -> assign("folder_name", '统计报告');
				$role_list = D("Role") -> get_role_list($user_id);
				$role_list = rotate($role_list);
				$role_list = $role_list['role_id'];

				$duty_list = D("Role") -> get_duty_list($role_list);
				$duty_list = rotate($duty_list);
				$duty_list = $duty_list['duty_id'];

				if (!empty($duty_list)) {
					$map['report_duty'] = array('in', $duty_list);
					$map['step'] = array('gt', 10);
				} else {
					$this -> error("没有权限");
				}
				break;
			case 'hr':
				$type = $_GET['type'];
				$name = $_GET['name'];
				$menu = D("Node") -> access_list();
				if(!empty($_GET['id'])){
					$allow = false;
					foreach ($menu as $v){
						$pu = parse_url($v['url']);
						$arr_query = convertUrlQuery($pu['query']);
						if($arr_query['type']=='common'){
							$res = M('FlowCommon')->where(array('flow_id'=>array('eq',$_GET['id'])))->select();
							if(!empty($res)){
								$allow = true;
								break;
							}
						}
						elseif(!empty($arr_query['name'])){
							$res = M('Flow'.convertUnderline1($arr_query['name']))->where(array('flow_id'=>array('eq',$_GET['id'])))->select();
							if(!empty($res)){
								$allow = true;
								break;
							}
						}
					}
					if(!$allow){
						$this -> error("没有权限");
					}
				}
				elseif(!empty($type)){
					$allow = false;
					//通过审核的
					$map['step'] = 40;
					//$map加上自己园区的
					if(isHeadquarters(get_user_id())==0){//总部
						//云客服部考勤单独做
						$pos_id = get_user_info(get_user_id(), 'pos_id');
						$pos = M('Dept')->find($pos_id);
						if($pos['name']=='云客服前台'){
							$map['dept_id'] = array('in',get_child_dept_all($pos['pid']));
						}else{
							$map['dept_id'] = array('in',get_child_dept_all(27));
						}
						
					}elseif (isHeadquarters(get_user_id())>0){//园区
						//园区加上总部下面的分公司财务
						
						$dept_o_a = array();
						$dept = D('Dept')->find(isHeadquarters(get_user_id()));
						if($dept['name'] == '杭州园区'){
							$dept_o = D('Dept')->field('id')->where(array('name'=>'杭州园区财务'))->find();
							if($dept_o){
								$dept_o_a = get_child_dept_all($dept_o['id']);
							}
						}elseif($dept['name'] == '金华园区'){
							$dept_o = D('Dept')->field('id')->where(array('name'=>'金华园区财务'))->find();
							if($dept_o){
								$dept_o_a = get_child_dept_all($dept_o['id']);
							}
						}elseif($dept['name'] == '宁波园区'){
							$dept_o = D('Dept')->field('id')->where(array('name'=>'宁波园区财务'))->find();
							if($dept_o){
								$dept_o_a = get_child_dept_all($dept_o['id']);
							}
						}
						$map['dept_id'] = array('in',array_merge(get_child_dept_all(isHeadquarters(get_user_id())),$dept_o_a));
						
					}elseif (isHeadquarters(get_user_id())==-1){//副总
						$map['dept_id'] = array('in',get_child_dept_all(86));
					}elseif (isHeadquarters(get_user_id())==-2){//总经理
						$map['dept_id'] = array('in',get_child_dept_all(27));
					}
					if($type=='common'){
						foreach ($menu as $v){
							$pu = parse_url($v['url']);
							$arr_query = convertUrlQuery($pu['query']);
							if($arr_query['type']=='common'){
								$flow_id = M('FlowCommon')->field('flow_id')->select();
								$rt = rotate($flow_id);
								$map['id'] = array('in',$rt['flow_id']);
								$allow = true;
								break;
							}
						}
						if(!$allow){
							$this -> error("没有权限");
						}
					}else{
						foreach ($menu as $v){
							$pu = parse_url($v['url']);
							$arr_query = convertUrlQuery($pu['query']);
							if($arr_query['name']==$name){
								$flow_id = M('Flow'.convertUnderline1($name))->field('flow_id')->select();
								$rt = rotate($flow_id);
								$map['id'] = array('in',$rt['flow_id']);
								$allow = true;
								break;
							}
						}
						if(!$allow){
							$this -> error("没有权限");
						}
					}
				}
				break;
		}
	}

	function folder() {

		$widget['date'] = true;
		$this -> assign("widget", $widget);

		$emp_no = get_emp_no();
		$user_id = get_user_id();

		$flow_type_where['is_del'] = array('eq', 0);

		$flow_type_list = M("FlowType") -> where($flow_type_where) -> getField("id,name");
		$this -> assign("flow_type_list", $flow_type_list);

		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$folder = $_REQUEST['fid'];

		$this -> assign("folder", $folder);
		$this -> assign("fid", $_REQUEST['fid']);
		$this -> assign("type", $_REQUEST['type']);
		$this -> assign("name", $_REQUEST['name']);
		$this -> assign("hui", $_REQUEST['hui']);
		
		if (empty($folder)) {
			$this -> error("系统错误");
		}

		$this -> _flow_auth_filter($folder, $map);
		$model = D("FlowView");

		if ($_REQUEST['mode'] == 'export') {
			if($_REQUEST['hui'] == '1'){
				if($_REQUEST['name'] == 'outside'){//外勤算24小时一天，请假、出勤、加班算8小时一天
					$rate = 24;
				}else{
					$rate = 8;
				}
				foreach ($map as $k=>$v){
					$map_['smeoa_flow.'.$k] = $v;
				}
				
				$data = M('Flow')->join('smeoa_flow_'.$_REQUEST['name'].' on smeoa_flow.id = smeoa_flow_'.$_REQUEST['name'].'.flow_id')->field('smeoa_flow.name,smeoa_flow.type,smeoa_flow.user_id,smeoa_flow.emp_no,smeoa_flow.user_name,smeoa_flow.dept_id,smeoa_flow.dept_name,sum(smeoa_flow_'.$_REQUEST['name'].'.day_num*'.$rate.'+smeoa_flow_'.$_REQUEST['name'].'.hour_num) as hour_sum')->where($map_)->group('smeoa_flow.user_id')->select();
			}else{
				if(empty($_REQUEST['date'])){
					$this -> error("请选择导出的日期！");
				}
				$map['_query'] = 'FROM_UNIXTIME(create_time,"%Y-%m")='.$_REQUEST['date'];
				$flow_id = M('Flow')->field('id')->where($map)->select();
				$flow_id_array = array();
				foreach ($flow_id as $v){
					$flow_id_array[] = $v['id'];
				}
				$data = M('Flow'.convertUnderline1($_REQUEST['name']))->where(array('flow_id'=>array('in',$flow_id_array)))->select();
				foreach ($data as $k=>$v){//
					unset($data[$k]['id']);
					unset($data[$k]['flow_id']);
					$flow = M('Flow')->find($v['flow_id']);
					array_unshift($data[$k],$flow['user_name']);
					// 				$data[$k]['user_name'] = $flow['user_name'];
				}
			}
			
			if(in_array($_REQUEST['name'],array('goods_procurement_allocation','office_supplies_application','office_use_application'))){
				$a = array();
				foreach ($data as $k=>$v){
					if($_REQUEST['name']=='office_use_application'){
						$ids = $v['ids'];
						$names = $v['names'];
						$types = $v['types'];
						$nums = $v['nums'];
						$marks = $v['marks'];
						
						if(!empty($ids)){$ids_a = explode('|',$ids);}
						if(!empty($names)){$names_a = explode('|',$names);}
						if(!empty($types)){$types_a = explode('|',$types);}
						if(!empty($nums)){$nums_a = explode('|',$nums);}
						if(!empty($marks)){$marks_a = explode('|',$marks);}
						
						foreach ($types_a as $kk=>$vv){
							if($vv!=''){
								$b = array();
// 								$b['id'] = $v['id'];
// 								$b['flow_id'] = $v['flow_id'];
								$b['user_name'] = $flow['user_name'];
								$b['ids'] = $ids_a[$kk];
								$b['names'] = $names_a[$kk];
								$b['types'] = $types_a[$kk];
								$b['nums'] = $nums_a[$kk];
								$b['marks'] = $marks_a[$kk];
								$a[] = $b;
							}
						}
					}else if($_REQUEST['name']=='goods_procurement_allocation'){
						$goods_name = $v['goods_name'];
						$types = $v['types'];
						$usage = $v['usage'];
						$use_dept = $v['use_dept'];
						$buy_num = $v['buy_num'];
						$add_num = $v['add_num'];
						$recovery_num = $v['recovery_num'];
						$is_allocation = $v['is_allocation'];
						$price = $v['price'];
						$amount = $v['amount'];
						$add_num_calculation = $v['add_num_calculation'];
						$pay_type = $v['pay_type'];
						$in_place_time = $v['in_place_time'];
						
						if(!empty($goods_name)){$goods_name_a = explode('|',$goods_name);}
						if(!empty($types)){$types_a = explode('|',$types);}
						if(!empty($usage)){$usage_a = explode('|',$usage);}
						if(!empty($use_dept)){$use_dept_a = explode('|',$use_dept);}
						if(!empty($buy_num)){$buy_num_a = explode('|',$buy_num);}
						if(!empty($add_num)){$add_num_a = explode('|',$add_num);}
						if(!empty($recovery_num)){$recovery_num_a = explode('|',$recovery_num);}
						if(!empty($is_allocation)){$is_allocation_a = explode('|',$is_allocation);}
						if(!empty($price)){$price_a = explode('|',$price);}
						if(!empty($amount)){$amount_a = explode('|',$amount);}
						if(!empty($add_num_calculation)){$add_num_calculation_a = explode('|',$add_num_calculation);}
						if(!empty($pay_type)){$pay_type_a = explode('|',$pay_type);}
						if(!empty($in_place_time)){$in_place_time_a = explode('|',$in_place_time);}
						
						foreach ($types_a as $kk=>$vv){
							if($vv!=''){
								$b = array();
// 								$b['id'] = $v['id'];
// 								$b['flow_id'] = $v['flow_id'];
								$b['user_name'] = $flow['user_name'];
								$b['apply_time'] = $v['apply_time'];
								$b['goods_name'] = $goods_name_a[$kk];
								$b['types'] = $types_a[$kk];
								$b['usage'] = $usage_a[$kk];
								$b['use_dept'] = $use_dept_a[$kk];
								$b['buy_num'] = $buy_num_a[$kk];
								$b['add_num'] = $add_num_a[$kk];
								$b['recovery_num'] = $recovery_num_a[$kk];
								$b['is_allocation'] = $is_allocation_a[$kk];
								$b['price'] = $price_a[$kk];
								$b['amount'] = $amount_a[$kk];
								$b['add_num_calculation'] = $add_num_calculation_a[$kk];
								$b['pay_type'] = $pay_type_a[$kk];
								$b['in_place_time'] = $in_place_time_a[$kk];
								$a[] = $b;
							}
						}
					}else if($_REQUEST['name']=='office_supplies_application'){
						$ids = $v['ids'];
						$names = $v['names'];
						$types = $v['types'];
						$nums = $v['nums'];
						$prices = $v['prices'];
						$amounts = $v['amounts'];
						$marks = $v['marks'];
						
						if(!empty($ids)){$ids_a = explode('|',$ids);}
						if(!empty($names)){$names_a = explode('|',$names);}
						if(!empty($types)){$types_a = explode('|',$types);}
						if(!empty($nums)){$nums_a = explode('|',$nums);}
						if(!empty($prices)){$prices_a = explode('|',$prices);}
						if(!empty($amounts)){$amounts_a = explode('|',$amounts);}
						if(!empty($marks)){$marks_a = explode('|',$marks);}
						
						foreach ($types_a as $kk=>$vv){
							if($vv!=''){
								$b = array();
// 								$b['id'] = $v['id'];
// 								$b['flow_id'] = $v['flow_id'];
								$b['user_name'] = $flow['user_name'];
								$b['ids'] = $ids_a[$kk];
								$b['names'] = $names_a[$kk];
								$b['types'] = $types_a[$kk];
								$b['nums'] = $nums_a[$kk];
								$b['prices'] = $prices_a[$kk];
								$b['amounts'] = $amounts_a[$kk];
								$b['marks'] = $marks_a[$kk];
								$a[] = $b;
							}
						}
					}
					
				}
				$data = $a;
// 				$this -> _folder_export_detail($data,'smeoa_flow_'.$_REQUEST['name'],$_REQUEST['name']);
			}
// 			foreach ($data as $k=>$v){//最后一列加上用户
// 				$flow = M('Flow')->find($v['flow_id']);
// 				$data[$k]['user_name'] = $flow['user_name'];
// 			}
			
			$this -> _folder_export_detail($data,'smeoa_flow_'.$_REQUEST['name'],$_REQUEST['name'],$_REQUEST['date'],$_REQUEST['hui']);
			
// 			$this -> _folder_export($model, $map);
		} else {
			if($_REQUEST['hui'] == '1' && ($_REQUEST['name'] == 'leave' || $_REQUEST['name'] =='over_time' || $_REQUEST['name'] =='outside' || $_REQUEST['name'] =='attendance')){
					
				$order = $model -> getPk();
				$sort = 'desc';
				
				//取得满足条件的记录数
				
				foreach ($map as $k=>$v){
					$map_['smeoa_flow.'.$k] = $v;
				}
				
				if($_REQUEST['name'] == 'outside'){//外勤算24小时一天，请假、出勤、加班算8小时一天
					$rate = 24;
				}else{
					$rate = 8;
				}
				$count = M('Flow')->join('smeoa_flow_'.$_REQUEST['name'].' on smeoa_flow.id = smeoa_flow_'.$_REQUEST['name'].'.flow_id')->field('smeoa_flow.name,smeoa_flow.type,smeoa_flow.user_id,smeoa_flow.emp_no,smeoa_flow.user_name,smeoa_flow.dept_id,smeoa_flow.dept_name,sum(smeoa_flow_'.$_REQUEST['name'].'.day_num*'.$rate.'+smeoa_flow_'.$_REQUEST['name'].'.hour_num) as hour_sum')->where($map_)->group('smeoa_flow.user_id')->count();

				if ($count > 0) {
					import("@.ORG.Util.Page");
					//创建分页对象
					if (!empty($_REQUEST['list_rows'])) {
						$listRows = $_REQUEST['list_rows'];
					} else {
						$listRows = get_user_config('list_rows');
					}
					$p = new Page($count, $listRows);
					//分页查询数据
						
					$flow_list = M('Flow')->join('smeoa_flow_'.$_REQUEST['name'].' on smeoa_flow.id = smeoa_flow_'.$_REQUEST['name'].'.flow_id')->field('smeoa_flow.name,smeoa_flow.type,smeoa_flow.user_id,smeoa_flow.emp_no,smeoa_flow.user_name,smeoa_flow.dept_id,smeoa_flow.dept_name,sum(smeoa_flow_'.$_REQUEST['name'].'.day_num*'.$rate.'+smeoa_flow_'.$_REQUEST['name'].'.hour_num) as hour_sum')->where($map_)->group('smeoa_flow.user_id') -> limit($p -> firstRow . ',' . $p -> listRows) -> select();
					
					//echo $model->getlastSql();
					$p -> parameter = $this -> _search();
					//分页显示
					$page = $p -> show();
				
					//列表排序显示
					$sortImg = $sort;
				
					//排序图标
					$sortAlt = $sort == 'desc' ? '升序排列' : '倒序排列';
				
					//排序提示
					$sort = $sort == 'desc' ? 1 : 0;
				
					//排序方式
				
					//模板赋值显示
					$name = $this -> getActionName();
					$this -> assign('sort', $sort);
					$this -> assign('order', $order);
					$this -> assign('sortImg', $sortImg);
					$this -> assign('sortType', $sortAlt);
					$this -> assign("page", $page);
				}
			}else{
				$flow_list = $this -> _list($model, $map);
// 				dump($model);
				
				foreach ($flow_list as $k=>$v){
					$auth = M('FlowLog')->where(array('flow_id'=>array('eq',$v['id']),'_string'=>'result is not null'))->select();
					if($auth){
						$flow_list[$k]['auth'] = 1;
					}else{
						$flow_list[$k]['auth'] = 0;
					}
					if(!empty($v['confirm'])){
// 						$confirm = explode('|',$v['confirm']);
// 						$flowLog = M('FlowLog')->where(array('flow_id'=>array('eq',$v['id']),'_string'=>'result is null'))->find();
						$flowLogAll = M('FlowLog')->where(array('flow_id'=>array('eq',$v['id'])))->select();
// 						$i = false;
// 						if(!empty($flowLog)){
// 							$i = array_search($flowLog['emp_no'],$confirm);
// 						}
						$confirm_name = array_filter(explode('<>',$v['confirm_name']));
			
// 						$s = '';
						$ss = '';
// 						foreach ($confirm_name as $kk=>$vv){
// 							if($i===$kk){
// 								$s.=$vv.'（审批中）'.'->';
// 							}else{
// 								$s.=$vv.'->';
// 							}
// 						}
						
						foreach ($confirm_name as $kk=>$vv){
							$ss.=$flowLogAll[$kk]?(empty($flowLogAll[$kk]['result'])?$flowLogAll[$kk]['user_name'].'（审批中）':$flowLogAll[$kk]['user_name']).'->':$vv.'->';
						
						}
// 						$s = substr($s,0,strlen($s)-2);
						$ss = substr($ss,0,strlen($ss)-2);
						$flow_list[$k]['flow_name'] = $ss;
					}
					$flow_detail = M(getModelName($v['id']))->where(array('flow_id'=>array('eq',$v['id'])))->find();
					if($flow_detail['hour_num']!==false){
						$flow_list[$k]['hour_num'] = $flow_detail['hour_num'];
					}
					if($flow_detail['day_num']!==false){
						$flow_list[$k]['day_num'] = $flow_detail['day_num'];
					}
				}
			}	
			
		}
		$date_num = M('Flow')->where($map)->field("count(FROM_UNIXTIME(create_time,'%Y-%m')) as count,FROM_UNIXTIME(create_time,'%Y-%m') as date")->group("FROM_UNIXTIME(create_time,'%Y-%m')")->select();
		
		$this -> assign("date_num", $date_num);
		
		$this -> assign("list", $flow_list);
		$this -> display();
	}

	private function _folder_export($model, $map) {
		$list = $model -> where($map) -> select();

		//导入thinkphp第三方类库
		Vendor('Excel.PHPExcel');

		//$inputFileName = "Public/templete/contact.xlsx";
		//$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
		$objPHPExcel = new PHPExcel();

		$objPHPExcel -> getProperties() -> setCreator("小微OA") -> setLastModifiedBy("小微OA") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
		// Add some data
		$i = 1;
		//dump($list);

		//编号，类型，标题，登录时间，部门，登录人，状态，审批，协商，抄送，审批情况，自定义字段
		$objPHPExcel -> setActiveSheetIndex(0) -> setCellValue("A$i", "编号") -> setCellValue("B$i", "类型") -> setCellValue("C$i", "标题") -> setCellValue("D$i", "登录时间") -> setCellValue("E$i", "部门") -> setCellValue("F$i", "登录人") -> setCellValue("G$i", "状态") -> setCellValue("H$i", "审批") -> setCellValue("I$i", "协商") -> setCellValue("J$i", "抄送") -> setCellValue("J$i", "审批情况");
		foreach ($list as $val) {
			$i++;
			//dump($val);
			$id = $val['id'];
			$doc_no = $val["doc_no"];
			//编号
			$name = $val["name"];
			//标题
			$confirm_name = strip_tags($val["confirm_name"]);
			//审批
			$consult_name = strip_tags($val["consult_name"]);
			//协商
			$refer_name = strip_tags($val["refer_name"]);
			//协商
			$type_name = $val["type_name"];
			//流程类型
			$user_name = $val["user_name"];
			//登记人
			$dept_name = $val["dept_name"];
			//不美分
			$create_time = $val["create_time"];
			$create_time = toDate($val["create_time"], 'Y-m-d H:i:s');
			//创建时间
			$step = show_step_type($val["step"]);
			//

			//编号，类型，标题，登录时间，部门，登录人，状态，审批，协商，抄送，审批情况，自定义字段
			$objPHPExcel -> setActiveSheetIndex(0) -> setCellValue("A$i", $doc_no) -> setCellValue("B$i", $type_name) -> setCellValue("C$i", $name) -> setCellValue("D$i", $create_time) -> setCellValue("E$i", $dept_name) -> setCellValue("F$i", $user_name) -> setCellValue("G$i", $step) -> setCellValue("H$i", $confirm_name) -> setCellValue("I$i", $consult_name);

			$model_flow_field = D("FlowField");
			$field_list = $model_flow_field -> get_data_list($id);
			//	dump($field_list);
			$k = 0;
			if (!empty($field_list)) {
				foreach ($field_list as $field) {
					$k++;
					$field_data = $field['name'] . ":" . $field['val'];
					$location = get_cell_location("J", $i, $k);
					$objPHPExcel -> setActiveSheetIndex(0) -> setCellValue($location, $field_data);
				}
			}
		}
		// Rename worksheet
		$objPHPExcel -> getActiveSheet() -> setTitle('流程统计');

		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel -> setActiveSheetIndex(0);
		$file_name = "流程统计.xlsx";
		// Redirect output to a client’s web browser (Excel2007)
		header("Content-Type: application/force-download");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition:attachment;filename =" . str_ireplace('+', '%20', URLEncode($file_name)));
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		//readfile($filename);
		$objWriter -> save('php://output');
		exit ;
	}
	function _folder_export_detail($data,$table_name,$type,$date,$hui){
		if($type=='leave'){//请假调休单特殊处理
			//云客服部考勤单独做
			$pos_id = get_user_info(get_user_id(), 'pos_id');
			$pos = M('Dept')->find($pos_id);
			if($pos['name']=='云客服前台'){
				$dept_id = $pos['pid'];
				$this -> _folder_export_detail_leave($date,$dept_id);
			}else{
				$this -> _folder_export_detail_leave($date);
			}
		}
		if($hui == '1'){
			$comment = array(array('COLUMN_COMMENT'=>'类型'),array('COLUMN_COMMENT'=>'类型id'),array('COLUMN_COMMENT'=>'用户id'),array('COLUMN_COMMENT'=>'登录名'),array('COLUMN_COMMENT'=>'用户名'),array('COLUMN_COMMENT'=>'部门id'),array('COLUMN_COMMENT'=>'部门'),array('COLUMN_COMMENT'=>'小时数'));
		}else{
			$sql = 'SELECT COLUMN_NAME,COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "'.$table_name.'" AND TABLE_SCHEMA = "smeoa"';
			$Model = new Model();
			$comment = $Model->query($sql);
			//第一行移除id，flow_id
			array_shift($comment);
			array_shift($comment);
		}
		
		$list = $data;
		
		//导入thinkphp第三方类库
		Vendor('Excel.PHPExcel');
		
		//$inputFileName = "Public/templete/contact.xlsx";
		//$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
		$objPHPExcel = new PHPExcel();
		
		$objPHPExcel -> getProperties() -> setCreator("小微OA") -> setLastModifiedBy("小微OA") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
		// Add some data
		$i = 1;
		//dump($list);
		
		//编号，类型，标题，登录时间，部门，登录人，状态，审批，协商，抄送，审批情况，自定义字段
		$q = $objPHPExcel -> setActiveSheetIndex(0);
		//第一列为用户
		if($hui != '1'){
			if($type=='office_use_application'){//办公用品领用申请
				$q = $q -> setCellValue("A$i", '用户');
			}elseif ($type=='goods_procurement_allocation'){//物品采购调拨申请
				$q = $q -> setCellValue("A$i", '用户');
			}elseif ($type=='office_supplies_application'){//办公用品采购申请
				$q = $q -> setCellValue("A$i", '用户');
			}else{
				$q = $q -> setCellValue("A$i", '用户');
			}
		}
		
		if($hui != '1'){
			$start = ord('B');
		}else{
			$start = ord('A');
		}
		
// 		$l=0;
		foreach($comment as $k=>$v){
			$q = $q -> setCellValue(chr($start+$k)."$i", $v['COLUMN_COMMENT']);
// 			$q ->getColumnDimension(chr($start+$k))->setAutoSize(true);
// 			$l = $k;
		}
		
// 		$objPHPExcel -> setActiveSheetIndex(0) -> setCellValue("A$i", "编号") -> setCellValue("B$i", "类型") -> setCellValue("C$i", "标题") -> setCellValue("D$i", "登录时间") -> setCellValue("E$i", "部门") -> setCellValue("F$i", "登录人") -> setCellValue("G$i", "状态") -> setCellValue("H$i", "审批") -> setCellValue("I$i", "协商") -> setCellValue("J$i", "抄送") -> setCellValue("J$i", "审批情况");
		foreach ($list as $val) {
			$i++;
			//dump($val);
			$id = $val['id'];
// 			$doc_no = $val["doc_no"];
// 			//编号
// 			$name = $val["name"];
// 			//标题
// 			$confirm_name = strip_tags($val["confirm_name"]);
// 			//审批
// 			$consult_name = strip_tags($val["consult_name"]);
// 			//协商
// 			$refer_name = strip_tags($val["refer_name"]);
// 			//协商
// 			$type_name = $val["type_name"];
// 			//流程类型
// 			$user_name = $val["user_name"];
// 			//登记人
// 			$dept_name = $val["dept_name"];
// 			//不美分
// 			$create_time = $val["create_time"];
// 			$create_time = toDate($val["create_time"], 'Y-m-d H:i:s');
// 			//创建时间
// 			$step = show_step_type($val["step"]);
			//
			$w = $objPHPExcel -> setActiveSheetIndex(0);
			$start2 = ord('A');
			$ii = 0;
			foreach($val as $kk=>$vv){
				$w = $w -> setCellValue(chr($start2+$ii)."$i", $vv);
// 				$w ->getColumnDimension(chr($start2+$ii))->setAutoSize(true);
				$ii++;
			}
			
			//编号，类型，标题，登录时间，部门，登录人，状态，审批，协商，抄送，审批情况，自定义字段
// 			$objPHPExcel -> setActiveSheetIndex(0) -> setCellValue("A$i", $doc_no) -> setCellValue("B$i", $type_name) -> setCellValue("C$i", $name) -> setCellValue("D$i", $create_time) -> setCellValue("E$i", $dept_name) -> setCellValue("F$i", $user_name) -> setCellValue("G$i", $step) -> setCellValue("H$i", $confirm_name) -> setCellValue("I$i", $consult_name);
		
			$model_flow_field = D("FlowField");
			$field_list = $model_flow_field -> get_data_list($id);
			//	dump($field_list);
			$k = 0;
			if (!empty($field_list)) {
				foreach ($field_list as $field) {
					$k++;
					$field_data = $field['name'] . ":" . $field['val'];
					$location = get_cell_location("J", $i, $k);
					$objPHPExcel -> setActiveSheetIndex(0) -> setCellValue($location, $field_data);
				}
			}
		}
		if($hui != '1'){
			$start = ord('B');
		}else{
			$start = ord('A');
		}
		foreach($comment as $k=>$v){
			$q ->getColumnDimension(chr($start+$k))->setWidth(20);
		}
		// Rename worksheet
		$node = M('Node')->where(array('url'=>array('like','%name='.$type)))->find();
		if($node){
			$title = $node['name'].$date;
		}else{
			$title = $date;
		}
		$title = str_replace('/','_',$title);
		$objPHPExcel -> getActiveSheet() -> setTitle($title);
		
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel -> setActiveSheetIndex(0);
		$file_name = $title.".xlsx";
		// Redirect output to a client’s web browser (Excel2007)
		header("Content-Type: application/force-download");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition:attachment;filename =" . str_ireplace('+', '%20', URLEncode($file_name)));
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		//readfile($filename);
		$objWriter -> save('php://output');
		exit ;
	}
	function _folder_export_detail_leave($date,$dept_id=null){
		if(isHeadquarters(get_user_id())==0){//总部
			if(empty($dept_id)){
				$dept = tree_to_list(list_to_tree(M("Dept") ->where('is_del=0')-> select(), 1));
				$dept = rotate($dept);
				$dept = implode(",", $dept['id']) . ",1";
			}else{
				$dept = tree_to_list(list_to_tree(M("Dept") ->where('is_del=0')-> select(), $dept_id));
				$dept = rotate($dept);
				$dept = implode(",", $dept['id']) . ",".$dept_id;
			}
			
			$dept_exc_0 = M("Dept") ->where(array('name'=>array('eq','各分公司财务')))-> find();
			$dept_exc = tree_to_list(list_to_tree(M("Dept") ->where('is_del=0')-> select(), $dept_exc_0['id']));
			$dept_exc = rotate($dept_exc);
			$dept_exc = implode(",", $dept_exc['id']) . ",".$dept_exc_0['id'];
		
			$model = D("UserView");
			$where['is_del'] = array('eq', '0');
			$where['pos_id'] = array('in', $dept);
			$where_pos_id['pos_id'] = array('not in', $dept_exc);
			$where['_complex'] = $where_pos_id;
			
			$where['id'] = array('neq', '1');
			$users = M('User')->field('id,name,duty')->where($where)->select();
			
			$time = strtotime($date.'-01');
			$month = date('m',$time);
			$month_day =  date('t', $time);

			$not_flow_id = M('FlowLog')->field('flow_id')->where('result is null')->select();
			$not_flow_id = rotate($not_flow_id);
			$not_flow_id = $not_flow_id['flow_id'];
			
			$not_flow_id_2 = M('Flow')->where(array('step'=>array('neq',40),'is_del'=>array('eq',1),'_logic'=>'or'))->field('id')->select();
			$not_flow_id_2 = rotate($not_flow_id_2);
			$not_flow_id_2 = $not_flow_id_2['id'];
			
			$not_flow_id = array_merge($not_flow_id,$not_flow_id_2);
			
			//请假单中取出数据并组织
			$where_leave_time['start_time'] = array('like','%'.$date.'%');
			$where_leave_time['end_time'] = array('like','%'.$date.'%');
			$where_leave_time['_logic'] = 'or';
			$where_leave['_complex'] = $where_leave_time;
			$where_leave['flow_id'] = array('not in',$not_flow_id);
			$flow_leave = M('FlowLeave')->where($where_leave)->select();
			$content = array();
			foreach ($users as $k => $v){
				$content[$v['id']*2] = array('A'=>$v['duty'],'B'=>$v['name']);
				$content[$v['id']*2+1] = array('A'=>$v['duty'],'B'=>$v['name']);
			}
			foreach ($flow_leave as $k => $v){
				$user_id = M('Flow')->where(array('step'=>array('eq',40),'is_del'=>array('eq',0)))->field('user_id')->find($v['flow_id']);
				$user_id = $user_id['user_id'];
				if(isHeadquarters($user_id) == 0 && $user_id != 1){//总部且不是管理员
					$where['is_del'] = array('eq', '0');
					$where['pos_id'] = array('in', $dept);
					$where_pos_id['pos_id'] = array('not in', $dept_exc);
					$where['_complex'] = $where_pos_id;
					$where['id'] = array('eq', $user_id);
					$check_user = M('User')->where($where)->select();
					if($check_user){
						$array_time = slice_time(strtotime($v['start_time']),strtotime($v['end_time']));
						foreach ($array_time as $kk => $vv){
							$event = explode('|',$vv);
							if($event[1]=='1'){//上午
								$content[$user_id*2][$event[2]] .= $v['style'].':'.$event[0].' ';
							}elseif($event[1]=='2'){//下午
								$content[$user_id*2+1][$event[2]] .= $v['style'].':'.$event[0].' ';
							}
								
						}
					}
				}
			}
			
			//外勤出差单中取出数据并组织
			$where_outside_time['start_time'] = array('like','%'.$date.'%');
			$where_outside_time['end_time'] = array('like','%'.$date.'%');
			$where_outside_time['_logic'] = 'or';
			$where_outside['_complex'] = $where_outside_time;
			$where_outside['flow_id'] = array('not in',$not_flow_id);
			$flow_outside = M('FlowOutside')->where($where_outside)->select();
		
			foreach ($flow_outside as $k => $v){
				$user_id = M('Flow')->where(array('step'=>array('eq',40),'is_del'=>array('eq',0)))->field('user_id')->find($v['flow_id']);
				$user_id = $user_id['user_id'];
				if(isHeadquarters($user_id) == 0 && $user_id != 1){//总部且不是管理员
					$where['is_del'] = array('eq', '0');
					$where['pos_id'] = array('in', $dept);
					$where_pos_id['pos_id'] = array('not in', $dept_exc);
					$where['_complex'] = $where_pos_id;
					$where['id'] = array('eq', $user_id);
					$check_user = M('User')->where($where)->select();
					$type = $v['outside_type']?$v['outside_type']:'外勤/出差';
					if($check_user){
						$array_time = slice_time(strtotime($v['start_time']),strtotime($v['end_time']));
						foreach ($array_time as $kk => $vv){
							$event = explode('|',$vv);
							if($event[1]=='1'){//上午
								$content[$user_id*2][$event[2]] .= $type.':'.$event[0].' ';
							}elseif($event[1]=='2'){//下午
								$content[$user_id*2+1][$event[2]] .= $type.':'.$event[0].' ';
							}
						
						}
					}
				}
			}
			
			//补勤单中取出数据并组织
			$where_attendance_time['start_time'] = array('like','%'.$date.'%');
			$where_attendance_time['end_time'] = array('like','%'.$date.'%');
			$where_attendance_time['_logic'] = 'or';
			$where_attendance['_complex'] = $where_attendance_time;
			$where_attendance['flow_id'] = array('not in',$not_flow_id);
			$flow_attendance = M('FlowAttendance')->where($where_attendance)->select();
			
			foreach ($flow_attendance as $k => $v){
				$user_id = M('Flow')->where(array('step'=>array('eq',40),'is_del'=>array('eq',0)))->field('user_id')->find($v['flow_id']);
				$user_id = $user_id['user_id'];
				if(isHeadquarters($user_id) == 0 && $user_id != 1){//总部且不是管理员
					$where['is_del'] = array('eq', '0');
					$where['pos_id'] = array('in', $dept);
					$where_pos_id['pos_id'] = array('not in', $dept_exc);
					$where['_complex'] = $where_pos_id;
					$where['id'] = array('eq', $user_id);
					$check_user = M('User')->where($where)->select();
					if($check_user){
						$array_time = slice_time(strtotime($v['start_time']),strtotime($v['end_time']));
						foreach ($array_time as $kk => $vv){
							$event = explode('|',$vv);
							if($event[1]=='1'){//上午
								$content[$user_id*2][$event[2]] .= '补勤'.':'.$event[0].' ';
							}elseif($event[1]=='2'){//下午
								$content[$user_id*2+1][$event[2]] .= '补勤'.':'.$event[0].' ';
							}
								
						}
					}
				}
			}
			
			//加班单中取出数据并组织
			$where_overtime_time['start_time'] = array('like','%'.$date.'%');
			$where_overtime_time['end_time'] = array('like','%'.$date.'%');
			$where_overtime_time['_logic'] = 'or';
			$where_overtime['_complex'] = $where_overtime_time;
			$where_overtime['flow_id'] = array('not in',$not_flow_id);
			$flow_overtime = M('FlowOverTime')->where($where_overtime)->select();
				
			foreach ($flow_overtime as $k => $v){
				$user_id = M('Flow')->where(array('step'=>array('eq',40),'is_del'=>array('eq',0)))->field('user_id')->find($v['flow_id']);
				$user_id = $user_id['user_id'];
				if(isHeadquarters($user_id) == 0 && $user_id != 1){//总部且不是管理员
					$where['is_del'] = array('eq', '0');
					$where['pos_id'] = array('in', $dept);
					$where_pos_id['pos_id'] = array('not in', $dept_exc);
					$where['_complex'] = $where_pos_id;
					$where['id'] = array('eq', $user_id);
					$check_user = M('User')->where($where)->select();
					if($check_user){
						$array_time = slice_time_over_time(strtotime($v['start_time']),strtotime($v['end_time']));
						foreach ($array_time as $kk => $vv){
							$event = explode('|',$vv);
							if($event[1]=='1'){//上午
								$content[$user_id*2][$event[2]] .= '加班'.':'.$event[0].' ';
							}elseif($event[1]=='2'){//下午
								$content[$user_id*2+1][$event[2]] .= '加班'.':'.$event[0].' ';
							}
			
						}
					}
				}
			}
			
			//导入thinkphp第三方类库
			Vendor('Excel.PHPExcel');
			
			$objPHPExcel = new PHPExcel();
			
			$objPHPExcel -> getProperties() -> setCreator("神洲OA") -> setLastModifiedBy("神洲OA") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
			// Add some data
			$i = 1;
			
			//编号，类型，标题，登录时间，部门，登录人，状态，审批，协商，抄送，审批情况，自定义字段
			$q = $objPHPExcel -> setActiveSheetIndex(0);
// 			$month_day;
			
// 			ToNumberSystem26($month_day+3);
			$q = $q -> mergeCells('A1:'.ToNumberSystem26($month_day+2).'1');
// 			$q = $q -> setCellValue("A1", '神洲酷奇');
			
			//使用本地图片
			$img=new PHPExcel_Worksheet_Drawing();
			$img->setPath('Public/img/img/logo_for_excel.png');//写入图片路径
			$img->setHeight(20);//写入图片高度
			$img->setWidth(20);//写入图片宽度
			$img->setOffsetX(1);//写入图片在指定格中的X坐标值
			$img->setOffsetY(1);//写入图片在指定格中的Y坐标值
			$img->setRotation(1);//设置旋转角度
			$img->getShadow()->setVisible(true);//
			$img->getShadow()->setDirection(50);//
			$img->setCoordinates('A1');//设置图片所在表格位置
			$img->setWorksheet($q);//把图片写到当前的表格中
			
			
			$q = $q -> mergeCells('A2:'.ToNumberSystem26($month_day+2).'2');
			$q = $q -> setCellValue("A2", $month.'月员工考勤登记表');
			$q->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			
			$q = $q -> mergeCells('A3:'.ToNumberSystem26($month_day+2).'3');
			$q = $q -> setCellValue("A3", '月度合计     单位：'.$month_day.'天');
			
			$q = $q -> mergeCells('A4:A5');
			$q = $q -> setCellValue("A4", '职务');
			
			$q = $q -> mergeCells('B4:B5');
			$q = $q -> setCellValue("B4", '姓名');
			
			$q = $q -> setCellValue("C4", '日期');
			$q = $q -> setCellValue("C5", '星期');
			$week_day_name = array('日','一','二','三','四','五','六');
			for($i=1;$i<$month_day+1;$i++){
				$q = $q -> setCellValue(ToNumberSystem26($i+3)."4", $i);
				$time = strtotime($date.'-'.$i);
				$week_day = date('w',$time);
				$q = $q -> setCellValue(ToNumberSystem26($i+3)."5", $week_day_name[$week_day]);
				
				if(is_holiday(strtotime($date.'-'.$i)) == '1'){//节假日背景设置灰色
					$q->getStyle(ToNumberSystem26($i+3)."4")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
					$q->getStyle(ToNumberSystem26($i+3)."4")->getFill()->getStartColor()->setARGB('FF808080');
					
					$q->getStyle(ToNumberSystem26($i+3)."5")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
					$q->getStyle(ToNumberSystem26($i+3)."5")->getFill()->getStartColor()->setARGB('FF808080');
				}
			}
			$content = array_values($content);
			foreach ($content as $k => $v){
				if($k%2==0){
					$q = $q -> mergeCells('A'.($k+6).':A'.($k+7));
					$q = $q -> setCellValue("A".($k+6), $v['A']);
					
					$q = $q -> mergeCells('B'.($k+6).':B'.($k+7));
					$q = $q -> setCellValue("B".($k+6), $v['B']);
					
					$q = $q -> setCellValue("C".($k+6), '上午');
				}else{
					$q = $q -> setCellValue("C".($k+6), '下午');
				}
				foreach ($v as $kk => $vv){
					if(is_numeric($kk)){
						$q = $q -> setCellValue(ToNumberSystem26($kk+3).($k+6), $vv);
					}
				}
				
			}
			
			// Rename worksheet
			$title = '考勤统计'.$date;
			$objPHPExcel -> getActiveSheet() -> setTitle('考勤统计'.$date);
			
			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objPHPExcel -> setActiveSheetIndex(0);
			$file_name = $title.".xlsx";
			// Redirect output to a client’s web browser (Excel2007)
			header("Content-Type: application/force-download");
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header("Content-Disposition:attachment;filename =" . str_ireplace('+', '%20', URLEncode($file_name)));
			header('Cache-Control: max-age=0');
			
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			//readfile($filename);
			$objWriter -> save('php://output');
			exit ;
			
		}else{
			echo '暂时不支持园区考勤统计';
		}
		exit ;
	}
	function export_office_supplies_application(){
		//导入thinkphp第三方类库
		Vendor('Excel.PHPExcel');
		
		$objPHPExcel = new PHPExcel();
		
		$objPHPExcel -> getProperties() -> setCreator("小微OA") -> setLastModifiedBy("小微OA") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
		// Add some data
		$i = 1;
		//dump($list);
		
		//编号，类型，标题，登录时间，部门，登录人，状态，审批，协商，抄送，审批情况，自定义字段
		$q = $objPHPExcel -> setActiveSheetIndex(0);
		//第一列为用户
		$q = $q -> setCellValue("A$i", '序号');
		$q = $q -> setCellValue("B$i", '品名');
		$q = $q -> setCellValue("C$i", '规格');
		$q = $q -> setCellValue("D$i", '数量');
		$q = $q -> setCellValue("E$i", '单价');
		$q = $q -> setCellValue("F$i", '金额');
		$q = $q -> setCellValue("G$i", '备注');
		
		// Rename worksheet
		$title = '办公用品采购申请';
		$objPHPExcel -> getActiveSheet() -> setTitle('办公用品采购申请');
		
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel -> setActiveSheetIndex(0);
		$file_name = $title.".xlsx";
		// Redirect output to a client’s web browser (Excel2007)
		header("Content-Type: application/force-download");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition:attachment;filename =" . str_ireplace('+', '%20', URLEncode($file_name)));
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		//readfile($filename);
		$objWriter -> save('php://output');
		exit ;
	}
	function import_office_supplies_application(){
		$save_path = get_save_path();
		$opmode = $_POST["opmode"];
		if ($opmode == "import") {
			import("@.ORG.Util.UploadFile");
			$upload = new UploadFile();
			$upload -> savePath = $save_path;
			$upload -> allowExts = array('xlsx');
			$upload -> saveRule = uniqid;
			$upload -> autoSub = false;
			if (!$upload -> upload()) {
				$this -> error($upload -> getErrorMsg());
			} else {
				//取得成功上传的文件信息
				$uploadList = $upload -> getUploadFileInfo();
				Vendor('Excel.PHPExcel');
				//导入thinkphp第三方类库
		
				$inputFileName = $save_path . $uploadList[0]["savename"];
				$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
				$sheetData = $objPHPExcel -> getActiveSheet() -> toArray(null, true, true, true);
				
				$start = ord('A');
				$column_name = array('序号','品名','规格','数量','单价','金额','备注');
				for($ii=$start;$ii<$start+7;$ii++){
					if($sheetData[1][chr($ii)]!=$column_name[$ii-$start]){
						$this -> error('导入的excel模板不对:'.$sheetData[1][chr($ii)]);
					}
				}
				$model_flow = D("Flow");
				$flow_data = array();
				$flow_data['user_id'] = get_user_id();
				$flow_data['user_name'] = get_user_name();
				$flow_data['doc_no'] = 1;
				$flow_data['name'] = '办公用品采购申请';
				$type = M('FlowType')->where(array('name'=>array('eq','办公用品采购申请')))->find();
				$flow_data['type'] = $type['id'];
				
				$uid = get_user_id();
				$dept_id = get_dept_id();
				$dept_uid = getDeptManagerId($uid,$dept_id);
				$flow = array($dept_uid,getHRDeputyGeneralManagerId($uid),getFinancialManagerId(),getGeneralManagerId($uid));
				$FlowData = getFlowData(array_unique2($flow));
				$flow_data['confirm'] = $FlowData['confirm'];
				$flow_data['confirm_name'] = $FlowData['confirm_name'];
				$flow_data['step'] = 10;
				$flow_data['create_time'] = time();
				$flow_id = $model_flow -> add($flow_data);
				
				$model = M("FlowOfficeSuppliesApplication");
				
				
				$sum = 0;
				$data = array();
				$column = array('ids','names','types','nums','prices','amounts','marks');
				for($i=$start;$i<$start+7;$i++){
					for ($j = 2; $j <= count($sheetData); $j++) {
						$data[$column[$i-$start]] .= $sheetData[$j][chr($i)].'|';
						if($i==$start+5){
							$sum += $sheetData[$j][chr($i)];
						}
					}
				}
				$data['flow_id'] = $flow_id;
				$data['sum'] = $sum;
				$model -> add($data);
				//dump($sheetData);
				if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/" . $inputFileName)) {
					unlink($_SERVER["DOCUMENT_ROOT"] . "/" . $inputFileName);
				}
				$this -> assign('jumpUrl', U("flow/edit",array('id'=>$flow_id,'fid'=>'darft')));
				$this -> success('导入成功！');
			}
		} else {
			$this -> display();
		}
	}
	
	function export_goods_procurement_allocation(){
		//导入thinkphp第三方类库
		Vendor('Excel.PHPExcel');
	
		$objPHPExcel = new PHPExcel();
	
		$objPHPExcel -> getProperties() -> setCreator("小微OA") -> setLastModifiedBy("小微OA") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
		// Add some data
		$i = 1;
		//dump($list);
	
		//编号，类型，标题，登录时间，部门，登录人，状态，审批，协商，抄送，审批情况，自定义字段
		$q = $objPHPExcel -> setActiveSheetIndex(0);
		//第一列为用户
		$q = $q -> setCellValue("A$i", '物品名称');
		$q = $q -> setCellValue("B$i", '型号/规格');
		$q = $q -> setCellValue("C$i", '用途');
		$q = $q -> setCellValue("D$i", '使用岗位');
		$q = $q -> setCellValue("E$i", '本部门已买数量');
		$q = $q -> setCellValue("F$i", '新增数量');
		$q = $q -> setCellValue("G$i", '回收数量');
		$q = $q -> setCellValue("H$i", '询问行政能否调拨');
		$q = $q -> setCellValue("I$i", '单价');
		$q = $q -> setCellValue("J$i", '金额');
		$q = $q -> setCellValue("K$i", '新增数量计算过程');
		$q = $q -> setCellValue("L$i", '支付方式');
		$q = $q -> setCellValue("M$i", '物品到位时间(年月日)');
	
		$start = ord('A');
		foreach($comment as $k=>$v){
			$q ->getColumnDimension(chr($start+$k))->setWidth(20);
		}
		// Rename worksheet
		$title = '物品采购调拨申请';
		$objPHPExcel -> getActiveSheet() -> setTitle('物品采购调拨申请');
	
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel -> setActiveSheetIndex(0);
		$file_name = $title.".xlsx";
		// Redirect output to a client’s web browser (Excel2007)
		header("Content-Type: application/force-download");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition:attachment;filename =" . str_ireplace('+', '%20', URLEncode($file_name)));
		header('Cache-Control: max-age=0');
	
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		//readfile($filename);
		$objWriter -> save('php://output');
		exit ;
	}
	function import_goods_procurement_allocation(){
		$save_path = get_save_path();
		$opmode = $_POST["opmode"];
		if ($opmode == "import") {
			import("@.ORG.Util.UploadFile");
			$upload = new UploadFile();
			$upload -> savePath = $save_path;
			$upload -> allowExts = array('xlsx');
			$upload -> saveRule = uniqid;
			$upload -> autoSub = false;
			if (!$upload -> upload()) {
				$this -> error($upload -> getErrorMsg());
			} else {
				//取得成功上传的文件信息
				$uploadList = $upload -> getUploadFileInfo();
				Vendor('Excel.PHPExcel');
				//导入thinkphp第三方类库
	
				$inputFileName = $save_path . $uploadList[0]["savename"];
				$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
				$sheetData = $objPHPExcel -> getActiveSheet() -> toArray(null, true, true, true);
				
				$start = ord('A');
				$column_name = array('物品名称','型号/规格','用途','使用岗位','本部门已买数量','新增数量','回收数量','询问行政能否调拨','单价','金额','新增数量计算过程','支付方式','物品到位时间(年月日)');
				for($ii=$start;$ii<$start+13;$ii++){
					if($sheetData[1][chr($ii)]!=$column_name[$ii-$start]){
						$this -> error('导入的excel模板不对:'.$sheetData[1][chr($ii)]);
					}
				}
				
				$model_flow = D("Flow");
				
				$flow_data = array();
				$flow_data['user_id'] = get_user_id();
				$flow_data['user_name'] = get_user_name();
				$flow_data['doc_no'] = 1;
				$flow_data['name'] = '物品采购调拨申请';
				$type = M('FlowType')->where(array('name'=>array('eq','物品采购调拨申请')))->find();
				$flow_data['type'] = $type['id'];
	
				$uid = get_user_id();
				$dept_id = get_dept_id();
				$dept_uid = getDeptManagerId($uid,$dept_id);
				$flow = array($dept_uid,getHRDeputyGeneralManagerId($uid),getFinancialManagerId(),getGeneralManagerId($uid));
				$FlowData = getFlowData(array_unique2($flow));
				$flow_data['confirm'] = $FlowData['confirm'];
				$flow_data['confirm_name'] = $FlowData['confirm_name'];
				$flow_data['step'] = 10;
				$flow_data['create_time'] = time();
				
				$flow_id = $model_flow -> add($flow_data);
	
				$model = M("FlowGoodsProcurementAllocation");
	
				
				$sum = 0;
				$data = array();
				$column = array('goods_name','types','usage','use_dept','buy_num','add_num','recovery_num','is_allocation','price','amount','add_num_calculation','pay_type','in_place_time');
				for($i=$start;$i<$start+13;$i++){
					for ($j = 2; $j <= count($sheetData); $j++) {
						if($i==$start+12){
							$t = explode('-',$sheetData[$j][chr($i)]);
							$time = '20'.$t[2].'-'.$t[0].'-'.$t[1];
							$data[$column[$i-$start]] .= $time.'|';
						}else{
							$data[$column[$i-$start]] .= $sheetData[$j][chr($i)].'|';
						}
					}
				}
				$data['apply_time'] = date('Y-m-d H:i',time());
				$data['flow_id'] = $flow_id;
// 				$data['sum'] = $sum;
				$model -> add($data);
				if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/" . $inputFileName)) {
					unlink($_SERVER["DOCUMENT_ROOT"] . "/" . $inputFileName);
				}
				$this -> assign('jumpUrl', U("flow/edit",array('id'=>$flow_id,'fid'=>'darft')));
				$this -> success('导入成功！');
			}
		} else {
			$this -> display();
		}
	}
	function add() {
		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);

		$type_id = $_REQUEST['type'];
		$model = M("FlowType");
		$flow_type = $model -> find($type_id);
		$this -> assign("flow_type", $flow_type);
		$this -> assign("type_id", $type_id);
		//区分人力资源和行政管理
		$fn = $flow_type['name'];
		$menu_first_title = '行政管理';
		if($fn == '员工请假申请' || $fn == '外勤/出差申请' || $fn == '部门招聘需求申请' || $fn == '员工调动申请' || $fn == '试用期评估表' || $fn == '员工调薪申请' || $fn == '转正申请' || $fn == '员工离职申请' || $fn == '员工离职交接申请' || $fn == '出勤异常申请' || $fn == '加班申请' || $fn == '每月考勤表' || $fn == '每月打卡信息'){
			$menu_first_title = '人力资源';
		}
		$this -> assign('first_title',$menu_first_title);

		if($menu_first_title = '人力资源'){
			$fn2 = $flow_type['name'];
			if($fn2 == '部门招聘需求申请'){
				$menu_second_title = '招聘管理';
			}elseif ($fn2 == '加班申请' || $fn2 == '员工请假申请' || $fn2 == '出勤异常申请' || $fn2 == '外勤/出差申请' || $fn2 == '每月考勤表' || $fn2 == '每月打卡信息') {
				$menu_second_title = '考勤管理';
			}elseif ($fn2 == '员工调动申请' || $fn2 == '试用期评估表' || $fn2 == '转正申请' || $fn2 == '员工离职申请' || $fn2 == '员工离职交接申请') {
				$menu_second_title = '员工关系管理';
			}
			elseif ($fn2 == '员工调薪申请') {
				$menu_second_title = '薪酬管理';
			}
			$this -> assign('second_title',$menu_second_title);
		}
		if($menu_first_title = '行政管理') {
			$fn2 = $flow_type['name'];
			if($fn2 == '私车公用申请' || $fn2 == '公交卡使用申请'){
				$menu_second_title = '物业管理';
			}elseif ($fn2 == '物品采购调拨申请' || $fn2 == '办公用品采购申请' || $fn2 == '办公用品领用申请') {
				$menu_second_title = '物品管理';
			}
			$this -> assign('second_title',$menu_second_title);
		}
		
		
		$model_flow_field = D("FlowField");
		$field_list = $model_flow_field -> get_field_list($type_id);
		$this -> assign("field_list", $field_list);
		
		$uid = get_user_id();
		if($uid){
			$info = array();
			$user_info = get_user_info($uid,'name,dept_name,dept_id,office_tel,mobile_tel,duty,email');
			foreach ($user_info as $v){
				$info = $v;
			}
			$info['user_id'] = $uid;
		}
		$info['available_hour'] = getAvailableHour();
		$info['available_hour2'] = getAvailableHour3(time(),$uid,'Create');
		$info['available_year'] = getAvailableYearHour()/2;
		$this -> assign("user_info", $info);
		$this -> assign("time", time());
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
				$now = time();
				$this_year = date('Y',$now);
				$this_month = date('m',$now);
				$this_day = date('d',$now);
				//这样的year只是模糊数，但是避免了闰月影响
				$year = $this_year-$in_date1[0]+($this_month-$in_date1[1])/30+($this_day-$in_date1[2])/360;
// 				$year = (time()-strtotime($in_date2))/(365*24*60*60);
			}
		}
		$this -> assign("year", $year);
		
		$flow_arr = array('uid'=>get_user_id(),'dept_id'=>get_dept_id(),'flow_type_id'=>$type_id);
		//默认7天
		$flow_arr['day'] = 7;
		//其实不加$add也可以，把$flow_arr['add']设置为0，（总经理要审批，但总经理进不去，也就无所谓）
		$flow_arr['add'] = '0';
		
		$flow_message = $this->_getFlowMessageByTypeName($flow_type['name'],$flow_arr);
		$this -> assign("confirm_text", $flow_message['confirm_text']);
		// 		echo $flow_message['confirm_text'];
		$this -> assign("isYuanQuCaiWuBu", isYuanQuCaiWuBu($uid));
		$this -> display();
	}
	public function ajaxgetflow(){
		$type = $_GET['type'];
		switch($type){
			case 'leave' : $this->ajaxgetflow_leave();
			case 'attendance' : $this->ajaxgetflow_attendance();
			case 'over_time' : $this->ajaxgetflow_over_time();
			case 'employment' : $this->ajaxgetflow_employment();
			case 'internal' : $this->ajaxgetflow_internal();
			case 'metting_communicate' : $this->ajaxgetflow_metting_communicate();
			case 'card_application' :$this->ajaxgetflow_card_application();
			case 'notice_file' :$this->ajaxgetflow_notice_file();
			case 'notice_personnel' :$this->ajaxgetflow_notice_personnel();
			case 'contract' :$this->ajaxgetflow_contract();
			case 'resignation_application' : $this->ajaxgetflow_resignation();
			case 'probation_evaluate' : $this->ajaxgetflow_probation();
			case 'regular_work_application' : $this->ajaxgetflow_regular_work_application();
			case 'personnel_changes' :$this->ajaxgetflow_personnel_changes();
			case 'salary_changes' :$this->ajaxgetflow_salary_changes();
			case 'resignation_list' :$this->ajaxgetflow_resignation_list();
			case 'office_supplies_application' :$this->ajaxgetflow_office_supplies_application();
			case 'office_use_application' :$this->ajaxgetflow_office_use_application();
			case 'goods_procurement_allocation' :$this->ajaxgetflow_goods_procurement_allocation();
			case 'bus_card_use' :$this->ajaxgetflow_bus_card_use();
			case 'chops_use' :$this->ajaxgetflow_chops_use();
			case 'car_use' :$this->ajaxgetflow_car_use();
			default :return false;
		}
	}
	public function ajaxgetflow_leave($array=array(),$flow_log){//外勤/出差申请,员工请假申请
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		$day = $_POST['day']?$_POST['day']:$array['day'];
		if(empty($uid) || ($day<0)){
			
			return false;
		}
// 		if(isHzYuanQu($uid)){
// 			$flow = HzYuanQuFlow($uid,$day);
// 		}else{
			$flow = getFlow($uid,$day);
// 		}

		if(!empty($flow)){
			if($this->isAjax()){
				$this->ajaxReturn(getFlowData($flow),null,1);
			}
			if(isYuanQuCaiWuBu($uid)){
				$confirm_text = getConfirmText(array('getParentid/getDeptManagerId','getFinancialManagerId','getHRDeputyGeneralManagerId','getGeneralManagerId'),$array['flow_type_id'],$uid);
			}else{
				$confirm_text = getConfirmText(array('getParentid/getDeptManagerId','getHRDeputyGeneralManagerId','getGeneralManagerId'),$array['flow_type_id'],$uid);
			}
			
			
			$this->_add_flow_index_log($flow,$flow_log);
			return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
		}else{
			if($this->isAjax()){
				$this->ajaxReturn(null,null,0);
			}
			return false;
		}
		
	}
	public function ajaxgetflow_attendance($array=array(),$flow_log){//补勤单
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		if(empty($uid)){
			return false;
		}
		if(isYuanQuCaiWuBu($uid)){
			$isYuanQuCaiWuBu = true;
			$flow = checkFlowNotMe(array(getParentid($uid),getFinancialManagerId(),getHRDeputyGeneralManagerId($uid)));
		}else{
			$isYuanQuCaiWuBu = false;
			$flow = checkFlowNotMe(array(getParentid($uid),getHRDeputyGeneralManagerId($uid)));
		}
		if(!empty($flow)){
			if($this->isAjax()){
				$this->ajaxReturn(getFlowData($flow),null,1);
			}
			if($isYuanQuCaiWuBu){
				$confirm_text = getConfirmTextNotMe(array('getParentid','getFinancialManagerId','getHRDeputyGeneralManagerId'),$array['flow_type_id'],$uid);
			}else{
				$confirm_text = getConfirmTextNotMe(array('getParentid','getHRDeputyGeneralManagerId'),$array['flow_type_id'],$uid);
			}
			$this->_add_flow_index_log($flow,$flow_log);
			
			return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
		}else{
			if($this->isAjax()){
				$this->ajaxReturn(null,null,0);
			}
			return false;
		}
	}
	public function ajaxgetflow_over_time($array=array(),$flow_log){
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		$dept_id = $_POST['dept_id']?$_POST['dept_id']:$array['dept_id'];
		if(empty($uid)){
			return false;
		}
		$Parentid = getParentid($uid);
		if(isYuanQuCaiWuBu($uid)){
			$isYuanQuCaiWuBu = true;
			$flow = checkFlowNotMe(array(getParentid($uid),getFinancialManagerId(),getHRDeputyGeneralManagerId($uid)));
		}else{
			$isYuanQuCaiWuBu = false;
			$flow = checkFlowNotMe(array($Parentid,getHRDeputyGeneralManagerId($uid)));
		}
		
		if($this->isAjax()){
			$this->ajaxReturn(getFlowData(array_unique2($flow)),null,1);
		}
		if($isYuanQuCaiWuBu){
			$confirm_text = getConfirmTextNotMe(array('getParentid','getFinancialManagerId','getHRDeputyGeneralManagerId'),$array['flow_type_id'],$uid);
		}else{
			$confirm_text = getConfirmTextNotMe(array('getParentid','getHRDeputyGeneralManagerId'),$array['flow_type_id'],$uid);
		}
		
		$this->_add_flow_index_log($flow,$flow_log);
		return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
	}
	public function ajaxgetflow_employment($array=array(),$flow_log){//用工申请表
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		$dept_id = $_POST['dept_id']?$_POST['dept_id']:$array['dept_id'];
		$add = $_GET['add']?$_GET['add']:$array['add'];
		if(empty($uid)){
			return false;
		}
		$dept_idd = getDeptManagerId($uid,$dept_id);
		if($add=='1'){//辞职补充
		    if (isYuanQuCaiWuBu($uid)) {
		    	$flow = checkFlowNotMe(array($dept_idd,getHRDeputyGeneralManagerId($uid),getFinancialManagerId(),getZhaopinDirector($uid)));
				$confirm_text = getConfirmTextNotMe(array('getDeptManagerId','getHRDeputyGeneralManagerId','getFinancialManagerId','getZhaopinDirector'),$array['flow_type_id'],$uid);
		    }else{
		    	$flow = checkFlowNotMe(array($dept_idd,getHRDeputyGeneralManagerId($uid),getZhaopinDirector($uid)));
				$confirm_text = getConfirmTextNotMe(array('getDeptManagerId','getHRDeputyGeneralManagerId','getZhaopinDirector'),$array['flow_type_id'],$uid);	
		    }
			
		}else{
			if (isYuanQuCaiWuBu($uid)) {
				$flow = checkFlowNotMe(array($dept_idd,getHRDeputyGeneralManagerId($uid),getFinancialManagerId(),getGeneralManagerId($uid),getZhaopinDirector($uid)));
				$confirm_text = getConfirmTextNotMe(array('getDeptManagerId','getHRDeputyGeneralManagerId','getFinancialManagerId','getGeneralManagerId','getZhaopinDirector'),$array['flow_type_id'],$uid);
			}else{
				$flow = checkFlowNotMe(array($dept_idd,getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid),getZhaopinDirector($uid)));
				$confirm_text = getConfirmTextNotMe(array('getDeptManagerId','getHRDeputyGeneralManagerId','getGeneralManagerId','getZhaopinDirector'),$array['flow_type_id'],$uid);
			}
			
		}
		if(!empty($flow)){
			if($this->isAjax()){
				$this->ajaxReturn(getFlowData($flow),null,1);
			}
			$this->_add_flow_index_log($flow,$flow_log);
			return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
		}else{
			if($this->isAjax()){
				$this->ajaxReturn(null,null,0);
			}
			return false;
		}
	
	}
	function ajaxgetflow_internal($array=array(),$flow_log){
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		$dept_id_from = $_POST['dept_id_from']?$_POST['dept_id_from']:$array['dept_id_from'];
		$dept_id_to = $_POST['dept_id_to']?$_POST['dept_id_to']:$array['dept_id_to'];
		$model = D('UserView');
// 		$user_from = $model->where(array('pos_id'=>array('eq',$dept_id_from),'is_del'=>array('eq',0)))->order('position_sort')->find();
		$user_to = $model->where(array('pos_id'=>array('eq',$dept_id_to),'is_del'=>array('eq',0)))->order('position_sort')->find();
		
		if(!empty($user_to)){
			$flow = checkFlowNotMe(array(getParentid($uid),getHRDeputyGeneralManagerId($user_from['id']),getGeneralManagerId($uid),$user_to['id']));
			if($this->isAjax()){
				$this->ajaxReturn(getFlowData($flow),null,1);
			}
			$confirm_text = getConfirmText(array('getDeptManagerIdFrom','getHRDeputyGeneralManagerId','getGeneralManagerId','getDeptManagerIdTo'),$array['flow_type_id'],$uid);
			$this->_add_flow_index_log($flow,$flow_log);
			return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
		}else{
			if($this->isAjax()){
				$this->ajaxReturn(null,null,1);
			}
			$confirm_text = getConfirmText(array('getDeptManagerIdFrom','getHRDeputyGeneralManagerId','getGeneralManagerId','getDeptManagerIdTo'),$array['flow_type_id'],$uid);
			$this->_add_flow_index_log($flow,$flow_log);
			return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
		}
		
	}
	function ajaxgetflow_metting_communicate($array=array(),$flow_log){
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		
		if(isYuanQuCaiWuBu($uid)){
			$isYuanQuCaiWuBu = true;
			$flow = array(getHRDeputyGeneralManagerId($uid),getFinancialManagerId());
		}else{
			$isYuanQuCaiWuBu = false;
			$flow = array(getHRDeputyGeneralManagerId($uid));
		}
		
		if($this->isAjax()){
			$this->ajaxReturn(getFlowData($flow),null,1);
		}
		if($isYuanQuCaiWuBu){
			$confirm_text = getConfirmTextNotMe(array('getHRDeputyGeneralManagerId','getFinancialManagerId'),$array['flow_type_id'],$uid);
		}else{
			$confirm_text = getConfirmTextNotMe(array('getHRDeputyGeneralManagerId'),$array['flow_type_id'],$uid);
		}
			
		$this->_add_flow_index_log($flow,$flow_log);
		return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
	}
	function ajaxgetflow_card_application($array=array(),$flow_log){
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		$dept_id = $_POST['dept_id']?$_POST['dept_id']:$array['dept_id'];
		$dept_uid = getDeptManagerId($uid,$dept_id);
		if(isYuanQuCaiWuBu($uid)){
			$isYuanQuCaiWuBu = true;
			$flow = array($dept_uid,getFinancialManagerId());
		}else{
			$isYuanQuCaiWuBu = false;
			$flow = array($dept_uid);
		}
		
		if($this->isAjax()){
			$this->ajaxReturn(getFlowData($flow),null,1);
		}
		if($isYuanQuCaiWuBu){
			$confirm_text = getConfirmTextNotMe(array('getDeptManagerId','getFinancialManagerId'),$array['flow_type_id'],$uid);
		}else{
			$confirm_text = getConfirmTextNotMe(array('getDeptManagerId'),$array['flow_type_id'],$uid);
		}
		
		$this->_add_flow_index_log($flow,$flow_log);
		return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
	}
	function ajaxgetflow_notice_file($array=array(),$flow_log){
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		$dept_id = $_POST['dept_id']?$_POST['dept_id']:$array['dept_id'];
		$dept_uid = getDeptManagerId($uid,$dept_id);
		if(isYuanQuCaiWuBu($uid)){
			$isYuanQuCaiWuBu = true;
			$flow = array($dept_uid,getFinancialManagerId(),getHRDeputyGeneralManagerId($uid));
		}else{
			$isYuanQuCaiWuBu = false;
			$flow = array($dept_uid,getHRDeputyGeneralManagerId($uid));
		}
		
		if($this->isAjax()){
			$this->ajaxReturn(getFlowData(array_unique2($flow)),null,1);
		}
		if($isYuanQuCaiWuBu){
			$confirm_text = getConfirmTextNotMe(array('getDeptManagerId','getFinancialManagerId','getHRDeputyGeneralManagerId'),$array['flow_type_id'],$uid);
		}else{
			$confirm_text = getConfirmTextNotMe(array('getDeptManagerId','getHRDeputyGeneralManagerId'),$array['flow_type_id'],$uid);
		}
		
		$this->_add_flow_index_log($flow,$flow_log);
		return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
	}
	function ajaxgetflow_notice_personnel($array=array(),$flow_log){
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		$dept_id = $_POST['dept_id']?$_POST['dept_id']:$array['dept_id'];
		$dept_uid = getDeptManagerId($uid,$dept_id);
		if(isYuanQuCaiWuBu($uid)){
			$isYuanQuCaiWuBu = true;
			$flow = checkFlowNotMe(array($dept_uid,getFinancialManagerId(),getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid)));
		}else{
			$isYuanQuCaiWuBu = false;
			$flow = checkFlowNotMe(array($dept_uid,getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid)));
		}
		
		if($this->isAjax()){
			$this->ajaxReturn(getFlowData(array_unique2($flow)),null,1);
		}
		if($isYuanQuCaiWuBu){
			$confirm_text = getConfirmTextNotMe(array('getDeptManagerId','getFinancialManagerId','getHRDeputyGeneralManagerId','getGeneralManagerId'),$array['flow_type_id'],$uid);
		}else{
			$confirm_text = getConfirmTextNotMe(array('getDeptManagerId','getHRDeputyGeneralManagerId','getGeneralManagerId'),$array['flow_type_id'],$uid);
		}
		
		$this->_add_flow_index_log($flow,$flow_log);
		return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
	}
	function ajaxgetflow_contract($array=array(),$flow_log){
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		$dept_id = $_POST['dept_id']?$_POST['dept_id']:$array['dept_id'];
		$dept_uid = getDeptManagerId($uid,$dept_id);
		if(isYuanQuCaiWuBu($uid)){
			$isYuanQuCaiWuBu = true;
			$flow = checkFlowNotMe(array($dept_uid,getFinancialManagerId(),getOfficeManagerId(),getLegalManagerId()));
		}else{
			$isYuanQuCaiWuBu = false;
			$flow = checkFlowNotMe(array($dept_uid,getOfficeManagerId(),getLegalManagerId()));
		}
		
		if($this->isAjax()){
			$this->ajaxReturn(getFlowData(array_unique2($flow)),null,1);
		}
		if($isYuanQuCaiWuBu){
			$confirm_text = getConfirmTextNotMe(array('getDeptManagerId','getFinancialManagerId','getOfficeManagerId','getLegalManagerId'),$array['flow_type_id'],$uid);
		}else{
			$confirm_text = getConfirmTextNotMe(array('getDeptManagerId','getOfficeManagerId','getLegalManagerId'),$array['flow_type_id'],$uid);
		}
		
		$this->_add_flow_index_log($flow,$flow_log);
		return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
	}
	function ajaxgetflow_resignation($array=array(),$flow_log){
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		$dept_id = $_POST['dept_id']?$_POST['dept_id']:$array['dept_id'];
		$parentid = getParentid($uid);
		$dept_uid = getDeptManagerId($uid,$dept_id);
		if(getRank($uid)>1){//普通
		    if(isYuanQuCaiWuBu($uid)){
				$isYuanQuCaiWuBu = true;
				$flow = checkFlowNotMe(array($parentid,$dept_uid,getFinancialManagerId(),getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid)));
			}else{
				$isYuanQuCaiWuBu = false;
				$flow = checkFlowNotMe(array($parentid,$dept_uid,getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid)));
			}
			
			if($this->isAjax()){
				$this->ajaxReturn(getFlowData(array_unique2($flow)),null,1);
			}
			if($isYuanQuCaiWuBu){
				$confirm_text = getConfirmTextNotMe(array('getParentid','getDeptManagerId','getFinancialManagerId','getHRDeputyGeneralManagerId','getGeneralManagerId'),$array['flow_type_id'],$uid);
			}else{
				$confirm_text = getConfirmTextNotMe(array('getParentid','getDeptManagerId','getHRDeputyGeneralManagerId','getGeneralManagerId'),$array['flow_type_id'],$uid);
			}
			
			$this->_add_flow_index_log($flow);
			return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
		}else{//行政副总，部门总监及以上
		      if(isYuanQuCaiWuBu($uid)){
					$isYuanQuCaiWuBu = true;
					$flow = checkFlowNotMe(array(getHRDeputyGeneralManagerId($uid),getFinancialManagerId(),getGeneralManagerId($uid)));
				}else{
					$isYuanQuCaiWuBu = false;
					$flow = checkFlowNotMe(array(getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid)));
				} 
			
			if($this->isAjax()){
				$this->ajaxReturn(getFlowData(array_unique2($flow)),null,1);
			}
			if($isYuanQuCaiWuBu){
				$confirm_text = getConfirmTextNotMe(array('getHRDeputyGeneralManagerId','getFinancialManagerId','getGeneralManagerId'),$array['flow_type_id'],$uid);
			}else{
				$confirm_text = getConfirmTextNotMe(array('getHRDeputyGeneralManagerId','getGeneralManagerId'),$array['flow_type_id'],$uid);
			}
			
			$this->_add_flow_index_log($flow,$flow_log);
			return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
		}
	}
	function ajaxgetflow_probation($array=array(),$flow_log){
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		$parentid = getParentid($uid);
		$dept_uid = getDeptManagerId($uid);
		if(isYuanQuCaiWuBu($uid)){
			$isYuanQuCaiWuBu = true;
			$flow = array($parentid,getFinancialManagerId(),getHRDeputyGeneralManagerId($uid));
		}else{
			$isYuanQuCaiWuBu = false;
			$flow = array($parentid,getHRDeputyGeneralManagerId($uid));
		}
		
		if($this->isAjax()){
			$this->ajaxReturn(getFlowData(array_unique2($flow)),null,1);
		}
		if($isYuanQuCaiWuBu){
			$confirm_text = getConfirmText(array('getParentid','getFinancialManagerId','getHRDeputyGeneralManagerId','self'),$array['flow_type_id'],$uid);
		}else{
			$confirm_text = getConfirmText(array('getParentid','getHRDeputyGeneralManagerId','self'),$array['flow_type_id'],$uid);
		}
		
		$this->_add_flow_index_log($flow,$flow_log);
		return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
	}
	function ajaxgetflow_regular_work_application($array=array(),$flow_log){
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		$dept_id = $_POST['dept_id']?$_POST['dept_id']:$array['dept_id'];
		$parentid = getParentid($uid);
		$dept_uid = getDeptManagerId($uid);
	
		if(isHeadquarters($uid)==0){//总部
		    if(isYuanQuCaiWuBu($uid)){
				$flow = array($dept_uid,getFinancialManagerId(),getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid));
				$flow = checkFlowNotMe($flow,$uid);
				$flow = array_slice($flow,0,2);
			
				$confirm_text_arr = getConfirmTextArrNotMe(array('getDeptManagerId','getFinancialManagerId','getHRDeputyGeneralManagerId','getGeneralManagerId'),$array['flow_type_id'],$uid);
				$confirm_text_arr = array_slice($confirm_text_arr,0,2);
				$confirm_text = '';
			}else{
				$flow = array($dept_uid,getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid));
				$flow = checkFlowNotMe($flow,$uid);
				$flow = array_slice($flow,0,2);
			
				$confirm_text_arr = getConfirmTextArrNotMe(array('getDeptManagerId','getHRDeputyGeneralManagerId','getGeneralManagerId'),$array['flow_type_id'],$uid);
				$confirm_text_arr = array_slice($confirm_text_arr,0,2);
				$confirm_text = '';
			}
			
			foreach ($confirm_text_arr as $k=>$v){
				$confirm_text.=$v;
			}
			
		}else{
			if(get_user_info($uid, 'position_name')=='副总'){
				 if (isYuanQuCaiWuBu($uid)) {
		    		$flow = checkFlowNotMe(array($parentid,getFinancialManagerId(),getParentid($parentid),getHRDeputyGeneralManagerId($uid)),$uid);
					$confirm_text = getConfirmTextNotMe(array('getParentid','getParentid','getFinancialManagerId','getHRDeputyGeneralManagerId'),$array['flow_type_id'],$uid);
		    	}else{
		    		$flow = checkFlowNotMe(array($parentid,getParentid($parentid),getHRDeputyGeneralManagerId($uid)),$uid);
					$confirm_text = getConfirmTextNotMe(array('getParentid','getParentid','getHRDeputyGeneralManagerId'),$array['flow_type_id'],$uid);	
		   		}
				
			}else{
				if (isYuanQuCaiWuBu($uid)) {
		    		$flow = checkFlowNotMe(array($parentid,$dept_uid,getFinancialManagerId(),getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid)),$uid);
					$confirm_text = getConfirmTextNotMe(array('getParentid','getDeptManagerId','getFinancialManagerId','getHRDeputyGeneralManagerId','getGeneralManagerId'),$array['flow_type_id'],$uid);
		    	}else{
		    		$flow = checkFlowNotMe(array($parentid,$dept_uid,getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid)),$uid);
					$confirm_text = getConfirmTextNotMe(array('getParentid','getDeptManagerId','getHRDeputyGeneralManagerId','getGeneralManagerId'),$array['flow_type_id'],$uid);	
		   	 	}
	
			}
		}
		
		if($this->isAjax()){
			$this->ajaxReturn(getFlowData(array_unique2($flow)),null,1);
		}
		$this->_add_flow_index_log($flow,$flow_log);
		return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
	}
	function ajaxgetflow_personnel_changes($array=array(),$flow_log){
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		$dept_id_from = $_POST['dept_id']?$_POST['dept_id']:$array['dept_id'];
		$dept_id_to = $_POST['dept_id_to']?$_POST['dept_id_to']:$array['dept_id_to'];
		
		$parentid_1 = getParentid($uid);
		$dept_uid_1 = getDeptManagerId($uid,$dept_id_from);
		
// 		$parentid_2 = getParentid(null,$dept_id_to);
		$dept_uid_2 = getDeptManagerId(null,$dept_id_to);
		if(isYuanQuCaiWuBu($uid)){
			$isYuanQuCaiWuBu = true;
			$flow = checkFlowNotMe(array($parentid_1,$dept_uid_1,$dept_uid_2,$dept_uid_2,getFinancialManagerId(),getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid)));
		}else{
			$isYuanQuCaiWuBu = false;
			$flow = checkFlowNotMe(array($parentid_1,$dept_uid_1,$dept_uid_2,$dept_uid_2,getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid)));
		}
		
		if($this->isAjax()){
			$this->ajaxReturn(getFlowData(array_unique2($flow)),null,1);
		}
		//因前端排列问题与流程顺序不一致，故不用此$confirm_text
// 		$confirm_text = getConfirmText(array('getParentidFrom','getDeptManagerIdFrom','getDeptManagerIdTo','getDeptManagerIdTo','getHRDeputyGeneralManagerId','getGeneralManagerId','self'),$array['flow_type_id'],$uid);
// 		$this->_add_flow_index_log($flow);
		return array('flow'=>$flow);
	}
	function ajaxgetflow_salary_changes($array=array(),$flow_log){
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		$dept_id_from = $_POST['dept_id']?$_POST['dept_id']:$array['dept_id'];
		
		$parentid_1 = getParentid($uid,$dept_id_from);
		$dept_uid_1 = getDeptManagerId($uid,$dept_id_from);
		
		if(isYuanQuCaiWuBu($uid)){
			$isYuanQuCaiWuBu = true;
			$flow = checkFlowNotMe(array($parentid_1,$dept_uid_1,getFinancialManagerId(),getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid)));
		}else{
			$isYuanQuCaiWuBu = false;
			$flow = checkFlowNotMe(array($parentid_1,$dept_uid_1,getHRDeputyGeneralManagerId($uid),getGeneralManagerId($uid)));
		}
		
		if($this->isAjax()){
			$this->ajaxReturn(getFlowData(array_unique2($flow)),null,1);
		}
		if($isYuanQuCaiWuBu){
			$confirm_text = getConfirmTextNotMe(array('getParentid','getDeptManagerId','getFinancialManagerId','getHRDeputyGeneralManagerId','getGeneralManagerId','get_user_id'),$array['flow_type_id'],$uid);
		}else{
			$confirm_text = getConfirmTextNotMe(array('getParentid','getDeptManagerId','getHRDeputyGeneralManagerId','getGeneralManagerId','get_user_id'),$array['flow_type_id'],$uid);
		}
		
		$this->_add_flow_index_log($flow,$flow_log);
		return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
	}
	function ajaxgetflow_resignation_list($array=array(),$flow_log){
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		$dept_id = $_POST['dept_id']?$_POST['dept_id']:$array['dept_id'];
		if(isYuanQuCaiWuBu($uid)){
			$isYuanQuCaiWuBu = true;
			$flow = array(getRSManagerId(),getFinancialManagerId());
		}else{
			$isYuanQuCaiWuBu = false;
			$flow = array(getRSManagerId());
		}
		
		if($this->isAjax()){
			$this->ajaxReturn(getFlowData(array_unique2($flow)),null,1);
		}
		if($isYuanQuCaiWuBu){
			$confirm_text = getConfirmText(array('getRSManagerId','getFinancialManagerId'),$array['flow_type_id'],$uid);
		}else{
			$confirm_text = getConfirmText(array('getRSManagerId'),$array['flow_type_id'],$uid);
		}
		
		$this->_add_flow_index_log($flow,$flow_log);
		return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
	}
	function ajaxgetflow_office_supplies_application($array=array(),$flow_log){
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		$dept_id = $_POST['dept_id']?$_POST['dept_id']:$array['dept_id'];
		$dept_uid = getDeptManagerId($uid,$dept_id);
		if(isYuanQuCaiWuBu($uid)){
			$isYuanQuCaiWuBu = true;
			$flow = checkFlowNotMe(array($dept_uid,getFinancialManagerId(),getHRDeputyGeneralManagerId($uid),getFinancialManagerId(),getGeneralManagerId($uid)));
		}else{
			$isYuanQuCaiWuBu = false;
			$flow = checkFlowNotMe(array($dept_uid,getHRDeputyGeneralManagerId($uid),getFinancialManagerId(),getGeneralManagerId($uid)));
		}
		
		if($this->isAjax()){
			$this->ajaxReturn(getFlowData(array_unique2($flow)),null,1);
		}
		if($isYuanQuCaiWuBu){
			$confirm_text = getConfirmTextNotMe(array('getDeptManagerId','getFinancialManagerId','getHRDeputyGeneralManagerId','getFinancialManagerId','getGeneralManagerId'),$array['flow_type_id'],$uid);
		}else{
			$confirm_text = getConfirmTextNotMe(array('getDeptManagerId','getHRDeputyGeneralManagerId','getFinancialManagerId','getGeneralManagerId'),$array['flow_type_id'],$uid);
		}
		
		$this->_add_flow_index_log($flow,$flow_log);
		return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
	}
	function ajaxgetflow_office_use_application($array=array(),$flow_log){
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		$dept_id = $_POST['dept_id']?$_POST['dept_id']:$array['dept_id'];
		$dept_uid = getDeptManagerId($uid,$dept_id);
		if(isYuanQuCaiWuBu($uid)){
			$isYuanQuCaiWuBu = true;
			$flow = array($dept_uid,getFinancialManagerId());
		}else{
			$isYuanQuCaiWuBu = false;
			$flow = array($dept_uid);
		}
		
		if($this->isAjax()){
			$this->ajaxReturn(getFlowData(array_unique2($flow)),null,1);
		}
		if($isYuanQuCaiWuBu){
			$confirm_text = getConfirmText(array('getDeptManagerId','getFinancialManagerId'),$array['flow_type_id'],$uid);
		}else{
			$confirm_text = getConfirmText(array('getDeptManagerId'),$array['flow_type_id'],$uid);
		}
		
		$this->_add_flow_index_log($flow,$flow_log);
		return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
	}
	function ajaxgetflow_goods_procurement_allocation($array=array(),$flow_log){
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		$dept_id = $_POST['dept_id']?$_POST['dept_id']:$array['dept_id'];
		$dept_uid = getDeptManagerId($uid,$dept_id);
		if(isYuanQuCaiWuBu($uid)){
			$isYuanQuCaiWuBu = true;
			$flow = checkFlowNotMe(array($dept_uid,getFinancialManagerId(),getHRDeputyGeneralManagerId($uid),getFinancialManagerId(),getGeneralManagerId($uid)));
		}else{
			$isYuanQuCaiWuBu = false;
			$flow = checkFlowNotMe(array($dept_uid,getHRDeputyGeneralManagerId($uid),getFinancialManagerId(),getGeneralManagerId($uid)));
		}
		
		if($this->isAjax()){
			$this->ajaxReturn(getFlowData(array_unique2($flow)),null,1);
		}
		if($isYuanQuCaiWuBu){
			$confirm_text = getConfirmTextNotMe(array('getDeptManagerId','getFinancialManagerId','getHRDeputyGeneralManagerId','getFinancialManagerId','getGeneralManagerId'),$array['flow_type_id'],$uid);
		}else{
			$confirm_text = getConfirmTextNotMe(array('getDeptManagerId','getHRDeputyGeneralManagerId','getFinancialManagerId','getGeneralManagerId'),$array['flow_type_id'],$uid);
		}
		
		$this->_add_flow_index_log($flow,$flow_log);
		return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
	}
	function ajaxgetflow_bus_card_use($array=array(),$flow_log){
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		$dept_id = $_POST['dept_id']?$_POST['dept_id']:$array['dept_id'];
		$dept_uid = getDeptManagerId($uid,$dept_id);
		if(isYuanQuCaiWuBu($uid)){
			$isYuanQuCaiWuBu = true;
			$flow = array($dept_uid,getFinancialManagerId(),getFrontDesk());
		}else{
			$isYuanQuCaiWuBu = true;
			$flow = array($dept_uid,getFrontDesk());
		}
		if($this->isAjax()){
			$this->ajaxReturn(getFlowData(array_unique2($flow)),null,1);
		}
		if($isYuanQuCaiWuBu){
			$confirm_text = getConfirmText(array('getDeptManagerId','getFinancialManagerId','getFrontDesk'),$array['flow_type_id'],$uid);
		}else{
			$confirm_text = getConfirmText(array('getDeptManagerId','getFrontDesk'),$array['flow_type_id'],$uid);
		}
		$this->_add_flow_index_log($flow,$flow_log);
		return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
	}
	function ajaxgetflow_chops_use($array=array(),$flow_log){
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		$dept_id = $_POST['dept_id']?$_POST['dept_id']:$array['dept_id'];
		$dept_uid = getDeptManagerId($uid,$dept_id);
		if(isYuanQuCaiWuBu($uid)){
			$isYuanQuCaiWuBu = true;
			$flow = array($dept_uid,getFinancialManagerId());
		}else{
			$isYuanQuCaiWuBu = true;
			$flow = array($dept_uid);
		}
		
		if($this->isAjax()){
			$this->ajaxReturn(getFlowData($flow),null,1);
		}
		if($isYuanQuCaiWuBu){
			$confirm_text = getConfirmText(array('getDeptManagerId','getFinancialManagerId'),$array['flow_type_id'],$uid);
		}else{
			$confirm_text = getConfirmText(array('getDeptManagerId'),$array['flow_type_id'],$uid);
		}
		
		$this->_add_flow_index_log($flow,$flow_log);
		return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
	}
	function ajaxgetflow_car_use($array=array(),$flow_log){
		$uid = $_POST['uid']?$_POST['uid']:$array['uid'];
		$dept_id = $_POST['dept_id']?$_POST['dept_id']:$array['dept_id'];
		$dept_uid = getDeptManagerId($uid,$dept_id);
		if(isYuanQuCaiWuBu($uid)){
			$isYuanQuCaiWuBu = true;
			$flow = array($dept_uid,getFinancialManagerId());
		}else{
			$isYuanQuCaiWuBu = true;
			$flow = array($dept_uid);
		}
		
		if($this->isAjax()){
			$this->ajaxReturn(getFlowData($flow),null,1);
		}
		if($isYuanQuCaiWuBu){
			$confirm_text = getConfirmText(array('getDeptManagerId','getFinancialManagerId'),$array['flow_type_id'],$uid);
		}else{
			$confirm_text = getConfirmText(array('getDeptManagerId'),$array['flow_type_id'],$uid);
		}
		
		$this->_add_flow_index_log($flow,$flow_log);
		return array('flow'=>$flow,'confirm_text'=>$this->fetch('',$confirm_text,''));
	}
	function ajaxgettime(){
		$start_time = $_POST['start_time'];
		$end_time = $_POST['end_time'];
		$type = $_GET['type'];
		if(strtotime($end_time)-strtotime($start_time)<3600){
			$this->ajaxReturn(null,null,1);
		}
		if ($type=='metting' || $type=='outside') {//会议、外勤，向下取整，24小时为一天
			$hour_sum = (strtotime($end_time)-strtotime($start_time))/3600;
			$day = floor($hour_sum/24);
			$hour = floor($hour_sum - $day*24);
			$this->ajaxReturn(array('day'=>$day,'hour'=>$hour),null,1);
		}elseif($type=='over_time'){//加班向下取整，8小时为一天
			$hour_sum = get_overtime_seconds(strtotime($start_time),strtotime($end_time))/3600;
			$day = floor($hour_sum/8);
			$hour = floor($hour_sum - $day*8);
			$this->ajaxReturn(array('day'=>$day,'hour'=>$hour),null,1);
		}else{//请假、出勤证明，向上取整，8小时为一天
			if($_GET['style']=='年假'){
				$start_date = date('Y-m-d',strtotime($start_time));
				$start_date_m_s = strtotime($start_date.' '.get_system_config("MORNING_START"));
				$start_date_a_s = strtotime($start_date.' '.get_system_config("AFTERNOON_START"));
				if(strtotime($start_time)>=$start_date_a_s){
					$start_time = $start_date.' '.get_system_config("AFTERNOON_START");
				}else{
					$start_time = $start_date.' '.get_system_config("MORNING_START");
				}
					
				$end_date = date('Y-m-d',strtotime($end_time));
				$end_date_m_s = strtotime($end_date.' '.get_system_config("MORNING_END"));
				$end_date_a_s = strtotime($end_date.' '.get_system_config("AFTERNOON_END"));
				if(strtotime($end_time)<=$end_date_m_s){
					$end_time = $end_date.' '.get_system_config("MORNING_END");
				}else{
					$end_time = $end_date.' '.get_system_config("AFTERNOON_END");
				}
			}
			
			$hour_sum = get_leave_seconds(strtotime($start_time),strtotime($end_time))/3600;
			$day = floor($hour_sum/8);
			$hour = ceil($hour_sum - $day*8);
			$available_hour = getAvailableHour(strtotime($start_time));
			$available_hour2 = getAvailableHour3(strtotime($start_time),get_user_id(),'Create');
			$available_year = getAvailableYearHour(strtotime($start_time))/2;
			
			$this->ajaxReturn(array('day'=>$day,'hour'=>$hour,'available_hour'=>$available_hour,'available_hour2'=>$available_hour2,'available_year'=>$available_year,'start_time'=>$start_time,'end_time'=>$end_time),null,1);
		}
		
	}

	/** 插入新新数据  **/
	protected function _insert() {
		$model = D("Flow");
		if(is_mobile_request()){
			unset($_GET['id']);
			unset($_GET['token']);
			if (false === $model -> create($_GET)) {
				$this -> error($model -> getError());
			}
		}else{
			if (false === $model -> create()) {
				$this -> error($model -> getError());
			}
		}
		
		if (in_array('user_id', $model -> getDbFields())) {
			$model -> user_id = get_user_id();
		};
		if (in_array('user_name', $model -> getDbFields())) {
			$model -> user_name = get_user_name();
		};
		$step = $model -> step;
// 		$str_confirm = D("Flow") -> _conv_auditor($model -> confirm);
// 		$str_consult = D("Flow") -> _conv_auditor($model -> consult);
// 		$str_auditor = $str_confirm . $str_consult;
// 		if (empty($str_auditor)) {
// 			$this -> error('没有找到任何审核人');
// 		}
		/*保存当前数据对象 */
		$list = $model -> add();

		$model_flow_filed = D("FlowField") -> set_field($list);

		if ($list !== false) {//保存成功
			$model = M(getModelName($list));
			
			$last = $model->where(array('flow_no'=>array('like',date('ym',time()).'%')))->order('flow_no desc')->limit(1)->find();
			if($last){
				$num = intval(substr($last['flow_no'],4));
				$num_str = formatto4w($num+1);
			}else{
				$num_str = formatto4w(1);
			}
			
			if(is_mobile_request()){
				unset($_GET['id']);
				unset($_GET['token']);
				if (false === $model -> create($_GET)) {
					$this -> error($model -> getError());
				}
			}else{
				if (false === $model -> create()) {
					$this -> error($model -> getError());
				}
			}
			
			//字段中存放数组
			$array_field = array(attitude_me,attitude_leader,ability_me,ability_leader,responsibility_me,responsibility_leader,coordinate_me,coordinate_leader,develop_me,develop_leader,ids,names,goods_name,goods_id,types,nums,prices,amounts,marks,goods_name,usage,use_dept,buy_num,add_num,recovery_num,is_allocation,price,amount,add_num_calculation,pay_type,in_place_time);
			foreach ($array_field as $v){
				if(!empty($model -> $v) && is_array($model -> $v)){
					$$v = '';
					foreach ($model -> $v as $vv){
						$$v .=$vv.'|';
					}
					$model -> $v = $$v;
				}
			}
			
			//加减资产
// 			if(getModelName($list)=='FlowOfficeSuppliesApplication' || getModelName($list)=='FlowOfficeUseApplication'){//办公用品采购申请或领用
// 				$flag = 1; 
// 				if(getModelName($list)=='FlowOfficeUseApplication'){
// 					$flag = -1;
// 				}
// 				if(!empty($model -> goods_id) && $step>10){
// 					$goods_id = explode('|',$model -> goods_id);
// 					$goods_nums = explode('|',$model -> nums);
// 					$goods_marks = explode('|',$model -> marks);
// 					$user = new Model();
// 					$user -> startTrans();
// 					$goods_add_check = true;
// 					foreach ($goods_id as $k=>$v){
// 						if(!empty($v)){
// 							$res = change_goods($v,$goods_nums[$k]*$flag,$goods_marks[$k],get_user_id(),time(),false);
// 							if($res===false){
// 								$goods_add_check = false;
// 								break;
// 							}
// 						}
// 					}
// 					if($goods_add_check){
// 						$user->commit();
// 					}else{
// 						$user->rollback();
// 					}
// 				}
// 			}
			
			
			$model -> flow_id = $list;
			$flow_id = $list;
			$style = $model -> style;
			
			$model -> flow_no = date('ym',time()).$num_str;
			
			$list = $model -> add();
			
			if(getModelName($flow_id)=='FlowLeave'){
				if($style=='调休'){
					$flow = M('FlowLeave')->where(array('flow_id'=>array('eq',$flow_id)))->find();
					$create_time = strtotime($flow['start_time']);
					$del_hour = $flow['day_num']*8+$flow['hour_num'];
					$data['hour'] = $del_hour*(-1);
					$data['create_time'] = $create_time;
					$data['user_id'] = get_user_id();
					$data['flow_id'] = $flow_id;
					$data['status'] = $step==10?3:0;
					M('FlowHourCreate')->add($data);
				}
				if($style=='年假'){
					$flow = M('FlowLeave')->where(array('flow_id'=>array('eq',$flow_id)))->find();
					$create_time = strtotime($flow['start_time']);
					$del_half_day = $flow['day_num']*2+($flow['hour_num']>0?1:0);
					$data['half_day'] = $del_half_day*(-1);
					$data['create_time'] = $create_time;
					$data['user_id'] = get_user_id();
					$data['flow_id'] = $flow_id;
					$data['status'] = $step==10?3:0;
					M('FlowYear')->add($data);
				}
			}
			if ($list !== false) {//保存成功
				$this -> assign('jumpUrl', get_return_url());
				$this -> success('新增成功!');
			}else {
				$this -> error('新增失败!');
				//失败提示
			}
			
		} else {
			$this -> error('新增失败!');
			//失败提示
		}
	}

	function read() {
		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
		$folder = $_REQUEST['fid']=='group'?'hr':$_REQUEST['fid'];
		$this -> assign("folder", $folder);
		if (empty($folder)) {
			$this -> error("系统错误");
		}
		$this -> _flow_auth_filter($folder, $map);

		$model = D("Flow");
		if(is_mobile_request()){
			$id = $_REQUEST['idd'];
		}else{
			$id = $_REQUEST['id'];
		}
		$where['id'] = array('eq', $id);
		$where['_logic'] = 'and';
		$map['_complex'] = $where;
		$vo = $model -> where($map) -> find();
		if (empty($vo)) {
			$this -> error("系统错误");
		}
		$flow = M(getModelName($vo['id']))->where(array('flow_id'=>array('eq',$vo['id'])))->find();
		$vo = array_merge($vo,$flow);
		$flow_type_id = $vo['type'];
		
		//字段中存放数组
		$array_field = array(attitude_me,attitude_leader,ability_me,ability_leader,responsibility_me,responsibility_leader,coordinate_me,coordinate_leader,develop_me,develop_leader,ids,names,types,nums,prices,amounts,marks,goods_name,usage,use_dept,buy_num,add_num,recovery_num,is_allocation,price,amount,add_num_calculation,pay_type,in_place_time,basic_info_1,basic_info_2,basic_info_3,basic_info_4,basic_info_5,basic_info_6,basic_info_7,skill_honor_hobby_expect,education,family,training,family_urgency,work_experience);
		foreach ($array_field as $v){
			if(!empty($vo[$v])){
				$vo[$v] = explode('|',$vo[$v]);
			}
		}
		if($vo['pic']){$vo['pic'] = get_save_url() . $vo['pic'];}
		if($vo['education']){$vo['education'] = exp_info($vo['education']);}
		if($vo['training']){$vo['training'] = exp_info($vo['training']);}
		if($vo['family']){$vo['family'] = exp_info($vo['family']);}
		if($vo['work_experience']){$vo['work_experience'] = exp_info($vo['work_experience']);}
		if(is_mobile_request()){
			$vo['mobile_add_file'] = mobile_show_file($vo['add_file'],'flow');
		}
		
		$this -> assign('vo', $vo);
		$this -> assign("emp_no", $vo['emp_no']);
		$this -> assign("user_name", $vo['user_name']);
		
		$model_flow_field = D("FlowField");
		$field_list = $model_flow_field -> get_data_list($id);
		$this -> assign("field_list", $field_list);
		
		$model = M("FlowType");
		$flow_type = $model -> find($flow_type_id);
		$this -> assign("flow_type", $flow_type);
		
		$model = M("FlowLog");
		$where = array();
		$where['flow_id'] = $id;
		$where['step'] = array('lt', 100);
		$where['_string'] = "result is not null";
		$flow_log = $model -> where($where) -> order("step desc") -> select();
		//已走的最后一步
		$flow_log_last = M('FlowLog')->where(array('flow_id'=>$id))->order('step desc')->limit(1)->find();
		foreach ($flow_log as $k=>$v){
			if($k==0 && $v['result'] == '1' && $flow_log_last['id'] == $v['id']){
				$flow_log[$k]['title'] = D('UserView2')->where(array('id'=>$v['user_id']))->getField('duty');
				$flow_log[$k]['title'] .= '归档';
			}else{
				$flow_log[$k]['title'] = D('UserView2')->where(array('id'=>$v['user_id']))->getField('duty');
				$flow_log[$k]['title'] .= '审批';
			}
		}
		$uid = get_user_id();
		$this -> assign("flow_log", $flow_log);
		
		//全部流程
		$flow_all = array();
		$flow_log_should = array_filter(explode('|',$vo['confirm']));
		$flow_log_should_name = array_filter(explode('<>',$vo['confirm_name']));
		$user_creator = D('UserView2')->where(array('id'=>$vo['user_id']))->find();
		if($flow_log_last['result'] === '1'){
			$flow_all[] = array('color'=>'green','class'=>'li1','title'=>'申请人','name'=>$user_creator['name'],'position_name'=>$user_creator['duty']);
			foreach ($flow_log_should as $k=>$v){
				$user = D('UserView2')->where(array('emp_no'=>$v))->find();
				$flow_all[] = array('color'=>'green','class'=>'li1','name'=>$flow_log_should_name[$k],'position_name'=>$user['duty']);
			}
		}elseif($flow_log_last['result'] === '0'){
			$flow_all[] = array('color'=>'orange','class'=>'li2','title'=>'申请人','name'=>$user_creator['name'],'position_name'=>$user_creator['duty']);
			foreach ($flow_log_should as $k=>$v){
				$user = D('UserView2')->where(array('emp_no'=>$v))->find();
				$flow_all[] = array('color'=>'gray','class'=>'li3','name'=>$flow_log_should_name[$k],'position_name'=>$user['duty']);
			}
		}else{
			$flow_all[] = array('color'=>'green','class'=>'li1','title'=>'申请人','name'=>$user_creator['name'],'position_name'=>$user_creator['duty']);
			foreach ($flow_log_should as $k=>$v){
				$user = D('UserView2')->where(array('emp_no'=>$v))->find();
				if($v == $flow_log_last['emp_no'] && $k == $flow_log_last['step']-21){
					$flow_all[] = array('color'=>'orange','class'=>'li2','name'=>$flow_log_should_name[$k],'position_name'=>$user['duty']);
				}else{
					$flow_all[] = array('color'=>'green','class'=>'li1','name'=>$flow_log_should_name[$k],'position_name'=>$user['duty']);
				}
			}
		}
		$this->assign('flow_all',$flow_all);
		
		$this -> assign("isZhaopinDirector", isZhaopinDirector(get_user_id()));
// 		var_dump(isZhaopinDirector(get_user_id()));
// 		print_r($flow_log);
// 		print_r($vo);
// 		print_r($flow);
// 		dump($flow_log_all);
// 		die;
// 		echo $vo['confirm'];
		$flow_step_user_id = array();
		foreach (array_filter(explode('|', $vo['confirm'])) as $v){
			$u = M('User')->where(array('emp_no'=>$v))->find();
			$flow_step_user_id[] = $u['id'];
		}
// 		dump($flow_step_user_id);
// 		die;
// 		$this->_add_flow_index_log($flow_step_user_id,$flow_log);
		
		$where = array();
		$where['flow_id'] = $id;
		$where['emp_no'] = get_emp_no();
		$where['_string'] = "result is null";
		$to_confirm = $model -> where($where) -> find();
		$this -> assign("to_confirm", $to_confirm);
		
		if (!empty($to_confirm)) {
			$is_edit = $flow_type['is_edit'];
			$this -> assign("is_edit", $is_edit);
		} else {
			$is_edit = $flow_type['is_edit'];
			if ($is_edit <> "2") {
				$this -> assign("is_edit", 0);
			}
		}

		$where = array();
		$where['flow_id'] = $id;
		$where['_string'] = "result is not null";
		$where['emp_no'] = array('neq', $vo['emp_no']);
		$confirmed = $model -> Distinct(true) -> where($where) -> field('emp_no,user_name') -> select();
		$this -> assign("confirmed", $confirmed);
		
		$can_cancel = 0;
		if(!$confirmed && $vo['user_id']==get_user_id()){
			$can_cancel = 1;
		}
		$this -> assign("can_cancel", $can_cancel);
		
		//从$vo中获取数据放到$flow_arr中，再调用ajaxgetflow_*获取审核流程
		$flow_arr = array('uid'=>$vo['user_id'],'dept_id'=>$vo['dept_id'],'flow_type_id'=>$vo['type']);
		if($vo['day_num']!==false){
			$flow_arr['day'] = $vo['day_num'];
		}
		//其实不加$add也可以，把$flow_arr['add']设置为0，（总经理要审批，但总经理进不去，也就无所谓）
		if($vo['apply_reason_2']=='辞职补充'){
			$flow_arr['add'] = '1';
		}else{
			$flow_arr['add'] = '0';
		}
		if($vo['dept_id_from']){
			$flow_arr['dept_id_from'] = $vo['dept_id_from'];
		}
		if($vo['dept_id_to']){
			$flow_arr['dept_id_to'] = $vo['dept_id_to'];
		}
		$flow_message = $this->_getFlowMessageByTypeName($vo['name'],$flow_arr,$flow_log);
		
		$this -> assign("confirm_text", $flow_message['confirm_text']);
		$this -> assign("isYuanQuCaiWuBu", isYuanQuCaiWuBu(get_user_id()));
		$this -> display();
	}
	function editflow() {
		$id = intval($_POST['id']);//flow_* 的id
		$flow_id = intval($_POST['flow_id']);//flow_* 的flow_id
		$is_retrial = $_POST['is_retrial'];
		$recruit_difficult = $_POST['recruit_difficult'];
		$real_arrive_date = $_POST['real_arrive_date'];
		$hand_over_time = $_POST['hand_over_time'];
		$non_competition_compensation = $_POST['non_competition_compensation'];
		$dept_leader_review = $_POST['dept_leader_review'];
		$dept_leader_type = $_POST['dept_leader_type'];
		$dept_leader_day = $_POST['dept_leader_day'];
		$attitude_leader = $_POST['attitude_leader'];
		$ability_leader = $_POST['ability_leader'];
		
		$responsibility_leader = $_POST['responsibility_leader'];
		$coordinate_leader = $_POST['coordinate_leader'];
		$develop_leader = $_POST['develop_leader'];
		$sum_leader = $_POST['sum_leader'];
		
		$dept_leader_date = $_POST['dept_leader_date'];
		$dept_director_review = $_POST['dept_director_review'];
		$dept_director_date = $_POST['dept_director_date'];
		$hr_type = $_POST['hr_type'];
		$hr_add_date = $_POST['hr_add_date'];
		$hr_execute = $_POST['hr_execute'];
		$hr_dismiss = $_POST['hr_dismiss'];
		
		$from_leader_review = $_POST['from_leader_review'];
		$from_leader_date = $_POST['from_leader_date'];
		$to_leader_review = $_POST['to_leader_review'];
		$to_leader_date = $_POST['to_leader_date'];
		$from_director_review = $_POST['from_director_review'];
		$to_director_review = $_POST['to_director_review'];
		$superior_estimate = $_POST['superior_estimate'];
		
		$model = M(getModelName($flow_id));
		if($id){
			$data['id'] = $id;
		}
		if($is_retrial){
			$data['is_retrial'] = $is_retrial;
		}
		if($recruit_difficult){
			$data['recruit_difficult'] = $recruit_difficult;
		}
		if($real_arrive_date){
			$data['real_arrive_date'] = $real_arrive_date;
		}
		if($hand_over_time){
			$data['hand_over_time'] = $hand_over_time;
		}
		if($non_competition_compensation){
			$data['non_competition_compensation'] = $non_competition_compensation;
		}
		if($dept_leader_review){
			$data['dept_leader_review'] = $dept_leader_review;
		}
		if($dept_leader_type){
			$data['dept_leader_type'] = $dept_leader_type;
		}
		if($dept_leader_day){
			$data['dept_leader_day'] = $dept_leader_day;
		}
		
		if($dept_leader_date){
			$data['dept_leader_date'] = $dept_leader_date;
		}
		if($dept_director_review){
			$data['dept_director_review'] = $dept_director_review;
		}
		if($dept_director_date){
			$data['dept_director_date'] = $dept_director_date;
		}
		if($hr_type){
			$data['hr_type'] = $hr_type;
		}
		if($hr_add_date){
			$data['hr_add_date'] = $hr_add_date;
		}
		if($hr_execute){
			$data['hr_execute'] = $hr_execute;
		}
		if($hr_dismiss){
			$data['hr_dismiss'] = $hr_dismiss;
		}
		
		if($from_leader_review){
			$data['from_leader_review'] = $from_leader_review;
		}
		if($from_leader_date){
			$data['from_leader_date'] = $from_leader_date;
		}
		if($to_leader_review){
			$data['to_leader_review'] = $to_leader_review;
		}
		if($to_leader_date){
			$data['to_leader_date'] = $to_leader_date;
		}
		if($from_director_review){
			$data['from_director_review'] = $from_director_review;
		}
		if($to_director_review){
			$data['to_director_review'] = $to_director_review;
		}
		if($attitude_leader){
			$data['attitude_leader'] = $attitude_leader;
		}
		if($ability_leader){
			$data['ability_leader'] = $ability_leader;
		}
		if($responsibility_leader){
			$data['responsibility_leader'] = $responsibility_leader;
		}
		if($coordinate_leader){
			$data['coordinate_leader'] = $coordinate_leader;
		}
		if($develop_leader){
			$data['develop_leader'] = $develop_leader;
		}
		if($sum_leader){
			$data['sum_leader'] = $sum_leader;
		}
		if($superior_estimate){
			$data['superior_estimate'] = $superior_estimate;
		}
		
		$res = $model->save($data);
		if($res){
			$this->ajaxReturn(1,1,0);
		}else{
			$this->ajaxReturn(null,null,0);
		}
	}
	function edit() {
		//草稿修改
// 		$this -> error("系统错误");
		
		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);

		$folder = $_REQUEST['fid'];
		$this -> assign("folder", $folder);

		if (empty($folder)) {
			$this -> error("系统错误");
		}
		$this -> _flow_auth_filter($folder, $map);

		$model = D("Flow");
		$id = $_REQUEST['id'];

		$where['id'] = array('eq', $id);
		$where['_logic'] = 'and';
		$map['_complex'] = $where;

		$vo = $model -> where($where) -> find();
		if (empty($vo)) {
			$this -> error("系统错误");
		}

		$flow = M(getModelName($vo['id']))->where(array('flow_id'=>array('eq',$vo['id'])))->find();
		$vo = array_merge($vo,$flow);
		
		//字段中存放数组
		$array_field = array(attitude_me,attitude_leader,ability_me,ability_leader,responsibility_me,responsibility_leader,coordinate_me,coordinate_leader,develop_me,develop_leader,ids,names,types,nums,prices,amounts,marks,goods_id,goods_name,usage,use_dept,buy_num,add_num,recovery_num,is_allocation,price,amount,add_num_calculation,pay_type,in_place_time);
		foreach ($array_field as $v){
			if(!empty($vo[$v])){
				$vo[$v] = explode('|',$vo[$v]);
			}
		}
		$this -> assign('vo', $vo);
		$model_flow_field = D("FlowField");
		$field_list = $model_flow_field -> get_data_list($id);
		$this -> assign("field_list", $field_list);

		$model = M("FlowType");
		$type = $vo['type'];
		$flow_type = $model -> find($type);
		$this -> assign("flow_type", $flow_type);
		
		$model = M("FlowLog");
		$where = array();
		$where['flow_id'] = $id;
		$where['_string'] = "result is not null";
		$flow_log = $model -> where($where) -> select();

		if ($flow_log) {
			$this -> error("系统错误");
		}
		$this -> assign("flow_log", $flow_log);
		$where = array();
		$where['flow_id'] = $id;
		$where['emp_no'] = get_emp_no();
		$where['_string'] = "result is null";
		$confirm = $model -> where($where) -> select();
		$this -> assign("confirm", $confirm[0]);
		
		$flow_arr = array('uid'=>get_user_id(),'dept_id'=>get_dept_id(),'flow_type_id'=>$type);
		//默认7天
		$flow_arr['day'] = 7;
		//其实不加$add也可以，把$flow_arr['add']设置为0，（总经理要审批，但总经理进不去，也就无所谓）
		$flow_arr['add'] = '0';
		
		$flow_message = $this->_getFlowMessageByTypeName($flow_type['name'],$flow_arr);
		$this -> assign("confirm_text", $flow_message['confirm_text']);
		
		$this -> display();
	}

	/* 更新数据  */
	protected function _update() {
		$name = $this -> getActionName();
		$model = D($name);
		
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		
		$idd = $model -> id;
		$flow_id = $_POST['flow_id'];//model中没有flow_id，所以不能用$model -> flow_id
		$model -> id = $flow_id;
		
		$step = $_POST['step'];
		$style = $_POST['style'];
		
		$list = $model -> save();
		$model_flow_filed = D("FlowField") -> set_field($flow_id);
		if (false !== $list) {
			$model = M(getModelName($flow_id));
			
			if (false === $model -> create()) {
				$this -> error($model -> getError());
			}
			//字段中存放数组
			$array_field = array(attitude_me,attitude_leader,ability_me,ability_leader,responsibility_me,responsibility_leader,coordinate_me,coordinate_leader,develop_me,develop_leader,ids,names,goods_name,goods_id,types,nums,prices,amounts,marks,goods_name,usage,use_dept,buy_num,add_num,recovery_num,is_allocation,price,amount,add_num_calculation,pay_type,in_place_time);
			foreach ($array_field as $v){
				if(!empty($model -> $v) && is_array($model -> $v)){
					$$v = '';
					foreach ($model -> $v as $vv){
						$$v .=$vv.'|';
					}
					$model -> $v = $$v;
				}
			}
// 			if(getModelName($flow_id)=='FlowOfficeSuppliesApplication' || getModelName($flow_id)=='FlowOfficeUseApplication'){//办公用品采购申请或领用
// 				$flag = 1; 
// 				if(getModelName($flow_id)=='FlowOfficeUseApplication'){
// 					$flag = -1;
// 				}
// 				if(!empty($model -> goods_id) && $step>10){
// 					$goods_id = explode('|',$model -> goods_id);
// 					$goods_nums = explode('|',$model -> nums);
// 					$goods_marks = explode('|',$model -> marks);
// 					$user = new Model();
// 					$user -> startTrans();
// 					$goods_add_check = true;
// 					foreach ($goods_id as $k=>$v){
// 						if(!empty($v)){
// 							$res = change_goods($v,$goods_nums[$k]*$flag,$goods_marks[$k],get_user_id(),time(),false);
// 							if($res===false){
// 								$goods_add_check = false;
// 								break;
// 							}
// 						}
// 					}
// 					if($goods_add_check){
// 						$user->commit();
// 					}else{
// 						$user->rollback();
// 					}
// 				}
// 			}
			$model -> id = $idd;
			
			$list = $model -> save();
			
			if(getModelName($flow_id)=='FlowLeave'){
				if($style=='调休'){
					$data['status'] = $step==10?3:0;
					M('FlowHourCreate')->where('flow_id='.$flow_id)->save($data);
				}
				if($style=='年假'){
					$data['status'] = $step==10?3:0;
					M('FlowYear')->where('flow_id='.$flow_id)->save($data);
				}
			}
			
			if (false !== $list) {
				$this -> assign('jumpUrl', get_return_url());
				$this -> success('编辑成功!');
			}
			//成功提示
		} else {
			$this -> error('编辑失败!');
			//错误提示
		}
	}
	public function cancel(){
		$flow_id = $_GET['id'];
		
		$model = M('Flow');
		$vo = $model->find($flow_id);
		
		$model = M('FlowLog');
		$where = array();
		$where['flow_id'] = $flow_id;
		$where['_string'] = "result is not null";
		$where['emp_no'] = array('neq', $vo['emp_no']);
		$confirmed = $model -> Distinct(true) -> where($where) -> field('emp_no,user_name') -> select();
		$this -> assign("confirmed", $confirmed);
		
		$can_cancel = 0;
		if(!$confirmed && $vo['user_id']==get_user_id()){
			$can_cancel = 1;
		}
		if($can_cancel==1){
			$res = M('Flow')->where(array('id'=>$flow_id))->setField(array('is_del'=>1,'del_time'=>time()));
			if($res && getModelName($flow_id)=='FlowLeave'){//员工请假申请
				$flow = M('FlowLeave')->where(array('flow_id'=>array('eq',$flow_id)))->find();
				$create_time = strtotime($flow['start_time']);
				if($flow['style']=='调休'){
					$flow_hour = M('FlowHourCreate')->where(array('flow_id'=>array('eq',$flow_id)))->find();
					if(!$flow_hour){
						$del_hour = $flow['day_num']*8+$flow['hour_num'];
						$flow = M('Flow')->find($flow_id);
						$data['hour'] = $del_hour*(-1);
						$data['create_time'] = $create_time;
						$data['user_id'] = $flow['user_id'];
						$data['status'] = 4;
						M('FlowHourCreate')->add($data);
					}else{
						$data['status'] = 4;
						M('FlowHourCreate')->where('flow_id='.$flow_id)->save($data);
					}
				}else if($flow['style']=='年假'){
					$flow_year = M('FlowYear')->where(array('flow_id'=>array('eq',$flow_id)))->find();
					if(!$flow_year){
						$del_half_year = $flow['day_num']*2+($flow['hour_num']>0?1:0);
						$flow = M('Flow')->find($flow_id);
						$data['hour'] = $del_half_year*(-1);
						$data['create_time'] = $create_time;
						$data['user_id'] = $flow['user_id'];
						$data['status'] = 4;
						M('FlowYear')->add($data);
					}else{
						$data['status'] = 4;
						M('FlowYear')->where('flow_id='.$flow_id)->save($data);
					}
				}
			}
			
			$this -> assign('jumpUrl', U('flow/folder?fid=submit'));
			$this -> success('操作成功!');
		}else{
			$this -> error('操作失败!');
		}
		
		echo $can_cancel;
	}
	public function mark() {
		$action = $_REQUEST['action'];
		switch ($action) {
			case 'approve' :
				$model = D("FlowLog");
				if(is_mobile_request()){
					if (false === $model -> create($_GET)) {
						$this -> error($model -> getError());
					}
					
					$model -> id = $_GET['idd'];
					if($_GET['confirm_user_id']!=$_GET['id']){
						$this -> error('操作失败!');
					}
				}else{
					if (false === $model -> create()) {
						$this -> error($model -> getError());
					}
				}
				
				$model -> result = 1;

				$flow_id = $model -> flow_id;
				$step = $model -> step;
				
				if(getModelName($flow_id)=='FlowOfficeSuppliesApplication' || getModelName($flow_id)=='FlowOfficeUseApplication'){//办公用品采购申请或领用
					if(getModelName($flow_id)=='FlowOfficeUseApplication'){
						$flag = -1;
						$flow = M('FlowOfficeUseApplication')->where(array('flow_id'=>array('eq',$flow_id)))->find();
					}else{
						$flag = 1;
						$flow = M('FlowOfficeSuppliesApplication')->where(array('flow_id'=>array('eq',$flow_id)))->find();
					}
						
					if(!empty($flow['goods_id'])){
						$goods_id = explode('|',$flow['goods_id']);
						$goods_nums = explode('|',$flow['nums']);
						$goods_marks = explode('|',$flow['marks']);
						$user = new Model();
						$user -> startTrans();
						$goods_add_check = true;
						foreach ($goods_id as $k=>$v){
							if(!empty($v)){
								$res = change_goods($v,$goods_nums[$k]*$flag,$goods_marks[$k],get_user_id(),time(),true);
								if($res===false){
									$goods_add_check = false;
									break;
								}
							}
						}
						if($goods_add_check){
							$user->commit();
						}else{
							$user->rollback();
							$this -> error('商品不足，无法领用!');
						}
					}
				}
				
				
				$list = $model -> save();
				
				$is_last_confirm = D("Flow") -> is_last_confirm($flow_id);
				
				$model = D("FlowLog");
				$model -> where("step=$step and flow_id=$flow_id and result is null") -> delete();

				if ($list !== false) {//保存成功
					D("Flow") -> save();
					D("Flow") -> next_step($flow_id, $step);
					
					if($is_last_confirm){
						if(getModelName($flow_id)=='FlowOverTime'){//加班单
							$flow = M('FlowOverTime')->where(array('flow_id'=>array('eq',$flow_id)))->find();
							$create_time = strtotime($flow['start_time']);
							if($flow['use_type']=='调休'){
								$add_hour = $flow['day_num']*8+$flow['hour_num'];
								$flow = M('Flow')->find($flow_id);
								$data['hour'] = $add_hour;
								$data['create_time'] = $create_time;
								$data['user_id'] = $flow['user_id'];
								$data['flow_id'] = $flow_id;
								$data['status'] = 1;
								M('FlowHourCreate')->add($data);
							}
						}elseif(getModelName($flow_id)=='FlowLeave'){//员工请假申请
							$flow = M('FlowLeave')->where(array('flow_id'=>array('eq',$flow_id)))->find();
							$create_time = strtotime($flow['start_time']);
							$end_time = strtotime($flow['end_time']);
							if($flow['style']=='调休'){
								$flow_hour = M('FlowHourCreate')->where(array('flow_id'=>array('eq',$flow_id)))->find();
								if(!$flow_hour){
									$del_hour = $flow['day_num']*8+$flow['hour_num'];
									$flow = M('Flow')->find($flow_id);
									$data['hour'] = $del_hour*(-1);
									$data['create_time'] = $create_time;
									$data['user_id'] = $flow['user_id'];
									$data['status'] = 1;
									//在调休单中标注用掉的是哪个加班单
									$use = getHourPlan($data['user_id'],$data['hour'],$create_time,'Create');
									$data['is_use'] = serialize($use);
									M('FlowHour')->add($data);
									M('FlowHourCreate')->add($data);
								}else{
									$flow = M('Flow')->find($flow_id);
									$data['status'] = 1;
									//在调休单中标注用掉的是哪个加班单
									$use = getHourPlan($flow_hour['user_id'],$flow_hour['hour'],$create_time,'Create');
									$data['is_use'] = serialize($use);
									M('FlowHour')->where('flow_id='.$flow_id)->save($data);
									M('FlowHourCreate')->where('flow_id='.$flow_id)->save($data);
								}
							}else if($flow['style']=='年假'){
								$flow_year = M('FlowYear')->where(array('flow_id'=>array('eq',$flow_id)))->find();
								if(!$flow_year){
									$del_half_day = $flow['day_num']*2+($flow['hour_num']>0?1:0);
									$flow = M('Flow')->find($flow_id);
									$data['half_day'] = $del_half_day*(-1);
									$data['create_time'] = $create_time;
									$data['user_id'] = $flow['user_id'];
									$data['status'] = 1;
									M('FlowYear')->add($data);
								}else{
									$data['status'] = 1;
									M('FlowYear')->where('flow_id='.$flow_id)->save($data);
								}
							}
							$this -> addAttendance($flow_id);
						}elseif (getModelName($flow_id)=='FlowAttendance' || getModelName($flow_id)=='FlowOutside'){
							$this -> addAttendance($flow_id );
						}
						//当最后一个审批人通过以后发送一条信息给提交人
						$flow = M('flow') -> find($flow_id);
						$info['sender_id'] = 1;
						$info['sender_name'] = '管理员';
						$info['receiver_id'] = $flow['user_id'];
						$info['receiver_name'] = $flow['user_name'];
						$info['owner_id'] = $flow['user_id'];
						$flow_name = $flow['name'];
						$info['content'] = $flow_name."已归档";
						$info['create_time']=time();
						M('Message') -> add($info);
						$this -> _pushReturn("", "您有新的消息, 请注意查收", 1,$flow['user_id']);
					}
					
					$this -> assign('jumpUrl', U('flow/folder?fid=confirm'));
					$this -> success('操作成功!');
				} else {
					//失败提示
					$this -> error('操作失败!');
				}
				break;
			case 'back' :
				$model = D("FlowLog");
				if (false === $model -> create()) {
					$this -> error($model -> getError());
				}

				$model -> result = 2;
				if (in_array('user_id', $model -> getDbFields())) {
					$model -> user_id = get_user_id();
				};
				if (in_array('user_name', $model -> getDbFields())) {
					$model -> user_name = get_user_name();
				};

				$flow_id = $model -> flow_id;
				$step = $model -> step;
				//保存当前数据对象
				$list = $model -> save();
				$emp_no = $_REQUEST['emp_no'];
				if ($list !== false) {//保存成功
					D("Flow") -> next_step($flow_id, $step, $emp_no);
					$this -> assign('jumpUrl', U('flow/folder?fid=confirm'));
					$this -> success('操作成功!');
				} else {
					//失败提示
					$this -> error('操作失败!');
				}
				break;
			case 'reject' :
				$model = D("FlowLog");
				if(is_mobile_request()){
					if (false === $model -> create($_GET)) {
						$this -> error($model -> getError());
					}
						
					$model -> id = $_GET['idd'];
					if($_GET['confirm_user_id']!=$_GET['id']){
						$this -> error('操作失败!');
					}
						
				}else{
					if (false === $model -> create()) {
						$this -> error($model -> getError());
					}
				}
				
				$model -> result = 0;
				if (in_array('user_id', $model -> getDbFields())) {
					$model -> user_id = get_user_id();
				};
				if (in_array('user_name', $model -> getDbFields())) {
					$model -> user_name = get_user_name();
				};

				$flow_id = $model -> flow_id;
				$step = $model -> step;
				//保存当前数据对象
				$list = $model -> save();
				//可以裁决的人有多个人的时候，一个人评价完以后，禁止其他人重复裁决。
				$model = D("FlowLog");
				$model -> where("step=$step and flow_id=$flow_id and result is null") -> delete();

				if ($list !== false) {//保存成功
					D("Flow") -> where("id=$flow_id") -> setField('step', 0);

					$user_id = M("Flow") -> where("id=$flow_id") -> getField('user_id');
						//
						//dump($flow_id);die;
						$flow = M('flow') -> find($flow_id);
						$info['sender_id'] = 1;
						$info['sender_name'] = '管理员';
						$info['receiver_id'] = $flow['user_id'];
						$info['receiver_name'] = $flow['user_name'];
						$info['owner_id'] = $flow['user_id'];
						$flow_name = $flow['name'];
						$info['content'] = $flow_name."已驳回";
						$info['create_time']=time();
						M('Message') -> add($info);
					$this -> _pushReturn($new, "您有一个流程被否决", 1, $user_id);

					
					if(getModelName($flow_id)=='FlowLeave'){//员工请假申请
						$flow = M('FlowLeave')->where(array('flow_id'=>array('eq',$flow_id)))->find();
						$create_time = strtotime($flow['start_time']);
						if($flow['style']=='调休'){
							$flow_hour = M('FlowHourCreate')->where(array('flow_id'=>array('eq',$flow_id)))->find();
							if(!$flow_hour){
								$del_hour = $flow['day_num']*8+$flow['hour_num'];
								$flow = M('Flow')->find($flow_id);
								$data['hour'] = $del_hour*(-1);
								$data['create_time'] = $create_time;
								$data['user_id'] = $flow['user_id'];
								$data['status'] = 2;
								M('FlowHourCreate')->add($data);
							}else{
								$data['status'] = 2;
								M('FlowHourCreate')->where('flow_id='.$flow_id)->save($data);
							}
						}else if($flow['style']=='年假'){
							$flow_year = M('FlowYear')->where(array('flow_id'=>array('eq',$flow_id)))->find();
							if(!$flow_year){
								$del_half_year = $flow['day_num']*2+($flow['hour_num']>0?1:0);
								$flow = M('Flow')->find($flow_id);
								$data['hour'] = $del_half_year*(-1);
								$data['create_time'] = $create_time;
								$data['user_id'] = $flow['user_id'];
								$data['status'] = 2;
								M('FlowYear')->add($data);
							}else{
								$data['status'] = 2;
								M('FlowYear')->where('flow_id='.$flow_id)->save($data);
							}
						}
					}

					$this -> assign('jumpUrl', U('flow/folder?fid=confirm'));
					$this -> success('操作成功!');
				} else {
					//失败提示
					$this -> error('操作失败!');
				}
				break;
			default :
				break;
		}
	}

	public function approve() {

		$model = D("FlowLog");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$model -> result = 1;

		$flow_id = $model -> flow_id;
		$step = $model -> step;
		//保存当前数据对象
		$list = $model -> save();

		$model = D("FlowLog");
		$model -> where("step=$step and flow_id=$flow_id and result is null") -> setField('is_del', 1);

		if ($list !== false) {//保存成功
			D("Flow") -> next_step($flow_id, $step);
			$this -> assign('jumpUrl', U('flow/confirm'));
			$this -> success('操作成功!');
		} else {
			//失败提示
			$this -> error('操作失败!');
		}
	}

	public function reject() {
		$model = D("FlowLog");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$model -> result = 0;
		if (in_array('user_id', $model -> getDbFields())) {
			$model -> user_id = get_user_id();
		};
		if (in_array('user_name', $model -> getDbFields())) {
			$model -> user_name = get_user_name();
		};

		$flow_id = $model -> flow_id;
		$step = $model -> step;
		//保存当前数据对象
		$list = $model -> save();
		//可以裁决的人有多个人的时候，一个人评价完以后，禁止其他人重复裁决。
		$model = D("FlowLog");
		$model -> where("step=$step and flow_id=$flow_id and result is null") -> setField('is_del', 1);

		if ($list !== false) {//保存成功
			D("Flow") -> where("id=$flow_id") -> setField('step', 0);

			$user_id = M("Flow") -> where("id=$flow_id") -> getField('user_id');

			$this -> _pushReturn($new, "您有一个流程被否决", 1, $user_id);

			$this -> assign('jumpUrl', U('flow/confirm'));
			$this -> success('操作成功!');
		} else {
			//失败提示
			$this -> error('操作失败!');
		}
	}

	public function del(){
		$this->_del($_GET['id']);
	}
	public function down() {
		$this -> _down();
	}

	public function upload() {
		$this -> _upload();
	}

	protected function _assign_tag_list() {
		$model = D("SystemTag");
		$tag_list = $model -> get_tag_list('id,name', 'FlowType');
		$this -> assign("tag_list", $tag_list);
	}
	public function winpop_goods() {
		$node = M("GoodsCategory");
		$menu = array();
		$menu = $node -> where('is_del=0') -> field('id,pid,name') -> order('sort asc') -> select();
	
		$menu2 = array();
		$menu2 = M("Goods") -> where('is_del=0') -> field('id as goods_id,cate_id as pid,goods_name as name,market_price,spec') -> order('sort asc') -> select();
		if(empty($menu2)){
			$menu2 = array();
		}
		$tree = list_to_tree(array_merge($menu,$menu2));
		$this -> assign('menu', popup_menu($tree,0,100,array('goods_id','market_price','spec')));
		$this -> assign('sid', $_GET['id']);
		$this -> assign('pid', $pid);
		$this -> display();
	}
	public function _add_flow_index_log($flow,$flow_log=null){
		if(!is_array($flow) && !empty($flow)){
			$flow = array($flow);
		}
		$search = array_keys($flow,get_user_id());
		$flow_index = array();
		if(!empty($search) && is_array($search)){
			foreach ($search as $k=>$v){
				$flow_index[$v+1] = $v+1;
			}
		}
		
		$this -> assign("flow_index", $flow_index);
// 		dump($flow);
// 		die;
		// 		if($search !== false){//自己在哪个流程上(审核用，总经理的是否需要参加复试)
		// 			$this -> assign("flow_index", $search+1);
		// 			$this -> assign("flow_index_n", count($confirm_array)-$search-1);
		// 		}
		
		$flow_log_all = array();
		foreach ($flow as $k=>$v){//加上null
			if($v == null){
				$flow_log_all[$k] = $v;
			}
		}
		foreach ($flow_log as $v){
			foreach ($flow as $kk=>$vv){
				if($v['user_id'] == $vv){
					$flow_log_all[$kk] = $v;
				}
			}
		}
// 				var_dump($flow_log);
// 				var_dump($flow_index);
// 				dump($flow);
// 				var_dump($flow_log_all);
// 				die;
		$this -> assign("flow_log_all", $flow_log_all);
	}
	/*
	 * 获取流程相关信息
	 * 入参：流程名字，申请人信息等，审核流程（查看、审核流程时带上）
	 */
	public function _getFlowMessageByTypeName($flow_type_name,$flow_arr,$flow_log=null){
		//获取审核流程（加上重复的，加上空的）
		if($flow_type_name=='部门招聘需求申请'){
			$flow_message = $this->ajaxgetflow_employment($flow_arr,$flow_log);
		}else if($flow_type_name=='员工请假申请' || $flow_type_name=='外勤/出差申请'){
			$flow_message = $this->ajaxgetflow_leave($flow_arr,$flow_log);
			
			if(!is_array($flow_message['flow']) && !empty($flow_message['flow'])){
				$flow_message['flow'] = array($flow_message['flow']);
			}
		}else if($flow_type_name=='出勤异常申请'){
			$flow_message = $this->ajaxgetflow_attendance($flow_arr,$flow_log);
		}else if($flow_type_name=='加班申请'){
			$flow_message = $this->ajaxgetflow_over_time($flow_arr,$flow_log);
		}else if($flow_type_name=='员工离职申请'){
			$flow_message = $this->ajaxgetflow_resignation($flow_arr,$flow_log);
		}else if($flow_type_name=='试用期评估表'){
			$flow_message = $this->ajaxgetflow_probation($flow_arr,$flow_log);
		}else if($flow_type_name=='转正申请'){
			$flow_message = $this->ajaxgetflow_regular_work_application($flow_arr,$flow_log);
		}elseif ($flow_type_name=='员工调动申请'){
			$flow_message = $this->ajaxgetflow_personnel_changes($flow_arr,$flow_log);
		}elseif ($flow_type_name=='员工调薪申请'){
			$flow_message = $this->ajaxgetflow_salary_changes($flow_arr,$flow_log);
		}elseif ($flow_type_name=='物品采购调拨申请'){
			$flow_message = $this->ajaxgetflow_goods_procurement_allocation($flow_arr,$flow_log);
		}elseif ($flow_type_name=='名片申请'){
			$flow_message = $this->ajaxgetflow_card_application($flow_arr,$flow_log);
		}elseif ($flow_type_name=='内部联络单'){
			$flow_message = $this->ajaxgetflow_internal($flow_arr,$flow_log);
		}elseif ($vo['type']==66){
			$uid = $vo['user_id'];
			$parentId = getParentId(1);
			$flow_message = array($parentId);
			$this->_add_flow_index_log($flow,$flow_log);
		}
		return $flow_message;
	}
	//每隔流程做一个列表页
	function getlist(){
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		
		$type = $_REQUEST['type'];
		$flow_name = M('FlowType')->field('id,name')->find($type);
		$this -> assign("flow_name", $flow_name);
		//搜索条件预设
		$menu = array();
		$dept_menu = D("Dept") -> field('id,pid,name') -> where("is_del=0 and is_real_dept=1") -> order('sort asc') -> select();
		$dept_tree = list_to_tree($dept_menu);
		$this -> assign('dept_list', select_tree_menu($dept_tree));
		
		$user_name = M('Flow') -> where('is_del = 0') -> field('user_id as id,user_name as name') ->distinct(true) -> select();
		$this -> assign('user_name', $user_name);
		
		$content_array = array(
			'leave'=>'style',
			'outside'=>'outside_type',
		);
		$flow = M('Flow')->field('id')->where(array('type'=>$type))->find();
		$ModelName = getModelName($flow['id']);
// 		$flow_name = strtolower(substr($ModelName,4));
		$flow_name = strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_', substr($ModelName,4)));
		$this->_search_provide($ModelName,$content_array[$flow_name],$content_array[$flow_name]);
// 		$leave_style = M('FlowLeave') -> field('style as id,style as name') ->distinct(true) -> select();
// 		$this -> assign('leave_style', $leave_style);
		
// 		$outside_outside_type = M('FlowOutside') -> field('outside_type as id,outside_type as name') ->distinct(true) -> select();
// 		$this -> assign('outside_outside_type', $outside_outside_type);

		$node = D("Dept");
		$dept_menu = $node -> field('id,pid,name') -> where("is_del=0 and is_real_dept=1") -> order('sort asc') -> select();
		$dept_tree = list_to_tree($dept_menu);
		if(!is_mobile_request()){
			$this -> assign('dept_list_new', select_tree_menu_mul($dept_tree));
		}
		//搜索条件预设结束
		
		//搜索条件处理
// 		if($_REQUEST['eq_dept_id_0']){
// 			$where['dept_id'] = $_REQUEST['eq_dept_id_0'];
// 		}
// 		if($_REQUEST['eq_dept_id_1']){
// 			$pos_id = $_REQUEST['eq_dept_id_1'];
// 			$user_id_in = M('User')->field('id')->where(array('pos_id'=>$pos_id))->select();
// 			$user_id_in = rotate($user_id_in);
// 			$user_id_in = $user_id_in['id'];
// 			$where['user_id'] = array('in',$user_id_in);
			
// 		}
		if (!empty($_REQUEST['dept_name_multi_data'])) {
			$dept_id_mul = $_REQUEST['dept_name_multi_data'];
			$dept_id_mul = array_filter(explode('|',$dept_id_mul));
			$dept_ids = array();
			foreach ($dept_id_mul as $dept_id){
				$dept_ids = array_merge($dept_ids,get_child_dept_all($dept_id));
			}
			$where['dept_id'] = array('in', $dept_ids);
		}
		if (!empty($_REQUEST['pos_name_multi_data'])) {
			$pos_id_mul = $_REQUEST['pos_name_multi_data'];
			$pos_id_mul = array_filter(explode('|',$pos_id_mul));
			$pos_ids = array();
			foreach ($pos_id_mul as $pos_id){
				$pos_ids = array_merge($pos_ids,get_child_dept_all($pos_id));
			}
			$user_id_in = M('User')->field('id')->where(array('pos_id'=>array('in',$pos_ids)))->select();
			$user_id_in = rotate($user_id_in);
			$user_id_in = $user_id_in['id'];
			$where['user_id'] = array('in',$user_id_in);
		}
		if($_REQUEST['eq_user_id']){
			$where['user_id'] = $_REQUEST['eq_user_id'];
		}
		if($_REQUEST['li_user_name']){
			$where['user_name'] = array('like', '%'.$_REQUEST['li_user_name'].'%') ;
		}
		if($_REQUEST['be_create_time']){
			$where['create_time'][] = array('egt',strtotime($_REQUEST['be_create_time']));
		}
		if($_REQUEST['en_create_time']){
			$where['create_time'][] = array('elt',strtotime($_REQUEST['en_create_time'].' 24:00:00'));
		}
		
		//特殊条件
		$condition_array = array(
				'leave'=>'style',
				'outside'=>'outside_type',
				'resignation_list'=>'bt_resignation_time',
		);
		$where_in = $this->_getwherein($ModelName,$condition_array[$flow_name]);
		if($where_in!==false){
			$where['id'] = array('in',$where_in);
		}
// 		if($_POST['leave_style']){
// 			$where_leave['style'] = $_POST['leave_style'];
// 		}
// 		if($_POST['outside_outside_type']){
// 			$where_outside['outside_type'] = $_POST['outside_outside_type'];
// 		}
		
// 		if(!empty($where_leave)){
// 			$flow_id_in = M('FlowLeave')->field('flow_id')->where($where_leave)->select();
// 			$flow_id_in = rotate($flow_id_in);
// 			$flow_id_in = $flow_id_in['flow_id'];
// 			$where['id'] = array('in',$flow_id_in);
// 		}
// 		if(!empty($where_outside)){
// 			$flow_id_in = M('FlowOutside')->field('flow_id')->where($where_outside)->select();
// 			$flow_id_in = rotate($flow_id_in);
// 			$flow_id_in = $flow_id_in['flow_id'];
// 			$where['id'] = array('in',$flow_id_in);
// 		}
		if(!empty($where)){
			$map['_complex'] = $where;
		}
		
		$map['type'] = $type;
		
		$auth = $this -> config['auth'];
		$this -> assign('auth', $auth);
		if (!$auth['admin']) {
			$flow_me = M('Flow')->field('id')->where(array('user_id'=>get_user_id()))->select();
			if(empty($flow_me)){
				$flow_me = array();
			}else{
				$flow_me = rotate($flow_me);
				$flow_me = $flow_me['id'];
			}
			$flow_to_me = M('FlowLog')->field('flow_id')->distinct(true)->where(array('user_id'=>get_user_id(),'_complex'=>'result is null'))->select();
			if(empty($flow_to_me)){
				$flow_to_me = array();
			}else{
				$flow_to_me = rotate($flow_to_me);
				$flow_to_me = $flow_to_me['flow_id'];
			}
			$map['id'] = array('in',array_merge($flow_me,$flow_to_me));
		}
		
		$map['is_del'] = 0;
// 		dump($map);
// 		dump(array_merge($flow_me,$flow_to_me));
		if($_GET['export']=='1'){
			$flow_common = M('Flow')->where($map)->order('id desc')->select();
		}else{
			$flow_common = $this->_list(M('Flow'), $map);
		}
		$flow_ext = array();
		foreach ($flow_common as $k=>$v){
			$model_name = getModelName($v['id']);
			$flow_ext[$k] = M($model_name)->where(array('flow_id'=>$v['id']))->find();
			$pos_id = M('User')->field('pos_id')->find($v['user_id']);
			$pos_name = M('Dept')->field('name')->find($pos_id['pos_id']);
			$flow_ext[$k]['pos_name'] =$pos_name['name'];
		}
// 		dump($flow_ext);
// 		$flow = M('Flow')->where(array('type'=>$type,'user_id'=>get_user_id()))->select();
		$this -> assign("flow_ext", $flow_ext);
		$this -> assign("user_id", get_user_id());
		

		$this -> assign("post", json_encode($_POST));
		if($_GET['export']=='1'){
			$this->export_excel($flow_common,$flow_ext,$_GET['line1']);
		}else{
			$this -> display();
		}
	}
	
	function _search_provide($ModelName,$field_id,$field_name){
// 		$flow = M('Flow')->field('id')->where(array('type'=>$flow_type))->find();
// 		$ModelName = getModelName($flow['id']);
		
		$flow_name = strtolower(substr($ModelName,4));
		$res = M($ModelName) -> field($field_id.' as id,'.$field_name.' as name') ->distinct(true) -> select();
		if(!empty($res)){
			$this -> assign($flow_name.'_'.$field_id, $res);
		}
	}
	function _getwherein($ModelName,$field_name){
// 		$flow_name = strtolower(substr($ModelName,4));
		$flow_name = strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_', substr($ModelName,4)));
// 		dump($flow_name);
// 		dump(substr($field_name,0));
		if(substr($field_name,0,3)=='bt_'){
			$field_name = substr($field_name,3);
			if($_REQUEST['be_'.$flow_name.'_'.$field_name]){
				$where[$field_name][] = array('egt',$_REQUEST['be_'.$flow_name.'_'.$field_name]);
			}
			if($_REQUEST['en_'.$flow_name.'_'.$field_name]){
				$where[$field_name][] = array('elt',$_REQUEST['en_'.$flow_name.'_'.$field_name]);
			}
			if(!empty($where)){
				$flow_id_in = M($ModelName)->field('flow_id')->where($where)->select();
				$flow_id_in = rotate($flow_id_in);
				$flow_id_in = $flow_id_in['flow_id'];
				return $flow_id_in;
			}
		}else{
			if($_REQUEST[$flow_name.'_'.$field_name]){
				$where[$field_name] = $_REQUEST[$flow_name.'_'.$field_name];
			}
			if(!empty($where)){
				$flow_id_in = M($ModelName)->field('flow_id')->where($where)->select();
				$flow_id_in = rotate($flow_id_in);
				$flow_id_in = $flow_id_in['flow_id'];
				return $flow_id_in;
			}
		}
		return false;
	}
	function export_excel($flow_common,$flow_ext,$line1){
		//dump($flow_common);
		//dump($flow_ext);
		//$str = 'return date("ym","1473477463");';
		//$a = eval($str);
		//echo $a;
		//die;
		$line1 = array_map(trim,array_filter(explode('|',$line1)));
		array_pop($line1);
		
		$nametofield = array(
			'标题'=>'return $v["name"];',
			'编号'=>'return date("ym",$v["create_time"]).formatto4w($flow_ext[$k]["id"]);',
			'申请时间'=>'return date("Y-m-d H:i:s",$v["create_time"]);',
			'部门'=>'return $v["dept_name"];',
			'岗位'=>'return $flow_ext[$k]["pos_name"];',
			'申请人'=>'return $v["user_name"];',
			'时长'=>'return $flow_ext[$k]["day_num"]."天".$flow_ext[$k]["hour_num"]."小时";',
			'开始时间'=>'return $flow_ext[$k]["start_time"];',
			'结束时间'=>'return $flow_ext[$k]["end_time"];',
			'审批状态'=>'return show_step($v["step"]);',
			'申请岗位'=>'return $flow_ext[$k]["apply_position"];',
			'请假类型'=>'return $flow_ext[$k]["style"];',
			'请假时长'=>'return $flow_ext[$k]["day_num"]."天".$flow_ext[$k]["hour_num"]."小时";',
			'请假开始时间'=>'return $flow_ext[$k]["start_time"];',
			'请假结束时间'=>'return $flow_ext[$k]["end_time"];',
			'外勤/出差'=>'return $flow_ext[$k]["outside_type"];',
			'天数'=>'return $flow_ext[$k]["day_num"]."天".$flow_ext[$k]["hour_num"]."小时";',
			'出发时间'=>'return $flow_ext[$k]["start_time"];',
			'结束时间'=>'return $flow_ext[$k]["end_time"];',
			'加班时长'=>'return $flow_ext[$k]["day_num"]."天".$flow_ext[$k]["hour_num"]."小时";',
			'加班开始时间'=>'return $flow_ext[$k]["start_time"];',
			'加班结束时间'=>'return $flow_ext[$k]["end_time"];',
			'同意转正日期'=>'return $flow_ext[$k]["hr_execute"];',
			'离职日期'=>'return $flow_ext[$k]["resignation_time"];',
		);
		$line_new = array();
		foreach($line1 as $k=>$v){
			$line_new[$k] = $nametofield[$v];
		}
		
		//dump($line_new);
		Vendor('Excel.PHPExcel');
	
		$objPHPExcel = new PHPExcel();
	
		$objPHPExcel -> getProperties() -> setCreator("小微OA") -> setLastModifiedBy("小微OA") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
		// Add some data
		$i = 1;
	
		$q = $objPHPExcel -> setActiveSheetIndex(0);
		foreach($line1 as $k=>$v){
			$col = ToNumberSystem26($k+1);
			$q = $q -> setCellValue($col."1", $v);
		}
		foreach($flow_common as $k=>$v){
			$j = $k+2;
			foreach($line_new as $kk=>$vv){
				$col = ToNumberSystem26($kk+1);
				if($vv){
					$q = $q -> setCellValue($col.$j, eval($vv));
				}
				
			}
		}
	
		// Rename worksheet
		$title = '流程导出';
		$objPHPExcel -> getActiveSheet() -> setTitle('流程导出');
	
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel -> setActiveSheetIndex(0);
		$file_name = $title.".xlsx";
		// Redirect output to a client’s web browser (Excel2007)
		header("Content-Type: application/force-download");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition:attachment;filename =" . str_ireplace('+', '%20', URLEncode($file_name)));
		header('Cache-Control: max-age=0');
	
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		//readfile($filename);
		$objWriter -> save('php://output');
		exit ;
		die;
		
	}
	/**
	 * 当审批最后一个人确认的时候就添加一条考勤记录
	 * @param integer $flow_id
	 */
	private function addAttendance($flow_id){
		//请假调休结束后,向考勤表中添加一条记录
		$user_flow = M('Flow')->find($flow_id);//找到请假人的信息
		$atten = M('Attendance');
		$info['user_id'] = $user_flow['user_id'];
		$info['dept_name'] = $user_flow['dept_name'];
		$info['user_name'] = $user_flow['user_name'];
		$info['num'] =  '';
		$info['machine_no'] = '1';
		$info['import_time'] = time();
		$flag = getModelName($flow_id);
		switch ($flag){
			case 'FlowLeave' :	//leave; 请假调休
				$flow = M('FlowLeave')->where(array('flow_id'=>array('eq',$flow_id)))->find();
				$create_time = strtotime($flow['start_time']);
				$end_time = strtotime($flow['end_time']);
				$remark = '请假调休单_' .$flow['style']."(".$flow['day_num'].'天'.$flow['hour_num']."小时)";
				$this -> getAttendanceInfo($create_time,$end_time,$info,$remark,$user_flow['user_id']);
				break;
			case 'FlowAttendance' : //attendance 出勤单
				$flow = M('FlowAttendance')->where(array('flow_id'=>array('eq',$flow_id)))->find();
				$create_time = strtotime($flow['start_time']);
				$end_time = strtotime($flow['end_time']);
				$info['attendance_time'] = $create_time;
				$remark = '出勤异常申请'.'('.$flow['day_num'].'天'.$flow['hour_num'].'小时)';
				$this -> getAttendanceInfo($create_time,$end_time,$info,$remark,$user_flow['user_id']);
				break;
			case 'FlowOutside' : //outside 外勤单
				$flow = M('FlowOutside')->where(array('flow_id'=>array('eq',$flow_id)))->find();
				$create_time = strtotime($flow['start_time']);
				$end_time = strtotime($flow['end_time']);
				$info['attendance_time'] = $create_time;
				$remark = '外勤/出差申请'.'('.$flow['day_num'].'天'.$flow['hour_num'].'小时)';
				$this -> getAttendanceInfo($create_time,$end_time,$info,$remark,$user_flow['user_id']);
				break;
		}
	}
	/**
	 * 获取并修改和添加动态考勤记录信息 
	 */
	private function getAttendanceInfo($create_time,$finish_time,$info,$remark,$user_id){
		$atten = M('Attendance');
		$where['is_del'] = 0;
		$where['user_id'] = $user_id;
		//$where['mark'] = array('in',array('in','out'));
		$start_time = strtotime(date('Y-m-d',$create_time));
		$end_time = strtotime(date('Y-m-d',$create_time)) + (3600*24-1);
		$where['attendance_time'] = array('between',array($start_time,$end_time));
		$res1 = $atten -> where($where)->order('attendance_time asc') -> select();
		//申请人当前时间(申请那天)已经有打卡信息了
		if(!empty($res1)){
			$count = count($res1);
			$d_start = $res1[0]['attendance_time'];
			$d_start_id = $res1[0]['id'];
			$d_end = $res1[$count-1]['attendance_time'];
			$d_end_id = $res1[$count-1]['id'];
			if($create_time<$d_start){
				$atten->where(array('id'=>$d_start_id,'mark'=>'in','is_del'=>0))->setField('mark','');
				$info['mark'] = 'in';
			}
			$info['style'] = $remark;
			$info['attendance_time'] = $create_time;
			$atten -> add($info);
			if($finish_time>$d_end){
				$atten->where(array('id'=>$d_end_id,'mark'=>'out','is_del'=>0))->setField('mark','');
				$info['mark'] = 'out';
			}
			$info['attendance_time'] = $finish_time;
			$atten -> add($info);
		}else{
			//开始时间
			$info['mark'] = 'in';
			$info['style'] = $remark;
			$info['attendance_time'] = $create_time;
			$atten -> add($info);
			//结束时间
			$info['mark'] = 'out';
			$info['attendance_time'] = $finish_time;
			$atten -> add($info);
		}
	}
}

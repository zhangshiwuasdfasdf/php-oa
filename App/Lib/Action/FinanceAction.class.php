<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class FinanceAction extends CommonAction {
	protected $config = array('app_type' => 'common', 'action_auth' => array('add_income' => 'write', 'add_payment' => 'write', 'add_transfer' => 'write', 'save_transfer' => 'write', 'account_list' => 'admin', 'add_account' => 'admin', 'read_account' => 'write', 'save_account' => 'admin', 'edit_account' => 'admin', 'del_account' => 'admin'));

	//过滤查询字段
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['keyword']) && empty($map['64'])) {
			$map['name'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}

	public function index() {
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign('auth', $this -> config['auth']);

		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}

		$model = D("FinanceView");
		if (!empty($model)) {
			$this -> _list($model, $map);
		}
		$this -> display();
	}

	public function add_income() {
		$widget['date'] = true;
		$widget['uploader'] = true;

		$account_list = M("FinanceAccount") -> where('is_del=0') -> getField("id,name");
		$this -> assign('account_list', $account_list);

		$customer_list = M("Customer") -> where('is_del=0') -> getField("name id,name");
		$this -> assign('customer_list', $customer_list);

		$this -> assign("widget", $widget);
		$this -> display();
	}

	public function add_payment() {
		$widget['date'] = true;
		$widget['uploader'] = true;

		$account_list = M("FinanceAccount") -> where('is_del=0') -> getField("id,name");
		$this -> assign('account_list', $account_list);

		$supplier_list = M("Supplier") -> where('is_del=0') -> getField("name id,name");
		$this -> assign('supplier_list', $supplier_list);

		$this -> assign("widget", $widget);
		$this -> display();
	}

	public function add_transfer() {
		$widget['date'] = true;
		$widget['uploader'] = true;

		$account_list = M("FinanceAccount") -> where('is_del=0') -> getField("id,name");
		$this -> assign('account_list', $account_list);

		$customer_list = M("Customer") -> where('is_del=0') -> getField("name id,name");
		$this -> assign('customer_list', $customer_list);

		$this -> assign("widget", $widget);
		$this -> display();
	}

	public function save_transfer() {
		
		$account_id_payment = $_REQUEST['account_id_payment'];
		$account_id_income = $_REQUEST['account_id_income'];

		$account_list = M("FinanceAccount") -> getField('id,name');

		$account_name_payment = $account_list[$account_id_payment];
		$account_name_income = $account_list[$account_id_income];

		$money = $_REQUEST['money'];

		$remark_income = "由[$account_name_payment]转入[$money]";
		$remark_payment = "向[$account_name_income]转出[$money]";

		
		$data['doc_no'] = $_REQUEST['doc_no'];
		$data['input_date'] = $_REQUEST['input_date'];
		$data['type'] = "转账";
		$data['actor_user_name'] = $_REQUEST['actor_user_name'];
		$data['doc_type']=3;

		$data_payment = $data;
		$data_income = $data;
		
	 
		$data_payment['account_id'] = $account_id_payment;
		$data_payment['payment'] = $money;
		$data_payment['remark'] = $remark_payment;

		$data_income['account_id'] = $account_id_income;
		$data_income['income'] = $money;
		$data_income['remark'] = $remark_income;
		
		
		$model = D("Finance");
		/*保存当前数据对象 */
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$model->account_id = $account_id_payment;
		$model->payment = $money;
		$model->remark = $remark_payment;	
					
		$list = $model -> add();		
		
		$model = D("Finance");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}		
		
		$model->account_id = $account_id_income;
		$model->income = $money;
		$model->remark = $remark_income;	
		
		$list = $model -> add();
		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('新增成功!');
		} else {
			$this -> error('新增失败!');
			//失败提示
		}
	}

	public function edit() {
		$widget['editor'] = true;
		$widget['date'] = true;
		$widget['uploader'] = true;
		$this -> assign("widget", $widget);

		$id = $_REQUEST['id'];

		if ($this -> isAjax()) {
			if ($vo !== false) {// 读取成功
				$this -> ajaxReturn($vo, "", 0);
			} else {
				die ;
			}
		}
		$this -> assign('vo', $vo);
		$this -> display();

	}

	function account_list() {
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		$this -> assign('auth', $this -> config['auth']);

		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}

		$model = D("FinanceAccount");
		if (!empty($model)) {
			$this -> _list($model, $map);
		}
		$this -> display();
	}

	function add_account() {
		$this -> display();
	}

	function read_account() {
		$this -> edit_account();
	}

	function edit_account() {
		$account_id = $_REQUEST['account_id'];
		$this -> _edit("FinanceAccount", $account_id);
	}

	function save_account() {
		$this -> _save("FinanceAccount");
	}

	function del_account() {
		$account_id = $_REQUEST['account_id'];
		$this -> _del($account_id, "FinanceAccount");
	}

	function upload() {
		$this -> _upload();
	}

	function down() {
		$this -> _down();
	}

}

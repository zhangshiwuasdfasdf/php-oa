<?php
class ProcessNodeAction extends CommonAction {
	function _search_filter(&$map) {
		$map['is_del'] = array('eq','0');
}
	//����ҳ��
	function index(){
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$fid = $_REQUEST['fid'];
		$type = $_REQUEST['type'];
		//���ͨ������
		$tid = M('FlowTypeSetting') -> where(array('flow_name'=>'ͨ��','is_del'=>'0')) -> getField('id');
		//�ҳ����̵İ汾
		$vid = M("FlowVersion") -> where(array('flow_type_setting_id' => array('in',array($fid,$tid)),'is_del'=>'0','status'=>'1')) -> getField('id',true);
		//�汾�еĽڵ�
		$node = M("FlowNode") -> where(array('flow_version_id'=>array('in',$vid),'is_del'=>'0'))->select();
		$this -> assign('list',$node);
		/**
		 * ��ǰ�������������Ϣ
		 */
		$info = M('FlowConfigDetail') -> where(array('flow_config_id'=>$fid,'type'=>$type,'is_del'=>'0')) -> select();
		foreach ($info as $k => $v){
			$sheet_info[$v['sheet_id']][] = $v; 
		}
		$this -> assign('pageCount',$sheet_info);
		$this -> assign('fid',$fid);
		$this -> assign('type',$type);
		$this->display();
	}
	
	//��ӻ����޸�������
	function editProcess(){
		$model = M('FlowConfigDetail');
		$data['node_condition_id'] = $_POST['fnode'];
		$data['flow_config_id'] = $_POST['fid'];
		$data['type'] = $_POST['type'];
		$data['sheet_condition_id'] = $_POST['fenzhi'];
		$data['id'] = $_POST['id'];
		$data['step'] = $_POST['step'];
		$data['is_remind'] = $_POST['remind'];
		$data['is_merge'] = $_POST['merge'];
		$data['sheet_id'] = $_POST['sheet'];
		if($data['id']){
			
		}else{
			$model ->add($data);
			$this->ajaxReturn($data,'��ӳɹ�',1);
		}
	}
	//��ά����ĳһ������ֵ�����ظ���ɾ���ظ���
	private function assoc_unique($arr, $key) {
		$tmp_arr = array();
		foreach ($arr as $k => $v) {
			if (in_array($v[$key], $tmp_arr)) {//����$v[$key]�Ƿ���$tmp_arr�����д��ڣ������ڷ���true
				unset($arr[$k]);
			} else {
				$tmp_arr[] = $v[$key];
			}
		}
		sort($arr); //sort�����������������
		return $arr;
	}
}
?>
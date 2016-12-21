<?php
class WorkAgentAction extends CommonAction {
	/*function _search_filter(&$map) {
		$fid=$_REQUEST['fid'];
		$map['is_del'] = array('eq','0');
		$map['fid'] = array('eq',$fid);
		if (!empty($_POST['li_company_id'])) {
			$map['company_id'] = array('eq',$_POST['li_company_id']);
		}
		if (!empty($_POST['li_version'])) {
			$map['version'] = array('eq',$_POST['li_version']);
		}
 		//dump($map);die;
}*/

	function index(){
		
		$this->display();
	}
	
//工作委托
function agency(){
	$current_client=$_POST['current_client'];
	$to_client=$_POST['to_client'];
	$where['step']=array('eq','20');
	$where['is_del']=array('eq','0');
	$where['confirm_name']=array('like','%'.$current_client.'%');
	$data=M("Flow")->field("id,confirm_name")->where($where)->select();
	foreach($data as $k=>$v){
		$name=array_filter(explode('<>',$v['confirm_name']));
		foreach($name as $k1=>$v1){
			if($v1==$current_client){
				$v1=$to_client;
			}
			$name[$k1]=$v1;
		}
		$confirm_name=implode('<>',$name);
		$res=M("Flow")->where(array('id'=>$v['id']))->save(array('confirm_name'=>$confirm_name));
	}
	dump($res);die;
	if($res){
		$this->success('委托成功！', U('index'));
    	exit;
	}	
}
function suggest(){
	$where['name']=array("like","%".$_POST['queryString']."%");
	$where['is_del']=array("eq","0");
	$data=M("user")->field("name")->where($where)->select();
	foreach($data as $k=>$v){
		$value=$v['name'];
		echo '<li onClick="fill(\''.$value.'\');">'.$value.'</li>';
	}
	//$this->ajaxReturn($data,'success','1');
}

}
?>
<?php
class WorkAgentAction extends CommonAction {
	function index(){
		
		$this->display();
	}
	
//工作委托
function agency(){
	$current_client=$_POST['current_client'];
	$current_client_emp_no=M("User")->where(array('name'=>$current_client))->getField("emp_no");
	$to_client=$_POST['to_client'];
	$to_client_emp_no=M("User")->where(array('name'=>$to_client))->getField("emp_no");
	$where['step']=array('eq','20');
	$where['is_del']=array('eq','0');
	$where['confirm_name']=array('like','%'.$current_client.'%');
	$data=M("Flow")->field("id,confirm,confirm_name")->where($where)->select();
	dump($data);
	foreach($data as $k=>$v){
		$name=explode('<>',$v['confirm_name']);
		$confirm=explode('|',$v['confirm']);
		foreach($name as $k1=>$v1){
			if($v1==$current_client){
				$v1=$to_client;
			}
			//替换
			$name[$k1]=$v1;
		}
		foreach($confirm as $k2=>$v2){
			if($v2==$current_client_emp_no){
				$v2=$to_client_emp_no;
			}
			//替换
			$confirm[$k2]=$v2;
		}
		$confirm_name=implode('<>',$name);
		$confirm=implode('|',$confirm);
		$id=$v['id'];
		$where2['flow_id']=array('eq',$v['id']);
		$where2['user_name']=array('eq',$current_client);
		$where2['result']=array('EXP','is null');  
		//dump($confirm);
		//dump($confirm_name);
		
	}
	die;
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
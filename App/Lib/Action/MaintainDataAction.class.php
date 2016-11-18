<?php
class MaintainAction extends CommonAction {
	
	public function data(){
		$menu_id=I("post.id");
        $data=M("RRoleMenu")
        ->field('a.id,a.menu_id,a.role_id,b.id,b.company,b.role_name')
        ->alias('a')
        ->join('LEFT JOIN __ROLE_MANAGER__  b ON a.role_id=b.id')
        ->where(array('menu_id' => $menu_id ))->distinct(true)->select();
        $company=array();
		foreach($data as $k=>$v){
			$company[$v['company']][$v['id']]=$v['role_name'];
			$data['_company']=$company;
		}
		
		$this->assign('data',$data);
		$this->display('data');
		//$this->ajaxReturn($data,'success','1');
		dump($data);die;
	}
	
}
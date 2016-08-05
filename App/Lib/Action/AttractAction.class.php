<?php
class AttractAction extends CommonAction {
	protected $config = array('app_type' => 'personal');
	
	function index (){
		$this -> display();	
	}
	
	public function down() {
		$this -> _down();
	}
	function import_client(){
		$opmode = $_POST['opmode'];
		if($opmode == 'import'){
			
		}else{
			$this -> display();
		}
	}

}
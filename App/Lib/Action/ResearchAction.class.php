<?php
class ResearchAction extends CommonAction {
	function index(){
		$model = D('User_suggest');
		if (!empty($model)) {
			$res = $this -> _list($model, $map);
		}
		
		$dayNew = 0;$dayOld = 0;$monNew = 0;$monOld = 0;$wekNew = 0;$wekOld = 0;
		foreach ($res as $k => $v){
			$tmp = explode('|',$v['sustain']);
			if($tmp[0] == '1'){$dayNew ++; }elseif($tmp[0] == '0'){$dayOld++;}
			if($tmp[1] == '1'){$monNew ++; }elseif($tmp[1] == '0'){$monOld++;}
			if($tmp[2] == '1'){$wekNew ++; }elseif($tmp[2] == '0'){$wekOld++;}
		}
		$info = array('dn' => $dayNew,'do' => $dayOld , 'mn' => $monNew ,'mo' => $monOld ,'wn' => $wekNew , 'wo' => $wekOld);
		$this -> assign('res',$info);
		$this -> display();
	}
	
	function add(){
		$this -> display();
	}
	//上传图片
	function save_order(){
		$this -> upload();
	}
	
	Public function upload(){
	import("@.ORG.Util.UploadFile");
	$upload = new UploadFile();// 实例化上传类
	$upload->maxSize  = 3145728 ;// 设置附件上传大小
	$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
	$upload -> savePath = get_save_path() .'research/';// 设置附件上传目录
	$upload -> saveRule = "com_create_guid";
	if(!$upload->upload()) {// 上传错误提示错误信息
		$this->error($upload->getErrorMsg());
	}else{// 上传成功 获取上传文件信息
		$info =  $upload->getUploadFileInfo();
		$model = M("File");
		$suggest = M("User_suggest_detail");
		$maxNow = $suggest -> max('now');
		$fid['now'] = $maxNow + 1;
		foreach ($info as $k=>$v){
			$model -> create($info[$k]);
			$model -> savename = $v['savepath'] . $v['savename'];
			$model -> create_time = time();
			$model -> user_id = get_user_id();
			$model -> module = MODULE_NAME;
			$fid['fid'] = $model -> add();
			$fid['flag'] = $k + 1;
			$tmp = $suggest -> add($fid);
		}
		$this->success('数据保存成功！');
		}
	}
}
<?php
class FileWidget extends Widget {
	public function render($data) {		
		$add_file=$data['add_file'];
		if(!empty($add_file)){			
			$files = array_filter(explode(';', $add_file));
			$where['sid'] = array('in', $files);
			$model = M("File");
			$file_list = $model -> where($where) -> select();
			foreach ($file_list as $k=>$v){
				$file_list[$k]['extension'] = strtolower($v['extension']);
			}
			$data['file_list']=$file_list;
		}
		$mode=$data['mode'];
		if(!empty($mode)){
			switch ($mode) {
					case 'add':
						$content = $this->renderFile('add',$data);	
						break;
					case 'edit':
						$content = $this->renderFile('edit',$data);
						break;
					case 'show':
						$content = $this->renderFile('show',$data);
						break;
					case 'resume':
						$content = $this->renderFile('resume',$data);
						break;
					case 'image':
						$content = $this->renderFile('image',$data);
						break;					
					default:
						$content = $this->renderFile('show',$data);						
						break;
				}
			}
		return $content;
	}
}
?>
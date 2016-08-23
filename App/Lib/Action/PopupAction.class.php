<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/
header("Access-Control-Allow-Origin:*");
/*星号表示所有的域都可以接受，*/
header("Access-Control-Allow-Methods:GET,POST");
class PopupAction extends CommonAction {
	protected $config = array('app_type' => 'asst');
	//过滤查询字段
	private $position;
	private $rank;
	private $dept;

	function _search_filter(&$map) {
		$map['name'] = array('like', "%" . $_POST['name'] . "%");
		$map['letter'] = array('like', "%" . $_POST['letter'] . "%");
		$map['is_del'] = array('eq', '0');
		if (!empty($_POST['group'])) {
			$map['group'] = $_POST['group'];
		}
		$map['user_id'] = array('eq', get_user_id());
	}

	function read(){
		$type = $_REQUEST['type'];
		$id = $_REQUEST['id'];
		switch ($type){
			case "company" :
				$model = M("Dept");
				$dept = tree_to_list(list_to_tree( M("Dept")->where('is_del=0') -> select(), $id));
				$dept = rotate($dept);
				$dept = implode(",", $dept['id']) . ",$id";

			case "emp" :
				$model = M("Dept");
				$dept = tree_to_list(list_to_tree(M("Dept")->where('is_del=0') -> select(), $id));
				$dept = rotate($dept);
				$dept = implode(",", $dept['id']) . ",$id";

				$sql = D("UserView") -> buildSql();
				$model = new Model();
				$where['dept_id'] = array('in', $dept);
				$where['is_del']=array('eq',0);
				$data = $model -> table($sql . "a") -> where($where) -> select();
				break;
			
			case "notice" :
				$model = M("Dept");
				$data = tree_to_list(list_to_tree( M("Dept")->where(array('is_del'=>0,'is_real_dept'=>1)) -> select(), $id));
				break;

			case "rank" :
				$sql = D("UserView") -> buildSql();
				$model = new Model();
				$where['rank_id'] = array('eq', $id);
				$where['is_del']=array('eq',0);
				$data = $model -> table($sql . "a") -> where($where) -> select();
				break;

			case "position" :
				$sql = D("UserView") -> buildSql();
				$model = new Model();
				$where['position_id'] = array('eq', $id);
				$where['is_del']=array('eq',0);
				$data = $model -> table($sql . "a") -> where($where) -> select();
				break;
			case "personal" :
				$model = D("UserTag");
				if ($id == "#") {
					$data = $model -> get_data_list("Contact");
					$data = rotate($data);
					$data = $data['row_id'];
					$where['id'] = array('not in', implode(",", $data));
				} else {
					$test=$model;
					$data = $model -> get_data_list("Contact",$id);
					$data = rotate($data);
					
					$data = $data['row_id'];
					$where['id'] = array('in', implode(",", $data));
				}
				$model = M("Contact");
				$where['is_del']=array('eq',0);
				$data = $model -> where($where) -> field('id,name,position as position_name,email') -> select();
				//echo $model->getLastSql();
				break;
			default :
		}
		$new = array();
		if (true) {// 读取成功
			$this -> ajaxReturn($data,dump($test,false), 1);
		}
	}

	function contact() {
		$widget['jquery-ui'] = true;		
		$this -> assign("widget", $widget);
		
		$model = M("Dept");
		$list = array();
		$list = $model->where('is_del=0') -> field('id,pid,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_company', popup_tree_menu($list,0,4));

		$model = M("Rank");
		$list = array();
		$list = $model -> field('id,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_rank', popup_tree_menu($list));

		$model = M("Position");
		$list = array();
		$list = $model -> field('id,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_position', popup_tree_menu($list));

		$model = D("UserTag");

		$tag_list = $model -> get_tag_list("id,name","Contact");
		$tag_list['#'] = "未分组";
		$this -> assign("list_personal", $tag_list);

		$this -> assign('type', 'company');
		$this -> display();
		return;
	}

	function auth() {
		$widget['jquery-ui'] = true;		
		$this -> assign("widget", $widget);
				
		$model = M("Dept");
		$list = array();
		$list = $model->where('is_del=0') -> field('id,pid,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_company', popup_tree_menu($list,0,4));

		$model = M("Rank");
		$list = array();
		$list = $model -> field('id,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_rank', popup_tree_menu($list));

		$model = M("Position");
		$list = array();
		$list = $model -> field('id,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_position', popup_tree_menu($list));

		$this -> assign('type', 'company');
		$this -> display();
		return;
	}
	
	function noticeauth() {
		$widget['jquery-ui'] = true;		
		$this -> assign("widget", $widget);
				
		$model = M("Dept");
		$list = array();
		$list = $model->where('is_del=0') -> field('id,pid,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_company', popup_tree_menu($list,0,4));

		$model = M("Rank");
		$list = array();
		$list = $model -> field('id,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_rank', popup_tree_menu($list));

		$model = M("Position");
		$list = array();
		$list = $model -> field('id,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_position', popup_tree_menu($list));

		$this -> assign('type', 'notice');
		$this -> display();
		return;
	}

	function upload(){
		$this -> _upload();
	}
	function mobile_pic_upload(){
			$picname = $_FILES['mypic']['name'];
			$picsize = $_FILES['mypic']['size'];
			$user_pic_pre = $_REQUEST['user_pic_pre']?$_REQUEST['user_pic_pre']:'';
			if ($picname != "") {
				if ($picsize > 5*1024*1024) { //限制上传大小
					echo '图片大小不能超过5MB';
					exit;
				}
				$type = strstr($picname, '.'); //限制上传格式
				if ($type != ".gif" && $type != ".jpg" && $type != ".jpeg" && $type != ".png") {
					echo '图片格式不对！';
					exit;
				}
				$rand = rand(100, 999);
				$pics = $user_pic_pre.$_REQUEST['id'] . $type; //命名图片名称
				
				//上传路径
				$pic_path = "./Data/Files/emp_pic/". $pics;
				
				$res = move_uploaded_file($_FILES['mypic']['tmp_name'], $pic_path);
				if($res){
					$data['id'] = intval($_REQUEST['id']);
					$data[$user_pic_pre.'pic'] = 'emp_pic/'.$pics;
					
					M('User')->save($data);
					$size = round($picsize/1024,2); //转换成kb
					$arr = array(
							'status'=>'1',
							'name'=>$picname,
							'pic'=>$pics,
							'size'=>$size,
							'path'=>$pic_path
					);
					echo json_encode($arr); //输出json数据
				}
				
			}
			

	}

	function emp() {
		$this -> auth();
	}

	function avatar(){
		$id=$_REQUEST['id'];
		$this->assign("id",$id);
		$this->assign("pic",M("User")->where("id=$id")->getField('pic'));
		$this->display();
	}
	
	function depts(){
		$widget['jquery-ui'] = true;		
		$this -> assign("widget", $widget);
				
		$model = M("Dept");
		$list = array();
		$list = $model ->where('is_del=0')->field('id,pid,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_dept', sub_tree_menu($list));
		$this -> display();
		return;
	}
		
	function actor() {
		$widget['jquery-ui'] = true;		
		$this -> assign("widget", $widget);
		
		$model = M("Dept");
		$list = array();
		$list = $model->where('is_del=0') -> field('id,pid,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_company', popup_tree_menu($list,0,4));

		$model = M("Rank");
		$list = array();
		$list = $model -> field('id,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_rank', popup_tree_menu($list));

		$model = M("Position");
		$list = array();
		$list = $model -> field('id,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_position', popup_tree_menu($list));

		$this -> assign('type', 'company');
		$this -> display();
		return;
	}

	function task(){
		$this->actor();
	}

	function confirm() {

		$widget['jquery-ui'] = true;		
		$this -> assign("widget", $widget);
		
		$model = M("Dept");
		$list = array();
		$list = $model->where('is_del=0') -> field('id,pid,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_company', popup_tree_menu($list));

		$model = M("Rank");
		$list = array();
		$list = $model -> field('id,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_rank', popup_tree_menu($list));

		$model = M("Position");
		$list = array();
		$list = $model -> field('id,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_position', popup_tree_menu($list));

		$this -> assign('type', 'company');
		$this -> display();
		return;
	}

	function recever(){
		$this->actor();
	}

	function flow() {

		$widget['jquery-ui'] = true;		
		$this -> assign("widget", $widget);
				
		$model = M("DeptGrade");
		$list = array();
		$list = $model -> field('id,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_dept_grade', sub_tree_menu($list));

		$model = M("Dept");
		$list = array();
		$list = $model ->where('is_del=0')-> field('id,pid,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_dept', sub_tree_menu($list));

		$model = M("Position");
		$list = array();
		$list = $model -> field('id,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_position', sub_tree_menu($list));

		$this -> assign('type', 'dgp');
		$this -> display();
		return;
	}

	function popup_depts() {
		$widget['jquery-ui'] = true;		
		$this -> assign("widget", $widget);
				
		$model = M("Dept");
		$list = array();
		$list = $model ->where('is_del=0')-> field('id,pid,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_dept', sub_tree_menu($list));
		$this -> display();
		return;
	}

	function position() {
		$widget['jquery-ui'] = true;		
		$this -> assign("widget", $widget);
				
		$model = M("Position");
		$list = array();
		$list = $model -> field('id,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_position', sub_tree_menu($list));
		$this -> display();
		return;
	}

	function resize_img() {
		if (!$image = $_POST["img"]) {
			$result['result_code'] = 101;
			$result['result_des'] = "图片不存在";
		} else {
			
			$real_img = $_SERVER['DOCUMENT_ROOT'] . $image;
			$info = get_img_info($real_img);

			if (!$info) {
				$result['result_code'] = 102;
				$result['result_des'] = $image;
			} else {
				$max_width = 440;
				if ($info['type'] == 'jpg' || $info['type'] == 'jpeg') {
					$im = imagecreatefromjpeg($real_img);
				}
				if ($info['type'] == 'gif') {
					$im = imagecreatefromgif($real_img);
				}
				if ($info['type'] == 'png') {
					$im = imagecreatefrompng($real_img);
				}
				if ($info['width'] <= $max_width) {
					$rate = 1;
				} else {
					$rate = $info['width'] / $max_width;
					if ($info['width'] > $info['height']) {
						$max_height = intval($info['height'] / ($info['width'] / $max_width));
					} else {
						$max_width = intval($info['width'] / ($info['height'] / $max_height));
					}
				}

				$x = $_POST["x"];
				$y = $_POST["y"];
				$w = $_POST["w"];
				$h = $_POST["h"];

				$width = $srcWidth = $info['width'];
				$height = $srcHeight = $info['height'];
				$type = empty($type) ? $info['type'] : $type;
				$type = strtolower($type);
				unset($info);
				//创建缩略图
				if ($type != 'gif' && function_exists('imagecreatetruecolor'))
					$thumbImg = imagecreatetruecolor($width, $height);
				else
					$thumbImg = imagecreate($width, $height);
				// 复制图片
				if (function_exists("imagecopyresampled"))
					imagecopyresampled($thumbImg, $im, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
				else
					imagecopyresized($thumbImg, $im, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
				
				
				if ('gif' == $type || 'png' == $type) {
					$background_color = imagecolorallocate($thumbImg, 0, 255, 0);
					imagecolortransparent($thumbImg, $background_color);
					
				}
				// 对jpeg图形设置隔行扫描
				if ('jpg' == $type || 'jpeg' == $type)
					imageinterlace($thumbImg, 1);

				if(!is_dir(get_save_path(). "emp_pic/")) {
					mkdir(get_save_path(). "emp_pic/",0777,true);
					chmod(get_save_path(). "emp_pic/",0777);
				}
				
				// 生成图片
				$imageFun = 'image' . ($type == 'jpg' ? 'jpeg' : $type);
				$id=$_REQUEST['id'];

				$thumbname = get_save_path().  "emp_pic/" .$id.".".$type;
				
				$imageFun($thumbImg, $thumbname);

				$thumbImg_120 = imagecreatetruecolor(120, 120);
				imagecopyresampled($thumbImg_120, $thumbImg, 0, 0,intval($x * $rate), intval($y * $rate), intval(120 * 1), intval(120 * 1),intval($w*$rate),intval($h*$rate));				
				$imageFun($thumbImg_120, $thumbname);

				imagedestroy($thumbImg);
				imagedestroy($im);
				
				$result['result_code'] = 1;
				$result['result_des'] = str_replace(get_save_path(), "", $thumbname);
				
				$data['id'] = $id;
				$data['pic'] = $result['result_des'];
				M('user') ->  save($data);
			}
		}
		echo json_encode($result);
	}

	function message() {
		$widget['jquery-ui'] = true;		
		$this -> assign("widget", $widget);
		
		$model = M("Dept");
		$list = array();
		$list = $model->where('is_del=0') -> field('id,pid,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_company', popup_tree_menu($list,0,4));

		$model = M("Rank");
		$list = array();
		$list = $model -> field('id,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_rank', popup_tree_menu($list));

		$model = M("Position");
		$list = array();
		$list = $model -> field('id,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_position', popup_tree_menu($list));		

		$this -> assign('type', 'company');
		$this -> display();
		return;
	}
	
	function share(){
		$this->message();
	}
	
	function help(){
		$subject=I('subject');
		$item=I('item');
		$this->assign('name',$subject);
		$this->assign('content',$item);
		$this->message();
	}	

	function json(){
		header("Content-Type:text/html; charset=utf-8");
		$type=$_REQUEST['type'];
		$key = $_REQUEST['key'];
		
		$model = M("User");
		$where['name'] = array('like', "%" . $key . "%");
		$where['letter'] = array('like', "%" . $key . "%");
		$where['email'] = array('like', "%" . $key . "%");
		$where['_logic'] = 'or';
		$map['_complex'] = $where;
		$map['is_del']=array('eq',0);
		$company = $model -> where($map) -> field('id,name,email') -> select();

		if($type=="all"){
			$where = array();
			$map=array();
			$model = M("Contact");
			$where['name'] = array('like', "%" . $key . "%");
			$where['letter'] = array('like', "%" . $key . "%");
			$where['email'] = array('like', "%" . $key . "%");
			$where['_logic'] = 'or';
			$map['_complex'] = $where;
			$map['email'] = array('neq', '');
			$map['user_id'] = array('eq', get_user_id());
			$personal = $model -> where($map) -> field('id,name,email') -> select();
		}
		if(empty($company)){
			$company=array();
		}
		if(empty($personal)){
			$personal=array();
		}		
		$contact = array_merge_recursive($company, $personal);
		exit(json_encode($contact));
	}
}
?>
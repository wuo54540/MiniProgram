<?php

namespace app\logic\logic;

class Fileupload{
	//图片上传设置;
	private $editor_config_setting = array(
		'image_upload_path'	=>	'/static/uploads/',
		'image_upload_name'	=>	'uploadimage',
		);

	public $image_upload_limit = array(
		'size'	=>	2*1024*1024,
		'type'	=>	array(
			'image/jpg','image/png','image/gif','image/jpeg',
			),
		);

	public function image_upload_handle($image_name){
		$info = $_FILES[$image_name];
		$initial = $this->upload_data_handle($info);
		return $initial;
	}

	private function upload_data_handle($info){
		$initial = initial_set('上传错误,请重新出尝试');
		if(array_key_exists('error',$info) && !$info['error']){
			if($info['size'] <= (2*1024*1024)){
				$pattern = '#^image/(gif|jpg|jpeg|bmp|png)$#';
				if(preg_match($pattern,$info['type'])){
					$orgin_name = explode('.',$info['name']);
					$format = array_pop($orgin_name);
					$name = array_shift($orgin_name);
					$filename = md5($name.time()).'.'.$format;
					
					$dir = date('Y-m-d');
					$dir_name = get_env('ROOT_PATH').'public'.$this->editor_config_setting['image_upload_path'].$dir;
					if(!file_exists($dir_name)){
						mkdir($dir_name,0775);
					}
					$result = move_uploaded_file($info['tmp_name'],$dir_name.'/'.$filename);
					if($result){
						$initial['status'] = 1;
						// $initial['msg'] = $this->editor_config_setting['image_upload_path'].$dir.'/'.$filename;
						// $initial['file'] = $filename;
						$initial['msg'] = '操作成功';
						$initial['file'] = $this->editor_config_setting['image_upload_path'].$dir.'/'.$filename;
					}else{
						$initial['msg'] = '系统错误,请请重新尝试';
					}
				}else{
					$initial['msg'] = '文件格式错误,请重新选择文件';
				}
			}else{
				$initial['msg'] = '图片大小超过2M,请重新选择图片';
			}
		}
		return $initial;
	}

	// public function mult_images_upload_handle($image_name,$key_name){
	// 	$files = $_FILES[$image_name];
	// 	$handle = array();
	// 	foreach ($files as $type => $item) {
	// 		foreach ($item as $key => $val) {
	// 			$handle[$key][$type] = $val[$key_name];
	// 		}
	// 	}

	// 	$list = array();
	// 	foreach ($handle as $key => $item) {
	// 		$list[$key]['result'] = $this->upload_data_handle($item);
	// 	}
	// 	return $list;
	// }

	public function mult_images_upload_handle($image_name){
		$files = $_FILES[$image_name];
		$handle = array();
		foreach ($files as $key => $item) {
			foreach ($item as $num => $group) {
				foreach ($group as $name => $val) {
					$handle[$name][$num][$key] = $val;
				}
			}
		}
		$list = array();
		foreach ($handle as $name => $item) {
			foreach ($item as $num => $val) {
				$list[$name][$num] = $this->upload_data_handle($val);
			}
		}

		return $list;
	}




	// public function get_editor_backend_config(){
	// 	if(IS_POST){
	// 		$initial = array(
	// 		    "state"		=>	"FAIL",
	// 		    "url"		=>	'',
	// 		    "title"		=>	'',
	// 		    "original"	=>	'',
	// 			);
	// 		$action = input('action/s','');
	// 		switch ($action) {
	// 			case $this->editor_config_setting['image_upload_name']:
	// 				$result = $this->image_upload_handle($this->editor_config_setting['image_upload_name']);
	// 				break;
	// 			default:
	// 				break;
	// 		}
	// 		if($result['status']){
	// 			$initial['state'] = 'SUCCESS';
	// 			$initial['url'] = $result['msg'];
	// 			$initial['title'] = $initial['original'] = $result['file'];
	// 		}
	// 		return $initial;

	// 	}else{
	// 		$filename = ROOT_PATH.'ueditor_config.json';
	// 		$data = preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents($filename));
	// 		$data = json_decode($data,true);
	// 		$config_change = array(
	// 			"imagePath"			=>	$this->editor_config_setting['image_upload_path'],
	// 			"imageFieldName"	=>	$this->editor_config_setting['image_upload_name'],
	// 			"imageMaxSize"		=>	2048*2048*2,
	// 			"imageAllowFiles"	=>	array(".png", ".jpg", ".jpeg", ".gif", ".bmp"),
	// 			'imagePathFormat'	=>	'{filename}',
	// 			'imageUrlPrefix'	=>	'',
	// 			);

	// 		$config = array_merge($data,$config_change);
	// 	}
	// 	return $config;
	// }
}
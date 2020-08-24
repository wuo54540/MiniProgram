<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

//缓存操作
function cache_action(string $cacheName, Callable $callback, bool $isReset = false, int $timeMin=1800, int $timeMax=3600){
	if($isReset){
		$data = $callback();
		\think\Cache::set($cacheName,$data,rand($timeMin,$timeMax));
		return $data;
	}else{
		$data = \think\Cache::get($cacheName);
		if(!$data){
			$data = $callback();
		}
		return $data;
	}
}

//where条件操作
// function normal_where(array $arr){
// 	$where = new think\db\Where($arr);
// 	return $where;
// }

//整理数组
function array_key_handle(array $array, string $key){
	if(count($array)>0){
		$data = array();
		foreach ($array as $item) {
			array_key_exists($key,$item) && $data[$item[$key]] = $item;
		}
		count($data)>0 && ($array = $data);
	}
	return $array;
}

//返回信息初始化
function msg_init(string $msg='error', int $status=0, array $data=array()){
	return array(
		'status'	=>	$status,
		'msg'		=>	$msg,
		'data'		=>	$data,
		);
}
//模型对象输出数据装换成数组形式
function object_data_to_array(array $objectData){
	return json_decode(json_encode($objectData),true);
}

/*---layui专用---*/
	function layui_table_data(array $data,string $msg='success', int $code=0){
		return array(
				'data'	=>	$data,
				'count'	=>	is_array($data) ? count($data) : 0,
				'msg'	=>	$msg,
				'code'	=>	$code,	
			);
	}
/*---end---*/
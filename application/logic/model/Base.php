<?php
namespace app\logic\model;

use \think\Model;

class Base extends Model{
	//查询回调函数封装
	protected function model_callback(array $where, string $field='*' ,string $sort='', int $page=0 , int $pagesize=0){
		$model = $this;
		$pagesize = $pagesize>0 ? $pagesize : config('paginate.list_rows');

		$callback = function() use ($model,$where,$field,$sort,$page,$pagesize){
			$list = $model->field($field)->where($where)->order($sort);
			if($page>0 && $pagesize>0){
				$list = $list->page($page,$pagesize);
			}
			$list = $list->select();
			// $list = $list->fetchSql(true)->select();
			// dump($list);die;
			return $list;
		};

		return $callback;
	}

	//查询分装
	public function model_select(array $where, string $field='*', string $sort='', int $page=0,int $pagesize=0, bool $isCache=false, string $cacheName='', bool $isReset=false){

		$callback = $this->model_callback($where,$field,$sort,$page,$pagesize);

		if($isCache && $cacheName){
			$cacheName .= "_{$page}_{$pagesize}_{$sort}";
			$data = cache_action($cacheName,$callback,$isReset);
		}else{
			$data = $callback();
		}

		return $data;
	}

	//添加数据
	public function model_insert(array $data_insert, bool $isMultiply){
		$result;
		if($isMultiply){
			$result = $this->insertAll($data_insert);
		}else{
			$result = $this->insertGetId($data_insert);
		}
		return $result;
	}

	//添加或者更新数据
	public function model_edit(array $data_edit, bool $isMultiply, array $where=array()){
		if($isMultiply){
			$result = $this->saveAll($data_edit);
		}else{
			$result = $this->save($data_edit,$where);
		}
		return $result;
	}

	//删除数据
	public function model_delete(array $data_delete, bool $isMultiply=false,bool $isSoftDelete=true, string $primaryKey='id', $statusName='is_delete'){
		if($isSoftDelete && !$isMultiply){
			$data_edit = array($statusName=>$data_delete[$statusName]);
			unset($data_delete[$statusName]);
			$result = $this->model_edit($data_edit,false,$data_delete);
		}elseif($isSoftDelete && $isMultiply){
			$data_edit = array();
			foreach ($data_delete as $item) {
				$data_edit[] = array(
					$primaryKey	=>	$item[$primaryKey],
					$statusName	=>	$item[$statusName],
					);
			}
			$result = $this->model_edit($data_edit,$isMultiply);
		}else{
			$result = $this->destroy($data_delete);
		}
		return $this;
	}

}
<?php
namespace app\logic\model;

class Article extends Base{
	protected $pk = 'id';

	public function article_list(bool $isSystem, array $where, string $field='*', string $sort='publish_time DESC', int $page=1, int $pagesize=15){
		$cacheName = '';
		if($isSystem){
			$cacheName = config('cache_name.article_list_is_system');
		}else{
			$cacheName = config('cache_name.article_list_not_system');
		}

		$data = $this->model_select($where,$field,$sort,$page,$pagesize,true,$cacheName);

		return $data;
	}

	public function article_add(){
		
	}

	public function article_edit(){

	}

	public function article_delete(){
		
	}

	// public function article_not_system(){
	// 	$cacheName = config('cache_name.article_not_system');
	// }
}
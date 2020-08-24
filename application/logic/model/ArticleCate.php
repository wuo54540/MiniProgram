<?php
namespace app\logic\model;

class ArticleCate extends Base{
	protected $pk = 'id';

	public function article_cate(bool $is_system=false, bool $isReset=false){
		$where = array(
			'is_delete'	=>	0,
			);
		if($is_system){
			$cacheName = config('cache_name.article_cate_is_system');
			$where['is_system'] = 1;
		}else{
			$cacheName = config('cache_name.article_cate_not_system');
			$where['is_system'] = 0;
		}

		$sort = 'parent_id ASC,sort ASC';

		$field = 'id,parent_id,name_zh,sort';

		$data = object_data_to_array($this->model_select($where,$field,$sort,0,0,true,$cacheName,$isReset));

		return $data;
	}

	public function article_cate_add_or_edit(string $name_zh, int $parent_id=0 , bool $is_system=false, int $sort=99, int $id=0){
		
		$initial = msg_init('操作错误');

		//1.检测分类名称是否存在
		if($name_zh){
			//2.检测是否已经有同名的分类
			$articleCate = $this->article_cate($is_system);
			$articleCate = array_column($articleCate,'name_zh','id');
			if((!in_array($name_zh, $articleCate) && !$id) || ($articleCate[$id]==$name_zh)){
				$data = array(
					'name_zh'	=>	$name_zh,
					'parent_id'	=>	$parent_id,
					'is_system'	=>	(bool)$is_system,
					'sort'		=>	$sort,
					);
				
					
				if($id){
					$result = $this->model_edit($data,false,array('id'=>$id));
				}else{
					$result = $this->model_insert($data,false);
				}

				if($result){
					$initial['msg'] = '操作成功';
					$initial['data'] = object_data_to_array($this->article_cate($is_system,true));
					$initial['status'] = 1;
				}else{
					$initial['msg'] = '操作失败,请重新尝试';
				}
			}else{
				$initial['msg'] = '已存在相同名称的分类';
			}
		}else{
			$initial['msg'] = '必须填写分类名称';
		}

		return $initial;
	}

	public function article_cate_delete(int $id, bool $is_system=false){
		//1.检测是否有下级分类
		$articleCate = $this->article_cate($is_system);
		$init = msg_init('该分类下还有子分类没有删除');
		foreach ($articleCate as $item) {
			if($item['parent_id']==$id){
				return $init;
			}
		}

		//2.删除条件,软删除
		$data_delete = array(
			'is_delete'	=>	1,
			'id'		=>	$id
			);

		$result = $this->model_delete($data_delete);
		if($result){
			$init['status'] = 1;
			$init['msg'] = '操作成功';
			$init['data'] = object_data_to_array($this->article_cate($is_system,true));
		}else{
			$init['msg'] = '操作失败';
		}
		return $init;
	}
}
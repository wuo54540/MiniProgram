<?php
namespace app\index\controller;

class Article extends Base{
	//获取文章列表
	public function list(){
		$articleModel = Loader::model('Article');
		$articleCateModel = Loader::model('ArticleCate');

		$output = array();
		if(IS_POST){
			$isSystem = boolval(input('post.isSystem/d',0));
			$articleCate = $articleCateModel->article_cate($isSystem);

			$title = input('post.title/s','');
			$cate = input('post.cate/d',0);
			
			// $sort = input('post.sort/d',0);
			$page = input('post.page/d',1);
			$pagesize = input('post.pagesize/d',0);

			$where = array(
				'id_delete'	=>	0,
				);
			$cateIdGroup = array_column($cateTree,'id');

			$where['cate'] = in_array($cate,$cateIdGroup) ? $cate : array('IN',implode(',', $cateIdGroup));
			$title && ($where['title'] = array('LIKE',"%{$title}%"));

			$field = 'id,title,author,publish_time,click,thumb';
			$sort = 'sort ASC,public_time DESC';
			$output['list'] = $articleModel->article_list($isSystem,$where,$field,$sort,$page,$pagesize);

		}else{
			$isSystem = boolval(input('isSystem',0));
			$articleCate = $articleCateModel->article_cate($isSystem);
			$output['cate'] = $articleCate;
		}

		return json($output,200);
	}
}
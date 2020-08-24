<?php
namespace app\admin\controller;

use app\logic\model\ArticleCate;
use app\logic\model\Article as ArticleModel;

use think\Loader;

class Article extends Base{
	public function __construct(){
		parent::__construct();
	}


	/*---文章分类部分---*/
		public function cate(){
			// $ArticleCate = new ArticleCate;
			// $articleCate = $ArticleCate->article_cate(false);

			// $this->assign('articleCate',$articleCate);
			$this->page_title('文章分类列表');
			return $this->fetch();
		}

		protected function handle_articleCateList(){
			$ArticleCate = new ArticleCate;
			$articleCate = $ArticleCate->article_cate(false);
			return layui_table_data($articleCate);
		}

		protected function handle_articleCateDelete(){
			$result = msg_init('操作错误');
			$id = input('post.id/d',0);
			if($id>0){
				$ArticleCate = new ArticleCate;
				$result = $ArticleCate->article_cate_delete($id,false);
			}
			return $result;
		}

		protected function handle_articleCateEdit(){
			$result = msg_init('操作错误');
			$id = input('post.id/d',0);
			if($id>0){
				$result = $this->handleSub_articleCateAdd_or_articleCateEdit($id);
			}
			return $result;
		}

		protected function handle_articleCateAdd(){
			$result = $this->handleSub_articleCateAdd_or_articleCateEdit();
			return $result;
		}

		protected function handleSub_articleCateAdd_or_articleCateEdit(int $id=0){
			// $data = input('post.*/a',array());
			// if()
			$sort = input('post.sort/d',99);
			$name_zh = input('post.name_zh/s','');
			$parent_id = input('post.parent_id/d',0);
			$is_system = input('post.is_system/d',0);

			$ArticleCate = new ArticleCate();

			$result = $ArticleCate->article_cate_add_or_edit($name_zh,$parent_id,$is_system,$sort,$id);

			return $result;
		}
	/*---end---*/

	/*---文章部分---*/

		public function article(){
			$ArticleCate = new ArticleCate;
			$articleCate = $ArticleCate->article_cate(false);

			$this->assign('articleCate',$articleCate);
			$this->page_title('文章列表');
			return $this->fetch();
		}

		protected function handle_articleList(){
			$page = input('post.page/d',$this->config['page']);
			$pagesize = input('post.pagesize/d',$this->config['pagesize']);
			$cate_id = input('post.cate/d',0);

			$title = input('post.title/s','');

			$where = array();
			$cate_id && ($where['cate_id'] = $cate_id);
			$title && ($where['title'] = array('LIKE',"%{$title}%"));
			$field = 'id,title,thumb,sort,publish_time,add_time,click,cate_id,is_top,content';
			// $sort_id = input('post.sort_id',0);

			$Article = new ArticleModel;

			return  layui_table_data($Article->article_list(false, $where, $field, $sort='add_time DESC', $page, $pagesize));
		}

		protected function handle_articleDelete(){

		}

		protected function handle_articleAdd(){
			$result = $this->handleSub_articleAdd_or_articleEdit();
			return $result;
		}

		protected function handle_articleEdit(){
			$result = msg_init('操作错误');
			$id = input('post.id/d',0);
			if($id>0){
				$result = $this->handleSub_articleAdd_or_articleEdit($id);
			}
			return $result;
		}

		private function handleSub_articleAdd_or_articleEdit(int $id=0){

		}


	/*---end---*/
}
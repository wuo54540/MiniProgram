<?php
namespace app\admin\controller;

use think\Cache;

class Index extends Base{
	private $indexDefaultUrl = 'Article/index';

	public function index(){
		$menu = require_once APP_PATH.'admin/config/Menu.php';

		$this->assign('indexMenu',$menu);
		$this->assign('indexDefaultUrl',$this->indexDefaultUrl);
		return $this->fetch();
	}

	public function clear(){
		Cache::clear();
		return $this->redirect(url($this->indexDefaultUrl));
	}

	public function login(){
		if(IS_POST){
			// $code = input('post.verify')
		}else{

		}
	}

	public function verify(){
		if(IS_POST){
			
		}
	}
}
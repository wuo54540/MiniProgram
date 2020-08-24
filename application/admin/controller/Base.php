<?php
namespace app\admin\controller;

use think\Controller;

class Base extends Controller{

	protected $config = array(
			'system_flag'	=>	'admin',
			'page'			=>	1,
			'pagesize'		=>	15,
		);

	public function __construct(){
		parent::__construct();
	}

	final public function handle(){
		$output = msg_init('操作错误');
		if(IS_POST){
			$methodName = 'handle_'.input('post.handle/s','');
			if(method_exists($this,$methodName)){
				$output = $this->$methodName();
			}
		}
		return json($output,200);
	}

	final protected function page_title(string $page_title){
		$this->assign('page_title',$page_title);
	}
}
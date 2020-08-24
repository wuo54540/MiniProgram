<?php
namespace app\index\controller;
use think\Controller


class Base extends Controller{
	public function __construct(){
		parent::__construct();

		$this->system_config();
	}

	

	protected function system_config(){

	}

}
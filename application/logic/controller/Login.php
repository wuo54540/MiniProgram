<?php
namespace app\logic\controller;

use think\captcha\Captcha;

class Login extends Base{
	private $system_flag_arr = array(
		'admin','user';
		);

	private $captcha_config = array(
		'length'	=>	4,
		'expire'	=>	1800,
		'reset'		=>	true,
		'useNoise'	=>	true,
		'fontSize'	=>	30,
		);

	public function __construct(string $systemFlag){
		parent::__construct();
		$this->systemFlag = in_array($systemFlag, $this->system_flag_arr) ? $systemFlag : 'user';
	}

	public function code_output(){
		$captcha = new Captcha($this->captcha_config);
		return $captcha->entry($this->systemFlag);
	}

	public function code_check(string $code){
		$captcha = new Captcha($this->captcha_config);
		return $captcha->check($code,$this->systemFlag);
	}
}
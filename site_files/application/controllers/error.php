<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Error extends MY_Controller{

	public function index(){
		$page = new Page('error');
		if(isset($_GET['r']))
			$page->Data('ref',$_GET['r']);
		$page->show();
	}
	

}

<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Error404 extends MY_Controller{

	public function index(){
		$page = new Page('error404');
		$page->title("Page Not Found");
		$page->show();
	}
	

}

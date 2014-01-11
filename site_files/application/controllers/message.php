<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Message extends MY_Controller{

	public function index(){
		$page = new Page('message');
		$page->title('CityTot Message');
		$page->Data('message',$this->session->userdata('page_message'));
		$this->session->unset_userdata('page_message');
		$page->show();
	}
	

}

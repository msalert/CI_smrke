<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Smrke{
     function __construct() {     	
     }
	 	public function debug($obj){		echo "<pre>".print_r($obj,true)."</pre>";		die;	}		// can only be called before any header output
	 public function redirect_error($msg) {		$ci = &get_instance();		$ci->load->model('errors_m');		$id = $ci->errors_m->log_error($msg);		header("Location: ".base_url('error?r='.$id));		exit;	 }	 	 public function page_message($msg) {	 	$ci = &get_instance();	 	$ci->session->set_userdata('page_message',$msg);		header("Location: ".base_url('message'));		exit;	 }	 	 	 // return filename to capitalized text	 private function clean_title($input){	 	$input = preg_replace('/^(\w)|_(\w)/e', ' strtoupper("$0")', $input);	 	return preg_replace('/_/', ' ', $input);	 }
 }
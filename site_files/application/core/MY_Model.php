<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class MY_Model extends CI_Model{

	function __construct(){

		parent::__construct();
		// session_start();
		// session_unset(); session_destroy();		
		// echo "SERVER: ";
		// echo "<pre>".print_r($_SESSION,true)."</pre>"; die;
		// $this->load->model('site_m');
		// $this->site = $this->site_m->get_site();
		
		// $this->server = $_SESSION['server']; 
		
		// if(!$this->site) {header("Location: ".base_url('install')); exit;}
		
			
		// $this->load->library('Page');
		
	}

	function log_error($err){
		// echo $err; die;
		$sql = "INSERT INTO site_error_log (error) VALUES (?)";
		$this->db->query($sql,$err);
		$sql = "SELECT LAST_INSERT_ID() AS id FROM site_error_log";
		$q = $this->db->query($sql);
		$q=$q->result();
		return $q[0]->id;
	}

	

	

}

	
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class Db_m extends MY_Model{	

	function __construct(){
		parent::__construct();
	}

        function run_query($q){
            return $this->db->query($q);
        }
}


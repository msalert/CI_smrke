<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class Works_m extends MY_Model{	

	function __construct(){
		parent::__construct();
	}

        function get_front_works(){
            $sql = "SELECT * FROM works";
            $q = $this->db->query($sql);
            return $q->result();
        }
}


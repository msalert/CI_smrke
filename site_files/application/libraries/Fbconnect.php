<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


require_once("fb_client/facebook.php");

class Fbconnect extends Facebook{
		
		// City Tot
		// App ID:	257768267701335
		// App Secret:	ba5c542d63bf963ca728a08a2af7ae27
		
		// BarPublic
		// App ID:	192866710829107
		// App Secret:	fef0efb653d20f9cdd070d16928b83e2
	public function __construct(){
		$ci =& get_instance();
		$config = array('appId' => '257768267701335',
					'secret' => 'ba5c542d63bf963ca728a08a2af7ae27');
		
		parent::__construct($config);
	}
	
	
	
	public function sendback($val){
		return strtoupper($val);
	}
	
}

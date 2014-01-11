<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/


$hook['my_pre_controller'][] = array(
	'class'		=> 'CPAC_Group',
	'function'	=> 'init_group',
	'filename'	=> 'group.php',
	'filepath'	=> 'hooks'
);

$hook['post_controller_constructor'][] = array(
	'class'		=> 'CSRF_Protection',
	'function'	=> 'validate_token',
	'filename'	=> 'csrf.php',
	'filepath'	=> 'hooks'
);

$hook['post_controller_constructor'][] = array(
	'class'		=> 'CSRF_Protection',
	'function'	=> 'generate_token',
	'filename'	=> 'csrf.php',
	'filepath'	=> 'hooks'
);



$hook['display_override'][] = array(
	'class'		=> 'CSRF_Protection',
	'function'	=> 'inject_tokens',
	'filename'	=> 'csrf.php',
	'filepath'	=> 'hooks'
);
/* End of file hooks.php */
/* Location: ./application/config/hooks.php */
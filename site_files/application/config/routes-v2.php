<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

session_start();
$route['default_controller'] = "sat_home";
$route['404_override'] = '';

require_once( BASEPATH .'database/DB'. EXT );
$server_name = $_SERVER['SERVER_NAME'];

if($server_name=="chronicpainassociation.ca"){
	$server->city = "canada";
	$server->google_id = "UA-40050299-1";
	// Main Site routes
	$route['default_controller'] = "home";	
	// $route['blog/(:any)'] = "blog/index/$1";
	// $route['events/(:any)'] = "events/index/$1";
	
}else{
	$db =& DB();
	$q = $db->query( 'SELECT * FROM groups WHERE domain_name=?',$server_name );
	if($q->num_rows==1){
		$q = $q->result();
		$server = $q[0];
		
		// Satellite site routes
		$route['default_controller'] = "sat_home";
		$route['about-us'] = "sat_about";
		$route['about-us/(:any)'] = "sat_about/$1";
		$route['support'] = "sat_support";
		$route['support/(:any)'] = "sat_support/$1";
		$route['get-involved'] = "sat_get_involved";
		$route['get-involved/(:any)'] = "sat_get_involved/$1";
		$route['resources'] = "sat_resources";
		$route['resources/(:any)'] = "sat_resources/$1";

					
	}else header("Location: http://chronicpainassociation.ca");		
}

$route['login'] = "user/login";
$route['logout'] = "user/logout";
$route['activate_accout'] = "user/activate_accout";
$route['activate_accout/(:any)'] = "user/activate_accout/$1";
$route['resend_email'] = "user/resend_email";
$route['resend_email/(:any)'] = "user/resend_email/$1";
$route['reset_password'] = "user/reset_password";
$route['reset_password/(:any)'] = "user/reset_password/$1";
$route['user/(:any)'] = "user/index/$1";

$route['admin'] = "admin2";
$route['admin/(:any)'] = "admin_$1";

$server->domain_name=$server_name;
$_SESSION['server'] = $server;
/* End of file routes.php */
/* Location: ./application/config/routes.php */
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
// routes-v2.php is the version before switching to pre_controller hook

// Main Site routes
$route['default_controller'] = "home";
$route['404_override'] = 'error404';

$route['site-administration'] = "site_administration";
$route['site-administration/(:any)'] = "site_administration/$1";
$route['providers-admin'] = "admin_providers";
$route['providers-admin/(:any)'] = "admin_providers/$1";
$route['classes-admin'] = "admin_classes";
$route['classes-admin/(:any)'] = "admin_classes/$1";


$route['login'] = "users/login";
$route['login/fb_login'] = "users/fb_login";
$route['login/g_login'] = "users/g_login";
$route['logout'] = "users/logout";
$route['register'] = "users/register";
$route['social_register'] = "users/social_register";


$route['user-settings'] = "users/user_settings";
$route['activate_account'] = "users/activate_account";
$route['activate_account/(:any)'] = "users/activate_account/$1";
$route['resend_email'] = "users/resend_email";
$route['resend_email/(:any)'] = "users/resend_email/$1";
$route['reset_password'] = "users/reset_password";
$route['reset_password/(:any)'] = "users/reset_password/$1";

$route['class'] = "classes";
$route['class/(:any)'] = "classes/index/$1";
$route['provider'] = "providers";
$route['provider/(:any)'] = "providers/index/$1";
$route['review-submitted'] = "review/review_submitted";
$route['confirm-review-edit'] = "review/confirm_review_edit";

/* End of file routes.php */
/* Location: ./application/config/routes.php */
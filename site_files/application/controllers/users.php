<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class Users extends MY_Controller{

	public function index(){
		
	}
	
	
	public function login(){
		if($this->session->userdata("user_id")){
			header("Location: ".base_url()); exit;
		}
		
		// $this->smrke->debug($_POST); die;
		
		$this->load->model('user_m');
		$errors = array(); $status = array();
		$page = new Page('');
		$page->title("Login");
		$page->content("login-v");
		$page->styles("login");
		if($this->session->userdata('system_message')){
			$page->Data('system_message',$this->session->userdata('system_message'));
		}
		
		if(isset($_POST['ct_log_in']) || isset($_POST['ct_log_in_x'])){
			// clean data
			
			$this->load->library('form_validation');
			$this->form_validation->set_rules('ct_email', 'Email', 'trim|xss_clean');
			$this->form_validation->set_rules('ct_password', 'Password', 'trim|xss_clean');
			$this->form_validation->run();
			
			$email = $_POST['ct_email'];
			$password = $_POST['ct_password'];
			
			$user = $this->user_m->verify_user($email,$password);
			if($user){
				// print_r($user); die;
				if(isset($user->error)){
					$errors['login'] = $user->error;
				}
				elseif($user->status!="active"){
					$errors['login'] = "There is a problem with your account.  Please contact us at info@citytot.com";
				}
				else{ // log usr in
					$this->session->unset_userdata('system_message');
					$this->user_m->login_user($user->id,array("citytot"=>true));
					// check for review or referer (and is citytot.com)
					if($this->session->userdata('temp_review')){
						$this->load->model('reviews_m');
						//insert review in database
						$review = $this->session->userdata('temp_review');
						$this->session->unset_userdata('temp_review');
						
						$class_id = $this->encrypt->decode($review['class_id_code']);
						if($this->reviews_m->has_reviewed($user->id,$class_id)){
							// display page asking if you want to replace the existing review
							$this->session->set_userdata('temp_review',$review);
							header("Location: ".base_url('confirm-review-edit?cid='.$class_id.'&uid='.$user->id));
							exit;
						}
						if($this->reviews_m->submit_review($user->id,$review['class_id_code'],$review['heart_hate'],$review['instructor_quality'],$review['customer_service'],$review['parent_engagement'],$review['facilities'],$review['comments'])){
							// forward to review submitted page.
							header("Location: ".base_url('review-submitted'));
							exit;						
						}else{
							// set message and forward to review submitted page.
							$this->session->set_userdata('system_message',"There was a problem submitting your review.");
							header("Location: ".base_url('review-submitted'));
							exit;
						}
					}
					// elseif($this->session->userdata('ct_referrer')){
						// $url = $this->session->userdata('ct_referrer');
						// $this->session->unset_userdata('ct_referrer');
						// header("Location: ".$url);
						// exit;
					// }
					else{
						header("Location: ".base_url());
						exit;
					} 
				}
				
			}else{
				$errors['login'] = "Invalid login information";
			}	
		}// end ct_login
		
		// user is resetting password
		if((isset($_POST['ct_reset_password'])||isset($_POST['ct_reset_password_x'])) && $_POST['email_conf'] == ""){
			// clean data
			$this->load->library('form_validation');
			$this->form_validation->set_rules('ct_email', 'Email', 'trim|xss_clean');
			$this->form_validation->run();
			if($this->user_m->send_reset_password($_POST['ct_email']))
				$status['reset-password'] = "Password reset information has been sent to your email account.";
			else
				$errors['reset-password'] = "There was a problem sending the reset password email.";
		}	
		$this->load->library('Fbconnect');	
		$this->load->library('gconnect');
		
		if(isset($_SERVER['HTTP_REFERER'])) $this->session->set_userdata('ct_referrer',$_SERVER['HTTP_REFERER']);
		
		// get login urls
		$fb_url = $this->fbconnect->getLoginUrl(array('redirect_uri' => 'http://citytot.electronspinsolutions.com/login/fb_login',
														'scope' => 'email'));
		$g_url = $this->gconnect->createAuthUrl();

		// display login page
		$page->Data('fb_url',$fb_url);
		$page->Data('g_url',$g_url);
		$page->Data('errors',$errors);
		$page->Data('status',$status);
		$page->show();
	}
	
	
	public function logout(){
		$this->session->sess_destroy();
		// logout fb		
		$this->load->library('Fbconnect');
		$this->fbconnect->destroySession(null);
		
		header("Location: ".base_url());  exit;
	}		
		
	public function fb_login(){
		$this->load->model('user_m');
		$this->load->library('Fbconnect');
		
		// if facebook login
		if($this->fbconnect->getUser()){
		   	// get user info
			$user_profile = $this->fbconnect->api('/me','GET');
			//	check if email in database
			$ct_user = $this->user_m->get_user_with_email($user_profile['email']); 
			if($ct_user){
				if($ct_user->status!="active"){
					$this->smrke->page_message("There is a problem with your account.  Please contact us at info@citytot.com");
				}
				// log user in
				$this->user_m->login_user($ct_user->id,array('facebook'=>$user_profile['id']));
				
				// check for review or referer (and is citytot.com)
				if($this->session->userdata('temp_review')){
					//insert review in database
					$review = $this->session->userdata('temp_review');
					$this->session->unset_userdata('temp_review');
					$this->load->model('reviews_m');
					$class_id = $this->encrypt->decode($review['class_id_code']);
					// check if user has reviewed this class before
					if($this->reviews_m->has_reviewed($ct_user->id,$class_id)){
						
						// display page asking if you want to replace the existing review
						$this->session->set_userdata('temp_review',$review);
						header("Location: ".base_url('confirm-review-edit?cid='.$class_id.'&uid='.$ct_user->id));
						exit;
					}
					if($this->reviews_m->submit_review($ct_user->id,$review['class_id_code'],$review['heart_hate'],$review['instructor_quality'],$review['customer_service'],$review['parent_engagement'],$review['facilities'],$review['comments'])){
						// forward to review submitted page.
						$this->session->unset_userdata('system_message');
						header("Location: ".base_url('review-submitted'));
						exit;						
					}else{
						// set message and forward to review submitted page.
						$this->session->set_userdata('system_message',"There was a problem submitting your review.");
						header("Location: ".base_url('review-submitted'));
						exit;
					}
				}
				// elseif($this->session->userdata('ct_referrer')){
					// $url = $this->session->userdata('ct_referrer');
					// $this->session->unset_userdata('ct_referrer');
					// header("Location: ".$url);
					// exit;
				// }
				else header("Location: ".base_url());
			}else{
				// if first-time user -> registration
				$this->session->set_userdata('fb_user',$user_profile);
				header("Location: ".base_url('register'));
				exit;
			}
		}
		
		// if error on facebook login
		if(isset($_GET['error_code'])){
		
			error_log("FB login error: Code ".$_GET['error_code']);
		
			// set error message and redirect to login
			$this->session->set_userdata('system_message',"CityTot was unable to interact with your Facebook Account.");
		}
		
		header("Location: ".base_url('login'));
		exit;	
	}
	
	public function g_login(){
		$this->load->model('user_m');
		$this->load->library('gconnect');
		// if google login
		if (isset($_GET['code'])) { // we received the positive auth callback, get the token and store it in session
		    $this->gconnect->authenticate();
		
			// get user info	
			$user_info = $this->gconnect->oauth2->userinfo->get();
			
			//	check if email in database
			$ct_user = $this->user_m->get_user_with_email($user_info['email']); 
			if($ct_user){
				if($ct_user->status!="active"){
					$this->smrke->page_message("There is a problem with your account.  Please contact us at info@citytot.com");
				}
				// log user in
				$this->user_m->login_user($ct_user->id,array('google'=>$user_info['id']));
				
				// check for review or referer (and is citytot.com)
				if($this->session->userdata('temp_review')){
					//insert review in database
					$review = $this->session->userdata('temp_review');
					$this->session->unset_userdata('temp_review');
					$this->load->model('reviews_m');
					// check if user has reviewed this class before
					$class_id = $this->encrypt->decode($review['class_id_code']);
					if($this->reviews_m->has_reviewed($ct_user->id,$class_id)){
						
						// display page asking if you want to replace the existing review
						$this->session->set_userdata('temp_review',$review);
						header("Location: ".base_url('confirm-review-edit?cid='.$class_id.'&uid='.$ct_user->id));
						exit;
					}
					if($this->reviews_m->submit_review($ct_user->id,$review['class_id_code'],$review['heart_hate'],$review['instructor_quality'],$review['customer_service'],$review['parent_engagement'],$review['facilities'],$review['comments'])){
						// forward to review submitted page.
						$this->session->unset_userdata('system_message');
						header("Location: ".base_url('review-submitted'));
						exit;						
					}else{
						// set message and forward to review submitted page.
						$this->session->set_userdata('system_message',"There was a problem submitting your review.");
						header("Location: ".base_url('review-submitted'));
						exit;
					}
				}
				// elseif($this->session->userdata('ct_referrer')){
					// $url = $this->session->userdata('ct_referrer');
					// $this->session->unset_userdata('ct_referrer');
					// header("Location: ".$url);
					// exit;
				// }
				else header("Location: ".base_url());
			}else{
				// if first-time user -> registration
				$this->session->set_userdata('g_user',$user_info);
				header("Location: ".base_url('register'));
				exit;
			}
			
		}
		
		// if error on google login
		if(isset($_GET['error'])){
			error_log("Google login error: ".$_GET['error']);
		
			// set error message and redirect to login
			$this->session->set_userdata('system_message',"CityTot was unable to interact with your Google Account.");
		}
		
		header("Location: ".base_url('login'));
		exit;	
	}
	

	public function register(){
		if($this->session->userdata("user_id")){
			header("Location: ".base_url()); exit;
		}
		$page = new Page('');
		$page->title('Register');
		$page->styles('login');
		$this->load->library('Fbconnect');	
		$this->load->library('gconnect');
		$this->load->model('user_m');
		$errors = array();	$status = array();
		
		// show citytot register and fb or g social_register
		if($this->session->userdata('fb_user')){
			$page->content('social_register-v');
			$fb_user = $this->session->userdata('fb_user');
			// $this->session->unset_userdata('fb_user');
			$page->Data('fb_user',$fb_user);
		}elseif($this->session->userdata('g_user')){
			$page->content('social_register-v');
			$g = $this->session->userdata('g_user');
			// $this->session->unset_userdata('g_user');
			$g_user = array(
				"id"=>$g['id'],
				"email"=>$g['email'],
				"first_name"=>$g['given_name'],
				"last_name"=>$g['family_name'],
			);
			$page->Data('g_user',$g_user);
			
		}else{
			$page->content('register-v');
			// get login urls
			$fb_url = $this->fbconnect->getLoginUrl(array('redirect_uri' => 'http://citytot.electronspinsolutions.com/login/fb_login',														'scope' => 'email'));
			$g_url = $this->gconnect->getLoginUrl(array('redirect_uri' => 'http://citytot.electronspinsolutions.com/login/g_login'));
			
			$page->Data('fb_url',$fb_url);
			$page->Data('g_url',$g_url);
		}
		
		

		if(isset($_POST['ct_new_account']) && $_POST['email_conf'] == ""){
			$this->load->library('form_validation');
			$this->form_validation->set_rules('ct_first_name', 'First Name', 'trim|required|max_length[20]|xss_clean');
			$this->form_validation->set_rules('ct_last_name', 'Last Name', 'trim|required|xss_clean');
			$this->form_validation->set_rules('ct_email', 'Email', 'trim|required|valid_email|is_unique[users.email]|xss_clean');
			$this->form_validation->set_rules('ct_username', 'Username', 'trim|required|max_length[20]|alpha_numeric|is_unique[users.username]|xss_clean');
			$this->form_validation->set_rules('ct_zip', 'Zip Code', 'trim|numeric|max_length[5]|min_length[5]');			   
			
			if(isset($_POST['facebook_id'])){ // if facebook....
				$this->form_validation->set_rules('ct_password', 'Password', 'trim|matches[ct_password2]|xss_clean');
				$this->form_validation->set_rules('ct_password2', 'Confirm Password', 'trim|matches[ct_password]');
			}elseif(isset($_POST['google_id'])){ // if google....
				$this->form_validation->set_rules('ct_password', 'Password', 'trim|matches[ct_password2]|xss_clean');
				$this->form_validation->set_rules('ct_password2', 'Confirm Password', 'trim|matches[ct_password]');
			}else{ // if citytot register user
				$this->form_validation->set_rules('ct_password', 'Password', 'trim|required|xss_clean');
				$this->form_validation->set_rules('ct_password2', 'Confirm Password', 'trim|required|matches[ct_password]');	
			}

			$this->form_validation->set_message('is_unique', 'We already have that %s in our system.');
			$this->form_validation->set_message('alpha_numeric', 'The %s may only contain letters or numbers.');
			
			if ($this->form_validation->run() == FALSE){
				foreach ($_POST as $key => $value) $page->Data($key,$value);
				$errors['new-account'] = $this->form_validation->error_array();
				if(!isset($_POST['tos']))
					$errors['new-account']['tos'] = "You must accept the CityTot Terms of Service to register.";
				
			}elseif(!isset($_POST['tos'])){
				foreach ($_POST as $key => $value) $page->Data($key,$value);
				$errors['new-account']['tos'] = "You must accept the CityTot Terms of Service to register.";
			}
			else{ //register user
				// echo "<pre>".print_r($_POST,true)."</pre>"; die;
				//create new user
				$email_opt_in = isset($_POST['email_opt_in'])? 1:0;
				if(isset($_POST['facebook_id'])){
				// if facebook....
					if($this->user_m->register_user_fb($_POST['ct_username'],$_POST['ct_email'],$_POST['ct_password'],$_POST['ct_first_name'],$_POST['ct_last_name'],$_POST['ct_zip'],$email_opt_in,$_POST['facebook_id'])){
						$this->session->unset_userdata('fb_user');
						$ct_user = $this->user_m->get_user_with_email($_POST['ct_email']); 
						$this->user_m->login_user($ct_user->id,array('facebook'=>$_POST['facebook_id']));
	
						// check for review or referer (and is citytot.com)
						if($this->session->userdata('temp_review')){
							//insert review in database
							$review = $this->session->userdata('temp_review');
							$this->session->unset_userdata('temp_review');
							$this->load->model('reviews_m');
							
							// check if user has reviewed this class before
							$class_id = $this->encrypt->decode($review['class_id_code']);
							if($this->reviews_m->has_reviewed($ct_user->id,$class_id)){
								
								// display page asking if you want to replace the existing review
								$this->session->set_userdata('temp_review',$review);
								header("Location: ".base_url('confirm-review-edit?cid='.$class_id.'&uid='.$ct_user->id));
								exit;
							}
							if($this->reviews_m->submit_review($ct_user->id,$review['class_id_code'],$review['heart_hate'],$review['instructor_quality'],$review['customer_service'],$review['parent_engagement'],$review['facilities'],$review['comments'])){
								// forward to review submitted page.
								header("Location: ".base_url('review-submitted'));
								exit;						
							}else{
								// set message and forward to review submitted page.
								$this->session->set_userdata('system_message',"There was a problem submitting your review.");
								header("Location: ".base_url('review-submitted'));
								exit;
							}
						}
						// elseif($this->session->userdata('ct_referrer')){
							// $url = $this->session->userdata('ct_referrer');
							// $this->session->unset_userdata('ct_referrer');
							// header("Location: ".$url);
							// exit;
						// }
						else header("Location: ".base_url());
					}else{ // register_user_fb_error
						
					}				
				}elseif(isset($_POST['google_id'])){
				// if google....
					if($this->user_m->register_user_g($_POST['ct_username'],$_POST['ct_email'],$_POST['ct_password'],$_POST['ct_first_name'],$_POST['ct_last_name'],$_POST['ct_zip'],$email_opt_in,$_POST['google_id'])){
						$this->session->unset_userdata('g_user');
						$ct_user = $this->user_m->get_user_with_email($_POST['ct_email']); 
						$this->user_m->login_user($ct_user->id,array('google'=>$_POST['google_id']));
	
						// check for review or referer (and is citytot.com)
						if($this->session->userdata('temp_review')){
							//insert review in database
							$review = $this->session->userdata('temp_review');
							$this->session->unset_userdata('temp_review');
							$this->load->model('reviews_m');
							
							$class_id = $this->encrypt->decode($review['class_id_code']);
							if($this->reviews_m->has_reviewed($ct_user->id,$class_id)){
								
								// display page asking if you want to replace the existing review
								$this->session->set_userdata('temp_review',$review);
								header("Location: ".base_url('confirm-review-edit?cid='.$class_id.'&uid='.$ct_user->id));
								exit;
							}
							if($this->reviews_m->submit_review($ct_user->id,$review['class_id_code'],$review['heart_hate'],$review['instructor_quality'],$review['customer_service'],$review['parent_engagement'],$review['facilities'],$review['comments'])){
								// forward to review submitted page.
								header("Location: ".base_url('review-submitted'));
								exit;						
							}else{
								// set message and forward to review submitted page.
								$this->session->set_userdata('system_message',"There was a problem submitting your review.");
								header("Location: ".base_url('review-submitted'));
								exit;
							}
						}
						// elseif($this->session->userdata('ct_referrer')){
							// $url = $this->session->userdata('ct_referrer');
							// $this->session->unset_userdata('ct_referrer');
							// header("Location: ".$url);
							// exit;
						// }
						else header("Location: ".base_url());
					}else{ // register_user_g_error
						
					}
				}else{
				// citytot register user
					$token = $this->user_m->register_user_ct($_POST['ct_username'],$_POST['ct_email'],$_POST['ct_password'],$_POST['ct_first_name'],$_POST['ct_last_name'],$_POST['ct_zip'],$email_opt_in);
					if($token){
						$t = explode(':', $this->encrypt->decode(urldecode($token)));
						// print_r($t); die;
						// $this->user_m->login_user($t[0],array('citytot'=>true));
						$email = $_POST['ct_email'];
						$subject = "Account Confirmation";
						$msg = "Thank you for registering at CityTot, NYC’s smartest resource hub for early childhood classes.\n\n";
						$msg .= "Please click the link below (or paste it into your browser's address bar) to activate your account.\n";
						$msg .= base_url('activate_account?c='.$token)."\n\n";
						$msg .= "If you have received this email in error - please disregard or report it to us.\n";
						
						
						if(mail($email, $subject, $msg)){
						// check for review or referer (and is citytot.com)
							if($this->session->userdata('temp_review')){
								//insert review in database
								$review = $this->session->userdata('temp_review');
								$this->session->unset_userdata('temp_review');
								$this->load->model('reviews_m');
								
								$class_id = $this->encrypt->decode($review['class_id_code']);
								if($this->reviews_m->has_reviewed($t[0],$class_id)){
									// should never be true - first time register
									// display page asking if you want to replace the existing review
									$this->session->set_userdata('temp_review',$review);
									header("Location: ".base_url('confirm-review-edit?cid='.$class_id.'&uid='.$t[0]));
									exit;
								}
								if($this->reviews_m->submit_review($t[0],$review['class_id_code'],$review['heart_hate'],$review['instructor_quality'],$review['customer_service'],$review['parent_engagement'],$review['facilities'],$review['comments'])){
									// forward to review submitted page.
									$this->session->set_userdata('system_message',"Your review has been submitted and a confirmation email for your CityTot account has been sent to your address.");
									header("Location: ".base_url('review-submitted'));
									exit;						
								}else{
									// set message and forward to review submitted page.
									$this->session->set_userdata('system_message',"There was a problem submitting your review.");
									header("Location: ".base_url('review-submitted'));
									exit;
								}
							}
							$status['new-account'] = "Thanks for registering.  A confirmation email has been sent to your email address.";	
						}else
							$errors['new-account'] = "There was a problem sending the confirmation email.  Please contact us directly.";
					}else{
						$errors['new-account'] = "There was a problem creating the account.  Please contact us directly.";
					}
				}
				
			}// end create new user
		} // end ct_new_account	
			
		$page->Data('errors',$errors);
		$page->Data('status',$status);
		$page->show();
	} // end register

	public function activate_account(){
		if(!isset($_GET['c'])){
			header("Location:".base_url()); exit;
		} 
		$this->load->model('user_m');
		if($this->user_m->activate_account($_GET['c'])){
			$this->smrke->page_message("Thank you for registering with CityTot.  You can now <a href=\"".base_url('login')."\" >log in</a> and write personal reviews of classes. Pay it forward and share your experiences.");
			exit;
		}
		// $page = new Page();
		// $page->content('activate_account-v');
		// $page->title("Activate Account");
		// if($this->user_m->activate_account($_GET['c']))
			// $page->Data('success',true);
		// $page->show();
	} // end activate_account

	
	public function reset_password(){
		if(!isset($_GET['c'])) header("Location:".base_url());
		$this->load->model('user_m');
		$page = new Page('reset_password');	
		if(isset($_POST['reset_password'])){
			$this->load->library('form_validation');
			$this->form_validation->set_rules('password', 'Password', 'trim|required|xssclean');
			$this->form_validation->set_rules('password_conf', 'Confirm Password', 'trim|required|matches[password]');
			 
			if ($this->form_validation->run() == FALSE)
			{
				$page->Data('errors',validation_errors());
			}
			else{
				if($this->user_m->reset_password($_GET['c'],$_POST['password']))
					$page->Data('status',"Your password has been reset.  You will be redirected in a few moments.");
				else
					$page->Data('errors',"There was a problem resetting your password.  Please contact us directly.");
			}
		}
		$page -> show();
	}
		
	public function resend_email(){
		if(!isset($_GET['c'])){
			$this->smrke->redirect_error("No token provided for resend_email.");
		} 
		$this->load->model('user_m');
		$page = new Page();
		$page->content('resend_email-v');
		$page->title("Resend Email");
		$email=$this->user_m->resend_email($_GET['c']);
		if($email){
			$this->smrke->page_message("We have resent the confirmation email to the address you provided (".$email.").");
		}
			
		$page->show();
		
	}
	
	public function user_settings(){
		// if($this->ct_user->password) echo 'yes';
		// else echo 'no';
		// die;
		$this->load->model('user_m');
		$this->load->library('form_validation');
		if(!$this->ct_user){
			header("Location:".base_url()); exit;
		}
		$page = new Page("user_settings");
		$errors = array(); $status = array();
		
		if(isset($_POST['ct_save_account'])){
			// echo "post:<pre>".print_r($_POST,true)."</pre>";
			$this->form_validation->set_rules('ct_first_name', 'First Name', 'trim|required|max_length[20]|xss_clean');
			$this->form_validation->set_rules('ct_last_name', 'Last Name', 'trim|required|xss_clean');
			if($this->ct_user->email != $_POST['ct_email'])
				$this->form_validation->set_rules('ct_email', 'Email', 'trim|required|valid_email|is_unique[users.email]|xss_clean');
			if($this->ct_user->username != $_POST['ct_username'])
			$this->form_validation->set_rules('ct_username', 'Username', 'trim|required|max_length[20]|is_unique[users.username]|xss_clean');
			$this->form_validation->set_rules('ct_zip', 'Zip Code', 'trim|numeric|max_length[5]|min_length[5]');			   

			$this->form_validation->set_message('is_unique', 'We already have that %s in our system.');
			
			if ($this->form_validation->run() == FALSE){
				foreach ($_POST as $key => $value) $page->Data($key,$value);
				$errors['save-account'] = $this->form_validation->error_array();
			}
			else{ //save user
				$args['username'] = $_POST['ct_username'];
				$args['first_name'] = $_POST['ct_first_name'];
				$args['last_name'] = $_POST['ct_last_name'];
				$args['email'] = $_POST['ct_email'];
				$args['zipcode'] = $_POST['ct_zip'];
				$args['email_opt_in'] = isset($_POST['email_opt_in'])?1:0;
				// foreach ($_POST as $key => $value){
					// echo $key.": ".$value."<br>";
					// $args[$key] = $value;
				// }
				// die('done');
				if($this->user_m->update_user($this->ct_user->id,$args)){
					//  show system message
					$status['save-account'] = "Your settings have been saved";
					// get updated user
					$this->ct_user = $this->user_m->get_user($this->session->userdata('user_id'));
				}else{
					// show system message
				}
					
			}
		} // end if save user
		
		if(isset($_POST['ct_change_password'])){
			if($this->ct_user->password)
				$this->form_validation->set_rules('ct_old_password', 'Old Password', 'trim|required|xss_clean');
			else $this->form_validation->set_rules('ct_old_password', 'Old Password', 'trim|xss_clean');
			$this->form_validation->set_rules('ct_password', 'New Password', 'trim|required|xss_clean');
			$this->form_validation->set_rules('ct_password2', 'Confirm Password', 'trim|required|matches[ct_password]');
			
			if ($this->form_validation->run() == FALSE){
				foreach ($_POST as $key => $value) $page->Data($key,$value);
				$errors['save-account'] = $this->form_validation->error_array();
			}
			else{ //change user password
				// check old password
				if($this->ct_user->password && $this->ct_user->password != crypt($_POST['ct_old_password'],$this->ct_user->password)){
					$errors['save-account']['ct_old_password'] = "Your password was incorrect";
				}else{
					if($this->user_m->change_password($this->ct_user->id,$_POST['ct_password']))
						$status['change-password'] = "Your password has been changed";
					else 
						$errors['save-account']['ct_password2'] = "Error changing passwords.";
				}
				// if($this->user_m->update_user($this->ct_user->id,$args)){
					// //  show system message
					
					// // get updated user
					// $this->ct_user = $this->user_m->get_user($this->session->userdata('user_id'));
				// }else{
					// // show system message
				// }						
			}
		}
		
		$page->Data('status',$status);
		$page->Data('errors',$errors);
		$page->show();
	}
	
}
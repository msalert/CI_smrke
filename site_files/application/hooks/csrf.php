<?php
/**
 * CSRF Protection Class
 */
 class CSRF_Protection
 {
 	/**
	 * Holds CI instance
	 * 
	 * @var CI instance
	 */
	 private $CI;
	 
	 /**
	  * Name to store csrf token in session
	  * 
	  * @var string
	  */
	  private static $token_name = "csrf_token";
	  
	  /**
	   * Stores the token
	   * 
	   * @var string
	   */
	  private static $token;
	  
	  // ---------------------------------------------------------------------
	  
	  public function __construct(){
	  	$this->CI =& get_instance();
		
	  	// Load session library
		$this->CI->load->library('session');
	  }
	  
	  /**
	   * Generate the CSRF token and store it to session.
	   * 
	   * @return void
	   */
	   public function generate_token(){
			if($this->CI->session->userdata(self::$token_name) === FALSE){
				// old token expired.  Generate and store new token.
				self::$token = md5(uniqid(time()));
				$this->CI->session->set_userdata(self::$token_name, self::$token);
			}
			else{
				// set it to local variable
				self::$token = $this->CI->session->userdata(self::$token_name);
			}
	   }
	   
	   
	   /**
	    * Validates a submitted token on POST
	    * 
	    * @return void
	    */
	    public function validate_token(){
	    	if($_SERVER['REQUEST_METHOD'] == 'POST'){
	    		self::$token = $this->CI->session->userdata(self::$token_name);
				$post_token = $this->CI->input->post(self::$token_name);
				// check token is set and valid
				if($post_token === FALSE || $post_token != self::$token){
					// Invalid request
					show_error('Request was invalid.  Tokens did not match.',400);
				}
	    	}
	    }
		
		/**
		 * Inject hidden tags with CSRF value into all POST forms and meta tags in head
		 * 
		 * @return void
		 */
		 public function inject_tokens(){
		 	$output = $this->CI->output->get_output();
			
			// print_r($output); 
			// echo "in hook function";
			// die;
			// Inject into form
			$output = preg_replace('/(<(form|FORM)[^>]*(method|METHOD)="(post|POST)"[^>]*>)/',
									'$0<input type="hidden" name="'.self::$token_name.'" value="'.self::$token.'" >',
									$output);
									
			// Inject into head
			$output = preg_replace('/<(\/head>)/',
									 '<meta name="csrf-name" content="'.self::$token_name.'">'."\n".
									 '<meta name="csrf-token" content="'.self::$token.'">'."\n".'$0',
									  $output);
			$this->CI->output->_display($output);
		}
 }
 
 

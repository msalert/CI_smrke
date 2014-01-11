<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class User_m extends MY_Model{	

	function __construct(){
		parent::__construct();
	}


	function get_users($args){
		$a = array();
		$users['num_total'] = $users['num_active'] = $users['num_suspended'] = $users['num_blocked'] = $users['num_deleted'] = 0;
		$sql = "SELECT status , COUNT( * ) AS count FROM users GROUP BY status";
		$q = $this->db->query($sql);
		foreach ($q->result() as $k) {
			$users['num_'.$k->status]=$k->count;
			$users['num_total']+=$k->count;
		}
		
		$sql = "SELECT u. * , IFNULL( r.total, 0 ) AS num_reviews
		FROM users u
		LEFT JOIN (
		
		SELECT COUNT( * ) total, user_id
		FROM reviews
		GROUP BY user_id
		)r ON u.id = r.user_id ";
			
		if(isset($args['status'])){
			$sql .= " WHERE u.status = ? ";
			$a[] = $args['status'];
		}
		
		if(isset($args['ord']) && isset($args['ocol']) && 
			in_array($args['ord'], array("asc","desc")) &&
			in_array($args['ocol'], array("last_name","username","email"))){
			$sql .= " ORDER BY ".$args['ocol']." ".strtoupper($args['ord'])." ";
		}
			
		$q = $this->db->query($sql,$a);
		$num_rows = $q->num_rows();
		$pagenum = (isset($args['page']) && is_numeric($args['page']))? $args['page']:1;
		$sql .= " LIMIT ".(($pagenum - 1)*10).", 10";
		$q = $this->db->query($sql,$a);
		$users['users'] = $q->result();
		$users['num_rows'] = $num_rows;
		$users['page_num'] = $pagenum;
		return $users;
		
	} //end function get_users







	function verify_user($email,$password){
		//get user
		$sql = "SELECT * FROM users WHERE email = ?";
		$q = $this->db->query($sql, $email);
		
		if ($q->num_rows()==1){
			
			$q = $q->result();
			// check password
			if($q[0]->password != crypt($password,$q[0]->password)){
				
				// echo "password wrong"; die;
				
				// if password invalid, check and increment login_attempts
				if($q[0]->invalid_attempts > 3){
					// too many login attempts - deactivate account and send email
					$this->load->library('encrypt');
					$token = $this->random_string(16);
					$encrypted_token = $this->encrypt->encode($q[0]->id.":".$token);
					
					$sql = "UPDATE users SET activated = 0, invalid_attempts = 0, token = ? WHERE id = ?";
					if($this->db->query($sql,array($token,$q[0]->id))){  
						
						$subject = "CityTot - Unauthorized Access Attempts";
						$msg = "Your account at CityTot has had several invalid login attempts.\n\n";
						$msg .= "Please click the link below (or paste it into your browser's address bar) to reactivate your account.\n";
						$msg .= "You should then change your password.\n\n";
						$msg .= base_url('activate_account?c='.urlencode($encrypted_token))."\n\n";
						$msg .= "If you have received this email in error - please disregard or report it to us.\n";
						
						// set error
						if(mail($q[0]->email, $subject, $msg))
							$q[0]->error = "Too many invalid login attempts.  Your account has been deactivated and an email sent to the address on file.";
						else
							$q[0]->error = "There's a problem with your account.  Please contact us directly.";
					}else{
						$q[0]->error = "There's a problem with your account.  Please contact us directly.";
					}
					
				}else{ // increment login_attempts
					$sql = "UPDATE users SET invalid_attempts = invalid_attempts + 1 WHERE id = ?";
					$this->db->query($sql,$q[0]->id);
					$q[0]->error = "Invalid login information";
				}
				
			}else{ // password correct
				// echo "password right"; die;
				
				
				if(!$q[0]->activated){ // user not activated yet.
					$encrypted_token = $this->encrypt->encode($q[0]->id.":".$q[0]->token);
					$q[0]->error = "Your account has not been activated.". 
					" You should have received a Confirmation email at the address you provided.".
					" Click the link in the email to activate your account."."<br><br>".
					"<a href='".base_url('resend_email?c='.urlencode($encrypted_token))."'>Resend Email</a>";
				}else{
					$sql = "UPDATE users SET invalid_attempts = 0 WHERE id = ?";
					$this->db->query($sql,$q[0]->id);
				}
			}	
			return $q[0];
		} else return false;
	}
	

	
	
	function delete_user($id){
		$sql = "DELETE FROM users WHERE id = ?";
		return $this->db->query($sql,$id);
	}
		
	function update_user($id,$args){			
		$a = array();
		$sql = "UPDATE users SET";
		foreach ($args as $key => $value) {
			$sql .= " ".$key."= ?,";
			$a[]=$value;
		}
		$sql = trim($sql,',');
		$sql .= " WHERE id = ?";
		$a[] = $id;
		
		return $this->db->query($sql,$a);	
	}

	function get_user_with_email($email){
		$sql = "SELECT * FROM users WHERE email = ?";
		$q = $this->db->query($sql,$email);
		if($q->num_rows==1){
			$q = $q->result();
			return $q[0];
		}else return false;
	} // end get_user_with_email
	
	function login_user($user, $method){
		// enter id and method into login table
		// echo $user; die;
		$sql = "INSERT INTO user_login_log (user_id,login_method) VALUES (?,?)";
		if($this->db->query($sql,array($user,key($method)))){
			$sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
			$this->db->query($sql,$user);
			$user = $this->get_user($user);
			// print_r($user); die;
			$this->session->set_userdata('user_id',$user->id);
			$this->session->set_userdata('first_name',$user->first_name);
			
			// enter fb or g id if appropriate in users table
			switch (key($method)) {
				case 'google':
					if($user->google_id){
						if($method['google'] != $user->google_id){
							$err = "User with id:".$user->id." logged in with google id:".
							$method['google']." and already has google id:".$user->google_id.
							" in the database.";
							$this->log_error($err);
						}
						$this->update_user($user->id, array("google_id"=>$method['google']));
					}else{
						$this->update_user($user->id, array("google_id"=>$method['google']));
					}
					break;
				case 'facebook':
					if($user->facebook_id){
						if($method['facebook'] != $user->facebook_id){
							$err = "User with id:".$user->id." logged in with facebook id:".
							$method['facebook']." and already has facebook id:".$user->facebook_id.
							" in the database.";
							$this->log_error($err);
						}
						$this->update_user($user->id, array("facebook_id"=>$method['facebook']));
					}else{
						$this->update_user($user->id, array("facebook_id"=>$method['facebook']));
					}
					break;
				
				default:
					
					break;
			}
			
		}else return false;
		
	}


	public function is_valid_username($u){
		if(!ctype_alnum($u) || strlen($u)>20) return false;
		
		$sql = "SELECT * FROM users WHERE username = ?";
		$q = $this->db->query($sql,$u);
		if($q->num_rows()>0) return false;
		
		$sql = "SELECT * FROM blocked_sequences WHERE ? LIKE CONCAT('%',word,'%')";
		$q=$this->db->query($sql,$u);
		if($q->num_rows()>0) return false;
		
		$sql = "SELECT * FROM blocked_words WHERE word LIKE ?";
		$q=$this->db->query($sql,trim($u,' -_*'));
		if($q->num_rows()>0) return false;
		
		return true;
	}
	

	
	
	function get_user($id){
		if($id===FALSE || $id === NULL) return false;
		$sql = "SELECT users.id, users.first_name, users.last_name, users.password,
			users.email, users.email_opt_in, users.username, permissions.name AS perm,
			users.zipcode, users.google_id, users.facebook_id
			FROM users LEFT JOIN users_permissions ON users.id = users_permissions.user_id
			LEFT JOIN permissions ON users_permissions.permission_id = permissions.id
			WHERE users.id = ?";
		$q = $this->db->query($sql,$id);
		if ($q->num_rows()==1){
			$q = $q->result();
			return $q[0];
		} else return false;
	}
		
	function change_password($user_id, $new_password){
		$salt = $this->random_string(8);
		$salt = sprintf('$1$%s$‌', $salt);
		$hash = crypt($new_password,$salt);
		$sql = "UPDATE users SET password = ? WHERE id = ?";
		return $this->db->query($sql,array($hash,$user_id));
	} //end function change_password

	function get_user_with_token($token){
		$sql = "SELECT * FROM users WHERE token = ?";
		$q = $this->db->query($sql,$token);
		if($q->num_rows()==1){
			$q = $q->result();
			return $q[0];	
		}else{
			return false;
		}
	}	

	// adds validation code to be sent out as link in email
	function set_token($email){
		$sql = "SELECT * FROM users WHERE email = ?";
		$q = $this->db->query($sql, array($email));

		if ($q->num_rows()==1){
			$q=$q->result();
			$sql = "UPDATE users SET token = ? WHERE id = ?";
			$token = $this->random_string(16);
			$this->db->query($sql,array($token,$q[0]->id));
			return $this->encrypt->encode($q[0]->id.":".$token);
		}else return false;
	}
	
	function send_reset_password($email){
			$e_token = $this->set_token($email); 
			if($e_token){
				$subject = "Password Reset Request";
				$msg = "A request has been made at CityTot to reset your password.\n\n";
				$msg .= "Please click the link below (or paste it into your browser's address bar) to reset your password.\n";
				$msg .= base_url('reset_password?c='.urlencode($e_token))."\n\n";
				$msg .= "If you have received this email in error - please disregard it or report it to us.\n";
				
				if(mail($email, $subject, $msg)){
					return true;
				}else{
					$this->log_error("Problem sending the reset password email.");
					return false;
				}
					
			}else{
				// no user with email
				return false;
			}
	}
	
	function reset_password($code,$password){
		$data = explode(":",$this->encrypt->decode($code));
		if(count($data)==2){
			// select for id and token
			$sql = "SELECT * FROM users WHERE id = ? AND token = ?";
			$query = $this->db->query($sql, $data);
			if($query->num_rows()===1){
				// set password user
				$salt = $this->random_string(8);
				$salt = sprintf('$1$%s$‌', $salt);
				$hash = crypt($password,$salt);
				$sql = "UPDATE users SET password = ? WHERE id = ?";
				return $this->db->query($sql,array($hash,$data[0]));	
			}else return false; // no user with id and token
		}else{ // error parsing url hash
			return false;
		}
		
		
		
		$sql = "SELECT * FROM users WHERE token = ?";
		$q = $this->db->query($sql,$token);
		if($q->num_rows()==1){
			$sql = "UPDATE users SET password = ?, token = NULL WHERE token = ?";
			return $this->db->query($sql,array(md5($password),$token));			
		}else 
			return false;
	}
	
	function register_user_ct($username,$email,$password,$first_name,$last_name,$zip_code,$email_opt_in){
		$this->load->library('encrypt');
		$token = $this->random_string(16);
		
		// set cost for password hash
		// $size = CRYPT_SALT_LENGTH - 4;
		// create random salt
		$salt = $this->random_string(8);
		// echo $salt."<hr>";
		$salt = sprintf('$1$%s$‌', $salt);
		$hash = crypt($password,$salt);
		// echo $hash." : ".strlen($hash)."<hr>"; 
		// echo crypt($password,$hash); die;
		// insert user
		$sql = "INSERT INTO users (username,email, password, first_name, last_name, zipcode, token, email_opt_in) VALUES (?,?,?,?,?,?,?,?)";
		if($this->db->query($sql, array($username,$email,$hash,$first_name,$last_name,$zip_code,$token,$email_opt_in))){
			// get user id
			$sql = "SELECT LAST_INSERT_ID() AS id FROM users";
			$q = $this->db->query($sql);
			if($q->num_rows()>0){
				$q = $q->result();
				$id = $q[0]->id;
				// encrypt id:token
				$encrypted_token = $this->encrypt->encode($id.":".$token); 
				return urlencode($encrypted_token);
			}else return false;  // getting last inset id failed
		} else return false;	// inserting user failed
	} // end function register_user()

	function register_user_fb($username,$email,$password,$first_name,$last_name,$zip_code,$email_opt_in,$fb_id){
		if($password){
			// echo "password"; die;
			// set cost for password hash
			// $size = CRYPT_SALT_LENGTH - 4;
			// create random salt
			$salt = $this->random_string(8);
			// echo $salt."<hr>";
			$salt = sprintf('$1$%s$‌', $salt);
			$password = crypt($password,$salt);
			// echo $hash." : ".strlen($hash)."<hr>"; 
			// echo crypt($password,$hash); die;
		}else{
			$password = null;
		}
		
		// insert user
		$sql = "INSERT INTO users (username,email, password, first_name, last_name, zipcode, facebook_id, email_opt_in, activated) VALUES (?,?,?,?,?,?,?,?,1)";
		if($this->db->query($sql, array($username,$email,$password,$first_name,$last_name,$zip_code,$fb_id,$email_opt_in))){
			// get user id
			$sql = "SELECT LAST_INSERT_ID() AS id FROM users";
			$q = $this->db->query($sql);
			if($q->num_rows()>0){
				$q = $q->result();
				return $q[0]->id;
			}else return false;  // getting last inset id failed
		} else return false;	// inserting user failed
	} // end function register_user_fb()
	
	
	function register_user_g($username,$email,$password,$first_name,$last_name,$zip_code,$email_opt_in,$g_id){
		if($password){
			$salt = $this->random_string(8);
			$salt = sprintf('$1$%s$‌', $salt);
			$password = crypt($password,$salt);
		}else{
			$password = null;
		}
		
		// insert user
		$sql = "INSERT INTO users (username,email, password, first_name, last_name, zipcode, google_id, email_opt_in, activated) VALUES (?,?,?,?,?,?,?,?,1)";
		if($this->db->query($sql, array($username,$email,$password,$first_name,$last_name,$zip_code,$g_id,$email_opt_in))){
			// get user id
			$sql = "SELECT LAST_INSERT_ID() AS id FROM users";
			$q = $this->db->query($sql);
			if($q->num_rows()>0){
				$q = $q->result();
				return $q[0]->id;
			}else return false;  // getting last inset id failed
		} else return false;	// inserting user failed
	} // end function register_user_g()	
	
		
	public function activate_account($code){
		$data = explode(":",$this->encrypt->decode($code));
		if(count($data)==2){
			// select for id and token
			$sql = "SELECT * FROM users WHERE id = ? AND token = ?";
			$query = $this->db->query($sql, $data);
			if($query->num_rows()===1){
				// activate user
				$sql = "UPDATE users SET activated = 1, token = NULL, invalid_attempts = 0 WHERE id = ?";
				return $this->db->query($sql,$data[0]);	
			}else return false; // no user with id and token
		}else{ // error parsing url hash
			return false;
		}
	} // end activate_account
		
	public function resend_email($code){
		// unencrypt code and get id and token
		$data = explode(":",$this->encrypt->decode($code));
		// print_r($data); die;
		if(count($data)==2){
			// select for id and token
			$sql = "SELECT * FROM users WHERE id = ? AND token = ?";
			$q = $this->db->query($sql, $data);
			if($q->num_rows()===1){
				$q=$q->result();
				// send email
				$subject = "Account Confirmation";
				$msg = "Thank you for registering at CityTot.\n\n";
				$msg .= "Please click the link below (or paste it into your browser's address bar) to activate your account.\n";
				$msg .= base_url('activate_account?c='.urlencode($code))."\n\n";
				$msg .= "If you have received this email in error - please disregard or report it to us.\n";
				
				if(mail($q[0]->email, $subject, $msg)) return $q[0]->email;
				else return false;
			}else return false; // no user with id and token
		}else{ // error parsing url hash
			return false;
		}	
	} // end resend_email
	
	
	
	public function show_logins(){
		$sql = "SELECT users.username, user_login_log.* FROM user_login_log 
		LEFT JOIN users ON user_login_log.user_id = users.id";
		$q = $this->db->query($sql);
		foreach ($q->result() as $l) {
			echo "<br>".print_r($l,true);
		}
	}
	
	private function random_string($len){
		mt_srand((double)microtime()*1000000);
		$base ='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$max = strlen($base) - 1;
		$string = '';
		while(strlen($string)<$len){
			$string .= $base{mt_rand(0, $max)};
		} 
		return $string;
	}
}

